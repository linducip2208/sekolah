@php
    $isActive = fn($pattern) => request()->routeIs($pattern) ? 'active' : '';
    $hasActive = fn(array $patterns) => collect($patterns)->contains(fn($p) => request()->routeIs($p));
    $icon = fn($d) => "<svg class='w-4 h-4 flex-shrink-0' fill='none' stroke='currentColor' viewBox='0 0 24 24' stroke-width='1.8'><path stroke-linecap='round' stroke-linejoin='round' d='{$d}'/></svg>";
    $chevron = "<svg class='w-3 h-3 transition-transform duration-200' :class=\"open ? 'rotate-90' : ''\" fill='none' stroke='currentColor' viewBox='0 0 24 24' stroke-width='2.5'><path stroke-linecap='round' stroke-linejoin='round' d='M9 5l7 7-7 7'/></svg>";

    $icons = [
        'dashboard'   => 'M3 12l9-9 9 9M5 10v10h14V10',
        'tasks'       => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
        'calendar'    => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        'bell'        => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
        'academic'    => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z',
        'students'    => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        'admissions'  => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
        'people'      => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
        'finance'     => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'procurement' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
        'inventory'   => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        'facilities'  => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        'library'     => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        'life'        => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z',
        'alumni'      => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        'comm'        => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
        'ai'          => 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z',
        'reports'     => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        'automation'  => 'M13 10V3L4 14h7v7l9-11h-7z',
        'system'      => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
    ];

    // Role-based navigation (presentation only; backend authorization enforced separately).
    $role    = auth()->check() ? (auth()->user()->getRoleNames()->first() ?? 'admin') : 'admin';
    $isAdmin = in_array($role, ['admin', 'super_admin'], true);
    $can = fn(array $roles) => in_array($role, $roles, true);
    $canProcurement  = $can(['admin', 'super_admin', 'procurement_admin']);
    $canAutomation   = $can(['admin', 'super_admin']);

    $sid = auth()->check() ? auth()->user()->school_id : null;
    $navCounts = [
        'ppdb'     => rescue(fn () => \App\Models\PPDB\PpdbApplication::where('school_id', $sid)->count(), 0, false),
        'invoices' => rescue(fn () => \App\Models\Finance\FeeInvoice::where('school_id', $sid)->whereIn('status', ['unpaid', 'partial', 'overdue'])->count(), 0, false),
        'workflow' => rescue(fn () => \App\Models\Workflow\WorkflowRequest::where('school_id', $sid)->whereIn('status', ['submitted', 'under_review'])->count(), 0, false),
    ];
@endphp

<div class="px-3 pt-3 pb-1.5 flex items-center gap-1.5 sidebar-search">
    <div class="relative flex-1" x-data="{ q: '' }">
        <svg class="w-4 h-4 absolute left-2.5 top-1/2 -translate-y-1/2 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="text" x-model="q" placeholder="Cari menu..." aria-label="Cari menu"
               class="w-full bg-white/10 border border-white/10 rounded-md pl-8 pr-2 py-1.5 text-[13px] text-white/80 placeholder-white/40 outline-none focus:border-white/30 focus:bg-white/15 transition"
               @input="document.querySelectorAll('.sidebar-section').forEach(s => { s.style.display = q.length<2 || s.textContent.toLowerCase().includes(q.toLowerCase()) ? '' : 'none' })">
    </div>
    <button onclick="document.querySelectorAll('.sidebar-section').forEach(s=>s.__x.$data.open=true)" class="text-white/40 hover:text-white/80 p-1.5 text-xs" title="Buka semua" aria-label="Buka semua bagian">&#x25BC;</button>
    <button onclick="document.querySelectorAll('.sidebar-section').forEach(s=>s.__x.$data.open=false)" class="text-white/40 hover:text-white/80 p-1.5 text-xs" title="Tutup semua" aria-label="Tutup semua bagian">&#x25B2;</button>
