import 'package:dio/dio.dart';

import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../../../core/api/response_unwrap.dart';
import '../../../core/error/error_handler.dart';

class ClassroomRepository {
  Future<List<Map<String, dynamic>>> assignments() async {
    try {
      final Response<dynamic> r =
          await ApiClient.dio.get<dynamic>(ApiEndpoints.classroomAssignments);
      return unwrapList(r.data);
    } on DioException catch (e) {
      throw mapDioError(e);
    }
  }

  Future<List<Map<String, dynamic>>> lessons() async {
    try {
      final Response<dynamic> r =
          await ApiClient.dio.get<dynamic>(ApiEndpoints.classroomLessons);
      return unwrapList(r.data);
    } on DioException catch (e) {
      throw mapDioError(e);
    }
  }

  Future<void> submitAssignment(int assignmentId,
      {String? note, String? fileUrl}) async {
    try {
      await ApiClient.dio.post<dynamic>(
        ApiEndpoints.submitAssignment(assignmentId),
        data: <String, dynamic>{
          if (note != null) 'note': note,
          if (fileUrl != null) 'file_url': fileUrl,
        },
      );
    } on DioException catch (e) {
      throw mapDioError(e);
    }
  }
}
