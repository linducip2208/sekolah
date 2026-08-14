<?php

use App\Http\Controllers\Api\Academic\AcademicYearController;
use App\Http\Controllers\Api\Academic\AttendanceController;
use App\Http\Controllers\Api\Academic\ClassroomController;
use App\Http\Controllers\Api\Academic\ExamController;
use App\Http\Controllers\Api\Academic\HolidayController;
use App\Http\Controllers\Api\Academic\MarksController;
use App\Http\Controllers\Api\Academic\TimetableController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Communication\ChatController;
use App\Http\Controllers\Api\Communication\NoticeController;
use App\Http\Controllers\Api\Communication\NotificationController;
use App\Http\Controllers\Api\Dashboard\DashboardController;
use App\Http\Controllers\Api\Facilities\HostelController;
use App\Http\Controllers\Api\Facilities\LibraryController;
use App\Http\Controllers\Api\Facilities\TransportController;
use App\Http\Controllers\Api\Finance\AdmissionController;
use App\Http\Controllers\Api\Finance\FeeController;
use App\Http\Controllers\Api\Finance\PayrollController;
use App\Http\Controllers\Api\Academic\ImportExportController;
use App\Http\Controllers\Api\Academic\ReportCardController;
use App\Http\Controllers\Api\Finance\PaymentGatewayController;
use App\Http\Controllers\Api\Finance\PaymentProviderController;
use App\Http\Controllers\Api\Finance\PaymentMethodController;
use App\Http\Controllers\Api\Branding\BrandingController;
use App\Http\Controllers\Api\PPDB\PpdbController;
use App\Http\Controllers\Api\Transport\VehicleTrackingController;
use App\Http\Controllers\Api\Gate\IdGateController;
use App\Http\Controllers\Api\Medical\ClinicController;
use App\Http\Controllers\Api\PortfolioController as ApiPortfolioController;
use App\Http\Controllers\Api\Counseling\CounselingController;
use App\Http\Controllers\Api\Discipline\DisciplineController;
use App\Http\Controllers\Api\LessonPlan\LessonPlanController;
use App\Http\Controllers\Api\Canteen\CanteenController;
use App\Http\Controllers\Api\Religious\ReligiousController;
use App\Http\Controllers\Api\AI\AiController;
use App\Http\Controllers\Api\LiveClass\LiveClassController;
use App\Http\Controllers\Api\QuestionBank\QuestionBankController;
use App\Http\Controllers\Api\Curriculum\CurriculumController;
use App\Http\Controllers\Api\Donation\DonationController;
use App\Http\Controllers\Api\Alumni\AlumniController;
use App\Http\Controllers\Api\Achievement\AchievementController;
use App\Http\Controllers\Api\Scholarship\ScholarshipController;
use App\Http\Controllers\Api\Career\CareerController;
use App\Http\Controllers\Api\Event\EventController;
use App\Http\Controllers\Api\DailyReport\DailyReportController;
use App\Http\Controllers\Api\Extracurricular\ExtracurricularController;
use App\Http\Controllers\Api\Dapodik\DapodikController;
use App\Http\Controllers\Api\Visitor\VisitorController;
use App\Http\Controllers\Api\VisitorScanController;
use App\Http\Controllers\Api\WaBotWebhookController;
use App\Http\Controllers\Api\Inventory\InventoryController;
use App\Http\Controllers\Api\CalendarController as ApiCalendarController;
use App\Http\Controllers\Api\Foundation\FoundationController;
use App\Http\Controllers\Api\Analytics\AnalyticsController;
use App\Http\Controllers\Api\ParentPortalController;
use App\Http\Controllers\Api\QrAttendanceController as ApiQrAttendanceController;
use App\Http\Controllers\Api\SchoolController;
use App\Http\Controllers\Api\SchoolRegistrationController;
use App\Http\Controllers\Api\SuperAdmin\SuperAnalyticsController;
use App\Http\Controllers\Api\SuperAdmin\SuperDashboardController;
use App\Http\Controllers\Api\SuperAdmin\SuperPlanController;
use App\Http\Controllers\Api\SuperAdmin\SuperSchoolController;
use App\Http\Controllers\Api\SuperAdmin\SuperSubscriptionController;
use App\Http\Controllers\Api\SuperAdmin\SuperSystemConfigController;
use App\Http\Controllers\Api\OfflineSyncController;
use Illuminate\Support\Facades\Route;

// Health check endpoints (public, no auth)
Route::prefix('v1')->group(function () {
    Route::get('/health',         [\App\Http\Controllers\Api\HealthController::class, 'shallow']);
    Route::get('/health/deep',    [\App\Http\Controllers\Api\HealthController::class, 'deep']);
    Route::get('/health/metrics', [\App\Http\Controllers\Api\HealthController::class, 'metrics']);
});

