<?php

namespace App\Http\Controllers\Web\Admin\Library;

use App\Http\Controllers\Controller;
use App\Models\Facilities\Book;
use App\Models\Facilities\BookReadingProgress;
use App\Models\Facilities\DigitalBookIssue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DigitalLibraryController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function upload(): View
    {
        $schoolId = $this->schoolId();
        $digitalBooks = Book::where('school_id', $schoolId)
            ->where('is_digital', true)
            ->with('category')
            ->orderByDesc('created_at')
            ->get();

        return view('school-admin.library.digital.upload', compact('digitalBooks'));
    }

    public function storeDigital(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'author'           => 'nullable|string|max:255',
            'book_category_id' => 'required|exists:book_categories,id',
            'publisher'        => 'nullable|string|max:255',
            'description'      => 'nullable|string',
            'digital_file'     => 'required|file|mimes:pdf,epub|max:102400',
            'preview_pages'    => 'nullable|integer|min:0|max:100',
            'is_downloadable'  => 'boolean',
        ]);

        $schoolId = $this->schoolId();
        $file = $request->file('digital_file');
        $path = $file->store("schools/{$schoolId}/digital-books", 'private');
        $extension = $file->getClientOriginalExtension();

        $book = Book::create([
            'school_id'        => $schoolId,
            'book_category_id' => $data['book_category_id'],
            'title'            => $data['title'],
            'author'           => $data['author'] ?? null,
            'publisher'        => $data['publisher'] ?? null,
            'description'      => $data['description'] ?? null,
            'is_active'        => true,
            'is_digital'       => true,
            'digital_file_path'=> $path,
            'file_type'        => $extension,
            'file_size'        => $file->getSize(),
            'total_quantity'   => 1,
            'available_quantity'=> 1,
            'preview_pages'    => $data['preview_pages'] ?? 10,
            'is_downloadable'  => $data['is_downloadable'] ?? false,
            'page_count'       => 0,
        ]);

        return redirect()->route('admin.library.digital.upload')
            ->with('success', 'Buku digital "' . $book->title . '" berhasil diunggah.');
    }

    public function deleteDigital(Book $book): RedirectResponse
    {
        $this->authorizeOwn($book);

        if ($book->digital_file_path) {
            Storage::disk('private')->delete($book->digital_file_path);
        }
        $book->delete();

        return back()->with('success', 'Buku digital dihapus.');
    }

    public function issueDigital(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'book_id'     => 'required|exists:books,id',
            'student_id'  => 'nullable|exists:users,id',
            'staff_id'    => 'nullable|exists:users,id',
            'duration_days'=> 'required|integer|min:1|max:365',
        ]);

        $schoolId = $this->schoolId();
        $book = Book::where('school_id', $schoolId)->findOrFail($data['book_id']);

        if (!$book->is_digital) {
            return back()->withErrors(['book_id' => 'Buku ini bukan buku digital.']);
        }

        $token = Str::random(64);
        $expiresAt = now()->addDays($data['duration_days']);

        DigitalBookIssue::create([
            'school_id'        => $schoolId,
            'book_id'          => $book->id,
            'student_id'       => $data['student_id'] ?? null,
            'staff_id'         => $data['staff_id'] ?? null,
            'access_token'     => $token,
            'access_expires_at'=> $expiresAt,
            'is_active'        => true,
        ]);

        $url = route('reader.view', $token);

        return back()->with('success', "Akses digital diberikan. Link baca: <a href=\"{$url}\" target=\"_blank\" class=\"underline\">{$url}</a>");
    }

    public function revokeAccess(DigitalBookIssue $issue): RedirectResponse
    {
        $this->authorizeOwn($issue);
        $issue->update(['is_active' => false]);
        return back()->with('success', 'Akses digital dicabut.');
    }

    public function stats(): View
    {
        $schoolId = $this->schoolId();

        $totalDigital = Book::where('school_id', $schoolId)->where('is_digital', true)->count();
        $totalReads = Book::where('school_id', $schoolId)->where('is_digital', true)->sum('read_count');
        $totalDownloads = Book::where('school_id', $schoolId)->where('is_digital', true)->sum('download_count');

        $activeIssues = DigitalBookIssue::where('school_id', $schoolId)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('access_expires_at')->orWhere('access_expires_at', '>', now());
            })
            ->with(['book', 'student', 'staff'])
            ->orderByDesc('issued_at')
            ->get();

        $topBooks = Book::where('school_id', $schoolId)
            ->where('is_digital', true)
            ->orderByDesc('read_count')
            ->limit(10)
            ->get();

        $progressData = BookReadingProgress::whereHas('digitalBookIssue', function ($q) use ($schoolId) {
            $q->where('school_id', $schoolId);
        })
            ->with('digitalBookIssue.book')
            ->orderByDesc('last_read_at')
            ->limit(20)
            ->get();

        return view('school-admin.library.digital.stats', compact(
            'totalDigital', 'totalReads', 'totalDownloads',
            'activeIssues', 'topBooks', 'progressData'
        ));
    }

    private function authorizeOwn($model): void
    {
        abort_unless($model->school_id === $this->schoolId(), 403);
    }
}
