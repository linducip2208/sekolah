<?php

namespace App\Http\Controllers\Web\Admin\Library;

use App\Http\Controllers\Controller;
use App\Models\Facilities\Book;
use App\Models\Facilities\BookCategory;
use App\Models\Facilities\BookIssue;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LibraryWebController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    /* ============== BOOKS ============== */

    public function books(Request $request): View
    {
        $schoolId = $this->schoolId();

        $books = Book::where('school_id', $schoolId)
            ->with('category')
            ->when($request->search, fn ($q) => $q->where(fn ($s) => $s
                ->where('title', 'like', "%{$request->search}%")
                ->orWhere('author', 'like', "%{$request->search}%")
                ->orWhere('isbn', 'like', "%{$request->search}%")))
            ->when($request->category_id, fn ($q) => $q->where('book_category_id', $request->category_id))
            ->orderBy('title')
            ->paginate(20)
            ->withQueryString();

        $categories = BookCategory::where('school_id', $schoolId)->orderBy('name')->get();

        return view('school-admin.library.books', compact('books', 'categories'));
    }

    public function storeBook(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'             => 'required|string|max:255',
            'author'            => 'nullable|string|max:255',
            'isbn'              => 'nullable|string|max:50',
            'book_category_id'  => 'required|exists:book_categories,id',
            'publisher'         => 'nullable|string|max:255',
            'publish_year'      => 'nullable|integer|min:1900|max:'.(date('Y') + 1),
            'total_quantity'    => 'required|integer|min:1|max:9999',
            'rack_location'     => 'nullable|string|max:50',
            'description'       => 'nullable|string',
        ]);

        $data['school_id'] = $this->schoolId();
        $data['available_quantity'] = $data['total_quantity'];
        $data['is_active'] = true;
        Book::create($data);

        return back()->with('success', 'Buku ditambahkan ke katalog.');
    }

    public function deleteBook(Book $book): RedirectResponse
    {
        $this->authorizeOwn($book);
        $book->delete();
        return back()->with('success', 'Buku dihapus.');
    }

    /* ============== CATEGORIES ============== */

    public function categories(): View
    {
        return view('school-admin.library.categories', [
            'categories' => BookCategory::where('school_id', $this->schoolId())->orderBy('name')->get(),
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => 'required|string|max:100']);
        $data['school_id'] = $this->schoolId();
        BookCategory::create($data);
        return back()->with('success', 'Kategori ditambahkan.');
    }

    public function deleteCategory(BookCategory $category): RedirectResponse
    {
        $this->authorizeOwn($category);
        $category->delete();
        return back()->with('success', 'Kategori dihapus.');
    }

    /* ============== ISSUE / RETURN ============== */

    public function issues(Request $request): View
    {
        $schoolId = $this->schoolId();

        $issues = BookIssue::where('school_id', $schoolId)
            ->with(['book', 'issuedTo:id,name'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('issue_date')
            ->paginate(25)
            ->withQueryString();

        return view('school-admin.library.issues', [
            'issues' => $issues,
            'books'  => Book::where('school_id', $schoolId)->where('available_quantity', '>', 0)->orderBy('title')->get(['id','title','available_quantity']),
            'users'  => User::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function issueBook(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'book_id'    => 'required|exists:books,id',
            'issued_to'  => 'required|exists:users,id',
            'issue_date' => 'required|date',
            'due_date'   => 'required|date|after_or_equal:issue_date',
            'note'       => 'nullable|string|max:255',
        ]);

        $schoolId = $this->schoolId();

        DB::transaction(function () use ($data, $schoolId) {
            $book = Book::where('school_id', $schoolId)->findOrFail($data['book_id']);
            if ($book->available_quantity <= 0) {
                abort(422, 'Buku tidak tersedia.');
            }

            BookIssue::create([
                'school_id'  => $schoolId,
                'book_id'    => $book->id,
                'issued_to'  => $data['issued_to'],
                'issued_by'  => auth()->id(),
                'issue_date' => $data['issue_date'],
                'due_date'   => $data['due_date'],
                'status'     => 'issued',
                'note'       => $data['note'] ?? null,
            ]);

            $book->decrement('available_quantity');
        });

        return back()->with('success', 'Buku berhasil dipinjamkan.');
    }

    public function returnBook(Request $request, BookIssue $issue): RedirectResponse
    {
        $this->authorizeOwn($issue);

        $data = $request->validate([
            'return_date' => 'required|date|after_or_equal:'.$issue->issue_date->toDateString(),
            'fine_amount_rupiah' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($issue, $data) {
            $issue->update([
                'return_date' => $data['return_date'],
                'returned_to' => auth()->id(),
                'fine_amount' => isset($data['fine_amount_rupiah']) ? (int) ($data['fine_amount_rupiah'] * 100) : 0,
                'status'      => 'returned',
            ]);
            $issue->book->increment('available_quantity');
        });

        return back()->with('success', 'Buku dikembalikan.');
    }

    private function authorizeOwn($model): void
    {
        abort_unless($model->school_id === $this->schoolId(), 403);
    }
}
