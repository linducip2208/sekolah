<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><style>
body { font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333; }
.header { background: #4f46e5; color: white; padding: 24px; text-align: center; }
.content { padding: 24px; }
.alert-box { background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 16px; margin: 16px 0; }
.btn { display: inline-block; background: #4f46e5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; }
.footer { text-align: center; color: #888; font-size: 12px; padding: 16px; border-top: 1px solid #eee; }
</style></head>
<body>
<div class="header"><h2>Sikad Pro</h2></div>
<div class="content">
    <h3>Langganan Akan Segera Berakhir</h3>
    <p>Yth. Admin Sekolah <strong>{{ $schoolName }}</strong>,</p>
    <div class="alert-box">
        ⚠️ Langganan plan <strong>{{ $planName }}</strong> Anda akan berakhir pada <strong>{{ $expiresAt }}</strong>.
    </div>
    <p>Untuk memastikan layanan tetap berjalan tanpa gangguan, silakan perpanjang langganan sebelum tanggal tersebut.</p>
    <p style="margin-top:16px"><a href="#" class="btn">Perpanjang Sekarang</a></p>
    <p style="margin-top:16px;font-size:12px;color:#888">Jika ada pertanyaan, hubungi kami di support@whitelabel.co.id</p>
</div>
<div class="footer">Sikad Pro — Platform Manajemen Sekolah</div>
</body>
</html>
