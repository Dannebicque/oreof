# Prototype CRUD moderne - TypeDiplome

Ce prototype est isolé dans un namespace `Prototype` pour servir de base duplicable sur d'autres CRUD administratifs.

## Fonctionnalités incluses

- Liste dynamique avec recherche texte, filtres booléens, tri, pagination.
- Création et modification via formulaire Symfony (`TypeDiplomeType`).
- Duplication sécurisée (POST + CSRF).
- Suppression sécurisée avec:
    - estimation d'impact (score + dépendances),
    - warning visuel,
    - blocage si dépendances critiques détectées.
- Persistance de l'état de liste dans `localStorage`.

## Fichiers créés

- `src/Controller/Prototype/TypeDiplomePrototypeController.php`
- `src/Service/Prototype/TypeDiplomePrototypeQueryService.php`
- `src/Service/Prototype/TypeDiplomeImpactEstimator.php`
- `src/Service/Prototype/TypeDiplomePrototypeDuplicator.php`
- `templates/prototype/type_diplome/index.html.twig`
- `templates/prototype/type_diplome/_list.html.twig`
- `templates/prototype/type_diplome/form.html.twig`
- `assets/controllers/type_diplome_prototype_controller.js`

## Accès

Route d'entrée: `/prototype/type-diplome/`

## Duplication vers un autre CRUD

1. Copier les fichiers vers le nouvel agrégat (`EntityXPrototypeController`, `EntityXPrototypeQueryService`, etc.).
2. Ajuster les colonnes/filters dans `_list.html.twig`.
3. Adapter le service d'impact (`TypeDiplomeImpactEstimator`) aux relations de l'entité cible.
4. Conserver le contrôleur Stimulus en renommant les champs d'état (`q`, `sort`, `page`, etc.).
5. Ajouter les routes dans le menu applicatif seulement une fois validé.