</div>

{{-- ===== FAVORITES ===== --}}
<div x-data="{ items: window.favorites ? window.favorites.all() : [] }"
     x-init="window.addEventListener('sikadpro:favorites-changed', () => items = window.favorites.all())"
     x-show="items.length" x-cloak class="sidebar-section">
    <div class="sidebar-section-header" style="cursor: default;">Favorites</div>
    <div class="sidebar-section-body">
        <template x-for="f in items" :key="f.href">
            <a :href="f.href" class="sidebar-sub-link" x-text="f.label"></a>
        </template>
    </div>
</div>

{{-- ===== TOP NAV ===== --}}
<a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ $isActive('admin.dashboard') }}">{!! $icon($icons['dashboard']) !!}<span>Dashboard</span></a>
<a href="{{ route('admin.workflow.index') }}" class="sidebar-link {{ $isActive('admin.workflow.*') }}">{!! $icon($icons['tasks']) !!}<span>My Tasks</span>@if($navCounts['workflow'] > 0)<span class="sidebar-badge">{{ $navCounts['workflow'] }}</span>@endif</a>
<a href="{{ route('admin.calendar.index') }}" class="sidebar-link {{ $isActive('admin.calendar.*') }}">{!! $icon($icons['calendar']) !!}<span>Calendar</span></a>
<a href="{{ route('admin.notifications.index') }}" class="sidebar-link {{ $isActive('admin.notifications.*') }}">{!! $icon($icons['bell']) !!}<span>Notifications</span></a>

@if($can(['admin','super_admin','principal','homeroom_teacher','hr']))
{{-- ACADEMIC --}}
<div class="sidebar-section" x-data="{ open: {{ $hasActive(['admin.academic.*','admin.curriculum.*','admin.timetable.*','admin.classroom.lessons.*','admin.assignments.*','admin.exams.*','admin.qbank.*','admin.raport-interaktif.*','admin.lesson-plan.*','admin.live-class.*','admin.academic.essay-grading.*','admin.courses.*']) ? 'true' : 'false' }} }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2.5">{!! $icon($icons['academic']) !!}Academic</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.academic.years.index') }}" class="sidebar-sub-link {{ $isActive('admin.academic.years.*') }}">Academic Years</a>
        <a href="{{ route('admin.curriculum.frameworks.index') }}" class="sidebar-sub-link {{ $isActive('admin.curriculum.*') }}">Curriculum</a>
        <a href="{{ route('admin.academic.subjects.index') }}" class="sidebar-sub-link {{ $isActive('admin.academic.subjects.*') }}">Subjects</a>
        <a href="{{ route('admin.academic.classes.index') }}" class="sidebar-sub-link {{ $isActive('admin.academic.classes.*') }}">Classes</a>
        <a href="{{ route('admin.academic.sections.index') }}" class="sidebar-sub-link {{ $isActive('admin.academic.sections.*') }}">Sections</a>
        <a href="{{ route('admin.academic.class-sections.index') }}" class="sidebar-sub-link {{ $isActive('admin.academic.class-sections.*') }}">Class Groups</a>
        <a href="{{ route('admin.academic.mediums.index') }}" class="sidebar-sub-link {{ $isActive('admin.academic.mediums.*') }}">Mediums</a>
        <a href="{{ route('admin.timetable.index') }}" class="sidebar-sub-link {{ $isActive('admin.timetable.*') }}">Schedules</a>
        <a href="{{ route('admin.classroom.lessons.index') }}" class="sidebar-sub-link {{ $isActive('admin.classroom.lessons.*') }}">Lessons</a>
        <a href="{{ route('admin.assignments.index') }}" class="sidebar-sub-link {{ $isActive('admin.assignments.*') }}">Assignments</a>
        <a href="{{ route('admin.exams.index') }}" class="sidebar-sub-link {{ $isActive('admin.exams.*') }}">Exams</a>
        <a href="{{ route('admin.qbank.items.index') }}" class="sidebar-sub-link {{ $isActive('admin.qbank.*') }}">Question Bank</a>
        <a href="{{ route('admin.raport-interaktif.index') }}" class="sidebar-sub-link {{ $isActive('admin.raport-interaktif.*') }}">Report Cards</a>
        <a href="{{ route('admin.grades.index') }}" class="sidebar-sub-link {{ $isActive('admin.grades.index') }}">Grading Scale</a>
        <a href="{{ route('admin.grades.approval') }}" class="sidebar-sub-link {{ $isActive('admin.grades.approval') }}">Grade Approval</a>
        <a href="{{ route('admin.grades.transcript') }}" class="sidebar-sub-link {{ $isActive('admin.grades.transcript') }}">Transcript</a>
        <a href="{{ route('admin.lesson-plan.index') }}" class="sidebar-sub-link {{ $isActive('admin.lesson-plan.*') }}">Lesson Plans</a>
        <a href="{{ route('admin.live-class.index') }}" class="sidebar-sub-link {{ $isActive('admin.live-class.*') }}">Live Class</a>
        <a href="{{ route('admin.courses.index') }}" class="sidebar-sub-link {{ $isActive('admin.courses.*') }}">Kursus (LMS)</a>
        <a href="{{ route('admin.quizzes.index') }}" class="sidebar-sub-link {{ $isActive('admin.quizzes.*') }}">Kuis</a>
        <a href="{{ route('admin.academic.essay-grading.index') }}" class="sidebar-sub-link {{ $isActive('admin.academic.essay-grading.*') }}">AI Essay Grading</a>
    </div>
