@extends('layouts.school-admin')
@section('title', 'AI Assistant')
@section('sidebar')@include('school-admin.partials.sidebar-extended')@endsection
@section('content')
<div class="space-y-6">
    <div class="flex justify-between">
        <div>
            <h2 class="text-xl font-bold">AI Provider Configuration</h2>
            <p class="text-sm text-gray-600">Bring Your Own Key — Anda input API key sendiri (OpenAI, Anthropic, Gemini, atau compatible).</p>
        </div>
        <button class="btn-brand">+ Tambah Provider</button>
    </div>

    <div class="bg-yellow-50 border border-yellow-200 rounded p-3 text-sm">
        ⚠️ <strong>No vendor lock-in.</strong> Pilih format API: OpenAI Compatible (untuk OpenAI, DeepSeek, Groq, Ollama, dll), Anthropic Format, atau Gemini Format. Tidak ada nama vendor di code.
    </div>

    <div class="bg-white rounded-lg shadow">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr><th class="px-4 py-2">Nama</th><th class="px-4 py-2">Format</th><th class="px-4 py-2">Base URL</th><th class="px-4 py-2">API Key</th><th class="px-4 py-2">Status</th></tr>
            </thead>
            <tbody class="divide-y">
                @forelse($providers as $p)
                    <tr>
                        <td class="px-4 py-2 font-medium">{{ $p->name }}</td>
                        <td class="px-4 py-2"><code class="text-xs bg-gray-100 px-2 py-0.5 rounded">{{ $p->api_format }}</code></td>
                        <td class="px-4 py-2 font-mono text-xs">{{ $p->base_url }}</td>
                        <td class="px-4 py-2 font-mono text-xs">{{ $p->maskedApiKey() ?? '—' }}</td>
                        <td class="px-4 py-2">@if($p->is_active)<span class="px-2 py-0.5 bg-green-100 text-green-800 rounded text-xs">Active</span>@else<span class="px-2 py-0.5 bg-gray-100 rounded text-xs">Off</span>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Belum ada provider AI. Tambahkan untuk aktifkan study assistant, AI grading, dll.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="px-4 py-3 border-b font-bold">📊 Usage 30 Hari Terakhir</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr><th class="px-4 py-2">Feature</th><th class="px-4 py-2">Requests</th><th class="px-4 py-2">Input Tokens</th><th class="px-4 py-2">Output Tokens</th><th class="px-4 py-2">Estimated Cost</th></tr>
            </thead>
            <tbody class="divide-y">
                @forelse($usage30d as $u)
                    <tr>
                        <td class="px-4 py-2 font-medium">{{ $u->feature_key }}</td>
                        <td class="px-4 py-2">{{ number_format($u->cnt) }}</td>
                        <td class="px-4 py-2 text-xs">{{ number_format($u->in_tok) }}</td>
                        <td class="px-4 py-2 text-xs">{{ number_format($u->out_tok) }}</td>
                        <td class="px-4 py-2 font-bold">${{ number_format($u->cost, 4) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada usage</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
