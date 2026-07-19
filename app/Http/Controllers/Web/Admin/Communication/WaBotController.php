<?php

namespace App\Http\Controllers\Web\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Models\Communication\WaBotCommand;
use App\Models\Communication\WaBotConversation;
use App\Services\Communication\WaBotService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WaBotController extends Controller
{
    public function commands(): View
    {
        $commands = WaBotCommand::where('school_id', auth()->user()->school_id)
            ->orderBy('command_keyword')
            ->get();

        return view('school-admin.communication.wa-bot.commands', compact('commands'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'command_keyword' => 'required|string|max:100',
            'response_type'   => 'required|in:static,text_function',
            'static_response' => 'nullable|string',
            'function_method' => 'nullable|string|max:100',
            'description'     => 'nullable|string|max:255',
        ]);

        WaBotCommand::create([
            'school_id'       => auth()->user()->school_id,
            'command_keyword' => strtolower($validated['command_keyword']),
            'response_type'   => $validated['response_type'],
            'static_response' => $validated['static_response'],
            'function_method' => $validated['function_method'],
            'description'     => $validated['description'],
            'is_active'       => true,
        ]);

        return redirect()->route('admin.wa-bot.commands.index')
            ->with('success', 'Perintah bot berhasil ditambahkan.');
    }

    public function update(Request $request, WaBotCommand $command): RedirectResponse
    {
        $validated = $request->validate([
            'command_keyword' => 'required|string|max:100',
            'response_type'   => 'required|in:static,text_function',
            'static_response' => 'nullable|string',
            'function_method' => 'nullable|string|max:100',
            'description'     => 'nullable|string|max:255',
        ]);

        $command->update([
            'command_keyword' => strtolower($validated['command_keyword']),
            'response_type'   => $validated['response_type'],
            'static_response' => $validated['static_response'],
            'function_method' => $validated['function_method'],
            'description'     => $validated['description'],
        ]);

        return redirect()->route('admin.wa-bot.commands.index')
            ->with('success', 'Perintah bot berhasil diperbarui.');
    }

    public function toggle(WaBotCommand $command): RedirectResponse
    {
        $command->update(['is_active' => !$command->is_active]);

        $status = $command->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Perintah '{$command->command_keyword}' {$status}.");
    }

    public function destroy(WaBotCommand $command): RedirectResponse
    {
        $command->delete();
        return back()->with('success', 'Perintah bot berhasil dihapus.');
    }

    public function conversations(Request $request): View
    {
        $schoolId = auth()->user()->school_id;

        $conversations = WaBotConversation::where('school_id', $schoolId)
            ->when($request->phone, fn($q) => $q->where('phone', 'like', "%{$request->phone}%"))
            ->when($request->direction, fn($q) => $q->where('message_direction', $request->direction))
            ->with('student.user:id,name')
            ->orderByDesc('created_at')
            ->paginate(50);

        return view('school-admin.communication.wa-bot.conversations', compact('conversations'));
    }

    public function test(Request $request): RedirectResponse
    {
        $request->validate([
            'phone'   => 'required|string|max:30',
            'message' => 'required|string',
        ]);

        $service = app(WaBotService::class);
        $result = $service->processIncoming($request->phone, $request->message);
        $service->sendReply($request->phone, $result['reply'], auth()->user()->school_id);

        return back()->with('success', "Pesan terkirim: {$result['reply']}");
    }
}
