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

class TeacherDashboardPage extends StatelessWidget {
  const TeacherDashboardPage({super.key});

  @override
  Widget build(BuildContext context) {
    return BlocProvider<DashboardBloc>(
      create: (_) => DashboardBloc(DashboardRepository())
        ..add(const DashboardLoadRequested('teacher')),
      child: const _TeacherDashboardView(),
    );
  }
}

class _TeacherDashboardView extends StatelessWidget {
  const _TeacherDashboardView();

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
                  .add(const DashboardRefreshRequested('teacher')),
              child: ListView(
                padding: EdgeInsets.zero,
                children: <Widget>[
                  GreetingHeader(
                    name: user?.name ?? 'Guru',
                    subtitle: 'Hari yang produktif menanti.',
                    avatarUrl: user?.avatarUrl,
                  ),
                  if (state.status == DashboardStatus.loading)
                    const Padding(padding: EdgeInsets.all(24), child: AppLoading())
                  else if (state.status == DashboardStatus.error)
                    AppError(
                      message: state.errorMessage ?? 'Gagal memuat',
                      onRetry: () => c
                          .read<DashboardBloc>()
                          .add(const DashboardRefreshRequested('teacher')),
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
    final int classesToday = (d['classes_today'] as num?)?.toInt() ?? 0;
    final int pendingGrading = (d['pending_grading'] as num?)?.toInt() ?? 0;
    final int unmarkedClasses = (d['unmarked_classes'] as num?)?.toInt() ?? 0;
    final List<dynamic> classesList =
        (d['today_classes'] as List<dynamic>?) ?? const <dynamic>[];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: <Widget>[
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
          child: Row(
            children: <Widget>[
              Expanded(
                child: StatCard(
                  label: 'Kelas Hari Ini',
                  value: '$classesToday',
                  icon: Icons.menu_book_outlined,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: StatCard(
                  label: 'Belum Dinilai',
                  value: '$pendingGrading',
                  icon: Icons.rate_review_outlined,
                  color: AppColors.warning,
                  onTap: () => context.push(Routes.teacherExam),
                ),
              ),
            ],
          ),
        ),
        if (unmarkedClasses > 0)
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 8),
            child: Card(
              color: AppColors.warning.withValues(alpha: 0.08),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(16),
                side: BorderSide(color: AppColors.warning.withValues(alpha: 0.3)),
              ),
              child: ListTile(
                leading: const Icon(Icons.warning_amber_rounded,
                    color: AppColors.warning),
                title: Text('$unmarkedClasses kelas belum diabsen'),
                subtitle: const Text('Tandai sekarang sebelum hari berakhir'),
                trailing: const Icon(Icons.chevron_right),
                onTap: () => context.push(Routes.teacherAttendance),
              ),
            ),
          ),
        const SectionHeader(title: 'Jadwal Mengajar Hari Ini'),
        if (classesList.isEmpty)
          const Padding(
            padding: EdgeInsets.all(16),
            child: AppEmpty(
              icon: Icons.schedule_outlined,
              title: 'Tidak ada kelas hari ini',
            ),
          )
        else
          ...classesList.map((dynamic raw) {
            final Map<String, dynamic> s = raw as Map<String, dynamic>;
            return Padding(
              padding: const EdgeInsets.fromLTRB(16, 4, 16, 8),
              child: Card(
                child: ListTile(
                  contentPadding: const EdgeInsets.symmetric(
                      horizontal: 14, vertical: 8),
                  leading: CircleAvatar(
                    backgroundColor:
                        AppColors.primary.withValues(alpha: 0.12),
                    child: const Icon(Icons.school_outlined,
                        color: AppColors.primary),
                  ),
                  title: Text('${s['class_name']} • ${s['subject']}'),
                  subtitle: Text(
                      '${s['start']} - ${s['end']} • ${s['room'] ?? '-'}'),
                  trailing: TextButton(
                    onPressed: () => context.push(Routes.teacherAttendance),
                    child: const Text('Absen'),
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
