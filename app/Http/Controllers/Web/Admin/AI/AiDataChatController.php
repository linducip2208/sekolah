<?php

namespace App\Http\Controllers\Web\Admin\AI;

use App\Http\Controllers\Controller;
use App\Models\AI\AiDataChatLog;
use App\Services\AI\DataChatService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiDataChatController extends Controller
{
    public function __construct(private DataChatService $service) {}

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $history = AiDataChatLog::where('school_id', $this->schoolId())
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $last = $request->session()->get('ai_chat_last');

        return view('school-admin.ai.chat-data', [
            'history'  => $history,
            'metrics'  => $this->service->metrics(),
            'last'     => $last,
        ]);
    }

    public function ask(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'question' => 'required|string|max:500',
        ]);

        $result = $this->service->ask(
            $this->schoolId(),
            auth()->id(),
            $data['question']
        );

        return back()->with('ai_chat_last', $result)->with('ai_chat_question', $data['question']);
    }
}
