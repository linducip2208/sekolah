import 'package:bloc_test/bloc_test.dart';
import 'package:eschool_app/core/api/api_client.dart';
import 'package:eschool_app/features/auth/data/auth_repository.dart';
import 'package:eschool_app/features/auth/presentation/bloc/auth_bloc.dart';
import 'package:eschool_app/features/auth/presentation/pages/login_page.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';

class _MockAuthBloc extends MockBloc<AuthEvent, AuthState> implements AuthBloc {}

class _MockAuthRepository extends Mock implements AuthRepository {}

class _FakeAuthEvent extends Fake implements AuthEvent {}

void main() {
  setUpAll(() {
    registerFallbackValue(_FakeAuthEvent());
    ApiClient.init();
  });

  Widget _wrap(Widget child, AuthBloc bloc, AuthRepository repo) {
    return MaterialApp(
      home: MultiRepositoryProvider(
        providers: <RepositoryProvider<Object>>[
          RepositoryProvider<AuthRepository>.value(value: repo),
        ],
        child: BlocProvider<AuthBloc>.value(value: bloc, child: child),
      ),
    );
  }

  testWidgets('LoginPage shows email + password fields and Masuk button',
      (WidgetTester tester) async {
    final _MockAuthBloc bloc = _MockAuthBloc();
    when(() => bloc.state).thenReturn(const AuthState(
      status: AuthStatus.unauthenticated,
    ));

    await tester.pumpWidget(_wrap(const LoginPage(), bloc, _MockAuthRepository()));

    expect(find.text('Email'), findsOneWidget);
    expect(find.text('Kata Sandi'), findsOneWidget);
    expect(find.text('Masuk'), findsOneWidget);
    expect(find.text('Lupa kata sandi?'), findsOneWidget);
  });

  testWidgets('Tapping Masuk with empty form triggers validation errors',
      (WidgetTester tester) async {
    final _MockAuthBloc bloc = _MockAuthBloc();
    when(() => bloc.state).thenReturn(const AuthState(
      status: AuthStatus.unauthenticated,
    ));

    await tester.pumpWidget(_wrap(const LoginPage(), bloc, _MockAuthRepository()));

    await tester.tap(find.text('Masuk'));
    await tester.pump();

    // Validators should produce at least one error message.
    expect(find.textContaining('wajib diisi'), findsWidgets);
  });

  testWidgets('Submitting valid form dispatches AuthLoginRequested',
      (WidgetTester tester) async {
    final _MockAuthBloc bloc = _MockAuthBloc();
    when(() => bloc.state).thenReturn(const AuthState(
      status: AuthStatus.unauthenticated,
    ));

    await tester.pumpWidget(_wrap(const LoginPage(), bloc, _MockAuthRepository()));

    await tester.enterText(find.byType(TextFormField).at(1), 'budi@school.id');
    await tester.enterText(find.byType(TextFormField).at(2), 'secret123');
    await tester.tap(find.text('Masuk'));
    await tester.pump();

    verify(() => bloc.add(any(that: isA<AuthLoginRequested>()))).called(1);
  });
}
