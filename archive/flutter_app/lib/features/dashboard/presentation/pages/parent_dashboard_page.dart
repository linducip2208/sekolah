import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../../../../app/router/routes.dart';
import '../../../../app/theme/app_colors.dart';
import '../../../../core/utils/currency_formatter.dart';
import '../../../../core/widgets/app_error.dart';
import '../../../../core/widgets/app_loading.dart';
import '../../../../core/widgets/section_header.dart';
import '../../../../core/widgets/stat_card.dart';
import '../../../auth/presentation/bloc/auth_bloc.dart';
import '../../data/dashboard_repository.dart';
import '../bloc/dashboard_bloc.dart';
import '../widgets/greeting_header.dart';

class ParentDashboardPage extends StatelessWidget {
  const ParentDashboardPage({super.key});

  @override
  Widget build(BuildContext context) {
    return BlocProvider<DashboardBloc>(
      create: (_) => DashboardBloc(DashboardRepository())
        ..add(const DashboardLoadRequested('parent')),
      child: const _ParentDashboardView(),
    );
  }
}

class _ParentDashboardView extends StatelessWidget {
  const _ParentDashboardView();

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthBloc>().state.user;
    return Scaffold(
      body: SafeArea(
        bottom: false,
        child: BlocBuilder<DashboardBloc, DashboardState>(
          builder: (BuildContext c, DashboardState state) {
            return RefreshIndicator(
              onRefresh: () async => c
                  .read<DashboardBloc>()
                  .add(const DashboardRefreshRequested('parent')),
              child: ListView(
                padding: EdgeInsets.zero,
                children: <Widget>[
                  GreetingHeader(
                    name: user?.name ?? 'Orang Tua',
                    subtitle: 'Pantau perkembangan ananda di sini.',
                    avatarUrl: user?.avatarUrl,
                  ),
                  if (state.status == DashboardStatus.loading)
                    const Padding(
                        padding: EdgeInsets.all(24), child: AppLoading())
                  else if (state.status == DashboardStatus.error)
                    AppError(
                      message: state.errorMessage ?? 'Gagal memuat',
                      onRetry: () => c
                          .read<DashboardBloc>()
                          .add(const DashboardRefreshRequested('parent')),
                    )
                  else
                    _content(c, state.data ?? const <String, dynamic>{}),
                ],
              ),
            );
          },
        ),
      ),
    );
  }

  Widget _content(BuildContext context, Map<String, dynamic> d) {
    final List<dynamic> children =
        (d['children'] as List<dynamic>?) ?? const <dynamic>[];
    final int totalUnpaid = (d['total_unpaid_amount'] as num?)?.toInt() ?? 0;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: <Widget>[
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
          child: Row(
            children: <Widget>[
              Expanded(
                child: StatCard(
                  label: 'Total Tagihan',
                  value: CurrencyFormatter.compact(totalUnpaid),
                  icon: Icons.receipt_long_outlined,
                  color: AppColors.danger,
                  onTap: () => context.push(Routes.parentFees),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: StatCard(
                  label: 'Anak Terdaftar',
                  value: '${children.length}',
                  icon: Icons.family_restroom_outlined,
                ),
              ),
            ],
          ),
        ),
        const SectionHeader(title: 'Anak'),
        if (children.isEmpty)
          const Padding(
            padding: EdgeInsets.all(16),
            child: AppEmpty(
              icon: Icons.child_care_outlined,
              title: 'Belum ada anak terhubung',
            ),
          )
        else
          ...children.map((dynamic raw) {
            final Map<String, dynamic> child =
                raw as Map<String, dynamic>;
            final num pct = (child['attendance_pct'] as num?) ?? 0;
            return Padding(
              padding: const EdgeInsets.fromLTRB(16, 4, 16, 8),
              child: Card(
                child: Padding(
                  padding: const EdgeInsets.all(14),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: <Widget>[
                      Row(
                        children: <Widget>[
                          CircleAvatar(
                            backgroundColor: AppColors.secondary
                                .withValues(alpha: 0.12),
                            child: const Icon(Icons.child_care_outlined,
                                color: AppColors.secondary),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: <Widget>[
                                Text(child['name'] as String? ?? '-',
                                    style: Theme.of(context)
                                        .textTheme
                                        .titleLarge),
                                Text(
                                  '${child['class_name'] ?? '-'}',
                                  style: Theme.of(context).textTheme.bodySmall,
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      LinearProgressIndicator(
                        value: pct / 100,
                        backgroundColor:
                            AppColors.success.withValues(alpha: 0.12),
                        color: AppColors.success,
                        minHeight: 8,
                        borderRadius: BorderRadius.circular(4),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Kehadiran ${pct.toStringAsFixed(0)}%',
                        style: Theme.of(context).textTheme.bodySmall,
                      ),
                    ],
                  ),
                ),
              ),
            );
          }),
        const SizedBox(height: 24),
      ],
    );
  }
}
