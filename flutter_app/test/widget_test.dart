// Smoke test entry. Real test coverage lives in:
//   test/features/auth/auth_bloc_test.dart
//   test/features/dashboard/dashboard_bloc_test.dart
//   test/widgets/login_page_test.dart
//   test/widgets/splash_redirect_test.dart
//   test/widgets/unwrap_test.dart
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  testWidgets('Smoke: MaterialApp boots', (WidgetTester tester) async {
    await tester.pumpWidget(
      const MaterialApp(
        home: Scaffold(body: Center(child: Text('eSchool'))),
      ),
    );
    expect(find.text('eSchool'), findsOneWidget);
  });
}
