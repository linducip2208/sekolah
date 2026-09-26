import 'package:bloc_test/bloc_test.dart';
import 'package:eschool_app/features/dashboard/data/dashboard_repository.dart';
import 'package:eschool_app/features/dashboard/presentation/bloc/dashboard_bloc.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';

class _MockDashboardRepository extends Mock implements DashboardRepository {}

void main() {
  group('DashboardBloc', () {
    late _MockDashboardRepository repo;

    setUp(() => repo = _MockDashboardRepository());

    blocTest<DashboardBloc, DashboardState>(
      'load student dashboard → loading then loaded',
      setUp: () {
        when(() => repo.fetch('student')).thenAnswer((_) async =>
            <String, dynamic>{
              'class_name': '10A',
              'pending_tasks': 3,
              'attendance_pct': 95,
            });
      },
      build: () => DashboardBloc(repo),
      act: (DashboardBloc b) => b.add(const DashboardLoadRequested('student')),
      expect: () => <Matcher>[
        predicate<DashboardState>(
            (DashboardState s) => s.status == DashboardStatus.loading,
            'loading'),
        predicate<DashboardState>(
          (DashboardState s) =>
              s.status == DashboardStatus.loaded &&
              s.data?['class_name'] == '10A' &&
              s.data?['pending_tasks'] == 3,
          'loaded with data',
        ),
      ],
    );

    blocTest<DashboardBloc, DashboardState>(
      'fetch failure → error state',
      setUp: () {
        when(() => repo.fetch(any()))
            .thenThrow(Exception('connection refused'));
      },
      build: () => DashboardBloc(repo),
      act: (DashboardBloc b) => b.add(const DashboardLoadRequested('student')),
      expect: () => <Matcher>[
        predicate<DashboardState>(
            (DashboardState s) => s.status == DashboardStatus.loading,
            'loading'),
        predicate<DashboardState>(
          (DashboardState s) =>
              s.status == DashboardStatus.error &&
              (s.errorMessage ?? '').contains('connection refused'),
          'error with connection message',
        ),
      ],
    );

    blocTest<DashboardBloc, DashboardState>(
      'refresh re-fetches the same role',
      setUp: () {
        when(() => repo.fetch('teacher')).thenAnswer((_) async =>
            <String, dynamic>{'classes_today': 3});
      },
      build: () => DashboardBloc(repo),
      act: (DashboardBloc b) =>
          b.add(const DashboardRefreshRequested('teacher')),
      expect: () => <Matcher>[
        predicate<DashboardState>(
            (DashboardState s) => s.status == DashboardStatus.loading,
            'loading'),
        predicate<DashboardState>(
          (DashboardState s) =>
              s.status == DashboardStatus.loaded &&
              s.data?['classes_today'] == 3,
          'loaded teacher data',
        ),
      ],
      verify: (_) => verify(() => repo.fetch('teacher')).called(1),
    );
  });
}
