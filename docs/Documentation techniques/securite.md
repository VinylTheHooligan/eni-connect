# Sécurité et gestion des accès

## Vue d'ensemble

La sécurité d'eni-connect repose sur le composant Security de Symfony, configuré dans `config/packages/security.yaml`. Voici les mécanismes en jeu :

- Un modèle utilisateur stocké en base de données, avec un provider et un checker personnalisés
- Une hiérarchie de rôles (admin > organisateur > participant > utilisateur)
- Des règles d'`access_control` qui protègent les routes par rôle
- Un voter métier (`OutingManagerVoter`) pour les règles d'autorisation plus fines sur les sorties
- Un handler qui transforme les refus d'accès en messages lisibles pour l'utilisateur
- Un système de reset de mot de passe via `symfonycasts/reset-password-bundle`

---

## Configuration Symfony Security

Le fichier `config/packages/security.yaml` est le point central. Voici ce qu'il définit.

**Provider** : `app_user_provider` utilise `App\Security\UserProvider` pour charger les utilisateurs depuis la base de données, en acceptant soit l'email soit le nom d'utilisateur comme identifiant.

**Firewall `main`** : couvre toute l'application. Il configure le formulaire de login (avec protection CSRF), la gestion de session et branche le `UserChecker` pour vérifier l'activation des comptes.

**Password hashers** : Symfony choisit automatiquement l'algorithme de hachage recommandé pour les classes qui implémentent `PasswordAuthenticatedUserInterface` (dont `User`).

---

## L'entité User et ses composants de sécurité

### L'entité `User` (`src/Entity/User.php`)

`User` implémente les interfaces Symfony `UserInterface` et `PasswordAuthenticatedUserInterface`. Elle porte :
- Les informations d'identité : `lastName`, `firstName`, `username`, `email`, `phoneNumber`
- Les informations de sécurité : `passwordHash`, `plainPassword` (champ transitoire, jamais persisté), `roles`, `isActive`
- Le profil : `profilePicture` et une relation vers `Campus`

### `UserProvider` (`src/Security/UserProvider.php`)

C'est lui que Symfony appelle pour charger un utilisateur pendant la phase d'authentification. Il sait chercher un `User` par email ou par username.

### `UserChecker` (`src/Security/UserChecker.php`)

Après avoir chargé l'utilisateur, Symfony passe par le checker. Ici, il vérifie que `isActive === true`. Si le compte est désactivé, la connexion est refusée avec un message approprié.

---

## Authentification : login et logout

### Le login

L'utilisateur soumet son formulaire sur `/login`. Le firewall `main` prend en charge l'authentification :

1. `UserProvider` charge l'utilisateur depuis la base
2. `UserChecker` vérifie que le compte est actif
3. Si tout est bon, Symfony crée la session et redirige vers la liste des sorties (route par défaut)

La protection CSRF est active sur ce formulaire.

### Le logout

La route `/logout` est gérée directement par le firewall (la méthode du contrôleur est vide, c'est Symfony qui fait le travail). Il invalide la session et redirige vers le login.

---

## Rôles et hiérarchie

L'application définit quatre rôles organisés hiérarchiquement :

```
ROLE_ADMIN > ROLE_ORGANIZER > ROLE_PARTICIPANT > ROLE_USER
```

Concrètement : un administrateur hérite de tous les droits en dessous de lui. Un organisateur peut faire tout ce qu'un participant ou un simple utilisateur peut faire.

### Ce que protègent les access_control

- `/admin/*` → réservé aux `ROLE_ADMIN`
- `/sorties/gestion/*` → réservé aux `ROLE_ORGANIZER` (et donc aux admins aussi)
- Les pages utilisateurs (profil, sorties) → accessible à partir de `ROLE_USER`, ce qui inclut tout le monde connecté

---

## Le voter métier : OutingManagerVoter

Le voter (`src/Security/Voter/OutingManagerVoter.php`) est là pour les règles d'autorisation qui dépendent du contexte métier et pas seulement du rôle. Par exemple : peut-on modifier *cette* sortie en particulier ?

Il gère cinq attributs : `CREATE`, `EDIT`, `PUBLISH`, `CANCEL`, `DELETE`.

**CREATE** : réservé aux `ROLE_ORGANIZER`.

**EDIT, PUBLISH, CANCEL, DELETE** : l'utilisateur doit être soit l'organisateur de la sortie, soit un admin. Mais ça ne s'arrête pas là – l'état de la sortie entre aussi en compte :

| Action | Condition supplémentaire |
|---|---|
| EDIT | La sortie est en cours de création ou encore ouverte |
| PUBLISH | La sortie est en création ET la deadline d'inscription n'est pas passée |
| CANCEL | La sortie n'a pas encore commencé et n'est pas déjà annulée |
| DELETE | La sortie est en création et n'a pas démarré |

---

## Gestion des accès refusés

Quand le voter refuse une action, `AccessDeniedHandler` (`src/Security/AccessDeniedHandler.php`) intercepte l'exception. Il traduit la situation en un message flash lisible par l'utilisateur (par exemple : *« Vous ne pouvez plus modifier cette sortie car elle a déjà commencé. »*) et redirige vers la liste des sorties. L'utilisateur comprend ce qui s'est passé sans tomber sur une page d'erreur générique.

---

## Reset de mot de passe

Le bundle `symfonycasts/reset-password-bundle` gère ce flux. La configuration est dans `config/packages/reset_password.yaml`, le contrôleur dans `src/Controller/ResetPasswordController.php`.

Le déroulé :

1. L'utilisateur remplit le formulaire de demande de reset
2. Une entrée `ResetPasswordRequest` est créée en base (token + date d'expiration)
3. Un email est envoyé via Symfony Mailer avec le lien de réinitialisation
4. L'utilisateur clique sur le lien, saisit son nouveau mot de passe
5. Le mot de passe est mis à jour, le token invalidé

L'entité `ResetPasswordRequest` (`src/Entity/ResetPasswordRequest.php`) stocke les tokens en attente.
