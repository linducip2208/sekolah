import 'package:flutter/material.dart';
import 'package:uuid/uuid.dart';

import '../../data/payment_repository.dart';
import '../../domain/payment_method.dart';
import 'payment_status_page.dart';

class PaymentMethodsPage extends StatefulWidget {
  final int invoiceId;
  final int amountCents;
  final String invoiceTitle;

  const PaymentMethodsPage({
    super.key,
    required this.invoiceId,
    required this.amountCents,
    required this.invoiceTitle,
  });

  @override
  State<PaymentMethodsPage> createState() => _PaymentMethodsPageState();
}

class _PaymentMethodsPageState extends State<PaymentMethodsPage> {
  final PaymentRepository _repo = PaymentRepository();
  late Future<List<PaymentMethod>> _methodsFuture;
  bool _initiating = false;

  @override
  void initState() {
    super.initState();
    _methodsFuture = _loadMethods();
  }

  Future<List<PaymentMethod>> _loadMethods() async {
    final List<Map<String, dynamic>> raw = await _repo.methods();
    return raw.map(PaymentMethod.fromJson).toList();
  }

  Future<void> _initiate(PaymentMethod method) async {
    if (_initiating) return;
    setState(() => _initiating = true);
    try {
      final String idemKey = const Uuid().v4();
      final Map<String, dynamic> data = await _repo.initiate(
        invoiceId: widget.invoiceId,
        paymentMethodId: method.id,
        idempotencyKey: idemKey,
      );
      final PaymentTransaction tx = PaymentTransaction.fromJson(data);
      if (!mounted) return;
      Navigator.of(context).pushReplacement(MaterialPageRoute<void>(
        builder: (_) => PaymentStatusPage(referenceNo: tx.referenceNo),
      ));
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Gagal memulai pembayaran: $e')),
      );
    } finally {
      if (mounted) setState(() => _initiating = false);
    }
  }

  String _formatRupiah(int cents) {
    final int rupiah = (cents / 100).round();
    return 'Rp ${rupiah.toString().replaceAllMapped(
          RegExp(r'(\d)(?=(\d{3})+(?!\d))'),
          (Match m) => '${m[1]}.',
        )}';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Pilih Metode Pembayaran')),
      body: Column(children: <Widget>[
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(16),
          color: Theme.of(context).colorScheme.surfaceContainerHighest,
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: <Widget>[
            Text(widget.invoiceTitle, style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 4),
            Text(_formatRupiah(widget.amountCents),
                style: Theme.of(context).textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.bold)),
          ]),
        ),
        Expanded(
          child: FutureBuilder<List<PaymentMethod>>(
            future: _methodsFuture,
            builder: (BuildContext c, AsyncSnapshot<List<PaymentMethod>> snap) {
              if (snap.connectionState == ConnectionState.waiting) {
                return const Center(child: CircularProgressIndicator());
              }
              if (snap.hasError) {
                return Center(child: Text('Gagal memuat: ${snap.error}'));
              }
              final List<PaymentMethod> methods = snap.data ?? <PaymentMethod>[];
              if (methods.isEmpty) {
                return const Center(
                  child: Padding(
                    padding: EdgeInsets.all(32),
                    child: Text(
                      'Belum ada metode pembayaran aktif.\nHubungi admin sekolah.',
                      textAlign: TextAlign.center,
                    ),
                  ),
                );
              }
              return ListView.separated(
                itemCount: methods.length,
                separatorBuilder: (_, __) => const Divider(height: 1),
                itemBuilder: (BuildContext c, int i) {
                  final PaymentMethod m = methods[i];
                  return ListTile(
                    onTap: _initiating ? null : () => _initiate(m),
                    leading: m.logoUrl != null
                        ? Image.network(m.logoUrl!, width: 40, height: 40,
                            errorBuilder: (_, __, ___) => const Icon(Icons.payments))
                        : const Icon(Icons.payments, size: 32),
                    title: Text(m.displayName),
                    subtitle: m.feeFlat > 0 || m.feePercentBp > 0
                        ? Text(
                            'Biaya admin: ${m.feeFlat > 0 ? _formatRupiah(m.feeFlat) : ''}'
                            '${m.feePercentBp > 0 ? ' + ${(m.feePercentBp / 100).toStringAsFixed(2)}%' : ''}'
                            '${m.feeBorneByParent ? ' (ditanggung Anda)' : ' (ditanggung sekolah)'}',
                          )
                        : (m.instruction != null ? Text(m.instruction!) : null),
                    trailing: const Icon(Icons.chevron_right),
                  );
                },
              );
            },
          ),
        ),
      ]),
    );
  }
}
