import 'package:dio/dio.dart';

import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../../../core/api/response_unwrap.dart';
import '../../../core/error/error_handler.dart';

class TimetableRepository {
  /// For students. Backend returns array; we group by `day_of_week`.
  Future<Map<String, List<Map<String, dynamic>>>> studentSchedule() async {
    try {
      final Response<dynamic> r =
          await ApiClient.dio.get<dynamic>(ApiEndpoints.timetableStudentMy);
      return _groupByDay(unwrapList(r.data));
    } on DioException catch (e) {
      throw mapDioError(e);
    }
  }

  /// For teachers (or anyone with `teacher_id` resolvable on backend).
  Future<Map<String, List<Map<String, dynamic>>>> teacherSchedule() async {
    try {
      final Response<dynamic> r =
          await ApiClient.dio.get<dynamic>(ApiEndpoints.timetableMy);
      return _groupByDay(unwrapList(r.data));
    } on DioException catch (e) {
      throw mapDioError(e);
    }
  }

  static const Map<int, String> _dayKey = <int, String>{
    1: 'monday',
    2: 'tuesday',
    3: 'wednesday',
    4: 'thursday',
    5: 'friday',
    6: 'saturday',
    7: 'sunday',
  };

  Map<String, List<Map<String, dynamic>>> _groupByDay(
      List<Map<String, dynamic>> items) {
    final Map<String, List<Map<String, dynamic>>> out = <String, List<Map<String, dynamic>>>{
      for (final String d in _dayKey.values) d: <Map<String, dynamic>>[],
    };
    for (final Map<String, dynamic> s in items) {
      final dynamic raw = s['day_of_week'];
      String? key;
      if (raw is num) key = _dayKey[raw.toInt()];
      if (raw is String) key = _dayKey[int.tryParse(raw) ?? 0] ?? raw.toLowerCase();
      key ??= 'monday';
      out[key]!.add(<String, dynamic>{
        ...s,
        'subject': s['subject'] is Map
            ? (s['subject'] as Map)['name']
            : s['subject_name'] ?? s['subject'],
        'teacher': s['teacher'] is Map
            ? (s['teacher'] as Map)['name']
            : s['teacher_name'] ?? s['teacher'],
        'start': _trim(s['start_time'] ?? s['start']),
        'end': _trim(s['end_time'] ?? s['end']),
      });
    }
    return out;
  }

  String? _trim(dynamic v) {
    if (v == null) return null;
    final String s = v.toString();
    return s.length >= 5 ? s.substring(0, 5) : s;
  }
}
