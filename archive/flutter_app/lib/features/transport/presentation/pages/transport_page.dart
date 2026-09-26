import 'package:flutter/material.dart';

import '../../../../app/theme/app_colors.dart';
import '../../../../core/widgets/app_error.dart';
import '../../../../core/widgets/app_loading.dart';
import '../../data/transport_repository.dart';

class TransportPage extends StatefulWidget {
  const TransportPage({super.key});

  @override
  State<TransportPage> createState() => _TransportPageState();
}

class _TransportPageState extends State<TransportPage>
    with SingleTickerProviderStateMixin {
  final TransportRepository _repo = TransportRepository();
  late final TabController _tab = TabController(length: 2, vsync: this);
  late Future<List<Map<String, dynamic>>> _routes = _repo.routes();
  late Future<List<Map<String, dynamic>>> _vehicles = _repo.vehicles();

  void _reload() {
    setState(() {
      _routes = _repo.routes();
      _vehicles = _repo.vehicles();
    });
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
        title: const Text('Transportasi'),
        bottom: TabBar(
          controller: _tab,
          tabs: const <Widget>[
            Tab(text: 'Rute', icon: Icon(Icons.route_outlined)),
            Tab(text: 'Kendaraan', icon: Icon(Icons.directions_bus_outlined)),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tab,
        children: <Widget>[
          _RouteList(future: _routes, onRefresh: _reload),
          _VehicleList(future: _vehicles, onRefresh: _reload),
        ],
      ),
    );
  }
}

class _RouteList extends StatelessWidget {
  const _RouteList({required this.future, required this.onRefresh});
  final Future<List<Map<String, dynamic>>> future;
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
        if (list.isEmpty) return const AppEmpty(title: 'Belum ada rute');
        return RefreshIndicator(
          onRefresh: () async => onRefresh(),
          child: ListView.separated(
            padding: const EdgeInsets.all(16),
            itemCount: list.length,
            separatorBuilder: (_, __) => const SizedBox(height: 8),
            itemBuilder: (BuildContext c, int i) {
              final Map<String, dynamic> r = list[i];
              final int stops = (r['stops_count'] as num?)?.toInt() ?? 0;
              return Card(
                child: ListTile(
                  contentPadding: const EdgeInsets.all(14),
                  leading: CircleAvatar(
                    backgroundColor:
                        AppColors.info.withValues(alpha: 0.12),
                    child: const Icon(Icons.alt_route_outlined,
                        color: AppColors.info),
                  ),
                  title: Text(r['name'] as String? ?? '-'),
                  subtitle: Text('$stops perhentian'),
                  trailing: r['fee_amount'] != null
                      ? Text(
                          'Rp ${(((r['fee_amount'] as num) / 100)).toStringAsFixed(0)}/bulan',
                          style: Theme.of(context).textTheme.bodySmall,
                        )
                      : null,
                ),
              );
            },
          ),
        );
      },
    );
  }
}

class _VehicleList extends StatelessWidget {
  const _VehicleList({required this.future, required this.onRefresh});
  final Future<List<Map<String, dynamic>>> future;
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
        if (list.isEmpty) return const AppEmpty(title: 'Belum ada kendaraan');
        return RefreshIndicator(
          onRefresh: () async => onRefresh(),
          child: ListView.separated(
            padding: const EdgeInsets.all(16),
            itemCount: list.length,
            separatorBuilder: (_, __) => const SizedBox(height: 8),
            itemBuilder: (BuildContext c, int i) {
              final Map<String, dynamic> v = list[i];
              return Card(
                child: ListTile(
                  contentPadding: const EdgeInsets.all(14),
                  leading: const Icon(Icons.directions_bus_outlined,
                      color: AppColors.warning, size: 32),
                  title: Text(v['plate_number'] as String? ?? '-'),
                  subtitle: Text(
                      '${v['model'] ?? '-'} • Kapasitas ${v['capacity'] ?? '-'}'),
                  trailing: Text(v['driver_name'] as String? ?? '-',
                      style: Theme.of(context).textTheme.bodySmall),
                ),
              );
            },
          ),
        );
      },
    );
  }
}
