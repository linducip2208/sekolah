<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\DigitalSignature;
use App\Models\SignedDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DigitalSignatureController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(): View
    {
        $signatures = DigitalSignature::where('school_id', $this->schoolId())
            ->with('user:id,name,email')
            ->get();

        return view('school-admin.documents.signatures', ['signatures' => $signatures]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'signature_image' => 'required|image|max:2048',
            'pin_code'        => 'required|string|min:4|max:20|confirmed',
        ]);

        $existing = DigitalSignature::where('school_id', $this->schoolId())
            ->where('user_id', auth()->id())
            ->first();

        if ($existing) {
            return back()->withErrors('Anda sudah memiliki tanda tangan digital. Hapus yang lama terlebih dahulu.');
        }

        $path = $request->file('signature_image')->store('signatures/' . $this->schoolId(), 'public');

        DigitalSignature::create([
            'school_id'            => $this->schoolId(),
            'user_id'              => auth()->id(),
            'signature_image_path' => $path,
            'pin_code'             => Hash::make($data['pin_code']),
        ]);

        return back()->with('success', 'Tanda tangan digital berhasil disimpan.');
    }

    public function destroy(DigitalSignature $signature): RedirectResponse
    {
        abort_unless($signature->school_id === $this->schoolId(), 403);

        Storage::disk('public')->delete($signature->signature_image_path);
        if ($signature->certificate_path) {
            Storage::disk('public')->delete($signature->certificate_path);
        }

        $signature->delete();

        return back()->with('success', 'Tanda tangan digital dihapus.');
    }

    public function sign(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'document_type'  => 'required|string',
            'document_id'    => 'required|integer',
            'pin_code'       => 'required|string',
        ]);

        $sig = DigitalSignature::where('school_id', $this->schoolId())
            ->where('user_id', auth()->id())
            ->where('is_active', true)
            ->first();

        if (!$sig || !$sig->verifyPin($data['pin_code'])) {
            return back()->withErrors('PIN tanda tangan salah atau tanda tangan tidak aktif.');
        }

        $hashValue = hash('sha256', $data['document_type'] . $data['document_id'] . $this->schoolId() . now()->timestamp);

        SignedDocument::create([
            'school_id'           => $this->schoolId(),
            'digital_signature_id'=> $sig->id,
            'document_type'       => $data['document_type'],
            'document_id'         => $data['document_id'],
            'signed_at'           => now(),
            'ip_address'          => $request->ip(),
            'hash_value'          => $hashValue,
        ]);

        return back()->with('success', 'Dokumen berhasil ditandatangani secara digital.');
    }

    public function verify(string $hash): View
    {
        $signed = SignedDocument::where('school_id', $this->schoolId())
            ->where('hash_value', $hash)
            ->with('signature.user:id,name,email')
            ->firstOrFail();

        $expectedHash = hash('sha256', $signed->document_type . $signed->document_id . $signed->school_id . $signed->signed_at->timestamp);
        $isValid = hash_equals($expectedHash, $signed->hash_value);

        return view('school-admin.documents.verify-signature', [
            'signed'  => $signed,
            'isValid' => $isValid,
        ]);
    }
}
