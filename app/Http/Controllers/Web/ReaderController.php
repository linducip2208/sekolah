<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Facilities\Book;
use App\Models\Facilities\BookReadingProgress;
use App\Models\Facilities\DigitalBookIssue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ReaderController extends Controller
{
    public function view(string $token): View
    {
        $issue = DigitalBookIssue::where('access_token', $token)
            ->where('is_active', true)
            ->with('book')
            ->firstOrFail();

        if ($issue->access_expires_at && $issue->access_expires_at->isPast()) {
            abort(410, 'Masa akses buku digital ini telah berakhir.');
        }

        $book = $issue->book;
        if (!$book || !$book->is_digital || !$book->digital_file_path) {
            abort(404, 'Buku digital tidak tersedia.');
        }

        $progress = BookReadingProgress::firstOrCreate(
            ['digital_book_issue_id' => $issue->id],
            [
                'current_page'     => 1,
                'total_pages'      => $book->page_count,
                'progress_percent' => 0,
                'last_read_at'     => now(),
            ]
        );

        $fileUrl = route('reader.serve', $token);

        $book->increment('read_count');

        return view('library.reader', compact('issue', 'book', 'progress', 'fileUrl'));
    }

    public function serve(string $token): \Illuminate\Http\Response
    {
        $issue = DigitalBookIssue::where('access_token', $token)
            ->where('is_active', true)
            ->with('book')
            ->firstOrFail();

        if ($issue->access_expires_at && $issue->access_expires_at->isPast()) {
            abort(410, 'Masa akses buku digital ini telah berakhir.');
        }

        $book = $issue->book;
        $path = $book->digital_file_path;

        if (!Storage::disk('private')->exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        $mimeMap = ['pdf' => 'application/pdf', 'epub' => 'application/epub+zip'];
        $mime = $mimeMap[$book->file_type] ?? 'application/octet-stream';

        return response(Storage::disk('private')->get($path), 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => ($book->is_downloadable)
                ? "attachment; filename=\"" . ($book->title) . ".{$book->file_type}\""
                : 'inline; filename="' . ($book->title) . ".{$book->file_type}" . '"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }
}
