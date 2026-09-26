import 'package:dio/dio.dart';

import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../../../core/api/response_unwrap.dart';
import '../../../core/error/error_handler.dart';

class MarksRepository {
  Future<List<Map<String, dynamic>>> mine() async {
    try {
      final Response<dynamic> r =
          await ApiClient.dio.get<dynamic>(ApiEndpoints.myMarks);
      return unwrapList(r.data).map(_normalize).toList();
    } on DioException catch (e) {
      throw mapDioError(e);
    }
  }

  Map<String, dynamic> _normalize(Map<String, dynamic> raw) {
    final num obtained = (raw['obtained_marks'] as num?) ?? 0;
    final num total = (raw['total_marks'] as num?) ?? 100;
    final num pct = total == 0 ? 0 : (obtained / total) * 100;
    return <String, dynamic>{
      ...raw,
      'subject': raw['subject'] is Map
          ? (raw['subject'] as Map)['name']
          : raw['subject_name'] ?? raw['subject'],
      'exam_name': raw['exam'] is Map
          ? (raw['exam'] as Map)['name']
          : raw['exam_name'] ?? '-',
      'score': pct,
      'grade': raw['grade'] ?? _gradeFromPct(pct.toDouble()),
    };
  }

  String _gradeFromPct(double p) {
    if (p >= 90) return 'A';
    if (p >= 80) return 'B';
    if (p >= 70) return 'C';
    if (p >= 60) return 'D';
    return 'E';
  }
}
