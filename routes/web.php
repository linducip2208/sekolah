<?php

use App\Http\Controllers\Web\Admin\Academic\AcademicWebController;
use App\Http\Controllers\Web\Admin\Academic\AccreditationController;
use App\Http\Controllers\Web\Admin\Academic\AssignmentController;
use App\Http\Controllers\Web\Admin\Academic\AttendanceWebController;
use App\Http\Controllers\Web\Admin\Academic\CalendarController;
use App\Http\Controllers\Web\Admin\Academic\ClassroomExtrasController;
use App\Http\Controllers\Web\Admin\Academic\LeaderboardController;
use App\Http\Controllers\Web\Admin\Alumni\JobBoardController;
use App\Http\Controllers\Web\Admin\Alumni\TracerController as AdminTracerController;
use App\Http\Controllers\Web\Admin\Alumni\BkkController;
use App\Http\Controllers\Web\Admin\Academic\AdiwiyataController;
use App\Http\Controllers\Web\Admin\Inventory\AdvancedAssetController;
use App\Http\Controllers\Web\Admin\Finance\CooperativeController;
use App\Http\Controllers\Web\Student\BkkStudentController;
use App\Http\Controllers\Web\Admin\Academic\EssayGradingController;
use App\Http\Controllers\Web\Admin\Academic\LessonPlanController;
use App\Http\Controllers\Web\Admin\Academic\PkgController;
use App\Http\Controllers\Web\Admin\Analytics\DropoutRiskController;
use App\Http\Controllers\Web\AlumniJobController;
use App\Http\Controllers\Web\Admin\Communication\LetterController;
use App\Http\Controllers\Web\Admin\Academic\ExamWebController;
use App\Http\Controllers\Web\Admin\Academic\StaffWebController;
use App\Http\Controllers\Web\Admin\Academic\StudentWebController;
use App\Http\Controllers\Web\Admin\Academic\TimetableWebController;
use App\Http\Controllers\Web\Admin\Branding\BrandingWebController;
use App\Http\Controllers\Web\Admin\Communication\ChatNotificationController;
use App\Http\Controllers\Web\Admin\Communication\NoticeWebController;
use App\Http\Controllers\Web\Admin\Academic\LessonStudyController;
use App\Http\Controllers\Web\Admin\Academic\TrainingController;
use App\Http\Controllers\Web\Admin\DashboardTvController;
use App\Http\Controllers\Web\Admin\DigitalSignageController;
use App\Http\Controllers\Web\Admin\Facilities\HostelWebController;
use App\Http\Controllers\Web\Admin\Facilities\RoomBookingController as AdminRoomBookingController;
use App\Http\Controllers\Web\Admin\Finance\BudgetController;
use App\Http\Controllers\Web\Admin\Finance\AccountingController;
use App\Http\Controllers\Web\Admin\Library\DigitalLibraryController;
use App\Http\Controllers\Web\ReaderController;
use App\Http\Controllers\Web\Admin\Finance\FeeWebController;
use App\Http\Controllers\Web\Admin\Finance\FinanceReportController;
use App\Http\Controllers\Web\Admin\Finance\PayrollWebController;
use App\Http\Controllers\Web\Admin\Library\LibraryWebController;
use App\Http\Controllers\Web\Admin\Misc\MiscCrudController;
use App\Http\Controllers\Web\Admin\Operations\OperationsController;
use App\Http\Controllers\Web\Admin\Payment\PaymentMethodWebController;
use App\Http\Controllers\Web\Admin\Payment\PaymentProviderWebController;
use App\Http\Controllers\Web\Admin\Phase8\Phase8CrudController;
use App\Http\Controllers\Web\Admin\Reports\AdvancedReportsController;
use App\Http\Controllers\Web\Admin\Reports\ReportBuilderController;
use App\Http\Controllers\Web\Admin\Foundation\FoundationBenchmarkController;
use App\Http\Controllers\Web\SuperAdmin\BenchmarkController;
use App\Http\Controllers\Web\Admin\Import\BulkImportController;
use App\Http\Controllers\Web\Admin\Print\PrintController;
use App\Http\Controllers\Web\Admin\Phase8\Phase8WebController;
use App\Http\Controllers\Web\Admin\Phase9\Phase9CrudController;
use App\Http\Controllers\Web\Admin\AI\AiDataChatController;
use App\Http\Controllers\Web\Admin\Lms\CourseController;
use App\Http\Controllers\Web\Admin\Phase9\Phase9WebController;
use App\Http\Controllers\Web\Admin\Phase10\Phase10CrudController;
use App\Http\Controllers\Web\Admin\Phase10\Phase10WebController;
use App\Http\Controllers\Web\Admin\Phase11\Phase11CrudController;
use App\Http\Controllers\Web\Admin\Phase11\Phase11WebController;
use App\Http\Controllers\Web\DocsController;
use App\Http\Controllers\Web\Parent\ParentPaymentController;
use App\Http\Controllers\Web\SEO\PseoController;
use App\Http\Controllers\Web\Public\SubscriptionController as PublicSubscriptionController;
use App\Http\Controllers\Web\SuperAdmin\DashboardController;
use App\Http\Controllers\Web\SuperAdmin\PlatformBillingController;
use App\Http\Controllers\Web\SuperAdmin\PlatformPanelController;
use App\Http\Controllers\Web\SuperAdmin\PlatformWhitelabelController;
use App\Http\Controllers\Web\SuperAdmin\SuperExtrasController;
use App\Http\Controllers\Web\Admin\Academic\InteractiveRaportController;
use App\Http\Controllers\Web\Admin\Academic\PortfolioController;
use App\Http\Controllers\Web\Admin\Communication\ConferenceController;
use App\Http\Controllers\Web\Admin\Communication\ReminderController;
use App\Http\Controllers\Web\Admin\Communication\SurveyController;
use App\Http\Controllers\Web\Admin\Communication\WaBotController;
use App\Http\Controllers\Web\Admin\Visitor\PreRegistrationController;
use App\Http\Controllers\Web\VisitorRegistrationController;
use App\Http\Controllers\Web\Admin\Academic\QrAttendanceController as WebQrAttendanceController;
use App\Http\Controllers\Web\ForumController as PublicForumController;
use App\Http\Controllers\Web\Admin\Workflow\WorkflowController;
use App\Http\Controllers\Web\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\Web\LandingController::class, 'index'])->name('home');

// Public Digital Signage display (no auth)
Route::get('/signage/{school_id}', [\App\Http\Controllers\Web\Admin\DigitalSignageController::class, 'display'])
    ->where('school_id', '[0-9]+')
    ->name('signage.display');

// Public Dashboard TV (no auth)
Route::get('/signage/{school_id}/tv', [\App\Http\Controllers\Web\Admin\DashboardTvController::class, 'display'])
    ->where('school_id', '[0-9]+')
    ->name('signage.dashboard-tv');

// Public e-Library Reader
Route::get('/baca/{token}', [ReaderController::class, 'view'])->name('reader.view');
Route::get('/baca/{token}/file', [ReaderController::class, 'serve'])->name('reader.serve');

require base_path('routes/pair-routes.php');

// Per-school whitelabel CSS (loaded by layouts)
Route::get('/branding/{schoolId}/theme.css', [\App\Http\Controllers\BrandingCssController::class, 'css'])
    ->where('schoolId', '[0-9]+')
    ->name('branding.css');

// ============================================================
// 2FA (TOTP)
// ============================================================
Route::middleware(['web'])->group(function () {
    Route::get('/2fa/challenge',  [\App\Http\Controllers\Web\Auth\TwoFactorController::class, 'showChallenge'])->name('2fa.challenge');
    Route::post('/2fa/challenge', [\App\Http\Controllers\Web\Auth\TwoFactorController::class, 'verifyChallenge'])
        ->middleware('throttle:2fa')->name('2fa.challenge.verify');

    Route::middleware('auth')->group(function () {
        Route::get('/2fa/enable',          [\App\Http\Controllers\Web\Auth\TwoFactorController::class, 'showEnable'])->name('2fa.enable');
        Route::post('/2fa/enable',         [\App\Http\Controllers\Web\Auth\TwoFactorController::class, 'confirm'])->name('2fa.confirm');
        Route::post('/2fa/disable',        [\App\Http\Controllers\Web\Auth\TwoFactorController::class, 'disable'])->name('2fa.disable');
        Route::post('/2fa/regenerate',     [\App\Http\Controllers\Web\Auth\TwoFactorController::class, 'regenerateRecovery'])->name('2fa.regenerate');
    });
});

// ============================================================
// Public Subscription / Pricing
// ============================================================
Route::get('/pricing',                                          [PublicSubscriptionController::class, 'pricing'])->name('public.pricing');
Route::get('/daftar',                                           [PublicSubscriptionController::class, 'register'])->name('public.subscription.register');
Route::post('/daftar',                                          [PublicSubscriptionController::class, 'submit'])->name('public.subscription.submit');
Route::get('/daftar/{registration}/pembayaran',                 [PublicSubscriptionController::class, 'payment'])->name('public.subscription.payment');
Route::post('/daftar/{registration}/upload-bukti',              [PublicSubscriptionController::class, 'uploadProof'])->name('public.subscription.upload');
Route::get('/daftar/{registration}/sukses',                     [PublicSubscriptionController::class, 'success'])->name('public.subscription.success');

