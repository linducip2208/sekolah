<?php

namespace App\Http\Controllers\Api\Medical;

use App\Http\Controllers\Controller;
use App\Models\Medical\ClinicVisit;
use App\Models\Medical\MedicalRecord;
use App\Models\Medical\Vaccination;
use App\Services\Medical\ClinicService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClinicController extends Controller
{
    public function __construct(private ClinicService $service) {}

    public function record(Request $request, int $studentId): JsonResponse
    {
        $record = $this->service->getOrCreateRecord($request->user()->school_id, $studentId);
        return response()->json($record);
    }

    public function updateRecord(Request $request, int $studentId): JsonResponse
    {
        $data = $request->validate([
            'blood_type'                => 'nullable|string|max:5',
            'allergies'                 => 'nullable|array',
            'chronic_conditions'        => 'nullable|array',
            'current_medications'       => 'nullable|array',
            'emergency_contact_name'    => 'nullable|string|max:200',
            'emergency_contact_phone'   => 'nullable|string|max:30',
            'insurance_provider'        => 'nullable|string|max:200',
            'insurance_number'          => 'nullable|string|max:100',
        ]);

        $record = $this->service->getOrCreateRecord($request->user()->school_id, $studentId);
        return response()->json($this->service->updateRecord($record, $data));
    }

    public function visits(Request $request): JsonResponse
    {
        $visits = ClinicVisit::where('school_id', $request->user()->school_id)
            ->when($request->input('student_id'), fn ($q, $sid) => $q->where('student_id', $sid))
            ->orderByDesc('visit_at')
            ->paginate(50);

        return response()->json($visits);
    }

    public function storeVisit(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_id'         => 'required|integer',
            'visit_at'           => 'nullable|date',
            'symptoms'           => 'required|string',
            'diagnosis'          => 'nullable|string',
            'treatment'          => 'nullable|string',
            'medications_given'  => 'nullable|array',
            'temperature_c'      => 'nullable|numeric|between:30,45',
            'blood_pressure'     => 'nullable|string|max:10',
            'returned_to_class'  => 'nullable|boolean',
            'sent_home'          => 'nullable|boolean',
            'referred_external'  => 'nullable|boolean',
            'referred_to'        => 'nullable|string|max:200',
        ]);

        $visit = $this->service->recordVisit(
            $request->user()->school_id,
            $data['student_id'],
            $request->user()->id,
            $data,
        );

        return response()->json($visit, 201);
    }

    public function visitsByStudent(Request $request, int $studentId): JsonResponse
    {
        $visits = ClinicVisit::where('school_id', $request->user()->school_id)
            ->where('student_id', $studentId)
            ->orderByDesc('visit_at')
            ->get();

        return response()->json(['data' => $visits]);
    }

    public function vaccinations(Request $request, int $studentId): JsonResponse
    {
        $vaccinations = Vaccination::where('school_id', $request->user()->school_id)
            ->where('student_id', $studentId)
            ->orderByDesc('vaccinated_at')
            ->get();

        return response()->json(['data' => $vaccinations]);
    }

    public function storeVaccination(Request $request, int $studentId): JsonResponse
    {
        $data = $request->validate([
            'vaccine_name'     => 'required|string|max:200',
            'vaccinated_at'    => 'required|date',
            'batch_number'     => 'nullable|string|max:100',
            'administered_by'  => 'nullable|string|max:200',
            'next_dose_due'    => 'nullable|date',
            'certificate_path' => 'nullable|string|max:500',
        ]);

        $v = $this->service->recordVaccination($request->user()->school_id, $studentId, $data);
        return response()->json($v, 201);
    }
}
