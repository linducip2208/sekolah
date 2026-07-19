import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../../../../app/router/routes.dart';
import '../../../../app/theme/app_colors.dart';
import '../../../../core/widgets/app_error.dart';
import '../../../../core/widgets/app_loading.dart';
import '../../../../core/widgets/section_header.dart';
import '../../../../core/widgets/stat_card.dart';
import '../../../auth/presentation/bloc/auth_bloc.dart';
import '../../data/dashboard_repository.dart';
import '../bloc/dashboard_bloc.dart';
import '../widgets/greeting_header.dart';

class StudentDashboardPage extends StatelessWidget {
  const StudentDashboardPage({super.key});

  @override
  Widget build(BuildContext context) {
    return BlocProvider<DashboardBloc>(
      create: (_) => DashboardBloc(DashboardRepository())
        ..add(const DashboardLoadRequested('student')),
      child: const _StudentDashboardView(),
    );
  }
}

class _StudentDashboardView extends StatelessWidget {
  const _StudentDashboardView();

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
                  .add(const DashboardRefreshRequested('student')),
              child: ListView(
                padding: EdgeInsets.zero,
                children: <Widget>[
                  GreetingHeader(
                    name: user?.name ?? 'Siswa',
                    subtitle:
                        '${state.data?['class_name'] ?? '—'} • ${school?.name ?? ''}',
                    avatarUrl: user?.avatarUrl,
                  ),
                  if (state.status == DashboardStatus.loading)
                    const Padding(
                      padding: EdgeInsets.all(24),
                      child: AppLoading(),
                    )
                  else if (state.status == DashboardStatus.error)
                    AppError(
                      message: state.errorMessage ?? 'Gagal memuat',
                      onRetry: () => c
                          .read<DashboardBloc>()
                          .add(const DashboardRefreshRequested('student')),
                    )
                  else
                    _buildContent(c, state.data ?? const <String, dynamic>{}),
                ],
              ),
            );
          },
        ),
      ),
    );
  }

  Widget _buildContent(BuildContext context, Map<String, dynamic> d) {
    final int pendingTasks = (d['pending_tasks'] as num?)?.toInt() ?? 0;
    final int upcomingExams = (d['upcoming_exams'] as num?)?.toInt() ?? 0;
    final int unpaidInvoices = (d['unpaid_invoices'] as num?)?.toInt() ?? 0;
    final num attendancePct = (d['attendance_pct'] as num?) ?? 0;
    final List<dynamic> todaySchedule =
        (d['today_schedule'] as List<dynamic>?) ?? const <dynamic>[];

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
                label: 'Tugas Belum Selesai',
                value: '$pendingTasks',
                icon: Icons.assignment_outlined,
                color: AppColors.accent,
                onTap: () => context.push(Routes.studentClassroom),
              ),
              StatCard(
                label: 'Ujian Mendatang',
                value: '$upcomingExams',
                icon: Icons.quiz_outlined,
                color: AppColors.info,
                onTap: () => context.push(Routes.studentExam),
              ),
              StatCard(
                label: 'Tagihan Belum Bayar',
                value: '$unpaidInvoices',
                icon: Icons.receipt_long_outlined,
                color: AppColors.danger,
                onTap: () => context.push(Routes.studentFees),
              ),
              StatCard(
                label: 'Kehadiran Bulan Ini',
                value: '${attendancePct.toStringAsFixed(0)}%',
                icon: Icons.fact_check_outlined,
                color: AppColors.success,
                onTap: () => context.push(Routes.studentAttendance),
              ),
            ],
          ),
        ),
        SectionHeader(
          title: 'Jadwal Hari Ini',
          action: TextButton(
            onPressed: () => context.push(Routes.studentTimetable),
            child: const Text('Lihat semua'),
          ),
        ),
        if (todaySchedule.isEmpty)
          const Padding(
            padding: EdgeInsets.all(16),
            child: AppEmpty(
              icon: Icons.schedule_outlined,
              title: 'Tidak ada jadwal hari ini',
            ),
          )
        else
          ...todaySchedule.map((dynamic raw) {
            final Map<String, dynamic> s = raw as Map<String, dynamic>;
            return _ScheduleTile(
              time: '${s['start'] ?? '--:--'} - ${s['end'] ?? '--:--'}',
              subject: s['subject'] as String? ?? '-',
              teacher: s['teacher'] as String? ?? '-',
              room: s['room'] as String?,
            );
          }),
        const SizedBox(height: 24),
      ],
    );
  }
}

class _ScheduleTile extends StatelessWidget {
  const _ScheduleTile({
    required this.time,
    required this.subject,
    required this.teacher,
    this.room,
  });
  final String time;
  final String subject;
  final String teacher;
  final String? room;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 4, 16, 8),
      child: Card(
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Row(
            children: <Widget>[
              Container(
                width: 4,
                height: 48,
                decoration: BoxDecoration(
                  color: AppColors.primary,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: <Widget>[
                    Text(time,
                        style: Theme.of(context).textTheme.bodySmall),
                    const SizedBox(height: 2),
                    Text(subject,
                        style: Theme.of(context).textTheme.titleLarge),
                    const SizedBox(height: 2),
                    Text(
                      room == null ? teacher : '$teacher • $room',
                      style: Theme.of(context).textTheme.bodySmall,
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
