import 'package:flutter/material.dart';

import '../../../../app/theme/app_colors.dart';
import '../../../../core/utils/date_formatter.dart';
import '../../../../core/widgets/app_error.dart';
import '../../../../core/widgets/app_loading.dart';
import '../../data/attendance_repository.dart';

class StudentAttendancePage extends StatefulWidget {
  const StudentAttendancePage({super.key});

  @override
  State<StudentAttendancePage> createState() => _StudentAttendancePageState();
}

class _StudentAttendancePageState extends State<StudentAttendancePage> {
  late Future<List<Map<String, dynamic>>> _future;
  final AttendanceRepository _repo = AttendanceRepository();

  @override
  void initState() {
    super.initState();
    _future = _repo.mine();
  }

  void _reload() => setState(() => _future = _repo.mine());

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Kehadiran')),
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
          final List<Map<String, dynamic>> list = snap.data ?? <Map<String, dynamic>>[];
          if (list.isEmpty) {
            return const AppEmpty(title: 'Belum ada catatan kehadiran');
          }
          return RefreshIndicator(
            onRefresh: () async => _reload(),
            child: ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: list.length,
              separatorBuilder: (_, __) => const SizedBox(height: 8),
              itemBuilder: (BuildContext c, int i) {
                final Map<String, dynamic> a = list[i];
                final String status = a['status'] as String? ?? 'absent';
                return Card(
                  child: ListTile(
                    leading: _StatusBadge(status: status),
                    title: Text(DateFormatter.fullDate(
                        DateTime.parse(a['date'] as String))),
                    subtitle: Text(a['note'] as String? ?? '-'),
                    trailing: Text(_label(status),
                        style: TextStyle(color: _color(status))),
                  ),
                );
              },
            ),
          );
        },
      ),
    );
  }

  String _label(String s) => switch (s) {
        'present' => 'Hadir',
        'late' => 'Terlambat',
        'absent' => 'Alpa',
        'sick' => 'Sakit',
        'permit' => 'Izin',
        _ => s,
      };

  Color _color(String s) => switch (s) {
        'present' => AppColors.success,
        'late' => AppColors.warning,
        'absent' => AppColors.danger,
        _ => AppColors.info,
      };
}

class _StatusBadge extends StatelessWidget {
  const _StatusBadge({required this.status});
  final String status;

  @override
  Widget build(BuildContext context) {
    final Color c = switch (status) {
      'present' => AppColors.success,
      'late' => AppColors.warning,
      'absent' => AppColors.danger,
      _ => AppColors.info,
    };
    final IconData icon = switch (status) {
      'present' => Icons.check_circle_outline,
      'late' => Icons.access_time,
      'absent' => Icons.cancel_outlined,
      'sick' => Icons.healing_outlined,
      _ => Icons.info_outline,
    };
    return CircleAvatar(
      backgroundColor: c.withValues(alpha: 0.12),
      child: Icon(icon, color: c),
    );
  }
}
