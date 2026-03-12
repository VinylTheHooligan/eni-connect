# API et intégration

## Deux endpoints JSON

eni-connect n'est pas une API REST complète, mais elle expose deux endpoints JSON utiles, regroupés dans `src/Controller/Api/`. Ils servent principalement aux interactions dynamiques côté front, mais pourraient aussi être consommés par des services tiers.

---

## API des sorties

**Contrôleur** : `src/Controller/Api/APIOutingController.php`  
**Route** : `GET /api/sorties`

Retourne la liste des sorties au format JSON. Chaque sortie inclut :

```json
[
  {
    "id": 1,
    "name": "Sortie d'exemple",
    "startDateTime": "2026-03-15T18:00:00+01:00",
    "registrationDeadline": "2026-03-10T23:59:59+01:00",
    "maxRegistrations": 20,
    "status": "OUVERTE",
    "campus": "Nantes",
    "place": "Salle A",
    "organizer": "jdoe"
  }
]
```

```bash
curl -X GET http://localhost/api/sorties
```

---

## API des lieux par campus

**Contrôleur** : `src/Controller/Api/APIPlaceController.php`  
**Route** : `GET /places/by-campus/{id}` (nom de route : `places_by_campus`)

Retourne la liste des lieux rattachés au campus identifié par `{id}`.

```json
[
  {
    "id": 10,
    "name": "Salle A",
    "street": "1 rue de l'Exemple",
    "latitude": 47.12345,
    "longitude": -1.56789
  },
  {
    "id": 11,
    "name": "Salle B",
    "street": "2 rue de l'Exemple",
    "latitude": 47.22345,
    "longitude": -1.66789
  }
]
```

```bash
curl -X GET http://localhost/places/by-campus/1
```

---

## Comment ces endpoints sont utilisés côté front

**`/places/by-campus/{id}`** est l'endpoint le plus utilisé au quotidien. Sur les formulaires de création et d'édition de sortie, quand l'organisateur sélectionne un campus, un contrôleur Stimulus intercepte l'événement de changement, appelle cet endpoint et met à jour dynamiquement la liste déroulante des lieux disponibles. Sans ça, il faudrait recharger la page pour changer de campus.

**`/api/sorties`** est moins utilisé dans l'interface actuelle. Il est surtout là pour les usages externes : un front JS plus riche (SPA, React, Vue…) ou des systèmes tiers qui auraient besoin de consommer la liste des sorties.

---

## Ce qu'on pourrait ajouter

L'API actuelle est volontairement minimale. Quelques pistes d'évolution naturelles :

**Filtres sur les sorties** : ajouter des paramètres de query permettrait de filtrer sans tout récupérer.
```
GET /api/sorties?campus=1&from=2026-03-01&to=2026-03-31
```

**Détail d'une sortie** : un endpoint dédié pour récupérer une sortie spécifique.
```
GET /api/sorties/{id}
```

**Inscription via API** : pour des clients mobiles ou des intégrations tierces.
```
POST   /api/sorties/{id}/inscription
DELETE /api/sorties/{id}/inscription
```

**Autres ressources** : campus, villes, utilisateurs (selon les besoins d'intégration).

**Sécurisation** : actuellement, les endpoints peuvent être accessibles publiquement selon la configuration dans `security.yaml`. Si l'API est exposée à des systèmes tiers, il faudra probablement ajouter un mécanisme d'authentification (API key, JWT…).
