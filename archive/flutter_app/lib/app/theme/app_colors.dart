import 'package:flutter/material.dart';

class AppColors {
  AppColors._();

  static const Color primary = Color(0xFF2563EB);
  static const Color primaryDark = Color(0xFF1D4ED8);
  static const Color secondary = Color(0xFF14B8A6);
  static const Color accent = Color(0xFFF59E0B);

  static const Color success = Color(0xFF16A34A);
  static const Color warning = Color(0xFFF59E0B);
  static const Color danger = Color(0xFFDC2626);
  static const Color info = Color(0xFF0EA5E9);

  static const Color bgLight = Color(0xFFF8FAFC);
  static const Color surfaceLight = Color(0xFFFFFFFF);
  static const Color borderLight = Color(0xFFE2E8F0);
  static const Color textPrimaryLight = Color(0xFF0F172A);
  static const Color textSecondaryLight = Color(0xFF475569);

  static const Color bgDark = Color(0xFF0B1220);
  static const Color surfaceDark = Color(0xFF111827);
  static const Color borderDark = Color(0xFF1F2937);
  static const Color textPrimaryDark = Color(0xFFE5E7EB);
  static const Color textSecondaryDark = Color(0xFF9CA3AF);

  static const Map<String, Color> roleAccent = <String, Color>{
    'student': Color(0xFF2563EB),
    'parent': Color(0xFF14B8A6),
    'teacher': Color(0xFF7C3AED),
    'admin': Color(0xFFDC2626),
    'super_admin': Color(0xFF0F172A),
    'staff': Color(0xFF0EA5E9),
    'librarian': Color(0xFFF59E0B),
    'accountant': Color(0xFF10B981),
  };
}
