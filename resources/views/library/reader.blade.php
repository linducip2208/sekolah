<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $book->title }} — eLibrary</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        *, *::before, *::after { margin:0;padding:0;box-sizing:border-box; }
        body { font-family:'Inter',sans-serif; background:#0f172a; color:#e2e8f0; overflow:hidden; height:100vh; display:flex; flex-direction:column; }
        .toolbar { display:flex;align-items:center;gap:12px;padding:8px 16px;background:#1e293b;border-bottom:1px solid #334155;flex-shrink:0; }
        .toolbar button, .toolbar select { background:#334155;border:1px solid #475569;color:#e2e8f0;padding:6px 14px;border-radius:6px;font-size:13px;cursor:pointer;font-family:'Inter',sans-serif; }
        .toolbar button:hover { background:#475569; }
        .toolbar button:disabled { opacity:.4;cursor:default; }
        .toolbar .page-info { font-size:13px;color:#94a3b8;min-width:120px;text-align:center; }
        .toolbar .title { font-weight:600;font-size:14px;color:#f1f5f9;margin-right:auto;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:300px; }
        .viewer-container { flex:1;overflow:auto;display:flex;justify-content:center;background:#0f172a;padding:20px; }
        #pdf-canvas { box-shadow:0 4px 30px rgba(0,0,0,.5);max-width:100%; }
        .progress-bar { height:3px;background:#334155;position:fixed;bottom:0;left:0;right:0; }
        .progress-bar-fill { height:100%;background:#3b82f6;transition:width .3s ease; }
        .loading-overlay { position:fixed;inset:0;background:rgba(15,23,42,.9);display:flex;align-items:center;justify-content:center;z-index:10; }
        .loading-spinner { width:40px;height:40px;border:3px solid #334155;border-top-color:#3b82f6;border-radius:50%;animation:spin .8s linear infinite; }
        @keyframes spin { to { transform:rotate(360deg); } }
    </style>
</head>
<body x-data="pdfReader()" x-init="init('{{ $fileUrl }}', {{ $book->page_count ?? 0 }}, {{ $progress->current_page ?? 1 }}, '{{ $issue->access_token }}')">

    <div class="toolbar">
        <span class="title">{{ $book->title }}</span>
        <button @click="prevPage" :disabled="currentPage <= 1">&lt;</button>
        <span class="page-info">
            <input type="number" x-model="currentPageInput" @keydown.enter="goToPage" @change="goToPage"
                   style="width:45px;background:#334155;border:1px solid #475569;color:#e2e8f0;text-align:center;border-radius:4px;padding:4px;font-size:13px;">
            / <span x-text="totalPages || '?'"></span>
        </span>
        <button @click="nextPage" :disabled="currentPage >= totalPages">&gt;</button>
        <button @click="zoomOut">−</button>
        <span style="font-size:13px;color:#94a3b8;" x-text="Math.round(scale * 100) + '%'"></span>
        <button @click="zoomIn">+</button>
        @if($book->is_downloadable)
            <a href="{{ $fileUrl }}" class="no-underline"><button style="background:#3b82f6;border-color:#3b82f6;">Download</button></a>
        @endif
    </div>

    <div class="viewer-container" id="viewer-container">
        <canvas id="pdf-canvas"></canvas>
    </div>

    <div class="progress-bar"><div class="progress-bar-fill" :style="'width:' + progressPercent + '%'"></div></div>

    <div x-show="loading" class="loading-overlay"><div class="loading-spinner"></div></div>

    <script>
        function pdfReader() {
            return {
                pdfDoc: null,
                currentPage: 1,
                totalPages: 0,
                scale: 1.5,
                loading: true,
                fileUrl: '',
                token: '',
                progressPercent: 0,
                currentPageInput: 1,
                lastSaveTime: 0,

                async init(url, pageCount, startPage, token) {
                    this.fileUrl = url;
                    this.token = token;
                    this.totalPages = pageCount;
                    this.currentPage = startPage;
                    this.currentPageInput = startPage;

                    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

                    try {
                        this.pdfDoc = await pdfjsLib.getDocument(url).promise;
                        if (!pageCount || pageCount === 0) {
                            this.totalPages = this.pdfDoc.numPages;
                        }
                        this.loading = false;
                        await this.renderPage(this.currentPage);
                    } catch (e) {
                        console.error('Gagal memuat PDF:', e);
                        document.getElementById('viewer-container').innerHTML =
                            '<div style="color:#ef4444;padding:40px;text-align:center;">Gagal memuat buku. Periksa kembali link akses.</div>';
                        this.loading = false;
                    }
                },

                async renderPage(pageNum) {
                    if (!this.pdfDoc) return;
                    this.loading = true;
                    this.currentPage = pageNum;
                    this.currentPageInput = pageNum;

                    const page = await this.pdfDoc.getPage(pageNum);
                    const viewport = page.getViewport({ scale: this.scale });

                    const canvas = document.getElementById('pdf-canvas');
                    const context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    await page.render({ canvasContext: context, viewport: viewport }).promise;
                    this.loading = false;

                    this.updateProgress();

                    const container = document.getElementById('viewer-container');
                    if (container) container.scrollTop = 0;
                },

                async prevPage() {
                    if (this.currentPage > 1) await this.renderPage(this.currentPage - 1);
                },

                async nextPage() {
                    if (this.currentPage < this.totalPages) await this.renderPage(this.currentPage + 1);
                },

                goToPage() {
                    const p = parseInt(this.currentPageInput);
                    if (p >= 1 && p <= this.totalPages) {
                        this.renderPage(p);
                    } else {
                        this.currentPageInput = this.currentPage;
                    }
                },

                zoomIn() {
                    this.scale = Math.min(this.scale + 0.25, 3);
                    this.renderPage(this.currentPage);
                },

                zoomOut() {
                    this.scale = Math.max(this.scale - 0.25, 0.5);
                    this.renderPage(this.currentPage);
                },

                updateProgress() {
                    this.progressPercent = this.totalPages > 0
                        ? Math.round((this.currentPage / this.totalPages) * 100)
                        : 0;

                    const now = Date.now();
                    if (now - this.lastSaveTime < 3000) return;
                    this.lastSaveTime = now;
                    this.saveProgress();
                },

                async saveProgress() {
                    try {
                        await fetch('/api/v1/reading/progress', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                access_token: this.token,
                                current_page: this.currentPage,
                                total_pages: this.totalPages,
                            }),
                        });
                    } catch (e) {
                        console.error('Gagal menyimpan progres:', e);
                    }
                },
            };
        }
    </script>
</body>
</html>
