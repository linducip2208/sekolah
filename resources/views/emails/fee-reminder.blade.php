<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><style>
body{font-family:Arial,sans-serif;max-width:600px;margin:0 auto;color:#333}
.header{background:#059669;color:white;padding:24px;text-align:center}
.content{padding:24px}
.fee-box{background:#ecfdf5;border:1px solid #6ee7b7;border-radius:8px;padding:16px;margin:16px 0;text-align:center}
.amount{font-size:28px;font-weight:bold;color:#065f46}
</style></head>
<body>
<div class="header"><h2>Pengingat Pembayaran SPP</h2></div>
<div class="content">
    <p>Yth. Orang Tua/Wali Murid <strong>{{ $studentName }}</strong>,</p>
    <p>Kami mengingatkan bahwa tagihan SPP belum dibayar:</p>
    <div class="fee-box">
        <p class="amount">Rp {{ $amount }}</p>
        <p>Jatuh Tempo: <strong>{{ $dueDate }}</strong></p>
    </div>
    <p>Mohon segera melakukan pembayaran. Terima kasih.</p>
</div>
</body>
</html>
