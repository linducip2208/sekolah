<?php

namespace App\Http\Controllers\Web\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Models\Academic\Student;
use App\Models\Finance\CooperativeMember;
use App\Models\Finance\CooperativeSaving;
use App\Models\Finance\CooperativeLoan;
use App\Models\Finance\CooperativeInstallment;
use App\Models\User;
use App\Services\CooperativeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CooperativeController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function dashboard(): View
    {
        $schoolId = $this->schoolId();
        $service = app(CooperativeService::class);

        $totalSavings = CooperativeMember::where('school_id', $schoolId)->sum('total_savings');
        $totalLoans = CooperativeMember::where('school_id', $schoolId)->sum('total_loans');
        $outstanding = $service->totalOutstanding($schoolId);
        $shu = $service->shuProjection($schoolId, (int) now()->format('Y'));

        return view('school-admin.finance.cooperative.dashboard', [
            'totalMembers' => CooperativeMember::where('school_id', $schoolId)->count(),
            'activeMembers' => CooperativeMember::where('school_id', $schoolId)->where('status', 'active')->count(),
            'totalSavings' => $totalSavings,
            'totalLoans' => $totalLoans,
            'outstanding' => $outstanding,
            'shu' => $shu,
            'activeLoans' => CooperativeLoan::where('school_id', $schoolId)->where('status', 'active')->count(),
            'pendingLoans' => CooperativeLoan::where('school_id', $schoolId)->where('status', 'pending')->count(),
            'recentSavings' => CooperativeSaving::where('school_id', $schoolId)->with('member')->latest()->take(5)->get(),
            'recentLoans' => CooperativeLoan::where('school_id', $schoolId)->with('member')->latest()->take(5)->get(),
        ]);
    }

    public function members(Request $request): View
    {
        $schoolId = $this->schoolId();
        $query = CooperativeMember::where('school_id', $schoolId)->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where('member_number', 'like', '%' . $request->search . '%');
        }

        return view('school-admin.finance.cooperative.members', [
            'members' => $query->paginate(20)->appends($request->query()),
            'staff' => User::where('school_id', $schoolId)->orderBy('name')->get(),
            'students' => Student::where('school_id', $schoolId)->with('user')->get(),
        ]);
    }

    public function storeMember(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'memberable_type' => 'required|in:staff,student',
            'memberable_id' => 'required|integer',
            'join_date' => 'required|date',
        ]);

        $schoolId = $this->schoolId();
        $lastNumber = CooperativeMember::where('school_id', $schoolId)->count();
        $memberNumber = 'KSP-' . now()->format('Y') . '-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

        $typeMap = ['staff' => User::class, 'student' => Student::class];

        CooperativeMember::create([
            'school_id' => $schoolId,
            'memberable_type' => $typeMap[$data['memberable_type']],
            'memberable_id' => $data['memberable_id'],
            'member_number' => $memberNumber,
            'join_date' => $data['join_date'],
            'total_savings' => 0,
            'total_loans' => 0,
            'status' => 'active',
        ]);

        return redirect()->route('admin.cooperative.members')->with('success', 'Anggota koperasi berhasil didaftarkan.');
    }

    public function updateMember(Request $request, CooperativeMember $member): RedirectResponse
    {
        abort_unless($member->school_id === $this->schoolId(), 403);

        $data = $request->validate([
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $member->update($data);
        return back()->with('success', 'Status anggota diperbarui.');
    }

    public function deleteMember(CooperativeMember $member): RedirectResponse
    {
        abort_unless($member->school_id === $this->schoolId(), 403);
        $member->delete();
        return back()->with('success', 'Anggota dihapus.');
    }

    public function savings(Request $request): View
    {
        $schoolId = $this->schoolId();
        $query = CooperativeSaving::where('school_id', $schoolId)
            ->with('member')
            ->orderByDesc('transaction_date');

        if ($request->filled('type')) {
            $query->where('savings_type', $request->type);
        }
        if ($request->filled('member')) {
            $query->where('cooperative_member_id', $request->member);
        }

        return view('school-admin.finance.cooperative.savings', [
            'savings' => $query->paginate(20)->appends($request->query()),
            'members' => CooperativeMember::where('school_id', $schoolId)->orderBy('member_number')->get(),
            'depositTotal' => CooperativeSaving::where('school_id', $schoolId)->where('transaction_type', 'deposit')->sum('amount'),
            'withdrawalTotal' => CooperativeSaving::where('school_id', $schoolId)->where('transaction_type', 'withdrawal')->sum('amount'),
        ]);
    }

    public function storeSaving(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'cooperative_member_id' => 'required|exists:cooperative_members,id',
            'transaction_date' => 'required|date',
            'amount' => 'required|integer|min:1',
            'savings_type' => 'required|in:pokok,wajib,sukarela',
            'transaction_type' => 'required|in:deposit,withdrawal',
            'notes' => 'nullable|string',
        ]);

        $data['school_id'] = $this->schoolId();
        $data['recorded_by'] = auth()->id();
        $data['reference_no'] = 'SVG-' . now()->format('Ymd') . '-' . rand(1000, 9999);

        $saving = CooperativeSaving::create($data);

        $member = CooperativeMember::find($data['cooperative_member_id']);
        if ($data['transaction_type'] === 'deposit') {
            $member->increment('total_savings', $data['amount']);
        } else {
            $member->decrement('total_savings', $data['amount']);
        }

        return redirect()->route('admin.cooperative.savings')->with('success', 'Simpanan berhasil dicatat.');
    }

    public function deleteSaving(CooperativeSaving $saving): RedirectResponse
    {
        abort_unless($saving->school_id === $this->schoolId(), 403);

        $member = $saving->member;
        if ($saving->transaction_type === 'deposit') {
            $member->decrement('total_savings', $saving->amount);
        } else {
            $member->increment('total_savings', $saving->amount);
        }

        $saving->delete();
        return back()->with('success', 'Simpanan dihapus.');
    }

    public function loans(Request $request): View
    {
        $schoolId = $this->schoolId();
        $query = CooperativeLoan::where('school_id', $schoolId)
            ->with(['member', 'approvedBy'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return view('school-admin.finance.cooperative.loans', [
            'loans' => $query->paginate(20)->appends($request->query()),
            'members' => CooperativeMember::where('school_id', $schoolId)->where('status', 'active')->orderBy('member_number')->get(),
        ]);
    }

    public function storeLoan(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'cooperative_member_id' => 'required|exists:cooperative_members,id',
            'loan_amount' => 'required|integer|min:1000',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'term_months' => 'required|integer|min:1|max:60',
            'start_date' => 'required|date',
            'purpose' => 'nullable|string',
        ]);

        $data['school_id'] = $this->schoolId();
        $data['interest_rate'] = $data['interest_rate'] ?? 0;

        $loan = CooperativeLoan::create($data);

        $service = app(CooperativeService::class);
        $service->generateInstallmentSchedule($loan);

        return redirect()->route('admin.cooperative.loans')->with('success', 'Pinjaman berhasil dibuat. Angsuran telah digenerate.');
    }

    public function approveLoan(CooperativeLoan $loan): RedirectResponse
    {
        abort_unless($loan->school_id === $this->schoolId(), 403);

        $loan->update([
            'status' => 'active',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $loan->member->increment('total_loans', $loan->loan_amount);

        return back()->with('success', 'Pinjaman disetujui.');
    }

    public function rejectLoan(CooperativeLoan $loan): RedirectResponse
    {
        abort_unless($loan->school_id === $this->schoolId(), 403);
        $loan->update(['status' => 'rejected']);
        return back()->with('success', 'Pinjaman ditolak.');
    }

    public function payInstallment(Request $request, CooperativeInstallment $installment): RedirectResponse
    {
        $loan = $installment->loan;
        abort_unless($loan->school_id === $this->schoolId(), 403);

        $data = $request->validate([
            'paid_amount' => 'required|integer|min:1',
        ]);

        $paidAmount = $data['paid_amount'];
        $newStatus = $paidAmount >= $installment->amount ? 'paid' : 'late';

        $installment->update([
            'paid_amount' => $installment->paid_amount + $paidAmount,
            'paid_date' => now(),
            'status' => $newStatus,
        ]);

        $allPaid = CooperativeInstallment::where('cooperative_loan_id', $loan->id)
            ->where('status', '!=', 'paid')
            ->doesntExist();

        if ($allPaid) {
            $loan->update(['status' => 'paid_off']);
        }

        return back()->with('success', 'Angsuran berhasil dibayar.');
    }

    public function deleteLoan(CooperativeLoan $loan): RedirectResponse
    {
        abort_unless($loan->school_id === $this->schoolId(), 403);
        $loan->delete();
        return back()->with('success', 'Pinjaman dihapus.');
    }

    public function shuReport(Request $request): View
    {
        $schoolId = $this->schoolId();
        $year = $request->get('tahun', (int) now()->format('Y'));
        $service = app(CooperativeService::class);

        return view('school-admin.finance.cooperative.shu-report', [
            'shu' => $service->shuProjection($schoolId, $year),
            'tahun' => $year,
            'members' => CooperativeMember::where('school_id', $schoolId)->where('status', 'active')->get(),
        ]);
    }

    public function memberStatement(CooperativeMember $member): View
    {
        abort_unless($member->school_id === $this->schoolId(), 403);
        $service = app(CooperativeService::class);

        return view('school-admin.finance.cooperative.savings', [
            'targetMember' => $member,
            'statement' => $service->memberSavingsStatement($member),
            'savings' => CooperativeSaving::where('school_id', $this->schoolId())->paginate(20),
            'members' => CooperativeMember::where('school_id', $this->schoolId())->get(),
        ]);
    }
}
