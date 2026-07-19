import 'dart:async';

import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

import '../../../../core/api/api_client.dart';
import '../../../../core/api/api_endpoints.dart';
import '../../../../core/api/response_unwrap.dart';

class BusTrackingPage extends StatefulWidget {
  final int studentId;
  final String studentName;
  const BusTrackingPage({super.key, required this.studentId, required this.studentName});

  @override
  State<BusTrackingPage> createState() => _BusTrackingPageState();
}

class _BusTrackingPageState extends State<BusTrackingPage> {
  Map<String, dynamic>? _data;
  Object? _error;
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    _refresh();
    _timer = Timer.periodic(const Duration(seconds: 10), (_) => _refresh());
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  Future<void> _refresh() async {
    try {
      final Response<dynamic> r = await ApiClient.dio
          .get<dynamic>(ApiEndpoints.childBusLocation(widget.studentId));
      if (!mounted) return;
      setState(() => _data = unwrapMap(r.data));
    } catch (e) {
      if (!mounted) return;
      setState(() => _error = e);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('🚌 Bus ${widget.studentName}')),
      body: RefreshIndicator(
        onRefresh: _refresh,
        child: _buildBody(),
      ),
    );
  }

  Widget _buildBody() {
    if (_error != null) {
      return Center(child: Text('Error: $_error'));
    }
    if (_data == null) {
      return const Center(child: CircularProgressIndicator());
    }

    final Map<String, dynamic>? loc = _data!['location'] as Map<String, dynamic>?;
    final Map<String, dynamic>? trip = _data!['trip'] as Map<String, dynamic>?;

    if (loc == null) {
      return ListView(
        padding: const EdgeInsets.all(24),
        children: const [
          Icon(Icons.directions_bus_outlined, size: 80, color: Colors.grey),
          SizedBox(height: 12),
          Center(
            child: Text(
              'Bus belum aktif saat ini',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
          ),
          SizedBox(height: 8),
          Center(
            child: Text(
              'Lokasi bus akan tampil saat trip aktif berjalan',
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.grey),
            ),
          ),
        ],
      );
    }

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        Card(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Row(children: [
                const Icon(Icons.directions_bus, color: Colors.blue, size: 32),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    Text(
                      trip?['direction'] == 'pickup' ? 'Antar Jemput' : 'Antar Pulang',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                    ),
                    Text('Started: ${trip?['started_at'] ?? '-'}',
                        style: const TextStyle(color: Colors.grey, fontSize: 12)),
                  ]),
                ),
              ]),
              const Divider(height: 24),
              _LocationRow(label: 'Latitude', value: '${loc['lat']}'),
              _LocationRow(label: 'Longitude', value: '${loc['lng']}'),
              _LocationRow(label: 'Speed', value: '${loc['speed_kmh'] ?? '-'} km/h'),
              _LocationRow(label: 'Last update', value: '${loc['recorded_at']}'),
            ]),
          ),
        ),
        const SizedBox(height: 12),
        Card(
          color: Colors.blue.shade50,
          child: const Padding(
            padding: EdgeInsets.all(12),
            child: Row(children: [
              Icon(Icons.refresh, size: 16),
              SizedBox(width: 8),
              Expanded(child: Text('Lokasi update otomatis tiap 10 detik', style: TextStyle(fontSize: 12))),
            ]),
          ),
        ),
      ],
    );
  }
}

class _LocationRow extends StatelessWidget {
  final String label;
  final String value;
  const _LocationRow({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(children: [
        Expanded(child: Text(label, style: const TextStyle(color: Colors.grey))),
        Text(value, style: const TextStyle(fontWeight: FontWeight.w500, fontFamily: 'monospace')),
      ]),
    );
  }
}
