import 'package:flutter/material.dart';

import '../../../../app/theme/app_colors.dart';
import '../../../../core/utils/date_formatter.dart';
import '../../../../core/widgets/app_error.dart';
import '../../../../core/widgets/app_loading.dart';
import '../../data/classroom_repository.dart';

class ClassroomPage extends StatefulWidget {
  const ClassroomPage({super.key});

  @override
  State<ClassroomPage> createState() => _ClassroomPageState();
}

class _ClassroomPageState extends State<ClassroomPage>
    with SingleTickerProviderStateMixin {
  late final TabController _tab = TabController(length: 2, vsync: this);
  final ClassroomRepository _repo = ClassroomRepository();
  late Future<List<Map<String, dynamic>>> _assignments;
  late Future<List<Map<String, dynamic>>> _materials;

  @override
  void initState() {
    super.initState();
    _assignments = _repo.assignments();
    _materials = _repo.materials();
  }

  void _reload() {
    setState(() {
      _assignments = _repo.assignments();
      _materials = _repo.materials();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Kelas'),
        bottom: TabBar(
          controller: _tab,
          tabs: const <Widget>[
            Tab(text: 'Tugas', icon: Icon(Icons.assignment_outlined)),
            Tab(text: 'Materi', icon: Icon(Icons.menu_book_outlined)),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tab,
        children: <Widget>[
          _List(future: _assignments, kind: 'assignment', onRefresh: _reload),
          _List(future: _materials, kind: 'material', onRefresh: _reload),
        ],
      ),
    );
  }
}

class _List extends StatelessWidget {
  const _List(
      {required this.future, required this.kind, required this.onRefresh});

  final Future<List<Map<String, dynamic>>> future;
  final String kind;
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
        if (list.isEmpty) {
          return AppEmpty(
            title: kind == 'assignment'
                ? 'Belum ada tugas'
                : 'Belum ada materi',
          );
        }
        return RefreshIndicator(
          onRefresh: () async => onRefresh(),
          child: ListView.separated(
            padding: const EdgeInsets.all(16),
            itemCount: list.length,
            separatorBuilder: (_, __) => const SizedBox(height: 8),
            itemBuilder: (BuildContext c, int i) {
              final Map<String, dynamic> a = list[i];
              return Card(
                child: ListTile(
                  contentPadding: const EdgeInsets.all(14),
                  leading: Icon(
                    kind == 'assignment'
                        ? Icons.assignment_outlined
                        : Icons.description_outlined,
                    color: AppColors.primary,
                  ),
                  title: Text(a['title'] as String? ?? '-'),
                  subtitle: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: <Widget>[
                      const SizedBox(height: 4),
                      Text(a['subject'] as String? ?? '-',
                          style: Theme.of(context).textTheme.bodySmall),
                      if (a['due_at'] != null) ...<Widget>[
                        const SizedBox(height: 4),
                        Text(
                          'Tenggat: ${DateFormatter.dateTime(DateTime.parse(a['due_at'] as String))}',
                          style: Theme.of(context).textTheme.bodySmall,
                        ),
                      ],
                    ],
                  ),
                  trailing: const Icon(Icons.chevron_right),
                ),
              );
            },
          ),
        );
      },
    );
  }
}
