# Instructions Copilot pour ORéOF v2

Ce dépôt est une application **Symfony 8** pour la gestion de l'offre de formation universitaire. Le code mélange une
base legacy Bootstrap et une migration progressive vers **Tailwind CSS v4**, **Stimulus**, **Turbo** et **Twig
Components**.

L'objectif principal de l'IA est de produire des changements **petits, cohérents, compatibles avec l'existant**, et
faciles à relire.

## 1. Stack et architecture du projet

### Backend

- **PHP >= 8.2**
- **Symfony 8**
- **Doctrine ORM / Migrations**
- **Twig** + extensions maison dans `src/Twig/`
- **Twig Components** dans `src/Twig/Components/` avec templates dans `templates/components/`
- **Symfony UX** : Autocomplete, Live Component, Turbo, Icons, Chart.js

### Frontend

- **Webpack Encore** (pas Vite)
- Entrées principales :
    - `assets/app.js`
    - `assets/legacy.js`
    - `assets/print.js`
- **Stimulus** via `assets/bootstrap.js` et `assets/controllers.json`
- **Turbo** activé via `@symfony/ux-turbo`
- **Tailwind CSS v4** chargé depuis `assets/styles/app.css`
- **Bootstrap 5** encore présent pour certaines interactions legacy (ex. tooltip, modal, comportements historiques)
- **Tom Select** utilisé via **Symfony UX Autocomplete**

### Organisation des dossiers

- `src/Controller/` : contrôleurs Symfony (routing par attributs)
- `src/Twig/` : extensions Twig et composants Twig PHP
- `templates/` : vues Twig, composants, fragments Turbo
- `assets/controllers/` : contrôleurs Stimulus
- `assets/styles/` : styles applicatifs
- `tests/` : tests PHP
- `cypress/` : tests E2E

## 2. Principes généraux de contribution

- Toujours **comprendre le flux complet** avant de modifier : contrôleur Symfony, template Twig, contrôleur Stimulus,
  styles Tailwind, éventuellement Turbo.
- Faire des **diffs minimaux** : ne pas reformater massivement un fichier sans nécessité.
- **Préserver les hooks existants** : `id`, `data-*`, `stimulus_controller()`, `stimulus_action()`, `stimulus_target()`
  sont souvent critiques.
- Conserver les API publiques existantes tant qu'un refactor global n'est pas explicitement demandé.
- Quand une fonctionnalité existe déjà sous forme de composant, **réutiliser** le composant au lieu de dupliquer du
  markup.

## 3. Règles Twig / Symfony

### Templates

- Préférer des fragments Twig clairs et composables.
- Réutiliser `templates/components/` dès qu'un motif UI se répète.
- Pour un nouveau composant UI réutilisable, créer :
    - un composant PHP dans `src/Twig/Components/`
    - un template dans `templates/components/`
- Respecter les conventions de nommage déjà présentes, par exemple :
    - `FormationStateComponent.php` ↔ `templates/components/formation_state.html.twig`
    - `RemplissageProgressComponent.php` ↔ `templates/components/remplissage_progress.html.twig`

### Extensions Twig

- Les extensions Twig de `src/Twig/` contiennent encore des helpers legacy qui génèrent du HTML.
- Pour les nouveaux développements UI, **préférer un composant Twig** à un filtre qui retourne une chaîne HTML.
- Garder les extensions existantes pour compatibilité tant qu'une migration complète n'est pas demandée.

### Contrôleurs Symfony

- Les routes sont chargées depuis `src/Controller/` via attributs.
- Lorsqu'un fragment est chargé avec **Turbo Frame** ou **Turbo Stream**, la réponse doit être compatible avec le
  contexte d'appel.
- Éviter les régressions de type **`content-missing`** :
    - si la requête vise un `turbo-frame`, retourner le bon wrapper `<turbo-frame id="...">`
    - si la requête attend un stream, retourner un `turbo-stream`
- Si une infrastructure dédiée existe (ex. factory Turbo Stream), la réutiliser plutôt que recréer une logique
  parallèle.

## 4. Règles Stimulus / Turbo / JS

### Stimulus

- Les contrôleurs sont dans `assets/controllers/`.
- Ne pas casser les conventions existantes Symfony UX :
    - `stimulus_controller()`
    - `stimulus_action()`
    - `stimulus_target()`
- En cas de refonte HTML, vérifier que les actions sont toujours attachées au bon élément et que `event.currentTarget`
  reste pertinent.
- Si un comportement dépend de templates injectés dynamiquement, vérifier l'impact sur les contrôleurs Stimulus déjà
  branchés.

### Turbo

- Avant toute modification d'une vue partielle, vérifier si elle peut être utilisée dans :
    - une page complète
    - un `turbo-frame`
    - un `turbo-stream`
- Les fragments Turbo doivent rester autonomes et ne pas supposer un layout complet.

### JavaScript global

- `assets/app.js` charge l'application principale, Bootstrap, Trix, Tailwind, et les initialisations globales.
- Ne pas introduire de nouvelle pile JS si Stimulus ou le JS existant suffit.
- Préférer des changements localisés et lisibles.

## 5. Règles CSS / Tailwind

### Principe directeur

