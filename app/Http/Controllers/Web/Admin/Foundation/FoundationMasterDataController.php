<?php

namespace App\Http\Controllers\Web\Admin\Foundation;

use App\Http\Controllers\Controller;
use App\Models\Foundation\Foundation;
use App\Models\Foundation\FoundationMasterData;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FoundationMasterDataController extends Controller
{
    private function getFoundation(): Foundation
    {
        $schoolId = auth()->user()->school_id;
        $school = School::findOrFail($schoolId);
        abort_unless($school->foundation_id, 404, 'Sekolah ini tidak terafiliasi dengan yayasan.');
        return Foundation::findOrFail($school->foundation_id);
    }

    public function index(Request $request): View
    {
        $foundation = $this->getFoundation();
        $dataType = $request->input('data_type', 'subject');

        $items = FoundationMasterData::where('foundation_id', $foundation->id)
            ->where('data_type', $dataType)
            ->orderByDesc('created_at')
            ->get();

        $schools = $foundation->schools()->get(['id', 'name']);

        return view('school-admin.foundation.master-data', [
            'foundation' => $foundation,
            'items'      => $items,
            'dataType'   => $dataType,
            'schools'    => $schools,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $foundation = $this->getFoundation();

        $data = $request->validate([
            'data_type' => 'required|in:subject,class_template,fee_template,grading_scale',
            'data_json' => 'required|array',
        ]);

        FoundationMasterData::create([
            'foundation_id' => $foundation->id,
            'data_type'     => $data['data_type'],
            'data_json'     => $data['data_json'],
            'is_synced'     => false,
        ]);

        return back()->with('success', 'Master data ditambahkan.');
    }

    public function sync(FoundationMasterData $item): RedirectResponse
    {
        $foundation = $this->getFoundation();
        abort_unless($item->foundation_id === $foundation->id, 403);

        $item->update(['is_synced' => true]);
        return back()->with('success', 'Data ditandai sudah di-sync ke cabang.');
    }

    public function destroy(FoundationMasterData $item): RedirectResponse
    {
        $foundation = $this->getFoundation();
        abort_unless($item->foundation_id === $foundation->id, 403);

        $item->delete();
        return back()->with('success', 'Master data dihapus.');
    }
}
