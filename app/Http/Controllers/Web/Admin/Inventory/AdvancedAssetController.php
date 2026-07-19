<?php

namespace App\Http\Controllers\Web\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Asset;
use App\Models\Inventory\AssetCategory;
use App\Models\Inventory\AssetMaintenanceSchedule;
use App\Models\Inventory\AssetWriteOff;
use App\Services\AssetDepreciationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdvancedAssetController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function enhancedIndex(Request $request): View
    {
        $schoolId = $this->schoolId();
        $query = Asset::where('school_id', $schoolId)->with('category')->orderByDesc('created_at');

        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }
        if ($request->filled('category')) {
            $query->where('asset_category_id', $request->category);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('asset_code', 'like', '%' . $request->search . '%');
            });
        }

        $service = app(AssetDepreciationService::class);

        return view('school-admin.inventory.assets.enhanced-index', [
            'assets' => $query->paginate(20)->appends($request->query()),
            'categories' => AssetCategory::where('school_id', $schoolId)->orderBy('name')->get(),
            'conditions' => ['excellent' => 'Sangat Baik', 'good' => 'Baik', 'fair' => 'Cukup', 'poor' => 'Buruk', 'damaged' => 'Rusak'],
            'depService' => $service,
        ]);
    }

    public function storeAsset(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'asset_category_id' => 'required|exists:asset_categories,id',
            'name' => 'required|string|max:255',
            'asset_code' => 'nullable|string|max:100|unique:assets,asset_code',
            'description' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|integer|min:0',
            'useful_life_years' => 'nullable|integer|min:1',
            'salvage_value' => 'nullable|integer|min:0',
            'depreciation_method' => 'nullable|in:straight_line,double_declining',
            'condition' => 'nullable|in:excellent,good,fair,poor,damaged',
            'supplier_name' => 'nullable|string|max:255',
            'warranty_expiry_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'location_detail' => 'nullable|string',
            'next_maintenance_date' => 'nullable|date',
        ]);

        $data['school_id'] = $this->schoolId();
        $data['qr_code'] = strtoupper(bin2hex(random_bytes(8)));
        $data['condition'] = $data['condition'] ?? 'good';
        $data['depreciation_method'] = $data['depreciation_method'] ?? 'straight_line';

        $asset = Asset::create($data);

        if ($asset->useful_life_years && $asset->purchase_price) {
            $service = app(AssetDepreciationService::class);
            $asset->update(['monthly_depreciation' => $service->calculateMonthlyDepreciation($asset)]);
        }

        return redirect()->route('admin.inventory.enhanced.index')->with('success', 'Aset berhasil ditambahkan.');
    }

    public function updateAsset(Request $request, Asset $asset): RedirectResponse
    {
        abort_unless($asset->school_id === $this->schoolId(), 403);

        $data = $request->validate([
            'asset_category_id' => 'required|exists:asset_categories,id',
            'name' => 'required|string|max:255',
            'asset_code' => 'nullable|string|max:100|unique:assets,asset_code,' . $asset->id,
            'description' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|integer|min:0',
            'useful_life_years' => 'nullable|integer|min:1',
            'salvage_value' => 'nullable|integer|min:0',
            'depreciation_method' => 'nullable|in:straight_line,double_declining',
            'condition' => 'nullable|in:excellent,good,fair,poor,damaged',
            'supplier_name' => 'nullable|string|max:255',
            'warranty_expiry_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'location_detail' => 'nullable|string',
            'next_maintenance_date' => 'nullable|date',
        ]);

        $asset->update($data);

        if ($asset->useful_life_years && $asset->purchase_price) {
            $service = app(AssetDepreciationService::class);
            $asset->update(['monthly_depreciation' => $service->calculateMonthlyDepreciation($asset)]);
        }

        return redirect()->route('admin.inventory.enhanced.index')->with('success', 'Aset diperbarui.');
    }

    public function showDepreciation(Asset $asset): View
    {
        abort_unless($asset->school_id === $this->schoolId(), 403);
        $service = app(AssetDepreciationService::class);

        return view('school-admin.inventory.assets.enhanced-index', [
            'target' => $asset,
            'depreciationSchedule' => $service->depreciationSchedule($asset),
            'currentBookValue' => $service->currentBookValue($asset),
            'depService' => $service,
            'assets' => Asset::where('school_id', $this->schoolId())->paginate(20),
            'categories' => AssetCategory::where('school_id', $this->schoolId())->get(),
            'conditions' => ['excellent' => 'Sangat Baik', 'good' => 'Baik', 'fair' => 'Cukup', 'poor' => 'Buruk', 'damaged' => 'Rusak'],
        ]);
    }

    public function qrPrint(Asset $asset)
    {
        abort_unless($asset->school_id === $this->schoolId(), 403);

        if (!$asset->qr_code) {
            $asset->update(['qr_code' => strtoupper(bin2hex(random_bytes(8)))]);
        }

        $qrData = 'data:image/svg+xml;base64,' . base64_encode($this->generateQrSvg($asset->qr_code));

        return view('school-admin.inventory.assets.qr-print', [
            'asset' => $asset,
            'qrData' => $qrData,
        ]);
    }

    private function generateQrSvg(string $code): string
    {
        $size = 200;
        $modules = 21;
        return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $modules . ' ' . $modules . '">
            <rect width="' . $modules . '" height="' . $modules . '" fill="white"/>
            <g fill="black" transform="scale(1 1)">' .
            $this->renderQrPattern($code) .
            '</g>
            <rect x="0" y="0" width="7" height="7" fill="black"/>
            <rect x="1" y="1" width="5" height="5" fill="white"/>
            <rect x="2" y="2" width="3" height="3" fill="black"/>
            <rect x="14" y="0" width="7" height="7" fill="black"/>
            <rect x="15" y="1" width="5" height="5" fill="white"/>
            <rect x="16" y="2" width="3" height="3" fill="black"/>
            <rect x="0" y="14" width="7" height="7" fill="black"/>
            <rect x="1" y="15" width="5" height="5" fill="white"/>
            <rect x="2" y="16" width="3" height="3" fill="black"/>
        </svg>';
    }

    private function renderQrPattern(string $code): string
    {
        $s = '';
        $len = strlen($code);
        for ($i = 0; $i < min(15, $len); $i++) {
            $val = ord($code[$i]);
            $x = $i % 7 + 8;
            $y = intval($i / 7);
            for ($b = 0; $b < 8; $b++) {
                if ($val & (1 << $b)) {
                    $px = $x * 3 + ($b % 3);
                    $py = $y * 3 + intval($b / 3) + 1;
                    $s .= '<rect x="' . $px . '" y="' . $py . '" width="1" height="1" fill="black"/>';
                }
            }
        }
        return $s;
    }

    public function maintenance(Request $request): View
    {
        $schoolId = $this->schoolId();
        $query = AssetMaintenanceSchedule::where('school_id', $schoolId)
            ->with('asset')
            ->orderBy('scheduled_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return view('school-admin.inventory.assets.maintenance', [
            'schedules' => $query->paginate(20)->appends($request->query()),
            'assets' => Asset::where('school_id', $schoolId)->orderBy('name')->get(),
            'types' => ['routine' => 'Rutin', 'repair' => 'Perbaikan', 'inspection' => 'Inspeksi', 'calibration' => 'Kalibrasi'],
            'statuses' => ['scheduled' => 'Terjadwal', 'in_progress' => 'Dalam Pengerjaan', 'completed' => 'Selesai', 'overdue' => 'Overdue'],
            'events' => AssetMaintenanceSchedule::where('school_id', $schoolId)
                ->with('asset')
                ->whereNotNull('scheduled_date')
                ->get()
                ->map(fn($s) => [
                    'title' => ($s->asset?->name ?? 'Aset') . ' - ' . ucfirst($s->maintenance_type),
                    'start' => $s->scheduled_date->format('Y-m-d'),
                    'color' => $s->status === 'completed' ? '#16a34a' : ($s->status === 'overdue' ? '#dc2626' : ($s->status === 'in_progress' ? '#2563eb' : '#eab308')),
                    'url' => route('admin.inventory.maintenance') . '?status=' . $s->status,
                ]),
        ]);
    }

    public function storeMaintenance(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'maintenance_type' => 'required|in:routine,repair,inspection,calibration',
            'scheduled_date' => 'required|date',
            'performed_by' => 'nullable|string|max:200',
            'cost' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $data['school_id'] = $this->schoolId();
        $data['cost'] = $data['cost'] ?? 0;
        AssetMaintenanceSchedule::create($data);

        return redirect()->route('admin.inventory.maintenance')->with('success', 'Jadwal maintenance ditambahkan.');
    }

    public function updateMaintenance(Request $request, AssetMaintenanceSchedule $schedule): RedirectResponse
    {
        abort_unless($schedule->school_id === $this->schoolId(), 403);

        $data = $request->validate([
            'status' => 'required|in:scheduled,in_progress,completed,overdue',
            'completed_date' => 'nullable|date',
            'cost' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($data['status'] === 'completed') {
            $data['completed_date'] = $data['completed_date'] ?? now()->toDateString();
        }

        $schedule->update($data);

        if ($data['status'] === 'completed') {
            $schedule->asset->update(['last_maintenance_date' => now()]);
        }

        return back()->with('success', 'Status maintenance diperbarui.');
    }

    public function deleteMaintenance(AssetMaintenanceSchedule $schedule): RedirectResponse
    {
        abort_unless($schedule->school_id === $this->schoolId(), 403);
        $schedule->delete();
        return back()->with('success', 'Jadwal maintenance dihapus.');
    }

    public function writeOffs(Request $request): View
    {
        $schoolId = $this->schoolId();
        $query = AssetWriteOff::where('school_id', $schoolId)
            ->with(['asset', 'approver'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return view('school-admin.inventory.assets.writeoffs', [
            'writeOffs' => $query->paginate(20)->appends($request->query()),
            'assets' => Asset::where('school_id', $schoolId)->orderBy('name')->get(),
        ]);
    }

    public function storeWriteOff(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'reason' => 'required|string',
            'condition_at_writeoff' => 'nullable|string',
            'estimated_value' => 'nullable|integer|min:0',
        ]);

        $data['school_id'] = $this->schoolId();
        $data['request_date'] = now();
        $data['estimated_value'] = $data['estimated_value'] ?? 0;

        AssetWriteOff::create($data);

        return redirect()->route('admin.inventory.writeoffs')->with('success', 'Pengajuan penghapusan aset dibuat.');
    }

    public function submitWriteOff(AssetWriteOff $writeOff): RedirectResponse
    {
        abort_unless($writeOff->school_id === $this->schoolId(), 403);
        $writeOff->update(['status' => 'submitted']);
        return back()->with('success', 'Pengajuan penghapusan diajukan.');
    }

    public function approveWriteOff(AssetWriteOff $writeOff): RedirectResponse
    {
        abort_unless($writeOff->school_id === $this->schoolId(), 403);
        $writeOff->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $writeOff->asset->update(['status' => 'written_off']);
        return back()->with('success', 'Penghapusan aset disetujui.');
    }

    public function rejectWriteOff(AssetWriteOff $writeOff): RedirectResponse
    {
        abort_unless($writeOff->school_id === $this->schoolId(), 403);
        $writeOff->update(['status' => 'rejected']);
        return back()->with('success', 'Penghapusan aset ditolak.');
    }
}
