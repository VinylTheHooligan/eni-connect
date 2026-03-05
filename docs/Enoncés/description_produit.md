## Liste des exigences

Pour rappel, votre objectif est de réaliser au moins les fonctionnalités décrites dans l'itération 1.
Dans ce projet, vous devrez mettre en place du Web Responsive et porter une attention particulière aux notions d'accessibilité.
Soyez également vigilant sur les notions de sécurité.
Une icône indique le niveau de difficulté d’une tâche.

 Bien respecter l’ordre des itérations et de chaque tâche à l’intérieur d’une itération.

| 1

---

### 1. Itération 1

Tâche Catégorie Nom Description

**1 Gestion des utilisateurs — Se connecter**

- [x] En tant qu’utilisateur, je peux me connecter sur la plateforme sortir.com avec un login (adresse mail ou pseudo) et un mot de passe.
Si mon compte est inactif, je ne pourrai plus accéder à aucune URL du site.

Il n’est pas demandé de créer une page "register".
Seul l’administrateur pourra créer des utilisateurs (tâches prévues uniquement dans les itérations 2 et 3).

**2 Gestion des utilisateurs — Se souvenir de moi**

- [x] En tant qu’utilisateur, je peux choisir d’enregistrer mes identifiants sur mon ordinateur pour ne pas avoir à les resaisir.

**3 Gestion des utilisateurs — Gérer son profil**

- [x] En tant qu’utilisateur, je peux gérer mes informations de profil, notamment mon nom, prénom, pseudo, email, mot de passe, et téléphone.
Le pseudo et l’email doivent être uniques entre tous les participants.
Dans cette tâche, il n’est pas demandé de gérer la photo du profil (prévu dans l’itération 2).

**4 Gestion des sorties — Afficher la liste des sorties par campus**

- [x] En tant que participant, je peux lister les sorties publiées sur chaque campus.
Je peux filtrer cette liste suivant différents critères (voir maquette écran).
Le campus sera automatiquement proposé par rapport au rattachement de l’utilisateur connecté.
Les sorties "créées" (pas encore publiées) ne seront visibles que si j’en suis l’organisateur.
Les actions liées à chaque sortie ne sont pas à traiter dans cette tâche.



Tâche Catégorie Nom Description

**5 Gestion des sorties — Afficher le détail d’une sortie**

- [x] La page affiche le détail de la sortie, ainsi que la liste des utilisateurs inscrits à cette sortie.

**6 Gestion des sorties — Afficher le profil des participants**

- [x] Je peux afficher le profil des autres participants.
Cette fonctionnalité est notamment disponible sur la page d’affichage de la liste des sorties (colonne organisateur) et sur la page qui affiche le détail de la sortie (depuis la liste des participants).

**7 Gestion des sorties — Créer une sortie**

- [x] En tant qu’organisateur d’une sortie, je peux créer une nouvelle sortie.
Je pourrai soit l’enregistrer simplement et la laisser en état "En création", soit la publier (passage en état "Ouverte") et la rendre disponible à l’affichage pour les autres utilisateurs.
Voir la maquette d’écran "création d’une sortie (version 1)".

**8 Gestion des sorties — Modifier une sortie**

- [x] En tant qu’organisateur d’une sortie, je peux modifier une sortie si elle n’est pas encore publiée.
Je pourrai soit l’enregistrer simplement et la laisser en état "En création", soit la publier (passage en état "Ouverte") et la rendre disponible à l’affichage pour les autres utilisateurs.
Je pourrai également décider de la supprimer totalement (pas d’historique).
Voir la maquette d’écran "création d’une sortie (version 1)".

**9 Gestion des sorties — Publier une sortie**

- [ ] Depuis la page de la liste des sorties, en tant qu’organisateur d’une sortie, je peux décider de publier une sortie qui est "En création".

**10 Gestion des sorties — S’inscrire**

- [ ] Depuis la page de la liste des sorties, en tant que participant, je peux m’inscrire à une sortie.
Il faut que la sortie ait été publiée (état "Ouverte"), et que la date limite d’inscription ne soit pas dépassée.

1. Itération 1 | 3

Tâche Catégorie Nom Description

**11 Gestion des sorties — Se désister**

