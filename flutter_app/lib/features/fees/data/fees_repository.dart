import 'package:dio/dio.dart';

import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../../../core/api/response_unwrap.dart';
import '../../../core/error/error_handler.dart';

class FeesRepository {
  Future<List<Map<String, dynamic>>> mine() async {
    try {
      final Response<dynamic> r =
          await ApiClient.dio.get<dynamic>(ApiEndpoints.myFeeInvoices);
      return unwrapList(r.data).map(_normalize).toList();
    } on DioException catch (e) {
      throw mapDioError(e);
    }
  }

  Future<List<Map<String, dynamic>>> all({String? status}) async {
    try {
      final Response<dynamic> r = await ApiClient.dio.get<dynamic>(
        ApiEndpoints.feeInvoices,
        queryParameters: <String, dynamic>{
          if (status != null) 'status': status,
        },
      );
      return unwrapList(r.data).map(_normalize).toList();
    } on DioException catch (e) {
      throw mapDioError(e);
    }
  }

  /// Triggers backend to create a payment link via configured gateway.
  Future<Map<String, dynamic>> initiatePayment(int invoiceId) async {
    try {
      final Response<dynamic> r = await ApiClient.dio
          .get<dynamic>(ApiEndpoints.invoicePaymentLink(invoiceId));
      return unwrapMap(r.data);
    } on DioException catch (e) {
      throw mapDioError(e);
    }
  }

  Map<String, dynamic> _normalize(Map<String, dynamic> raw) {
    return <String, dynamic>{
      ...raw,
      'title': raw['title'] ??
          (raw['fee_structure'] is Map
              ? (raw['fee_structure'] as Map)['name']
              : raw['period'] ?? raw['invoice_no'] ?? 'Invoice'),
      'due_at': raw['due_date'] ?? raw['due_at'],
    };
  }
}
