<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    #[OA\Post(
        path: '/register',
        summary: 'Inscription d\'un nouvel utilisateur',
        description: 'Crée un nouvel utilisateur et génère un token d\'authentification',
        tags: ['Authentification']
    )]
    #[OA\Parameter(
        name: 'Accept',
        in: 'header',
        description: 'Toujours "application/json"',
        required: true,
        schema: new OA\Schema(type: 'string', example: 'application/json')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name', 'email', 'password'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'John Doe', maxLength: 255),
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com', maxLength: 255),
                new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123', minLength: 8)
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Utilisateur créé avec succès',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Utilisateur créé avec succès'),
                new OA\Property(
                    property: 'user',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                        new OA\Property(property: 'email', type: 'string', example: 'john@example.com')
                    ],
                    type: 'object'
                ),
                new OA\Property(property: 'token', type: 'string', example: '1|abcdefghijklmnopqrstuvwxyz')
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 422,
        description: 'Erreur de validation',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'The email has already been taken.'),
                new OA\Property(property: 'errors', type: 'object')
            ],
            type: 'object'
        )
    )]
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Utilisateur créé avec succès',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    #[OA\Post(
        path: '/login',
        summary: 'Connexion d\'un utilisateur',
        description: 'Authentifie un utilisateur et retourne un token',
        tags: ['Authentification']
    )]
    #[OA\Parameter(
        name: 'Accept',
        in: 'header',
        description: 'Toujours "application/json"',
        required: true,
        schema: new OA\Schema(type: 'string', example: 'application/json')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123')
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Connexion réussie',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Connexion réussie'),
                new OA\Property(
                    property: 'user',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                        new OA\Property(property: 'email', type: 'string', example: 'john@example.com')
                    ],
                    type: 'object'
                ),
                new OA\Property(property: 'token', type: 'string', example: '2|xyz789abcdef')
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 422,
        description: 'Identifiants incorrects',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'The provided credentials are incorrect.'),
                new OA\Property(property: 'errors', type: 'object')
            ],
            type: 'object'
        )
    )]
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants sont incorrects.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    #[OA\Post(
        path: '/logout',
        summary: 'Déconnexion d\'un utilisateur',
        description: 'Révoque le token d\'authentification actuel',
        security: [['bearerAuth' => []]],
        tags: ['Authentification']
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
        description: 'Toujours "application/json"',
        required: true,
        schema: new OA\Schema(type: 'string', example: 'application/json')
    )]
    #[OA\Response(
        response: 200,
        description: 'Déconnexion réussie',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Déconnexion réussie')
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
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie',
        ], 200);
    }
}
