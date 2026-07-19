import 'package:intl/intl.dart';

class CurrencyFormatter {
  CurrencyFormatter._();

  /// [amount] is integer in smallest unit (sen / cents).
  /// Returns 'Rp 1.250.000' style string.
  static String idr(int amount) {
    final NumberFormat fmt = NumberFormat.currency(
      locale: 'id_ID',
      symbol: 'Rp ',
      decimalDigits: 0,
    );
    return fmt.format(amount / 100);
  }

  static String compact(int amount) {
    final NumberFormat fmt = NumberFormat.compactCurrency(
      locale: 'id_ID',
      symbol: 'Rp ',
      decimalDigits: 1,
    );
    return fmt.format(amount / 100);
  }
}
