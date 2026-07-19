import 'package:flutter/material.dart';

import '../../../../app/theme/app_colors.dart';
import '../../../../core/utils/currency_formatter.dart';
import '../../../../core/widgets/app_error.dart';
import '../../../../core/widgets/app_loading.dart';
import '../../data/fees_repository.dart';

class AdminFeesPage extends StatefulWidget {
  const AdminFeesPage({super.key});

  @override
  State<AdminFeesPage> createState() => _AdminFeesPageState();
}

class _AdminFeesPageState extends State<AdminFeesPage>
    with SingleTickerProviderStateMixin {
  late final TabController _tab = TabController(length: 2, vsync: this);
  final FeesRepository _repo = FeesRepository();
  late Future<List<Map<String, dynamic>>> _unpaid = _repo.all(status: 'unpaid');
  late Future<List<Map<String, dynamic>>> _paid = _repo.all(status: 'paid');

  void _reload() {
    setState(() {
      _unpaid = _repo.all(status: 'unpaid');
      _paid = _repo.all(status: 'paid');
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Keuangan'),
        bottom: TabBar(controller: _tab, tabs: const <Widget>[
          Tab(text: 'Belum Bayar'),
          Tab(text: 'Lunas'),
        ]),
      ),
      body: TabBarView(
        controller: _tab,
        children: <Widget>[
          _List(future: _unpaid, isPaid: false, onRefresh: _reload),
          _List(future: _paid, isPaid: true, onRefresh: _reload),
        ],
      ),
    );
  }
}

class _List extends StatelessWidget {
  const _List(
      {required this.future, required this.isPaid, required this.onRefresh});

  final Future<List<Map<String, dynamic>>> future;
  final bool isPaid;
  final VoidCallback onRefresh;

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<List<Map<String, dynamic>>>(
      future: future,
      builder: (BuildContext c,
          AsyncSnapshot<List<Map<String, dynamic>>> snap) {
        if (snap.connectionState == ConnectionState.waiting) {
          return const AppLoading();
        }
        if (snap.hasError) {
          return AppError(message: '${snap.error}', onRetry: onRefresh);
        }
        final List<Map<String, dynamic>> list =
            snap.data ?? <Map<String, dynamic>>[];
        if (list.isEmpty) return const AppEmpty(title: 'Tidak ada data');
        return RefreshIndicator(
          onRefresh: () async => onRefresh(),
          child: ListView.separated(
            padding: const EdgeInsets.all(16),
            itemCount: list.length,
            separatorBuilder: (_, __) => const SizedBox(height: 8),
            itemBuilder: (BuildContext c, int i) {
              final Map<String, dynamic> inv = list[i];
              final int amount = (inv['amount'] as num?)?.toInt() ?? 0;
              return Card(
                child: ListTile(
                  contentPadding: const EdgeInsets.all(14),
                  leading: CircleAvatar(
                    backgroundColor:
                        (isPaid ? AppColors.success : AppColors.warning)
                            .withValues(alpha: 0.12),
                    child: Icon(
                      isPaid
                          ? Icons.check_circle_outline
                          : Icons.schedule_outlined,
                      color: isPaid ? AppColors.success : AppColors.warning,
                    ),
                  ),
                  title: Text(inv['student_name'] as String? ?? '-'),
                  subtitle: Text('${inv['title'] ?? '-'} • ${inv['class_name'] ?? ''}'),
                  trailing: Text(CurrencyFormatter.compact(amount),
                      style: const TextStyle(fontWeight: FontWeight.w700)),
                ),
              );
            },
          ),
        );
      },
    );
  }
}
