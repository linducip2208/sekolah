<?php

namespace App\Http\Controllers\Api\Facilities;

use App\Http\Controllers\Controller;
use App\Models\Facilities\Book;
use App\Models\Facilities\BookCategory;
use App\Models\Facilities\BookIssue;
use App\Services\Facilities\LibraryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    public function __construct(private LibraryService $service) {}

    public function categories(): JsonResponse
    {
        return response()->json(BookCategory::all());
    }

    public function storeCategory(Request $request): JsonResponse
    {
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $validated['school_id'] = auth()->user()->school_id;
        return response()->json(BookCategory::create($validated), 201);
    }

    public function books(Request $request): JsonResponse
    {
        $books = Book::when($request->category_id, fn($q) => $q->where('book_category_id', $request->category_id))
            ->when($request->search, fn($q) => $q->where('title', 'like', '%' . $request->search . '%'))
            ->when($request->barcode, fn($q) => $q->where('barcode', $request->barcode))
            ->with('bookCategory')
            ->latest()
            ->get();
        return response()->json($books);
    }

    public function storeBook(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'book_category_id'   => 'required|integer|exists:book_categories,id',
            'title'              => 'required|string|max:255',
            'author'             => 'nullable|string|max:255',
            'isbn'               => 'nullable|string|max:50',
            'publisher'          => 'nullable|string|max:255',
            'total_quantity'     => 'sometimes|integer|min:1',
            'available_quantity' => 'sometimes|integer|min:0',
            'barcode'            => 'nullable|string|max:100',
            'rack_location'      => 'nullable|string|max:100',
        ]);
        $validated['school_id'] = auth()->user()->school_id;
        return response()->json(Book::create($validated), 201);
    }

    public function updateBook(Request $request, Book $book): JsonResponse
    {
        $validated = $request->validate([
            'title'          => 'sometimes|string|max:255',
            'total_quantity' => 'sometimes|integer|min:1',
            'rack_location'  => 'nullable|string|max:100',
            'is_active'      => 'sometimes|boolean',
        ]);
        $book->update($validated);
        return response()->json($book->fresh());
    }

    public function destroyBook(Book $book): JsonResponse
    {
        $book->delete();
        return response()->json(['message' => 'Book deleted.']);
    }

    public function issue(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'book_id'  => 'required|integer|exists:books,id',
            'user_id'  => 'required|integer|exists:users,id',
            'due_days' => 'sometimes|integer|min:1|max:90',
        ]);

        $issue = $this->service->issueBook(
            $validated['book_id'],
            $validated['user_id'],
            auth()->id(),
            $validated['due_days'] ?? 14
        );
        return response()->json($issue, 201);
    }

    public function returnBook(Request $request, int $issueId): JsonResponse
    {
        $issue = $this->service->returnBook($issueId, auth()->id());
        return response()->json($issue);
    }

    public function issues(Request $request): JsonResponse
    {
        $issues = BookIssue::when($request->status, fn($q) => $q->where('status', $request->status))
            ->with('book', 'issuedTo', 'issuedBy')
            ->latest()
            ->get();
        return response()->json($issues);
    }

    public function markOverdue(): JsonResponse
    {
        $count = $this->service->markOverdue();
        return response()->json(['marked' => $count]);
    }
}
