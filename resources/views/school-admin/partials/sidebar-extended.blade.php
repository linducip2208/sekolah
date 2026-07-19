@include('school-admin.partials.sidebar')

<div class="px-4 mt-4 mb-1 text-xs uppercase text-white/50 tracking-wider">Phase 8 — Student Lifecycle</div>
<a href="{{ route('admin.ppdb.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.ppdb.*') ? 'active' : '' }}">
    <span>👨‍🎓</span> PPDB
</a>
<a href="{{ route('admin.transport.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.transport.*') ? 'active' : '' }}">
    <span>🚌</span> Transport & Gerbang
</a>
<a href="{{ route('admin.medical.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.medical.*') ? 'active' : '' }}">
    <span>🏥</span> UKS / Klinik
</a>
<a href="{{ route('admin.counseling.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.counseling.*') ? 'active' : '' }}">
    <span>🧠</span> BP / BK
</a>
<a href="{{ route('admin.discipline.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.discipline.*') ? 'active' : '' }}">
    <span>📋</span> Tata Tertib
</a>

<div class="px-4 mt-4 mb-1 text-xs uppercase text-white/50 tracking-wider">Phase 9 — Teaching</div>
<a href="{{ route('admin.lesson-plan.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.lesson-plan.*') ? 'active' : '' }}">
    <span>📝</span> Lesson Plan / RPP
</a>
<a href="{{ route('admin.canteen.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.canteen.*') ? 'active' : '' }}">
    <span>🍱</span> Kantin
</a>
<a href="{{ route('admin.religious.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.religious.*') ? 'active' : '' }}">
    <span>🕌</span> Religious / Pesantren
</a>
<a href="{{ route('admin.ai.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.ai.*') ? 'active' : '' }}">
    <span>🤖</span> AI Assistant
</a>
<a href="{{ route('admin.live-class.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.live-class.*') ? 'active' : '' }}">
    <span>🎥</span> Live Class
</a>

<div class="px-4 mt-4 mb-1 text-xs uppercase text-white/50 tracking-wider">Phase 10 — Engagement</div>
<a href="{{ route('admin.donations.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.donations.*') ? 'active' : '' }}">
    <span>💝</span> Donasi
</a>
<a href="{{ route('admin.events.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.events.*') ? 'active' : '' }}">
    <span>🎉</span> Events
</a>
<a href="{{ route('admin.achievements.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.achievements.*') ? 'active' : '' }}">
    <span>🏆</span> Achievement
</a>
<a href="{{ route('admin.scholarship.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.scholarship.*') ? 'active' : '' }}">
    <span>🎓</span> Beasiswa
</a>

<div class="px-4 mt-4 mb-1 text-xs uppercase text-white/50 tracking-wider">Phase 11 — Operations</div>
<a href="{{ route('admin.dapodik.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dapodik.*') ? 'active' : '' }}">
    <span>🏛️</span> Dapodik Sync
</a>
<a href="{{ route('admin.visitors.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.visitors.*') ? 'active' : '' }}">
    <span>👋</span> Tamu
</a>
<a href="{{ route('admin.inventory.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
    <span>📦</span> Inventory
</a>
<a href="{{ route('admin.analytics.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
    <span>📊</span> Analytics
</a>
