import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

import '../../../../core/api/api_client.dart';
import '../../../../core/api/api_endpoints.dart';
import '../../../../core/api/response_unwrap.dart';

class ClinicVisitsPage extends StatefulWidget {
  final int studentId;
  final String studentName;
  const ClinicVisitsPage({super.key, required this.studentId, required this.studentName});

  @override
  State<ClinicVisitsPage> createState() => _ClinicVisitsPageState();
}

class _ClinicVisitsPageState extends State<ClinicVisitsPage> {
  late Future<List<Map<String, dynamic>>> _future;

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<List<Map<String, dynamic>>> _load() async {
    final Response<dynamic> r = await ApiClient.dio
        .get<dynamic>(ApiEndpoints.studentClinicVisits(widget.studentId));
    return unwrapList(r.data);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('🏥 UKS — ${widget.studentName}')),
      body: FutureBuilder<List<Map<String, dynamic>>>(
        future: _future,
        builder: (BuildContext c, AsyncSnapshot<List<Map<String, dynamic>>> snap) {
          if (snap.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snap.hasError) return Center(child: Text('Error: ${snap.error}'));
          final List<Map<String, dynamic>> visits = snap.data ?? <Map<String, dynamic>>[];
          if (visits.isEmpty) {
            return const Center(child: Text('Tidak ada riwayat kunjungan UKS'));
          }
          return ListView.separated(
            itemCount: visits.length,
            separatorBuilder: (_, __) => const Divider(height: 1),
            itemBuilder: (BuildContext c, int i) {
              final Map<String, dynamic> v = visits[i];
              return ListTile(
                leading: CircleAvatar(
                  backgroundColor: (v['sent_home'] == true) ? Colors.orange : Colors.blue,
                  child: const Icon(Icons.medical_services, color: Colors.white, size: 18),
                ),
                title: Text(v['symptoms']?.toString() ?? '-'),
                subtitle: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Text('Diagnosis: ${v['diagnosis'] ?? '-'}'),
                  Text(v['visit_at']?.toString() ?? '-',
                      style: const TextStyle(fontSize: 11, color: Colors.grey)),
                ]),
                trailing: v['sent_home'] == true
                    ? const Chip(label: Text('Pulang', style: TextStyle(fontSize: 10)))
                    : null,
                isThreeLine: true,
              );
            },
          );
        },
      ),
    );
  }
}
