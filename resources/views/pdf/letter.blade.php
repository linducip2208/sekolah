<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat - {{ $letter->letter_number }}</title>
    <style>
        @page { margin: 2.5cm 2cm 2.5cm 2cm; size: a4; }
        body { font-family: 'Times New Roman', Georgia, serif; font-size: 12pt; line-height: 1.6; color: #1a1a1a; }

        .letterhead { text-align: center; margin-bottom: 20px; border-bottom: 3px double #0b1d3a; padding-bottom: 18px; }
        .letterhead .logo { max-height: 70px; margin-bottom: 8px; }
        .letterhead .school-name { font-size: 16pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2px; }
        .letterhead .school-address { font-size: 10pt; margin-top: 4px; }

        .meta { margin-bottom: 24px; }
        .meta table { width: 100%; border-collapse: collapse; }
        .meta td { vertical-align: top; padding: 2px 0; font-size: 11pt; }
        .meta .label { width: 110px; }

        .content { margin-top: 20px; text-align: justify; }
        .content p { margin: 8px 0; }
        .content ul, .content ol { margin: 8px 0; padding-left: 24px; }

        .signature { margin-top: 40px; display: flex; justify-content: flex-end; }
        .signature .box { text-align: center; width: 45%; }
        .signature .place-date { margin-bottom: 8px; font-size: 11pt; }
        .signature .name { margin-top: 60px; font-weight: bold; font-size: 11pt; text-decoration: underline; }
        .signature .title { font-size: 10pt; margin-top: 2px; }

        .footer { position: fixed; bottom: 0; left: 0; right: 0; font-size: 8pt; text-align: center; color: #999; padding: 8px 0; border-top: 1px solid #ddd; }
    </style>
</head>
<body>

<div class="letterhead">
    @if(!empty($branding['logos']['primary']))
        <img src="{{ $branding['logos']['primary'] }}" class="logo" alt="Logo">
    @endif
    <div class="school-name">{{ $branding['display_name'] ?? config('app.name', 'Sekolah') }}</div>
    <div class="school-address">{{ $branding['address'] ?? '' }}</div>
    @if(!empty($branding['phone']))
        <div class="school-address">Telp: {{ $branding['phone'] }} | Email: {{ $branding['email'] ?? '' }}</div>
    @endif
</div>

<div class="meta">
    <table>
        <tr>
            <td class="label">Nomor</td>
            <td>: {{ $letter->letter_number }}</td>
        </tr>
        <tr>
            <td class="label">Lampiran</td>
            <td>: —</td>
        </tr>
        <tr>
            <td class="label">Perihal</td>
            <td>: <strong>{{ $letter->subject }}</strong></td>
        </tr>
    </table>
</div>

<div style="margin-bottom: 16px;">
    <div style="margin-bottom: 4px;">Kepada Yth.</div>
    <div><strong>{{ $letter->recipient_name }}</strong></div>
    @if($letter->recipient_address)
        <div>{{ $letter->recipient_address }}</div>
    @endif
    <div>di Tempat</div>
</div>

<div style="margin-bottom: 20px; font-size: 11pt;">
    <em>Assalamu'alaikum Wr. Wb.</em>
</div>

<div class="content">
    {!! $letter->content !!}
</div>

<div style="margin-top: 20px; font-size: 11pt;">
    <p>Demikian surat ini kami sampaikan. Atas perhatian dan kerjasamanya, kami ucapkan terima kasih.</p>
    <p style="margin-top: 16px;"><em>Wassalamu'alaikum Wr. Wb.</em></p>
</div>

<div class="signature">
    <div class="box">
        <div class="place-date">{{ $letter->school->city ?? '__________' }}, {{ ($letter->issued_at ?? $letter->created_at)->format('d F Y') }}</div>
        <div style="font-size: 10pt;">Kepala Sekolah,</div>
        <div class="name">{{ $branding['principal_name'] ?? '_______________________________' }}</div>
        <div class="title">NIP. {{ $branding['principal_nip'] ?? '_______________________________' }}</div>
    </div>
</div>

<div class="footer">
    Dokumen ini diterbitkan melalui eSchool SaaS &mdash; {{ config('app.name') }}
</div>

</body>
</html>
