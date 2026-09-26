import 'package:dio/dio.dart';

import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../../../core/error/app_exception.dart';
import '../../../core/error/error_handler.dart';
import '../../../core/storage/app_storage.dart';
import 'models/school_model.dart';
import 'models/user_model.dart';

class AuthSession {
  AuthSession({required this.user, required this.school, required this.token});
  final UserModel user;
  final SchoolModel school;
  final String token;
}

class AuthRepository {
  Future<AuthSession> login({
    required String email,
    required String password,
    String? schoolCode,
  }) async {
    try {
      final Response<dynamic> r = await ApiClient.dio.post<dynamic>(
        ApiEndpoints.login,
        data: <String, dynamic>{
          'email': email,
          'password': password,
          'device_name': 'mobile',
          if (schoolCode != null && schoolCode.isNotEmpty)
            'school_code': schoolCode,
        },
      );
      if (r.statusCode != 200 || r.data is! Map) {
        throw ServerException(
          (r.data is Map ? r.data['message'] : 'Login gagal') as String,
        );
      }

      final Map<String, dynamic> body = Map<String, dynamic>.from(r.data as Map);
      final String token = body['token'] as String;
      final Map<String, dynamic> userMap =
          Map<String, dynamic>.from(body['user'] as Map);
      final Map<String, dynamic>? schoolMap = userMap['school'] is Map
          ? Map<String, dynamic>.from(userMap['school'] as Map)
          : null;

      final UserModel user = UserModel.fromJson(userMap);
      final SchoolModel school = schoolMap != null
          ? SchoolModel.fromJson(schoolMap)
          : SchoolModel(id: user.schoolId, name: '');

      await AppStorage.saveToken(token);
      await AppStorage.saveUser(user.toJson());
      await AppStorage.saveSchool(school.toJson());

      return AuthSession(user: user, school: school, token: token);
    } on DioException catch (e) {
      throw mapDioError(e);
    }
  }

  Future<void> forgotPassword(String email) async {
    try {
      await ApiClient.dio.post<dynamic>(
        ApiEndpoints.forgotPassword,
        data: <String, String>{'email': email},
      );
    } on DioException catch (e) {
      throw mapDioError(e);
    }
  }

  Future<AuthSession?> restoreSession() async {
    final String? token = await AppStorage.getToken();
    final Map<String, dynamic>? user = await AppStorage.getUser();
    final Map<String, dynamic>? school = await AppStorage.getSchool();
    if (token == null || user == null || school == null) return null;
    return AuthSession(
      user: UserModel.fromJson(user),
      school: SchoolModel.fromJson(school),
      token: token,
    );
  }

  Future<void> logout() async {
    try {
      await ApiClient.dio.post<dynamic>(ApiEndpoints.logout);
    } catch (_) {
      // best effort — local clear regardless
    }
    await AppStorage.clearAuth();
  }

  Future<void> changePassword({
    required String current,
    required String next,
  }) async {
    try {
      await ApiClient.dio.post<dynamic>(
        ApiEndpoints.changePassword,
        data: <String, String>{
          'current_password': current,
          'password': next,
          'password_confirmation': next,
        },
      );
    } on DioException catch (e) {
      throw mapDioError(e);
    }
  }
}
