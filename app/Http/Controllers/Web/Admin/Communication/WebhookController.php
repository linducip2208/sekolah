<?php

namespace App\Http\Controllers\Web\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Jobs\DeliverWebhookJob;
use App\Models\Communication\Webhook;
use App\Models\Communication\WebhookDelivery;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    private const AVAILABLE_EVENTS = [
        'student.created', 'student.updated', 'student.deleted',
        'invoice.created', 'invoice.paid', 'invoice.cancelled',
        'attendance.recorded',
        'exam.published', 'marks.published',
        'payment.received',
        'admission.submitted', 'admission.approved',
        'announcement.published',
    ];

    public function index()
    {
        $schoolId = auth()->user()->school_id;
        $webhooks = Webhook::where('school_id', $schoolId)->orderByDesc('id')->get();
        return view('school-admin.webhooks.index', [
            'webhooks' => $webhooks,
            'events'   => self::AVAILABLE_EVENTS,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:200',
            'url'         => 'required|url|max:500',
            'secret'      => 'nullable|string|max:200',
            'events'      => 'required|array|min:1',
            'events.*'    => 'string|in:' . implode(',', self::AVAILABLE_EVENTS),
            'max_retries' => 'nullable|integer|min:0|max:10',
            'extra_headers' => 'nullable|string',
        ]);

        $headers = $data['extra_headers'] ?? null;
        if ($headers) {
            $decoded = json_decode($headers, true);
            $headers = is_array($decoded) ? $decoded : null;
        }

        $w = new Webhook();
        $w->school_id = auth()->user()->school_id;
        $w->name = $data['name'];
        $w->url  = $data['url'];
        $w->secret = $data['secret'] ?? null;
        $w->events = $data['events'];
        $w->extra_headers = $headers;
        $w->max_retries = $data['max_retries'] ?? 3;
        $w->is_active = true;
        $w->save();

        return back()->with('success', 'Webhook tersimpan.');
    }

    public function toggle(Webhook $webhook)
    {
        $this->authorizeAccess($webhook);
        $webhook->is_active = !$webhook->is_active;
        $webhook->save();
        return back()->with('success', 'Status webhook diperbarui.');
    }

    public function destroy(Webhook $webhook)
    {
        $this->authorizeAccess($webhook);
        $webhook->delete();
        return back()->with('success', 'Webhook dihapus.');
    }

    public function deliveries(Webhook $webhook)
    {
        $this->authorizeAccess($webhook);
        $deliveries = WebhookDelivery::where('webhook_id', $webhook->id)
            ->orderByDesc('id')
            ->paginate(50);
        return view('school-admin.webhooks.deliveries', compact('webhook', 'deliveries'));
    }

    public function retry(WebhookDelivery $delivery)
    {
        abort_unless($delivery->school_id === auth()->user()->school_id, 403);
        $delivery->status = 'pending';
        $delivery->next_retry_at = null;
        $delivery->save();
        DeliverWebhookJob::dispatch($delivery->id);
        return back()->with('success', 'Delivery di-retry.');
    }

    private function authorizeAccess(Webhook $webhook): void
    {
        abort_unless($webhook->school_id === auth()->user()->school_id, 403);
    }
}
