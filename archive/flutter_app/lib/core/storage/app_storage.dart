import 'dart:convert';

import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:shared_preferences/shared_preferences.dart';

class AppStorage {
  AppStorage._();

  static const FlutterSecureStorage _secure = FlutterSecureStorage(
    aOptions: AndroidOptions(encryptedSharedPreferences: true),
    iOptions: IOSOptions(accessibility: KeychainAccessibility.first_unlock),
  );
  static SharedPreferences? _prefs;

  static const String _kToken = 'auth_token';
  static const String _kUser = 'auth_user';
  static const String _kSchool = 'auth_school';
  static const String _kFcmToken = 'fcm_token';
  static const String _kLocale = 'locale';
  static const String _kThemeMode = 'theme_mode';

  static Future<void> init() async {
    _prefs = await SharedPreferences.getInstance();
  }

  // ── Auth (secure)
  static Future<String?> getToken() => _secure.read(key: _kToken);
  static Future<void> saveToken(String token) =>
      _secure.write(key: _kToken, value: token);
  static Future<void> deleteToken() => _secure.delete(key: _kToken);

  static Future<Map<String, dynamic>?> getUser() async {
    final String? raw = await _secure.read(key: _kUser);
    return raw == null ? null : json.decode(raw) as Map<String, dynamic>;
  }

  static Future<void> saveUser(Map<String, dynamic> user) =>
      _secure.write(key: _kUser, value: json.encode(user));

  static Future<Map<String, dynamic>?> getSchool() async {
    final String? raw = await _secure.read(key: _kSchool);
    return raw == null ? null : json.decode(raw) as Map<String, dynamic>;
  }

  static Future<void> saveSchool(Map<String, dynamic> school) =>
      _secure.write(key: _kSchool, value: json.encode(school));

  static Future<void> clearAuth() async {
    await _secure.delete(key: _kToken);
    await _secure.delete(key: _kUser);
    await _secure.delete(key: _kSchool);
  }

  // ── Misc (prefs)
  static String? getLocale() => _prefs?.getString(_kLocale);
  static Future<void> setLocale(String code) async =>
      _prefs?.setString(_kLocale, code);

  static String? getThemeMode() => _prefs?.getString(_kThemeMode);
  static Future<void> setThemeMode(String mode) async =>
      _prefs?.setString(_kThemeMode, mode);

  static String? getFcmToken() => _prefs?.getString(_kFcmToken);
  static Future<void> saveFcmToken(String token) async =>
      _prefs?.setString(_kFcmToken, token);
}
