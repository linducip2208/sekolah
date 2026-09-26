class AppException implements Exception {
  AppException(this.message, {this.statusCode, this.errors});

  final String message;
  final int? statusCode;
  final Map<String, List<String>>? errors;

  @override
  String toString() => message;
}

class NetworkException extends AppException {
  NetworkException(super.message);
}

class UnauthorizedException extends AppException {
  UnauthorizedException(super.message) : super(statusCode: 401);
}

class ForbiddenException extends AppException {
  ForbiddenException(super.message) : super(statusCode: 403);
}

class ValidationException extends AppException {
  ValidationException(super.message, {Map<String, List<String>>? errors})
      : super(statusCode: 422, errors: errors);
}

class ServerException extends AppException {
  ServerException(super.message, {int? statusCode})
      : super(statusCode: statusCode ?? 500);
}
