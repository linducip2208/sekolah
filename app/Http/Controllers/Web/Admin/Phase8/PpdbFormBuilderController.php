<?php

namespace App\Http\Controllers\Web\Admin\Phase8;

use App\Http\Controllers\Controller;
use App\Models\PPDB\PpdbFormField;
use App\Models\PPDB\PpdbPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PpdbFormBuilderController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $periodId = $request->input('period_id');
        $periods = PpdbPeriod::where('school_id', $this->schoolId())->orderByDesc('open_date')->get();

        $fields = PpdbFormField::where('school_id', $this->schoolId())
            ->when($periodId, fn($q) => $q->where('period_id', $periodId))
            ->orderBy('sort_order')
            ->get();

        return view('school-admin.ppdb.form-builder', [
            'fields'  => $fields,
            'periods' => $periods,
            'periodId'=> $periodId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'period_id'     => 'required|exists:ppdb_periods,id',
            'field_name'    => 'required|string|max:100',
            'field_type'    => 'required|in:text,textarea,number,date,file,select,checkbox,radio',
            'field_label'   => 'required|string|max:200',
            'options'       => 'nullable|array',
            'is_required'   => 'nullable|boolean',
            'sort_order'    => 'nullable|integer|min:0',
        ]);

        $maxOrder = PpdbFormField::where('school_id', $this->schoolId())
            ->where('period_id', $data['period_id'])
            ->max('sort_order') ?? 0;

        PpdbFormField::create([
            'school_id'      => $this->schoolId(),
            'period_id'      => $data['period_id'],
            'field_name'     => $data['field_name'],
            'field_type'     => $data['field_type'],
            'field_label'    => $data['field_label'],
            'options'        => $data['options'] ?? null,
            'is_required'    => $data['is_required'] ?? true,
            'validation_rules'=> null,
            'sort_order'     => $data['sort_order'] ?? ($maxOrder + 1),
            'is_active'      => true,
        ]);

        return back()->with('success', 'Field formulir ditambahkan.');
    }

    public function update(Request $request, PpdbFormField $field): RedirectResponse
    {
        abort_unless($field->school_id === $this->schoolId(), 403);

        $data = $request->validate([
            'field_label'   => 'sometimes|string|max:200',
            'field_type'    => 'sometimes|in:text,textarea,number,date,file,select,checkbox,radio',
            'options'       => 'nullable|array',
            'is_required'   => 'sometimes|boolean',
            'is_active'     => 'sometimes|boolean',
            'sort_order'    => 'sometimes|integer|min:0',
        ]);

        $field->update($data);
        return back()->with('success', 'Field diperbarui.');
    }

    public function destroy(PpdbFormField $field): RedirectResponse
    {
        abort_unless($field->school_id === $this->schoolId(), 403);
        $field->delete();
        return back()->with('success', 'Field dihapus.');
    }

    public function reorder(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'order'   => 'required|array',
            'order.*' => 'integer|exists:ppdb_form_fields,id',
        ]);

        foreach ($request->input('order') as $index => $fieldId) {
            PpdbFormField::where('id', $fieldId)
                ->where('school_id', $this->schoolId())
                ->update(['sort_order' => $index + 1]);
        }

        return response()->json(['ok' => true]);
    }
}
