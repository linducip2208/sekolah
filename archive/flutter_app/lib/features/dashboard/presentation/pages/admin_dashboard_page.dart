import 'package:fl_chart/fl_chart.dart';
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

class AdminDashboardPage extends StatelessWidget {
  const AdminDashboardPage({super.key});

  @override
  Widget build(BuildContext context) {
    return BlocProvider<DashboardBloc>(
      create: (_) => DashboardBloc(DashboardRepository())
        ..add(const DashboardLoadRequested('admin')),
      child: const _AdminDashboardView(),
    );
  }
}

class _AdminDashboardView extends StatelessWidget {
  const _AdminDashboardView();

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthBloc>().state.user;
    final school = context.watch<AuthBloc>().state.school;
    return Scaffold(
      body: SafeArea(
        bottom: false,
        child: BlocBuilder<DashboardBloc, DashboardState>(
          builder: (BuildContext c, DashboardState state) {
            return RefreshIndicator(
              onRefresh: () async => c
                  .read<DashboardBloc>()
                  .add(const DashboardRefreshRequested('admin')),
              child: ListView(
                padding: EdgeInsets.zero,
                children: <Widget>[
                  GreetingHeader(
                    name: user?.name ?? 'Admin',
                    subtitle: school?.name ?? '',
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
                          .add(const DashboardRefreshRequested('admin')),
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
    final int totalStudents = (d['total_students'] as num?)?.toInt() ?? 0;
    final int totalTeachers = (d['total_teachers'] as num?)?.toInt() ?? 0;
    final int feesCollected = (d['fees_collected'] as num?)?.toInt() ?? 0;
    final int feesPending = (d['fees_pending'] as num?)?.toInt() ?? 0;
    final List<dynamic> trend =
        (d['attendance_trend'] as List<dynamic>?) ?? const <dynamic>[];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: <Widget>[
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
          child: GridView.count(
            crossAxisCount: 2,
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            crossAxisSpacing: 12,
            mainAxisSpacing: 12,
            childAspectRatio: 1.4,
            children: <Widget>[
              StatCard(
                label: 'Total Siswa',
                value: '$totalStudents',
                icon: Icons.school_outlined,
              ),
              StatCard(
                label: 'Total Guru/Staf',
                value: '$totalTeachers',
                icon: Icons.badge_outlined,
                color: AppColors.secondary,
              ),
              StatCard(
                label: 'Iuran Diterima',
                value: CurrencyFormatter.compact(feesCollected),
                icon: Icons.payments_outlined,
                color: AppColors.success,
              ),
              StatCard(
                label: 'Tunggakan',
                value: CurrencyFormatter.compact(feesPending),
                icon: Icons.warning_amber_outlined,
                color: AppColors.danger,
              ),
            ],
          ),
        ),
        const SectionHeader(title: 'Akses Cepat'),
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 4, 16, 8),
          child: Row(
            children: <Widget>[
              Expanded(
                child: _QuickAction(
                  icon: Icons.apartment_outlined,
                  label: 'Asrama',
                  color: AppColors.secondary,
                  onTap: () => context.push(Routes.hostel),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _QuickAction(
                  icon: Icons.directions_bus_outlined,
                  label: 'Transport',
                  color: AppColors.warning,
                  onTap: () => context.push(Routes.transport),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _QuickAction(
                  icon: Icons.menu_book_outlined,
                  label: 'Library',
                  color: AppColors.info,
                  onTap: () => context.push(Routes.studentLibrary),
                ),
              ),
            ],
          ),
        ),
        const SectionHeader(title: 'Tren Kehadiran (7 hari)'),
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 4, 16, 16),
          child: Card(
            child: Padding(
              padding: const EdgeInsets.all(14),
              child: SizedBox(
                height: 200,
                child: trend.isEmpty
                    ? const Center(child: Text('Belum ada data'))
                    : LineChart(_chart(trend)),
              ),
            ),
          ),
        ),
      ],
    );
  }

  LineChartData _chart(List<dynamic> data) {
    final List<FlSpot> spots = <FlSpot>[];
    for (int i = 0; i < data.length; i++) {
      final num v = ((data[i] as Map)['value'] as num?) ?? 0;
      spots.add(FlSpot(i.toDouble(), v.toDouble()));
    }
    return LineChartData(
      gridData: const FlGridData(show: true, drawVerticalLine: false),
      borderData: FlBorderData(show: false),
      titlesData: const FlTitlesData(
        show: true,
        rightTitles: AxisTitles(sideTitles: SideTitles(showTitles: false)),
        topTitles: AxisTitles(sideTitles: SideTitles(showTitles: false)),
      ),
      minY: 0,
      maxY: 100,
      lineBarsData: <LineChartBarData>[
        LineChartBarData(
          spots: spots,
          isCurved: true,
          color: AppColors.primary,
          barWidth: 3,
          dotData: const FlDotData(show: false),
          belowBarData: BarAreaData(
            show: true,
            color: AppColors.primary.withValues(alpha: 0.12),
          ),
        ),
      ],
    );
  }
}

class _QuickAction extends StatelessWidget {
  const _QuickAction({
    required this.icon,
    required this.label,
    required this.color,
    required this.onTap,
  });
  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: InkWell(
        borderRadius: BorderRadius.circular(16),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 8),
          child: Column(
            children: <Widget>[
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.12),
                  shape: BoxShape.circle,
                ),
                child: Icon(icon, color: color, size: 22),
              ),
              const SizedBox(height: 8),
              Text(label,
                  style: Theme.of(context)
                      .textTheme
                      .bodySmall
                      ?.copyWith(fontWeight: FontWeight.w600),
                  textAlign: TextAlign.center),
            ],
          ),
        ),
      ),
    );
  }
}
