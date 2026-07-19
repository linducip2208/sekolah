import 'package:dio/dio.dart';

import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../../../core/api/response_unwrap.dart';
import '../../../core/error/error_handler.dart';

class LibraryRepository {
  Future<List<Map<String, dynamic>>> books({String? query}) async {
    try {
      final Response<dynamic> r = await ApiClient.dio.get<dynamic>(
        ApiEndpoints.libraryBooks,
        queryParameters: <String, dynamic>{
          if (query != null && query.isNotEmpty) 'q': query,
        },
      );
      return unwrapList(r.data);
    } on DioException catch (e) {
      throw mapDioError(e);
    }
  }

  Future<List<Map<String, dynamic>>> issues() async {
    try {
      final Response<dynamic> r =
          await ApiClient.dio.get<dynamic>(ApiEndpoints.libraryIssues);
      return unwrapList(r.data);
    } on DioException catch (e) {
      throw mapDioError(e);
    }
  }

  Future<void> issue({required int bookId, required int studentId}) async {
    try {
      await ApiClient.dio.post<dynamic>(
        ApiEndpoints.libraryIssue,
        data: <String, dynamic>{'book_id': bookId, 'student_id': studentId},
      );
    } on DioException catch (e) {
      throw mapDioError(e);
    }
  }

  Future<void> returnBook(int issueId) async {
    try {
      await ApiClient.dio.post<dynamic>(ApiEndpoints.libraryReturn(issueId));
    } on DioException catch (e) {
      throw mapDioError(e);
    }
  }
}
