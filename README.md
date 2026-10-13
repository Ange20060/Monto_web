# Monto Web

API Laravel pour une plateforme de covoiturage / réservation de trajets.

Le projet est conçu pour gérer l’inscription d’utilisateurs, les profils chauffeur et passager, la création de trajets, les réservations, les paiements, les wallets, les transactions et les évaluations.

## 1. Vue d’ensemble

Monto Web est une application backend basée sur Laravel 12, orientée API REST avec Sanctum pour l’authentification. Elle couvre les flux de base d’une plateforme de transport partagé :

- inscription / connexion / déconnexion
- profil chauffeur
- profil passager
- gestion du véhicule
- publication de trajets
- recherche et filtrage des trajets
- réservation d’un trajet
- validation / refus / annulation des réservations
- gestion du paiement et du solde
- historique des transactions
- notation d’un chauffeur après trajet

## 2. Stack technique

- PHP 8.2
- Laravel 12
- Sanctum
- SQLite pour le développement local (par défaut)
- PHPUnit pour les tests

## 3. Fonctionnalités déjà implémentées

### Authentification
- inscription (`POST /api/register`)
- connexion (`POST /api/login`)
- récupération du profil connecté (`GET /api/me`)
- déconnexion propre avec suppression des tokens (`POST /api/logout`)

### Gestion utilisateur
- création automatique d’un profil passager lors de l’inscription
- création automatique d’un wallet utilisateur

### Chauffeur
- création du profil chauffeur
- consultation du profil
- mise à jour du profil
- mise en ligne / hors ligne
- consultation du statut du chauffeur

### Véhicule
- liste des véhicules du chauffeur
- ajout d’un véhicule
- modification d’un véhicule
- suppression d’un véhicule
- validation de plaque, couleur, année, nombre de places

### Trajets
- création d’un trajet
- consultation d’un trajet
- liste des trajets
- historique des trajets d’un chauffeur
- recherche avec filtres :
  - départ
  - destination
  - date
  - places disponibles
  - prix maximum
  - statut
- mise à jour / suppression d’un trajet selon le propriétaire

### Réservations
- création d’une réservation pour un trajet disponible
- vérification des places restantes
- blocage de réservation si trajet non ouvert
- consultation des réservation
- mise à jour du statut : pending, accepted, confirmed, cancelled
- annulation d’une réservation en attente
- contrôle d’accès par Policy (`BookingPolicy`)

### Paiement et wallet
- historique des paiements
- paiement d’une réservation confirmée
- validation du solde avant débit
- création automatique d’une transaction de type `payment`
- stockage du montant, méthode de paiement et statut

### Évaluation
- ajout d’une note à une réservation
- calcul automatique du rating chauffeur
- consultation des notes d’un chauffeur

## 4. Structure du projet

```text
app/
├── Http/
│   └── Controllers/
│       └── Api/
│           ├── AuthController.php
│           ├── BookingController.php
│           ├── DriverController.php
│           ├── PaymentController.php
│           ├── RatingController.php
│           ├── TripController.php
│           └── VehicleController.php
├── Models/
│   ├── Booking.php
│   ├── Driver.php
│   ├── Passenger.php
│   ├── Payment.php
│   ├── Rating.php
│   ├── Transaction.php
│   ├── Trip.php
│   ├── User.php
│   ├── Vehicle.php
│   └── Wallet.php
├── Policies/
│   └── BookingPolicy.php
├── Providers/
│   └── AuthServiceProvider.php
├── Services/
│   └── (à compléter selon l’évolution du projet)
├── Repositories/
│   └── (à compléter selon l’évolution du projet)
├── ...
config/
database/
├── factories/
├── migrations/
├── seeders/
public/
routes/
├── api.php
├── web.php
storage/
tests/
├── Feature/
│   ├── BookingApiTest.php
│   ├── PaymentApiTest.php
│   └── ExampleTest.php
└── TestCase.php
```

## 5. Routes API

### Authentification

| Méthode | Endpoint | Description |
|---|---|---|
| POST | `/api/register` | Inscription |
| POST | `/api/login` | Connexion |
| POST | `/api/logout` | Déconnexion |
| GET | `/api/me` | Profil de l’utilisateur courant |

### Driver

| Méthode | Endpoint | Description |
|---|---|---|
| GET | `/api/driver/profile` | Profil chauffeur |
| POST | `/api/driver/profile` | Création du profil chauffeur |
| PUT | `/api/driver/profile` | Mise à jour du profil chauffeur |
| POST | `/api/driver/online` | Mettre le chauffeur en ligne |
| POST | `/api/driver/offline` | Mettre le chauffeur hors ligne |
| GET | `/api/driver/status` | Statut du chauffeur |

### Véhicules

