import 'package:dio/dio.dart';

import 'app_exception.dart';

AppException mapDioError(DioException e) {
  if (e.type == DioExceptionType.connectionTimeout ||
      e.type == DioExceptionType.receiveTimeout ||
      e.type == DioExceptionType.sendTimeout) {
    return NetworkException('Koneksi timeout. Coba lagi.');
  }
  if (e.type == DioExceptionType.connectionError) {
    return NetworkException('Tidak dapat terhubung ke server.');
  }

  final Response<dynamic>? r = e.response;
  if (r == null) {
    return AppException(e.message ?? 'Terjadi kesalahan');
  }

  final dynamic data = r.data;
  final String msg = (data is Map && data['message'] is String)
      ? data['message'] as String
      : 'Terjadi kesalahan (${r.statusCode})';

  switch (r.statusCode) {
    case 401:
      return UnauthorizedException(msg);
    case 403:
      return ForbiddenException(msg);
    case 422:
      Map<String, List<String>>? errors;
      if (data is Map && data['errors'] is Map) {
        errors = (data['errors'] as Map<dynamic, dynamic>).map(
          (dynamic k, dynamic v) => MapEntry<String, List<String>>(
            k.toString(),
            (v as List<dynamic>).map((dynamic e) => e.toString()).toList(),
          ),
        );
      }
      return ValidationException(msg, errors: errors);
    default:
      return ServerException(msg, statusCode: r.statusCode);
  }
}
