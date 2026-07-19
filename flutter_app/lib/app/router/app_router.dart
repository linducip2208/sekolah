import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../../features/auth/presentation/bloc/auth_bloc.dart';
import '../../features/auth/presentation/pages/forgot_password_page.dart';
import '../../features/auth/presentation/pages/login_page.dart';
import '../../features/auth/presentation/pages/splash_page.dart';
import '../../features/attendance/presentation/pages/student_attendance_page.dart';
import '../../features/attendance/presentation/pages/teacher_attendance_page.dart';
import '../../features/chat/presentation/pages/chat_conversation_page.dart';
import '../../features/chat/presentation/pages/chat_list_page.dart';
import '../../features/classroom/presentation/pages/classroom_page.dart';
import '../../features/dashboard/presentation/pages/admin_dashboard_page.dart';
import '../../features/dashboard/presentation/pages/parent_dashboard_page.dart';
import '../../features/dashboard/presentation/pages/staff_dashboard_page.dart';
import '../../features/dashboard/presentation/pages/student_dashboard_page.dart';
import '../../features/dashboard/presentation/pages/teacher_dashboard_page.dart';
import '../../features/exam/presentation/pages/exam_list_page.dart';
import '../../features/fees/presentation/pages/admin_fees_page.dart';
import '../../features/fees/presentation/pages/student_fees_page.dart';
import '../../features/hostel/presentation/pages/hostel_page.dart';
import '../../features/library/presentation/pages/library_page.dart';
import '../../features/transport/presentation/pages/transport_page.dart';
import '../../features/marks/presentation/pages/marks_page.dart';
import '../../features/notice/presentation/pages/admin_notice_page.dart';
import '../../features/notice/presentation/pages/notice_list_page.dart';
import '../../features/notifications/presentation/pages/notifications_page.dart';
import '../../features/profile/presentation/pages/profile_page.dart';
import '../../features/payroll/presentation/pages/payroll_page.dart';
import '../../features/admission/presentation/pages/admission_page.dart';
import '../../features/timetable/presentation/pages/timetable_page.dart';
// Phase 8-11
import '../../features/ppdb/presentation/pages/ppdb_register_page.dart';
import '../../features/bus_tracking/presentation/pages/bus_tracking_page.dart';
import '../../features/medical/presentation/pages/clinic_visits_page.dart';
import '../../features/counseling/presentation/pages/wellness_checkin_page.dart';
import '../../features/ai_assistant/presentation/pages/study_assistant_page.dart';
import '../../features/hafalan/presentation/pages/hafalan_input_page.dart';
import '../../features/canteen/presentation/pages/canteen_menu_page.dart';
import '../../features/daily_report/presentation/pages/daily_report_viewer_page.dart';
import '../../shells/admin_shell.dart';
import '../../shells/parent_shell.dart';
import '../../shells/staff_shell.dart';
import '../../shells/student_shell.dart';
import '../../shells/teacher_shell.dart';
import 'routes.dart';

class AppRouter {
  AppRouter._(this._authBloc) : _rootNavigatorKey = GlobalKey<NavigatorState>() {
    config = _build();
  }

  static AppRouter? _instance;
  static AppRouter of(BuildContext context) {
    _instance ??= AppRouter._(context.read<AuthBloc>());
    return _instance!;
  }

  static AppRouter? get maybeInstance => _instance;

  final AuthBloc _authBloc;
  final GlobalKey<NavigatorState> _rootNavigatorKey;
  late final GoRouter config;

  GoRouter get router => config;

