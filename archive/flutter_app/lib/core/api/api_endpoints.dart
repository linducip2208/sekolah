class ApiEndpoints {
  ApiEndpoints._();

  // ── Auth
  static const String login = '/auth/login';
  static const String logout = '/auth/logout';
  static const String forgotPassword = '/auth/forgot-password';
  static const String resetPassword = '/auth/reset-password';
  static const String me = '/auth/me';
  static const String updateProfile = '/auth/profile';
  static const String updateAvatar = '/auth/avatar';
  static const String changePassword = '/auth/change-password';
  static const String registerFcmToken = '/auth/fcm-token';

  // ── School
  static const String schoolProfile = '/school/profile';
  static const String schoolSettings = '/school/settings';

  // ── Dashboard (aggregator per role)
  static const String studentDashboard = '/dashboard/student';
  static const String teacherDashboard = '/dashboard/teacher';
  static const String parentDashboard = '/dashboard/parent';
  static const String adminDashboard = '/dashboard/admin';

  // ── Academic Years / Holidays
  static const String academicYears = '/academic-years';
  static const String holidays = '/holidays';

  // ── Attendance
  static const String myAttendance = '/attendance/me';
  static String attendanceByClass(int sectionId) =>
      '/attendance/class/$sectionId';
  static String attendanceByStudent(int studentId) =>
      '/attendance/student/$studentId';
  static String attendanceSummary(int studentId) =>
      '/attendance/summary/$studentId';

  // ── Timetable
  static const String timetableMy = '/timetable/my';
  static const String timetableStudentMy = '/timetable/student/my';
  static String timetableByClass(int sectionId) =>
      '/timetable/class/$sectionId';
  static String timetableByTeacher(int teacherId) =>
      '/timetable/teacher/$teacherId';

  // ── Classroom
  static const String classroomLessons = '/classroom/lessons';
  static const String classroomAssignments = '/classroom/assignments';
  static String submitAssignment(int assignmentId) =>
      '/classroom/assignments/$assignmentId/submit';
  static String assignmentSubmissions(int assignmentId) =>
      '/classroom/assignments/$assignmentId/submissions';

  // ── Exam
  static const String exams = '/exams';
  static String examQuestions(int examId) => '/exams/$examId/questions';
  static String startExam(int examId) => '/exams/$examId/start';
  static String submitExam(int examId) => '/exams/$examId/submit';
  static String examResult(int examId) => '/exams/$examId/result';

  // ── Marks
  static const String myMarks = '/marks/me';
  static String marksByStudent(int studentId) => '/marks/student/$studentId';
  static String reportCardByStudent(int studentId) =>
      '/report-cards/student/$studentId';
  static String reportCardPdf(int reportCardId) =>
      '/report-cards/$reportCardId/pdf';

  // ── Admission
  static const String admission = '/admission';
  static const String admissionStats = '/admission/stats';

  // ── Fees
  static const String feeStructures = '/fee/structures';
  static const String feeInvoices = '/fee/invoices';
  static const String myFeeInvoices = '/fee/invoices/me';
  static String invoicePaymentLink(int invoiceId) =>
      '/fee/invoices/$invoiceId/payment-link';
  static String invoicePay(int invoiceId) =>
      '/fee/invoices/$invoiceId/pay';

  // ── Payroll
  static const String payrollSlips = '/payroll/slips';
  static const String payrollStructures = '/payroll/structures';

  // ── Library
  static const String libraryBooks = '/library/books';
  static const String libraryCategories = '/library/categories';
  static const String libraryIssues = '/library/issues';
  static const String libraryIssue = '/library/issue';
  static String libraryReturn(int issueId) => '/library/return/$issueId';

  // ── Hostel
  static const String hostels = '/hostel';
  static String hostelRooms(int hostelId) => '/hostel/$hostelId/rooms';
  static const String hostelAllocate = '/hostel/allocate';

  // ── Transport
  static const String transportRoutes = '/transport/routes';
  static const String transportVehicles = '/transport/vehicles';
  static const String transportAssign = '/transport/assign-student';

  // ── Notice
  static const String notices = '/notices';

  // ── Chat
  static const String conversations = '/chat/conversations';
  static String conversationMessages(int id) =>
      '/chat/conversations/$id/messages';
  static String sendMessage(int id) => '/chat/conversations/$id/send';

  // ── Notifications
  static const String notifications = '/notifications';
  static const String notificationsUnreadCount = '/notifications/unread-count';
  static String markNotificationRead(int id) => '/notifications/$id/read';
  static const String markAllNotificationsRead = '/notifications/read-all';

  // ── Parent Portal
  static const String parentChildren = '/parent/children';
  static String parentChildAttendance(int studentId) =>
      '/parent/children/$studentId/attendance';
  static String parentChildMarks(int studentId) =>
      '/parent/children/$studentId/marks';
  static String parentChildInvoices(int studentId) =>
      '/parent/children/$studentId/invoices';

  // ── Branding (school whitelabel)
  static String brandingPublic(String subdomain) => '/branding/$subdomain';
  static const String brandingMine = '/branding';

  // ── Payments (dynamic gateway)
  static const String paymentMethods = '/payments/methods';
  static const String paymentInitiate = '/payments/initiate';
  static String paymentShow(String referenceNo) => '/payments/$referenceNo';
  static String paymentCancel(String referenceNo) =>
      '/payments/$referenceNo/cancel';

  // ── PPDB (public + authenticated)
  static String ppdbPeriods(String subdomain) =>
      '/public/ppdb/$subdomain/periods';
  static String ppdbRegister(String subdomain) =>
      '/public/ppdb/$subdomain/register';
  static const String ppdbMyApplications = '/ppdb/applications/me';

  // ── Bus tracking + Gate
  static String childBusLocation(int studentId) =>
      '/parent/children/$studentId/bus-location';
  static String childGateEvents(int studentId) =>
      '/parent/children/$studentId/gate-events';

  // ── UKS / Medical (parent view)
  static String studentClinicVisits(int studentId) =>
      '/medical/students/$studentId/visits';
  static String studentVaccinations(int studentId) =>
      '/medical/students/$studentId/vaccinations';

  // ── Wellness checkin
  static const String wellnessCheckin = '/wellness/checkin';

  // ── Hafalan (Religious)
  static const String hafalanRecord = '/religious/hafalan';
  static String hafalanSummary(int studentId) =>
      '/religious/hafalan/student/$studentId';
  static const String ibadahLog = '/religious/ibadah';
  static String ibadahSummary(int studentId) =>
      '/religious/ibadah/student/$studentId';

  // ── Canteen
  static const String canteenMenu = '/canteen/menu';
  static String canteenWallet(int studentId) => '/canteen/wallet/$studentId';
  static String canteenTopup(int studentId) =>
      '/canteen/wallet/$studentId/topup';
  static const String canteenOrder = '/canteen/orders';

  // ── AI
  static const String aiStudyAssistant = '/ai/study-assistant';

  // ── Live class
  static String liveClassJoin(int sessionId) =>
      '/live-class/sessions/$sessionId/join';

  // ── Daily report
  static String childDailyReports(int studentId) =>
      '/parent/children/$studentId/daily-reports';

  // ── Events
  static String publicEventList(String subdomain) =>
      '/public/events/$subdomain';
  static String eventRsvp(int eventId) => '/events/$eventId/rsvp';

  // ── Branding
  static String brandingPublicSubdomain(String subdomain) =>
      '/branding/$subdomain';
}
