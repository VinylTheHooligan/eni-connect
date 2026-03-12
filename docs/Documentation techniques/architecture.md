# Architecture applicative

## Vue d'ensemble

**eni-connect** est une application Symfony classique, monolithique. Pas de micro-services, pas d'API REST séparée : tout est dans le même projet. Les pages HTML sont générées côté serveur (avec Twig), et quelques interactions dynamiques utilisent Stimulus. La base de données est interrogée via Doctrine ORM.

De façon schématique, une requête suit ce chemin :

```
Navigateur → Contrôleur Symfony → Service métier → Repository Doctrine → Base de données
                                                                        ↓
                                                              Réponse HTML ou JSON
```

---

## Les quatre couches

### 1. La couche présentation

C'est tout ce qui est en contact avec l'extérieur : les contrôleurs HTTP, les templates Twig et le JavaScript.

**Contrôleurs** (dans `src/Controller/`) :

- `MainController` – page d'accueil, redirige selon si l'utilisateur est connecté ou non
- `SecurityController` – login / logout
- `UserController` – profil utilisateur
- `OutingController` – liste des sorties, détail, inscription, désinscription
- `OutingManagerController` – tout ce qui concerne la gestion d'une sortie côté organisateur (création, modification, publication, annulation, suppression)
- `Admin\AdminDashboardController`, `UserAdminController`, `CampusAdminController`, `CityAdminController` – le back-office d'administration
- `Api\APIOutingController`, `Api\APIPlaceController` – les deux endpoints JSON

**Templates Twig** (dans `templates/`) :

- `base.html.twig` – le layout global (header, footer, inclusion des assets)
- `templates/outing/` – tout ce qui concerne les sorties (liste, détail, création, édition, annulation…)
- `templates/user/` – les pages de profil
- `templates/admin/` – le back-office
- `templates/security/` et `templates/reset_password/` – authentification et reset de mot de passe

**JavaScript** (dans `assets/`) :

Le JS est minimal et organisé autour de Stimulus. Les contrôleurs notables sont `leaflet_map_controller.js` (pour afficher les lieux sur une carte) et `csrf_protection_controller.js`. Le point d'entrée est `assets/bootstrap.js`.

---

### 2. La couche domaine

C'est le cœur de l'application : les entités Doctrine et leurs repositories.

**Entités** (dans `src/Entity/`) : `User`, `Outing`, `Campus`, `Place`, `City`, `Registration`, `ResetPasswordRequest`.

**Repositories** (dans `src/Repository/`) : un repository par entité, qui encapsule toutes les requêtes SQL nécessaires. Le plus riche est `OutingRepository`, qui gère les recherches et filtres complexes sur les sorties.

---

### 3. La couche service (métier)

Les services portent la logique métier. Les contrôleurs leur délèguent tout ce qui dépasse la simple gestion de la requête HTTP.

**Services** (dans `src/Services/`) :

- `OutingControllerService` – gère les inscriptions et désinscriptions
- `OutingManagementService` – gère le cycle de vie d'une sortie (initialisation, publication, inscription automatique de l'organisateur, sauvegarde)
- `OutingStatusUpdater` – calcule et met à jour le statut des sorties
- `Admin\UserManager`, `Admin\CampusManager`, `Admin\CityManager` – logique d'administration

**Formulaires** (dans `src/Form/`) : `UserType`, `OutingType`, `CancelOutingType`, `PlaceType`, `CampusType`, `CityType`, `UserImportType`, `ResetPasswordRequestFormType`, `ChangePasswordFormType`.

---

### 4. La couche infrastructure

Tout ce qui fait tourner l'application sans être de la logique métier : sécurité, configuration, point d'entrée.

- `src/Security/` – `UserProvider`, `UserChecker`, `AccessDeniedHandler`, `Voter/OutingManagerVoter`
- `public/index.php` – le front-controller Symfony (point d'entrée unique de toutes les requêtes HTTP)
- `src/Kernel.php` – le kernel Symfony
- `config/` – toute la configuration des packages, sécurité, doctrine, twig…

---

## Ce qui se passe lors d'une requête typique

Pour rendre les choses concrètes, voici comment se déroule une inscription à une sortie.

1. L'utilisateur clique sur « S'inscrire » sur la page d'une sortie → son navigateur envoie une requête vers `/sorties/{id}/inscription`.
2. La requête arrive sur `public/index.php` (le front-controller) puis est transmise au kernel Symfony.
3. Le kernel initialise le contexte et passe la requête au routeur, qui identifie la méthode `register()` de `OutingController`.
4. Le contrôleur récupère l'utilisateur connecté et la sortie ciblée, puis appelle `OutingControllerService::register()`.
5. Le service vérifie les règles métier (sortie ouverte ? deadline non dépassée ? places disponibles ?) via `OutingRepository` et `RegistrationRepository`, puis crée l'inscription en base.
6. Le contrôleur reçoit le résultat et renvoie soit une redirection, soit le rendu du template de détail de la sortie.

Ce qui est important ici : le contrôleur ne contient aucune règle métier, il coordonne juste. Toute la logique vit dans le service.

---

## Organisation des dossiers

```
src/
  Controller/   → contrôleurs HTTP (présentation)
  Services/     → logique métier
  Entity/       → modèle de données (entités Doctrine)
  Repository/   → accès aux données
  Security/     → sécurité (providers, voters, handlers)
  Form/         → formulaires Symfony

templates/      → vues Twig
assets/         → JavaScript (Stimulus) et autres assets
config/         → configuration applicative
public/         → front-controller et assets publics
```

---

## Routes principales à connaître

| Route | Contrôleur | Description |
|---|---|---|
| `/` | `MainController::index()` | Redirige selon l'état de connexion |
| `/login`, `/logout` | `SecurityController` | Authentification |
| `/sorties` | `OutingController` | Liste et détail des sorties |
| `/sorties/gestion/*` | `OutingManagerController` | Gestion des sorties (organisateur) |
| `/admin/*` | Contrôleurs `Admin\*` | Back-office |
| `/api/sorties` | `Api\APIOutingController` | Endpoint JSON des sorties |
| `/places/by-campus/{id}` | `Api\APIPlaceController` | Endpoint JSON des lieux |
