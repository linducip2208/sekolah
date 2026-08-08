<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracer Study Alumni — Sikad Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.bunny.net/css?family=playfair-display:400,600,700|inter:300,400,600|cormorant-garamond:400,500,600,400i&display=swap" rel="stylesheet">
    <style>
        :root { --c-primary: #0b1d3a; --c-accent: #b8860b; }
        body { font-family: 'Inter', sans-serif; background: #f8f5ee; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .font-display { font-family: 'Playfair Display', serif; }
        .font-serif { font-family: 'Cormorant Garamond', serif; }
        .card { background: #fff; border: 1px solid rgba(11,29,58,.12); max-width: 640px; width: 100%; padding: 2rem; }
    </style>
</head>
<body>
<div class="card">
    <div class="text-center mb-6">
        <div class="text-4xl mb-2">🎓</div>
        <h1 class="font-display text-2xl font-bold" style="color:var(--c-primary)">Tracer Study Alumni</h1>
        <p class="font-serif italic text-gray-500 mt-1">Bantu kami mengetahui kabar Anda setelah lulus</p>
    </div>

    <form id="tracerForm" class="space-y-4">
        <div>
            <label class="block text-xs uppercase tracking-wider font-semibold mb-1" style="color:var(--c-primary)">Status Saat Ini *</label>
            <select id="status" required class="w-full border-2 border-gray-200 px-3 py-2 font-serif text-sm">
                <option value="">— Pilih —</option>
                <option value="kerja">Bekerja</option>
                <option value="kuliah">Kuliah</option>
                <option value="wirausaha">Wirausaha</option>
                <option value="menganggur">Menganggur</option>
                <option value="lainnya">Lainnya</option>
            </select>
        </div>
        <div>
            <label class="block text-xs uppercase tracking-wider font-semibold mb-1" style="color:var(--c-primary)">Nama Perusahaan / Kampus</label>
            <input type="text" id="company_name" class="w-full border-2 border-gray-200 px-3 py-2 font-serif text-sm" placeholder="...">
        </div>
        <div>
            <label class="block text-xs uppercase tracking-wider font-semibold mb-1" style="color:var(--c-primary)">Jabatan / Program Studi</label>
            <input type="text" id="position" class="w-full border-2 border-gray-200 px-3 py-2 font-serif text-sm" placeholder="...">
        </div>
        <div>
            <label class="block text-xs uppercase tracking-wider font-semibold mb-1" style="color:var(--c-primary)">Rentang Gaji</label>
            <select id="salary_range" class="w-full border-2 border-gray-200 px-3 py-2 font-serif text-sm">
                <option value="">— Pilih —</option>
                <option value="< 1 juta">< 1 juta</option>
                <option value="1-3 juta">1-3 juta</option>
                <option value="3-5 juta">3-5 juta</option>
                <option value="5-10 juta">5-10 juta</option>
                <option value="10-20 juta">10-20 juta</option>
                <option value="> 20 juta">> 20 juta</option>
            </select>
        </div>
        <div>
            <label class="block text-xs uppercase tracking-wider font-semibold mb-1" style="color:var(--c-primary)">Apakah pekerjaan/kuliah relevan dengan jurusan?</label>
            <div class="flex gap-4">
                <label class="flex items-center gap-2"><input type="radio" name="is_relevant" value="1"> Ya</label>
                <label class="flex items-center gap-2"><input type="radio" name="is_relevant" value="0"> Tidak</label>
            </div>
        </div>
        <div>
            <label class="block text-xs uppercase tracking-wider font-semibold mb-1" style="color:var(--c-primary)">Saran untuk Sekolah</label>
            <textarea id="feedback" rows="3" class="w-full border-2 border-gray-200 px-3 py-2 font-serif text-sm" placeholder="Saran dan masukan untuk almamater..."></textarea>
        </div>

        <div id="customQuestions"></div>

        <button type="submit" class="w-full py-3 text-white font-semibold text-sm uppercase tracking-wider" style="background:var(--c-primary)">Kirim</button>
        <div id="msg" class="hidden text-center text-sm font-serif mt-3 p-3"></div>
    </form>
</div>

<script>
const urlParams = new URLSearchParams(window.location.search);
const alumniId = urlParams.get('alumni_id');

document.addEventListener('DOMContentLoaded', async () => {
    if (!alumniId) {
        document.getElementById('msg').className = 'text-center text-sm font-serif mt-3 p-3 text-red-700';
        document.getElementById('msg').textContent = 'Link tidak valid. Silakan gunakan link yang dikirim oleh sekolah.';
        document.querySelector('button').disabled = true;
        return;
    }

    try {
        const res = await fetch('/api/tracer/form?alumni_id=' + alumniId);
        if (!res.ok) throw new Error('Not found');
        const data = await res.json();

        const customDiv = document.getElementById('customQuestions');
        if (data.questions && data.questions.length > 0) {
            data.questions.forEach(q => {
                const html = document.createElement('div');
                html.className = 'border-t border-gray-200 pt-4 mt-4';
                let input = '';
                if (q.question_type === 'radio' && q.options) {
                    input = q.options.map((o, i) => `<label class="flex items-center gap-2 mb-1"><input type="radio" name="answers[${q.id}]" value="${o}"> ${o}</label>`).join('');
                } else if (q.question_type === 'select' && q.options) {
                    input = '<select name="answers[' + q.id + ']" class="w-full border-2 border-gray-200 px-3 py-2 font-serif text-sm"><option value="">— Pilih —</option>' + q.options.map(o => `<option>${o}</option>`).join('') + '</select>';
                } else if (q.question_type === 'textarea') {
                    input = '<textarea name="answers[' + q.id + ']" rows="3" class="w-full border-2 border-gray-200 px-3 py-2 font-serif text-sm" placeholder="..."></textarea>';
                } else {
                    input = '<input type="text" name="answers[' + q.id + ']" class="w-full border-2 border-gray-200 px-3 py-2 font-serif text-sm" placeholder="...">';
                }
                html.innerHTML = `<label class="block text-xs uppercase tracking-wider font-semibold mb-1" style="color:var(--c-primary)">${q.question_text}</label>${input}`;
                customDiv.appendChild(html);
            });
        }
    } catch (e) {
        document.getElementById('msg').className = 'text-center text-sm font-serif mt-3 p-3 text-red-700';
        document.getElementById('msg').textContent = 'Data alumni tidak ditemukan.';
        document.querySelector('button').disabled = true;
    }
});

document.getElementById('tracerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const msg = document.getElementById('msg');
    msg.className = 'text-center text-sm font-serif mt-3 p-3 text-blue-700';
    msg.textContent = 'Mengirim...';
    msg.classList.remove('hidden');

    const answers = {};
    document.querySelectorAll('[name^="answers["]').forEach(el => {
        const name = el.name.match(/\[(\d+)\]/)[1];
        if (el.type === 'radio' && !el.checked) return;
        answers[name] = el.value;
    });

    const isRelevantEl = document.querySelector('input[name="is_relevant"]:checked');

    const body = {
        alumni_id: parseInt(alumniId),
        status: document.getElementById('status').value,
        company_name: document.getElementById('company_name').value || null,
        position: document.getElementById('position').value || null,
        salary_range: document.getElementById('salary_range').value || null,
        is_relevant: isRelevantEl ? (isRelevantEl.value === '1') : null,
        feedback: document.getElementById('feedback').value || null,
        answers: Object.keys(answers).length ? answers : null,
    };

    try {
        const res = await fetch('/api/tracer/submit', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
            body: JSON.stringify(body),
        });
        const data = await res.json();
        msg.className = 'text-center text-sm font-serif mt-3 p-3 text-green-700';
        msg.textContent = data.message || 'Terima kasih! Data tracer study berhasil disimpan.';
        document.querySelector('button').disabled = true;
    } catch (err) {
        msg.className = 'text-center text-sm font-serif mt-3 p-3 text-red-700';
        msg.textContent = 'Gagal mengirim. Silakan coba lagi.';
    }
});
</script>
</body>
</html>