  GoRouter _build() {
    return GoRouter(
      navigatorKey: _rootNavigatorKey,
      initialLocation: Routes.splash,
      refreshListenable: _AuthListenable(_authBloc),
      redirect: _redirect,
      routes: <RouteBase>[
        GoRoute(
          path: Routes.splash,
          builder: (_, __) => const SplashPage(),
        ),
        GoRoute(
          path: Routes.login,
          builder: (_, __) => const LoginPage(),
        ),
        GoRoute(
          path: Routes.forgotPassword,
          builder: (_, __) => const ForgotPasswordPage(),
        ),

        // ── Student
        ShellRoute(
          builder: (BuildContext c, GoRouterState s, Widget child) =>
              StudentShell(location: s.uri.path, child: child),
          routes: <RouteBase>[
            GoRoute(path: Routes.studentDashboard, builder: (_, __) => const StudentDashboardPage()),
            GoRoute(path: Routes.studentTimetable, builder: (_, __) => const TimetablePage()),
            GoRoute(path: Routes.studentClassroom, builder: (_, __) => const ClassroomPage()),
            GoRoute(path: Routes.studentAttendance, builder: (_, __) => const StudentAttendancePage()),
            GoRoute(path: Routes.studentExam, builder: (_, __) => const ExamListPage()),
            GoRoute(path: Routes.studentMarks, builder: (_, __) => const MarksPage()),
            GoRoute(path: Routes.studentFees, builder: (_, __) => const StudentFeesPage()),
            GoRoute(path: Routes.studentLibrary, builder: (_, __) => const LibraryPage()),
            GoRoute(path: Routes.studentChat, builder: (_, __) => const ChatListPage()),
            GoRoute(path: Routes.studentProfile, builder: (_, __) => const ProfilePage()),
          ],
        ),

        // ── Parent
        ShellRoute(
          builder: (BuildContext c, GoRouterState s, Widget child) =>
              ParentShell(location: s.uri.path, child: child),
          routes: <RouteBase>[
            GoRoute(path: Routes.parentDashboard, builder: (_, __) => const ParentDashboardPage()),
            GoRoute(path: Routes.parentMarks, builder: (_, __) => const MarksPage()),
            GoRoute(path: Routes.parentAttendance, builder: (_, __) => const StudentAttendancePage()),
            GoRoute(path: Routes.parentFees, builder: (_, __) => const StudentFeesPage()),
            GoRoute(path: Routes.parentChat, builder: (_, __) => const ChatListPage()),
            GoRoute(path: Routes.parentProfile, builder: (_, __) => const ProfilePage()),
          ],
        ),

        // ── Teacher
        ShellRoute(
          builder: (BuildContext c, GoRouterState s, Widget child) =>
              TeacherShell(location: s.uri.path, child: child),
          routes: <RouteBase>[
            GoRoute(path: Routes.teacherDashboard, builder: (_, __) => const TeacherDashboardPage()),
            GoRoute(path: Routes.teacherAttendance, builder: (_, __) => const TeacherAttendancePage()),
            GoRoute(path: Routes.teacherClassroom, builder: (_, __) => const ClassroomPage()),
            GoRoute(path: Routes.teacherExam, builder: (_, __) => const ExamListPage()),
            GoRoute(path: Routes.teacherChat, builder: (_, __) => const ChatListPage()),
            GoRoute(path: Routes.teacherProfile, builder: (_, __) => const ProfilePage()),
          ],
        ),

        // ── Admin
        ShellRoute(
          builder: (BuildContext c, GoRouterState s, Widget child) =>
              AdminShell(location: s.uri.path, child: child),
          routes: <RouteBase>[
            GoRoute(path: Routes.adminDashboard, builder: (_, __) => const AdminDashboardPage()),
            GoRoute(path: Routes.adminAdmissions, builder: (_, __) => const AdmissionPage()),
            GoRoute(path: Routes.adminFees, builder: (_, __) => const AdminFeesPage()),
            GoRoute(path: Routes.adminPayroll, builder: (_, __) => const PayrollPage()),
            GoRoute(path: Routes.adminNotice, builder: (_, __) => const AdminNoticePage()),
            GoRoute(path: Routes.adminProfile, builder: (_, __) => const ProfilePage()),
          ],
        ),

        // ── Staff
        ShellRoute(
          builder: (BuildContext c, GoRouterState s, Widget child) =>
              StaffShell(location: s.uri.path, child: child),
          routes: <RouteBase>[
            GoRoute(path: Routes.staffDashboard, builder: (_, __) => const StaffDashboardPage()),
            GoRoute(path: Routes.staffProfile, builder: (_, __) => const ProfilePage()),
          ],
        ),

        // ── Common
        GoRoute(path: Routes.notice, builder: (_, __) => const NoticeListPage()),
        GoRoute(path: Routes.notifications, builder: (_, __) => const NotificationsPage()),
        GoRoute(
          path: Routes.chatConversation,
          builder: (BuildContext c, GoRouterState s) => ChatConversationPage(
            conversationId: int.parse(s.pathParameters['conversationId']!),
          ),
        ),
        GoRoute(path: Routes.hostel, builder: (_, __) => const HostelPage()),
        GoRoute(path: Routes.transport, builder: (_, __) => const TransportPage()),

        // ===== Phase 8 — Student Lifecycle =====
        GoRoute(
          path: Routes.ppdbRegister,
          builder: (_, GoRouterState s) {
            final String subdomain = s.uri.queryParameters['subdomain'] ?? 'demo';
            return PpdbRegisterPage(subdomain: subdomain);
          },
        ),
        GoRoute(
          path: Routes.parentBusTracking,
          builder: (_, GoRouterState s) => BusTrackingPage(
            studentId: int.parse(s.pathParameters['studentId']!),
            studentName: s.uri.queryParameters['name'] ?? 'Anak',
          ),
        ),
        GoRoute(
          path: Routes.parentClinicVisits,
          builder: (_, GoRouterState s) => ClinicVisitsPage(
            studentId: int.parse(s.pathParameters['studentId']!),
            studentName: s.uri.queryParameters['name'] ?? 'Anak',
          ),
        ),
        GoRoute(
          path: Routes.wellnessCheckin,
          builder: (_, GoRouterState s) {
            final int? studentId = int.tryParse(s.uri.queryParameters['student_id'] ?? '');
            return WellnessCheckinPage(studentId: studentId ?? 0);
          },
        ),

        // ===== Phase 9 — Teaching Tools =====
        GoRoute(path: Routes.studyAssistant, builder: (_, __) => const StudyAssistantPage()),
        GoRoute(
          path: Routes.hafalanInput,
          builder: (_, GoRouterState s) {
            final int? studentId = int.tryParse(s.uri.queryParameters['student_id'] ?? '');
            return HafalanInputPage(studentId: studentId ?? 0);
          },
        ),
        GoRoute(
          path: Routes.canteenMenu,
          builder: (_, GoRouterState s) => CanteenMenuPage(
            studentId: int.parse(s.pathParameters['studentId']!),
          ),
        ),

        // ===== Phase 10 — Engagement =====
        GoRoute(
          path: Routes.parentDailyReport,
          builder: (_, GoRouterState s) => DailyReportViewerPage(
            studentId: int.parse(s.pathParameters['studentId']!),
            studentName: s.uri.queryParameters['name'] ?? 'Anak',
          ),
        ),
      ],
      errorBuilder: (BuildContext c, GoRouterState s) => Scaffold(
        body: Center(
          child: Padding(
            padding: const EdgeInsets.all(24),
            child: Text(
              'Halaman tidak ditemukan:\n${s.uri}',
              textAlign: TextAlign.center,
            ),
          ),
        ),
      ),
    );
  }

  String? _redirect(BuildContext context, GoRouterState state) {
    final AuthState s = _authBloc.state;
    final String loc = state.matchedLocation;

    if (s.status == AuthStatus.unknown) {
      return loc == Routes.splash ? null : Routes.splash;
    }
    final bool authed = s.status == AuthStatus.authenticated && s.user != null;
    final bool isPublic =
        loc == Routes.login || loc == Routes.forgotPassword || loc == Routes.splash;

    if (!authed && !isPublic) return Routes.login;
    if (authed && isPublic) return Routes.homeForRole(s.user!.role);
    return null;
  }
}

class _AuthListenable extends ChangeNotifier {
  _AuthListenable(AuthBloc bloc) {
    _sub = bloc.stream.listen((_) => notifyListeners());
  }
  late final Object _sub;

  @override
  void dispose() {
    (_sub as dynamic).cancel();
    super.dispose();
  }
}
