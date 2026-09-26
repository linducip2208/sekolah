import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:qr_flutter/qr_flutter.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../data/payment_repository.dart';
import '../../domain/payment_method.dart';

class PaymentStatusPage extends StatefulWidget {
  final String referenceNo;
  const PaymentStatusPage({super.key, required this.referenceNo});

  @override
  State<PaymentStatusPage> createState() => _PaymentStatusPageState();
}

class _PaymentStatusPageState extends State<PaymentStatusPage> {
  final PaymentRepository _repo = PaymentRepository();
  PaymentTransaction? _tx;
  Object? _error;
  Timer? _pollTimer;

  @override
  void initState() {
    super.initState();
    _refresh();
    _pollTimer = Timer.periodic(const Duration(seconds: 5), (_) {
      if (_tx?.isTerminal ?? false) {
        _pollTimer?.cancel();
      } else {
        _refresh();
      }
    });
  }

  @override
  void dispose() {
    _pollTimer?.cancel();
    super.dispose();
  }

  Future<void> _refresh() async {
    try {
      final Map<String, dynamic> data = await _repo.show(widget.referenceNo);
      if (!mounted) return;
      setState(() => _tx = PaymentTransaction.fromJson(data));
    } catch (e) {
      if (!mounted) return;
      setState(() => _error = e);
    }
  }

