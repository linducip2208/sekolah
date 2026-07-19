import 'package:equatable/equatable.dart';

class UserModel extends Equatable {
  const UserModel({
    required this.id,
    required this.schoolId,
    required this.name,
    required this.email,
    required this.role,
    this.phone,
    this.avatarUrl,
    this.locale = 'id',
    this.studentIds = const <int>[],
  });

  final int id;
  final int schoolId;
  final String name;
  final String email;
  final String role;
  final String? phone;
  final String? avatarUrl;
  final String locale;
  final List<int> studentIds;

  factory UserModel.fromJson(Map<String, dynamic> j) => UserModel(
        id: (j['id'] as num).toInt(),
        schoolId: (j['school_id'] as num?)?.toInt() ?? 0,
        name: j['name'] as String? ?? '',
        email: j['email'] as String? ?? '',
        role: (j['role'] ?? j['role_name'] ?? 'staff') as String,
        phone: j['phone'] as String?,
        avatarUrl: j['avatar_url'] as String?,
        locale: (j['locale'] as String?) ?? 'id',
        studentIds: (j['student_ids'] as List<dynamic>?)
                ?.map((dynamic e) => (e as num).toInt())
                .toList() ??
            const <int>[],
      );

  Map<String, dynamic> toJson() => <String, dynamic>{
        'id': id,
        'school_id': schoolId,
        'name': name,
        'email': email,
        'role': role,
        'phone': phone,
        'avatar_url': avatarUrl,
        'locale': locale,
        'student_ids': studentIds,
      };

  UserModel copyWith({String? locale, String? avatarUrl, String? phone}) =>
      UserModel(
        id: id,
        schoolId: schoolId,
        name: name,
        email: email,
        role: role,
        phone: phone ?? this.phone,
        avatarUrl: avatarUrl ?? this.avatarUrl,
        locale: locale ?? this.locale,
        studentIds: studentIds,
      );

  @override
  List<Object?> get props =>
      <Object?>[id, schoolId, name, email, role, phone, avatarUrl, locale];
}
