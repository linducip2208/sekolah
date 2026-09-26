import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../data/dashboard_repository.dart';

abstract class DashboardEvent extends Equatable {
  const DashboardEvent();
  @override
  List<Object?> get props => <Object?>[];
}

class DashboardLoadRequested extends DashboardEvent {
  const DashboardLoadRequested(this.role);
  final String role;
  @override
  List<Object?> get props => <Object?>[role];
}

class DashboardRefreshRequested extends DashboardEvent {
  const DashboardRefreshRequested(this.role);
  final String role;
  @override
  List<Object?> get props => <Object?>[role];
}

enum DashboardStatus { initial, loading, loaded, error }

class DashboardState extends Equatable {
  const DashboardState({
    this.status = DashboardStatus.initial,
    this.data,
    this.errorMessage,
  });

  final DashboardStatus status;
  final Map<String, dynamic>? data;
  final String? errorMessage;

  DashboardState copyWith({
    DashboardStatus? status,
    Map<String, dynamic>? data,
    String? errorMessage,
  }) =>
      DashboardState(
        status: status ?? this.status,
        data: data ?? this.data,
        errorMessage: errorMessage ?? this.errorMessage,
      );

  @override
  List<Object?> get props => <Object?>[status, data, errorMessage];
}

class DashboardBloc extends Bloc<DashboardEvent, DashboardState> {
  DashboardBloc(this._repo) : super(const DashboardState()) {
    on<DashboardLoadRequested>(_onLoad);
    on<DashboardRefreshRequested>(_onLoad);
  }

  final DashboardRepository _repo;

  Future<void> _onLoad(DashboardEvent e, Emitter<DashboardState> emit) async {
    final String role = (e as dynamic).role as String;
    emit(state.copyWith(status: DashboardStatus.loading));
    try {
      final Map<String, dynamic> data = await _repo.fetch(role);
      emit(state.copyWith(status: DashboardStatus.loaded, data: data));
    } catch (err) {
      emit(state.copyWith(
        status: DashboardStatus.error,
        errorMessage: err.toString(),
      ));
    }
  }
}