| Méthode | Endpoint | Description |
|---|---|---|
| GET | `/api/driver/vehicles` | Liste des véhicules |
| POST | `/api/driver/vehicle` | Ajouter un véhicule |
| PUT | `/api/driver/vehicle/{id}` | Modifier un véhicule |
| DELETE | `/api/driver/vehicle/{id}` | Supprimer un véhicule |

### Trajets

| Méthode | Endpoint | Description |
|---|---|---|
| GET | `/api/trips` | Recherche / liste des trajets |
| GET | `/api/trips/{id}` | Détails d’un trajet |
| POST | `/api/driver/trip` | Créer un trajet |
| GET | `/api/driver/trips` | Historique des trajets du chauffeur |
| PUT | `/api/trips/{id}` | Modifier un trajet |
| DELETE | `/api/trips/{id}` | Supprimer un trajet |

### Réservations

| Méthode | Endpoint | Description |
|---|---|---|
| GET | `/api/bookings` | Liste des réservations du passager |
| POST | `/api/bookings` | Réserver un trajet |
| GET | `/api/bookings/{id}` | Détail d’une réservation |
| PUT | `/api/bookings/{id}` | Mettre à jour le statut |
| DELETE | `/api/bookings/{id}` | Annuler une réservation |

### Paiements

| Méthode | Endpoint | Description |
|---|---|---|
| GET | `/api/payments/history` | Historique de paiement / wallet |
| POST | `/api/payments` | Effectuer un paiement |

### Évaluations

| Méthode | Endpoint | Description |
|---|---|---|
| POST | `/api/ratings` | Noter un trajet / chauffeur |
| GET | `/api/ratings/driver/{driverId}` | Notes d’un chauffeur |

## 6. Modèles de données

### User
- id
- name
- email
- phone
- password
- email_verified_at
- timestamps

### Driver
- user_id
- license_number
- license_expiration
- phone
- city
- verified
- available
- is_online
- rating

### Passenger
- user_id

### Vehicle
- driver_id
- brand
- model
- color
- plate_number
- year
- seats
- verified

### Trip
- driver_id
- departure
- destination
- departure_lat
- departure_lng
- destination_lat
- destination_lng
- price
- available_seats
- departure_time
- status

### Booking
- trip_id
- passenger_id
- seats
- status

### Payment
- booking_id
- amount
- method
- status

### Wallet
- user_id
- balance

### Transaction
- wallet_id
- amount
- type

### Rating
- booking_id
- stars
- comment

## 7. Sécurité

Les règles de sécurité mises en place ou à renforcer incluent :

- authentification par token Sanctum
- protection des endpoints avec `auth:sanctum`
- contrôle d’accès sur les réservations via `BookingPolicy`
- validation des entrées côté serveur via Laravel Validation
- contrôle de propriété des ressources (un chauffeur ne modifie que ses véhicules / trajets)
- restriction du paiement à la réservation du compte connecté
- vérification du solde avant débit

À compléter / renforcer :
- politiques de sécurité explicites pour `Vehicle`, `Trip`, `Payment`, `Rating`
- règles métier strictes sur les transitions de statut
- validations supplémentaires sur les montants et les états de paiement
- audit plus complet des permissions selon le rôle

## 8. Installation

### Prérequis
- PHP >= 8.2
- Composer
- Node.js / npm (si le front est ajouté ultérieurement)
- Git

### Installation locale

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

### Vérification du projet

```bash
php artisan test
```

### Test ciblé

```bash
php artisan test --filter='BookingApiTest|PaymentApiTest'
```

## 9. Exemple d’utilisation

### Inscription

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "97000000",
    "password": "secret123"
  }'
```

### Connexion

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "secret123"
  }'
```

### Utilisation du token

```bash
curl -X GET http://localhost:8000/api/me \
  -H "Authorization: Bearer <token>"
```

## 10. Tests

Le projet contient des tests d’intégration pour les fonctionnalités critiques :

- `BookingApiTest`
- `PaymentApiTest`

Exécution :

```bash
php artisan test --filter='BookingApiTest|PaymentApiTest'
```

## 11. État du projet

Le projet est actuellement en phase de développement backend API pour une plateforme de covoiturage. Les fonctionnalités de base sont en place et testées pour les flux principaux, mais certaines parties restent à finaliser ou à durcir côté sécurité et business logic.

### À compléter / améliorer
- politiques complètes des modules non encore sécurisés
- audit des règles de validation métier
- historique complet des transactions
- gestion plus avancée des rôles et permissions
- séparation claire des services métier
- extension éventuellement vers interface web ou mobile

## 12. Licence

Ce projet est fourni sous licence MIT.

## 13. Auteur / contribution

Projet développé dans le cadre de la plateforme Monto Web.

---

Pour toute évolution, il est recommandé de continuer le développement en ajoutant :
- un module de notifications,
- un système de validation de conducteur,
- un moteur de recherche géographique plus avancé,
- un contrôle strict des statuts de paiement et de réservation,
- une couverture de tests plus complète sur les modules `Vehicle`, `Trip`, `Payment`, `Rating`.
