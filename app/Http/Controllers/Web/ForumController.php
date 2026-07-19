<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Communication\ForumCategory;
use App\Models\Communication\ForumReply;
use App\Models\Communication\ForumSubscription;
use App\Models\Communication\ForumTopic;
use App\Services\Communication\WhatsAppNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ForumController extends Controller
{
    public function __construct(private WhatsAppNotificationService $whatsapp) {}

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(): View
    {
        $categories = ForumCategory::where('school_id', $this->schoolId())
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->with(['topics' => fn($q) => $q->with('user:id,name')->withCount('replies')->orderByDesc('last_reply_at')->limit(10)])
            ->get();

        $recentTopics = ForumTopic::where('school_id', $this->schoolId())
            ->with(['user:id,name', 'category'])
            ->withCount('replies')
            ->orderByDesc('last_reply_at')
            ->limit(10)
            ->get();

        return view('forum.index', compact('categories', 'recentTopics'));
    }

    public function category(string $categoryId): View
    {
        $category = ForumCategory::where('school_id', $this->schoolId())
            ->where('is_active', true)
            ->findOrFail($categoryId);

        $topics = ForumTopic::where('forum_category_id', $category->id)
            ->with(['user:id,name'])
            ->withCount('replies')
            ->orderByDesc('is_pinned')
            ->orderByDesc('last_reply_at')
            ->paginate(15);

        return view('forum.category', compact('category', 'topics'));
    }

    public function showTopic(ForumTopic $topic): View
    {
        abort_unless($topic->school_id === $this->schoolId(), 403);

        $topic->increment('view_count');

        $replies = $topic->replies()
            ->where('is_approved', true)
            ->whereNull('parent_id')
            ->with('children.user:id,name')
            ->orderBy('created_at')
            ->paginate(30);

        $isSubscribed = ForumSubscription::where('forum_topic_id', $topic->id)
            ->where('user_id', auth()->id())
            ->exists();

        return view('forum.topic', compact('topic', 'replies', 'isSubscribed'));
    }

    public function createTopic(): View
    {
        $categories = ForumCategory::where('school_id', $this->schoolId())
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('forum.create', compact('categories'));
    }

    public function storeTopic(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'forum_category_id' => 'required|exists:forum_categories,id',
            'title'             => 'required|string|max:255',
            'content'           => 'required|string|max:10000',
        ]);

        $topic = ForumTopic::create([
            'school_id'        => $this->schoolId(),
            'forum_category_id' => $data['forum_category_id'],
            'user_id'          => auth()->id(),
            'title'            => $data['title'],
            'content'          => $data['content'],
            'last_reply_at'    => now(),
        ]);

        return redirect()->route('forum.topic', $topic)
            ->with('success', 'Topik berhasil dibuat.');
    }

    public function storeReply(Request $request, ForumTopic $topic): RedirectResponse
    {
        abort_unless($topic->school_id === $this->schoolId(), 403);

        if ($topic->is_locked) {
            return back()->with('error', 'Topik ini sudah dikunci.');
        }

        $data = $request->validate([
            'content'   => 'required|string|max:5000',
            'parent_id' => 'nullable|exists:forum_replies,id',
        ]);

        $reply = ForumReply::create([
            'school_id'      => $this->schoolId(),
            'forum_topic_id' => $topic->id,
            'user_id'        => auth()->id(),
            'content'        => $data['content'],
            'parent_id'      => $data['parent_id'] ?? null,
            'is_approved'    => true,
        ]);

        $topic->update(['last_reply_at' => now()]);

        $this->notifySubscribers($topic, $reply);

        return back()->with('success', 'Balasan berhasil dikirim.');
    }

    public function subscribe(ForumTopic $topic): RedirectResponse
    {
        abort_unless($topic->school_id === $this->schoolId(), 403);

        $sub = ForumSubscription::withTrashed()
            ->where('forum_topic_id', $topic->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($sub && $sub->trashed()) {
            $sub->restore();
        } elseif (!$sub) {
            ForumSubscription::create([
                'school_id'      => $this->schoolId(),
                'forum_topic_id' => $topic->id,
                'user_id'        => auth()->id(),
            ]);
        }

        return back()->with('success', 'Anda berlangganan notifikasi topik ini.');
    }

    public function unsubscribe(ForumTopic $topic): RedirectResponse
    {
        abort_unless($topic->school_id === $this->schoolId(), 403);

        ForumSubscription::where('forum_topic_id', $topic->id)
            ->where('user_id', auth()->id())
            ->delete();

        return back()->with('success', 'Langganan notifikasi dihentikan.');
    }

    private function notifySubscribers(ForumTopic $topic, ForumReply $reply): void
    {
        $subs = ForumSubscription::where('forum_topic_id', $topic->id)
            ->where('user_id', '!=', auth()->id())
            ->with('user')
            ->get();

        $replier = auth()->user()->name;
        $preview = \Illuminate\Support\Str::limit(strip_tags($reply->content), 100);

        foreach ($subs as $sub) {
            if ($sub->user && $sub->user->phone) {
                $msg = "*Balasan Baru: {$topic->title}*\n\n"
                    . "{$replier} membalas:\n"
                    . "_{$preview}_\n\n"
                    . "Lihat: " . route('forum.topic', $topic);

                $this->whatsapp->send($sub->user->phone, $msg, $this->schoolId());
            }
        }
    }
}
