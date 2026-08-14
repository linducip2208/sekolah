@props(['theme' => [], 'landing' => []])
<section class="lp-section lp-bg-surface" id="demo">
    <div class="lp-container max-w-4xl">
        <div class="text-center mb-12">
            <p class="lp-kicker mb-3">Akses Sandbox</p>
            <h2 class="lp-title text-3xl sm:text-4xl">Coba langsung dengan akun demo.</h2>
            <p class="lp-lead mt-4">Jelajahi panel admin, portal orang tua, dan dashboard siswa tanpa perlu mendaftar.</p>
        </div>
        <div class="lp-card lp-card-{{ $theme['style']['card'] }} overflow-x-auto reveal">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide" style="color: var(--lp-muted); border-bottom: 1px solid var(--lp-border);">
                        <th class="px-5 py-3 font-semibold">Role</th>
                        <th class="px-5 py-3 font-semibold">Email</th>
                        <th class="px-5 py-3 font-semibold">Password</th>
                        <th class="px-5 py-3 font-semibold">Akses</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach([
                        ['Super Admin', 'super@sikadpro.app', 'SuperAdmin123!', route('super.login')],
                        ['Administrator Sekolah', 'admin@sman1demo.sch.id', 'Admin123!', route('admin.login')],
                        ['Guru / Staff', 'guru1@sman1demo.sch.id', 'Guru123!', route('admin.login')],
                        ['Orang Tua / Wali', 'wali1@sman1demo.sch.id', 'Wali123!', route('portal.dashboard')],
                        ['Siswa', 'siswa0_0@sman1demo.sch.id', 'Siswa123!', route('student.dashboard')],
                    ] as [$role, $email, $pass, $link])
                        <tr style="border-bottom: 1px solid var(--lp-border);">
                            <td class="px-5 py-3.5 font-medium" style="color: var(--lp-ink);">{{ $role }}</td>
                            <td class="px-5 py-3.5 font-mono text-xs" style="color: var(--lp-muted);">{{ $email }}</td>
                            <td class="px-5 py-3.5 font-mono text-xs" style="color: var(--lp-muted);">{{ $pass }}</td>
                            <td class="px-5 py-3.5"><a href="{{ $link }}" class="text-xs font-semibold" style="color: var(--lp-accent);">Buka →</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="text-center mt-8">
            <a href="{{ route('admin.login') }}" class="lp-btn">Masuk ke Demo</a>
        </div>
    </div>
</section>
