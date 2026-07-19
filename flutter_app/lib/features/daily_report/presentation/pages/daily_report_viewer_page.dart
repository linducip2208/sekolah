import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

import '../../../../core/api/api_client.dart';
import '../../../../core/api/api_endpoints.dart';

class DailyReportViewerPage extends StatefulWidget {
  final int studentId;
  final String studentName;
  const DailyReportViewerPage({super.key, required this.studentId, required this.studentName});

  @override
  State<DailyReportViewerPage> createState() => _DailyReportViewerPageState();
}

class _DailyReportViewerPageState extends State<DailyReportViewerPage> {
  late Future<List<Map<String, dynamic>>> _future;

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<List<Map<String, dynamic>>> _load() async {
    final Response<dynamic> r = await ApiClient.dio
        .get<dynamic>(ApiEndpoints.childDailyReports(widget.studentId));
    final dynamic body = r.data;
    final List<dynamic> raw = body is Map<String, dynamic>
        ? (body['data'] as List<dynamic>? ?? <dynamic>[])
        : (body as List<dynamic>? ?? <dynamic>[]);
    return raw.cast<Map<String, dynamic>>();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Daily Report — ${widget.studentName}')),
      body: FutureBuilder<List<Map<String, dynamic>>>(
        future: _future,
        builder: (BuildContext c, AsyncSnapshot<List<Map<String, dynamic>>> snap) {
          if (snap.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snap.hasError) return Center(child: Text('Error: ${snap.error}'));
          final List<Map<String, dynamic>> reports = snap.data ?? <Map<String, dynamic>>[];
          if (reports.isEmpty) {
            return const Center(child: Text('Belum ada laporan harian'));
          }
          return ListView.separated(
            padding: const EdgeInsets.all(12),
            itemCount: reports.length,
            separatorBuilder: (_, __) => const SizedBox(height: 8),
            itemBuilder: (_, int i) => _ReportCard(report: reports[i]),
          );
        },
      ),
    );
  }
}

class _ReportCard extends StatelessWidget {
  final Map<String, dynamic> report;
  const _ReportCard({required this.report});

  @override
  Widget build(BuildContext context) {
    final Map<String, dynamic>? attendance = report['attendance'] as Map<String, dynamic>?;
    final Map<String, dynamic>? canteen    = report['canteen_summary'] as Map<String, dynamic>?;
    final Map<String, dynamic>? clinic     = report['clinic_visit'] as Map<String, dynamic>?;
    final Map<String, dynamic>? wellness   = report['wellness_checkin'] as Map<String, dynamic>?;

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text('📅 ${report['report_date']}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
          const Divider(),
          if (attendance != null) ...<Widget>[
            _Row(label: '🎒 Kehadiran', value: attendance['status']?.toString() ?? '-'),
          ],
          if (canteen != null) ...<Widget>[
            _Row(label: '🍱 Kantin', value: '${canteen['orders']} pesanan · Rp ${((canteen['total'] ?? 0) / 100).toStringAsFixed(0)}'),
          ],
          if (clinic != null) ...<Widget>[
            _Row(label: '🏥 UKS', value: clinic['symptoms']?.toString() ?? '-'),
            if (clinic['sent_home'] == true)
              const Padding(padding: EdgeInsets.only(top: 4), child: Text('⚠️ Dipulangkan', style: TextStyle(color: Colors.orange))),
          ],
          if (wellness != null) ...<Widget>[
            _Row(label: '😊 Mood', value: '${wellness['mood_score']}/10'),
          ],
        ]),
      ),
    );
  }
}

class _Row extends StatelessWidget {
  final String label;
  final String value;
  const _Row({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2),
      child: Row(children: [
        Expanded(flex: 2, child: Text(label, style: const TextStyle(color: Colors.grey, fontSize: 13))),
        Expanded(flex: 3, child: Text(value, style: const TextStyle(fontSize: 13))),
      ]),
    );
  }
}
