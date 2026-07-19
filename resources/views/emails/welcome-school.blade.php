<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><style>
body { font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333; }
.header { background: #4f46e5; color: white; padding: 24px; text-align: center; }
.content { padding: 24px; }
.cred-box { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 16px; margin: 16px 0; font-family: monospace; }
.btn { display: inline-block; background: #4f46e5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; }
</style></head>
<body>
<div class="header"><h2>🎉 Selamat Datang di eSchool SaaS!</h2></div>
<div class="content">
    <p>Yth. Admin <strong>{{ $schoolName }}</strong>,</p>
    <p>Sekolah Anda berhasil didaftarkan. Berikut informasi akses Anda:</p>
    <div class="cred-box">
        <p><strong>URL:</strong> https://{{ $subdomain }}.{{ config('multitenancy.base_domain') }}</p>
        <p><strong>Email:</strong> {{ $adminEmail }}</p>
        <p><strong>Password:</strong> {{ $adminPassword }}</p>
    </div>
    <p>⚠️ Segera ubah password setelah login pertama.</p>
    <p style="margin-top:16px"><a href="https://{{ $subdomain }}.{{ config('multitenancy.base_domain') }}" class="btn">Mulai Sekarang</a></p>
</div>
</body>
</html>
