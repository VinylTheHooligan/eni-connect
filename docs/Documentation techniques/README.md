# Documentation technique – eni-connect

## C'est quoi eni-connect ?

**eni-connect** est une application web qui permet de gérer des sorties (événements, activités) au sein d'une organisation. Les utilisateurs peuvent consulter les sorties disponibles, s'y inscrire ou se désinscrire. Les organisateurs peuvent créer et publier leurs sorties. Les administrateurs, eux, gèrent les utilisateurs, les campus et les villes.

Techniquement, c'est un **monolithe Symfony 7.4** qui tourne sur PHP 8.2 minimum. Pas de front-end séparé : les pages sont générées côté serveur avec Twig, quelques interactions dynamiques étant assurées par Stimulus (un framework JS léger).

**Stack en résumé :**
- Backend : Symfony 7.4 + Doctrine ORM 3.6
- Frontend : Twig + Stimulus + Asset Mapper
- Base de données : MySQL en local, Postgres possible via Docker
- Outils : Docker Compose, PHPUnit, Zenstruck Foundry, Monolog, Symfony Mailer

---

## Architecture en quelques mots

L'application suit l'organisation classique de Symfony, découpée en quatre grandes couches :

- **Présentation** – les contrôleurs HTTP (`src/Controller/`), les templates Twig (`templates/`) et les contrôleurs Stimulus (`assets/`)
- **Métier** – les services (`src/Services/`) et les formulaires (`src/Form/`)
- **Domaine** – les entités Doctrine (`src/Entity/`) et leurs repositories (`src/Repository/`)
- **Infrastructure** – la sécurité (`src/Security/`), la configuration (`config/`) et le point d'entrée HTTP (`public/index.php`)

Ce qui compte à retenir : les contrôleurs restent légers, toute la logique métier vit dans les services, et les accès à la base passent toujours par les repositories.

---

## Ce que l'appli sait faire

- Authentification complète avec gestion de profil et reset de mot de passe
- Consultation des sorties avec filtres (campus, dates, participation…)
- Inscription et désinscription aux sorties
- Gestion du cycle de vie d'une sortie côté organisateur (création → publication → annulation)
- Back-office admin : gestion des utilisateurs (y compris import CSV), des campus et des villes
- Quelques endpoints JSON pour exposer les sorties et les lieux

---

## Table des matières

| Fichier | Contenu |
|---|---|
| `architecture.md` | Découpage en couches, organisation des dossiers, flux d'une requête HTTP |
| `securite.md` | Configuration Security, rôles, voter métier, reset de mot de passe |
| `donnees-et-persistance.md` | Entités, relations, logique d'état des sorties, repositories, migrations |
| `fonctionnalites-metier.md` | Cas d'usage détaillés (utilisateur, organisateur, admin), flux fonctionnels |
| `api-et-integration.md` | Endpoints JSON, formats de réponse, pistes d'extension |
| `configuration-et-deploiement.md` | Variables d'environnement, configuration des packages, installation, déploiement |
| `qualite-et-tests.md` | Tests PHPUnit, fixtures, conventions, checklist avant merge |
