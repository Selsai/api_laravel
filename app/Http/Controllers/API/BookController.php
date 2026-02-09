<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Http\Resources\BookResource;
use Illuminate\Http\Request;

class BookController extends Controller
{
    // Lister tous les livres
    public function index()
    {
        return BookResource::collection(Book::all());
    }

    // Créer un nouveau livre
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|min:3|max:255',
            'author' => 'required|string|min:3|max:100',
            'summary' => 'required|string|min:10|max:500',
            'isbn' => 'required|string|size:13|unique:books,isbn',
        ]); 
               
        return new BookResource(Book::create($validated));
    }

    // Afficher un livre spécifique
    public function show(Book $book)
    {
        return new BookResource($book);
    }

    // Mettre à jour un livre
    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|min:3|max:255',
            'author' => 'sometimes|required|string|min:3|max:100',
            'summary' => 'sometimes|required|string|min:10|max:500',
            'isbn' => 'sometimes|required|string|size:13|unique:books,isbn,' . $book->id,
        ]);

        $book->update($validated);
        
        return new BookResource($book);
    }

    // Supprimer un livre
    public function destroy(Book $book)
    {
        $book->delete();
        
        return response()->noContent();
    }
}