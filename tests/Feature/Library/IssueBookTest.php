<?php

use App\Models\Academic\Student;
use App\Models\Facilities\Book;
use App\Models\Facilities\BookCategory;
use App\Models\Facilities\BookIssue;
use App\Models\School;
use App\Models\User;
use App\Services\Facilities\LibraryService;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->school = School::factory()->create([
        'settings' => [
            'library' => [
                'max_books_per_member' => 3,
                'default_issue_days'   => 14,
                'fine_per_day_cents'   => 50000,
            ],
        ],
    ]);

    app()->instance('current_school', $this->school);

    $this->librarian = User::factory()->create([
        'school_id' => $this->school->id,
        'is_active' => true,
    ]);

    $category     = BookCategory::create(['school_id' => $this->school->id, 'name' => 'Fiction']);
    $this->book   = Book::create([
        'school_id'          => $this->school->id,
        'book_category_id'   => $category->id,
        'title'              => 'Test Book',
        'total_quantity'     => 3,
        'available_quantity' => 3,
    ]);

    $memberUser    = User::factory()->create(['school_id' => $this->school->id]);
    $this->student = Student::create([
        'user_id'   => $memberUser->id,
        'school_id' => $this->school->id,
    ]);
});

test('librarian can issue a book', function () {
    $service = app(LibraryService::class);
    $issue   = $service->issueBook($this->book->id, $this->librarian->id, $this->librarian->id);

    expect($issue->status)->toBe('issued')
        ->and($issue->due_date)->toEqual(today()->addDays(14));

    expect(Book::find($this->book->id)->available_quantity)->toBe(2);
});

test('cannot issue book when no copies available', function () {
    $this->book->update(['available_quantity' => 0]);

    $service = app(LibraryService::class);

    expect(fn() => $service->issueBook($this->book->id, $this->librarian->id, $this->librarian->id))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

test('cannot issue more than max books per member', function () {
    $service = app(LibraryService::class);

    // Issue 3 books (max)
    for ($i = 0; $i < 3; $i++) {
        $cat  = BookCategory::create(['school_id' => $this->school->id, 'name' => "Cat $i"]);
        $book = Book::create([
            'school_id'          => $this->school->id,
            'book_category_id'   => $cat->id,
            'title'              => "Book $i",
            'total_quantity'     => 5,
            'available_quantity' => 5,
        ]);
        $service->issueBook($book->id, $this->librarian->id, $this->librarian->id);
    }

    // 4th should fail
    expect(fn() => $service->issueBook($this->book->id, $this->librarian->id, $this->librarian->id))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

test('available quantity increments on return', function () {
    $service = app(LibraryService::class);
    $issue   = $service->issueBook($this->book->id, $this->librarian->id, $this->librarian->id);

    expect(Book::find($this->book->id)->available_quantity)->toBe(2);

    $service->returnBook($issue->id, $this->librarian->id);

    expect(Book::find($this->book->id)->available_quantity)->toBe(3);
});

test('fine is calculated correctly for overdue books', function () {
    $service = app(LibraryService::class);
    $issue   = BookIssue::create([
        'school_id'  => $this->school->id,
        'book_id'    => $this->book->id,
        'issued_to'  => $this->librarian->id,
        'issued_by'  => $this->librarian->id,
        'issue_date' => today()->subDays(20),
        'due_date'   => today()->subDays(6),
        'status'     => 'issued',
    ]);

    $fine = $service->calculateFine($issue);
    expect($fine)->toBe(6 * 50000); // 6 days * 50000 cents = 300000
});
