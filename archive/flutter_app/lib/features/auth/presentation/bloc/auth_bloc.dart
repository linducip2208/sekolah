import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../data/auth_repository.dart';
import '../../data/models/school_model.dart';
import '../../data/models/user_model.dart';

// ── Events
abstract class AuthEvent extends Equatable {
  const AuthEvent();
  @override
  List<Object?> get props => <Object?>[];
}

class AuthBootRequested extends AuthEvent {
  const AuthBootRequested();
}

class AuthLoginRequested extends AuthEvent {
  const AuthLoginRequested({
    required this.email,
    required this.password,
    this.schoolCode,
  });
  final String email;
  final String password;
  final String? schoolCode;

  @override
  List<Object?> get props => <Object?>[email, password, schoolCode];
}

class AuthLogoutRequested extends AuthEvent {
  const AuthLogoutRequested();
}

class AuthLocaleChanged extends AuthEvent {
  const AuthLocaleChanged(this.locale);
  final String locale;

  @override
  List<Object?> get props => <Object?>[locale];
}

// ── State
enum AuthStatus { unknown, authenticated, unauthenticated, loggingIn, error }

class AuthState extends Equatable {
  const AuthState({
    this.status = AuthStatus.unknown,
    this.user,
    this.school,
    this.errorMessage,
  });

  final AuthStatus status;
  final UserModel? user;
  final SchoolModel? school;
  final String? errorMessage;

  AuthState copyWith({
    AuthStatus? status,
    UserModel? user,
    SchoolModel? school,
    String? errorMessage,
    bool clearError = false,
  }) =>
      AuthState(
        status: status ?? this.status,
        user: user ?? this.user,
        school: school ?? this.school,
        errorMessage: clearError ? null : (errorMessage ?? this.errorMessage),
      );

  @override
  List<Object?> get props => <Object?>[status, user, school, errorMessage];
}

class AuthBloc extends Bloc<AuthEvent, AuthState> {
  AuthBloc(this._repo) : super(const AuthState()) {
    on<AuthBootRequested>(_onBoot);
    on<AuthLoginRequested>(_onLogin);
    on<AuthLogoutRequested>(_onLogout);
    on<AuthLocaleChanged>(_onLocale);
  }

  final AuthRepository _repo;

  Future<void> _onBoot(AuthBootRequested e, Emitter<AuthState> emit) async {
    final AuthSession? s = await _repo.restoreSession();
    if (s != null) {
      emit(state.copyWith(
        status: AuthStatus.authenticated,
        user: s.user,
        school: s.school,
        clearError: true,
      ));
    } else {
      emit(state.copyWith(status: AuthStatus.unauthenticated, clearError: true));
    }
  }

  Future<void> _onLogin(
      AuthLoginRequested e, Emitter<AuthState> emit) async {
    emit(state.copyWith(status: AuthStatus.loggingIn, clearError: true));
    try {
      final AuthSession s = await _repo.login(
        email: e.email,
        password: e.password,
        schoolCode: e.schoolCode,
      );
      emit(state.copyWith(
        status: AuthStatus.authenticated,
        user: s.user,
        school: s.school,
        clearError: true,
      ));
    } catch (err) {
      emit(state.copyWith(
        status: AuthStatus.error,
        errorMessage: err.toString(),
      ));
    }
  }

  Future<void> _onLogout(
      AuthLogoutRequested e, Emitter<AuthState> emit) async {
    await _repo.logout();
    emit(const AuthState(status: AuthStatus.unauthenticated));
  }

  void _onLocale(AuthLocaleChanged e, Emitter<AuthState> emit) {
    final UserModel? u = state.user;
    if (u != null) emit(state.copyWith(user: u.copyWith(locale: e.locale)));
  }
}