- **Tailwind d'abord**, Bootstrap seulement en compatibilité legacy.
- Ne pas introduire de nouveau markup Bootstrap si un équivalent Tailwind est possible.
- Si un composant Bootstrap legacy doit être conservé, surcharger son rendu pour rester cohérent avec le design global.

### Fichier de référence

- Le point d'entrée principal des styles applicatifs est `assets/styles/app.css`.
- Ce fichier contient déjà :
    - les sources Tailwind (`@source`)
    - le dark mode via `html[data-theme="dark"]`
    - les composants applicatifs `app-*`
    - les couches de compatibilité Bootstrap
    - les surcharges Tom Select / Symfony UX Autocomplete

### Conventions visuelles

- Réutiliser autant que possible les classes applicatives existantes :
    - `app-btn*`
    - `app-tabs*`
    - `app-section-*`
    - `app-alert*`
    - `app-progress*`
    - autres classes `app-*` déjà présentes
- Lorsqu'un motif revient souvent, créer une classe composant réutilisable avec `@layer components` + `@apply`.
- Respecter le **dark mode** systématiquement si le composant est visible dans l'UI principale.

### Migration Bootstrap → Tailwind

- Le dépôt est en migration progressive :
    - ne pas supprimer brutalement les classes legacy si elles sont encore utilisées par du JS ou du HTML existant
    - préférer une migration incrémentale, écran par écran, composant par composant
- Si une vue mélange Bootstrap et Tailwind, viser un état transitoire stable plutôt qu'une réécriture risquée.

## 6. Symfony UX Autocomplete / Tom Select

- Les listes déroulantes utilisent **Symfony UX Autocomplete** avec **Tom Select**.
- Les CSS par défaut de Tom Select sont désactivés dans `assets/controllers.json`.
- Le thème applicatif est géré dans `assets/styles/app.css` via les classes `.ts-*`.
- Pour toute évolution d'autocomplete :
    - ne pas réactiver les CSS par défaut de Tom Select
    - conserver une cohérence visuelle avec les champs Tailwind du projet
    - vérifier les états `focus`, `disabled`, `multi`, dropdown, dark mode

## 7. UI et accessibilité

- Conserver ou améliorer les attributs d'accessibilité existants :
    - `aria-*`
    - `role`
    - libellés explicites
- Les boutons d'action doivent rester compréhensibles sans ambiguïté.
- Les états visuels doivent être distinguables en light et dark mode.
- Les tableaux, onglets, dropdowns et progress bars doivent rester utilisables au clavier.

## 8. Tables, boutons, badges et composants métier

- Les listes et tableaux doivent suivre le style moderne déjà amorcé dans les écrans formations / parcours.
- Pour les tableaux :
    - privilégier wrappers responsive (`overflow-x-auto`)
    - garder une hiérarchie visuelle sobre
    - conserver les hooks de tri/filtre Stimulus
- Pour les boutons d'action :
    - réutiliser `app-btn`, `app-btn-primary`, `app-btn-success`, `app-btn-warning`, `app-btn-danger`, etc.
- Pour les indicateurs de statut / remplissage :
    - préférer des composants Twig dédiés plutôt que du HTML inline dans les filtres Twig

## 9. Validation avant de conclure un changement

Après modification, vérifier autant que possible les points pertinents.

### Frontend

- Build :
    - `npm run dev`
- Lint JS si nécessaire :
    - `npm run lint`

### Backend

- Syntaxe PHP ciblée si besoin
- Analyse statique si le contexte le permet :
    - `make phpstan`
- Tests PHP si la zone est couverte :
    - `make test`

### E2E / interactions critiques

- Si un flux UI critique est touché et que des scénarios existent :
    - `make cypress-run`

Si l'environnement local ou Docker n'est pas disponible, le préciser explicitement et faire au minimum les validations
faisables sur les fichiers modifiés.

## 10. Ce qu'il faut éviter

- Ne pas réintroduire massivement Bootstrap dans les nouvelles vues.
- Ne pas casser les contrôleurs Stimulus existants en changeant arbitrairement les sélecteurs.
- Ne pas déplacer de logique métier côté front si elle appartient au backend Symfony.
- Ne pas dupliquer un composant déjà existant sous un autre nom.
- Ne pas supprimer des classes legacy sans vérifier leurs usages JS/Twig/CSS.
- Ne pas faire de refactor global non demandé sous couvert d'une correction locale.

## 11. Checklist rapide avant toute proposition

- Le bon niveau a-t-il été modifié ? (contrôleur, composant Twig, template, Stimulus, CSS)
- Les hooks `stimulus_*`, `data-*`, `id` et `aria-*` sont-ils préservés ?
- Le rendu est-il cohérent avec `assets/styles/app.css` et les composants `app-*` ?
- Le dark mode est-il pris en compte ?
- La compatibilité Turbo / Turbo Frame / Turbo Stream a-t-elle été vérifiée si nécessaire ?
- Un composant réutilisable aurait-il été préférable à du markup dupliqué ?
- Les validations minimales ont-elles été exécutées ?

## 12. Résumé de la stratégie attendue

Pour ORéOF v2, l'IA doit privilégier une approche :

- **Symfony + Twig Components côté serveur**
- **Stimulus + Turbo côté interactions**
- **Tailwind + classes applicatives `app-*` côté design**
- **migration progressive et sûre** du legacy Bootstrap
- **validation systématique** des changements touchant l'UI ou les flux métier
