<?php

namespace App\Http\Controllers\Web\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Models\Emergency\EmergencyAlert;
use App\Models\Emergency\EmergencyContact;
use App\Models\Emergency\EmergencyRecipient;
use App\Models\Emergency\EmergencyTemplate;
use App\Models\Academic\ClassSection;
use App\Services\EmergencyAlertService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmergencyController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function __construct(
        private EmergencyAlertService $alertService
    ) {}

    public function index(): View
    {
        $alerts = EmergencyAlert::with('triggeredBy')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $totalSent = EmergencyAlert::where('status', 'sent')->count();
        $totalDraft = EmergencyAlert::where('status', 'draft')->count();
        $lastSent = EmergencyAlert::where('status', 'sent')->latest('sent_at')->first();

        return view('school-admin.emergency.index', compact('alerts', 'totalSent', 'totalDraft', 'lastSent'));
    }

    public function create(): View
    {
        $templates = EmergencyTemplate::orderBy('name')->get();
        $classes = ClassSection::with('classRoom', 'section')->get();
        $contacts = EmergencyContact::where('is_active', true)->orderBy('priority_order')->get();

        return view('school-admin.emergency.create', compact('templates', 'classes', 'contacts'));
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'alert_type'     => 'required|string|in:fire,earthquake,flood,security,medical,other',
            'title'          => 'required|string|max:255',
            'message'        => 'required|string',
            'severity'       => 'required|string|in:info,warning,critical',
            'recipient_type' => 'required|string|in:all_parents,all_staff,class,individual',
            'recipient_ids'  => 'nullable|array',
            'recipient_ids.*' => 'nullable|integer',
            'confirm'        => 'required|accepted',
        ]);

        $alert = EmergencyAlert::create([
            'school_id'    => $this->schoolId(),
            'alert_type'   => $data['alert_type'],
            'title'        => $data['title'],
            'message'      => $data['message'],
            'triggered_by' => auth()->id(),
            'severity'     => $data['severity'],
            'status'       => 'draft',
        ]);

        if ($data['recipient_type'] === 'class' && !empty($data['recipient_ids'])) {
            foreach ($data['recipient_ids'] as $classId) {
                EmergencyRecipient::create([
                    'emergency_alert_id' => $alert->id,
                    'recipient_type'     => 'class',
                    'recipient_id'       => $classId,
                ]);
            }
        } elseif ($data['recipient_type'] === 'individual' && !empty($data['recipient_ids'])) {
            foreach ($data['recipient_ids'] as $userId) {
                EmergencyRecipient::create([
                    'emergency_alert_id' => $alert->id,
                    'recipient_type'     => 'individual',
                    'recipient_id'       => $userId,
                ]);
            }
        } else {
            EmergencyRecipient::create([
                'emergency_alert_id' => $alert->id,
                'recipient_type'     => $data['recipient_type'],
                'recipient_id'       => null,
            ]);
        }

        dispatch(function () use ($alert) {
            $this->alertService->sendBroadcast($alert);
        });

        return redirect()->route('admin.emergency.index')
            ->with('success', 'Peringatan darurat dikirim. ' . $alert->recipient_count . ' penerima terjangkau.');
    }

    public function history(): View
    {
        $alerts = EmergencyAlert::with([
            'triggeredBy',
            'recipients',
        ])->orderByDesc('created_at')->paginate(20);

        return view('school-admin.emergency.index', compact('alerts'));
    }

    public function show(EmergencyAlert $alert): View
    {
        $alert->load(['triggeredBy', 'recipients']);

        return view('school-admin.emergency.show', compact('alert'));
    }

    public function cancel(EmergencyAlert $alert): \Illuminate\Http\RedirectResponse
    {
        if ($alert->status !== 'draft') {
            return back()->withErrors(['status' => 'Hanya alert draft yang dapat dibatalkan.']);
        }

        $alert->update(['status' => 'cancelled']);

        return back()->with('success', 'Alert dibatalkan.');
    }

    public function getTemplatesByType(string $type): \Illuminate\Http\JsonResponse
    {
        $templates = EmergencyTemplate::where('alert_type', $type)
            ->orderBy('name')
            ->get();

        return response()->json($templates);
    }

    // ───── Emergency Contacts CRUD ─────

    public function contacts(): View
    {
        $contacts = EmergencyContact::orderBy('priority_order')->get();

        return view('school-admin.emergency.contacts', compact('contacts'));
    }

    public function storeContact(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'phone'          => 'nullable|string|max:30',
            'email'          => 'nullable|email|max:255',
            'contact_type'   => 'required|string|in:police,fire,hospital,security,other',
            'priority_order' => 'nullable|integer|min:0',
        ]);

        $data['school_id'] = $this->schoolId();
        $data['is_active'] = true;

        EmergencyContact::create($data);

        return back()->with('success', 'Kontak darurat berhasil ditambahkan.');
    }

    public function updateContact(Request $request, EmergencyContact $contact): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'phone'          => 'nullable|string|max:30',
            'email'          => 'nullable|email|max:255',
            'contact_type'   => 'required|string|in:police,fire,hospital,security,other',
            'priority_order' => 'nullable|integer|min:0',
            'is_active'      => 'boolean',
        ]);

        $contact->update($data);

        return back()->with('success', 'Kontak darurat diperbarui.');
    }

    public function deleteContact(EmergencyContact $contact): \Illuminate\Http\RedirectResponse
    {
        $contact->delete();

        return back()->with('success', 'Kontak darurat dihapus.');
    }

    // ───── Quick Alert (Panic Modal) ─────

    public function quickAlert(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'alert_type' => 'required|string|in:fire,earthquake,flood,security,medical,other',
            'message'    => 'required|string|max:500',
        ]);

        $alert = EmergencyAlert::create([
            'school_id'    => $this->schoolId(),
            'alert_type'   => $data['alert_type'],
            'title'        => 'DANGER — ' . strtoupper(__('emergency.types.' . $data['alert_type'])),
            'message'      => $data['message'],
            'triggered_by' => auth()->id(),
            'severity'     => 'critical',
            'status'       => 'draft',
        ]);

        EmergencyRecipient::create([
            'emergency_alert_id' => $alert->id,
            'recipient_type'     => 'all_staff',
        ]);

        EmergencyRecipient::create([
            'emergency_alert_id' => $alert->id,
            'recipient_type'     => 'all_parents',
        ]);

        dispatch(function () use ($alert) {
            $this->alertService->sendBroadcast($alert);
        });

        return back()->with('success', 'PERINGATAN DARURAT DIKIRIM KE SEMUA!');
    }
}