// School Admin Web Panel
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.post');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware(['auth', 'role:admin|accountant', 'subscription.active', '2fa.enforce'])->group(function () {
        Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

        // ==================== AKADEMIK ====================
        Route::get('/academic/years',                    [AcademicWebController::class, 'years'])->name('academic.years.index');
        Route::post('/academic/years',                   [AcademicWebController::class, 'storeYear'])->name('academic.years.store');
        Route::put('/academic/years/{year}',             [AcademicWebController::class, 'updateYear'])->name('academic.years.update');
        Route::post('/academic/years/{year}/activate',   [AcademicWebController::class, 'activateYear'])->name('academic.years.activate');
        Route::delete('/academic/years/{year}',          [AcademicWebController::class, 'deleteYear'])->name('academic.years.destroy');

        Route::get('/academic/subjects',                 [AcademicWebController::class, 'subjects'])->name('academic.subjects.index');
        Route::post('/academic/subjects',                [AcademicWebController::class, 'storeSubject'])->name('academic.subjects.store');
        Route::put('/academic/subjects/{subject}',       [AcademicWebController::class, 'updateSubject'])->name('academic.subjects.update');
        Route::delete('/academic/subjects/{subject}',    [AcademicWebController::class, 'deleteSubject'])->name('academic.subjects.destroy');

        Route::get('/academic/classes',                  [AcademicWebController::class, 'classes'])->name('academic.classes.index');
        Route::post('/academic/classes',                 [AcademicWebController::class, 'storeClass'])->name('academic.classes.store');
        Route::put('/academic/classes/{class}',          [AcademicWebController::class, 'updateClass'])->name('academic.classes.update');
        Route::delete('/academic/classes/{class}',       [AcademicWebController::class, 'deleteClass'])->name('academic.classes.destroy');

        Route::get('/academic/sections',                 [AcademicWebController::class, 'sections'])->name('academic.sections.index');
        Route::post('/academic/sections',                [AcademicWebController::class, 'storeSection'])->name('academic.sections.store');
        Route::put('/academic/sections/{section}',       [AcademicWebController::class, 'updateSection'])->name('academic.sections.update');
        Route::delete('/academic/sections/{section}',    [AcademicWebController::class, 'deleteSection'])->name('academic.sections.destroy');

        Route::get('/academic/mediums',                  [AcademicWebController::class, 'mediums'])->name('academic.mediums.index');
        Route::post('/academic/mediums',                 [AcademicWebController::class, 'storeMedium'])->name('academic.mediums.store');
        Route::delete('/academic/mediums/{medium}',      [AcademicWebController::class, 'deleteMedium'])->name('academic.mediums.destroy');

        Route::get('/academic/class-sections',           [AcademicWebController::class, 'classSections'])->name('academic.class-sections.index');
        Route::post('/academic/class-sections',          [AcademicWebController::class, 'storeClassSection'])->name('academic.class-sections.store');
        Route::delete('/academic/class-sections/{classSection}', [AcademicWebController::class, 'deleteClassSection'])->name('academic.class-sections.destroy');

        // Students
        Route::get('/students',                          [StudentWebController::class, 'index'])->name('students.index');
        Route::get('/students/create',                   [StudentWebController::class, 'create'])->name('students.create');
        Route::post('/students',                         [StudentWebController::class, 'store'])->name('students.store');
        Route::get('/students/{student}/edit',           [StudentWebController::class, 'edit'])->name('students.edit');
        Route::put('/students/{student}',                [StudentWebController::class, 'update'])->name('students.update');
        Route::delete('/students/{student}',             [StudentWebController::class, 'destroy'])->name('students.destroy');
        Route::get('/students/timeline',                [StudentWebController::class, 'timeline'])->name('students.timeline');
        Route::get('/students/{student}',               [StudentWebController::class, 'show'])->name('students.show');

        // Timetable
        Route::get('/timetable',                         [TimetableWebController::class, 'index'])->name('timetable.index');
        Route::post('/timetable',                        [TimetableWebController::class, 'store'])->name('timetable.store');
        Route::delete('/timetable/{slot}',               [TimetableWebController::class, 'destroy'])->name('timetable.destroy');

        // Timetable Generator
        Route::get('/timetable/generator',               [\App\Http\Controllers\Web\Admin\Academic\TimetableGeneratorController::class, 'wizard'])->name('timetable.generator.wizard');
        Route::post('/timetable/generator/step',          [\App\Http\Controllers\Web\Admin\Academic\TimetableGeneratorController::class, 'postStep'])->name('timetable.generator.post-step');
        Route::post('/timetable/generator/run',           [\App\Http\Controllers\Web\Admin\Academic\TimetableGeneratorController::class, 'generate'])->name('timetable.generator.generate');

        // Leaderboard & Gamifikasi
        Route::get('/leaderboard',                       [LeaderboardController::class, 'index'])->name('leaderboard.index');
        Route::post('/leaderboard/config',               [LeaderboardController::class, 'saveConfig'])->name('leaderboard.config');
        Route::post('/leaderboard/award',                [LeaderboardController::class, 'awardPoints'])->name('leaderboard.award');
        Route::post('/leaderboard/award-batch',          [LeaderboardController::class, 'awardPointsBatch'])->name('leaderboard.award-batch');
        Route::post('/leaderboard/deduct',               [LeaderboardController::class, 'deductPoints'])->name('leaderboard.deduct');
        Route::get('/leaderboard/history',               [LeaderboardController::class, 'history'])->name('leaderboard.history');
        Route::post('/leaderboard/sync',                 [LeaderboardController::class, 'syncFromSources'])->name('leaderboard.sync');

        // Payroll
        Route::get('/payroll/structures',                [PayrollWebController::class, 'structures'])->name('payroll.structures.index');
        Route::post('/payroll/structures',               [PayrollWebController::class, 'storeStructure'])->name('payroll.structures.store');
        Route::delete('/payroll/structures/{structure}', [PayrollWebController::class, 'deleteStructure'])->name('payroll.structures.destroy');
        Route::get('/payroll/slips',                     [PayrollWebController::class, 'slips'])->name('payroll.slips.index');
        Route::post('/payroll/slips/generate',           [PayrollWebController::class, 'generateSlips'])->name('payroll.slips.generate');
        Route::post('/payroll/slips/{slip}/pay',         [PayrollWebController::class, 'paySlip'])->name('payroll.slips.pay');
        Route::delete('/payroll/slips/{slip}',           [PayrollWebController::class, 'deleteSlip'])->name('payroll.slips.destroy');

        // Exams & Marks
        Route::get('/exams',                             [ExamWebController::class, 'index'])->name('exams.index');
        Route::post('/exams',                            [ExamWebController::class, 'store'])->name('exams.store');
        Route::delete('/exams/{exam}',                   [ExamWebController::class, 'destroy'])->name('exams.destroy');
        Route::get('/exams/{exam}/marks',                [ExamWebController::class, 'inputMarks'])->name('exams.marks');
        Route::post('/exams/{exam}/marks',               [ExamWebController::class, 'saveMarks'])->name('exams.marks.save');
        Route::get('/exams/{exam}/analysis',             [ExamWebController::class, 'analysis'])->name('exams.analysis');
        Route::get('/exams/{exam}/generate',             [ExamWebController::class, 'generateFromBank'])->name('exams.generate');
        Route::post('/exams/{exam}/generate',            [ExamWebController::class, 'storeGeneratedFromBank'])->name('exams.generate.store');

        // AI Essay Grading
        Route::get('/academic/essay-grading',              [EssayGradingController::class, 'index'])->name('academic.essay-grading.index');
        Route::post('/academic/essay-grading/grade',       [EssayGradingController::class, 'gradeSingle'])->name('academic.essay-grading.grade');
        Route::post('/academic/essay-grading/grade-batch', [EssayGradingController::class, 'gradeBatch'])->name('academic.essay-grading.grade-batch');
        Route::get('/academic/essay-grading/export',       [EssayGradingController::class, 'export'])->name('academic.essay-grading.export');

        // Library
        Route::get('/library/books',                     [LibraryWebController::class, 'books'])->name('library.books.index');
        Route::post('/library/books',                    [LibraryWebController::class, 'storeBook'])->name('library.books.store');
        Route::delete('/library/books/{book}',           [LibraryWebController::class, 'deleteBook'])->name('library.books.destroy');
        Route::get('/library/categories',                [LibraryWebController::class, 'categories'])->name('library.categories.index');
        Route::post('/library/categories',               [LibraryWebController::class, 'storeCategory'])->name('library.categories.store');
        Route::delete('/library/categories/{category}',  [LibraryWebController::class, 'deleteCategory'])->name('library.categories.destroy');
        Route::get('/library/issues',                    [LibraryWebController::class, 'issues'])->name('library.issues.index');
        Route::post('/library/issues',                   [LibraryWebController::class, 'issueBook'])->name('library.issues.store');
        Route::post('/library/issues/{issue}/return',    [LibraryWebController::class, 'returnBook'])->name('library.issues.return');

        // Digital Library
        Route::get('/library/digital',               [DigitalLibraryController::class, 'upload'])->name('library.digital.upload');
        Route::post('/library/digital',              [DigitalLibraryController::class, 'storeDigital'])->name('library.digital.store');
        Route::delete('/library/digital/{book}',     [DigitalLibraryController::class, 'deleteDigital'])->name('library.digital.delete');
        Route::post('/library/digital/issue',        [DigitalLibraryController::class, 'issueDigital'])->name('library.digital.issue');
        Route::post('/library/digital/revoke',       [DigitalLibraryController::class, 'revokeAccess'])->name('library.digital.revoke');
        Route::get('/library/digital/stats',          [DigitalLibraryController::class, 'stats'])->name('library.digital.stats');

        // ============== PHASE 8 SUB-CRUD ==============
        Route::get('/ppdb/periods',                      [Phase8CrudController::class, 'ppdbPeriods'])->name('ppdb.periods.index');
        Route::post('/ppdb/periods',                     [Phase8CrudController::class, 'storePpdbPeriod'])->name('ppdb.periods.store');
        Route::post('/ppdb/periods/{period}/publish',    [Phase8CrudController::class, 'publishPpdbPeriod'])->name('ppdb.periods.publish');
        Route::delete('/ppdb/periods/{period}',          [Phase8CrudController::class, 'deletePpdbPeriod'])->name('ppdb.periods.destroy');
        Route::get('/ppdb/applications',                 [Phase8CrudController::class, 'ppdbApplications'])->name('ppdb.applications.index');
        Route::post('/ppdb/applications/{application}/review', [Phase8CrudController::class, 'reviewPpdbApplication'])->name('ppdb.applications.review');

        Route::get('/clinic/visits',                     [Phase8CrudController::class, 'clinicVisits'])->name('clinic.visits.index');
        Route::post('/clinic/visits',                    [Phase8CrudController::class, 'storeClinicVisit'])->name('clinic.visits.store');
        Route::get('/clinic/vaccinations',               [Phase8CrudController::class, 'vaccinations'])->name('clinic.vaccinations.index');
        Route::post('/clinic/vaccinations',              [Phase8CrudController::class, 'storeVaccination'])->name('clinic.vaccinations.store');

        Route::get('/counseling/sessions',               [Phase8CrudController::class, 'counselingSessions'])->name('counseling.sessions.index');
        Route::post('/counseling/sessions',              [Phase8CrudController::class, 'storeCounselingSession'])->name('counseling.sessions.store');
        Route::get('/counseling/bullying',               [Phase8CrudController::class, 'bullyingReports'])->name('counseling.bullying.index');
        Route::put('/counseling/bullying/{report}',      [Phase8CrudController::class, 'updateBullyingReport'])->name('counseling.bullying.update');

        Route::get('/discipline/categories',             [Phase8CrudController::class, 'disciplineCategories'])->name('discipline.categories.index');
        Route::post('/discipline/categories',            [Phase8CrudController::class, 'storeDisciplineCategory'])->name('discipline.categories.store');
        Route::delete('/discipline/categories/{category}', [Phase8CrudController::class, 'deleteDisciplineCategory'])->name('discipline.categories.destroy');
        Route::get('/discipline/records',                [Phase8CrudController::class, 'disciplineRecords'])->name('discipline.records.index');
        Route::post('/discipline/records',               [Phase8CrudController::class, 'storeDisciplineRecord'])->name('discipline.records.store');

        Route::get('/transport/vehicles',                [Phase8CrudController::class, 'vehicles'])->name('transport.vehicles.index');
        Route::post('/transport/vehicles',               [Phase8CrudController::class, 'storeVehicle'])->name('transport.vehicles.store');
        Route::delete('/transport/vehicles/{vehicle}',   [Phase8CrudController::class, 'deleteVehicle'])->name('transport.vehicles.destroy');
        Route::get('/transport/routes',                  [Phase8CrudController::class, 'transportRoutes'])->name('transport.routes.index');
        Route::post('/transport/routes',                 [Phase8CrudController::class, 'storeTransportRoute'])->name('transport.routes.store');
        Route::delete('/transport/routes/{route}',       [Phase8CrudController::class, 'deleteTransportRoute'])->name('transport.routes.destroy');

        // ============== PHASE 9 SUB-CRUD ==============
        Route::get('/lesson-plan',                       [LessonPlanController::class, 'index'])->name('lesson-plan.index');
        Route::post('/lesson-plan',                      [LessonPlanController::class, 'store'])->name('lesson-plan.store');
        Route::delete('/lesson-plan/{plan}',             [LessonPlanController::class, 'destroy'])->name('lesson-plan.destroy');
        Route::post('/lesson-plan/generate',             [LessonPlanController::class, 'generate'])->name('lesson-plan.generate');
        Route::post('/lesson-plan/generate-save',        [LessonPlanController::class, 'generateAndSave'])->name('lesson-plan.generate-save');

        Route::get('/live-class/sessions',               [Phase9CrudController::class, 'liveClassSessions'])->name('live-class.index');
        Route::post('/live-class/sessions',              [Phase9CrudController::class, 'storeLiveClassSession'])->name('live-class.store');
        Route::delete('/live-class/sessions/{session}',  [Phase9CrudController::class, 'deleteLiveClassSession'])->name('live-class.destroy');

        // ============== LMS: KURSUS ==============
        Route::get('/courses',                           [CourseController::class, 'index'])->name('courses.index');
        Route::post('/courses',                          [CourseController::class, 'store'])->name('courses.store');
        Route::get('/courses/{course}',                  [CourseController::class, 'show'])->name('courses.show');
        Route::put('/courses/{course}',                  [CourseController::class, 'update'])->name('courses.update');
        Route::delete('/courses/{course}',               [CourseController::class, 'destroy'])->name('courses.destroy');
        Route::post('/courses/{course}/modules',         [CourseController::class, 'storeModule'])->name('courses.modules.store');
        Route::delete('/courses/modules/{module}',       [CourseController::class, 'deleteModule'])->name('courses.modules.destroy');
        Route::post('/courses/modules/{module}/lessons', [CourseController::class, 'storeLesson'])->name('courses.lessons.store');
        Route::delete('/courses/lessons/{lesson}',       [CourseController::class, 'deleteLesson'])->name('courses.lessons.destroy');
        Route::post('/courses/{course}/enroll',          [CourseController::class, 'enroll'])->name('courses.enroll');
        Route::delete('/courses/enrollments/{enrollment}',[CourseController::class, 'unenroll'])->name('courses.enrollments.destroy');
        Route::post('/courses/enrollments/{enrollment}/complete/{lesson}', [CourseController::class, 'markComplete'])->name('courses.enrollments.complete');

        Route::get('/ai/providers',                      [Phase9CrudController::class, 'aiProviders'])->name('ai.providers.index');
        Route::post('/ai/providers',                     [Phase9CrudController::class, 'storeAiProvider'])->name('ai.providers.store');
        Route::delete('/ai/providers/{provider}',        [Phase9CrudController::class, 'deleteAiProvider'])->name('ai.providers.destroy');

        // AI Chat with Data (Tanya Data Sekolah)
        Route::get('/ai/chat-data',                      [AiDataChatController::class, 'index'])->name('ai.chat-data.index');
        Route::post('/ai/chat-data',                     [AiDataChatController::class, 'ask'])->name('ai.chat-data.ask');

        // PKG — Penilaian Kinerja Guru
        Route::get('/pkg',                               [PkgController::class, 'index'])->name('pkg.index');
        Route::get('/pkg/create',                        [PkgController::class, 'create'])->name('pkg.create');
        Route::post('/pkg',                              [PkgController::class, 'store'])->name('pkg.store');
        Route::get('/pkg/{assessment}',                  [PkgController::class, 'detail'])->name('pkg.detail');
        Route::post('/pkg/{assessment}/verify',          [PkgController::class, 'verify'])->name('pkg.verify');
        Route::delete('/pkg/{assessment}',               [PkgController::class, 'destroy'])->name('pkg.destroy');
        Route::get('/pkg/{assessment}/pdf',              [PkgController::class, 'exportPdf'])->name('pkg.export-pdf');

        // Diklat & Sertifikasi Guru
        Route::get('/training',                              [TrainingController::class, 'index'])->name('training.index');
        Route::get('/training/create',                       [TrainingController::class, 'create'])->name('training.create');
        Route::post('/training',                             [TrainingController::class, 'store'])->name('training.store');
        Route::get('/training/{training}/edit',              [TrainingController::class, 'edit'])->name('training.edit');
        Route::put('/training/{training}',                   [TrainingController::class, 'update'])->name('training.update');
        Route::delete('/training/{training}',                [TrainingController::class, 'destroy'])->name('training.destroy');
        Route::get('/training/{training}/participants',      [TrainingController::class, 'participants'])->name('training.participants');
        Route::post('/training/{training}/register',         [TrainingController::class, 'registerParticipant'])->name('training.register-participant');
        Route::post('/training/{training}/participants/{participant}/update', [TrainingController::class, 'updateParticipantStatus'])->name('training.update-participant');
        Route::delete('/training/{training}/participants/{participant}', [TrainingController::class, 'removeParticipant'])->name('training.remove-participant');
        Route::post('/training/{training}/participants/{participant}/issue-cert', [TrainingController::class, 'issueCertificate'])->name('training.issue-certificate');
        Route::get('/training/{training}/participants/{participant}/cert-pdf', [TrainingController::class, 'certificatePdf'])->name('training.certificate-pdf');
        Route::get('/training/certifications',               [TrainingController::class, 'certifications'])->name('training.certifications');
        Route::post('/training/certifications',              [TrainingController::class, 'storeCertification'])->name('training.store-certification');
        Route::put('/training/certifications/{certification}', [TrainingController::class, 'updateCertification'])->name('training.update-certification');
        Route::delete('/training/certifications/{certification}', [TrainingController::class, 'deleteCertification'])->name('training.delete-certification');

        // Lesson Study
        Route::get('/lesson-study',                          [LessonStudyController::class, 'index'])->name('lesson-study.index');
        Route::get('/lesson-study/create',                   [LessonStudyController::class, 'create'])->name('lesson-study.create');
        Route::post('/lesson-study',                         [LessonStudyController::class, 'store'])->name('lesson-study.store');
        Route::get('/lesson-study/{lessonStudy}',            [LessonStudyController::class, 'show'])->name('lesson-study.show');
        Route::get('/lesson-study/{lessonStudy}/edit',       [LessonStudyController::class, 'edit'])->name('lesson-study.edit');
        Route::put('/lesson-study/{lessonStudy}',            [LessonStudyController::class, 'update'])->name('lesson-study.update');
        Route::delete('/lesson-study/{lessonStudy}',         [LessonStudyController::class, 'destroy'])->name('lesson-study.destroy');
        Route::post('/lesson-study/{lessonStudy}/advance',   [LessonStudyController::class, 'advancePhase'])->name('lesson-study.advance-phase');
        Route::get('/lesson-study/{lessonStudy}/observe',    [LessonStudyController::class, 'observe'])->name('lesson-study.observe');
        Route::post('/lesson-study/{lessonStudy}/observe',   [LessonStudyController::class, 'storeObservation'])->name('lesson-study.store-observation');
        Route::get('/lesson-study/{lessonStudy}/reflect',    [LessonStudyController::class, 'reflect'])->name('lesson-study.reflect');
        Route::post('/lesson-study/{lessonStudy}/reflect',   [LessonStudyController::class, 'storeReflection'])->name('lesson-study.store-reflection');
        Route::get('/lesson-study/{lessonStudy}/report-pdf', [LessonStudyController::class, 'reportPdf'])->name('lesson-study.report-pdf');

        // Enhanced Assignments (Online Classroom)
        Route::get('/classroom/assignments',                  [AssignmentController::class, 'index'])->name('assignments.index');
        Route::get('/classroom/assignments/create',           [AssignmentController::class, 'create'])->name('assignments.create');
        Route::post('/classroom/assignments',                 [AssignmentController::class, 'store'])->name('assignments.store');
        Route::get('/classroom/assignments/{assignment}/edit',[AssignmentController::class, 'edit'])->name('assignments.edit');
        Route::put('/classroom/assignments/{assignment}',     [AssignmentController::class, 'update'])->name('assignments.update');
        Route::delete('/classroom/assignments/{assignment}',  [AssignmentController::class, 'destroy'])->name('assignments.destroy');
        Route::get('/classroom/assignments/{assignment}/submissions', [AssignmentController::class, 'submissions'])->name('assignments.submissions');
        Route::post('/classroom/assignments/submissions/{submission}/grade', [AssignmentController::class, 'grade'])->name('assignments.submissions.grade');
        Route::delete('/classroom/assignments/submissions/{submission}', [AssignmentController::class, 'destroySubmission'])->name('assignments.submissions.destroy');

        Route::get('/canteen/categories',                [Phase9CrudController::class, 'canteenCategories'])->name('canteen.categories.index');
        Route::post('/canteen/categories',               [Phase9CrudController::class, 'storeCanteenCategory'])->name('canteen.categories.store');
        Route::delete('/canteen/categories/{category}',  [Phase9CrudController::class, 'deleteCanteenCategory'])->name('canteen.categories.destroy');
        Route::get('/canteen/menu',                      [Phase9CrudController::class, 'canteenMenu'])->name('canteen.menu.index');
        Route::post('/canteen/menu',                     [Phase9CrudController::class, 'storeCanteenMenuItem'])->name('canteen.menu.store');
        Route::delete('/canteen/menu/{item}',            [Phase9CrudController::class, 'deleteCanteenMenuItem'])->name('canteen.menu.destroy');

        Route::get('/religious/targets',                 [Phase9CrudController::class, 'hafalanTargets'])->name('religious.targets.index');
        Route::post('/religious/targets',                [Phase9CrudController::class, 'storeHafalanTarget'])->name('religious.targets.store');
        Route::delete('/religious/targets/{target}',     [Phase9CrudController::class, 'deleteHafalanTarget'])->name('religious.targets.destroy');
        Route::get('/religious/progress',                [Phase9CrudController::class, 'hafalanProgress'])->name('religious.progress.index');
        Route::post('/religious/progress',               [Phase9CrudController::class, 'storeHafalanProgress'])->name('religious.progress.store');

        // ============== PHASE 10 SUB-CRUD ==============
        Route::get('/donations/campaigns',               [Phase10CrudController::class, 'donationCampaigns'])->name('donations.campaigns.index');
        Route::post('/donations/campaigns',              [Phase10CrudController::class, 'storeDonationCampaign'])->name('donations.campaigns.store');
        Route::delete('/donations/campaigns/{campaign}', [Phase10CrudController::class, 'deleteDonationCampaign'])->name('donations.campaigns.destroy');
        Route::get('/donations/list',                    [Phase10CrudController::class, 'donationsList'])->name('donations.list');

        Route::get('/achievements/categories',           [Phase10CrudController::class, 'achievementCategories'])->name('achievements.categories.index');
        Route::post('/achievements/categories',          [Phase10CrudController::class, 'storeAchievementCategory'])->name('achievements.categories.store');
        Route::delete('/achievements/categories/{category}', [Phase10CrudController::class, 'deleteAchievementCategory'])->name('achievements.categories.destroy');
        Route::get('/achievements/records',              [Phase10CrudController::class, 'studentAchievements'])->name('achievements.records.index');
        Route::post('/achievements/records',             [Phase10CrudController::class, 'storeStudentAchievement'])->name('achievements.records.store');

        Route::get('/scholarship/programs',              [Phase10CrudController::class, 'scholarshipPrograms'])->name('scholarship.programs.index');
        Route::post('/scholarship/programs',             [Phase10CrudController::class, 'storeScholarshipProgram'])->name('scholarship.programs.store');
        Route::delete('/scholarship/programs/{program}', [Phase10CrudController::class, 'deleteScholarshipProgram'])->name('scholarship.programs.destroy');
        Route::get('/scholarship/applications',          [Phase10CrudController::class, 'scholarshipApplications'])->name('scholarship.applications.index');
        Route::post('/scholarship/applications/{application}/review', [Phase10CrudController::class, 'reviewScholarshipApplication'])->name('scholarship.applications.review');

        Route::get('/events',                            [Phase10CrudController::class, 'events'])->name('events.index');
        Route::post('/events',                           [Phase10CrudController::class, 'storeEvent'])->name('events.store');
        Route::delete('/events/{event}',                 [Phase10CrudController::class, 'deleteEvent'])->name('events.destroy');

        Route::get('/alumni',                            [Phase10CrudController::class, 'alumni'])->name('alumni.index');
        Route::post('/alumni/{alumni}/verify',           [Phase10CrudController::class, 'verifyAlumni'])->name('alumni.verify');

        // Tracer Study Alumni
        Route::get('/alumni/tracer',                     [AdminTracerController::class, 'dashboard'])->name('tracer.dashboard');
        Route::get('/alumni/tracer/questions',           [AdminTracerController::class, 'questions'])->name('tracer.questions');
        Route::post('/alumni/tracer/questions',          [AdminTracerController::class, 'storeQuestion'])->name('tracer.questions.store');
        Route::put('/alumni/tracer/questions/{question}', [AdminTracerController::class, 'updateQuestion'])->name('tracer.questions.update');
        Route::delete('/alumni/tracer/questions/{question}', [AdminTracerController::class, 'deleteQuestion'])->name('tracer.questions.destroy');
        Route::get('/alumni/tracer/responses',           [AdminTracerController::class, 'responses'])->name('tracer.responses');
        Route::get('/alumni/tracer/export-csv',          [AdminTracerController::class, 'exportCsv'])->name('tracer.export-csv');

        // Job Board Alumni
        Route::get('/jobs',                              [JobBoardController::class, 'index'])->name('jobs.index');
        Route::get('/jobs/create',                       [JobBoardController::class, 'create'])->name('jobs.create');
        Route::post('/jobs',                             [JobBoardController::class, 'store'])->name('jobs.store');
        Route::get('/jobs/{listing}/edit',               [JobBoardController::class, 'edit'])->name('jobs.edit');
        Route::put('/jobs/{listing}',                    [JobBoardController::class, 'update'])->name('jobs.update');
        Route::delete('/jobs/{listing}',                 [JobBoardController::class, 'destroy'])->name('jobs.destroy');
        Route::post('/jobs/{listing}/toggle-verify',     [JobBoardController::class, 'toggleVerify'])->name('jobs.toggle-verify');
        Route::post('/jobs/{listing}/toggle-active',     [JobBoardController::class, 'toggleActive'])->name('jobs.toggle-active');
        Route::get('/jobs/{listing}/applications',       [JobBoardController::class, 'applications'])->name('jobs.applications');
        Route::post('/applications/{application}/status',[JobBoardController::class, 'updateApplicationStatus'])->name('jobs.application-status');

        // ============== BKK — Bursa Kerja Khusus ==============
        Route::get('/bkk',                               [BkkController::class, 'dashboard'])->name('bkk.dashboard');
        Route::get('/bkk/partners',                      [BkkController::class, 'partners'])->name('bkk.partners');
        Route::post('/bkk/partners',                     [BkkController::class, 'storePartner'])->name('bkk.partners.store');
        Route::put('/bkk/partners/{partner}',            [BkkController::class, 'updatePartner'])->name('bkk.partners.update');
        Route::delete('/bkk/partners/{partner}',         [BkkController::class, 'deletePartner'])->name('bkk.partners.delete');
        Route::get('/bkk/placements',                    [BkkController::class, 'placements'])->name('bkk.placements');
        Route::post('/bkk/placements',                   [BkkController::class, 'storePlacement'])->name('bkk.placements.store');
        Route::put('/bkk/placements/{placement}',        [BkkController::class, 'updatePlacement'])->name('bkk.placements.update');
        Route::delete('/bkk/placements/{placement}',     [BkkController::class, 'deletePlacement'])->name('bkk.placements.delete');
        Route::get('/bkk/reports',                       [BkkController::class, 'reports'])->name('bkk.reports');
        Route::post('/bkk/reports/generate',             [BkkController::class, 'generateReport'])->name('bkk.reports.generate');
        Route::put('/bkk/reports/{report}',              [BkkController::class, 'updateReport'])->name('bkk.reports.update');
        Route::delete('/bkk/reports/{report}',           [BkkController::class, 'deleteReport'])->name('bkk.reports.delete');

        // ============== PHASE 11 SUB-CRUD ==============
        Route::get('/visitor/logs',                      [Phase11CrudController::class, 'visitorLogs'])->name('visitor.logs.index');
        Route::post('/visitor/logs/checkin',              [Phase11CrudController::class, 'checkInVisitor'])->name('visitor.logs.checkin');
        Route::post('/visitor/logs/{log}/checkout',       [Phase11CrudController::class, 'checkOutVisitor'])->name('visitor.logs.checkout');
        Route::get('/visitor/blacklist',                  [Phase11CrudController::class, 'visitorBlacklist'])->name('visitor.blacklist.index');
        Route::post('/visitor/blacklist',                 [Phase11CrudController::class, 'storeVisitorBlacklist'])->name('visitor.blacklist.store');
        Route::delete('/visitor/blacklist/{entry}',       [Phase11CrudController::class, 'deleteVisitorBlacklist'])->name('visitor.blacklist.destroy');

        // Pre-Registration Visitor
        Route::get('/visitor/pre-registration',           [PreRegistrationController::class, 'index'])->name('visitor.pre-registration.index');
        Route::post('/visitor/pre-registration/checkin/{visitor}', [PreRegistrationController::class, 'checkIn'])->name('visitor.pre-registration.checkin');
        Route::post('/visitor/pre-registration/checkout/{visitor}', [PreRegistrationController::class, 'checkOut'])->name('visitor.pre-registration.checkout');
        Route::post('/visitor/pre-registration/cancel/{visitor}', [PreRegistrationController::class, 'cancel'])->name('visitor.pre-registration.cancel');
        Route::get('/visitor/pre-registration/export',    [PreRegistrationController::class, 'export'])->name('visitor.pre-registration.export');

        Route::get('/inventory/categories',              [Phase11CrudController::class, 'assetCategories'])->name('inventory.categories.index');
        Route::post('/inventory/categories',             [Phase11CrudController::class, 'storeAssetCategory'])->name('inventory.categories.store');
        Route::delete('/inventory/categories/{category}', [Phase11CrudController::class, 'deleteAssetCategory'])->name('inventory.categories.destroy');
        Route::get('/inventory/assets',                  [Phase11CrudController::class, 'assets'])->name('inventory.assets.index');
        Route::post('/inventory/assets',                 [Phase11CrudController::class, 'storeAsset'])->name('inventory.assets.store');
        Route::delete('/inventory/assets/{asset}',       [Phase11CrudController::class, 'deleteAsset'])->name('inventory.assets.destroy');
        Route::get('/inventory/loans',                   [Phase11CrudController::class, 'assetLoans'])->name('inventory.loans.index');
        Route::post('/inventory/loans',                  [Phase11CrudController::class, 'storeAssetLoan'])->name('inventory.loans.store');
        Route::post('/inventory/loans/{loan}/return',    [Phase11CrudController::class, 'returnAssetLoan'])->name('inventory.loans.return');

        // ============== INVENTORY ADVANCED ==============
        Route::get('/inventory/enhanced',                [AdvancedAssetController::class, 'enhancedIndex'])->name('inventory.enhanced.index');
        Route::post('/inventory/enhanced',               [AdvancedAssetController::class, 'storeAsset'])->name('inventory.enhanced.store');
        Route::put('/inventory/enhanced/{asset}',        [AdvancedAssetController::class, 'updateAsset'])->name('inventory.enhanced.update');
        Route::get('/inventory/depreciation/{asset}',    [AdvancedAssetController::class, 'showDepreciation'])->name('inventory.depreciation');
        Route::get('/inventory/qr-print/{asset}',        [AdvancedAssetController::class, 'qrPrint'])->name('inventory.qr-print');
        Route::get('/inventory/maintenance',             [AdvancedAssetController::class, 'maintenance'])->name('inventory.maintenance');
        Route::post('/inventory/maintenance',            [AdvancedAssetController::class, 'storeMaintenance'])->name('inventory.maintenance.store');
        Route::put('/inventory/maintenance/{schedule}',  [AdvancedAssetController::class, 'updateMaintenance'])->name('inventory.maintenance.update');
        Route::delete('/inventory/maintenance/{schedule}', [AdvancedAssetController::class, 'deleteMaintenance'])->name('inventory.maintenance.delete');
        Route::get('/inventory/writeoffs',               [AdvancedAssetController::class, 'writeOffs'])->name('inventory.writeoffs');
        Route::post('/inventory/writeoffs',              [AdvancedAssetController::class, 'storeWriteOff'])->name('inventory.writeoffs.store');
        Route::post('/inventory/writeoffs/{writeOff}/submit', [AdvancedAssetController::class, 'submitWriteOff'])->name('inventory.writeoffs.submit');
        Route::post('/inventory/writeoffs/{writeOff}/approve', [AdvancedAssetController::class, 'approveWriteOff'])->name('inventory.writeoffs.approve');
        Route::post('/inventory/writeoffs/{writeOff}/reject', [AdvancedAssetController::class, 'rejectWriteOff'])->name('inventory.writeoffs.reject');

        Route::get('/dapodik/config',                    [Phase11CrudController::class, 'dapodikConfig'])->name('dapodik.config.index');
        Route::put('/dapodik/config',                    [Phase11CrudController::class, 'updateDapodikConfig'])->name('dapodik.config.update');

        Route::get('/analytics/risks',                   [Phase11CrudController::class, 'riskScores'])->name('analytics.risks.index');

        // AI Dropout Prediction
        Route::get('/analytics/dropout-risk',              [DropoutRiskController::class, 'index'])->name('analytics.dropout-risk.index');
        Route::post('/analytics/dropout-risk/predict',     [DropoutRiskController::class, 'runPrediction'])->name('analytics.dropout-risk.predict');
        Route::post('/analytics/dropout-risk/predict-one', [DropoutRiskController::class, 'runSinglePrediction'])->name('analytics.dropout-risk.predict-one');
        Route::post('/analytics/dropout-risk/notify',      [DropoutRiskController::class, 'notifyParents'])->name('analytics.dropout-risk.notify');

        // ============== HOSTEL ==============
        Route::get('/hostel',                            [HostelWebController::class, 'hostels'])->name('hostel.list.index');
        Route::post('/hostel',                           [HostelWebController::class, 'storeHostel'])->name('hostel.list.store');
        Route::delete('/hostel/{hostel}',                [HostelWebController::class, 'deleteHostel'])->name('hostel.list.destroy');
        Route::get('/hostel/{hostel}/rooms',              [HostelWebController::class, 'rooms'])->name('hostel.rooms.index');
        Route::post('/hostel/{hostel}/rooms',             [HostelWebController::class, 'storeRoom'])->name('hostel.rooms.store');
        Route::delete('/hostel/rooms/{room}',             [HostelWebController::class, 'deleteRoom'])->name('hostel.rooms.destroy');
        Route::get('/hostel-allocations',                [HostelWebController::class, 'allocations'])->name('hostel.allocations.index');
        Route::post('/hostel-allocations',               [HostelWebController::class, 'storeAllocation'])->name('hostel.allocations.store');

        // ============== ROOM BOOKING ==============
        Route::prefix('facilities/rooms')->name('facilities.rooms.')->group(function () {
            Route::get('/',                              [\App\Http\Controllers\Web\Admin\Facilities\RoomBookingController::class, 'index'])->name('index');
            Route::post('/',                             [\App\Http\Controllers\Web\Admin\Facilities\RoomBookingController::class, 'storeRoom'])->name('store');
            Route::put('/{room}/update',                 [\App\Http\Controllers\Web\Admin\Facilities\RoomBookingController::class, 'updateRoom'])->name('update');
            Route::delete('/{room}',                     [\App\Http\Controllers\Web\Admin\Facilities\RoomBookingController::class, 'deleteRoom'])->name('destroy');
            Route::post('/{room}/upload-photo',          [\App\Http\Controllers\Web\Admin\Facilities\RoomBookingController::class, 'uploadRoomPhoto'])->name('upload-photo');
            Route::get('/calendar',                      [\App\Http\Controllers\Web\Admin\Facilities\RoomBookingController::class, 'calendar'])->name('calendar');
            Route::get('/calendar/feed',                 [\App\Http\Controllers\Web\Admin\Facilities\RoomBookingController::class, 'calendarFeed'])->name('calendar.feed');
            Route::post('/booking',                      [\App\Http\Controllers\Web\Admin\Facilities\RoomBookingController::class, 'storeBooking'])->name('booking.store');
            Route::post('/booking/{bookingId}/approve',  [\App\Http\Controllers\Web\Admin\Facilities\RoomBookingController::class, 'approve'])->name('approve');
            Route::post('/booking/{bookingId}/reject',   [\App\Http\Controllers\Web\Admin\Facilities\RoomBookingController::class, 'reject'])->name('reject');
            Route::post('/booking/{bookingId}/cancel',   [\App\Http\Controllers\Web\Admin\Facilities\RoomBookingController::class, 'cancel'])->name('cancel');
            Route::get('/approvals',                     [\App\Http\Controllers\Web\Admin\Facilities\RoomBookingController::class, 'approvals'])->name('approvals');
            Route::get('/{room}/rules',                  [\App\Http\Controllers\Web\Admin\Facilities\RoomBookingController::class, 'rules'])->name('rules');
            Route::post('/{room}/rules',                 [\App\Http\Controllers\Web\Admin\Facilities\RoomBookingController::class, 'saveRules'])->name('rules.save');
        });

        // ============== CHAT & NOTIFICATIONS ==============
        Route::get('/chat',                              [ChatNotificationController::class, 'inbox'])->name('chat.inbox');
        Route::get('/chat/{conversation}',               [ChatNotificationController::class, 'showConversation'])->name('chat.show');
        Route::post('/chat/start',                       [ChatNotificationController::class, 'startConversation'])->name('chat.start');
        Route::post('/chat/{conversation}/send',         [ChatNotificationController::class, 'sendMessage'])->name('chat.send');
        Route::get('/notifications',                     [ChatNotificationController::class, 'notifications'])->name('notifications.index');
        Route::post('/notifications/{notification}/read', [ChatNotificationController::class, 'markRead'])->name('notifications.read');

        // Audit Log
        Route::get('/audit-log',                          [\App\Http\Controllers\Web\Admin\AuditLogController::class, 'index'])->name('audit.index');
        Route::get('/audit-log/{activity}',               [\App\Http\Controllers\Web\Admin\AuditLogController::class, 'show'])->name('audit.show');

        // Multi-currency
        Route::get('/currency',     [\App\Http\Controllers\Web\Admin\CurrencyController::class, 'show'])->name('currency.show');
        Route::put('/currency',     [\App\Http\Controllers\Web\Admin\CurrencyController::class, 'update'])->name('currency.update');

        // AI usage dashboard
        Route::get('/ai/usage',     [\App\Http\Controllers\Web\Admin\AI\AiUsageDashboardController::class, 'index'])->name('ai.usage');

        // Data export (full per-school)
        Route::get('/exports',                  [\App\Http\Controllers\Web\Admin\SchoolDataExportController::class, 'index'])->name('exports.index');
        Route::post('/exports',                 [\App\Http\Controllers\Web\Admin\SchoolDataExportController::class, 'store'])
            ->middleware('throttle:export')->name('exports.store');
        Route::get('/exports/{export}/download',[\App\Http\Controllers\Web\Admin\SchoolDataExportController::class, 'download'])->name('exports.download');
        Route::delete('/exports/{export}',      [\App\Http\Controllers\Web\Admin\SchoolDataExportController::class, 'destroy'])->name('exports.destroy');

        // Webhooks outbound
        Route::get('/webhooks',                           [\App\Http\Controllers\Web\Admin\Communication\WebhookController::class, 'index'])->name('webhooks.index');
        Route::post('/webhooks',                          [\App\Http\Controllers\Web\Admin\Communication\WebhookController::class, 'store'])->name('webhooks.store');
        Route::post('/webhooks/{webhook}/toggle',         [\App\Http\Controllers\Web\Admin\Communication\WebhookController::class, 'toggle'])->name('webhooks.toggle');
        Route::delete('/webhooks/{webhook}',              [\App\Http\Controllers\Web\Admin\Communication\WebhookController::class, 'destroy'])->name('webhooks.destroy');
        Route::get('/webhooks/{webhook}/deliveries',      [\App\Http\Controllers\Web\Admin\Communication\WebhookController::class, 'deliveries'])->name('webhooks.deliveries');
        Route::post('/webhooks/deliveries/{delivery}/retry', [\App\Http\Controllers\Web\Admin\Communication\WebhookController::class, 'retry'])->name('webhooks.retry');

        // ============== DOKUMEN ==============
        Route::get('/documents',                             [\App\Http\Controllers\Web\Admin\Communication\DocumentController::class, 'index'])->name('documents.index');
        Route::post('/documents',                            [\App\Http\Controllers\Web\Admin\Communication\DocumentController::class, 'store'])->name('documents.store');
        Route::put('/documents/{document}',                  [\App\Http\Controllers\Web\Admin\Communication\DocumentController::class, 'update'])->name('documents.update');
        Route::delete('/documents/{document}',               [\App\Http\Controllers\Web\Admin\Communication\DocumentController::class, 'destroy'])->name('documents.destroy');
        Route::get('/documents/{document}/download',          [\App\Http\Controllers\Web\Admin\Communication\DocumentController::class, 'download'])->name('documents.download');
        Route::post('/documents/{document}/share',            [\App\Http\Controllers\Web\Admin\Communication\DocumentController::class, 'share'])->name('documents.share');
        Route::post('/documents/shares/{share}/revoke',       [\App\Http\Controllers\Web\Admin\Communication\DocumentController::class, 'revokeShare'])->name('documents.share.revoke');
        Route::get('/documents/approvals',                    [\App\Http\Controllers\Web\Admin\Communication\DocumentController::class, 'approvals'])->name('documents.approvals');
        Route::post('/documents/approvals/{approval}/decide', [\App\Http\Controllers\Web\Admin\Communication\DocumentController::class, 'decideApproval'])->name('documents.decide-approval');
        Route::get('/documents/categories',                   [\App\Http\Controllers\Web\Admin\Communication\DocumentController::class, 'categoriesIndex'])->name('documents.categories');
        Route::post('/documents/categories',                  [\App\Http\Controllers\Web\Admin\Communication\DocumentController::class, 'storeCategory'])->name('documents.categories.store');
        Route::put('/documents/categories/{category}',        [\App\Http\Controllers\Web\Admin\Communication\DocumentController::class, 'updateCategory'])->name('documents.categories.update');
        Route::delete('/documents/categories/{category}',     [\App\Http\Controllers\Web\Admin\Communication\DocumentController::class, 'deleteCategory'])->name('documents.categories.delete');

        // ============== SURAT-MENYURAT ==============
        Route::get('/letters/templates',                     [LetterController::class, 'templates'])->name('letters.templates');
        Route::post('/letters/templates',                    [LetterController::class, 'storeTemplate'])->name('letters.templates.store');
        Route::put('/letters/templates/{template}',          [LetterController::class, 'updateTemplate'])->name('letters.templates.update');
        Route::delete('/letters/templates/{template}',       [LetterController::class, 'deleteTemplate'])->name('letters.templates.delete');
        Route::get('/letters',                               [LetterController::class, 'index'])->name('letters.index');
        Route::get('/letters/create',                        [LetterController::class, 'create'])->name('letters.create');
        Route::post('/letters',                              [LetterController::class, 'store'])->name('letters.store');
        Route::get('/letters/{letter}/edit',                 [LetterController::class, 'edit'])->name('letters.edit');
        Route::put('/letters/{letter}',                      [LetterController::class, 'update'])->name('letters.update');
        Route::delete('/letters/{letter}',                   [LetterController::class, 'destroy'])->name('letters.destroy');
        Route::get('/letters/{letter}/print',                [LetterController::class, 'print'])->name('letters.print');

        // Notification Providers (push, sms, whatsapp — dynamic per school)
        Route::get('/notif/providers',                  [\App\Http\Controllers\Web\Admin\Communication\NotificationProviderController::class, 'index'])->name('notif.providers.index');
        Route::post('/notif/providers',                 [\App\Http\Controllers\Web\Admin\Communication\NotificationProviderController::class, 'store'])->name('notif.providers.store');
        Route::post('/notif/providers/{provider}/toggle',  [\App\Http\Controllers\Web\Admin\Communication\NotificationProviderController::class, 'toggle'])->name('notif.providers.toggle');
        Route::post('/notif/providers/{provider}/default', [\App\Http\Controllers\Web\Admin\Communication\NotificationProviderController::class, 'setDefault'])->name('notif.providers.default');
        Route::post('/notif/providers/{provider}/test',    [\App\Http\Controllers\Web\Admin\Communication\NotificationProviderController::class, 'test'])->name('notif.providers.test');
        Route::get('/notif/providers/preset/{name}',       [\App\Http\Controllers\Web\Admin\Communication\NotificationProviderController::class, 'preset'])->name('notif.providers.preset');
        Route::delete('/notif/providers/{provider}',    [\App\Http\Controllers\Web\Admin\Communication\NotificationProviderController::class, 'destroy'])->name('notif.providers.destroy');

        // WhatsApp Bot
        Route::get('/wa-bot/commands',                  [WaBotController::class, 'commands'])->name('wa-bot.commands.index');
        Route::post('/wa-bot/commands',                 [WaBotController::class, 'store'])->name('wa-bot.commands.store');
        Route::put('/wa-bot/commands/{command}',        [WaBotController::class, 'update'])->name('wa-bot.commands.update');
        Route::post('/wa-bot/commands/{command}/toggle',[WaBotController::class, 'toggle'])->name('wa-bot.commands.toggle');
        Route::delete('/wa-bot/commands/{command}',     [WaBotController::class, 'destroy'])->name('wa-bot.commands.destroy');
        Route::get('/wa-bot/conversations',             [WaBotController::class, 'conversations'])->name('wa-bot.conversations.index');
        Route::post('/wa-bot/test',                     [WaBotController::class, 'test'])->name('wa-bot.test');

        // SPP Reminder Scheduler
        Route::get('/reminders',                         [ReminderController::class, 'index'])->name('reminders.index');
        Route::post('/reminders',                        [ReminderController::class, 'store'])->name('reminders.store');
        Route::put('/reminders/{schedule}',              [ReminderController::class, 'update'])->name('reminders.update');
        Route::post('/reminders/{schedule}/toggle',      [ReminderController::class, 'toggle'])->name('reminders.toggle');
        Route::delete('/reminders/{schedule}',            [ReminderController::class, 'destroy'])->name('reminders.destroy');
        Route::get('/reminders/logs',                    [ReminderController::class, 'logs'])->name('reminders.logs.index');
        Route::post('/reminders/{schedule}/test',        [ReminderController::class, 'testSend'])->name('reminders.test');

        // ============== ONLINE CLASSROOM ==============
        Route::get('/classroom/lessons',                 [ClassroomExtrasController::class, 'lessons'])->name('classroom.lessons.index');
        Route::post('/classroom/lessons',                [ClassroomExtrasController::class, 'storeLesson'])->name('classroom.lessons.store');
        Route::delete('/classroom/lessons/{lesson}',     [ClassroomExtrasController::class, 'deleteLesson'])->name('classroom.lessons.destroy');

        // ============== QUESTION BANK ==============
        Route::get('/qbank/categories',                  [ClassroomExtrasController::class, 'questionBankCategories'])->name('qbank.categories.index');
        Route::post('/qbank/categories',                 [ClassroomExtrasController::class, 'storeQuestionBankCategory'])->name('qbank.categories.store');
        Route::delete('/qbank/categories/{category}',    [ClassroomExtrasController::class, 'deleteQuestionBankCategory'])->name('qbank.categories.destroy');
        Route::get('/qbank/items',                       [ClassroomExtrasController::class, 'questionBankItems'])->name('qbank.items.index');
        Route::post('/qbank/items',                      [ClassroomExtrasController::class, 'storeQuestionBankItem'])->name('qbank.items.store');
        Route::delete('/qbank/items/{item}',             [ClassroomExtrasController::class, 'deleteQuestionBankItem'])->name('qbank.items.destroy');

        // ============== EXTRACURRICULAR ==============
        Route::get('/extracurricular',                   [ClassroomExtrasController::class, 'extracurriculars'])->name('extracurricular.index');
        Route::post('/extracurricular',                  [ClassroomExtrasController::class, 'storeExtracurricular'])->name('extracurricular.store');
        Route::delete('/extracurricular/{extra}',        [ClassroomExtrasController::class, 'deleteExtracurricular'])->name('extracurricular.destroy');

        // ============== CURRICULUM ==============
        Route::get('/curriculum/frameworks',             [ClassroomExtrasController::class, 'curriculumFrameworks'])->name('curriculum.frameworks.index');
        Route::post('/curriculum/frameworks',            [ClassroomExtrasController::class, 'storeCurriculumFramework'])->name('curriculum.frameworks.store');
        Route::delete('/curriculum/frameworks/{framework}', [ClassroomExtrasController::class, 'deleteCurriculumFramework'])->name('curriculum.frameworks.destroy');

        // ============== CALENDAR ==============
        Route::get('/calendar',                          [CalendarController::class, 'index'])->name('calendar.index');
        Route::get('/calendar/feed',                     [CalendarController::class, 'feed'])->name('calendar.feed');
        Route::get('/calendar/ical',                     [CalendarController::class, 'ical'])->name('calendar.ical');
        Route::post('/calendar',                         [CalendarController::class, 'store'])->name('calendar.store');
        Route::put('/calendar/{event}',                  [CalendarController::class, 'update'])->name('calendar.update');
        Route::delete('/calendar/{event}',               [CalendarController::class, 'destroy'])->name('calendar.destroy');

        // ============== OPERATIONS (Bus tracking + ID Gate) ==============
        Route::get('/operations/gate-devices',           [OperationsController::class, 'gateDevices'])->name('operations.gate-devices.index');
        Route::post('/operations/gate-devices',          [OperationsController::class, 'storeGateDevice'])->name('operations.gate-devices.store');
        Route::delete('/operations/gate-devices/{device}', [OperationsController::class, 'deleteGateDevice'])->name('operations.gate-devices.destroy');
        Route::get('/operations/gate-events',            [OperationsController::class, 'gateEvents'])->name('operations.gate-events.index');
        Route::get('/operations/vehicle-trips',          [OperationsController::class, 'vehicleTrips'])->name('operations.vehicle-trips.index');

        // Notices / Pengumuman
        Route::get('/notices',                           [NoticeWebController::class, 'index'])->name('notices.index');
        Route::get('/notices/create',                    [NoticeWebController::class, 'create'])->name('notices.create');
        Route::post('/notices',                          [NoticeWebController::class, 'store'])->name('notices.store');
        Route::get('/notices/{notice}/edit',             [NoticeWebController::class, 'edit'])->name('notices.edit');
        Route::put('/notices/{notice}',                  [NoticeWebController::class, 'update'])->name('notices.update');
        Route::delete('/notices/{notice}',               [NoticeWebController::class, 'destroy'])->name('notices.destroy');

        // Attendance
        Route::get('/attendance',                        [AttendanceWebController::class, 'index'])->name('attendance.index');
        Route::post('/attendance',                       [AttendanceWebController::class, 'save'])->name('attendance.save');
        Route::get('/attendance/recap',                  [AttendanceWebController::class, 'recap'])->name('attendance.recap');

        // QR Attendance
        Route::get('/attendance/qr',                     [WebQrAttendanceController::class, 'show'])->name('qr-attendance.show');
        Route::post('/attendance/qr/generate',           [WebQrAttendanceController::class, 'generate'])->name('qr-attendance.generate');
        Route::get('/attendance/qr/{session}/status',    [WebQrAttendanceController::class, 'status'])->name('qr-attendance.status');
        Route::post('/attendance/qr/{session}/manual',   [WebQrAttendanceController::class, 'manualOverride'])->name('qr-attendance.manual');
        Route::post('/attendance/qr/{session}/deactivate', [WebQrAttendanceController::class, 'deactivate'])->name('qr-attendance.deactivate');
        Route::get('/attendance/qr-history',             [WebQrAttendanceController::class, 'history'])->name('qr-attendance.history');
        Route::get('/attendance/qr-history/{session}',   [WebQrAttendanceController::class, 'sessionDetail'])->name('qr-attendance.session');

        // Conferences
        Route::get('/conferences',                       [ConferenceController::class, 'index'])->name('conferences.index');
        Route::get('/conferences/create',                [ConferenceController::class, 'create'])->name('conferences.create');
        Route::post('/conferences',                      [ConferenceController::class, 'store'])->name('conferences.store');
        Route::get('/conferences/{session}/edit',        [ConferenceController::class, 'edit'])->name('conferences.edit');
        Route::put('/conferences/{session}',             [ConferenceController::class, 'update'])->name('conferences.update');
        Route::delete('/conferences/{session}',          [ConferenceController::class, 'destroy'])->name('conferences.destroy');
        Route::get('/conferences/{session}/bookings',    [ConferenceController::class, 'bookings'])->name('conferences.bookings');
        Route::post('/conferences/bookings/{booking}/confirm',  [ConferenceController::class, 'confirmBooking'])->name('conferences.bookings.confirm');
        Route::post('/conferences/bookings/{booking}/cancel',   [ConferenceController::class, 'cancelBooking'])->name('conferences.bookings.cancel');
        Route::post('/conferences/bookings/{booking}/complete', [ConferenceController::class, 'completeBooking'])->name('conferences.bookings.complete');
        Route::post('/conferences/bookings/{booking}/notes',    [ConferenceController::class, 'updateBookingNotes'])->name('conferences.bookings.notes');
        Route::get('/conferences/{session}/attendance-print',   [ConferenceController::class, 'printAttendance'])->name('conferences.attendance-print');

        // Forum Management
        Route::get('/forum/categories',                  [\App\Http\Controllers\Web\Admin\Communication\ForumController::class, 'categories'])->name('forum.categories');
        Route::post('/forum/categories',                 [\App\Http\Controllers\Web\Admin\Communication\ForumController::class, 'storeCategory'])->name('forum.categories.store');
        Route::put('/forum/categories/{category}',       [\App\Http\Controllers\Web\Admin\Communication\ForumController::class, 'updateCategory'])->name('forum.categories.update');
        Route::delete('/forum/categories/{category}',    [\App\Http\Controllers\Web\Admin\Communication\ForumController::class, 'deleteCategory'])->name('forum.categories.destroy');
        Route::get('/forum/topics',                      [\App\Http\Controllers\Web\Admin\Communication\ForumController::class, 'topics'])->name('forum.topics.index');
        Route::post('/forum/topics/{topic}/pin',         [\App\Http\Controllers\Web\Admin\Communication\ForumController::class, 'togglePin'])->name('forum.topics.pin');
        Route::post('/forum/topics/{topic}/lock',        [\App\Http\Controllers\Web\Admin\Communication\ForumController::class, 'toggleLock'])->name('forum.topics.lock');
        Route::delete('/forum/topics/{topic}',           [\App\Http\Controllers\Web\Admin\Communication\ForumController::class, 'deleteTopic'])->name('forum.topics.destroy');

        // ==================== FINANCE / FEES ====================
        Route::get('/fee/structures',                    [FeeWebController::class, 'structures'])->name('fee.structures.index');
        Route::post('/fee/structures',                   [FeeWebController::class, 'storeStructure'])->name('fee.structures.store');
        Route::put('/fee/structures/{structure}',        [FeeWebController::class, 'updateStructure'])->name('fee.structures.update');
        Route::delete('/fee/structures/{structure}',     [FeeWebController::class, 'deleteStructure'])->name('fee.structures.destroy');

        Route::get('/fee/invoices',                      [FeeWebController::class, 'invoices'])->name('fee.invoices.index');
        Route::post('/fee/invoices/generate',            [FeeWebController::class, 'generateInvoices'])->name('fee.invoices.generate');
        Route::get('/fee/invoices/{invoice}',            [FeeWebController::class, 'showInvoice'])->name('fee.invoices.show');
        Route::post('/fee/invoices/{invoice}/pay',       [FeeWebController::class, 'recordPayment'])->name('fee.invoices.pay');
        Route::post('/fee/invoices/{invoice}/installments', [FeeWebController::class, 'createInstallments'])->name('fee.invoices.installments.store');
        Route::post('/fee/installments/{installment}/pay',  [FeeWebController::class, 'payInstallment'])->name('fee.installments.pay');
        Route::post('/fee/invoices/{invoice}/refund',       [FeeWebController::class, 'refund'])->name('fee.invoices.refund');
        Route::post('/fee/late-fee',                        [FeeWebController::class, 'applyLateFee'])->name('fee.late-fee');
        Route::delete('/fee/invoices/{invoice}',         [FeeWebController::class, 'deleteInvoice'])->name('fee.invoices.destroy');

        // ============== PRINT / PDF ==============
        Route::get('/print/invoice/{invoice}',           [PrintController::class, 'invoice'])->name('print.invoice');
        Route::get('/print/payment/{payment}',           [PrintController::class, 'paymentReceipt'])->name('print.payment');
        Route::get('/print/salary-slip/{slip}',          [PrintController::class, 'salarySlip'])->name('print.salary-slip');
        Route::get('/print/id-card/{student}',           [PrintController::class, 'idCard'])->name('print.id-card');
        Route::get('/print/report-card/{student}',       [PrintController::class, 'reportCard'])->name('print.report-card');
        Route::get('/print/gateway-receipt/{tx}',       [PrintController::class, 'gatewayReceipt'])->name('print.gateway-receipt');
        Route::get('/print/donation-receipt/{donation}', [PrintController::class, 'donationReceipt'])->name('print.donation-receipt');
        Route::get('/print/ppdb-acceptance/{app}',      [PrintController::class, 'ppdbAcceptance'])->name('print.ppdb-acceptance');
        Route::get('/print/certificate/{achievement}',   [PrintController::class, 'achievementCertificate'])->name('print.certificate');

        // ============== BULK IMPORT CSV ==============
        Route::get('/import',                            [BulkImportController::class, 'index'])->name('import.index');
        Route::get('/import/template/students',          [BulkImportController::class, 'templateStudents'])->name('import.template.students');
        Route::get('/import/template/staff',             [BulkImportController::class, 'templateStaff'])->name('import.template.staff');
        Route::post('/import/students',                  [BulkImportController::class, 'importStudents'])->name('import.students');
        Route::post('/import/staff',                     [BulkImportController::class, 'importStaff'])->name('import.staff');

        // ============== MISC SUB-FEATURES (P1) ==============
        Route::get('/misc/maintenance',                  [MiscCrudController::class, 'maintenance'])->name('misc.maintenance.index');
        Route::post('/misc/maintenance',                 [MiscCrudController::class, 'storeMaintenance'])->name('misc.maintenance.store');
        Route::post('/misc/maintenance/{req}/resolve',   [MiscCrudController::class, 'resolveMaintenance'])->name('misc.maintenance.resolve');

        Route::get('/misc/canteen-wallets',              [MiscCrudController::class, 'canteenWallets'])->name('misc.canteen.wallets');
        Route::post('/misc/canteen-topup',               [MiscCrudController::class, 'topupWallet'])->name('misc.canteen.topup');

        Route::get('/misc/daily-reports',                [MiscCrudController::class, 'dailyReports'])->name('misc.daily-reports');
        Route::get('/misc/career-assessments',           [MiscCrudController::class, 'careerAssessments'])->name('misc.career');
        Route::get('/misc/colleges',                     [MiscCrudController::class, 'collegeDatabase'])->name('misc.colleges');

        Route::get('/misc/internships',                  [MiscCrudController::class, 'internships'])->name('misc.internships.index');
        Route::post('/misc/internships',                 [MiscCrudController::class, 'storeInternship'])->name('misc.internships.store');

        Route::get('/misc/badges',                       [MiscCrudController::class, 'badges'])->name('misc.badges.index');
        Route::post('/misc/badges',                      [MiscCrudController::class, 'storeBadge'])->name('misc.badges.store');

        Route::get('/misc/alumni-events',                [MiscCrudController::class, 'alumniEvents'])->name('misc.alumni-events');
        Route::get('/misc/alumni-jobs',                  [MiscCrudController::class, 'alumniJobs'])->name('misc.alumni-jobs');
        Route::get('/misc/kitab-kuning',                 [MiscCrudController::class, 'kitabKuning'])->name('misc.kitab-kuning');
        Route::get('/misc/ibadah-log',                   [MiscCrudController::class, 'ibadahLog'])->name('misc.ibadah-log');
        Route::get('/misc/competencies',                 [MiscCrudController::class, 'competencies'])->name('misc.competencies');
        Route::get('/misc/live-class-attendances',       [MiscCrudController::class, 'liveClassAttendances'])->name('misc.live-class-attendances');

        Route::get('/misc/ppdb-zones',                   [MiscCrudController::class, 'ppdbZones'])->name('misc.ppdb-zones.index');
        Route::post('/misc/ppdb-zones',                  [MiscCrudController::class, 'storePpdbZone'])->name('misc.ppdb-zones.store');

        // ============== PENGADAAN ==============
        Route::get('/procurement',                          [\App\Http\Controllers\Web\Admin\Finance\ProcurementController::class, 'index'])->name('procurement.index');
        Route::get('/procurement/create',                   [\App\Http\Controllers\Web\Admin\Finance\ProcurementController::class, 'create'])->name('procurement.create');
        Route::post('/procurement',                         [\App\Http\Controllers\Web\Admin\Finance\ProcurementController::class, 'store'])->name('procurement.store');
        Route::get('/procurement/{procurement}',             [\App\Http\Controllers\Web\Admin\Finance\ProcurementController::class, 'show'])->name('procurement.show');
        Route::get('/procurement/{procurement}/edit',        [\App\Http\Controllers\Web\Admin\Finance\ProcurementController::class, 'edit'])->name('procurement.edit');
        Route::put('/procurement/{procurement}',             [\App\Http\Controllers\Web\Admin\Finance\ProcurementController::class, 'update'])->name('procurement.update');
        Route::delete('/procurement/{procurement}',          [\App\Http\Controllers\Web\Admin\Finance\ProcurementController::class, 'destroy'])->name('procurement.destroy');
        Route::post('/procurement/{procurement}/submit',     [\App\Http\Controllers\Web\Admin\Finance\ProcurementController::class, 'submit'])->name('procurement.submit');
        Route::get('/procurement/approvals',                  [\App\Http\Controllers\Web\Admin\Finance\ProcurementController::class, 'approvals'])->name('procurement.approvals');
        Route::post('/procurement/approvals/{approval}/decide', [\App\Http\Controllers\Web\Admin\Finance\ProcurementController::class, 'decideApproval'])->name('procurement.decide-approval');
        Route::post('/procurement/{procurement}/mark-ordered', [\App\Http\Controllers\Web\Admin\Finance\ProcurementController::class, 'markOrdered'])->name('procurement.mark-ordered');
        Route::post('/procurement/{procurement}/receive',      [\App\Http\Controllers\Web\Admin\Finance\ProcurementController::class, 'receiveItems'])->name('procurement.receive-items');
        Route::get('/procurement/suppliers',                    [\App\Http\Controllers\Web\Admin\Finance\ProcurementController::class, 'suppliers'])->name('procurement.suppliers');
        Route::post('/procurement/suppliers',                   [\App\Http\Controllers\Web\Admin\Finance\ProcurementController::class, 'storeSupplier'])->name('procurement.suppliers.store');
        Route::put('/procurement/suppliers/{supplier}',         [\App\Http\Controllers\Web\Admin\Finance\ProcurementController::class, 'updateSupplier'])->name('procurement.suppliers.update');
        Route::delete('/procurement/suppliers/{supplier}',      [\App\Http\Controllers\Web\Admin\Finance\ProcurementController::class, 'deleteSupplier'])->name('procurement.suppliers.delete');

        // ============== EMERGENCY ALERT SYSTEM ==============
        Route::get('/emergency',                             [\App\Http\Controllers\Web\Admin\Communication\EmergencyController::class, 'index'])->name('emergency.index');
        Route::get('/emergency/create',                      [\App\Http\Controllers\Web\Admin\Communication\EmergencyController::class, 'create'])->name('emergency.create');
        Route::post('/emergency',                            [\App\Http\Controllers\Web\Admin\Communication\EmergencyController::class, 'store'])->name('emergency.store');
        Route::post('/emergency/quick',                      [\App\Http\Controllers\Web\Admin\Communication\EmergencyController::class, 'quickAlert'])->name('emergency.quick');
        Route::post('/emergency/{alert}/cancel',             [\App\Http\Controllers\Web\Admin\Communication\EmergencyController::class, 'cancel'])->name('emergency.cancel');
        Route::get('/emergency/history',                     [\App\Http\Controllers\Web\Admin\Communication\EmergencyController::class, 'history'])->name('emergency.history');
        Route::get('/emergency/templates-by-type/{type}',    [\App\Http\Controllers\Web\Admin\Communication\EmergencyController::class, 'getTemplatesByType'])->name('emergency.templates.by-type');
        Route::get('/emergency/{alert}',                     [\App\Http\Controllers\Web\Admin\Communication\EmergencyController::class, 'show'])->name('emergency.show');
        Route::get('/emergency/contacts',                    [\App\Http\Controllers\Web\Admin\Communication\EmergencyController::class, 'contacts'])->name('emergency.contacts');
        Route::post('/emergency/contacts',                   [\App\Http\Controllers\Web\Admin\Communication\EmergencyController::class, 'storeContact'])->name('emergency.contacts.store');
        Route::post('/emergency/contacts/{contact}/update',  [\App\Http\Controllers\Web\Admin\Communication\EmergencyController::class, 'updateContact'])->name('emergency.contacts.update');
        Route::delete('/emergency/contacts/{contact}',       [\App\Http\Controllers\Web\Admin\Communication\EmergencyController::class, 'deleteContact'])->name('emergency.contacts.delete');

        // ============== PTA / KOMITE SEKOLAH ==============
        Route::get('/committee/members',                     [\App\Http\Controllers\Web\Admin\Communication\CommitteeController::class, 'members'])->name('committee.members');
        Route::post('/committee/members',                    [\App\Http\Controllers\Web\Admin\Communication\CommitteeController::class, 'storeMember'])->name('committee.members.store');
        Route::delete('/committee/members/{member}',         [\App\Http\Controllers\Web\Admin\Communication\CommitteeController::class, 'deleteMember'])->name('committee.members.delete');
        Route::get('/committee/meetings',                    [\App\Http\Controllers\Web\Admin\Communication\CommitteeController::class, 'meetings'])->name('committee.meetings');
        Route::post('/committee/meetings',                   [\App\Http\Controllers\Web\Admin\Communication\CommitteeController::class, 'storeMeeting'])->name('committee.meetings.store');
        Route::put('/committee/meetings/{meeting}',          [\App\Http\Controllers\Web\Admin\Communication\CommitteeController::class, 'updateMeeting'])->name('committee.meetings.update');
        Route::delete('/committee/meetings/{meeting}',       [\App\Http\Controllers\Web\Admin\Communication\CommitteeController::class, 'deleteMeeting'])->name('committee.meetings.delete');
        Route::get('/committee/decisions',                   [\App\Http\Controllers\Web\Admin\Communication\CommitteeController::class, 'decisions'])->name('committee.decisions');
        Route::post('/committee/decisions',                  [\App\Http\Controllers\Web\Admin\Communication\CommitteeController::class, 'storeDecision'])->name('committee.decisions.store');
        Route::delete('/committee/decisions/{decision}',     [\App\Http\Controllers\Web\Admin\Communication\CommitteeController::class, 'deleteDecision'])->name('committee.decisions.delete');
        Route::get('/committee/proposals',                   [\App\Http\Controllers\Web\Admin\Communication\CommitteeController::class, 'proposals'])->name('committee.proposals');
        Route::post('/committee/proposals',                  [\App\Http\Controllers\Web\Admin\Communication\CommitteeController::class, 'storeProposal'])->name('committee.proposals.store');
        Route::post('/committee/proposals/{proposal}/review',[\App\Http\Controllers\Web\Admin\Communication\CommitteeController::class, 'reviewProposal'])->name('committee.proposals.review');
        Route::delete('/committee/proposals/{proposal}',     [\App\Http\Controllers\Web\Admin\Communication\CommitteeController::class, 'deleteProposal'])->name('committee.proposals.delete');

        // ============== OSIS MANAGER ==============
        Route::get('/osis',                                  [\App\Http\Controllers\Web\Admin\Academic\OsisController::class, 'index'])->name('osis.index');
        Route::post('/osis',                                 [\App\Http\Controllers\Web\Admin\Academic\OsisController::class, 'storeElection'])->name('osis.store');
        Route::put('/osis/{election}',                       [\App\Http\Controllers\Web\Admin\Academic\OsisController::class, 'updateElection'])->name('osis.update');
        Route::delete('/osis/{election}',                    [\App\Http\Controllers\Web\Admin\Academic\OsisController::class, 'deleteElection'])->name('osis.delete');
        Route::get('/osis/{election}/candidates',            [\App\Http\Controllers\Web\Admin\Academic\OsisController::class, 'candidates'])->name('osis.candidates');
        Route::post('/osis/{election}/candidates',           [\App\Http\Controllers\Web\Admin\Academic\OsisController::class, 'storeCandidate'])->name('osis.candidates.store');
        Route::post('/osis/candidates/{candidate}/approve',  [\App\Http\Controllers\Web\Admin\Academic\OsisController::class, 'approveCandidate'])->name('osis.candidates.approve');
        Route::post('/osis/candidates/{candidate}/disqualify',[\App\Http\Controllers\Web\Admin\Academic\OsisController::class, 'disqualifyCandidate'])->name('osis.candidates.disqualify');
        Route::delete('/osis/candidates/{candidate}',        [\App\Http\Controllers\Web\Admin\Academic\OsisController::class, 'deleteCandidate'])->name('osis.candidates.delete');
        Route::get('/osis/{election}/results',               [\App\Http\Controllers\Web\Admin\Academic\OsisController::class, 'results'])->name('osis.results');
        Route::get('/osis/{election}/live-votes',            [\App\Http\Controllers\Web\Admin\Academic\OsisController::class, 'liveVotes'])->name('osis.live-votes');
        Route::post('/osis/{election}/finalize',             [\App\Http\Controllers\Web\Admin\Academic\OsisController::class, 'finalizeResults'])->name('osis.finalize');
        Route::get('/osis-programs',                         [\App\Http\Controllers\Web\Admin\Academic\OsisController::class, 'programs'])->name('osis.programs');
        Route::post('/osis-programs',                        [\App\Http\Controllers\Web\Admin\Academic\OsisController::class, 'storeProgram'])->name('osis.programs.store');
        Route::put('/osis-programs/{program}',               [\App\Http\Controllers\Web\Admin\Academic\OsisController::class, 'updateProgram'])->name('osis.programs.update');
        Route::delete('/osis-programs/{program}',            [\App\Http\Controllers\Web\Admin\Academic\OsisController::class, 'deleteProgram'])->name('osis.programs.delete');

        // ============== BUDGET / RKAS ==============
        Route::get('/budget/dashboard',                  [BudgetController::class, 'dashboard'])->name('budget.dashboard');
        Route::get('/budget/export',                     [BudgetController::class, 'export'])->name('budget.export');
        Route::get('/budget/categories',                 [BudgetController::class, 'categories'])->name('budget.categories.index');
        Route::post('/budget/categories',                [BudgetController::class, 'storeCategory'])->name('budget.categories.store');
        Route::put('/budget/categories/{category}',      [BudgetController::class, 'updateCategory'])->name('budget.categories.update');
        Route::delete('/budget/categories/{category}',   [BudgetController::class, 'deleteCategory'])->name('budget.categories.destroy');
        Route::get('/budget/items',                      [BudgetController::class, 'items'])->name('budget.items.index');
        Route::post('/budget/items',                     [BudgetController::class, 'storeItem'])->name('budget.items.store');
        Route::put('/budget/items/{item}',               [BudgetController::class, 'updateItem'])->name('budget.items.update');
        Route::delete('/budget/items/{item}',            [BudgetController::class, 'deleteItem'])->name('budget.items.destroy');
        Route::post('/budget/items/{item}/toggle',       [BudgetController::class, 'toggleStatusItem'])->name('budget.items.toggle');
        Route::get('/budget/transactions',               [BudgetController::class, 'transactions'])->name('budget.transactions.index');
        Route::post('/budget/transactions',              [BudgetController::class, 'storeTransaction'])->name('budget.transactions.store');
        Route::delete('/budget/transactions/{transaction}', [BudgetController::class, 'deleteTransaction'])->name('budget.transactions.destroy');

        // Finance reports (sekolah)
        Route::get('/finance/reports',                   [FinanceReportController::class, 'summary'])->name('finance.reports.summary');
        Route::get('/finance/reports/outstanding',       [FinanceReportController::class, 'outstanding'])->name('finance.reports.outstanding');
        Route::get('/finance/reports/export',            [FinanceReportController::class, 'exportCsv'])->name('finance.reports.export');

        // ============== AKUNTANSI (COA + Jurnal + Laporan) ==============
        Route::get('/accounting/coa',                    [AccountingController::class, 'coa'])->name('accounting.coa');
        Route::post('/accounting/coa',                   [AccountingController::class, 'storeAccount'])->name('accounting.coa.store');
        Route::put('/accounting/coa/{account}',          [AccountingController::class, 'updateAccount'])->name('accounting.coa.update');
        Route::delete('/accounting/coa/{account}',       [AccountingController::class, 'deleteAccount'])->name('accounting.coa.destroy');
        Route::post('/accounting/coa/seed',              [AccountingController::class, 'seedCoa'])->name('accounting.coa.seed');
        Route::get('/accounting/journal',                [AccountingController::class, 'journal'])->name('accounting.journal.index');
        Route::post('/accounting/journal',               [AccountingController::class, 'storeJournal'])->name('accounting.journal.store');
        Route::get('/accounting/journal/{entry}',        [AccountingController::class, 'showJournal'])->name('accounting.journal.show');
        Route::post('/accounting/journal/{entry}/post',  [AccountingController::class, 'postJournal'])->name('accounting.journal.post');
        Route::delete('/accounting/journal/{entry}',     [AccountingController::class, 'deleteJournal'])->name('accounting.journal.destroy');
        Route::get('/accounting/trial-balance',          [AccountingController::class, 'trialBalance'])->name('accounting.trial-balance');
        Route::get('/accounting/profit-loss',            [AccountingController::class, 'profitLoss'])->name('accounting.profit-loss');
        Route::get('/accounting/balance-sheet',          [AccountingController::class, 'balanceSheet'])->name('accounting.balance-sheet');

        // ============== KOPERASI ==============
        Route::get('/cooperative',                       [CooperativeController::class, 'dashboard'])->name('cooperative.dashboard');
        Route::get('/cooperative/members',               [CooperativeController::class, 'members'])->name('cooperative.members');
        Route::post('/cooperative/members',              [CooperativeController::class, 'storeMember'])->name('cooperative.members.store');
        Route::put('/cooperative/members/{member}',      [CooperativeController::class, 'updateMember'])->name('cooperative.members.update');
        Route::delete('/cooperative/members/{member}',   [CooperativeController::class, 'deleteMember'])->name('cooperative.members.delete');
        Route::get('/cooperative/savings',               [CooperativeController::class, 'savings'])->name('cooperative.savings');
        Route::post('/cooperative/savings',              [CooperativeController::class, 'storeSaving'])->name('cooperative.savings.store');
        Route::delete('/cooperative/savings/{saving}',   [CooperativeController::class, 'deleteSaving'])->name('cooperative.savings.delete');
        Route::get('/cooperative/loans',                 [CooperativeController::class, 'loans'])->name('cooperative.loans');
        Route::post('/cooperative/loans',                [CooperativeController::class, 'storeLoan'])->name('cooperative.loans.store');
        Route::post('/cooperative/loans/{loan}/approve', [CooperativeController::class, 'approveLoan'])->name('cooperative.loans.approve');
        Route::post('/cooperative/loans/{loan}/reject',  [CooperativeController::class, 'rejectLoan'])->name('cooperative.loans.reject');
        Route::delete('/cooperative/loans/{loan}',       [CooperativeController::class, 'deleteLoan'])->name('cooperative.loans.delete');
        Route::post('/cooperative/installments/{installment}/pay', [CooperativeController::class, 'payInstallment'])->name('cooperative.installments.pay');
        Route::get('/cooperative/shu-report',             [CooperativeController::class, 'shuReport'])->name('cooperative.shu-report');

        // ============== GLOBAL SEARCH (Cmd+K) ==============
        Route::get('/search',                            [\App\Http\Controllers\Web\Admin\Search\GlobalSearchController::class, 'search'])->name('search');

        // ============== WORKFLOW / APPROVAL ==============
        Route::get('/workflow',                            [WorkflowController::class, 'index'])->name('workflow.index');
        Route::get('/workflow/create',                     [WorkflowController::class, 'create'])->name('workflow.create');
        Route::post('/workflow',                           [WorkflowController::class, 'store'])->name('workflow.store');
        Route::get('/workflow/{workflowRequest}',          [WorkflowController::class, 'show'])->name('workflow.show');
        Route::post('/workflow/{workflowRequest}/approve', [WorkflowController::class, 'approve'])->name('workflow.approve');
        Route::post('/workflow/{workflowRequest}/reject',  [WorkflowController::class, 'reject'])->name('workflow.reject');

        // ============== BLOG ==============
        Route::get('/blog',                              [\App\Http\Controllers\Web\Admin\BlogController::class, 'index'])->name('blog.index');
        Route::get('/blog/create',                       [\App\Http\Controllers\Web\Admin\BlogController::class, 'create'])->name('blog.create');
        Route::post('/blog',                             [\App\Http\Controllers\Web\Admin\BlogController::class, 'store'])->name('blog.store');
        Route::get('/blog/{post}/edit',                  [\App\Http\Controllers\Web\Admin\BlogController::class, 'edit'])->name('blog.edit');
        Route::put('/blog/{post}',                       [\App\Http\Controllers\Web\Admin\BlogController::class, 'update'])->name('blog.update');
        Route::delete('/blog/{post}',                    [\App\Http\Controllers\Web\Admin\BlogController::class, 'destroy'])->name('blog.destroy');
        Route::get('/blog-categories',                   [\App\Http\Controllers\Web\Admin\BlogController::class, 'categories'])->name('blog.categories.index');
        Route::post('/blog-categories',                  [\App\Http\Controllers\Web\Admin\BlogController::class, 'storeCategory'])->name('blog.categories.store');
        Route::put('/blog-categories/{category}',        [\App\Http\Controllers\Web\Admin\BlogController::class, 'updateCategory'])->name('blog.categories.update');
        Route::delete('/blog-categories/{category}',     [\App\Http\Controllers\Web\Admin\BlogController::class, 'destroyCategory'])->name('blog.categories.destroy');

        // ============== BULK ACTIONS ==============
        Route::post('/bulk/students',  [\App\Http\Controllers\Web\Admin\Bulk\BulkActionController::class, 'studentsBulk'])->name('bulk.students');
        Route::post('/bulk/staff',     [\App\Http\Controllers\Web\Admin\Bulk\BulkActionController::class, 'staffBulk'])->name('bulk.staff');
        Route::post('/bulk/invoices',  [\App\Http\Controllers\Web\Admin\Bulk\BulkActionController::class, 'invoicesBulk'])->name('bulk.invoices');
        Route::post('/bulk/notices',   [\App\Http\Controllers\Web\Admin\Bulk\BulkActionController::class, 'noticesBulk'])->name('bulk.notices');

        // ============== ADVANCED REPORTS ==============
        Route::get('/reports/spp-aging',                 [AdvancedReportsController::class, 'sppAging'])->name('reports.spp-aging');
        Route::get('/reports/attendance-pct',            [AdvancedReportsController::class, 'attendancePercent'])->name('reports.attendance-pct');
        Route::get('/reports/grade-distribution',        [AdvancedReportsController::class, 'gradeDistribution'])->name('reports.grade-distribution');
        Route::get('/reports/discipline-leaderboard',    [AdvancedReportsController::class, 'disciplineLeaderboard'])->name('reports.discipline-leaderboard');
        Route::get('/reports/cash-flow',                 [AdvancedReportsController::class, 'cashFlow'])->name('reports.cash-flow');

        // ============== REPORT BUILDER (Module 10) ==============
        Route::get('/reports/builder',                        [ReportBuilderController::class, 'index'])->name('reports.builder.index');
        Route::post('/reports/builder/preview',               [ReportBuilderController::class, 'preview'])->name('reports.builder.preview');
        Route::post('/reports/builder/export-csv',            [ReportBuilderController::class, 'exportCsv'])->name('reports.builder.export-csv');
        Route::post('/reports/builder/export-pdf',            [ReportBuilderController::class, 'exportPdf'])->name('reports.builder.export-pdf');
        Route::get('/reports/builder/download',               [ReportBuilderController::class, 'download'])->name('reports.builder.download');
        Route::post('/reports/builder/templates',             [ReportBuilderController::class, 'saveTemplate'])->name('reports.builder.save-template');
        Route::put('/reports/builder/templates/{template}',   [ReportBuilderController::class, 'updateTemplate'])->name('reports.builder.update-template');
        Route::delete('/reports/builder/templates/{template}', [ReportBuilderController::class, 'deleteTemplate'])->name('reports.builder.delete-template');
        Route::get('/reports/builder/templates/list',         [ReportBuilderController::class, 'templates'])->name('reports.builder.templates');

        // ============== BENCHMARK YAYASAN (Module 11) ==============
        Route::get('/foundation/benchmark',                   [FoundationBenchmarkController::class, 'index'])->name('foundation.benchmark.index');
        Route::get('/foundation/benchmark/trend',             [FoundationBenchmarkController::class, 'trend'])->name('foundation.benchmark.trend');

        // ============== SURVEYS ==============
        Route::get('/surveys/templates',                 [SurveyController::class, 'templates'])->name('surveys.templates.index');
        Route::post('/surveys/templates',                [SurveyController::class, 'storeTemplate'])->name('surveys.templates.store');
        Route::put('/surveys/templates/{template}',      [SurveyController::class, 'updateTemplate'])->name('surveys.templates.update');
        Route::delete('/surveys/templates/{template}',   [SurveyController::class, 'deleteTemplate'])->name('surveys.templates.destroy');
        Route::get('/surveys/templates/{template}/questions',  [SurveyController::class, 'questions'])->name('surveys.questions');
        Route::post('/surveys/templates/{template}/questions', [SurveyController::class, 'storeQuestion'])->name('surveys.questions.store');
        Route::put('/surveys/templates/{template}/questions/{question}',  [SurveyController::class, 'updateQuestion'])->name('surveys.questions.update');
        Route::delete('/surveys/templates/{template}/questions/{question}', [SurveyController::class, 'deleteQuestion'])->name('surveys.questions.destroy');
        Route::get('/surveys/templates/{template}/responses',  [SurveyController::class, 'responses'])->name('surveys.responses');
        Route::delete('/surveys/templates/{template}/responses/{response}', [SurveyController::class, 'deleteResponse'])->name('surveys.responses.destroy');
        Route::get('/surveys/templates/{template}/analytics',  [SurveyController::class, 'analytics'])->name('surveys.analytics');

        // ============== RAPORT INTERAKTIF ==============
        Route::get('/academic/raport-interaktif',        [InteractiveRaportController::class, 'index'])->name('raport-interaktif.index');

        // ============== E-PORTFOLIO ==============
        Route::get('/portfolios',                        [PortfolioController::class, 'index'])->name('portfolios.index');
        Route::post('/portfolios',                       [PortfolioController::class, 'store'])->name('portfolios.store');
        Route::put('/portfolios/{portfolio}',            [PortfolioController::class, 'update'])->name('portfolios.update');
        Route::post('/portfolios/{portfolio}/approve',   [PortfolioController::class, 'approve'])->name('portfolios.approve');
        Route::post('/portfolios/{portfolio}/reject',    [PortfolioController::class, 'reject'])->name('portfolios.reject');
        Route::delete('/portfolios/{portfolio}',         [PortfolioController::class, 'destroy'])->name('portfolios.destroy');

        // Staff
        Route::get('/staff',                             [StaffWebController::class, 'index'])->name('staff.index');
        Route::get('/staff/create',                      [StaffWebController::class, 'create'])->name('staff.create');
        Route::post('/staff',                            [StaffWebController::class, 'store'])->name('staff.store');
        Route::get('/staff/{staff}/edit',                [StaffWebController::class, 'edit'])->name('staff.edit');
        Route::put('/staff/{staff}',                     [StaffWebController::class, 'update'])->name('staff.update');
        Route::delete('/staff/{staff}',                  [StaffWebController::class, 'destroy'])->name('staff.destroy');

        // Payment Providers
        Route::get('/payment/providers',                 [PaymentProviderWebController::class, 'index'])->name('payment.providers.index');
        Route::get('/payment/providers/create',          [PaymentProviderWebController::class, 'create'])->name('payment.providers.create');
        Route::post('/payment/providers',                [PaymentProviderWebController::class, 'store'])->name('payment.providers.store');
        Route::get('/payment/providers/{id}/edit',       [PaymentProviderWebController::class, 'edit'])->name('payment.providers.edit');
        Route::put('/payment/providers/{id}',            [PaymentProviderWebController::class, 'update'])->name('payment.providers.update');
        Route::delete('/payment/providers/{id}',         [PaymentProviderWebController::class, 'destroy'])->name('payment.providers.destroy');

        // Payment Methods
        Route::get('/payment/methods',                   [PaymentMethodWebController::class, 'index'])->name('payment.methods.index');
        Route::get('/payment/methods/create',            [PaymentMethodWebController::class, 'create'])->name('payment.methods.create');
        Route::post('/payment/methods',                  [PaymentMethodWebController::class, 'store'])->name('payment.methods.store');
        Route::get('/payment/methods/{id}/edit',         [PaymentMethodWebController::class, 'edit'])->name('payment.methods.edit');
        Route::put('/payment/methods/{id}',              [PaymentMethodWebController::class, 'update'])->name('payment.methods.update');
        Route::delete('/payment/methods/{id}',           [PaymentMethodWebController::class, 'destroy'])->name('payment.methods.destroy');

        // Branding
        Route::get('/branding',                          [BrandingWebController::class, 'show'])->name('branding.show');
        Route::put('/branding',                          [BrandingWebController::class, 'update'])->name('branding.update');
        Route::post('/branding/upload-logo',             [BrandingWebController::class, 'uploadLogo'])->name('branding.upload-logo');
        Route::delete('/branding/logo/{type}',           [BrandingWebController::class, 'removeLogo'])->name('branding.remove-logo');
        Route::post('/branding/reset',                   [BrandingWebController::class, 'reset'])->name('branding.reset');

        // ============== WEBSITE BUILDER ==============
        Route::prefix('branding/website')->name('branding.website.')->group(function () {
            Route::get('/pages',                         [\App\Http\Controllers\Web\Admin\Branding\WebsiteBuilderController::class, 'pages'])->name('pages');
            Route::post('/pages',                        [\App\Http\Controllers\Web\Admin\Branding\WebsiteBuilderController::class, 'storePage'])->name('page.store');
            Route::put('/pages/{page}',                  [\App\Http\Controllers\Web\Admin\Branding\WebsiteBuilderController::class, 'updatePage'])->name('page.update');
            Route::delete('/pages/{page}',               [\App\Http\Controllers\Web\Admin\Branding\WebsiteBuilderController::class, 'deletePage'])->name('page.destroy');
            Route::get('/pages/{page}/builder',          [\App\Http\Controllers\Web\Admin\Branding\WebsiteBuilderController::class, 'builder'])->name('builder');
            Route::post('/pages/{page}/sections',        [\App\Http\Controllers\Web\Admin\Branding\WebsiteBuilderController::class, 'storeSection'])->name('section.store');
            Route::put('/section/{section}/update',      [\App\Http\Controllers\Web\Admin\Branding\WebsiteBuilderController::class, 'updateSection'])->name('section.update');
            Route::delete('/section/{section}',          [\App\Http\Controllers\Web\Admin\Branding\WebsiteBuilderController::class, 'deleteSection'])->name('section.destroy');
            Route::post('/pages/{page}/sections/reorder',[\App\Http\Controllers\Web\Admin\Branding\WebsiteBuilderController::class, 'reorderSections'])->name('sections.reorder');
            Route::post('/section/{section}/upload-image',[\App\Http\Controllers\Web\Admin\Branding\WebsiteBuilderController::class, 'uploadSectionImage'])->name('section.upload-image');
            Route::get('/gallery',                       [\App\Http\Controllers\Web\Admin\Branding\WebsiteBuilderController::class, 'gallery'])->name('gallery');
            Route::post('/gallery',                      [\App\Http\Controllers\Web\Admin\Branding\WebsiteBuilderController::class, 'storeGallery'])->name('gallery.store');
            Route::put('/gallery/{gallery}/update',      [\App\Http\Controllers\Web\Admin\Branding\WebsiteBuilderController::class, 'updateGallery'])->name('gallery.update');
            Route::delete('/gallery/{gallery}',          [\App\Http\Controllers\Web\Admin\Branding\WebsiteBuilderController::class, 'deleteGallery'])->name('gallery.destroy');
            Route::get('/testimonials',                  [\App\Http\Controllers\Web\Admin\Branding\WebsiteBuilderController::class, 'testimonials'])->name('testimonials');
            Route::post('/testimonials',                 [\App\Http\Controllers\Web\Admin\Branding\WebsiteBuilderController::class, 'storeTestimonial'])->name('testimonials.store');
            Route::put('/testimonials/{testimonial}/update',[\App\Http\Controllers\Web\Admin\Branding\WebsiteBuilderController::class, 'updateTestimonial'])->name('testimonials.update');
            Route::delete('/testimonials/{testimonial}', [\App\Http\Controllers\Web\Admin\Branding\WebsiteBuilderController::class, 'deleteTestimonial'])->name('testimonials.destroy');
            Route::get('/contacts',                      [\App\Http\Controllers\Web\Admin\Branding\WebsiteBuilderController::class, 'contacts'])->name('contacts');
            Route::post('/contacts/{contact}/read',      [\App\Http\Controllers\Web\Admin\Branding\WebsiteBuilderController::class, 'markContactRead'])->name('contacts.read');
            Route::delete('/contacts/{contact}',         [\App\Http\Controllers\Web\Admin\Branding\WebsiteBuilderController::class, 'deleteContact'])->name('contacts.destroy');
        });

        // ============== DIGITAL SIGNAGE ==============
        Route::get('/signage/config',                    [DigitalSignageController::class, 'config'])->name('signage.config');
        Route::post('/signage/config',                   [DigitalSignageController::class, 'saveConfig'])->name('signage.config.save');

        // Dashboard TV
        Route::get('/dashboard-tv/config',               [DashboardTvController::class, 'config'])->name('dashboard-tv.config');
        Route::post('/dashboard-tv/config',              [DashboardTvController::class, 'saveConfig'])->name('dashboard-tv.config.save');

        // ============== AKREDITASI ==============
        Route::get('/accreditation',                    [AccreditationController::class, 'dashboard'])->name('accreditation.dashboard');
        Route::get('/accreditation/instruments',        [AccreditationController::class, 'instruments'])->name('accreditation.instruments');
        Route::post('/accreditation/scores',            [AccreditationController::class, 'saveScore'])->name('accreditation.scores.save');
        Route::get('/accreditation/documents',          [AccreditationController::class, 'documents'])->name('accreditation.documents');
        Route::post('/accreditation/documents/upload',  [AccreditationController::class, 'uploadDocument'])->name('accreditation.documents.upload');
        Route::get('/accreditation/action-plans',       [AccreditationController::class, 'actionPlans'])->name('accreditation.action-plans');
        Route::post('/accreditation/action-plans',      [AccreditationController::class, 'storeActionPlan'])->name('accreditation.action-plans.store');
        Route::post('/accreditation/action-plans/{plan}/status', [AccreditationController::class, 'updateActionPlanStatus'])->name('accreditation.action-plans.status');
        Route::delete('/accreditation/action-plans/{plan}', [AccreditationController::class, 'deleteActionPlan'])->name('accreditation.action-plans.destroy');
        Route::post('/accreditation/documents/{document}/review', [AccreditationController::class, 'reviewDocument'])->name('accreditation.documents.review');
        Route::delete('/accreditation/documents/{document}', [AccreditationController::class, 'deleteDocument'])->name('accreditation.documents.destroy');
        Route::get('/accreditation/print-summary',      [AccreditationController::class, 'printSummary'])->name('accreditation.print-summary');

        // ============== ADIWIYATA ==============
        Route::get('/adiwiyata',                         [AdiwiyataController::class, 'dashboard'])->name('adiwiyata.dashboard');
        Route::get('/adiwiyata/indicators',              [AdiwiyataController::class, 'indicators'])->name('adiwiyata.indicators');
        Route::get('/adiwiyata/indicators/{indicator}/evidence', [AdiwiyataController::class, 'evidence'])->name('adiwiyata.evidence');
        Route::post('/adiwiyata/indicators/{indicator}/evidence', [AdiwiyataController::class, 'storeEvidence'])->name('adiwiyata.evidence.store');
        Route::post('/adiwiyata/evidence/{evidence}/verify', [AdiwiyataController::class, 'verifyEvidence'])->name('adiwiyata.evidence.verify');
        Route::post('/adiwiyata/evidence/{evidence}/reject', [AdiwiyataController::class, 'rejectEvidence'])->name('adiwiyata.evidence.reject');
        Route::delete('/adiwiyata/evidence/{evidence}',  [AdiwiyataController::class, 'deleteEvidence'])->name('adiwiyata.evidence.delete');
        Route::post('/adiwiyata/levels',                  [AdiwiyataController::class, 'storeLevel'])->name('adiwiyata.levels.store');

        // ==================== PHASE 8-11 DASHBOARDS (overview, prefixed /dash/) ====================
        Route::get('/dash/ppdb',                         [Phase8WebController::class, 'ppdbDashboard'])->name('ppdb.dashboard');
        Route::get('/dash/transport',                    [Phase8WebController::class, 'transportDashboard'])->name('transport.dashboard');
        Route::get('/dash/medical',                      [Phase8WebController::class, 'clinicDashboard'])->name('medical.dashboard');
        Route::get('/dash/counseling',                   [Phase8WebController::class, 'counselingDashboard'])->name('counseling.dashboard');
        Route::get('/dash/discipline',                   [Phase8WebController::class, 'disciplineDashboard'])->name('discipline.dashboard');
        Route::get('/dash/lesson-plan',                  [Phase9WebController::class, 'lessonPlan'])->name('lesson-plan.dashboard');
        Route::get('/dash/canteen',                      [Phase9WebController::class, 'canteen'])->name('canteen.dashboard');
        Route::get('/dash/religious',                    [Phase9WebController::class, 'religious'])->name('religious.dashboard');
        Route::get('/dash/ai',                           [Phase9WebController::class, 'ai'])->name('ai.dashboard');
        Route::get('/dash/live-class',                   [Phase9WebController::class, 'liveClass'])->name('live-class.dashboard');
        Route::get('/dash/donations',                    [Phase10WebController::class, 'donations'])->name('donations.dashboard');
        Route::get('/dash/achievements',                 [Phase10WebController::class, 'achievements'])->name('achievements.dashboard');
        Route::get('/dash/scholarship',                  [Phase10WebController::class, 'scholarship'])->name('scholarship.dashboard');
        Route::get('/dash/events',                       [Phase10WebController::class, 'events'])->name('events.dashboard');
        Route::get('/dash/dapodik',                      [Phase11WebController::class, 'dapodik'])->name('dapodik.dashboard');
        Route::get('/dash/visitors',                     [Phase11WebController::class, 'visitors'])->name('visitors.dashboard');
        Route::get('/dash/inventory',                    [Phase11WebController::class, 'inventory'])->name('inventory.dashboard');
        Route::get('/dash/analytics',                    [Phase11WebController::class, 'analytics'])->name('analytics.dashboard');
    });
});

