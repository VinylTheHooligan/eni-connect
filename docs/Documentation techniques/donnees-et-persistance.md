# Données et persistance

## Doctrine ORM : la configuration de base

L'application utilise **Doctrine ORM 3.6**. La configuration se trouve dans `config/packages/doctrine.yaml` :

- La connexion à la base est définie via `DATABASE_URL` (MySQL en local par défaut)
- Le mapping des entités se fait par attributs PHP sur les classes du namespace `App\Entity`
- Les migrations sont dans le dossier `migrations/`, configurées dans `doctrine_migrations.yaml`

---

## Les entités principales

### User

Fichier : `src/Entity/User.php`

Représente un utilisateur de la plateforme, qu'il soit simple participant, organisateur ou admin. Ses champs principaux :

- **Identité** : `lastName`, `firstName`, `username`, `email`, `phoneNumber`
- **Sécurité** : `passwordHash`, `plainPassword` (transitoire, jamais en base), `roles`, `isActive`
- **Profil** : `profilePicture`
- **Relations** : appartient à un `Campus` (ManyToOne), peut organiser plusieurs `Outing` (OneToMany), peut participer à plusieurs `Outing` via `Registration` (OneToMany)

### Outing

Fichier : `src/Entity/Outing.php`

Représente une sortie. C'est l'entité centrale de l'application.

- **Champs** : `name`, `startDateTime`, `duration`, `registrationDeadline`, `maxRegistrations`, `eventInfo` (description), `status`, `createdDateTime`, `cancelReason`
- **Relations** : un organisateur (`User`, ManyToOne), un campus (`Campus`, ManyToOne), un lieu (`Place`, ManyToOne), plusieurs inscriptions (`Registration`, OneToMany)

L'entité porte aussi beaucoup de logique métier — voir la section dédiée plus bas.

### Campus

Fichier : `src/Entity/Campus.php`

Un campus est un site géographique de l'organisation. Il sert de point de rattachement pour les utilisateurs, les lieux et les sorties. Son seul champ de données est `name`, mais il est lié à `User`, `Outing` et `Place` en OneToMany.

### City

Fichier : `src/Entity/City.php`

Une ville, avec `name` et `postalCode`. Elle regroupe plusieurs `Place`.

### Place

Fichier : `src/Entity/Place.php`

Un lieu précis où peut se dérouler une sortie.

- **Champs** : `name`, `street`, `latitude`, `longitude`
- **Relations** : appartient à une `City` et à un `Campus`, peut accueillir plusieurs `Outing`

### Registration

Fichier : `src/Entity/Registration.php`

L'inscription d'un participant à une sortie. C'est une entité de liaison entre `User` et `Outing`, avec un champ `registrationDate`. Une contrainte garantit qu'un même utilisateur ne peut être inscrit qu'une seule fois à une même sortie.

### ResetPasswordRequest

Fichier : `src/Entity/ResetPasswordRequest.php`

Utilisée par le bundle de reset de mot de passe. Stocke le token de réinitialisation, l'utilisateur concerné et la date d'expiration.

---

## Relations entre les entités

En résumé :

- Un `User` appartient à un `Campus`. Il peut organiser plusieurs sorties et s'inscrire à d'autres via `Registration`.
- Un `Campus` rassemble plusieurs utilisateurs, sorties et lieux.
- Une `City` contient plusieurs lieux (`Place`).
- Un `Place` peut accueillir plusieurs sorties, et appartient à un campus et une ville.
- Une `Outing` se déroule dans un lieu, est organisée par un utilisateur, et regroupe plusieurs `Registration`.

C'est un modèle classique « utilisateurs – événements – inscriptions », enrichi par la notion de campus et de géographie (ville, lieu avec coordonnées GPS).

---

## La logique d'état d'une Outing

`Outing` ne stocke pas juste des données, elle contient aussi la logique qui détermine ce qu'on peut faire avec elle. C'est important à comprendre pour naviguer dans le code.

**Les états possibles** : en création, publiée, ouverte, clôturée, en cours, terminée, annulée, historisée.

**La méthode clé : `getStatus()`**

Elle calcule l'état fonctionnel de la sortie en fonction de plusieurs critères : la date de début, la date limite d'inscription, le nombre de participants inscrits par rapport au maximum, et l'état stocké en base. Ce n'est pas un simple getter — c'est une logique de calcul.

**Les autres méthodes utilitaires** :

- `isPublished()`, `isCancelled()`, `isOpen()` — états booléens de la sortie
- `isRegistrationDeadlinePassed()`, `isMaxRegistrationsReached()`, `isStarted()` — vérifications temporelles et de capacité
- `isRegistered(User $user)`, `getRegistrationFor(User $user)` — concernant un utilisateur spécifique

Ces méthodes sont utilisées partout dans les services et les contrôleurs pour décider quelles actions sont possibles.

---

## Les repositories principaux

### OutingRepository

C'est le plus riche. Sa méthode `search()` accepte de nombreux critères :

- Campus
- Texte libre (recherche dans le nom)
- Période (date de début et de fin)
- Participation : sorties organisées par l'utilisateur, celles où il est inscrit, celles où il ne l'est pas
- Inclusion ou non des sorties passées

C'est elle qui alimente la page de liste des sorties.

### UserRepository

Fournit les méthodes de recherche côté administration, utilisées principalement par `Admin\UserManager`.

### Autres repositories

`CampusRepository`, `CityRepository`, `PlaceRepository`, `RegistrationRepository`, `ResetPasswordRequestRepository` — moins complexes, ils servent aux opérations CRUD standards dans les services et contrôleurs concernés.

---

## Migrations et données de démo

**Migrations Doctrine** : les fichiers de migration sont dans `migrations/`. Deux commandes à connaître :

```bash
# Générer une migration après avoir modifié une entité
php bin/console doctrine:migrations:diff

# Appliquer les migrations en attente
php bin/console doctrine:migrations:migrate
```

**Fixtures** : pour charger des données de démo ou de test. Le projet utilise **Zenstruck Foundry** pour générer des données réalistes facilement.

- Les classes de fixtures sont dans `src/DataFixtures/`
- Les factories (qui fabriquent des entités) sont dans `src/Factory/`
- Le scénario principal est dans `src/Story/AppStory.php`

```bash
php bin/console doctrine:fixtures:load
```

⚠️ Cette commande écrase les données existantes par défaut.
