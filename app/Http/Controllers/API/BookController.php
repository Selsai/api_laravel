<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Http\Resources\BookResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BookController extends Controller
{
    /**
     * Lister tous les livres avec pagination (2 éléments par page)
     */
    public function index()
    {
        $books = Book::paginate(2);
        return BookResource::collection($books);
    }

    /**
     * Créer un nouveau livre
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|min:3|max:255',
            'author' => 'required|string|min:3|max:100',
            'summary' => 'required|string|min:10|max:500',
            'isbn' => 'required|string|size:13|unique:books,isbn',
        ]); 
               
        $book = Book::create($validated);
        
        return response()->json(new BookResource($book), 201);
    }

    /**
     * Afficher un livre spécifique avec mise en cache (60 minutes)
     */
    public function show(Book $book)
    {
        $cachedBook = Cache::remember("book.{$book->id}", 3600, function () use ($book) {
            return $book;
        });

        return new BookResource($cachedBook);
    }

    /**
     * Mettre à jour un livre
     */
    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|min:3|max:255',
            'author' => 'sometimes|required|string|min:3|max:100',
            'summary' => 'sometimes|required|string|min:10|max:500',
            'isbn' => 'sometimes|required|string|size:13|unique:books,isbn,' . $book->id,
        ]);

        $book->update($validated);
        
        // Invalider le cache après la mise à jour
        Cache::forget("book.{$book->id}");
        
        return new BookResource($book);
    }

    /**
     * Supprimer un livre
     */
    public function destroy(Book $book)
    {
        // Invalider le cache avant la suppression
        Cache::forget("book.{$book->id}");
        
        $book->delete();
        
        return response()->noContent();
    }
}