</div>

{{-- STUDENTS --}}
<div class="sidebar-section" x-data="{ open: {{ $hasActive(['admin.students.*','admin.import.*','admin.attendance.*','admin.discipline.*','admin.counseling.*','admin.clinic.*','admin.achievements.*','admin.portfolios.*','admin.misc.career','admin.misc.internships.*','admin.qr-attendance.*']) ? 'true' : 'false' }} }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2.5">{!! $icon($icons['students']) !!}Students</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.students.index') }}" class="sidebar-sub-link {{ $isActive('admin.students.*') }}">Student Directory</a>
        <a href="{{ route('admin.import.index') }}" class="sidebar-sub-link {{ $isActive('admin.import.*') }}">Import CSV</a>
        <a href="{{ route('admin.attendance.index') }}" class="sidebar-sub-link {{ $isActive('admin.attendance.*') }}">Attendance</a>
        <a href="{{ route('admin.discipline.records.index') }}" class="sidebar-sub-link {{ $isActive('admin.discipline.*') }}">Discipline</a>
        <a href="{{ route('admin.counseling.sessions.index') }}" class="sidebar-sub-link {{ $isActive('admin.counseling.*') }}">Counseling</a>
        <a href="{{ route('admin.clinic.visits.index') }}" class="sidebar-sub-link {{ $isActive('admin.clinic.*') || $isActive('admin.medical.*') }}">Health</a>
        <a href="{{ route('admin.achievements.records.index') }}" class="sidebar-sub-link {{ $isActive('admin.achievements.*') }}">Achievements</a>
        <a href="{{ route('admin.portfolios.index') }}" class="sidebar-sub-link {{ $isActive('admin.portfolios.*') }}">e-Portfolio</a>
        <a href="{{ route('admin.misc.career') }}" class="sidebar-sub-link {{ $isActive('admin.misc.career') }}">Career Guidance</a>
        <a href="{{ route('admin.misc.internships.index') }}" class="sidebar-sub-link {{ $isActive('admin.misc.internships.*') }}">Internships</a>
    </div>
</div>

