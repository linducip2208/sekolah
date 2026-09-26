import 'package:flutter/material.dart';

import '../../../../app/theme/app_colors.dart';
import '../../../../core/utils/date_formatter.dart';
import '../../../../core/widgets/app_error.dart';
import '../../../../core/widgets/app_loading.dart';
import '../../data/attendance_repository.dart';

class TeacherAttendancePage extends StatefulWidget {
  const TeacherAttendancePage({super.key, this.sectionId = 1});
  final int sectionId;

  @override
  State<TeacherAttendancePage> createState() => _TeacherAttendancePageState();
}

class _TeacherAttendancePageState extends State<TeacherAttendancePage> {
  final AttendanceRepository _repo = AttendanceRepository();
  DateTime _date = DateTime.now();
  final Map<int, String> _statuses = <int, String>{};
  late Future<List<Map<String, dynamic>>> _future;
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    _future = _repo.classRoster(sectionId: widget.sectionId, date: _date);
  }

  void _reload() {
    setState(() {
      _future = _repo.classRoster(sectionId: widget.sectionId, date: _date);
      _statuses.clear();
    });
  }

  Future<void> _save() async {
    if (_statuses.isEmpty) return;
    setState(() => _saving = true);
    try {
      await _repo.markAttendance(
        sectionId: widget.sectionId,
        date: _date,
        studentStatuses: _statuses,
      );
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Absensi tersimpan.')),
      );
      _reload();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context)
          .showSnackBar(SnackBar(content: Text(e.toString())));
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  Future<void> _pickDate() async {
    final DateTime? d = await showDatePicker(
      context: context,
      firstDate: DateTime(DateTime.now().year - 1),
      lastDate: DateTime.now().add(const Duration(days: 1)),
      initialDate: _date,
    );
    if (d != null) {
      setState(() => _date = d);
      _reload();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Absensi Kelas'),
        actions: <Widget>[
          IconButton(
            icon: const Icon(Icons.calendar_today_outlined),
            onPressed: _pickDate,
          ),
        ],
      ),
      body: Column(
        children: <Widget>[
          Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: <Widget>[
                Icon(Icons.event_note_outlined,
                    color: Theme.of(context).hintColor),
                const SizedBox(width: 8),
                Text(DateFormatter.fullDate(_date),
                    style: Theme.of(context).textTheme.bodyMedium),
              ],
            ),
          ),
          Expanded(
            child: FutureBuilder<List<Map<String, dynamic>>>(
              future: _future,
              builder: (BuildContext c,
                  AsyncSnapshot<List<Map<String, dynamic>>> snap) {
                if (snap.connectionState == ConnectionState.waiting) {
                  return const AppLoading();
                }
                if (snap.hasError) {
                  return AppError(message: '${snap.error}', onRetry: _reload);
                }
                final List<Map<String, dynamic>> roster =
                    snap.data ?? <Map<String, dynamic>>[];
                if (roster.isEmpty) {
                  return const AppEmpty(title: 'Tidak ada siswa');
                }
                return ListView.separated(
                  padding: const EdgeInsets.fromLTRB(12, 0, 12, 96),
                  itemCount: roster.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 6),
                  itemBuilder: (BuildContext c, int i) {
                    final Map<String, dynamic> s = roster[i];
                    final int id = (s['id'] as num).toInt();
                    final String current =
                        _statuses[id] ?? (s['status'] as String? ?? 'present');
                    return Card(
                      child: Padding(
                        padding: const EdgeInsets.all(10),
                        child: Row(
                          children: <Widget>[
                            CircleAvatar(
                              backgroundColor:
                                  AppColors.primary.withValues(alpha: 0.12),
                              child: Text(
                                ((s['name'] as String?) ?? '?')[0].toUpperCase(),
                                style: const TextStyle(
                                    color: AppColors.primary,
                                    fontWeight: FontWeight.w700),
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: <Widget>[
                                  Text(s['name'] as String? ?? '-',
                                      style: Theme.of(context)
                                          .textTheme
                                          .bodyLarge),
                                  Text('NIS: ${s['nis'] ?? '-'}',
                                      style: Theme.of(context)
                                          .textTheme
                                          .bodySmall),
                                ],
                              ),
                            ),
                            SegmentedButton<String>(
                              showSelectedIcon: false,
                              style: const ButtonStyle(
                                visualDensity: VisualDensity.compact,
                              ),
                              segments: const <ButtonSegment<String>>[
                                ButtonSegment<String>(
                                    value: 'present', label: Text('H')),
                                ButtonSegment<String>(
                                    value: 'late', label: Text('T')),
                                ButtonSegment<String>(
                                    value: 'sick', label: Text('S')),
                                ButtonSegment<String>(
                                    value: 'absent', label: Text('A')),
                              ],
                              selected: <String>{current},
                              onSelectionChanged: (Set<String> set) {
                                setState(() => _statuses[id] = set.first);
                              },
                            ),
                          ],
                        ),
                      ),
                    );
                  },
                );
              },
            ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _saving ? null : _save,
        icon: _saving
            ? const SizedBox(
                height: 18,
                width: 18,
                child: CircularProgressIndicator(
                  strokeWidth: 2,
                  valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                ),
              )
            : const Icon(Icons.save_outlined),
        label: const Text('Simpan'),
      ),
    );
  }
}