- [ ] Depuis la page de la liste des sorties, en tant que participant inscrit à une sortie, je peux me désister tant que la sortie n’a pas débuté.
En cas de désistement, la place devient libre pour un autre participant si la date limite d’inscription n’est pas dépassée.

**12 Gestion des sorties — Annuler une sortie**

- [ ] Depuis la page de la liste des sorties, en tant qu’organisateur d’une sortie, je peux annuler une sortie si celle-ci a été publiée, mais n’est pas encore commencée.
La sortie sera alors marquée comme annulée et sera accompagnée d’un motif d’annulation.

**13 Gestion des sorties — Clôture des inscriptions**

- [ ] Une sortie est clôturée si le nombre maxi de participants prévu ou si la date limite d’inscription sont atteints.

**14 Gestion des sorties — Archiver les sorties**

- [ ] Les sorties réalisées depuis plus d’un mois ne sont pas consultables et doivent passer à l’état "Historisée".



---

### 2. Itération 2

Tâche Catégorie Nom Description

**1 Gestion des utilisateurs — Photo pour le profil**

- [ ] En tant que participant, je peux uploader une photo pour être affichée dans ma page Profil.

**2 Administration — Page pour administrateur**

- [x] En tant qu’administrateur, j’ai accès à une page d’accueil spécifique pour administrer certaines données du site (gérer les utilisateurs, les campus et les villes).

**3 Gestion des utilisateurs — Liste des utilisateurs**

- [ ] En tant qu’administrateur, je peux lister les utilisateurs.

**4 Gestion des utilisateurs — Modifier un utilisateur**

- [ ] En tant qu’administrateur, je peux modifier les informations d’un utilisateur et rendre son compte inactif.

**5 Gestion des utilisateurs — Inscrire un utilisateur manuellement**

- [ ] En tant qu’administrateur, je peux créer un nouvel utilisateur avec saisie manuelle des informations.

**6 Gestion des sorties — Gérer les villes**

- [ ] En tant qu’administrateur, je peux ajouter des villes utilisables dans la plateforme.

**7 Gestion des sorties — Gérer les campus**

- [ ] En tant qu’administrateur, je peux ajouter des campus utilisables dans la plateforme.

**8 Gestion des sorties — Création d’une sortie**

- [ ] Au lieu de lister tous les lieux, je peux filter la liste en fonction de la saisie d’une ville.
Voir maquette d’écran "Création d’une sortie (version 2").



---

### 3. Itération 3

Tâche Catégorie Nom Description

**1 Gestion des utilisateurs — Inscrire des utilisateurs par intégration d’un fichier**

- [ ] En tant qu’administrateur, je peux inscrire plusieurs utilisateurs via l’intégration d’un fichier .csv (Format à définir)

**2 Gestion d’une API — Liste des sorties**

- [ ] Une API doit permettre d’extraire la liste des sorties et être mise à disposition pour des applications tierces.
Possibilité de filtrer les sorties selon l’état et la date prévue.
On ne pourra pas extraire les sorties qui sont en cours de création ou terminées.

**3 Gestion d’une API — Liste des sorties**

- [ ] L’API ne sera accessible uniquement pour des utilisateurs authentifiés.

**4 Gestion des utilisateurs — Mot de passe oublié**

- [ ] En tant qu’utilisateur, je peux faire une demande de ré-initialisation de mot de passe.
La plateforme crée un lien vers un écran de saisie du nouveau mot de passe.
Ce lien sera envoyé par mail à l’utilisateur.



---

### 4. Itération 4

Importance Catégorie Nom Description

**1 Gestion des sorties — Annuler une sortie en tant qu’administrateur**

- [ ] En tant qu’administrateur, je peux annuler une sortie qui a été proposée par un autre organisateur.

**2 Gestion des utilisateurs — Supprimer des utilisateurs**

- [ ] En tant qu’administrateur, je peux supprimer des utilisateurs sélectionnés dans une liste d’utilisateurs (uniquement s’ils ne sont pas présents dans des sorties)

**3 Gestion des sorties — Gérer les lieux**

- [ ] En tant que participant, je peux ajouter des lieux dans la plateforme.
Voir maquette d’écran "Création d’une sortie (version 3)"

**4 Gestion des sorties — Afficher une carte**

- [ ] Dans la page d’affichage du détail d’une sortie, j’affiche une carte avec un point correspondant aux coordonnées géographiques saisies dans la sortie (latitude et longitude).