// Public auth routes
Route::prefix('v1')->middleware(['api'])->group(function () {
    Route::post('/auth/login',           [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('/auth/2fa/verify',      [AuthController::class, 'verifyTwoFactor'])->middleware('throttle:2fa');
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:password-reset');
    Route::post('/auth/reset-password',  [AuthController::class, 'resetPassword'])->middleware('throttle:password-reset');

    // School self-registration (public)
    Route::post('/schools/register', [SchoolRegistrationController::class, 'register']);

    // Public branding lookup (used by login page + Flutter app boot)
    Route::get('/branding/{subdomain}', [BrandingController::class, 'publicShow']);

    // Payment gateway webhooks (public, dynamic provider — signature verified internally per-provider)
    Route::post('/payments/webhook/{providerSlug}', [PaymentGatewayController::class, 'webhook']);

    // PPDB public registration
    Route::get('/public/ppdb/{subdomain}/periods',  [PpdbController::class, 'publicPeriods']);
    Route::post('/public/ppdb/{subdomain}/register',[PpdbController::class, 'publicRegister']);

    // Donation public campaigns
    Route::get('/public/donations/{subdomain}/campaigns',          [DonationController::class, 'publicCampaigns']);
    Route::get('/public/donations/{subdomain}/campaigns/{slug}',   [DonationController::class, 'publicShowCampaign']);
    Route::post('/public/donations/{subdomain}/campaigns/{slug}/donate', [DonationController::class, 'publicDonate']);

    // Alumni public directory
    Route::get('/public/alumni/{subdomain}', [AlumniController::class, 'publicDirectory']);

    // Event public list
    Route::get('/public/events/{subdomain}',         [EventController::class, 'publicList']);
    Route::get('/public/events/{subdomain}/{slug}',  [EventController::class, 'publicShow']);

    // Device-token-authenticated endpoints (vehicle GPS, gate scanners)
    Route::post('/devices/gps-ping',  [VehicleTrackingController::class, 'ping']);
    Route::post('/devices/gate-scan', [IdGateController::class, 'scan']);

    // WhatsApp Bot webhook (public, from ChatGo / WhatsApp gateway)
    Route::post('/webhook/wa-bot',    [WaBotWebhookController::class, '__invoke']);

    // Visitor QR scan at gate (can be from authenticated device)
    Route::post('/visitor/scan',      [VisitorScanController::class, 'scan']);
});

// Authenticated — no school check (works for all users including super_admin)
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    Route::post('/auth/logout',    [AuthController::class, 'logout']);
    Route::post('/auth/fcm-token', [AuthController::class, 'updateFcmToken']);

    // Device tokens (multi-device push)
    Route::post('/devices/register',   [\App\Http\Controllers\Api\DeviceTokenController::class, 'register']);
    Route::post('/devices/unregister', [\App\Http\Controllers\Api\DeviceTokenController::class, 'unregister']);
});

