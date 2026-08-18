<?php

namespace App\Http\Controllers\Api\PPDB;

use App\Http\Controllers\Controller;
use App\Models\PPDB\PpdbApplication;
use App\Models\PPDB\PpdbPeriod;
use App\Models\School;
use App\Services\PPDB\PpdbService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PpdbController extends Controller
{
    public function __construct(private PpdbService $service) {}

    public function publicPeriods(string $subdomain): JsonResponse
    {
        $school = School::where('subdomain', $subdomain)->firstOrFail();
        $periods = PpdbPeriod::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('is_published', true)
            ->where('close_date', '>=', now()->toDateString())
            ->orderBy('open_date')
            ->get();

        return response()->json([
            'school' => ['id' => $school->id, 'name' => $school->name, 'subdomain' => $school->subdomain],
            'data'   => $periods,
        ]);
    }

    public function publicRegister(Request $request, string $subdomain): JsonResponse
    {
        $school = School::where('subdomain', $subdomain)->firstOrFail();

        $data = $request->validate([
            'ppdb_period_id'  => 'required|integer',
            'jalur'           => 'required|in:zonasi,prestasi,afirmasi,undian,reguler',
            'student_name'    => 'required|string|max:200',
            'nisn'            => 'nullable|string|max:20',
            'date_of_birth'   => 'required|date',
            'gender'          => 'required|in:male,female',
            'address'         => 'required|string',
            'district'        => 'required|string|max:100',
            'city'            => 'required|string|max:100',
            'home_lat'        => 'nullable|numeric',
            'home_lng'        => 'nullable|numeric',
            'previous_school' => 'nullable|string|max:200',
            'parent_name'     => 'required|string|max:200',
            'parent_phone'    => 'required|string|max:30',
            'parent_email'    => 'required|email|max:200',
            'documents'       => 'nullable|array',
            'achievements'    => 'nullable|array',
            'average_score'   => 'nullable|numeric|min:0|max:100',
        ]);

        $period = PpdbPeriod::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('is_published', true)
            ->findOrFail($data['ppdb_period_id']);

        $app = $this->service->register($period, $data);

        return response()->json($app, 201);
    }

    public function myApplications(Request $request): JsonResponse
    {
        $apps = PpdbApplication::where('school_id', $request->user()->school_id)
            ->where('parent_email', $request->user()->email)
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => $apps]);
    }

    public function submit(Request $request, int $id): JsonResponse
    {
        $app = PpdbApplication::where('school_id', $request->user()->school_id)
            ->where('parent_email', $request->user()->email)
            ->findOrFail($id);

        return response()->json($this->service->submit($app));
    }

    // Admin
    public function adminIndex(Request $request): JsonResponse
    {
        $apps = PpdbApplication::where('school_id', $request->user()->school_id)
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->input('period_id'), fn ($q, $p) => $q->where('ppdb_period_id', $p))
            ->orderByDesc('created_at')
            ->paginate(50);

        return response()->json($apps);
    }

    public function verify(Request $request, int $id): JsonResponse
    {
        $app = PpdbApplication::where('school_id', $request->user()->school_id)->findOrFail($id);
        return response()->json($this->service->verify($app, $request->user()->id));
    }

    public function accept(Request $request, int $id): JsonResponse
    {
        $app = PpdbApplication::where('school_id', $request->user()->school_id)->findOrFail($id);
        return response()->json($this->service->accept($app, $request->user()->id, $request->input('note')));
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $request->validate(['note' => 'required|string|max:1000']);
        $app = PpdbApplication::where('school_id', $request->user()->school_id)->findOrFail($id);
        return response()->json($this->service->reject($app, $request->user()->id, $request->input('note')));
    }

    public function uploadDoc(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'file'     => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png',
            'doc_type' => 'required|string|max:50',
        ]);

        $app = PpdbApplication::where('school_id', $request->user()->school_id)->findOrFail($id);
        $app = $this->service->uploadDocument($app, $request->input('doc_type'), $request->file('file'));

        return response()->json($app);
    }

    public function batchEnroll(Request $request): JsonResponse
    {
        $data = $request->validate([
            'application_ids'   => 'required|array|min:1',
            'application_ids.*' => 'integer|exists:ppdb_applications,id',
            'class_section_id'  => 'required|exists:class_sections,id',
        ]);

        $result = $this->service->batchEnroll(
            $data['application_ids'],
            $data['class_section_id'],
            $request->user()->id,
        );

        return response()->json($result);
    }

    public function reports(Request $request): JsonResponse
    {
        $reports = $this->service->getReports(
            $request->user()->school_id,
            $request->input('period_id'),
        );

        return response()->json($reports);
    }

    public function runSelection(Request $request, int $periodId): JsonResponse
    {
        $period = PpdbPeriod::where('school_id', $request->user()->school_id)->findOrFail($periodId);
        return response()->json($this->service->runSelection($period));
    }
}
