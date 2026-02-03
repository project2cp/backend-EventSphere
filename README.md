# backend

# Backend – Plateforme Événement 

[![Laravel](https://img.shields.io/badge/Laravel-11-red)](https://laravel.com/)
[![PHP](https://img.shields.io/badge/PHP-8.2-blue)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8-green)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-MIT-blue)](LICENSE)

Backend de la plateforme de gestion d'événements, développé avec Laravel 11.  
Ce projet gère tous les aspects liés aux événements, utilisateurs, organisateurs, paiements et notifications.

# Fonctionnalités principales
- Authentification et gestion des rôles (Admin, Organisateur, Utilisateur)  
- CRUD complet pour les événements  
- Gestion des tickets et paiement sécurisé avec Stripe  
- Notifications et messagerie interne  
- Export des données (PDF, CSV, Excel)  
- Intégration avec API externes : Zoom, YouTube Live

# Technologies utilisées
- Backend : Laravel 11  
- Base de données : MySQL   
- API : RESTful API  
- Authentification : Laravel Sanctum  
- Gestion des rôles : spatie/laravel-permission  
- Paiements : Stripe API

# Structure du projet
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   └── Models/
├── config/
├── database/
│   ├── migrations/
│   └── seeders/
├── routes/
│   └── api.php
├── tests/
└── composer.json

# installation
1. Cloner le repo :
```bash
git clone https://github.com/tonpseudo/backend-platforme-event.git

2.Installer les dépendances :
composer install

3.Copier .env.example en .env et configurer la base de données :
cp .env.example .env

4.Générer la clé Laravel :
php artisan key:generate

5.Migrer la base de données :
php artisan migrate --seed

6.Lancer le serveur Laravel :
php artisan serve

| Endpoint                | Méthode | Description                          |
|-------------------------|---------|--------------------------------------|
| /api/register           | POST    | Inscription utilisateur              |
| /api/login              | POST    | Connexion utilisateur                |
| /api/users              | GET     | Liste des utilisateurs (Admin)       |
| /api/events             | GET     | Liste des événements                 |
| /api/events             | POST    | Créer un événement (Organisateur)   |
| /api/events/{id}        | PUT     | Modifier un événement                |
| /api/events/{id}        | DELETE  | Supprimer un événement               |
| /api/tickets            | POST    | Achat d’un ticket                    |
| /api/notifications      | GET     | Liste des notifications              |

# Example de reponse json
```json
{
    "id": 1,
    "title": "Hackathon React",
    "description": "Un événement pour développeurs React",
    "start_date": "2026-02-10",
    "end_date": "2026-02-12",
    "organizer_id": 3
}