// Authenticated routes with school access check
Route::prefix('v1')->middleware(['auth:sanctum', 'school.access', 'subscription.active'])->group(function () {
    Route::get('/auth/me',              [AuthController::class, 'me']);
    Route::put('/auth/profile',         [AuthController::class, 'updateProfile']);
    Route::post('/auth/avatar',         [AuthController::class, 'updateAvatar']);
    Route::post('/auth/change-password',[AuthController::class, 'changePassword']);

    // Dashboard (per role aggregator)
    Route::get('/dashboard/student', [DashboardController::class, 'student']);
    Route::get('/dashboard/teacher', [DashboardController::class, 'teacher']);
    Route::get('/dashboard/parent',  [DashboardController::class, 'parent']);
    Route::get('/dashboard/admin',   [DashboardController::class, 'admin']);

    // Module 03 — School Setup
    Route::get('/school/profile',  [SchoolController::class, 'profile']);
    Route::put('/school/profile',  [SchoolController::class, 'updateProfile']);
    Route::post('/school/logo',    [SchoolController::class, 'uploadLogo']);
    Route::get('/school/settings', [SchoolController::class, 'settings']);
    Route::put('/school/settings', [SchoolController::class, 'updateSettings']);

    Route::apiResource('academic-years', AcademicYearController::class)->except(['show', 'destroy']);
    Route::post('/academic-years/{academicYear}/activate', [AcademicYearController::class, 'activate']);

    Route::get('/holidays',               [HolidayController::class, 'index']);
    Route::post('/holidays',              [HolidayController::class, 'store']);
    Route::delete('/holidays/{holiday}',  [HolidayController::class, 'destroy']);

    // Module 05 — Attendance
    Route::get('/attendance/me',                        [AttendanceController::class, 'mine']);
    Route::get('/attendance/class/{classSectionId}',    [AttendanceController::class, 'getByClass']);
    Route::post('/attendance/class/{classSectionId}',   [AttendanceController::class, 'bulkMark']);
    Route::put('/attendance/{attendance}',              [AttendanceController::class, 'update']);
    Route::get('/attendance/student/{studentId}',       [AttendanceController::class, 'getByStudent']);
    Route::get('/attendance/summary/{studentId}',       [AttendanceController::class, 'summary']);

    // QR Attendance
    Route::post('/qr/scan',                             [ApiQrAttendanceController::class, 'scan']);

    // Module 06 — Timetable
    Route::get('/timetable/class/{classSectionId}',     [TimetableController::class, 'byClass']);
    Route::get('/timetable/teacher/{teacherId}',        [TimetableController::class, 'byTeacher']);
    Route::get('/timetable/my',                         [TimetableController::class, 'mySchedule']);
    Route::get('/timetable/student/my',                 [TimetableController::class, 'studentSchedule']);
    Route::get('/timetable/check-conflict',             [TimetableController::class, 'checkConflict']);
    Route::get('/timetable/breaks',                     [TimetableController::class, 'breaks']);
    Route::post('/timetable/breaks',                    [TimetableController::class, 'storeBreak']);
    Route::post('/timetable/bulk',                      [TimetableController::class, 'bulk']);
    Route::post('/timetable',                           [TimetableController::class, 'store']);
    Route::put('/timetable/{timetableSlot}',            [TimetableController::class, 'update']);
    Route::delete('/timetable/{timetableSlot}',         [TimetableController::class, 'destroy']);

    // Module 07 — Online Classroom
    Route::get('/classroom/lessons',                              [ClassroomController::class, 'lessons']);
    Route::post('/classroom/lessons',                             [ClassroomController::class, 'storeLesson']);
    Route::put('/classroom/lessons/{lesson}',                     [ClassroomController::class, 'updateLesson']);
    Route::delete('/classroom/lessons/{lesson}',                  [ClassroomController::class, 'destroyLesson']);
    Route::post('/classroom/lessons/{lesson}/materials',          [ClassroomController::class, 'storeMaterial']);
    Route::delete('/classroom/materials/{materialId}',            [ClassroomController::class, 'destroyMaterial']);
    Route::get('/classroom/assignments',                          [ClassroomController::class, 'assignments']);
    Route::post('/classroom/assignments',                         [ClassroomController::class, 'storeAssignment']);
    Route::get('/classroom/assignments/{assignment}',             [ClassroomController::class, 'showAssignment']);
    Route::put('/classroom/assignments/{assignment}',             [ClassroomController::class, 'updateAssignment']);
    Route::delete('/classroom/assignments/{assignment}',          [ClassroomController::class, 'destroyAssignment']);
    Route::post('/classroom/assignments/{assignment}/submit',     [ClassroomController::class, 'submit']);
    Route::get('/classroom/assignments/{assignment}/submissions', [ClassroomController::class, 'submissions']);
    Route::post('/classroom/submissions/{submission}/grade',      [ClassroomController::class, 'grade']);

    // Module 08 — Exam Engine
    Route::get('/exams',                                    [ExamController::class, 'index']);
    Route::post('/exams',                                   [ExamController::class, 'store']);
    Route::put('/exams/{exam}',                             [ExamController::class, 'update']);
    Route::delete('/exams/{exam}',                          [ExamController::class, 'destroy']);
    Route::get('/exams/{exam}/questions',                   [ExamController::class, 'questions']);
    Route::post('/exams/{exam}/questions',                  [ExamController::class, 'storeQuestion']);
    Route::put('/exams/questions/{question}',               [ExamController::class, 'updateQuestion']);
    Route::delete('/exams/questions/{question}',            [ExamController::class, 'destroyQuestion']);
    Route::get('/exams/{exam}/start',                       [ExamController::class, 'start']);
    Route::post('/exams/{exam}/submit',                     [ExamController::class, 'submit']);
    Route::get('/exams/{exam}/result',                      [ExamController::class, 'result']);
    Route::get('/exams/{exam}/submissions',                 [ExamController::class, 'submissions']);

    // Module 09 — Marks & Grades
    Route::get('/marks/me',                            [MarksController::class, 'mine']);
    Route::get('/marks/student/{studentId}',           [MarksController::class, 'byStudent']);
    Route::post('/marks/bulk',                         [MarksController::class, 'bulk']);
    Route::put('/marks/{mark}',                        [MarksController::class, 'update']);
    Route::get('/grade-systems',                       [MarksController::class, 'gradeSystems']);
    Route::post('/grade-systems',                      [MarksController::class, 'storeGradeSystem']);
    Route::post('/report-cards/generate',              [MarksController::class, 'generateReportCards']);
    Route::get('/report-cards/student/{studentId}',    [MarksController::class, 'studentReportCard']);
    Route::post('/report-cards/{reportCard}/publish',  [MarksController::class, 'publishReportCard']);

    // Module 10 — Admission
    Route::get('/admission',                                [AdmissionController::class, 'index']);
    Route::post('/admission',                               [AdmissionController::class, 'store']);
    Route::put('/admission/{admissionEnquiry}',             [AdmissionController::class, 'update']);
    Route::post('/admission/{admissionEnquiry}/enroll',     [AdmissionController::class, 'enroll']);
    Route::get('/admission/stats',                          [AdmissionController::class, 'stats']);

    // Module 11 — Fee & Invoice
    Route::get('/fee/structures',                           [FeeController::class, 'structures']);
    Route::post('/fee/structures',                          [FeeController::class, 'storeStructure']);
    Route::get('/fee/invoices',                             [FeeController::class, 'invoices']);
    Route::get('/fee/invoices/me',                          [FeeController::class, 'myInvoices']);
    Route::post('/fee/generate-monthly',                    [FeeController::class, 'generateMonthly']);
    Route::post('/fee/invoices/{invoiceId}/pay',            [FeeController::class, 'recordPayment']);

    // Module 12 — Payroll
    Route::get('/payroll/structures',                       [PayrollController::class, 'structures']);
    Route::post('/payroll/structures',                      [PayrollController::class, 'storeStructure']);
    Route::post('/payroll/generate-slip',                   [PayrollController::class, 'generateSlip']);
    Route::get('/payroll/slips',                            [PayrollController::class, 'slips']);
    Route::post('/payroll/slips/{salarySlip}/mark-paid',    [PayrollController::class, 'markPaid']);

    // Module 14 — Library
    Route::get('/library/categories',                       [LibraryController::class, 'categories']);
    Route::post('/library/categories',                      [LibraryController::class, 'storeCategory']);
    Route::get('/library/books',                            [LibraryController::class, 'books']);
    Route::post('/library/books',                           [LibraryController::class, 'storeBook']);
    Route::put('/library/books/{book}',                     [LibraryController::class, 'updateBook']);
    Route::delete('/library/books/{book}',                  [LibraryController::class, 'destroyBook']);
    Route::post('/library/issue',                           [LibraryController::class, 'issue']);
    Route::post('/library/return/{issueId}',                [LibraryController::class, 'returnBook']);
    Route::get('/library/issues',                           [LibraryController::class, 'issues']);
    Route::post('/library/mark-overdue',                    [LibraryController::class, 'markOverdue']);

    // e-Library Digital — Reading Progress
    Route::post('/reading/progress',                         [\App\Http\Controllers\Api\ReadingProgressController::class, 'saveProgress']);
    Route::get('/reading/progress',                          [\App\Http\Controllers\Api\ReadingProgressController::class, 'getProgress']);

    // Module 15 — Hostel
    Route::get('/hostel',                                   [HostelController::class, 'index']);
    Route::post('/hostel',                                   [HostelController::class, 'store']);
    Route::post('/hostel/{hostel}/rooms',                   [HostelController::class, 'storeRoom']);
    Route::post('/hostel/allocate',                         [HostelController::class, 'allocate']);

    // Module 16 — Transport
    Route::get('/transport/routes',                         [TransportController::class, 'routes']);
    Route::post('/transport/routes',                        [TransportController::class, 'storeRoute']);
    Route::get('/transport/vehicles',                       [TransportController::class, 'vehicles']);
    Route::post('/transport/vehicles',                      [TransportController::class, 'storeVehicle']);
    Route::post('/transport/assign-student',                [TransportController::class, 'assignStudent']);

    // Parent Portal
    Route::get('/parent/children',                                     [ParentPortalController::class, 'children']);
    Route::get('/parent/children/{studentId}/attendance',              [ParentPortalController::class, 'childAttendance']);
    Route::get('/parent/children/{studentId}/marks',                   [ParentPortalController::class, 'childMarks']);
    Route::get('/parent/children/{studentId}/invoices',                [ParentPortalController::class, 'childInvoices']);

    // Import / Export
    Route::post('/import/students',                                    [ImportExportController::class, 'importStudents']);
    Route::get('/import/students/template',                            [ImportExportController::class, 'studentImportTemplate']);
    Route::get('/export/marks',                                        [ImportExportController::class, 'exportMarks']);
    Route::get('/export/fee-collection',                               [ImportExportController::class, 'exportFeeCollection']);

    // Report Card PDF
    Route::get('/report-cards/{reportCard}/pdf',                       [ReportCardController::class, 'downloadPdf']);
    Route::get('/report-cards/class/{classSectionId}/pdf/{semesterId}', [ReportCardController::class, 'downloadClassPdf']);

    // Branding (authenticated - own school)
    Route::get('/branding',                                            [BrandingController::class, 'showMine']);

    // Payment Gateway — Parent / Student (initiate, status, cancel)
    Route::get('/payments/methods',                                    [PaymentGatewayController::class, 'methods']);
    Route::post('/payments/initiate',                                  [PaymentGatewayController::class, 'initiate']);
    Route::get('/payments/{referenceNo}',                              [PaymentGatewayController::class, 'show']);
    Route::post('/payments/{referenceNo}/cancel',                      [PaymentGatewayController::class, 'cancel']);
    Route::get('/fee/invoices/{invoiceId}/payment-link',               [PaymentGatewayController::class, 'createPaymentLink']);

    // Payment Gateway — Admin (provider + method CRUD)
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::apiResource('/payment-providers', PaymentProviderController::class)
            ->only(['index', 'store', 'update', 'destroy']);
        Route::post('/payment-providers/{id}/test', [PaymentProviderController::class, 'test']);
        Route::get('/payment-providers/presets/list', [PaymentProviderController::class, 'listPresets']);
        Route::post('/payment-providers/presets/load', [PaymentProviderController::class, 'getPreset']);

        Route::apiResource('/payment-methods', PaymentMethodController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        // Branding admin
        Route::get('/branding',                  [BrandingController::class, 'showMine']);
        Route::put('/branding',                  [BrandingController::class, 'update']);
        Route::post('/branding/upload-logo',     [BrandingController::class, 'uploadLogo']);
        Route::delete('/branding/logo/{type}',   [BrandingController::class, 'removeLogo']);
        Route::post('/branding/reset',           [BrandingController::class, 'reset']);
    });

    // Module 17 — Notice Board
    Route::get('/notices',                                  [NoticeController::class, 'index']);
    Route::get('/notices/all',                              [NoticeController::class, 'all']);
    Route::post('/notices',                                 [NoticeController::class, 'store']);
    Route::put('/notices/{notice}',                         [NoticeController::class, 'update']);
    Route::delete('/notices/{notice}',                      [NoticeController::class, 'destroy']);

    // Module 18 — Chat
    Route::get('/chat/conversations',                       [ChatController::class, 'conversations']);
    Route::post('/chat/conversations',                      [ChatController::class, 'startConversation']);
    Route::get('/chat/conversations/{conversation}/messages', [ChatController::class, 'messages']);
    Route::post('/chat/conversations/{conversation}/send',  [ChatController::class, 'send']);

    // Module 19 — Notifications
    Route::get('/notifications',                            [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count',               [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read',                 [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all',                  [NotificationController::class, 'markAllRead']);

    // ============================================================
    // PHASE 8 — Student Lifecycle
    // ============================================================

    // Module 22 — PPDB
    Route::get('/ppdb/applications/me',           [PpdbController::class, 'myApplications']);
    Route::post('/ppdb/applications/{id}/submit', [PpdbController::class, 'submit']);
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/ppdb/applications',                  [PpdbController::class, 'adminIndex']);
        Route::post('/admin/ppdb/applications/{id}/verify',     [PpdbController::class, 'verify']);
        Route::post('/admin/ppdb/applications/{id}/accept',     [PpdbController::class, 'accept']);
        Route::post('/admin/ppdb/applications/{id}/reject',     [PpdbController::class, 'reject']);
        Route::post('/admin/ppdb/{periodId}/run-selection',     [PpdbController::class, 'runSelection']);
    });

    // Module 23 — Bus Tracking + ID Gate
    Route::get('/parent/children/{studentId}/bus-location', [VehicleTrackingController::class, 'busLocationForChild']);
    Route::get('/parent/children/{studentId}/gate-events',  [IdGateController::class, 'gateEventsForChild']);
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/transport/active-trips',         [VehicleTrackingController::class, 'activeTripsAdmin']);
        Route::get('/admin/transport/trips/{id}/track',     [VehicleTrackingController::class, 'trackTrip']);
        Route::post('/admin/students/{id}/id-card',         [IdGateController::class, 'issueCard']);
        Route::post('/admin/id-cards/{id}/rotate-qr',       [IdGateController::class, 'rotateQr']);
    });

    // Module 24 — UKS / Klinik
    Route::middleware('role:admin|nurse')->group(function () {
        Route::get('/medical/visits',                       [ClinicController::class, 'visits']);
        Route::post('/medical/visits',                      [ClinicController::class, 'storeVisit']);
        Route::get('/medical/students/{id}/record',         [ClinicController::class, 'record']);
        Route::put('/medical/students/{id}/record',         [ClinicController::class, 'updateRecord']);
        Route::get('/medical/students/{id}/visits',         [ClinicController::class, 'visitsByStudent']);
        Route::get('/medical/students/{id}/vaccinations',   [ClinicController::class, 'vaccinations']);
        Route::post('/medical/students/{id}/vaccinations',  [ClinicController::class, 'storeVaccination']);
    });

    // Module 25 — BP/BK + Discipline
    Route::get('/counseling/sessions',                  [CounselingController::class, 'sessions']);
    Route::post('/counseling/sessions',                 [CounselingController::class, 'scheduleSession']);
    Route::post('/counseling/sessions/{id}/complete',   [CounselingController::class, 'completeSession']);
    Route::get('/counseling/bullying-reports',          [CounselingController::class, 'bullyingReports']);
    Route::post('/counseling/bullying-reports',         [CounselingController::class, 'reportBullying']);
    Route::post('/counseling/bullying-reports/{id}/assign', [CounselingController::class, 'assignBullying']);
    Route::post('/counseling/bullying-reports/{id}/close',  [CounselingController::class, 'closeBullying']);
    Route::post('/wellness/checkin',                    [CounselingController::class, 'checkin']);
    Route::get('/wellness/at-risk',                     [CounselingController::class, 'atRiskStudents']);
    Route::get('/discipline/categories',                [DisciplineController::class, 'categories']);
    Route::post('/discipline/categories',               [DisciplineController::class, 'storeCategory']);
    Route::get('/discipline/records',                   [DisciplineController::class, 'records']);
    Route::post('/discipline/records',                  [DisciplineController::class, 'storeRecord']);
    Route::get('/discipline/students/{id}/summary',     [DisciplineController::class, 'summary']);
    Route::get('/discipline/leaderboard',               [DisciplineController::class, 'leaderboard']);

    // ============================================================
    // PHASE 9 — Teaching Tools
    // ============================================================

    // Module 26 — Lesson Plan
    Route::get('/lesson-plans',                        [LessonPlanController::class, 'index']);
    Route::post('/lesson-plans',                       [LessonPlanController::class, 'store']);
    Route::get('/lesson-plans/{id}',                   [LessonPlanController::class, 'show']);
    Route::put('/lesson-plans/{id}',                   [LessonPlanController::class, 'update']);
    Route::post('/lesson-plans/{id}/submit',           [LessonPlanController::class, 'submit']);
    Route::post('/lesson-plans/{id}/approve',          [LessonPlanController::class, 'approve']);
    Route::post('/lesson-plans/{id}/reject',           [LessonPlanController::class, 'reject']);
    Route::post('/lesson-plans/{id}/mark-executed',    [LessonPlanController::class, 'markExecuted']);
    Route::get('/lesson-plans/coverage/{semesterId}',  [LessonPlanController::class, 'coverage']);

    // Module 27 — Cafeteria
    Route::get('/canteen/menu',                        [CanteenController::class, 'menu']);
    Route::get('/canteen/wallet/{studentId}',          [CanteenController::class, 'wallet']);
    Route::post('/canteen/wallet/{studentId}/topup',   [CanteenController::class, 'topup']);
    Route::post('/canteen/orders',                     [CanteenController::class, 'placeOrder']);
    Route::get('/canteen/orders/today',                [CanteenController::class, 'ordersToday']);
    Route::put('/canteen/orders/{id}/status',          [CanteenController::class, 'updateStatus']);
    Route::put('/canteen/wallet/{walletId}/lock',      [CanteenController::class, 'lockWallet']);

    // Module 28 — Religious / Pesantren Mode
    Route::get('/religious/config',                    [ReligiousController::class, 'config']);
    Route::put('/religious/config',                    [ReligiousController::class, 'updateConfig']);
    Route::get('/religious/hafalan/targets',           [ReligiousController::class, 'targets']);
    Route::post('/religious/hafalan/targets',          [ReligiousController::class, 'storeTarget']);
    Route::post('/religious/hafalan',                  [ReligiousController::class, 'recordHafalan']);
    Route::get('/religious/hafalan/student/{id}',      [ReligiousController::class, 'hafalanSummary']);
    Route::post('/religious/ibadah',                   [ReligiousController::class, 'logIbadah']);
    Route::get('/religious/ibadah/student/{id}',       [ReligiousController::class, 'ibadahSummary']);

    // Module 31 — AI Assistant (Dynamic provider)
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/ai/providers',              [AiController::class, 'providers']);
        Route::post('/admin/ai/providers',             [AiController::class, 'storeProvider']);
        Route::put('/admin/ai/providers/{id}',         [AiController::class, 'updateProvider']);
        Route::delete('/admin/ai/providers/{id}',      [AiController::class, 'destroyProvider']);
        Route::get('/admin/ai/models',                 [AiController::class, 'models']);
        Route::post('/admin/ai/models',                [AiController::class, 'storeModel']);
        Route::get('/admin/ai/features',               [AiController::class, 'features']);
        Route::post('/admin/ai/features',              [AiController::class, 'assignFeature']);
        Route::get('/admin/ai/usage',                  [AiController::class, 'usage']);
    });
    Route::post('/ai/study-assistant',                 [AiController::class, 'studyAssistant']);
    Route::post('/ai/lesson-plan',                     [AiController::class, 'lessonPlanGenerator']);
    Route::post('/ai/essay-grade',                     [AiController::class, 'essayGrader']);

    // Module 35 — Live Class
    Route::get('/live-class/providers',                [LiveClassController::class, 'providers']);
    Route::post('/live-class/providers',               [LiveClassController::class, 'storeProvider']);
    Route::get('/live-class/sessions',                 [LiveClassController::class, 'sessions']);
    Route::post('/live-class/sessions',                [LiveClassController::class, 'schedule']);
    Route::post('/live-class/sessions/{id}/start',     [LiveClassController::class, 'start']);
    Route::post('/live-class/sessions/{id}/end',       [LiveClassController::class, 'end']);
    Route::post('/live-class/sessions/{id}/join',      [LiveClassController::class, 'join']);
    Route::post('/live-class/sessions/{id}/leave',     [LiveClassController::class, 'recordLeave']);

    // Module 36 — Question Bank
    Route::get('/question-bank/categories',            [QuestionBankController::class, 'categories']);
    Route::get('/question-bank/items',                 [QuestionBankController::class, 'items']);
    Route::post('/question-bank/items',                [QuestionBankController::class, 'store']);
    Route::post('/question-bank/generate-exam',        [QuestionBankController::class, 'generateExam']);

    // Module 40 — Curriculum Mapping
    Route::get('/curriculum/frameworks',               [CurriculumController::class, 'frameworks']);
    Route::post('/curriculum/frameworks',              [CurriculumController::class, 'storeFramework']);
    Route::get('/curriculum/competencies',             [CurriculumController::class, 'competencies']);
    Route::post('/curriculum/competencies',            [CurriculumController::class, 'storeCompetency']);
    Route::post('/curriculum/assessments',             [CurriculumController::class, 'recordAssessment']);
    Route::get('/curriculum/coverage',                 [CurriculumController::class, 'coverage']);

    // ============================================================
    // PHASE 10 — Engagement
    // ============================================================

    // Module 29 — Donations (admin)
    Route::middleware('role:admin|accountant')->group(function () {
        Route::get('/admin/donations/campaigns',       [DonationController::class, 'adminCampaigns']);
        Route::post('/admin/donations/campaigns',      [DonationController::class, 'storeCampaign']);
        Route::get('/admin/donations',                 [DonationController::class, 'donations']);
    });

    // Module 30 — Alumni
    Route::get('/alumni/profile',                      [AlumniController::class, 'profile']);
    Route::put('/alumni/profile',                      [AlumniController::class, 'updateProfile']);
    Route::middleware('role:admin')->group(function () {
        Route::post('/admin/alumni/{id}/verify',       [AlumniController::class, 'verify']);
    });

    // Module 37 — Achievement
    Route::get('/achievements/categories',                  [AchievementController::class, 'categories']);
    Route::post('/achievements/categories',                 [AchievementController::class, 'storeCategory']);
    Route::get('/achievements/students/{id}',               [AchievementController::class, 'studentAchievements']);
    Route::post('/achievements',                            [AchievementController::class, 'recordAchievement']);
    Route::post('/achievements/{id}/verify',                [AchievementController::class, 'verifyAchievement']);
    Route::get('/achievements/badges',                      [AchievementController::class, 'badges']);
    Route::get('/achievements/students/{id}/badges',        [AchievementController::class, 'studentBadges']);
    Route::get('/achievements/leaderboard',                 [AchievementController::class, 'leaderboard']);

    // Module 38 — Scholarship
    Route::get('/scholarship/programs',                     [ScholarshipController::class, 'programs']);
    Route::post('/scholarship/programs',                    [ScholarshipController::class, 'storeProgram']);
    Route::get('/scholarship/applications',                 [ScholarshipController::class, 'applications']);
    Route::post('/scholarship/applications',                [ScholarshipController::class, 'apply']);
    Route::post('/scholarship/applications/{id}/grant',     [ScholarshipController::class, 'grant']);
    Route::post('/scholarship/applications/{id}/apply-to-invoice', [ScholarshipController::class, 'applyToInvoice']);

    // Module 39 — Career Guidance
    Route::post('/career/assessments',                      [CareerController::class, 'recordAssessment']);
    Route::get('/career/assessments/student/{id}',          [CareerController::class, 'studentAssessments']);
    Route::get('/career/internships',                       [CareerController::class, 'internships']);
    Route::post('/career/internships',                      [CareerController::class, 'storeInternship']);
    Route::post('/career/internships/{id}/log-activity',    [CareerController::class, 'logDailyActivity']);

    // Module 42 — Event Management
    Route::get('/events',                                   [EventController::class, 'adminList']);
    Route::post('/events',                                  [EventController::class, 'store']);
    Route::post('/events/{id}/rsvp',                        [EventController::class, 'rsvp']);
    Route::post('/events/check-in',                         [EventController::class, 'checkIn']);
    Route::get('/events/{id}/rsvps',                        [EventController::class, 'rsvps']);

    // Module — Academic Calendar iCal Feed
    Route::get('/calendar/ical', [ApiCalendarController::class, 'ical']);

    // Module 43 — Daily Report
    Route::get('/parent/children/{studentId}/daily-reports', [DailyReportController::class, 'reportsForChild']);
    Route::middleware('role:admin')->group(function () {
        Route::post('/admin/daily-reports/generate',        [DailyReportController::class, 'generate']);
        Route::post('/admin/daily-reports/{id}/send',       [DailyReportController::class, 'send']);
    });

    // Module 44 — Extracurricular
    Route::get('/ekskul',                                   [ExtracurricularController::class, 'index']);
    Route::post('/ekskul',                                  [ExtracurricularController::class, 'store']);
    Route::post('/ekskul/{id}/enroll',                      [ExtracurricularController::class, 'enroll']);
    Route::post('/ekskul/{id}/attendance',                  [ExtracurricularController::class, 'markAttendance']);

    // ============================================================
    // PHASE 11 — Operations & Intelligence
    // ============================================================

    // Module 32 — Dapodik
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dapodik/config',                 [DapodikController::class, 'config']);
        Route::put('/admin/dapodik/config',                 [DapodikController::class, 'updateConfig']);
        Route::post('/admin/dapodik/import-students',       [DapodikController::class, 'importStudents']);
        Route::get('/admin/dapodik/export-students',        [DapodikController::class, 'exportStudents']);
    });

    // Module 33 — Visitor
    Route::middleware('role:admin|receptionist')->group(function () {
        Route::get('/visitors',                             [VisitorController::class, 'index']);
        Route::post('/visitors/check-in',                   [VisitorController::class, 'checkIn']);
        Route::post('/visitors/{id}/check-out',             [VisitorController::class, 'checkOut']);
    });

    // Module 34 — Inventory
    Route::get('/inventory/assets',                         [InventoryController::class, 'assets']);
    Route::post('/inventory/assets',                        [InventoryController::class, 'storeAsset']);
    Route::post('/inventory/loans',                         [InventoryController::class, 'requestLoan']);
    Route::post('/inventory/loans/{id}/approve',            [InventoryController::class, 'approveLoan']);
    Route::post('/inventory/loans/{id}/return',             [InventoryController::class, 'returnLoan']);
    Route::get('/inventory/maintenance',                    [InventoryController::class, 'maintenanceRequests']);
    Route::post('/inventory/maintenance',                   [InventoryController::class, 'reportMaintenance']);
    Route::post('/inventory/maintenance/{id}/resolve',      [InventoryController::class, 'resolveMaintenance']);

    // Module 41 — Yayasan / Foundation
    Route::get('/foundations/mine',                         [FoundationController::class, 'myFoundations']);
    Route::get('/foundations/{id}/dashboard',               [FoundationController::class, 'dashboard']);
    Route::get('/foundations/{foundationId}/schools/{schoolId}', [FoundationController::class, 'schoolDetail']);

    // Module 45 — Learning Analytics
    Route::middleware('role:admin|teacher')->group(function () {
        Route::post('/analytics/risk-scores/compute',       [AnalyticsController::class, 'compute']);
        Route::get('/analytics/risk-scores/student/{id}',   [AnalyticsController::class, 'studentRiskScore']);
        Route::get('/analytics/risk-scores/at-risk',        [AnalyticsController::class, 'topAtRisk']);
    });

    // Module 12 — Offline Sync
    Route::post('/sync/batch',                              [OfflineSyncController::class, 'batch'])->name('api.sync.batch');

    // Emergency — Panic Button
    Route::post('/emergency/panic',                         [\App\Http\Controllers\Api\EmergencyController::class, 'panic'])->name('api.emergency.panic');
    Route::get('/emergency/recent',                         [\App\Http\Controllers\Api\EmergencyController::class, 'recent'])->name('api.emergency.recent');
    Route::get('/emergency/contacts',                       [\App\Http\Controllers\Api\EmergencyController::class, 'contacts'])->name('api.emergency.contacts');
});

