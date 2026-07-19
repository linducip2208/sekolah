<?php

namespace App\Http\Controllers\Web\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Models\Academic\Student;
use App\Models\Academic\Staff;
use App\Models\Communication\Letter;
use App\Models\Communication\LetterTemplate;
use App\Services\LetterService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LetterController extends Controller
{
    private const RECIPIENT_TYPES = ['student', 'staff', 'other'];

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function templates(): View
    {
        $templates = LetterTemplate::where('school_id', $this->schoolId())
            ->orderBy('name')
            ->paginate(15);

        return view('school-admin.letters.templates', compact('templates'));
    }

    public function storeTemplate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'      => 'required|string|max:200',
            'code'      => 'required|string|max:50|unique:letter_templates,code,NULL,id,school_id,' . $this->schoolId(),
            'content'   => 'required|string|max:50000',
            'variables' => 'nullable|array',
            'category'  => 'required|in:sk,surat-keterangan,surat-izin,surat-panggilan,other',
            'is_active' => 'nullable|boolean',
        ]);

        LetterTemplate::create([
            'school_id' => $this->schoolId(),
            'name'      => $data['name'],
            'code'      => $data['code'],
            'content'   => $data['content'],
            'variables' => $data['variables'] ?? [],
            'category'  => $data['category'],
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        return redirect()->route('admin.letters.templates')->with('success', 'Template surat berhasil ditambahkan.');
    }

    public function updateTemplate(Request $request, LetterTemplate $template): RedirectResponse
    {
        abort_if($template->school_id !== $this->schoolId(), 403);

        $data = $request->validate([
            'name'      => 'required|string|max:200',
            'code'      => 'required|string|max:50|unique:letter_templates,code,' . $template->id . ',id,school_id,' . $this->schoolId(),
            'content'   => 'required|string|max:50000',
            'variables' => 'nullable|array',
            'category'  => 'required|in:sk,surat-keterangan,surat-izin,surat-panggilan,other',
            'is_active' => 'nullable|boolean',
        ]);

        $template->update([
            'name'      => $data['name'],
            'code'      => $data['code'],
            'content'   => $data['content'],
            'variables' => $data['variables'] ?? [],
            'category'  => $data['category'],
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        return redirect()->route('admin.letters.templates')->with('success', 'Template surat berhasil diperbarui.');
    }

    public function deleteTemplate(LetterTemplate $template): RedirectResponse
    {
        abort_if($template->school_id !== $this->schoolId(), 403);
        $template->delete();
        return redirect()->route('admin.letters.templates')->with('success', 'Template surat berhasil dihapus.');
    }

    public function index(): View
    {
        $letters = Letter::where('school_id', $this->schoolId())
            ->with(['template:id,name,code', 'issuer:id,name'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('school-admin.letters.index', compact('letters'));
    }

    public function create(): View
    {
        $templates = LetterTemplate::where('school_id', $this->schoolId())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $students = Student::where('school_id', $this->schoolId())
            ->with('user:id,name', 'classSection.classRoom', 'classSection.section')
            ->orderBy('admission_no')
            ->get();

        $staffs = Staff::where('school_id', $this->schoolId())
            ->with('user:id,name')
            ->orderBy('employee_id')
            ->get();

        return view('school-admin.letters.create', compact('templates', 'students', 'staffs'));
    }

    public function store(Request $request, LetterService $letterService): RedirectResponse
    {
        $data = $request->validate([
            'letter_template_id' => 'nullable|exists:letter_templates,id',
            'recipient_type'     => 'required|in:' . implode(',', self::RECIPIENT_TYPES),
            'recipient_id'       => 'nullable|integer',
            'recipient_name'     => 'required|string|max:200',
            'recipient_address'  => 'nullable|string|max:1000',
            'subject'            => 'required|string|max:255',
            'content'            => 'required|string|max:50000',
            'status'             => 'required|in:draft,sent',
            'notes'              => 'nullable|string|max:5000',
        ]);

        $schoolId = $this->schoolId();

        if ($data['letter_template_id']) {
            $template = LetterTemplate::findOrFail($data['letter_template_id']);
            $code = $template->code;
            $variables = $this->resolveTemplateVariables($data['recipient_type'], $data['recipient_id']);
            $content = $letterService->renderTemplate($template, $variables);
        } else {
            $code = 'UMUM';
            $content = $data['content'];
        }

        $letterNumber = $letterService->generateLetterNumber($code, $schoolId);

        $letter = Letter::create([
            'school_id'          => $schoolId,
            'letter_template_id' => $data['letter_template_id'] ?? null,
            'letter_number'      => $letterNumber,
            'recipient_type'     => $data['recipient_type'],
            'recipient_id'       => $data['recipient_id'] ?? null,
            'recipient_name'     => $data['recipient_name'],
            'recipient_address'  => $data['recipient_address'] ?? null,
            'subject'            => $data['subject'],
            'content'            => $content,
            'status'             => $data['status'],
            'issued_by'          => auth()->id(),
            'issued_at'          => $data['status'] === 'sent' ? now() : null,
            'notes'              => $data['notes'] ?? null,
        ]);

        return redirect()->route('admin.letters.index')->with('success', 'Surat berhasil dibuat. Nomor: ' . $letterNumber);
    }

    public function edit(Letter $letter): View
    {
        abort_if($letter->school_id !== $this->schoolId(), 403);

        $templates = LetterTemplate::where('school_id', $this->schoolId())
            ->where('is_active', true)
            ->orderBy('name')->get();

        $students = Student::where('school_id', $this->schoolId())
            ->with('user:id,name', 'classSection.classRoom', 'classSection.section')
            ->orderBy('admission_no')->get();

        $staffs = Staff::where('school_id', $this->schoolId())
            ->with('user:id,name')
            ->orderBy('employee_id')->get();

        return view('school-admin.letters.create', compact('letter', 'templates', 'students', 'staffs'));
    }

    public function update(Request $request, Letter $letter): RedirectResponse
    {
        abort_if($letter->school_id !== $this->schoolId(), 403);

        $data = $request->validate([
            'recipient_name'    => 'required|string|max:200',
            'recipient_address' => 'nullable|string|max:1000',
            'subject'           => 'required|string|max:255',
            'content'           => 'required|string|max:50000',
            'status'            => 'required|in:draft,sent,archived',
            'notes'             => 'nullable|string|max:5000',
        ]);

        $update = [
            'recipient_name'    => $data['recipient_name'],
            'recipient_address' => $data['recipient_address'] ?? null,
            'subject'           => $data['subject'],
            'content'           => $data['content'],
            'status'            => $data['status'],
            'notes'             => $data['notes'] ?? null,
        ];

        if ($data['status'] === 'sent' && !$letter->issued_at) {
            $update['issued_at'] = now();
        }

        $letter->update($update);

        return redirect()->route('admin.letters.index')->with('success', 'Surat berhasil diperbarui.');
    }

    public function destroy(Letter $letter): RedirectResponse
    {
        abort_if($letter->school_id !== $this->schoolId(), 403);
        $letter->delete();
        return redirect()->route('admin.letters.index')->with('success', 'Surat berhasil dihapus.');
    }

    public function print(Letter $letter)
    {
        abort_if($letter->school_id !== $this->schoolId(), 403);
        $branding = app(\App\Services\Branding\BrandingService::class)->getForSchool($letter->school_id);

        $pdf = Pdf::loadView('pdf.letter', [
            'letter'   => $letter,
            'branding' => $branding,
        ]);

        $pdf->setPaper('a4');
        $pdf->setOptions([
            'defaultFont'          => 'serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
        ]);

        $filename = 'surat-' . Str::slug($letter->letter_number) . '.pdf';

        return $pdf->download($filename);
    }

    public function previewTemplate(LetterTemplate $template, Request $request, LetterService $letterService): string
    {
        abort_if($template->school_id !== $this->schoolId(), 403);

        $variables = $this->resolveTemplateVariables(
            $request->get('recipient_type', 'student'),
            $request->get('recipient_id')
        );

        return $letterService->renderTemplate($template, $variables);
    }

    private function resolveTemplateVariables(string $recipientType, ?int $recipientId): array
    {
        $service = app(LetterService::class);

        $base = [
            'sekolah' => config('app.name', 'Sekolah'),
            'tanggal' => now()->format('d F Y'),
        ];

        if ($recipientId && $recipientType === 'student') {
            $student = Student::with('user', 'classSection.classRoom', 'classSection.section')->find($recipientId);
            if ($student) {
                return array_merge($base, $service->getDefaultVariablesForStudent($student));
            }
        }

        if ($recipientId && $recipientType === 'staff') {
            $staff = Staff::with('user')->find($recipientId);
            if ($staff) {
                return array_merge($base, $service->getDefaultVariablesForStaff($staff));
            }
        }

        return $base;
    }
}
