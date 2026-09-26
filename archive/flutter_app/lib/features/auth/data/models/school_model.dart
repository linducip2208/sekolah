import 'package:equatable/equatable.dart';

class SchoolModel extends Equatable {
  const SchoolModel({
    required this.id,
    required this.name,
    this.subdomain,
    this.logoUrl,
    this.timezone = 'Asia/Jakarta',
    this.locale = 'id',
  });

  final int id;
  final String name;
  final String? subdomain;
  final String? logoUrl;
  final String timezone;
  final String locale;

  /// Convenience: subdomain doubles as a "code" identifier.
  String? get code => subdomain;

  factory SchoolModel.fromJson(Map<String, dynamic> j) => SchoolModel(
        id: (j['id'] as num).toInt(),
        name: j['name'] as String? ?? '',
        subdomain: j['subdomain'] as String?,
        logoUrl: j['logo_url'] as String?,
        timezone: (j['timezone'] as String?) ?? 'Asia/Jakarta',
        locale: (j['locale'] as String?) ?? 'id',
      );

  Map<String, dynamic> toJson() => <String, dynamic>{
        'id': id,
        'name': name,
        'subdomain': subdomain,
        'logo_url': logoUrl,
        'timezone': timezone,
        'locale': locale,
      };

  @override
  List<Object?> get props =>
      <Object?>[id, name, subdomain, logoUrl, timezone, locale];
}
