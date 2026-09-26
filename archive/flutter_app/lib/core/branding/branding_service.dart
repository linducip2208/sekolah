import 'dart:convert';

import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../api/api_client.dart';
import '../api/api_endpoints.dart';
import '../api/response_unwrap.dart';

class Branding {
  final String? displayName;
  final String? tagline;
  final Color colorPrimary;
  final Color colorSecondary;
  final Color colorSuccess;
  final Color colorWarning;
  final Color colorDanger;
  final String? logoPrimaryUrl;
  final String? logoSecondaryUrl;
  final String? faviconUrl;
  final String? splashLogoUrl;
  final Color splashBgColor;
  final String? mobileAppDisplayName;
  final int cacheVersion;

  Branding({
    this.displayName,
    this.tagline,
    required this.colorPrimary,
    required this.colorSecondary,
    required this.colorSuccess,
    required this.colorWarning,
    required this.colorDanger,
    this.logoPrimaryUrl,
    this.logoSecondaryUrl,
    this.faviconUrl,
    this.splashLogoUrl,
    required this.splashBgColor,
    this.mobileAppDisplayName,
    this.cacheVersion = 1,
  });

  static Color _hex(String? hex, [Color fallback = const Color(0xFF2563EB)]) {
    if (hex == null || hex.isEmpty) return fallback;
    var s = hex.replaceFirst('#', '');
    if (s.length == 6) s = 'FF$s';
    if (s.length == 8) {
      final int? value = int.tryParse(s, radix: 16);
      if (value != null) return Color(value);
    }
    return fallback;
  }

  factory Branding.fromJson(Map<String, dynamic> json) {
    final Map colors = (json['colors'] ?? <String, dynamic>{}) as Map;
    final Map logos = (json['logos'] ?? <String, dynamic>{}) as Map;
    final Map mobile = (json['mobile'] ?? <String, dynamic>{}) as Map;
    return Branding(
      displayName: json['display_name'] as String?,
      tagline: json['tagline'] as String?,
      colorPrimary: _hex(colors['primary']?.toString()),
      colorSecondary: _hex(colors['secondary']?.toString(), const Color(0xFF64748B)),
      colorSuccess: _hex(colors['success']?.toString(), const Color(0xFF16A34A)),
      colorWarning: _hex(colors['warning']?.toString(), const Color(0xFFEAB308)),
      colorDanger: _hex(colors['danger']?.toString(), const Color(0xFFDC2626)),
      logoPrimaryUrl: logos['primary'] as String?,
      logoSecondaryUrl: logos['secondary'] as String?,
      faviconUrl: logos['favicon'] as String?,
      splashLogoUrl: logos['splash_logo'] as String?,
      splashBgColor: _hex(mobile['splash_bg_color']?.toString(), Colors.white),
      mobileAppDisplayName: mobile['app_name'] as String?,
      cacheVersion: (json['cache_version'] ?? 1) as int,
    );
  }

  Map<String, dynamic> toJson() => <String, dynamic>{
        'display_name': displayName,
        'tagline': tagline,
        'colors': <String, String>{
          'primary': '#${colorPrimary.toARGB32().toRadixString(16).padLeft(8, '0').substring(2)}',
        },
        'cache_version': cacheVersion,
      };
}

class BrandingService {
  static const String _cacheKey = 'branding_cache';
  Branding? _current;

  Branding? get current => _current;

  Future<Branding> load(String subdomain) async {
    final SharedPreferences sp = await SharedPreferences.getInstance();
    final String? cached = sp.getString(_cacheKey);

    Branding? cachedBranding;
    if (cached != null) {
      try {
        final Map<String, dynamic> j = json.decode(cached) as Map<String, dynamic>;
        cachedBranding = Branding.fromJson(j);
        _current = cachedBranding;
      } catch (_) {}
    }

    try {
      final Response<dynamic> r =
          await ApiClient.dio.get<dynamic>(ApiEndpoints.brandingPublic(subdomain));
      final Map<String, dynamic> data = unwrapMap(r.data);
      _current = Branding.fromJson(data);
      await sp.setString(_cacheKey, json.encode(data));
      return _current!;
    } catch (_) {
      if (cachedBranding != null) return cachedBranding;
      rethrow;
    }
  }

  ThemeData buildTheme({Brightness brightness = Brightness.light}) {
    final Branding b = _current ?? Branding(
      colorPrimary: const Color(0xFF2563EB),
      colorSecondary: const Color(0xFF64748B),
      colorSuccess: const Color(0xFF16A34A),
      colorWarning: const Color(0xFFEAB308),
      colorDanger: const Color(0xFFDC2626),
      splashBgColor: Colors.white,
    );
    return ThemeData(
      useMaterial3: true,
      colorScheme: ColorScheme.fromSeed(
        seedColor: b.colorPrimary,
        brightness: brightness,
      ),
    );
  }
}
