import 'package:intl/intl.dart';
import 'package:timeago/timeago.dart' as timeago;

class DateFormatter {
  DateFormatter._();

  static String dayMonthYear(DateTime d) =>
      DateFormat('dd MMM yyyy', 'id_ID').format(d);

  static String fullDate(DateTime d) =>
      DateFormat('EEEE, dd MMMM yyyy', 'id_ID').format(d);

  static String time(DateTime d) => DateFormat('HH:mm').format(d);

  static String dateTime(DateTime d) =>
      DateFormat('dd MMM yyyy • HH:mm', 'id_ID').format(d);

  static String relative(DateTime d) {
    timeago.setLocaleMessages('id', timeago.IdMessages());
    return timeago.format(d, locale: 'id');
  }

  static String dayName(DateTime d) =>
      DateFormat('EEEE', 'id_ID').format(d);

  static String monthYear(DateTime d) =>
      DateFormat('MMMM yyyy', 'id_ID').format(d);
}
