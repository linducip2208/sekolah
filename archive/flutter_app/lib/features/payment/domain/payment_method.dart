class PaymentMethod {
  final int id;
  final String code;
  final String displayName;
  final String? logoUrl;
  final int feeFlat;
  final int feePercentBp;
  final int feeBorneBy; // 0=parent, 1=school
  final String? instruction;
  final String apiFormat;
  final int expiryMinutes;

  PaymentMethod({
    required this.id,
    required this.code,
    required this.displayName,
    this.logoUrl,
    required this.feeFlat,
    required this.feePercentBp,
    required this.feeBorneBy,
    this.instruction,
    required this.apiFormat,
    required this.expiryMinutes,
  });

  factory PaymentMethod.fromJson(Map<String, dynamic> json) => PaymentMethod(
        id: json['id'] as int,
        code: json['code'] as String,
        displayName: json['display_name'] as String,
        logoUrl: json['logo_url'] as String?,
        feeFlat: (json['fee_flat'] ?? 0) as int,
        feePercentBp: (json['fee_percent_bp'] ?? 0) as int,
        feeBorneBy: (json['fee_borne_by'] ?? 0) as int,
        instruction: json['instruction'] as String?,
        apiFormat: json['api_format'] as String,
        expiryMinutes: (json['expiry_minutes'] ?? 1440) as int,
      );

  bool get feeBorneByParent => feeBorneBy == 0;
}

class PaymentTransaction {
  final int id;
  final String referenceNo;
  final String? externalId;
  final String status;
  final int amount;
  final int feeAmount;
  final int netAmount;
  final String currency;
  final String? redirectUrl;
  final String? vaNumber;
  final String? vaBankCode;
  final String? qrString;
  final String? deeplinkUrl;
  final DateTime? expiredAt;
  final DateTime? paidAt;
  final List<dynamic>? manualInstructions;
  final Map<String, dynamic>? invoice;
  final Map<String, dynamic>? method;

  PaymentTransaction({
    required this.id,
    required this.referenceNo,
    this.externalId,
    required this.status,
    required this.amount,
    required this.feeAmount,
    required this.netAmount,
    required this.currency,
    this.redirectUrl,
    this.vaNumber,
    this.vaBankCode,
    this.qrString,
    this.deeplinkUrl,
    this.expiredAt,
    this.paidAt,
    this.manualInstructions,
    this.invoice,
    this.method,
  });

  factory PaymentTransaction.fromJson(Map<String, dynamic> json) =>
      PaymentTransaction(
        id: json['id'] as int,
        referenceNo: json['reference_no'] as String,
        externalId: json['external_id'] as String?,
        status: json['status'] as String,
        amount: (json['amount'] ?? 0) as int,
        feeAmount: (json['fee_amount'] ?? 0) as int,
        netAmount: (json['net_amount'] ?? 0) as int,
        currency: json['currency'] as String? ?? 'IDR',
        redirectUrl: json['redirect_url'] as String?,
        vaNumber: json['va_number'] as String?,
        vaBankCode: json['va_bank_code'] as String?,
        qrString: json['qr_string'] as String?,
        deeplinkUrl: json['deeplink_url'] as String?,
        expiredAt: json['expired_at'] != null
            ? DateTime.tryParse(json['expired_at'].toString())
            : null,
        paidAt: json['paid_at'] != null
            ? DateTime.tryParse(json['paid_at'].toString())
            : null,
        manualInstructions: json['manual_instructions'] is List
            ? (json['manual_instructions'] as List)
            : null,
        invoice: json['invoice'] is Map<String, dynamic>
            ? json['invoice'] as Map<String, dynamic>
            : null,
        method: json['method'] is Map<String, dynamic>
            ? json['method'] as Map<String, dynamic>
            : null,
      );

  bool get isTerminal => const <String>{
        'paid',
        'expired',
        'failed',
        'cancelled',
        'refunded',
      }.contains(status);
}