  Future<void> _cancel() async {
    final bool? ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Batalkan transaksi?'),
        content: const Text('Pembayaran tidak akan diproses jika dibatalkan.'),
        actions: <Widget>[
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Batal')),
          FilledButton(onPressed: () => Navigator.pop(context, true), child: const Text('Ya')),
        ],
      ),
    );
    if (ok != true) return;
    try {
      await _repo.cancel(widget.referenceNo);
      _refresh();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Gagal membatalkan: $e')),
      );
    }
  }

  String _formatRupiah(int cents) {
    final int r = (cents / 100).round();
    return 'Rp ${r.toString().replaceAllMapped(
          RegExp(r'(\d)(?=(\d{3})+(?!\d))'),
          (Match m) => '${m[1]}.',
        )}';
  }

  @override
  Widget build(BuildContext context) {
    if (_error != null) {
      return Scaffold(
        appBar: AppBar(title: const Text('Status Pembayaran')),
        body: Center(child: Text('Gagal memuat: $_error')),
      );
    }
    if (_tx == null) {
      return Scaffold(
        appBar: AppBar(title: const Text('Status Pembayaran')),
        body: const Center(child: CircularProgressIndicator()),
      );
    }
    final PaymentTransaction tx = _tx!;
    return Scaffold(
      appBar: AppBar(title: const Text('Status Pembayaran')),
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: <Widget>[
            _StatusBadge(status: tx.status),
            const SizedBox(height: 12),
            Text(_formatRupiah(tx.amount),
                style: Theme.of(context).textTheme.headlineMedium?.copyWith(fontWeight: FontWeight.bold),
                textAlign: TextAlign.center),
            const SizedBox(height: 4),
            Text(tx.referenceNo,
                style: Theme.of(context).textTheme.bodySmall?.copyWith(fontFamily: 'monospace'),
                textAlign: TextAlign.center),
            const SizedBox(height: 24),
            if (tx.status == 'awaiting_payment') ..._buildAwaitingActions(tx),
            if (tx.status == 'paid')
              const Center(child: Text('Pembayaran berhasil. Terima kasih.')),
            const Spacer(),
            if (tx.status == 'awaiting_payment')
              OutlinedButton(onPressed: _cancel, child: const Text('Batalkan transaksi')),
          ]),
        ),
      ),
    );
  }

  List<Widget> _buildAwaitingActions(PaymentTransaction tx) {
    return <Widget>[
      if (tx.redirectUrl != null)
        FilledButton.icon(
          onPressed: () => launchUrl(Uri.parse(tx.redirectUrl!),
              mode: LaunchMode.externalApplication),
          icon: const Icon(Icons.open_in_new),
          label: const Text('Lanjutkan ke halaman pembayaran'),
        ),
      if (tx.deeplinkUrl != null) ...<Widget>[
        const SizedBox(height: 8),
        FilledButton.icon(
          onPressed: () => launchUrl(Uri.parse(tx.deeplinkUrl!),
              mode: LaunchMode.externalApplication),
          icon: const Icon(Icons.account_balance_wallet),
          label: const Text('Buka di aplikasi e-wallet'),
        ),
      ],
      if (tx.vaNumber != null) ...<Widget>[
        const SizedBox(height: 16),
        Card(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: <Widget>[
              Text('Virtual Account ${tx.vaBankCode ?? ""}',
                  style: Theme.of(context).textTheme.bodySmall),
              Row(children: <Widget>[
                Expanded(
                  child: SelectableText(
                    tx.vaNumber!,
                    style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold, fontFamily: 'monospace'),
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.copy),
                  onPressed: () => Clipboard.setData(ClipboardData(text: tx.vaNumber!)),
                ),
              ]),
              Text('Transfer dari mobile/internet banking. Sistem otomatis update setelah pembayaran diterima.',
                  style: Theme.of(context).textTheme.bodySmall),
            ]),
          ),
        ),
      ],
      if (tx.qrString != null) ...<Widget>[
        const SizedBox(height: 16),
        const Center(child: Text('Scan QR di bawah dengan aplikasi pembayaran Anda')),
        const SizedBox(height: 12),
        Center(
          child: Card(
            elevation: 2,
            child: Padding(
              padding: const EdgeInsets.all(12),
              child: QrImageView(data: tx.qrString!, size: 240),
            ),
          ),
        ),
      ],
      if (tx.manualInstructions != null && tx.manualInstructions!.isNotEmpty) ...<Widget>[
        const SizedBox(height: 16),
        Card(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: <Widget>[
              const Text('Transfer ke salah satu rekening berikut:',
                  style: TextStyle(fontWeight: FontWeight.bold)),
              const SizedBox(height: 8),
              for (final dynamic acc in tx.manualInstructions!) ...<Widget>[
                if (acc is Map<String, dynamic>) ...<Widget>[
                  Text('${acc['bank_name'] ?? ''}',
                      style: Theme.of(context).textTheme.bodySmall),
                  SelectableText(acc['account_number']?.toString() ?? '',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontFamily: 'monospace')),
                  Text(acc['account_holder']?.toString() ?? ''),
                  const SizedBox(height: 8),
                ],
              ],
            ]),
          ),
        ),
      ],
      if (tx.expiredAt != null) ...<Widget>[
        const SizedBox(height: 12),
        Center(child: Text('Berlaku hingga: ${tx.expiredAt!.toLocal()}')),
      ],
    ];
  }
}

class _StatusBadge extends StatelessWidget {
  final String status;
  const _StatusBadge({required this.status});

  @override
  Widget build(BuildContext context) {
    final ({Color color, String label}) cfg = switch (status) {
      'paid'             => (color: Colors.green, label: 'Berhasil dibayar'),
      'awaiting_payment' => (color: Colors.orange, label: 'Menunggu pembayaran'),
      'expired'          => (color: Colors.grey, label: 'Kedaluwarsa'),
      'failed'           => (color: Colors.red, label: 'Gagal'),
      'cancelled'        => (color: Colors.grey, label: 'Dibatalkan'),
      'refunded'         => (color: Colors.blue, label: 'Direfund'),
      _                  => (color: Colors.blue, label: status),
    };
    return Center(
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
        decoration: BoxDecoration(
          color: cfg.color.withValues(alpha: 0.15),
          borderRadius: BorderRadius.circular(20),
        ),
        child: Text(cfg.label,
            style: TextStyle(color: cfg.color, fontWeight: FontWeight.w600)),
      ),
    );
  }
}
