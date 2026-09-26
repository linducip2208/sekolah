import 'package:dio/dio.dart';

import '../../../app/router/routes.dart';
import '../../../app/router/app_router.dart';
import '../../storage/app_storage.dart';

class ErrorInterceptor extends Interceptor {
  @override
  Future<void> onError(
    DioException err,
    ErrorInterceptorHandler handler,
  ) async {
    if (err.response?.statusCode == 401) {
      await AppStorage.clearAuth();
      AppRouter.maybeInstance?.router.go(Routes.login);
    }
    handler.next(err);
  }
}
