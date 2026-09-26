class Routes {
  Routes._();

  static const String splash = '/';
  static const String login = '/login';
  static const String forgotPassword = '/forgot-password';

  // Student
  static const String studentDashboard = '/student/dashboard';
  static const String studentTimetable = '/student/timetable';
  static const String studentClassroom = '/student/classroom';
  static const String studentAttendance = '/student/attendance';
  static const String studentExam = '/student/exam';
  static const String studentMarks = '/student/marks';
  static const String studentFees = '/student/fees';
  static const String studentLibrary = '/student/library';
  static const String studentChat = '/student/chat';
  static const String studentProfile = '/student/profile';

  // Parent
  static const String parentDashboard = '/parent/dashboard';
  static const String parentMarks = '/parent/marks';
  static const String parentAttendance = '/parent/attendance';
  static const String parentFees = '/parent/fees';
  static const String parentChat = '/parent/chat';
  static const String parentProfile = '/parent/profile';

  // Teacher
  static const String teacherDashboard = '/teacher/dashboard';
  static const String teacherAttendance = '/teacher/attendance';
  static const String teacherClassroom = '/teacher/classroom';
  static const String teacherExam = '/teacher/exam';
  static const String teacherChat = '/teacher/chat';
  static const String teacherProfile = '/teacher/profile';

  // Admin
  static const String adminDashboard = '/admin/dashboard';
  static const String adminAdmissions = '/admin/admissions';
  static const String adminFees = '/admin/fees';
  static const String adminPayroll = '/admin/payroll';
  static const String adminNotice = '/admin/notice';
  static const String adminProfile = '/admin/profile';

  // Staff
  static const String staffDashboard = '/staff/dashboard';
  static const String staffProfile = '/staff/profile';

  // Common
  static const String notice = '/notice';
  static const String notifications = '/notifications';
  static const String chatConversation = '/chat/:conversationId';
  static const String hostel = '/hostel';
  static const String transport = '/transport';

  // ===== Phase 8-11 routes =====

  // Phase 8 — Student Lifecycle
  static const String ppdbRegister = '/ppdb/register';
  static const String parentBusTracking = '/parent/bus-tracking/:studentId';
  static const String parentClinicVisits = '/parent/clinic/:studentId';
  static const String wellnessCheckin = '/student/wellness';

  // Phase 9 — Teaching Tools
  static const String studyAssistant = '/student/ai-assistant';
  static const String hafalanInput = '/teacher/hafalan';
  static const String canteenMenu = '/student/canteen/:studentId';

  // Phase 10 — Engagement
  static const String parentDailyReport = '/parent/daily-report/:studentId';

  // Phase 11 already covered by web

  static String homeForRole(String role) {
    return switch (role) {
      'student' => studentDashboard,
      'parent' => parentDashboard,
      'teacher' => teacherDashboard,
      'admin' || 'school_admin' => adminDashboard,
      _ => staffDashboard,
    };
  }
}
