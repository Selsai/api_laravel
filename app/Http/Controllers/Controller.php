<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'API Laravel Books',
    description: 'Documentation complète de l\'API de gestion de livres avec authentification par token (Laravel Sanctum)'
)]
#[OA\Server(
    url: 'http://localhost:8000/api/v1',
    description: 'Serveur de développement local'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Entrez votre token d\'authentification au format: Bearer {token}'
)]
#[OA\Tag(
    name: 'Authentification',
    description: 'Endpoints pour l\'inscription, la connexion et la déconnexion des utilisateurs'
)]
#[OA\Tag(
    name: 'Livres',
    description: 'Endpoints CRUD pour la gestion des livres'
)]
abstract class Controller
{
    //
}