// Super Admin routes (bypass SchoolScope)
Route::prefix('v1/super')->middleware(['auth:sanctum', 'role:super_admin'])->group(function () {
    // Module 21 — SaaS Panel
    Route::get('/dashboard',                                    [SuperDashboardController::class, 'index']);

    Route::get('/schools',                                      [SuperSchoolController::class, 'index']);
    Route::post('/schools',                                     [SuperSchoolController::class, 'store']);
    Route::get('/schools/{id}',                                 [SuperSchoolController::class, 'show']);
    Route::put('/schools/{id}',                                 [SuperSchoolController::class, 'update']);
    Route::post('/schools/{id}/suspend',                        [SuperSchoolController::class, 'suspend']);
    Route::post('/schools/{id}/activate',                       [SuperSchoolController::class, 'activate']);
    Route::delete('/schools/{id}',                              [SuperSchoolController::class, 'destroy']);
    Route::get('/schools/{id}/stats',                           [SuperSchoolController::class, 'stats']);
    Route::get('/schools/{id}/activity-log',                    [SuperSchoolController::class, 'activityLog']);
    Route::post('/schools/{id}/subscription/extend',            [SuperSchoolController::class, 'extendSubscription']);
    Route::post('/schools/{id}/subscription/upgrade',           [SuperSchoolController::class, 'upgradeSubscription']);

    Route::get('/plans',                                        [SuperPlanController::class, 'index']);
    Route::post('/plans',                                       [SuperPlanController::class, 'store']);
    Route::put('/plans/{plan}',                                 [SuperPlanController::class, 'update']);

    Route::get('/subscriptions',                                [SuperSubscriptionController::class, 'index']);
    Route::post('/subscriptions',                               [SuperSubscriptionController::class, 'store']);

    Route::get('/analytics/revenue',                            [SuperAnalyticsController::class, 'revenue']);
    Route::get('/analytics/growth',                             [SuperAnalyticsController::class, 'growth']);

    Route::get('/system/config',                                [SuperSystemConfigController::class, 'show']);
    Route::put('/system/config',                                [SuperSystemConfigController::class, 'update']);
});

// ============================================================
// Public API — Tracer Study Alumni (no auth)
// ============================================================
Route::get('/tracer/form', [\App\Http\Controllers\Api\TracerController::class, 'showForm']);
Route::post('/tracer/submit', [\App\Http\Controllers\Api\TracerController::class, 'submit']);
