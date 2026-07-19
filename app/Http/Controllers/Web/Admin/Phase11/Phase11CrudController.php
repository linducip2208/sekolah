<?php

namespace App\Http\Controllers\Web\Admin\Phase11;

use App\Http\Controllers\Controller;
use App\Models\Analytics\StudentRiskScore;
use App\Models\Dapodik\DapodikConfig;
use App\Models\Inventory\Asset;
use App\Models\Inventory\AssetCategory;
use App\Models\Inventory\AssetLoan;
use App\Models\Visitor\VisitorBlacklist;
use App\Models\Visitor\VisitorLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;

class Phase11CrudController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    private function authorizeOwn($model): void
    {
        abort_unless($model->school_id === $this->schoolId(), 403);
    }

    /* ============== VISITOR ============== */

    public function visitorLogs(): View
    {
        return view('school-admin.visitor.logs', [
            'logs' => VisitorLog::where('school_id', $this->schoolId())
                ->orderByDesc('checked_in_at')->paginate(25),
        ]);
    }

    public function checkInVisitor(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'visitor_name' => 'required|string|max:200',
            'id_number'    => 'nullable|string|max:50',
            'phone'        => 'nullable|string|max:30',
            'purpose'      => 'required|string|max:200',
            'badge_no'     => 'nullable|string|max:30',
        ]);
        VisitorLog::create([
            'school_id'      => $this->schoolId(),
            'visitor_name'   => $data['visitor_name'],
            'id_number'      => $data['id_number'] ?? null,
            'phone'          => $data['phone'] ?? null,
            'purpose'        => $data['purpose'],
            'badge_no'       => $data['badge_no'] ?? null,
            'checked_in_at'  => now(),
            'logged_by'      => auth()->id(),
            'is_blacklisted' => false,
        ]);
        return back()->with('success', 'Tamu check-in.');
    }

    public function checkOutVisitor(VisitorLog $log): RedirectResponse
    {
        $this->authorizeOwn($log);
        $log->update(['checked_out_at' => now()]);
        return back()->with('success', 'Tamu check-out.');
    }

    public function visitorBlacklist(): View
    {
        return view('school-admin.visitor.blacklist', [
            'list' => VisitorBlacklist::where('school_id', $this->schoolId())->orderBy('full_name')->get(),
        ]);
    }

    public function storeVisitorBlacklist(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:200',
            'id_number' => 'required|string|max:50',
            'reason'    => 'required|string|max:500',
        ]);
        VisitorBlacklist::create([
            'school_id' => $this->schoolId(),
            'full_name' => $data['full_name'],
            'id_number' => $data['id_number'],
            'reason'    => $data['reason'],
            'added_by'  => auth()->id(),
        ]);
        return back()->with('success', 'Ditambahkan ke blacklist.');
    }

    public function deleteVisitorBlacklist(VisitorBlacklist $entry): RedirectResponse
    {
        $this->authorizeOwn($entry);
        $entry->delete();
        return back()->with('success', 'Dihapus dari blacklist.');
    }

    /* ============== INVENTORY / ASSETS ============== */

    public function assetCategories(): View
    {
        return view('school-admin.inventory.categories', [
            'categories' => AssetCategory::where('school_id', $this->schoolId())->orderBy('name')->get(),
        ]);
    }

    public function storeAssetCategory(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => 'required|string|max:100', 'icon' => 'nullable|string|max:50']);
        $data['school_id'] = $this->schoolId();
        AssetCategory::create($data);
        return back()->with('success', 'Kategori ditambahkan.');
    }

    public function deleteAssetCategory(AssetCategory $category): RedirectResponse
    {
        $this->authorizeOwn($category);
        $category->delete();
        return back()->with('success', 'Kategori dihapus.');
    }

    public function assets(): View
    {
        return view('school-admin.inventory.assets', [
            'assets'     => Asset::where('school_id', $this->schoolId())->with('category')->orderBy('asset_code')->paginate(25),
            'categories' => AssetCategory::where('school_id', $this->schoolId())->orderBy('name')->get(),
        ]);
    }

    public function storeAsset(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'asset_code'        => 'required|string|max:50',
            'name'              => 'required|string|max:200',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'serial_number'     => 'nullable|string|max:100',
            'location'          => 'nullable|string|max:200',
            'condition'         => 'required|in:excellent,good,fair,poor,damaged',
            'purchased_at'      => 'nullable|date',
            'purchase_price_rupiah' => 'nullable|numeric|min:0',
        ]);
        Asset::create([
            'school_id'         => $this->schoolId(),
            'asset_category_id' => $data['asset_category_id'],
            'asset_code'        => $data['asset_code'],
            'name'              => $data['name'],
            'serial_number'     => $data['serial_number'] ?? null,
            'location'          => $data['location'] ?? null,
            'condition'         => $data['condition'],
            'status'            => 'available',
            'purchased_at'      => $data['purchased_at'] ?? null,
            'purchase_price'    => isset($data['purchase_price_rupiah']) ? (int)($data['purchase_price_rupiah']*100) : null,
        ]);
        return back()->with('success', 'Aset ditambahkan.');
    }

    public function deleteAsset(Asset $asset): RedirectResponse
    {
        $this->authorizeOwn($asset);
        $asset->delete();
        return back()->with('success', 'Aset dihapus.');
    }

    public function assetLoans(): View
    {
        return view('school-admin.inventory.loans', [
            'loans'   => AssetLoan::where('school_id', $this->schoolId())
                ->with(['asset:id,name,asset_code', 'borrower:id,name'])
                ->orderByDesc('borrowed_at')->paginate(25),
            'assets'  => Asset::where('school_id', $this->schoolId())->where('status', 'available')->orderBy('name')->get(),
            'users'   => \App\Models\User::where('school_id', $this->schoolId())->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function storeAssetLoan(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'asset_id'    => 'required|exists:assets,id',
            'borrower_id' => 'required|exists:users,id',
            'borrowed_at' => 'required|date',
            'due_at'      => 'required|date|after_or_equal:borrowed_at',
            'note'        => 'nullable|string',
        ]);
        AssetLoan::create([
            'school_id'   => $this->schoolId(),
            'asset_id'    => $data['asset_id'],
            'borrower_id' => $data['borrower_id'],
            'approved_by' => auth()->id(),
            'borrowed_at' => $data['borrowed_at'],
            'due_at'      => $data['due_at'],
            'status'      => 'borrowed',
            'note'        => $data['note'] ?? null,
        ]);
        Asset::where('id', $data['asset_id'])->update(['status' => 'in_use']);
        return back()->with('success', 'Aset dipinjamkan.');
    }

    public function returnAssetLoan(AssetLoan $loan): RedirectResponse
    {
        $this->authorizeOwn($loan);
        $loan->update(['returned_at' => now(), 'status' => 'returned']);
        Asset::where('id', $loan->asset_id)->update(['status' => 'available']);
        return back()->with('success', 'Aset dikembalikan.');
    }

    /* ============== DAPODIK ============== */

    public function dapodikConfig(): View
    {
        $config = DapodikConfig::firstOrCreate(
            ['school_id' => $this->schoolId()],
            ['npsn' => '']
        );
        return view('school-admin.dapodik.config', ['config' => $config]);
    }

    public function updateDapodikConfig(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'npsn'         => 'required|string|max:15',
            'endpoint_url' => 'nullable|url|max:500',
            'username'     => 'nullable|string|max:200',
            'password'     => 'nullable|string|max:200',
        ]);
        $config = DapodikConfig::firstOrCreate(['school_id' => $this->schoolId()], ['npsn' => $data['npsn']]);
        $config->npsn = $data['npsn'];
        $config->endpoint_url = $data['endpoint_url'] ?? null;
        if (!empty($data['username'])) {
            $config->username_encrypted = Crypt::encryptString($data['username']);
        }
        if (!empty($data['password'])) {
            $config->password_encrypted = Crypt::encryptString($data['password']);
        }
        $config->save();

        return back()->with('success', 'Konfigurasi Dapodik tersimpan.');
    }

    /* ============== ANALYTICS ============== */

    public function riskScores(): View
    {
        return view('school-admin.analytics.risks', [
            'scores' => StudentRiskScore::where('school_id', $this->schoolId())
                ->with('student.user:id,name')
                ->orderByDesc('snapshot_date')->orderByDesc('overall_risk')->paginate(30),
        ]);
    }
}
