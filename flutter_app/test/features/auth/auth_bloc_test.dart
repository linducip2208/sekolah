import 'package:bloc_test/bloc_test.dart';
import 'package:eschool_app/features/auth/data/auth_repository.dart';
import 'package:eschool_app/features/auth/presentation/bloc/auth_bloc.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';

import '../../helpers/fixtures.dart';

class _MockAuthRepository extends Mock implements AuthRepository {}

void main() {
  group('AuthBloc', () {
    late _MockAuthRepository repo;

    setUp(() => repo = _MockAuthRepository());

    blocTest<AuthBloc, AuthState>(
      'AuthBootRequested → authenticated when session restored',
      setUp: () {
        when(() => repo.restoreSession()).thenAnswer((_) async => testSession);
      },
      build: () => AuthBloc(repo),
      act: (AuthBloc b) => b.add(const AuthBootRequested()),
      expect: () => <Matcher>[
        predicate<AuthState>(
          (AuthState s) =>
              s.status == AuthStatus.authenticated &&
              s.user?.id == testUser.id &&
              s.school?.id == testSchool.id,
          'authenticated with restored user',
        ),
      ],
    );

    blocTest<AuthBloc, AuthState>(
      'AuthBootRequested → unauthenticated when no session',
      setUp: () {
        when(() => repo.restoreSession()).thenAnswer((_) async => null);
      },
      build: () => AuthBloc(repo),
      act: (AuthBloc b) => b.add(const AuthBootRequested()),
      expect: () => <Matcher>[
        predicate<AuthState>(
          (AuthState s) => s.status == AuthStatus.unauthenticated,
          'unauthenticated',
        ),
      ],
    );

    blocTest<AuthBloc, AuthState>(
      'AuthLoginRequested → loggingIn → authenticated on success',
      setUp: () {
        when(() => repo.login(
              email: any(named: 'email'),
              password: any(named: 'password'),
              schoolCode: any(named: 'schoolCode'),
            )).thenAnswer((_) async => testSession);
      },
      build: () => AuthBloc(repo),
      act: (AuthBloc b) => b.add(const AuthLoginRequested(
        email: 'budi@school.id',
        password: 'secret123',
      )),
      expect: () => <Matcher>[
        predicate<AuthState>(
            (AuthState s) => s.status == AuthStatus.loggingIn, 'loggingIn'),
        predicate<AuthState>(
          (AuthState s) => s.status == AuthStatus.authenticated,
          'authenticated',
        ),
      ],
    );

    blocTest<AuthBloc, AuthState>(
      'AuthLoginRequested → error when repository throws',
      setUp: () {
        when(() => repo.login(
              email: any(named: 'email'),
              password: any(named: 'password'),
              schoolCode: any(named: 'schoolCode'),
            )).thenThrow(Exception('Invalid credentials'));
      },
      build: () => AuthBloc(repo),
      act: (AuthBloc b) => b.add(const AuthLoginRequested(
        email: 'wrong@x.id',
        password: 'wrong',
      )),
      expect: () => <Matcher>[
        predicate<AuthState>(
            (AuthState s) => s.status == AuthStatus.loggingIn, 'loggingIn'),
        predicate<AuthState>(
          (AuthState s) =>
              s.status == AuthStatus.error && s.errorMessage != null,
          'error with message',
        ),
      ],
    );

    blocTest<AuthBloc, AuthState>(
      'AuthLogoutRequested → unauthenticated and clears state',
      setUp: () {
        when(() => repo.logout()).thenAnswer((_) async {});
      },
      build: () => AuthBloc(repo)
        ..emit(AuthState(
          status: AuthStatus.authenticated,
          user: testUser,
          school: testSchool,
        )),
      act: (AuthBloc b) => b.add(const AuthLogoutRequested()),
      expect: () => <Matcher>[
        predicate<AuthState>(
          (AuthState s) =>
              s.status == AuthStatus.unauthenticated &&
              s.user == null &&
              s.school == null,
          'logged out',
        ),
      ],
    );

    blocTest<AuthBloc, AuthState>(
      'AuthLocaleChanged updates user.locale',
      build: () => AuthBloc(repo)
        ..emit(AuthState(
          status: AuthStatus.authenticated,
          user: testUser,
          school: testSchool,
        )),
      act: (AuthBloc b) => b.add(const AuthLocaleChanged('en')),
      expect: () => <Matcher>[
        predicate<AuthState>(
          (AuthState s) => s.user?.locale == 'en',
          'locale switched to en',
        ),
      ],
    );
  });
}
