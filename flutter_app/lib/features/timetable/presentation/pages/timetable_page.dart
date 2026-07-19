import 'package:flutter/material.dart';

import '../../../../app/theme/app_colors.dart';
import '../../../../core/widgets/app_error.dart';
import '../../../../core/widgets/app_loading.dart';
import '../../data/timetable_repository.dart';

class TimetablePage extends StatefulWidget {
  const TimetablePage({super.key});

  @override
  State<TimetablePage> createState() => _TimetablePageState();
}

class _TimetablePageState extends State<TimetablePage>
    with SingleTickerProviderStateMixin {
  late final TabController _tab;
  final TimetableRepository _repo = TimetableRepository();
  late Future<Map<String, List<Map<String, dynamic>>>> _future;

  static const List<String> _days = <String>[
    'monday',
    'tuesday',
    'wednesday',
    'thursday',
    'friday',
    'saturday',
  ];
  static const List<String> _dayLabels = <String>[
    'Sen',
    'Sel',
    'Rab',
    'Kam',
    'Jum',
    'Sab',
  ];

  @override
  void initState() {
    super.initState();
    _tab = TabController(length: _days.length, vsync: this);
    _future = _repo.mine();
    final int wd = DateTime.now().weekday;
    if (wd >= 1 && wd <= 6) _tab.index = wd - 1;
  }

  @override
  void dispose() {
    _tab.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Jadwal Pelajaran'),
        bottom: TabBar(
          controller: _tab,
          isScrollable: true,
          tabs: <Widget>[for (final String l in _dayLabels) Tab(text: l)],
        ),
      ),
      body: FutureBuilder<Map<String, List<Map<String, dynamic>>>>(
        future: _future,
        builder: (BuildContext c,
            AsyncSnapshot<Map<String, List<Map<String, dynamic>>>> snap) {
          if (snap.connectionState == ConnectionState.waiting) {
            return const AppLoading();
          }
          if (snap.hasError) {
            return AppError(
              message: '${snap.error}',
              onRetry: () => setState(() => _future = _repo.mine()),
            );
          }
          final Map<String, List<Map<String, dynamic>>> data =
              snap.data ?? <String, List<Map<String, dynamic>>>{};
          return TabBarView(
            controller: _tab,
            children: <Widget>[
              for (final String day in _days)
                _DayList(items: data[day] ?? const <Map<String, dynamic>>[]),
            ],
          );
        },
      ),
    );
  }
}

class _DayList extends StatelessWidget {
  const _DayList({required this.items});
  final List<Map<String, dynamic>> items;

  @override
  Widget build(BuildContext context) {
    if (items.isEmpty) return const AppEmpty(title: 'Tidak ada jadwal');
    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: items.length,
      separatorBuilder: (_, __) => const SizedBox(height: 8),
      itemBuilder: (BuildContext c, int i) {
        final Map<String, dynamic> s = items[i];
        return Card(
          child: Padding(
            padding: const EdgeInsets.all(14),
            child: Row(
              children: <Widget>[
                Container(
                  width: 60,
                  padding: const EdgeInsets.symmetric(vertical: 8),
                  decoration: BoxDecoration(
                    color: AppColors.primary.withValues(alpha: 0.08),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Column(
                    children: <Widget>[
                      Text(s['start'] as String? ?? '--:--',
                          style: const TextStyle(
                              fontSize: 13, fontWeight: FontWeight.w600)),
                      const SizedBox(height: 2),
                      Text(s['end'] as String? ?? '--:--',
                          style: TextStyle(
                              fontSize: 11,
                              color: Theme.of(context).hintColor)),
                    ],
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: <Widget>[
                      Text(s['subject'] as String? ?? '-',
                          style: Theme.of(context).textTheme.titleLarge),
                      const SizedBox(height: 4),
                      Text(
                        '${s['teacher'] ?? '-'} • ${s['room'] ?? '-'}',
                        style: Theme.of(context).textTheme.bodySmall,
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}
