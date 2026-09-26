import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../../app/theme/app_colors.dart';
import '../../../../core/utils/currency_formatter.dart';
import '../../../../core/utils/date_formatter.dart';
import '../../../../core/widgets/app_error.dart';
import '../../../../core/widgets/app_loading.dart';
import '../../data/fees_repository.dart';

class StudentFeesPage extends StatefulWidget {
  const StudentFeesPage({super.key});

  @override
  State<StudentFeesPage> createState() => _StudentFeesPageState();
}

class _StudentFeesPageState extends State<StudentFeesPage> {
  final FeesRepository _repo = FeesRepository();
  late Future<List<Map<String, dynamic>>> _future = _repo.mine();
  bool _processing = false;

  void _reload() => setState(() => _future = _repo.mine());

  Future<void> _pay(int id) async {
    setState(() => _processing = true);
    try {
      final Map<String, dynamic> r = await _repo.initiatePayment(id);
      final String? url = (r['redirect_url'] ??
              r['payment_url'] ??
              r['payment_link']) as String?;
      if (url != null && url.isNotEmpty) {
        await launchUrl(Uri.parse(url), mode: LaunchMode.externalApplication);
      } else if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Tautan pembayaran tidak tersedia.')),
        );
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context)
          .showSnackBar(SnackBar(content: Text(e.toString())));
    } finally {
      if (mounted) setState(() => _processing = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Tagihan')),
      body: FutureBuilder<List<Map<String, dynamic>>>(
        future: _future,
        builder: (BuildContext c,
            AsyncSnapshot<List<Map<String, dynamic>>> snap) {
          if (snap.connectionState == ConnectionState.waiting) {
            return const AppLoading();
          }
          if (snap.hasError) {
            return AppError(message: '${snap.error}', onRetry: _reload);
          }
          final List<Map<String, dynamic>> list =
              snap.data ?? <Map<String, dynamic>>[];
          if (list.isEmpty) return const AppEmpty(title: 'Tidak ada tagihan');
          return RefreshIndicator(
            onRefresh: () async => _reload(),
            child: ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: list.length,
              separatorBuilder: (_, __) => const SizedBox(height: 8),
              itemBuilder: (BuildContext c, int i) {
                final Map<String, dynamic> inv = list[i];
                final int amount = (inv['amount'] as num?)?.toInt() ?? 0;
                final String status = inv['status'] as String? ?? 'unpaid';
                final bool unpaid = status != 'paid';
                return Card(
                  child: Padding(
                    padding: const EdgeInsets.all(14),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: <Widget>[
                        Row(
                          children: <Widget>[
                            Expanded(
                              child: Text(inv['title'] as String? ?? 'Invoice',
                                  style:
                                      Theme.of(context).textTheme.titleLarge),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: (unpaid
                                        ? AppColors.danger
                                        : AppColors.success)
                                    .withValues(alpha: 0.12),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text(
                                unpaid ? 'Belum Bayar' : 'Lunas',
                                style: TextStyle(
                                  color: unpaid
                                      ? AppColors.danger
                                      : AppColors.success,
                                  fontWeight: FontWeight.w600,
                                  fontSize: 11,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Text(CurrencyFormatter.idr(amount),
                            style: Theme.of(context)
                                .textTheme
                                .headlineMedium
                                ?.copyWith(fontWeight: FontWeight.w700)),
                        if (inv['due_at'] != null) ...<Widget>[
                          const SizedBox(height: 4),
                          Text(
                              'Jatuh tempo: ${DateFormatter.dayMonthYear(DateTime.parse(inv['due_at'] as String))}',
                              style: Theme.of(context).textTheme.bodySmall),
                        ],
                        if (unpaid) ...<Widget>[
                          const SizedBox(height: 12),
                          SizedBox(
                            width: double.infinity,
                            child: ElevatedButton.icon(
                              onPressed: _processing
                                  ? null
                                  : () => _pay((inv['id'] as num).toInt()),
                              icon: const Icon(Icons.payment),
                              label: const Text('Bayar Sekarang'),
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                );
              },
            ),
          );
        },
      ),
    );
  }
}
