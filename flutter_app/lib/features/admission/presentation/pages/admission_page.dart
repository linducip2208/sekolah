import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

import '../../../../app/theme/app_colors.dart';
import '../../../../core/api/api_client.dart';
import '../../../../core/api/api_endpoints.dart';
import '../../../../core/api/response_unwrap.dart';
import '../../../../core/error/error_handler.dart';
import '../../../../core/widgets/app_error.dart';
import '../../../../core/widgets/app_loading.dart';

class AdmissionPage extends StatefulWidget {
  const AdmissionPage({super.key});

  @override
  State<AdmissionPage> createState() => _AdmissionPageState();
}

class _AdmissionPageState extends State<AdmissionPage> {
  late Future<List<Map<String, dynamic>>> _future = _fetch();

  Future<List<Map<String, dynamic>>> _fetch() async {
    try {
      final Response<dynamic> r =
          await ApiClient.dio.get<dynamic>(ApiEndpoints.admission);
      return unwrapList(r.data);
    } on DioException catch (e) {
      throw mapDioError(e);
    }
  }

  void _reload() => setState(() => _future = _fetch());

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Pendaftaran Siswa Baru')),
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
          final List<Map<String, dynamic>> list =
              snap.data ?? <Map<String, dynamic>>[];
          if (list.isEmpty) {
            return const AppEmpty(title: 'Belum ada pendaftar');
          }
          return RefreshIndicator(
            onRefresh: () async => _reload(),
            child: ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: list.length,
              separatorBuilder: (_, __) => const SizedBox(height: 8),
              itemBuilder: (BuildContext c, int i) {
                final Map<String, dynamic> e = list[i];
                final String status = e['status'] as String? ?? 'pending';
                return Card(
                  child: ListTile(
                    leading: CircleAvatar(
                      backgroundColor:
                          AppColors.primary.withValues(alpha: 0.12),
                      child: const Icon(Icons.person_outline,
                          color: AppColors.primary),
                    ),
                    title: Text(e['name'] as String? ?? '-'),
                    subtitle: Text('${e['phone'] ?? '-'} • ${e['source'] ?? '-'}'),
                    trailing: Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: _color(status).withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(_label(status),
                          style: TextStyle(
                              color: _color(status),
                              fontSize: 11,
                              fontWeight: FontWeight.w600)),
                    ),
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
        'pending' => 'Menunggu',
        'contacted' => 'Dihubungi',
        'enrolled' => 'Diterima',
        'rejected' => 'Ditolak',
        _ => s,
      };

  Color _color(String s) => switch (s) {
        'enrolled' => AppColors.success,
        'rejected' => AppColors.danger,
        'contacted' => AppColors.info,
        _ => AppColors.warning,
      };
}
