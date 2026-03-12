# Configuration, environnements et déploiement

## Les variables d'environnement

Tout démarre avec le fichier `.env` à la racine du projet. Il définit les variables qui changent selon l'environnement. Les principales :

| Variable | Rôle |
|---|---|
| `APP_ENV` | Environnement actif : `dev`, `test` ou `prod` |
| `APP_SECRET` | Secret Symfony, utilisé pour les tokens CSRF et autres signatures |
| `DATABASE_URL` | URL de connexion à la base (MySQL local sur `127.0.0.1:3306` par défaut) |
| `MAILER_DSN` | Configuration de l'envoi d'emails (Mailpit en dev) |
| `MESSENGER_TRANSPORT_DSN` | Transport de messages asynchrones (si utilisé) |
| `UX_MAP_DSN` | Configuration des cartes Leaflet/UX |

En développement, on travaille avec un fichier `.env.local` qui surcharge `.env` sans être commité. En production, ces valeurs doivent être injectées via le système d'environnement du serveur ou du conteneur — **jamais commitées**.

---

## La configuration des packages Symfony

Tous les fichiers de configuration sont dans `config/packages/`. En voici les principaux :

**`framework.yaml`** – configuration de base du framework : secret, sessions, comportement des erreurs.

**`security.yaml`** – tout ce qui concerne la sécurité : providers, firewalls, hiérarchie des rôles, règles d'accès par URL. C'est le fichier le plus important à comprendre pour naviguer dans la sécurité de l'appli (voir `securite.md`).

**`doctrine.yaml`** – connexion à la base via `DATABASE_URL` et configuration de l'ORM (mapping des entités `App\Entity`).

**`doctrine_migrations.yaml`** – indique où se trouvent les fichiers de migration (`migrations/`) et quel namespace leur est associé.

**`twig.yaml`** – configuration du moteur de templates.

**`monolog.yaml`** – configuration des logs (handlers, niveaux, channels).

**`mailer.yaml`** – transport des emails via `MAILER_DSN`.

**`asset_mapper.yaml`** – configuration des assets front : chemin `assets/`, intégration avec `assets/bootstrap.js`.

**`notifier.yaml`, `messenger.yaml`, `cache.yaml`** – selon les besoins, notification, messagerie interne et cache.

---

## Le routage

- `config/routes.yaml` – point d'entrée principal, importe les routes définies par attributs dans les contrôleurs
- `config/routes/framework.yaml` et `web_profiler.yaml` – routes du framework et du profiler (dev uniquement)
- `config/routes/security.yaml` – route de logout gérée par le composant Security

---

## Les services et l'injection de dépendances

Le fichier `config/services.yaml` active l'autowiring et l'autoconfiguration. En pratique, ça veut dire que la grande majorité des services sont déclarés automatiquement : Symfony détecte les classes dans `src/` et les enregistre comme services sans configuration manuelle. Il reste possible de définir des alias ou de surcharger des services si nécessaire.

---

## Docker pour le développement

Le projet inclut `compose.yaml` et `compose.override.yaml` pour démarrer des services en local sans installation manuelle :

- **`database`** – PostgreSQL, en alternative à MySQL pour la démo ou la CI
- **`mailer`** – Mailpit, une interface web pour visualiser les emails envoyés en dev

Pour démarrer ces services : `docker compose up -d`.

---

## Installation en local

### Pré-requis

- PHP ≥ 8.2
- Un serveur web (WAMP, LAMP, MAMP…) avec la racine pointant vers `public/`
- MySQL avec une base `eni-connect` (ou le nom configuré dans `DATABASE_URL`)
- Composer

### Étapes

```bash
# 1. Cloner le dépôt
git clone <url-du-repo>
cd eni-connect

# 2. Installer les dépendances PHP
composer install

# 3. Configurer l'environnement local
cp .env .env.local
# Éditer .env.local : DATABASE_URL, APP_SECRET, MAILER_DSN…

# 4. Créer la base de données
php bin/console doctrine:database:create

# 5. Appliquer les migrations
php bin/console doctrine:migrations:migrate

# 6. (Optionnel) Charger des données de démo
php bin/console doctrine:fixtures:load

# 7. Lancer le serveur
symfony serve
# ou configurer Apache/Nginx pour pointer vers public/
```

---

## Mettre à jour l'application

Après un `git pull` :

```bash
# Mettre à jour les dépendances
composer install

# Appliquer les nouvelles migrations
php bin/console doctrine:migrations:migrate

# Si de nouvelles fixtures ont été ajoutées en démo
php bin/console doctrine:fixtures:load

# Vider le cache en prod
php bin/console cache:clear --env=prod
```

---

## Recommandations pour la production

**Secrets** : ne jamais commiter `APP_SECRET` ni les identifiants de base. Utiliser les variables d'environnement du système d'hébergement ou un vault.

**Migrations** : toujours les exécuter dans une étape contrôlée du déploiement. Prévoir un plan de rollback si une migration échoue.

**Cache** : vider le cache après chaque déploiement significatif avec `php bin/console cache:clear --env=prod`.

**Logs** : surveiller les logs via Monolog. La configuration dans `monolog.yaml` détermine où ils sont écrits.

**Sécurité serveur** : vérifier les permissions sur `var/` et `public/`. S'assurer que le profiler Symfony et les routes de dev ne sont pas accessibles en production.
