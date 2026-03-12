# Qualité, tests et bonnes pratiques

## Stratégie de tests

L'application utilise **PHPUnit**, configuré dans `phpunit.dist.xml` avec `tests/bootstrap.php` comme point d'entrée.

Les tests existants couvrent deux zones :

**Tests de fumée** (`tests/BasicSmokeTest.php`) : vérifient que les routes de base répondent correctement. Par exemple, que `/` renvoie bien une redirection vers `/login`. Ce sont des tests minimaux qui garantissent que l'application démarre et route correctement.

**Tests fonctionnels de sécurité** (`tests/Application/Security/LoginFailureTest.php`) : vérifient les comportements lors d'un échec de connexion (mauvais mot de passe, compte inexistant…).

La couverture de tests est aujourd'hui limitée. L'idée à retenir pour les contributions : tout ajout fonctionnel non trivial devrait s'accompagner d'au moins un test.

Pour lancer les tests :

```bash
php bin/phpunit
```

---

## Fixtures et données de test

Le projet utilise **Zenstruck Foundry** pour générer des données de test réalistes.

- `src/DataFixtures/` – les classes de fixtures
- `src/Factory/` – les factories (une par entité : `UserFactory`, `OutingFactory`…)
- `src/Story/AppStory.php` – le scénario principal qui orchestre la création des données

L'avantage de Foundry : créer des données de test lisibles et maintenables. Par exemple, `UserFactory::new()->createMany(5)` crée cinq utilisateurs avec des données cohérentes en une ligne.

```bash
php bin/console doctrine:fixtures:load
```

---

## Outils et conventions

**`.editorconfig`** : définit l'encodage, le type d'indentation et sa taille. Il est à la racine du projet et est normalement pris en charge automatiquement par les IDE courants. Son but est simple : que tout le monde formate son code de la même façon, peu importe son éditeur.

**Scripts Composer** : le fichier `composer.json` définit des scripts auto-exécutés après `composer install` et `composer update` :
- `bin/console cache:clear`
- `bin/console assets:install`
- `bin/console importmap:install`

Pas besoin de les lancer manuellement après une installation.

**Analyse statique et formatage** : il n'y a pas encore d'outil de linting configuré dans le dépôt (pas de PHP-CS-Fixer, pas de PHPStan). C'est un manque. La recommandation serait d'ajouter progressivement PHP-CS-Fixer pour le style et PHPStan pour la robustesse du code.

**Notifications GitHub** : le workflow `.github/workflows/discord.yml` envoie des notifications sur un canal Discord lors des événements GitHub (push, pull requests…).

---

## Bonnes pratiques de contribution

### Où mettre quoi

**Nouveau service métier** → `src/Services/` (ou un sous-dossier, ex. `Admin/` pour l'administration). Le principe fondamental : les contrôleurs restent légers. Si une logique dépasse la gestion de la requête HTTP, elle va dans un service.

**Nouvelle entité** → `src/Entity/`. Générer la migration correspondante, créer le repository dans `src/Repository/` si des requêtes spécifiques seront nécessaires.

**Nouveau formulaire** → `src/Form/`. Le lier à l'entité ou au DTO concerné.

**Nouveau contrôleur** → `src/Controller/`, dans le sous-dossier approprié (`Admin/` pour le back-office, `Api/` pour les endpoints JSON).

### Tests

Pour toute nouvelle fonctionnalité non triviale, ajouter au moins un test. Utiliser Foundry pour créer les données de test nécessaires — c'est rapide et ça donne des tests lisibles.

Pour une régression corrigée : ajouter un test qui reproduit le bug, vérifier qu'il échoue avant le fix, puis passer.

### Revue de code

Points à vérifier lors d'une review :

- La structure des dossiers est respectée
- Le pattern est cohérent avec l'existant (services, formulaires, entités)
- Pas de logique métier dupliquée — si quelque chose existe déjà dans un service ou une méthode d'entité, l'utiliser plutôt qu'en recréer une version

---

## Checklist avant merge

Avant de merger une branche, s'assurer que :

- [ ] `php bin/phpunit` passe sans erreur
- [ ] Les migrations Doctrine sont générées et appliquées en local si le modèle de données a changé
- [ ] Aucun fichier sensible n'est commité (pas de `.env.local`, pas de secrets en dur)
- [ ] Le code respecte les conventions du projet (indentation, nommage, organisation)
- [ ] La documentation technique est à jour si la modification impacte l'architecture, la sécurité, les données ou un flux métier