{{-- ADMISSIONS --}}
<div class="sidebar-section" x-data="{ open: {{ $hasActive(['admin.ppdb.*']) ? 'true' : 'false' }} }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2.5">{!! $icon($icons['admissions']) !!}Admissions</span>@if($navCounts['ppdb'] > 0)<span class="sidebar-badge">{{ $navCounts['ppdb'] }}</span>@endif{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.ppdb.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.ppdb.dashboard') }}">PPDB Dashboard</a>
        <a href="{{ route('admin.ppdb.applications.index') }}" class="sidebar-sub-link {{ $isActive('admin.ppdb.applications.*') }}">Applicants</a>
        <a href="{{ route('admin.ppdb.periods.index') }}" class="sidebar-sub-link {{ $isActive('admin.ppdb.periods.*') }}">Periods</a>
    </div>
</div>

{{-- PEOPLE --}}
<div class="sidebar-section" x-data="{ open: {{ $hasActive(['admin.staff.*','admin.pkg.*','admin.training.*','admin.lesson-study.*','admin.payroll.*']) ? 'true' : 'false' }} }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2.5">{!! $icon($icons['people']) !!}People</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.staff.index') }}" class="sidebar-sub-link {{ $isActive('admin.staff.*') }}">Teachers & Staff</a>
        <a href="{{ route('admin.pkg.index') }}" class="sidebar-sub-link {{ $isActive('admin.pkg.*') }}">Performance (PKG)</a>
        <a href="{{ route('admin.training.index') }}" class="sidebar-sub-link {{ $isActive('admin.training.*') }}">Training</a>
        <a href="{{ route('admin.training.certifications') }}" class="sidebar-sub-link {{ $isActive('admin.training.certifications') }}">Certifications</a>
        <a href="{{ route('admin.lesson-study.index') }}" class="sidebar-sub-link {{ $isActive('admin.lesson-study.*') }}">Lesson Study</a>
        <a href="{{ route('admin.payroll.slips.index') }}" class="sidebar-sub-link {{ $isActive('admin.payroll.*') }}">Payroll</a>
    </div>
</div>
@endif

{{-- FINANCE --}}
<div class="sidebar-section" x-data="{ open: {{ $hasActive(['admin.fee.*','admin.payment.*','admin.budget.*','admin.cooperative.*','admin.finance.*','admin.currency.*','admin.accounting.*']) ? 'true' : 'false' }} }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2.5">{!! $icon($icons['finance']) !!}Finance</span>@if($navCounts['invoices'] > 0)<span class="sidebar-badge">{{ $navCounts['invoices'] }}</span>@endif{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.fee.structures.index') }}" class="sidebar-sub-link {{ $isActive('admin.fee.structures.*') }}">SPP / Billing</a>
        <a href="{{ route('admin.fee.invoices.index') }}" class="sidebar-sub-link {{ $isActive('admin.fee.invoices.*') }}">Invoices</a>
        <a href="{{ route('admin.payment.providers.index') }}" class="sidebar-sub-link {{ $isActive('admin.payment.*') }}">Payments</a>
        <a href="{{ route('admin.budget.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.budget.*') }}">Budget</a>
        <a href="{{ route('admin.cooperative.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.cooperative.*') }}">Cooperative</a>
        <a href="{{ route('admin.finance.reports.summary') }}" class="sidebar-sub-link {{ $isActive('admin.finance.reports.summary') }}">Financial Summary</a>
        <a href="{{ route('admin.finance.reports.outstanding') }}" class="sidebar-sub-link {{ $isActive('admin.finance.reports.outstanding') }}">SPP Outstanding</a>
        <a href="{{ route('admin.accounting.coa') }}" class="sidebar-sub-link {{ $isActive('admin.accounting.coa') }}">Akuntansi (COA)</a>
        <a href="{{ route('admin.accounting.bank-reconciliation') }}" class="sidebar-sub-link {{ $isActive('admin.accounting.bank-reconciliation') }}">Rekonsiliasi Bank</a>
        <a href="{{ route('admin.currency.show') }}" class="sidebar-sub-link {{ $isActive('admin.currency.*') }}">Currency</a>
    </div>
</div>

@if($canProcurement)
{{-- PROCUREMENT --}}
<div class="sidebar-section" x-data="{ open: {{ $hasActive(['admin.procurement.*']) ? 'true' : 'false' }} }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2.5">{!! $icon($icons['procurement']) !!}Procurement</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.procurement.index') }}" class="sidebar-sub-link {{ $isActive('admin.procurement.index') }}">Purchase Requests</a>
        <a href="{{ route('admin.procurement.approvals') }}" class="sidebar-sub-link {{ $isActive('admin.procurement.approvals') }}">Approvals</a>
        <a href="{{ route('admin.procurement.suppliers') }}" class="sidebar-sub-link {{ $isActive('admin.procurement.suppliers') }}">Suppliers</a>
    </div>
</div>

{{-- INVENTORY & ASSETS --}}
<div class="sidebar-section" x-data="{ open: {{ $hasActive(['admin.inventory.*','admin.misc.maintenance.*']) ? 'true' : 'false' }} }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2.5">{!! $icon($icons['inventory']) !!}Inventory &amp; Assets</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.inventory.assets.index') }}" class="sidebar-sub-link {{ $isActive('admin.inventory.assets.*') }}">Assets</a>
        <a href="{{ route('admin.inventory.categories.index') }}" class="sidebar-sub-link {{ $isActive('admin.inventory.categories.*') }}">Categories</a>
        <a href="{{ route('admin.inventory.loans.index') }}" class="sidebar-sub-link {{ $isActive('admin.inventory.loans.*') }}">Loans</a>
        <a href="{{ route('admin.misc.maintenance.index') }}" class="sidebar-sub-link {{ $isActive('admin.misc.maintenance.*') }}">Maintenance</a>
    </div>
</div>

{{-- FACILITIES & OPERATIONS --}}
<div class="sidebar-section" x-data="{ open: {{ $hasActive(['admin.hostel.*','admin.transport.*','admin.facilities.rooms.*','admin.visitor.*','admin.operations.*','admin.dapodik.*','admin.visitors.*']) ? 'true' : 'false' }} }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2.5">{!! $icon($icons['facilities']) !!}Facilities &amp; Operations</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.hostel.list.index') }}" class="sidebar-sub-link {{ $isActive('admin.hostel.*') }}">Dormitory</a>
        <a href="{{ route('admin.transport.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.transport.*') }}">Transportation</a>
        <a href="{{ route('admin.transport.tracking') }}" class="sidebar-sub-link {{ $isActive('admin.transport.tracking') }}">Live Tracking Bus</a>
        <a href="{{ route('admin.transport.attendance.index') }}" class="sidebar-sub-link {{ $isActive('admin.transport.attendance.*') }}">Transport Attendance</a>
        <a href="{{ route('admin.transport.driver-schedule.index') }}" class="sidebar-sub-link {{ $isActive('admin.transport.driver-schedule.*') }}">Driver Schedule</a>
        <a href="{{ route('admin.facilities.rooms.index') }}" class="sidebar-sub-link {{ $isActive('admin.facilities.rooms.*') }}">Room Booking</a>
        <a href="{{ route('admin.visitor.logs.index') }}" class="sidebar-sub-link {{ $isActive('admin.visitor.*') }}">Visitors</a>
        <a href="{{ route('admin.operations.gate-devices.index') }}" class="sidebar-sub-link {{ $isActive('admin.operations.*') }}">Gate Devices</a>
        <a href="{{ route('admin.dapodik.config.index') }}" class="sidebar-sub-link {{ $isActive('admin.dapodik.*') }}">Dapodik Sync</a>
    </div>
</div>

{{-- LIBRARY --}}
<div class="sidebar-section" x-data="{ open: {{ $hasActive(['admin.library.*']) ? 'true' : 'false' }} }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2.5">{!! $icon($icons['library']) !!}Library</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.library.books.index') }}" class="sidebar-sub-link {{ $isActive('admin.library.books.*') }}">Books</a>
        <a href="{{ route('admin.library.categories.index') }}" class="sidebar-sub-link {{ $isActive('admin.library.categories.*') }}">Categories</a>
        <a href="{{ route('admin.library.digital.upload') }}" class="sidebar-sub-link {{ $isActive('admin.library.digital.*') }}">e-Library</a>
    </div>
</div>

{{-- STUDENT LIFE --}}
<div class="sidebar-section" x-data="{ open: {{ $hasActive(['admin.extracurricular.*','admin.events.*','admin.leaderboard.*','admin.canteen.*','admin.religious.*','admin.donations.*','admin.scholarship.*','admin.osis.*','admin.misc.daily-reports']) ? 'true' : 'false' }} }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2.5">{!! $icon($icons['life']) !!}Student Life</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.extracurricular.index') }}" class="sidebar-sub-link {{ $isActive('admin.extracurricular.*') }}">Extracurricular</a>
        <a href="{{ route('admin.events.index') }}" class="sidebar-sub-link {{ $isActive('admin.events.*') }}">Events</a>
        <a href="{{ route('admin.leaderboard.index') }}" class="sidebar-sub-link {{ $isActive('admin.leaderboard.*') }}">Leaderboard</a>
        <a href="{{ route('admin.osis.index') }}" class="sidebar-sub-link {{ $isActive('admin.osis.*') }}">OSIS</a>
        <a href="{{ route('admin.canteen.menu.index') }}" class="sidebar-sub-link {{ $isActive('admin.canteen.*') }}">Canteen</a>
        <a href="{{ route('admin.religious.targets.index') }}" class="sidebar-sub-link {{ $isActive('admin.religious.*') }}">Pesantren / Madrasah</a>
        <a href="{{ route('admin.donations.campaigns.index') }}" class="sidebar-sub-link {{ $isActive('admin.donations.*') }}">Donations</a>
        <a href="{{ route('admin.scholarship.programs.index') }}" class="sidebar-sub-link {{ $isActive('admin.scholarship.*') }}">Scholarships</a>
        <a href="{{ route('admin.misc.daily-reports') }}" class="sidebar-sub-link {{ $isActive('admin.misc.daily-reports') }}">Daily Reports</a>
    </div>
</div>

{{-- ALUMNI --}}
<div class="sidebar-section" x-data="{ open: {{ $hasActive(['admin.alumni.*','admin.tracer.*','admin.jobs.*','admin.bkk.*']) ? 'true' : 'false' }} }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2.5">{!! $icon($icons['alumni']) !!}Alumni</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.alumni.index') }}" class="sidebar-sub-link {{ $isActive('admin.alumni.*') }}">Directory</a>
        <a href="{{ route('admin.tracer.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.tracer.*') }}">Tracer Study</a>
        <a href="{{ route('admin.jobs.index') }}" class="sidebar-sub-link {{ $isActive('admin.jobs.*') }}">Job Board</a>
        <a href="{{ route('admin.bkk.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.bkk.*') }}">BKK</a>
    </div>
</div>

{{-- COMMUNICATION --}}
<div class="sidebar-section" x-data="{ open: {{ $hasActive(['admin.notices.*','admin.chat.*','admin.wa-bot.*','admin.reminders.*','admin.emergency.*','admin.notif.*','admin.forum.*','admin.conferences.*','admin.committee.*']) ? 'true' : 'false' }} }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2.5">{!! $icon($icons['comm']) !!}Communication</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.notices.index') }}" class="sidebar-sub-link {{ $isActive('admin.notices.*') }}">Announcements</a>
        <a href="{{ route('admin.chat.inbox') }}" class="sidebar-sub-link {{ $isActive('admin.chat.*') }}">Messages</a>
        <a href="{{ route('admin.wa-bot.commands.index') }}" class="sidebar-sub-link {{ $isActive('admin.wa-bot.*') }}">WhatsApp Bot</a>
        <a href="{{ route('admin.reminders.index') }}" class="sidebar-sub-link {{ $isActive('admin.reminders.*') }}">Reminders</a>
        <a href="{{ route('admin.emergency.index') }}" class="sidebar-sub-link {{ $isActive('admin.emergency.*') }}">Emergency</a>
        <a href="{{ route('admin.notif.providers.index') }}" class="sidebar-sub-link {{ $isActive('admin.notif.*') }}">Notification Providers</a>
        <a href="{{ route('admin.forum.categories') }}" class="sidebar-sub-link {{ $isActive('admin.forum.*') }}">Forum</a>
        <a href="{{ route('admin.conferences.index') }}" class="sidebar-sub-link {{ $isActive('admin.conferences.*') }}">Parent Conferences</a>
        <a href="{{ route('admin.committee.members') }}" class="sidebar-sub-link {{ $isActive('admin.committee.*') }}">Committee</a>
    </div>
</div>
@endif

{{-- AI & ANALYTICS --}}
<div class="sidebar-section" x-data="{ open: {{ $hasActive(['admin.analytics.*','admin.ai.*']) ? 'true' : 'false' }} }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2.5">{!! $icon($icons['ai']) !!}AI &amp; Analytics</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.analytics.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.analytics.dashboard') }}">School Intelligence</a>
        <a href="{{ route('admin.analytics.risks.index') }}" class="sidebar-sub-link {{ $isActive('admin.analytics.risks.*') }}">Student Risk</a>
        <a href="{{ route('admin.analytics.dropout-risk.index') }}" class="sidebar-sub-link {{ $isActive('admin.analytics.dropout-risk.*') }}">Dropout Prediction</a>
        <a href="{{ route('admin.analytics.anomalies.index') }}" class="sidebar-sub-link {{ $isActive('admin.analytics.anomalies.*') }}">Anomaly Detection</a>
        <a href="{{ route('admin.ai.chat-data.index') }}" class="sidebar-sub-link {{ $isActive('admin.ai.chat-data.*') }}">Tanya Data (AI)</a>
        <a href="{{ route('admin.ai.ocr.index') }}" class="sidebar-sub-link {{ $isActive('admin.ai.ocr.*') }}">OCR Dokumen</a>
        <a href="{{ route('admin.ai.providers.index') }}" class="sidebar-sub-link {{ $isActive('admin.ai.providers.*') }}">AI Providers</a>
        <a href="{{ route('admin.ai.usage') }}" class="sidebar-sub-link {{ $isActive('admin.ai.usage') }}">AI Usage</a>
    </div>
</div>

{{-- REPORTS --}}
<div class="sidebar-section" x-data="{ open: {{ $hasActive(['admin.reports.*','admin.foundation.benchmark.*']) ? 'true' : 'false' }} }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2.5">{!! $icon($icons['reports']) !!}Reports</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.reports.spp-aging') }}" class="sidebar-sub-link {{ $isActive('admin.reports.spp-aging') }}">SPP Aging</a>
        <a href="{{ route('admin.reports.attendance-pct') }}" class="sidebar-sub-link {{ $isActive('admin.reports.attendance-pct') }}">Attendance</a>
        <a href="{{ route('admin.reports.grade-distribution') }}" class="sidebar-sub-link {{ $isActive('admin.reports.grade-distribution') }}">Grade Distribution</a>
        <a href="{{ route('admin.reports.discipline-leaderboard') }}" class="sidebar-sub-link {{ $isActive('admin.reports.discipline-leaderboard') }}">Discipline</a>
        <a href="{{ route('admin.reports.cash-flow') }}" class="sidebar-sub-link {{ $isActive('admin.reports.cash-flow') }}">Cash Flow</a>
        <a href="{{ route('admin.reports.builder.index') }}" class="sidebar-sub-link {{ $isActive('admin.reports.builder.*') }}">Report Builder</a>
        <a href="{{ route('admin.foundation.benchmark.index') }}" class="sidebar-sub-link {{ $isActive('admin.foundation.benchmark.*') }}">Benchmark</a>
    </div>
</div>

@if($canAutomation)
{{-- AUTOMATION --}}
<div class="sidebar-section" x-data="{ open: {{ $hasActive(['admin.webhooks.*']) ? 'true' : 'false' }} }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2.5">{!! $icon($icons['automation']) !!}Automation</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.workflow.create') }}" class="sidebar-sub-link {{ $isActive('admin.workflow.create') }}">Workflow Request</a>
        <a href="{{ route('admin.webhooks.index') }}" class="sidebar-sub-link {{ $isActive('admin.webhooks.*') }}">Webhooks</a>
        <a href="{{ route('admin.automation.rules.index') }}" class="sidebar-sub-link {{ $isActive('admin.automation.*') }}">Automation Rules</a>
    </div>
</div>

{{-- SYSTEM --}}
<div class="sidebar-section" x-data="{ open: {{ $hasActive(['admin.branding.*','admin.blog.*','admin.documents.*','admin.letters.*','admin.surveys.*','admin.exports.*','admin.audit.*','admin.internal-audit.*','admin.signage.*','admin.dashboard-tv.*','admin.accreditation.*','admin.compliance.*','admin.adiwiyata.*']) ? 'true' : 'false' }} }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2.5">{!! $icon($icons['system']) !!}System</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.branding.show') }}" class="sidebar-sub-link {{ $isActive('admin.branding.show') }}">Branding</a>
        <a href="{{ route('admin.branding.website.pages') }}" class="sidebar-sub-link {{ $isActive('admin.branding.website.*') }}">Website Builder</a>
        <a href="{{ route('admin.blog.index') }}" class="sidebar-sub-link {{ $isActive('admin.blog.*') }}">Blog</a>
        <a href="{{ route('admin.documents.index') }}" class="sidebar-sub-link {{ $isActive('admin.documents.*') }}">Documents</a>
        <a href="{{ route('admin.documents.approvals') }}" class="sidebar-sub-link {{ $isActive('admin.documents.approvals') }}">Document Approvals</a>
        <a href="{{ route('admin.letters.templates') }}" class="sidebar-sub-link {{ $isActive('admin.letters.*') }}">Letters</a>
        <a href="{{ route('admin.surveys.templates.index') }}" class="sidebar-sub-link {{ $isActive('admin.surveys.*') }}">Surveys</a>
        <a href="{{ route('admin.exports.index') }}" class="sidebar-sub-link {{ $isActive('admin.exports.*') }}">Exports</a>
        <a href="{{ route('admin.audit.index') }}" class="sidebar-sub-link {{ $isActive('admin.audit.*') }}">Audit Log</a>
        <a href="{{ route('admin.internal-audit.index') }}" class="sidebar-sub-link {{ $isActive('admin.internal-audit.*') }}">Internal Audit</a>
        <a href="{{ route('admin.signage.config') }}" class="sidebar-sub-link {{ $isActive('admin.signage.*') }}">Digital Signage</a>
        <a href="{{ route('admin.dashboard-tv.config') }}" class="sidebar-sub-link {{ $isActive('admin.dashboard-tv.*') }}">Dashboard TV</a>
        <a href="{{ route('admin.accreditation.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.accreditation.*') }}">Accreditation</a>
        <a href="{{ route('admin.compliance.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.compliance.*') }}">Compliance</a>
        <a href="{{ route('admin.adiwiyata.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.adiwiyata.*') }}">Adiwiyata</a>
    </div>
</div>
@endif