// Parent / Student web payment portal
Route::prefix('portal')->name('portal.')->middleware(['auth'])->group(function () {
    Route::get('/',                                      [\App\Http\Controllers\Web\Parent\ParentPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/anak/{student}',                        [\App\Http\Controllers\Web\Parent\ParentPortalController::class, 'child'])->name('child');
    Route::get('/anak/{student}/absensi',                [\App\Http\Controllers\Web\Parent\ParentPortalController::class, 'childAttendance'])->name('child.attendance');
    Route::get('/anak/{student}/nilai',                  [\App\Http\Controllers\Web\Parent\ParentPortalController::class, 'childMarks'])->name('child.marks');
    Route::get('/anak/{student}/uks',                    [\App\Http\Controllers\Web\Parent\ParentPortalController::class, 'childHealth'])->name('child.health');
    Route::get('/anak/{student}/disiplin',               [\App\Http\Controllers\Web\Parent\ParentPortalController::class, 'childDiscipline'])->name('child.discipline');
    Route::get('/anak/{student}/prestasi',               [\App\Http\Controllers\Web\Parent\ParentPortalController::class, 'childAchievements'])->name('child.achievements');
    Route::get('/anak/{student}/konseling',              [\App\Http\Controllers\Web\Parent\ParentPortalController::class, 'childCounseling'])->name('child.counseling');
    Route::get('/anak/{student}/aktivitas',              [\App\Http\Controllers\Web\Parent\ParentPortalController::class, 'childActivity'])->name('child.activity');

    Route::get('/anak/{student}/raport-interaktif',  [InteractiveRaportController::class, 'parentView'])->name('child.raport-interaktif');

    Route::get('/invoices',                              [ParentPaymentController::class, 'invoices'])->name('invoices');
    Route::get('/invoices/{invoiceId}/pay',              [ParentPaymentController::class, 'choose'])->name('invoices.pay');
    Route::post('/invoices/{invoiceId}/initiate',        [ParentPaymentController::class, 'initiate'])->name('invoices.initiate');
    Route::get('/payments/{referenceNo}',                [ParentPaymentController::class, 'show'])->name('payments.show');
    Route::post('/payments/{referenceNo}/cancel',        [ParentPaymentController::class, 'cancel'])->name('payments.cancel');

    // Parent Surveys
    Route::get('/surveys',                               [SurveyController::class, 'parentFill'])->name('surveys');
    Route::get('/surveys/{template}/isi',                [SurveyController::class, 'parentDoFill'])->name('surveys.fill');
    Route::post('/surveys/{template}/submit',            [SurveyController::class, 'parentSubmit'])->name('surveys.submit');

        // Parent Conference
        Route::get('/conferences',                           [\App\Http\Controllers\Web\Portal\ConferencePortalController::class, 'index'])->name('conferences');
        Route::post('/conferences/{session}/book',           [\App\Http\Controllers\Web\Portal\ConferencePortalController::class, 'book'])->name('conferences.book');
        Route::post('/conferences/bookings/{booking}/cancel',[\App\Http\Controllers\Web\Portal\ConferencePortalController::class, 'cancel'])->name('conferences.cancel');

        // Komite Sekolah
        Route::get('/komite',                                [\App\Http\Controllers\Web\Parent\CommitteePortalController::class, 'index'])->name('committee');
        Route::get('/komite/{id}',                            [\App\Http\Controllers\Web\Parent\CommitteePortalController::class, 'showMeeting'])->name('committee.meeting');
    });

Route::get('/payment/return', [ParentPaymentController::class, 'returnFromGateway'])->name('payment.return');

// ============================================================
// API Documentation (publik untuk developer/integrator)
// ============================================================
Route::get('/api-docs',              [\App\Http\Controllers\Web\ApiDocsController::class, 'index'])->name('api.docs');
Route::get('/api-docs/openapi.json', [\App\Http\Controllers\Web\ApiDocsController::class, 'spec'])->name('api.docs.spec');

// ============================================================
// Profile (untuk semua user yang login)
// ============================================================
Route::middleware('auth')->prefix('profile')->name('profile.')->group(function () {
    Route::get('/',           [\App\Http\Controllers\Web\Profile\ProfileController::class, 'edit'])->name('edit');
    Route::put('/',           [\App\Http\Controllers\Web\Profile\ProfileController::class, 'update'])->name('update');
    Route::post('/password',  [\App\Http\Controllers\Web\Profile\ProfileController::class, 'changePassword'])->name('password');
});

// ============================================================
// Student Portal
// ============================================================
Route::prefix('siswa')->name('student.')->middleware(['auth', 'role:student'])->group(function () {
    Route::get('/',            [\App\Http\Controllers\Web\Student\StudentPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/jadwal',      [\App\Http\Controllers\Web\Student\StudentPortalController::class, 'schedule'])->name('schedule');
    Route::get('/nilai',       [\App\Http\Controllers\Web\Student\StudentPortalController::class, 'marks'])->name('marks');
    Route::get('/absensi',     [\App\Http\Controllers\Web\Student\StudentPortalController::class, 'attendance'])->name('attendance');
    Route::get('/materi',      [\App\Http\Controllers\Web\Student\StudentPortalController::class, 'lessons'])->name('lessons');
    Route::get('/tugas',       [\App\Http\Controllers\Web\Student\StudentPortalController::class, 'assignments'])->name('assignments');
    Route::get('/tugas/{assignment}/kerjakan', [AssignmentController::class, 'doAssignment'])->name('assignments.do');
    Route::post('/tugas/{assignment}/kumpulkan', [AssignmentController::class, 'submitAssignment'])->name('assignments.submit');
    Route::get('/leaderboard', [\App\Http\Controllers\Web\Student\StudentPortalController::class, 'leaderboard'])->name('leaderboard');
    Route::get('/aktivitas',  [\App\Http\Controllers\Web\Student\StudentPortalController::class, 'activity'])->name('activity');

    Route::get('/perpustakaan-digital', [\App\Http\Controllers\Web\Student\StudentPortalController::class, 'digitalLibrary'])->name('digital-library');

    // Student Surveys
    Route::get('/survei',                              [SurveyController::class, 'studentFill'])->name('surveys');
    Route::get('/survei/{template}/isi',               [SurveyController::class, 'studentDoFill'])->name('surveys.fill');
    Route::post('/survei/{template}/submit',           [SurveyController::class, 'studentSubmit'])->name('surveys.submit');

    // Student e-Portfolio
    Route::get('/portofolio',                          [PortfolioController::class, 'studentIndex'])->name('portfolios');
    Route::post('/portofolio',                         [PortfolioController::class, 'studentStore'])->name('portfolios.store');
    Route::delete('/portofolio/{portfolio}',           [PortfolioController::class, 'studentDestroy'])->name('portfolios.destroy');

        // Student QR Attendance
        Route::get('/absensi-qr',                          [\App\Http\Controllers\Web\Student\StudentPortalController::class, 'qrAttendance'])->name('qr-attendance');

        // BKK / Bursa Kerja
        Route::get('/bkk',                                   [BkkStudentController::class, 'index'])->name('bkk.index');
        Route::post('/bkk/apply',                            [BkkStudentController::class, 'apply'])->name('bkk.apply');

        // OSIS
        Route::get('/osis',                                [\App\Http\Controllers\Web\Student\OsisStudentController::class, 'election'])->name('osis');
        Route::post('/osis/{election}/vote',               [\App\Http\Controllers\Web\Student\OsisStudentController::class, 'castVote'])->name('osis.vote');
        Route::get('/osis/{electionId}/hasil',             [\App\Http\Controllers\Web\Student\OsisStudentController::class, 'results'])->name('osis.results');
        Route::get('/osis/program',                        [\App\Http\Controllers\Web\Student\OsisStudentController::class, 'programs'])->name('osis.programs');
        Route::post('/osis/program',                       [\App\Http\Controllers\Web\Student\OsisStudentController::class, 'proposeProgram'])->name('osis.programs.propose');
    });

// ============================================================
// Teacher Portal
// ============================================================
Route::prefix('guru')->name('teacher.')->middleware(['auth', 'role:teacher|admin'])->group(function () {
    Route::get('/',                            [\App\Http\Controllers\Web\Teacher\TeacherDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/rombel/{classSection}',       [\App\Http\Controllers\Web\Teacher\TeacherDashboardController::class, 'myClass'])->name('my-class');

    Route::get('/room-booking',                [\App\Http\Controllers\Web\Teacher\RoomBookingController::class, 'index'])->name('room-booking');
    Route::get('/room-booking/feed',           [\App\Http\Controllers\Web\Teacher\RoomBookingController::class, 'calendarFeed'])->name('room-booking.calendar.feed');
    Route::post('/room-booking',               [\App\Http\Controllers\Web\Teacher\RoomBookingController::class, 'store'])->name('room-booking.store');
    Route::post('/room-booking/{bookingId}/cancel', [\App\Http\Controllers\Web\Teacher\RoomBookingController::class, 'cancel'])->name('room-booking.cancel');
});

// ============================================================
// Public Portfolio Share
// ============================================================
Route::get('/portfolio/{token}', [\App\Http\Controllers\Api\PortfolioController::class, 'publicShow'])->name('portfolio.public');

// Public Document Share (dokumen dibagikan via token)
Route::get('/docs-shared/{token}', [\App\Http\Controllers\Web\Admin\Communication\DocumentController::class, 'sharedAccess'])->name('documents.shared');

// ============================================================
// Public docs / tutorial
// ============================================================
Route::get('/docs',                                            [DocsController::class, 'index'])->name('docs.index');
Route::get('/docs/{role}',                                     [DocsController::class, 'show'])
    ->where('role', 'admin|parent|student|teacher|super-admin|developer');

// ============================================================
// Public Visitor Pre-Registration
// ============================================================
Route::get('/kunjungan',                                       [VisitorRegistrationController::class, 'showForm'])->name('visitor.register');
Route::post('/kunjungan',                                      [VisitorRegistrationController::class, 'submit'])->name('visitor.register.submit');

// ============================================================
// Public Signage — Leaderboard display for school monitors
// ============================================================
Route::get('/signage/{schoolId}/leaderboard',                  [LeaderboardController::class, 'signage'])
    ->where('schoolId', '[0-9]+')->name('signage.leaderboard');

// Public Signage — OSIS Election Results
Route::get('/signage/{schoolId}/osis-results',                 [\App\Http\Controllers\Web\Admin\DigitalSignageController::class, 'osisResults'])
    ->where('schoolId', '[0-9]+')->name('signage.osis-results');

// ============================================================
// Public Job Board
// ============================================================
Route::get('/job-board',                                       [AlumniJobController::class, 'index'])->name('job-board.index');
Route::get('/job-board/{slug}',                                [AlumniJobController::class, 'show'])->name('job-board.show');
Route::post('/job-board/{slug}/apply',                         [AlumniJobController::class, 'apply'])->name('job-board.apply');

// ============================================================
// Public Forum Komunitas
// ============================================================
Route::middleware(['auth'])->prefix('komunitas')->name('forum.')->group(function () {
    Route::get('/',                                            [PublicForumController::class, 'index'])->name('index');
    Route::get('/buat',                                        [PublicForumController::class, 'createTopic'])->name('create');
    Route::post('/buat',                                       [PublicForumController::class, 'storeTopic'])->name('store');
    Route::get('/kategori/{category}',                         [PublicForumController::class, 'category'])->name('category');
    Route::get('/t/{topic}',                                   [PublicForumController::class, 'showTopic'])->name('topic');
    Route::post('/t/{topic}/balas',                            [PublicForumController::class, 'storeReply'])->name('reply');
    Route::post('/t/{topic}/subscribe',                        [PublicForumController::class, 'subscribe'])->name('subscribe');
    Route::post('/t/{topic}/unsubscribe',                      [PublicForumController::class, 'unsubscribe'])->name('unsubscribe');
});

// ============================================================
// Public Blog
// ============================================================
Route::get('/blog', [\App\Http\Controllers\Web\BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/feed.xml', [\App\Http\Controllers\Web\BlogController::class, 'feed'])->name('blog.feed');
Route::get('/blog/category/{slug}', [\App\Http\Controllers\Web\BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/{slug}', [\App\Http\Controllers\Web\BlogController::class, 'show'])->name('blog.show');

// ============================================================
// School Website Builder — Public Frontend
// ============================================================
Route::prefix('s/{subdomain}')->group(function () {
    Route::get('/',                                    [\App\Http\Controllers\Web\SchoolWebsiteController::class, 'homepage'])->name('school-website.home');
    Route::get('/kontak', [AuthController::class, 'kontak']);
    Route::post('/kontak',                             [\App\Http\Controllers\Web\SchoolWebsiteController::class, 'postContact'])->name('school-website.contact');
    Route::get('/{slug}',                              [\App\Http\Controllers\Web\SchoolWebsiteController::class, 'customPage'])->name('school-website.page');
});

// ============================================================
// Public Alumni Tracer Study Form
// ============================================================
Route::get('/alumni/tracer', [AuthController::class, 'alumniTracer'])->name('alumni.tracer');

// ============================================================
// Programmatic SEO (public, indexable)
// ============================================================
Route::get('/sitemap.xml',                                     [PseoController::class, 'sitemap']);
Route::get('/robots.txt',                                      [PseoController::class, 'robots']);
Route::get('/best-schools-{city}-{year}',                      [PseoController::class, 'bestSchools'])
    ->where('year', '[0-9]{4}');
Route::get('/alternatives-to-{slug}',                          [PseoController::class, 'alternatives']);
Route::get('/compare/{a}-vs-{b}',                              [PseoController::class, 'compare']);
Route::get('/ppdb/{city}',                                     [PseoController::class, 'ppdbByCity']);
Route::get('/donate/{subdomain}/{slug}',                       [PseoController::class, 'donationLanding']);
Route::get('/events/{subdomain}/{slug}',                       [PseoController::class, 'eventLanding']);
Route::get('/alumni/{subdomain}/{year}',                       [PseoController::class, 'alumniByYear'])
    ->where('year', '[0-9]{4}');

// ----- New pSEO routes -----
Route::get('/best-{type}-schools-in-{city}-{year}',            [PseoController::class, 'bestSchoolsByType'])
    ->where(['type' => 'sd|smp|sma|smk|tk|paud|pesantren|madrasah|internasional|islam|katolik|kristen|swasta|negeri', 'year' => '[0-9]{4}']);
Route::get('/sekolah-{religion}-{city}',                       [PseoController::class, 'schoolsByReligion'])
    ->where('religion', 'islam|katolik|kristen|hindu|buddha');
Route::get('/sekolah-internasional-{city}',                    [PseoController::class, 'internationalSchools']);
Route::get('/sekolah-asrama-{city}',                           [PseoController::class, 'boardingSchools']);
Route::get('/sekolah-akreditasi-a-{city}',                     [PseoController::class, 'accreditationASchools']);
Route::get('/biaya-spp-{type}-{city}',                         [PseoController::class, 'tuitionByCity'])
    ->where('type', 'sd|smp|sma|smk|tk|pesantren');
Route::get('/kurikulum-{name}',                                [PseoController::class, 'curriculumGuide'])
    ->where('name', 'merdeka|k13|cambridge|ib|montessori|charlotte-mason|diniyah');
Route::get('/jurusan-sma-{name}',                              [PseoController::class, 'smaMajor'])
    ->where('name', 'ipa|ips|bahasa|agama');
Route::get('/jurusan-smk-{name}',                              [PseoController::class, 'smkMajor'])
    ->where('name', 'rpl|tkj|akuntansi|tata-boga|multimedia|farmasi|keperawatan|otomotif|teknik-mesin|listrik');
Route::get('/lowongan-guru-{subject}-{city}',                  [PseoController::class, 'teacherJobs'])
    ->where('subject', 'matematika|bahasa-inggris|bahasa-indonesia|fisika|kimia|biologi|ipa|ips|sejarah|geografi|ekonomi|seni|olahraga|agama|tik|bk');
Route::get('/beasiswa-{type}-{year}',                          [PseoController::class, 'scholarshipGuide'])
    ->where(['type' => 'prestasi|kurang-mampu|tahfidz|olahraga|seni|akademik', 'year' => '[0-9]{4}']);
Route::get('/ekstrakurikuler-{name}-{city}',                   [PseoController::class, 'extracurricularByCity'])
    ->where('name', 'pramuka|paskibra|rohis|english-club|robotik|musik|tari|silat|basket|futsal|sepak-bola|badminton|catur|jurnalistik|teater');

// Super Admin Web Panel
Route::prefix('super')->name('super.')->group(function () {
    // Auth
    Route::get('/login', [AuthController::class, 'showSuperLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'superLogin'])->middleware('throttle:login')->name('login.post');

    // Protected routes
    Route::middleware(['auth', 'role:super_admin'])->group(function () {
        Route::get('/dashboard',                                   [DashboardController::class, 'index'])->name('dashboard');

        // Whitelabel / platform settings
        Route::get('/whitelabel',                                  [PlatformWhitelabelController::class, 'show'])->name('whitelabel.show');
        Route::put('/whitelabel',                                  [PlatformWhitelabelController::class, 'update'])->name('whitelabel.update');
        Route::post('/whitelabel/upload/{field}',                  [PlatformWhitelabelController::class, 'uploadImage'])->name('whitelabel.upload');
        Route::delete('/whitelabel/remove/{field}',                [PlatformWhitelabelController::class, 'removeImage'])->name('whitelabel.remove');

        Route::get('/schools',                                     [DashboardController::class, 'schools'])->name('schools.index');
        Route::get('/schools/create',                              [DashboardController::class, 'createSchool'])->name('schools.create');
        Route::post('/schools',                                    [DashboardController::class, 'storeSchool'])->name('schools.store');
        Route::get('/schools/{school}',                            [DashboardController::class, 'showSchool'])->name('schools.show');
        Route::post('/schools/{school}/suspend',                   [DashboardController::class, 'suspendSchool'])->name('schools.suspend');
        Route::post('/schools/{school}/activate',                  [DashboardController::class, 'activateSchool'])->name('schools.activate');
        Route::post('/schools/{school}/extend',                    [DashboardController::class, 'extendSchool'])->name('schools.extend');

        Route::get('/plans',                                       [DashboardController::class, 'plans'])->name('plans.index');
        Route::post('/plans',                                      [DashboardController::class, 'storePlan'])->name('plans.store');
        Route::put('/plans/{plan}',                                [DashboardController::class, 'updatePlan'])->name('plans.update');

        Route::get('/subscriptions',                               [DashboardController::class, 'subscriptions'])->name('subscriptions.index');

        Route::get('/analytics',                                   [DashboardController::class, 'analytics'])->name('analytics');

        Route::match(['GET', 'PUT'], '/config',                    [DashboardController::class, 'config'])->name('config');

        // ===== Platform admin extras =====
        Route::get('/users',                                       [PlatformPanelController::class, 'users'])->name('users.index');
        Route::post('/users/{user}/toggle',                        [PlatformPanelController::class, 'toggleUser'])->name('users.toggle');

        Route::get('/audit-log',                                   [PlatformPanelController::class, 'auditLog'])->name('audit.index');

        Route::get('/foundations',                                 [PlatformPanelController::class, 'foundations'])->name('foundations.index');
        Route::get('/foundations/create',                          [PlatformPanelController::class, 'createFoundation'])->name('foundations.create');
        Route::post('/foundations',                                [PlatformPanelController::class, 'storeFoundation'])->name('foundations.store');

        Route::get('/announcements',                               [PlatformPanelController::class, 'announcements'])->name('announcements.index');
        Route::post('/announcements',                              [PlatformPanelController::class, 'storeAnnouncement'])->name('announcements.store');
        Route::delete('/announcements/{id}',                       [PlatformPanelController::class, 'deleteAnnouncement'])->name('announcements.destroy');

        Route::get('/system-health',                               [PlatformPanelController::class, 'systemHealth'])->name('system.health');

        // ===== Subscription / Billing =====
        Route::get('/registrations',                               [PlatformBillingController::class, 'registrations'])->name('registrations.index');
        Route::get('/registrations/{registration}',               [PlatformBillingController::class, 'showRegistration'])->name('registrations.show');
        Route::post('/registrations/{registration}/approve',      [PlatformBillingController::class, 'approveRegistration'])->name('registrations.approve');
        Route::post('/registrations/{registration}/reject',       [PlatformBillingController::class, 'rejectRegistration'])->name('registrations.reject');

        Route::get('/billing/accounts',                            [PlatformBillingController::class, 'billingAccounts'])->name('billing.accounts.index');
        Route::post('/billing/accounts',                           [PlatformBillingController::class, 'storeBillingAccount'])->name('billing.accounts.store');
        Route::post('/billing/accounts/{account}/toggle',          [PlatformBillingController::class, 'toggleBillingAccount'])->name('billing.accounts.toggle');
        Route::delete('/billing/accounts/{account}',               [PlatformBillingController::class, 'deleteBillingAccount'])->name('billing.accounts.destroy');

        Route::get('/billing/gateways',                            [PlatformBillingController::class, 'gateways'])->name('billing.gateways.index');
        Route::post('/billing/gateways',                           [PlatformBillingController::class, 'storeGateway'])->name('billing.gateways.store');
        Route::get('/billing/gateways/{gateway}/edit',             [PlatformBillingController::class, 'editGateway'])->name('billing.gateways.edit');
        Route::put('/billing/gateways/{gateway}',                  [PlatformBillingController::class, 'updateGateway'])->name('billing.gateways.update');
        Route::post('/billing/gateways/{gateway}/toggle',          [PlatformBillingController::class, 'toggleGateway'])->name('billing.gateways.toggle');
        Route::delete('/billing/gateways/{gateway}',               [PlatformBillingController::class, 'deleteGateway'])->name('billing.gateways.destroy');

        // ===== Super Admin Extras =====
        Route::get('/email-templates',                             [SuperExtrasController::class, 'emailTemplates'])->name('email-templates.index');
        Route::post('/email-templates',                            [SuperExtrasController::class, 'saveEmailTemplate'])->name('email-templates.save');

        Route::get('/backups',                                     [SuperExtrasController::class, 'backups'])->name('backups.index');
        Route::post('/backups',                                    [SuperExtrasController::class, 'triggerBackup'])->name('backups.trigger');
        Route::get('/backups/{name}/download',                     [SuperExtrasController::class, 'downloadBackup'])->name('backups.download')->where('name', '[A-Za-z0-9_\-\.]+');
        Route::delete('/backups/{name}',                           [SuperExtrasController::class, 'deleteBackup'])->name('backups.destroy')->where('name', '[A-Za-z0-9_\-\.]+');
        Route::post('/backups/upload',                             [SuperExtrasController::class, 'uploadRestore'])->name('backups.upload');
        Route::post('/backups/{name}/restore',                     [SuperExtrasController::class, 'restoreBackup'])->name('backups.restore')->where('name', '[A-Za-z0-9_\-\.]+');

        Route::get('/maintenance',                                 [SuperExtrasController::class, 'maintenance'])->name('maintenance.index');
        Route::post('/maintenance/enable',                         [SuperExtrasController::class, 'enableMaintenance'])->name('maintenance.enable');
        Route::post('/maintenance/disable',                        [SuperExtrasController::class, 'disableMaintenance'])->name('maintenance.disable');

        Route::get('/reports',                                     [SuperExtrasController::class, 'reports'])->name('reports.index');
        Route::get('/reports/export',                              [SuperExtrasController::class, 'reportsExportCsv'])->name('reports.export');

        Route::get('/foundations/{foundation}/admins',             [SuperExtrasController::class, 'foundationAdmins'])->name('foundations.admins.index');
        Route::post('/foundations/{foundation}/admins',            [SuperExtrasController::class, 'assignFoundationAdmin'])->name('foundations.admins.store');
        Route::delete('/foundations/admins/{admin}',               [SuperExtrasController::class, 'removeFoundationAdmin'])->name('foundations.admins.destroy');

        Route::get('/webhooks',                                    [SuperExtrasController::class, 'webhookLogs'])->name('webhooks.index');

        // Global AI usage
        Route::get('/ai/usage',                                    [\App\Http\Controllers\Web\SuperAdmin\AiUsageController::class, 'index'])->name('ai.usage');

        // ===== BENCHMARK ANTAR SEKOLAH (Module 11 - Super Admin) =====
        Route::get('/benchmark',                                 [BenchmarkController::class, 'index'])->name('benchmark.index');
        Route::get('/benchmark/drilldown',                       [BenchmarkController::class, 'drilldown'])->name('benchmark.drilldown');

        Route::post('/logout',                                     [DashboardController::class, 'logout'])->name('logout');
    });
});
