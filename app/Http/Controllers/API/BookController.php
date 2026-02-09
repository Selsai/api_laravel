<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Http\Resources\BookResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    // Lister tous les livres
    public function index()
    {
        $books = Book::all();
        return BookResource::collection($books);
    }

    // Créer un nouveau livre
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:3|max:255',
            'author' => 'required|string|min:3|max:100',
            'summary' => 'required|string|min:10|max:500',
            'isbn' => 'required|string|size:13|unique:books,isbn',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $book = Book::create($request->all());
        
        return (new BookResource($book))
            ->response()
            ->setStatusCode(201);
    }

    // Afficher un livre spécifique
    public function show(Book $book)
    {
        return new BookResource($book);
    }

    // Mettre à jour un livre
    public function update(Request $request, Book $book)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|min:3|max:255',
            'author' => 'sometimes|required|string|min:3|max:100',
            'summary' => 'sometimes|required|string|min:10|max:500',
            'isbn' => 'sometimes|required|string|size:13|unique:books,isbn,' . $book->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $book->update($request->all());
        
        return new BookResource($book);
    }

    // Supprimer un livre
    public function destroy(Book $book)
    {
        $book->delete();
        
        return response()->json(null, 204);
    }
}