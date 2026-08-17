<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Attendance
            'attendance.view', 'attendance.manage',
            // Timetable
            'timetable.view', 'timetable.manage',
            // Classroom
            'classroom.view', 'classroom.manage',
            // Exam
            'exam.view', 'exam.manage', 'exam.attempt',
            // Marks
            'marks.view', 'marks.manage',
            // Fee
            'fee.view', 'fee.manage', 'fee.payment',
            // Payroll
            'payroll.view', 'payroll.manage',
            // Library
            'library.view', 'library.manage',
            // Hostel
            'hostel.view', 'hostel.manage',
            // Transport
            'transport.view', 'transport.manage',
            // Notice
            'notice.view', 'notice.manage',
            // Chat
            'chat.use',
            // Admission
            'admission.view', 'admission.manage',
            // School setup
            'school.manage',
            // Students
            'student.view', 'student.manage',
            // Staff
            'staff.view', 'staff.manage',
            // Reports
            'report.view',
            // Super admin
            'saas.manage',

            // ===== Phase 8-11 permissions =====
            // PPDB (Module 22)
            'ppdb.view', 'ppdb.manage', 'ppdb.review',
            // Bus Tracking + Gate (Module 23)
            'transport.tracking.view', 'gate.scan', 'gate.manage',
            // UKS (Module 24)
            'medical.view', 'medical.manage',
            // BP/BK + Discipline (Module 25)
            'counseling.view', 'counseling.manage',
            'discipline.view', 'discipline.manage',
            // Lesson Plan (Module 26)
            'lesson_plan.view', 'lesson_plan.manage', 'lesson_plan.approve',
            // Cafeteria (Module 27)
            'canteen.view', 'canteen.manage',
            // Religious / Pesantren (Module 28)
            'religious.view', 'religious.manage',
            // Donations (Module 29)
            'donation.view', 'donation.manage',
            // Alumni (Module 30)
            'alumni.view', 'alumni.manage',
            // AI (Module 31)
            'ai.use', 'ai.manage',
            // Dapodik (Module 32)
            'dapodik.sync',
            // Visitor (Module 33)
            'visitor.view', 'visitor.manage',
            // Inventory (Module 34)
            'inventory.view', 'inventory.manage',
            // Live Class (Module 35)
            'liveclass.view', 'liveclass.manage',
            // Question Bank (Module 36)
            'question_bank.view', 'question_bank.manage',
            // Achievement (Module 37)
            'achievement.view', 'achievement.manage',
            // Scholarship (Module 38)
            'scholarship.view', 'scholarship.manage', 'scholarship.apply',
            // Career Guidance (Module 39)
            'career.view', 'career.manage',
            // Curriculum (Module 40)
            'curriculum.view', 'curriculum.manage',
            // Yayasan (Module 41)
            'foundation.view',
            // Event (Module 42)
            'event.view', 'event.manage',
            // Daily Report (Module 43)
            'daily_report.view', 'daily_report.manage',
            // Extracurricular (Module 44)
            'ekskul.view', 'ekskul.manage',
            // Analytics (Module 45)
            'analytics.view',
            // Branding (Module 03b)
            'branding.manage',
            // Payment (Module 11b)
            'payment.providers.manage',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $roles = [
            'super_admin' => ['saas.manage', 'school.manage', 'student.manage', 'staff.manage',
                              'report.view', 'fee.manage', 'payroll.manage'],

            'admin'       => ['school.manage', 'student.manage', 'student.view', 'staff.manage',
                              'staff.view', 'attendance.view', 'attendance.manage', 'timetable.manage',
                              'timetable.view', 'classroom.manage', 'classroom.view', 'exam.manage',
                              'exam.view', 'marks.manage', 'marks.view', 'fee.manage', 'fee.view',
                              'fee.payment', 'payroll.manage', 'payroll.view', 'library.manage',
                              'library.view', 'hostel.manage', 'hostel.view', 'transport.manage',
                              'transport.view', 'notice.manage', 'notice.view', 'admission.manage',
                              'admission.view', 'report.view', 'chat.use',
                              // Phase 8-11
                              'ppdb.view', 'ppdb.manage', 'ppdb.review',
                              'transport.tracking.view', 'gate.manage',
                              'medical.view', 'medical.manage',
                              'counseling.view', 'counseling.manage',
                              'discipline.view', 'discipline.manage',
                              'lesson_plan.view', 'lesson_plan.approve',
                              'canteen.view', 'canteen.manage',
                              'religious.view', 'religious.manage',
                              'donation.view', 'donation.manage',
                              'alumni.view', 'alumni.manage',
                              'ai.use', 'ai.manage',
                              'dapodik.sync',
                              'visitor.view', 'visitor.manage',
                              'inventory.view', 'inventory.manage',
                              'liveclass.view', 'liveclass.manage',
                              'question_bank.view', 'question_bank.manage',
                              'achievement.view', 'achievement.manage',
                              'scholarship.view', 'scholarship.manage',
                              'career.view', 'career.manage',
                              'curriculum.view', 'curriculum.manage',
                              'event.view', 'event.manage',
                              'daily_report.view', 'daily_report.manage',
                              'ekskul.view', 'ekskul.manage',
                              'analytics.view',
                              'branding.manage', 'payment.providers.manage'],

            'teacher'     => ['attendance.manage', 'attendance.view', 'timetable.view',
                              'classroom.manage', 'classroom.view', 'exam.manage', 'exam.view',
                              'marks.manage', 'marks.view', 'notice.view', 'student.view',
                              'library.view', 'chat.use', 'report.view',
                              'lesson_plan.view', 'lesson_plan.manage', 'discipline.manage',
                              'achievement.manage', 'liveclass.view', 'liveclass.manage',
                              'question_bank.view', 'question_bank.manage', 'curriculum.view',
                              'ai.use', 'religious.manage', 'ekskul.manage'],

            'student'     => ['attendance.view', 'timetable.view', 'classroom.view',
                              'exam.attempt', 'exam.view', 'marks.view', 'fee.view',
                              'library.view', 'notice.view', 'chat.use',
                              'scholarship.apply', 'achievement.view', 'event.view',
                              'canteen.view', 'liveclass.view', 'ai.use'],

            'parent'      => ['attendance.view', 'marks.view', 'fee.view',
                              'notice.view', 'chat.use',
                              'transport.tracking.view', 'medical.view',
                              'achievement.view', 'daily_report.view',
                              'donation.view', 'event.view', 'ppdb.view'],

            'accountant'  => ['fee.manage', 'fee.view', 'fee.payment', 'payroll.manage',
                              'payroll.view', 'report.view',
                              'donation.manage', 'scholarship.view', 'scholarship.manage',
                              'payment.providers.manage', 'analytics.view'],

            'librarian'   => ['library.manage', 'library.view', 'student.view'],

            'receptionist' => ['admission.manage', 'admission.view', 'student.view',
                               'notice.view', 'chat.use',
                               'visitor.view', 'visitor.manage',
                               'ppdb.view', 'ppdb.manage'],

            // ===== New roles for Phase 8-11 =====
            'nurse'       => ['medical.view', 'medical.manage', 'student.view'],
            'counselor'   => ['counseling.view', 'counseling.manage', 'discipline.view',
                              'discipline.manage', 'student.view', 'analytics.view'],
            'foundation_admin' => ['foundation.view'],

            // ===== Enterprise roles (Role expansion) =====
            'principal'     => ['school.manage', 'student.view', 'staff.view', 'attendance.view',
                                'marks.view', 'fee.view', 'report.view', 'analytics.view',
                                'notice.view', 'notice.manage', 'chat.use', 'curriculum.view',
                                'lesson_plan.view', 'lesson_plan.approve', 'medical.view'],
            'hr'            => ['staff.view', 'staff.manage', 'payroll.view', 'payroll.manage', 'report.view'],
            'transport_admin' => ['transport.view', 'transport.manage', 'transport.tracking.view', 'gate.manage', 'gate.scan'],
            'hostel_admin'  => ['hostel.view', 'hostel.manage', 'student.view'],
            'procurement_admin' => ['inventory.view', 'inventory.manage', 'report.view'],
            'homeroom_teacher' => ['attendance.view', 'attendance.manage', 'student.view', 'marks.view',
                                   'marks.manage', 'notice.view', 'chat.use', 'classroom.view', 'report.view'],
            'driver'        => ['gate.scan', 'transport.tracking.view'],
        ];

        foreach ($roles as $roleName => $rolePerms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($rolePerms);
        }
    }
}
