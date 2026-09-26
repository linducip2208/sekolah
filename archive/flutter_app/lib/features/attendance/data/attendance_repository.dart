import 'package:dio/dio.dart';

import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../../../core/api/response_unwrap.dart';
import '../../../core/error/error_handler.dart';

class AttendanceRepository {
  Future<List<Map<String, dynamic>>> mine({DateTime? from, DateTime? to}) async {
    try {
      final Response<dynamic> r = await ApiClient.dio.get<dynamic>(
        ApiEndpoints.myAttendance,
        queryParameters: <String, dynamic>{
          if (from != null) 'from_date': from.toIso8601String().substring(0, 10),
          if (to != null) 'to_date': to.toIso8601String().substring(0, 10),
        },
      );
      return unwrapList(r.data);
    } on DioException catch (e) {
      throw mapDioError(e);
    }
  }

  Future<List<Map<String, dynamic>>> classRoster({
    required int sectionId,
    required DateTime date,
  }) async {
    try {
      final Response<dynamic> r = await ApiClient.dio.get<dynamic>(
        ApiEndpoints.attendanceByClass(sectionId),
        queryParameters: <String, dynamic>{
          'date': date.toIso8601String().substring(0, 10),
        },
      );
      return unwrapList(r.data);
    } on DioException catch (e) {
      throw mapDioError(e);
    }
  }

  Future<void> markAttendance({
    required int sectionId,
    required DateTime date,
    required Map<int, String> studentStatuses,
  }) async {
    try {
      await ApiClient.dio.post<dynamic>(
        ApiEndpoints.attendanceByClass(sectionId),
        data: <String, dynamic>{
          'date': date.toIso8601String().substring(0, 10),
          'attendances': studentStatuses.entries
              .map((MapEntry<int, String> e) => <String, dynamic>{
                    'student_id': e.key,
                    'status': e.value,
                  })
              .toList(),
        },
      );
    } on DioException catch (e) {
      throw mapDioError(e);
    }
  }
}
