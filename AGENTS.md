# AGENTS.md — ORéOF

## À lire en premier

- `README.md` pour le périmètre produit.
- `install.md` pour le flux Docker/Makefile et production.
- `command.md` pour les commandes métier exposées.
- `bootstrap-to-tailwind-style-guide.md` pour la transition de bootstrap à tailwind.
- `docs/architecture/maquette-modulaire.md` pour la direction d’architecture.
- `doc-api.md` et `dpe.md` pour la documentation technique et métier liée au versioning/DPE.
- `CHANGELOG.md` pour l'historique des changements et évolutions.
- `Update_BDD.md` pour les procédures de migration/import de base de données.
- `Recopie_FicheMatiere.md` pour les opérations de recopie des fiches matières.
- `python_worker/README.md` pour les scripts Python liés à la génération/export.
- `UX_IMPROVEMENTS.md` et `UX_IMPROVEMENTS_SUMMARY.md` pour le contexte UX et les décisions récentes.
- `config/services.yaml`, `config/routes.yaml`, `config/packages/security.yaml`, `config/packages/workflow.yaml` pour les points d’intégration.

## Vue d’ensemble

- Application Symfony 8/PHP 8.4+ orientée métier universitaire (maquettes, parcours, MCCC, validation, export).
- Les routes sont principalement en attributs dans `src/Controller/` (ex. `DefaultController` avec `#[Route]`).
- Les règles métier structurantes sont souvent dans des services/handlers dédiés plutôt que dans les contrôleurs.

## Conventions d’architecture à respecter

- Les services sont autowirés/autoconfigurés depuis `src/` via `config/services.yaml` ; ajoutez des tags explicites quand un registre les consomme.
- Les handlers de type diplôme sont résolus par `App\TypeDiplome\TypeDiplomeResolver` via une clé dérivée de `TypeDiplome::libelleCourt` (code en majuscules).
- Les workflows Symfony sont déclarés dans `config/packages/workflow.yaml` et pilotent aussi la UI via leurs `metadata` (boutons, icônes, formulaires, destinataires).
- Les uploads sensibles passent par `App\Service\SecureUploadService` ; ne contournez pas ses contrôles extension/MIME/taille.
- Gardez le vocabulaire métier en français et suivez les noms déjà présents (`Parcours`, `FicheMatiere`, `DpeParcours`, etc.).

## Fichiers repères à consulter avant de modifier

- `src/Command/McccPdfCommand.php` pour les exports PDF et les traitements par type de diplôme.
- `src/Service/SecureUploadService.php` pour la politique d’upload sécurisé.
- `src/Workflow/StepHandlerRegistry.php` et `config/packages/workflow.yaml` pour la mécanique de workflow.
- `src/TypeDiplome/TypeDiplomeResolver.php` et `config/services.yaml` pour l’ajout de nouveaux handlers.
- `docs/architecture/maquette-modulaire.md` avant toute refonte de structure/validation/rendu.

## Commandes utiles

- Développement Docker: `make up`, `make start`, `make open`, `make logs`, `make ps`, `make cli`.
- QA PHP: `make test`, `make test-coverage`, `make phpstan`.
- Frontend: `npm run dev`, `npm run watch`, `npm run build`, `npm run lint`.
- Base de données: `make import-db FILE=dump.sql DB=oreof_2026`.
- Symfony dans le conteneur web: `php bin/console about`, `php bin/console doctrine:migrations:migrate -n`.

## Points d’attention

- `Makefile` expose `make cypress-open` / `make cypress-run`, mais `package.json` ne définit pas encore ces scripts ; vérifiez avant de les utiliser.
- Les accès publics et la sécurité sont pilotés par `config/packages/security.yaml` ; attention aux routes d’export publiques.
- Messenger route `App\Message\Export` vers `async_export` (`config/packages/messenger.yaml`) : surveillez les effets de bord asynchrones.
- Les assets sont gérés par Encore (`webpack.config.js`) avec `assets/app.js` et `assets/print.js`; l’interface mélange encore Tailwind/Turbo et des fragments legacy Bootstrap, donc ne supposez pas une migration front 100% uniforme.
