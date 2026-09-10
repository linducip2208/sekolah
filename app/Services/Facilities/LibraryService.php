<?php

namespace App\Services\Facilities;

use App\Models\Facilities\Book;
use App\Models\Facilities\BookIssue;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LibraryService
{
    public function issueBook(int $bookId, int $userId, int $librarianId, int $dueDays = 14): BookIssue
    {
        return DB::transaction(function () use ($bookId, $userId, $librarianId, $dueDays) {
            $schoolId = $this->schoolId();
            $book = Book::withoutGlobalScopes()
                ->where('school_id', $schoolId)
                ->lockForUpdate()
                ->findOrFail($bookId);
            User::withoutGlobalScopes()->where('school_id', $schoolId)->findOrFail($userId);
            User::withoutGlobalScopes()->where('school_id', $schoolId)->findOrFail($librarianId);

            if ($book->available_quantity < 1) {
                abort(422, "Tidak ada salinan tersedia: {$book->title}");
            }

            $overdue = BookIssue::withoutGlobalScopes()
                ->where('school_id', $schoolId)
                ->where('issued_to', $userId)
                ->where('status', 'overdue')
                ->exists();

            if ($overdue) {
                abort(422, 'Anggota memiliki buku yang terlambat. Kembalikan dulu.');
            }

            $school = app('current_school');
            $maxBooks = data_get($school->settings, 'library.max_books_per_member', 3);

            $activeBorrows = BookIssue::withoutGlobalScopes()
                ->where('school_id', $schoolId)
                ->where('issued_to', $userId)
                ->where('status', 'issued')
                ->count();

            if ($activeBorrows >= $maxBooks) {
                abort(422, "Anggota sudah meminjam {$maxBooks} buku (batas maksimal).");
            }

            $issue = BookIssue::create([
                'school_id' => $book->school_id,
                'book_id' => $bookId,
                'issued_to' => $userId,
                'issued_by' => $librarianId,
                'issue_date' => today(),
                'due_date' => today()->addDays($dueDays),
                'status' => 'issued',
            ]);

            $book->decrement('available_quantity');

            return $issue->load('book', 'issuedTo');
        });
    }

    public function returnBook(int $issueId, int $librarianId): BookIssue
    {
        return DB::transaction(function () use ($issueId, $librarianId) {
            $schoolId = $this->schoolId();
            User::withoutGlobalScopes()->where('school_id', $schoolId)->findOrFail($librarianId);
            $issue = BookIssue::withoutGlobalScopes()
                ->where('school_id', $schoolId)
                ->lockForUpdate()
                ->where('status', '!=', 'returned')
                ->findOrFail($issueId);

            $fine = $this->calculateFine($issue);

            $issue->update([
                'return_date' => today(),
                'returned_to' => $librarianId,
                'status' => 'returned',
                'fine_amount' => $fine,
                'fine_paid' => $fine === 0,
            ]);

            Book::withoutGlobalScopes()
                ->where('school_id', $schoolId)
                ->whereKey($issue->book_id)
                ->lockForUpdate()
                ->increment('available_quantity');

            return $issue->fresh()->load('book');
        });
    }

    public function calculateFine(BookIssue $issue): int
    {
        if (today()->lte($issue->due_date)) {
            return 0;
        }

        $school = app('current_school');
        $finePerDay = data_get($school->settings, 'library.fine_per_day_cents', 50000);
        $overdueDays = $issue->due_date->diffInDays(today());

        return $overdueDays * $finePerDay;
    }

    public function markOverdue(): int
    {
        return BookIssue::where('due_date', '<', today())
            ->where('status', 'issued')
            ->update(['status' => 'overdue']);
    }

    protected function schoolId(): int
    {
        return (int) (auth()->user()?->school_id ?? app('current_school')->id);
    }
}
