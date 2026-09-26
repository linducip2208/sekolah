import 'package:dio/dio.dart';

import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../../../core/api/response_unwrap.dart';
import '../../../core/error/error_handler.dart';

class DashboardRepository {
  Future<Map<String, dynamic>> fetch(String role) async {
    try {
      final String path = switch (role) {
        'teacher' => ApiEndpoints.teacherDashboard,
        'parent' => ApiEndpoints.parentDashboard,
        'admin' || 'school_admin' => ApiEndpoints.adminDashboard,
        _ => ApiEndpoints.studentDashboard,
      };
      final Response<dynamic> r = await ApiClient.dio.get<dynamic>(path);
      return unwrapMap(r.data);
    } on DioException catch (e) {
      throw mapDioError(e);
    }
  }
}
