<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Http\Resources\BookResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use OpenApi\Attributes as OA;

class BookController extends Controller
{
    #[OA\Get(
        path: '/books',
        summary: 'Liste tous les livres',
        description: 'Retourne une liste paginée de tous les livres (2 par page)',
        tags: ['Livres']
    )]
    #[OA\Parameter(
        name: 'page',
        in: 'query',
        description: 'Numéro de la page',
        required: false,
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: 'Liste des livres récupérée avec succès',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'title', type: 'string', example: '1984'),
                            new OA\Property(property: 'author', type: 'string', example: 'George Orwell'),
                            new OA\Property(property: 'summary', type: 'string', example: 'Un roman dystopique...'),
                            new OA\Property(property: 'isbn', type: 'string', example: '9780451524935'),
                        ],
                        type: 'object'
                    )
                ),
                new OA\Property(
                    property: 'meta',
                    type: 'object',
                    example: [
                        'current_page' => 1,
                        'per_page'     => 2,
                        'total'        => 10
                    ]
                )
            ],
            type: 'object'
        )
    )]
    public function index()
    {
        $books = Book::paginate(2);
        return BookResource::collection($books);
    }

    #[OA\Post(
        path: '/books',
        summary: 'Créer un nouveau livre',
        description: 'Ajoute un nouveau livre (authentification requise)',
        security: [['bearerAuth' => []]],
        tags: ['Livres']
    )]
    #[OA\Parameter(
        name: 'Authorization',
        in: 'header',
        description: 'Token Bearer. Exemple: Bearer 1|abcdefghijklmnopqrstuvwxyz',
        required: true,
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'Accept',
        in: 'header',
        description: 'Type de réponse attendu',
        required: false,
        schema: new OA\Schema(type: 'string', example: 'application/json')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['title', 'author', 'summary', 'isbn'],
            properties: [
                new OA\Property(property: 'title', type: 'string', minLength: 3, maxLength: 255, example: '1984'),
                new OA\Property(property: 'author', type: 'string', minLength: 3, maxLength: 100, example: 'George Orwell'),
                new OA\Property(property: 'summary', type: 'string', minLength: 10, maxLength: 500, example: 'Un roman dystopique...'),
                new OA\Property(property: 'isbn', type: 'string', minLength: 13, maxLength: 13, example: '9780451524935')
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Livre créé avec succès',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'title', type: 'string', example: '1984'),
                new OA\Property(property: 'author', type: 'string', example: 'George Orwell'),
                new OA\Property(property: 'summary', type: 'string', example: 'Un roman dystopique...'),
                new OA\Property(property: 'isbn', type: 'string', example: '9780451524935'),
                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Non authentifié',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 422,
        description: 'Erreur de validation',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
                new OA\Property(property: 'errors', type: 'object', example: [
                    'title' => ['Le titre est obligatoire.']
                ])
            ],
            type: 'object'
        )
    )]
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

    #[OA\Get(
        path: '/books/{id}',
        summary: 'Afficher un livre spécifique',
        description: 'Retourne les détails d\'un livre (avec cache de 60 minutes)',
        tags: ['Livres']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'ID du livre',
        required: true,
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: 'Livre récupéré avec succès',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'title', type: 'string', example: '1984'),
                new OA\Property(property: 'author', type: 'string', example: 'George Orwell'),
                new OA\Property(property: 'summary', type: 'string', example: 'Un roman dystopique...'),
                new OA\Property(property: 'isbn', type: 'string', example: '9780451524935'),
                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Livre non trouvé',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Livre non trouvé')
            ],
            type: 'object'
        )
    )]
    public function show(Book $book)
    {
        $cachedBook = Cache::remember("book.{$book->id}", 3600, function () use ($book) {
            return $book;
        });

        return new BookResource($cachedBook);
    }

    #[OA\Put(
        path: '/books/{id}',
        summary: 'Mettre à jour un livre',
        description: 'Modifie un livre existant (authentification requise)',
        security: [['bearerAuth' => []]],
        tags: ['Livres']
    )]
    #[OA\Patch(
        path: '/books/{id}',
        summary: 'Mettre à jour partiellement un livre',
        description: 'Modifie partiellement un livre (authentification requise)',
        security: [['bearerAuth' => []]],
        tags: ['Livres']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'ID du livre',
        required: true,
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Parameter(
        name: 'Authorization',
        in: 'header',
        description: 'Token Bearer. Exemple: Bearer 1|abcdefghijklmnopqrstuvwxyz',
        required: true,
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'Accept',
        in: 'header',
        description: 'Type de réponse attendu',
        required: false,
        schema: new OA\Schema(type: 'string', example: 'application/json')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'title', type: 'string', example: 'Nouveau titre'),
                new OA\Property(property: 'author', type: 'string', example: 'Nouvel auteur'),
                new OA\Property(property: 'summary', type: 'string', example: 'Nouveau résumé...'),
                new OA\Property(property: 'isbn', type: 'string', example: '9781234567890')
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Livre mis à jour',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'title', type: 'string', example: 'Nouveau titre'),
                new OA\Property(property: 'author', type: 'string', example: 'Nouvel auteur'),
                new OA\Property(property: 'summary', type: 'string', example: 'Nouveau résumé...'),
                new OA\Property(property: 'isbn', type: 'string', example: '9781234567890'),
                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Non authentifié',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Livre non trouvé',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Livre non trouvé')
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 422,
        description: 'Erreur de validation',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
                new OA\Property(property: 'errors', type: 'object', example: [
                    'title' => ['Le titre doit contenir au moins 3 caractères.']
                ])
            ],
            type: 'object'
        )
    )]
    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|min:3|max:255',
            'author' => 'sometimes|required|string|min:3|max:100',
            'summary' => 'sometimes|required|string|min:10|max:500',
            'isbn' => 'sometimes|required|string|size:13|unique:books,isbn,' . $book->id,
        ]);

        $book->update($validated);

        Cache::forget("book.{$book->id}");

        return new BookResource($book);
    }

    #[OA\Delete(
        path: '/books/{id}',
        summary: 'Supprimer un livre',
        description: 'Supprime un livre (authentification requise)',
        security: [['bearerAuth' => []]],
        tags: ['Livres']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'ID du livre',
        required: true,
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Parameter(
        name: 'Authorization',
        in: 'header',
        description: 'Token Bearer. Exemple: Bearer 1|abcdefghijklmnopqrstuvwxyz',
        required: true,
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'Accept',
        in: 'header',
        description: 'Type de réponse attendu',
        required: false,
        schema: new OA\Schema(type: 'string', example: 'application/json')
    )]
    #[OA\Response(
        response: 204,
        description: 'Livre supprimé avec succès'
    )]
    #[OA\Response(
        response: 401,
        description: 'Non authentifié',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Livre non trouvé',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Livre non trouvé')
            ],
            type: 'object'
        )
    )]
    public function destroy(Book $book)
    {
        Cache::forget("book.{$book->id}");

        $book->delete();

        return response()->noContent();
    }
}
