# Fonctionnalités métier et flux

## Ce que fait l'application

eni-connect couvre quatre grandes zones fonctionnelles :

1. **Authentification et profil** – se connecter, gérer son compte, réinitialiser son mot de passe
2. **Sorties côté utilisateur** – consulter les sorties, s'inscrire, se désinscrire
3. **Sorties côté organisateur** – créer, modifier, publier, annuler, supprimer une sortie
4. **Administration** – gérer les utilisateurs, les campus, les villes, importer des utilisateurs en masse

---

## Authentification et profil utilisateur

### Se connecter

- **Contrôleur** : `src/Controller/SecurityController.php`
- **Routes** : `/login` et `/logout`
- **Vue** : `templates/security/login.html.twig`

Le formulaire de login est géré par le firewall Symfony avec protection CSRF. Si le compte est inactif, le `UserChecker` bloque la connexion avant même de vérifier le mot de passe.

### Gérer son profil

- **Contrôleur** : `src/Controller/UserController.php`
- **Routes** : `/profil` (édition) et `/profil/{id}` (consultation d'un autre profil)
- **Formulaire** : `src/Form/UserType.php`

Depuis son profil, un utilisateur peut mettre à jour ses informations personnelles, changer son mot de passe et uploader une photo de profil (stockée dans `public/uploads/profile_pictures`).

---

## Sorties côté utilisateur

### Lister et filtrer les sorties

- **Contrôleur** : `src/Controller/OutingController.php`
- **Route** : `/sorties`
- **Vue** : `templates/outing/index.html.twig`

La page de liste propose plusieurs filtres cumulables :

- Par campus
- Par texte libre (titre de la sortie)
- Par période (date de début / fin)
- Par rapport à l'utilisateur connecté : sorties qu'il organise, auxquelles il est inscrit, auxquelles il n'est pas inscrit
- Inclure ou non les sorties passées

Tout ça passe par la méthode `search()` de `OutingRepository`.

### S'inscrire ou se désinscrire

- **Route détail** : `/sorties/{id}`
- **Route inscription** : `/sorties/{id}/inscription`
- **Route désinscription** : `/sorties/{id}/desistement`

La logique d'inscription et de désinscription est dans `OutingControllerService`.

**Pour s'inscrire**, il faut que :
- La sortie soit ouverte (`isOpen()`)
- La date limite d'inscription ne soit pas dépassée
- Le nombre maximum de participants ne soit pas atteint
- L'utilisateur ne soit pas déjà inscrit

**Pour se désinscrire**, il faut que :
- La sortie n'ait pas encore commencé
- Le statut de la sortie le permette (pas annulée, pas terminée)

---

## Sorties côté organisateur

### Créer une sortie

- **Contrôleur** : `src/Controller/OutingManagerController.php`
- **Route** : `/sorties/gestion/creer`
- **Formulaire** : `src/Form/OutingType.php`
- **Service** : `OutingManagementService`

Le service `OutingManagementService` orchestre le tout avec quatre méthodes :
- `initializeOuting()` – initialise une nouvelle sortie avec les valeurs par défaut
- `publish()` – gère le changement de statut lors de la publication
- `autoRegisterOrganizer()` – inscrit automatiquement l'organisateur à sa propre sortie
- `save()` – persiste la sortie en base

### Modifier, publier, annuler, supprimer

Ces actions passent toutes par `OutingManagerController` et sont soumises au `OutingManagerVoter` (voir le document sécurité pour les règles détaillées). En résumé : seul l'organisateur ou un admin peut agir, et l'état de la sortie détermine ce qui est possible.

| Action | Route | Attribut voter |
|---|---|---|
| Modifier | `/sorties/gestion/{id}/modifier` | `EDIT` |
| Publier | route `manage_publish` | `PUBLISH` |
| Annuler | route `manage_cancel` | `CANCEL` |
| Supprimer | route `manage_delete` | `DELETE` |

### Gérer les lieux

Un organisateur peut créer un nouveau lieu depuis `/sorties/gestion/lieu/creer` (formulaire `PlaceType`). La sélection du lieu lors de la création d'une sortie est dynamique : quand l'organisateur choisit un campus dans le formulaire, un contrôleur Stimulus appelle l'API `/places/by-campus/{id}` pour charger la liste des lieux correspondants.

---

## Administration

### Gérer les utilisateurs

- **Contrôleur** : `src/Controller/Admin/UserAdminController.php`
- **Service** : `Admin\UserManager`
- **Route principale** : `/admin/utilisateurs`

L'admin peut lister les utilisateurs avec des filtres (nom, email, campus, rôle…), créer ou modifier un compte, activer ou désactiver un compte (ce qui impacte directement la connexion via `UserChecker`), et effectuer des actions groupées (désactivation en masse, suppression conditionnelle).

### Importer des utilisateurs via CSV

C'est un flux en deux étapes, pour éviter les mauvaises surprises.

1. L'admin upload un fichier CSV sur `/admin/utilisateurs/import`
2. `UserManager` parse le fichier et valide les données (format, champs obligatoires, doublons)
3. Une page de confirmation liste les utilisateurs qui vont être créés
4. Après validation, une requête vers `/admin/utilisateurs/import/confirmer` déclenche la création effective en base
5. Un résumé de l'import (succès, erreurs éventuelles) est affiché

L'idée de la confirmation intermédiaire est importante : elle permet à l'admin de vérifier avant de s'engager, surtout sur des imports potentiellement volumineux.

### Gérer les campus et les villes

Deux sections du back-office avec des CRUD classiques :

- **Campus** : `/admin/campus` – `CampusAdminController` + `CampusManager` + `CampusType`
- **Villes** : `/admin/villes` – `CityAdminController` + `CityManager` + `CityType`

Les routes suivent le même pattern : liste, ajout, modification, suppression.

---

## Deux flux en détail

### Créer et publier une sortie

Voici exactement ce qui se passe quand un organisateur crée une sortie :

1. Il ouvre `/sorties/gestion/creer` → `OutingManagerController` affiche le formulaire `OutingType`
2. Il remplit et soumet le formulaire
3. Le contrôleur reçoit la requête POST et appelle `OutingManagementService` dans l'ordre :
   - `initializeOuting()` → valeurs par défaut
   - `publish()` → si l'organisateur publie directement
   - `autoRegisterOrganizer()` → inscription automatique
   - `save()` → persistance via `OutingRepository`
4. Le contrôleur redirige vers la liste ou vers la page de détail de la sortie créée

Ce flux illustre bien la séparation des responsabilités : le contrôleur gère le HTTP, le service gère les règles métier, le repository gère la persistance.

### Importer des utilisateurs en CSV

1. L'admin va sur `/admin/utilisateurs/import` → formulaire `UserImportType`
2. Il uploade son fichier CSV
3. `UserAdminController` transmet le fichier à `UserManager` qui l'analyse et valide les données
4. Une page de confirmation affiche ce qui va être créé
5. L'admin confirme → nouvelle requête vers `/admin/utilisateurs/import/confirmer`
6. `UserManager` crée les utilisateurs en base et renvoie un bilan
7. L'admin voit un résumé : combien de créations réussies, quelles erreurs éventuellement

Ce flux montre pourquoi il est important de sortir les traitements lourds des contrôleurs : le parsing, la validation et la création en masse sont tous dans `UserManager`, pas dans le contrôleur.
