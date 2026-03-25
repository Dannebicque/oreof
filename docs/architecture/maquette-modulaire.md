# Architecture cible — maquette pédagogique modulaire

## Objectif

Faire évoluer ORéOF d’une structure de maquette **figée par convention métier** vers un système **piloté par profils de
diplôme**, capable de :

- représenter des structures différentes selon le diplôme ;
- gérer des niveaux hiérarchiques variables et des blocs de choix imbriqués ;
- porter les données pédagogiques (MCCC, heures, ECTS, coefficients, compétences, validation) à plusieurs niveaux ;
- définir des règles de calcul, d’héritage, d’obligation et de validation **hors du socle générique** ;
- adapter l’affichage, l’édition et les exports au profil de diplôme et à la structure réellement construite.

---

## Constat sur l’existant

La base actuelle contient déjà une première séparation par type de diplôme via `TypeDiplomeHandlerInterface`, mais elle
reste limitée car le modèle métier principal demeure rigide.

### Points de rigidité identifiés

#### 1. Hiérarchie implicite dans les entités et DTO

La structure est aujourd’hui encodée comme :

- `Parcours`
- `Annee`
- `SemestreParcours` / `Semestre`
- `Ue`
- `ElementConstitutif`
- `FicheMatiere`

avec des variantes gérées par des champs spécifiques (`ueParent`, `ecParent`, `natureUeEc`, `isLibre`, `isChoix`, etc.).

Cela fonctionne pour le cas dominant, mais force le métier à entrer dans une hiérarchie unique.

#### 2. Règles d’affichage codées en dur

Exemples relevés :

- `src/Entity/Ue.php` : affichage spécial si le type diplôme est `BUT` ou `M2E`.
- `src/Controller/ParcoursExportController.php` : traitement des choix déroulé à la main, avec une profondeur
  explicitement gérée jusqu’à 2 niveaux.
- `src/DTO/StructureSemestre.php` : `getAnnee()` codé pour `1..6`.
- `src/Service/Parcours/GenereStructureParcours.php` : génération fondée sur la règle “2 semestres = 1 année”.

#### 3. Règles de priorité/héritage disséminées

`src/Classes/GetElementConstitutif.php` contient déjà beaucoup de logique métier transverse :

- source des MCCC ;
- source des ECTS ;
- source des heures ;
- héritage via parent EC ;
- surcharge via fiche matière ;
- cas des enfants identiques ;
- cas des éléments rattachés.

Cette logique est utile, mais aujourd’hui trop centrée sur le duo `EC/FicheMatiere` au lieu d’un mécanisme générique de
résolution d’attribut.

#### 4. Responsabilités trop larges dans les handlers de diplôme

Les handlers actuels agrègent des sujets hétérogènes :

- calcul de structure ;
- validation ;
- MCCC ;
- export ;
- rendu ;
- récupération des types d’épreuves.

Exemple : `UniversityHandler`, `ButHandler`, `M2EHandler`.

Cela rend la spécialisation possible, mais difficile à maintenir et à tester.

#### 5. Données de structure encore partiellement figées dans `TypeDiplome`

`src/Entity/TypeDiplome.php` porte aujourd’hui plusieurs contraintes métiers structurelles :

- bornes de semestres ;
- nombre min/max d’UE ;
- nb d’EC par UE ;
- flags d’obligation sur MCCC / ECTS ;
- options de présence de stage/projet/mémoire.

Ces champs sont utiles, mais ils ne suffisent pas pour représenter des profils riches ou divergents.

#### 6. Début de métamodèle déjà présent mais non exploité

`src/Entity/NodeType.php` et `src/Enums/NodeTypeEnum.php` sont une très bonne base :

- type de nœud ;
- libellé ;
- capacités (`supportsMccc`, `supportsEcts`, `supportsCompetencies`) ;
- caractère structurel.

En revanche, ce modèle n’est pas encore utilisé comme cœur de la maquette.

---

## Principe directeur

Le socle doit connaître **comment orchestrer** une maquette, mais pas **quelles règles métier appliquer pour chaque
diplôme**.

En pratique :

- le **socle** gère un arbre/graph de nœuds, des attributs, des résolveurs, un moteur de règles et un moteur de rendu ;
- chaque **profil de diplôme** déclare sa structure autorisée, ses règles d’héritage, ses règles de validation, ses
  règles d’affichage et éventuellement ses exports spécifiques.

---

## Architecture cible

## 1. Métamodèle générique de maquette

Introduire une représentation canonique indépendante du stockage historique.

### `CurriculumNode` / `MaquetteNode`

Représente un nœud de la maquette.

Champs conceptuels :

- `id`
- `nodeType` (`NodeTypeEnum`)
- `label`
- `code`
- `parent`
- `children`
- `position`
- `path`
- `context` (parcours, formation, diplôme, campagne)
- `sourceEntityType`
- `sourceEntityId`

Ce nœud peut représenter :

- racine de maquette ;
- année ;
- semestre ;
- bloc pédagogique ;
- UE ;
- EC ;
- fiche matière ;
- bloc de choix ;
- regroupement ad hoc imposé par un diplôme.

> Important : la structure canonique peut être un **read model** au départ, construit à partir des entités existantes,
> sans migration immédiate du stockage principal.

### `NodeCapability`

À partir de `NodeType`, définir ce qu’un nœud peut porter :

- MCCC ;
- heures ;
- ECTS ;
- coefficients ;
- compétences ;
- quitus ;
- règles de validation ;
- enfants ;
- choix ;
- mutualisation ;
- rattachement à une fiche.

L’idée est de séparer :

- **la nature du nœud** ;
- **les capacités du nœud** ;
- **la politique propre au diplôme**.

### `NodeAttributeBag`

Plutôt que de disperser la logique dans `UE`, `EC`, `Fiche`, chaque nœud expose un sac d’attributs normalisés :

- `hours.cm.pres`
- `hours.td.pres`
- `hours.tp.pres`
- `hours.cm.dist`
- `mccc`
- `ects`
- `coeff`
- `competencies`
- `validation_rules`
- `display.badges`
- `choice.min`
- `choice.max`
- `choice.strategy`

Ces attributs peuvent être :

- locaux ;
- hérités ;
- calculés ;
- imposés par une source prioritaire.

---

## 2. Moteur de résolution des attributs

C’est le point clé pour remplacer la logique actuelle de `GetElementConstitutif`.

### `AttributeResolverInterface`

Exemples de résolveurs :

- `HoursResolver`
- `EctsResolver`
- `McccResolver`
- `CoefficientResolver`
- `CompetencyResolver`
- `ValidationRulesResolver`

Chaque résolveur reçoit :

- le nœud courant ;
- le contexte (`Parcours`, `TypeDiplome`, campagne, options) ;
- la politique du diplôme ;
- éventuellement le chemin de nœuds ancêtres.

Il renvoie :

- une valeur calculée ;
- sa source ;
- son état (`provided`, `inherited`, `computed`, `missing`, `invalid`).

### Politique de priorité par diplôme

Exemples :

- pour un diplôme A, les MCCC par défaut viennent de la fiche matière ;
- pour un diplôme B, elles viennent du niveau EC sauf si un flag impose l’héritage parent ;
- pour un diplôme C, les ECTS sont interdits au niveau EC mais autorisés au niveau UE.

Ces règles ne doivent plus être codées dans l’entité, mais dans un objet de politique.

Exemple conceptuel :

- `DiplomaAttributePolicy::resolveMcccSource(node)`
- `DiplomaAttributePolicy::resolveEctsSource(node)`
- `DiplomaAttributePolicy::isAttributeRequired(node, attribute)`

---

## 3. Profil de diplôme modulaire

Créer un vrai contrat de profil métier.

### `DiplomaProfileInterface`

Responsabilités recommandées :

- `getCode()`
- `getStructureDefinition()`
- `getAttributePolicy()`
- `getValidationRuleset()`
- `getDisplaySchema()`
- `getExportStrategies()`
- `getEditingPolicy()`

Ce profil remplace le rôle de “gros handler” unique.

### Découpage interne recommandé

#### `StructureDefinition`

Définit :

- types de nœuds autorisés ;
- relations autorisées ;
- cardinalités ;
- profondeur autorisée ;
- présence ou non d’années / semestres / blocs intermédiaires ;
- possibilités de choix ;
- niveaux où l’on peut attacher une fiche matière.

#### `AttributePolicy`

Définit :

- où vivent les heures ;
- où vivent les MCCC ;
- où vivent les ECTS ;
- règles d’héritage ;
- règles de surcharge ;
- attributs obligatoires selon le type de nœud.

#### `ValidationRuleset`

Définit :

- règles structurelles ;
- règles d’équilibre ECTS ;
- règles de présence des MCCC ;
- règles de validation annuelle / semestrielle / compensation ;
- règles spécifiques au diplôme.

#### `DisplaySchema`

Définit :

- quels nœuds afficher dans la navigation ;
- comment les libeller ;
- quels badges afficher ;
- quelles colonnes montrer ;
- comment rendre un bloc de choix.

#### `ExportStrategy`

Décline la représentation vers :

- PDF ;
- Excel ;
- JSON ;
- API ;
- exports Apogée.

---

## 4. Moteur de validation déclaratif + extensible

Le validateur de parcours ne doit plus être une classe monolithique par diplôme.

### `RuleInterface`

Chaque règle reçoit un contexte et renvoie des issues.

Exemples :

- `SemesterMustHaveCreditsRule`
- `LeafNodeMustHaveCourseSheetRule`
- `McccRequiredOnLeafRule`
- `ChoiceNodeMustHaveChildrenRule`
- `CreditsRangeRule`
- `ButCompetencyCoverageRule`
- `LicenceSemesterEquals30EctsRule`

### Niveaux de portée

Une règle doit déclarer son scope :

- parcours ;
- année ;
- semestre ;
- nœud ;
- relation parent/enfant.

### Composition par profil de diplôme

Exemple :

- un tronc commun de règles du socle ;
- un paquet de règles “university” ;
- un paquet `BUT` ;
- un paquet `M2E`.

Cela permet d’éviter de tout dupliquer dans `ValideParcoursLicence`, `ValideParcoursBut`, etc.

---

## 5. Rendu piloté par schéma plutôt que par type en dur

Aujourd’hui plusieurs affichages dépendent implicitement de `BUT`, `M2E`, etc.

À la place, utiliser un `DisplaySchema` capable de décrire :

- la hiérarchie à présenter ;
- la profondeur à rendre ;
- les attributs visibles ;
- les composants Twig à utiliser ;
- les labels de niveau ;
- les variantes d’UI par type de diplôme.

### Recommandation Twig

Conserver les templates spécifiques, mais les faire consommer une représentation générique :

- `tree`
- `node`
- `node.attributes`
- `node.validation`
- `node.children`
- `schema`

Ainsi, le diplôme impacte l’affichage via un schéma, pas via des `if type == BUT` dispersés.

---

## 6. Coexistence ancien modèle / nouveau modèle

Refonte recommandée en **deux couches**.

### Couche 1 — Canonical read model

Construire un arbre générique `MaquetteTree` depuis les entités existantes :

- `Parcours`
- `Annee`
- `SemestreParcours`
- `Ue`
- `ElementConstitutif`
- `FicheMatiere`

C’est la stratégie la moins risquée pour démarrer.

### Couche 2 — Écriture native éventuelle

Dans un second temps seulement, si nécessaire, introduire un stockage générique natif pour la maquette.

Ce stockage pourra alors remplacer progressivement les liaisons trop spécialisées.

---

## Proposition concrète de découpage Symfony

## Nouvelles briques recommandées

### `src/Maquette/Model/`

- `MaquetteTree`
- `MaquetteNode`
- `NodeAttributeBag`
- `ResolvedAttribute`
- `NodePath`

### `src/Maquette/Definition/`

- `StructureDefinitionInterface`
- `NodeDefinition`
- `EdgeDefinition`
- `ChoiceDefinition`
- `DisplaySchema`

### `src/Maquette/Resolver/`

- `AttributeResolverInterface`
- `HoursResolver`
- `EctsResolver`
- `McccResolver`
- `CoefficientResolver`
- `ValidationPolicyResolver`

### `src/Maquette/Validation/`

- `RuleInterface`
- `RuleContext`
- `Ruleset`
- `RuleEngine`
- `RuleResult`

### `src/DiplomaProfile/`

- `DiplomaProfileInterface`
- `ProfileRegistry`
- `AbstractDiplomaProfile`

### `src/DiplomaProfile/Profiles/Licence/`

### `src/DiplomaProfile/Profiles/But/`

### `src/DiplomaProfile/Profiles/M2E/`

avec, pour chaque profil :

- `...StructureDefinition`
- `...AttributePolicy`
- `...Ruleset`
- `...DisplaySchema`
- `...ExportStrategy`

---

## Réutilisation de l’existant

## À conserver

- `TypeDiplomeResolver` comme point d’entrée de résolution par diplôme ;
- le principe des handlers/profils par type ;
- les DTO de validation (`ValidationResult`, `ValidationIssueDto`) ;
- les exports spécifiques quand ils sont réellement distincts ;
- `NodeType` / `NodeTypeEnum` comme base des capacités des nœuds.

## À faire évoluer

- `TypeDiplomeHandlerInterface` vers un rôle d’orchestrateur léger ou façade ;
- `StructureParcours*` vers un builder de `MaquetteTree` ;
- `GetElementConstitutif` vers des résolveurs d’attributs ;
- `ValideParcours*` vers une composition de règles ;
- les contrôleurs / templates vers une consommation du modèle générique.

## À dé-rigidifier en priorité

- `GenereStructureParcours`
- `StructureSemestre::getAnnee()`
- `Ue::display()`
- `ParcoursExportController` pour la gestion des choix à profondeur arbitraire

---

## Stratégie de migration incrémentale

## Lot 1 — Stabiliser le socle sans casser l’existant

Objectif : introduire la couche canonique sans changer les écrans.

1. Créer `MaquetteTree` et `MaquetteNode`.
2. Ajouter un builder depuis les entités existantes.
3. Brancher les calculs de structure existants sur ce builder.
4. Garder les DTO actuels comme adaptateurs de sortie.

Résultat : vous avez un modèle générique sans migration de données.

## Lot 2 — Extraire les politiques d’attributs

1. Remplacer progressivement `GetElementConstitutif` par des résolveurs.
2. Formaliser l’ordre de priorité pour heures / MCCC / ECTS.
3. Permettre à chaque diplôme d’écraser la politique de résolution.

Résultat : le comportement pédagogique devient configurable par profil.

## Lot 3 — Refondre la validation

1. Introduire un moteur de règles.
2. Migrer d’abord les règles licence déjà explicites.
3. Ajouter ensuite les règles spécifiques BUT et M2E.

Résultat : validation testable, composable, documentable.

## Lot 4 — Rendu générique

1. Introduire un `DisplaySchema`.
2. Alimenter les templates avec `MaquetteTree` ou un view model générique.
3. Supprimer les tests `if diplôme == ...` des entités et contrôleurs.

Résultat : affichage piloté par profil et structure réelle.

## Lot 5 — Édition modulaire

1. Décrire ce qu’on peut créer à chaque niveau par profil.
2. Utiliser le schéma pour générer boutons, formulaires, actions disponibles.
3. Déporter les restrictions de création/saisie hors des contrôleurs historiques.

Résultat : l’éditeur devient lui aussi piloté par profil.

## Lot 6 — Persistance générique native si nécessaire

À ne faire qu’une fois le read model stabilisé et largement utilisé.

---

## Recommandations très concrètes pour votre cas

## 1. Ne partez pas d’abord d’un nouveau schéma BDD

Le bon premier investissement n’est pas une migration lourde, mais un **modèle canonique en mémoire**.

Cela vous permettra de :

- tester l’architecture ;
- migrer écran par écran ;
- limiter le risque sur les données ;
- conserver les imports/exports existants pendant la transition.

## 2. Faites du type de diplôme un “profil”, pas juste un switch

Aujourd’hui, `TypeDiplomeResolver` choisit essentiellement un handler.
Demain, il devrait retourner un profil riche décrivant :

- structure ;
- politiques ;
- validation ;
- affichage ;
- exports.

## 3. Gardez le socle très petit

Le socle doit seulement imposer :

- une structure de nœuds ;
- une mécanique de résolution ;
- une mécanique de validation ;
- une mécanique de rendu.

Il ne doit pas imposer :

- “une année contient exactement 2 semestres” ;
- “un semestre contient forcément des UE” ;
- “une UE contient forcément des EC” ;
- “les ECTS sont obligatoires à tel niveau”.

Ces règles doivent vivre dans les profils.

## 4. Utilisez `NodeType` comme point d’appui

Vous avez déjà commencé à modéliser des types de nœuds et des capacités.
C’est probablement le meilleur point d’ancrage pour la refonte.

Je recommande d’étendre cette logique avec :

- capacités supplémentaires ;
- types de relation autorisés ;
- règles d’éditabilité ;
- schémas d’affichage par type.

## 5. Standardisez les attributs pédagogiques

Heures, MCCC, ECTS, coefficients, quitus, compétences et règles de validation doivent devenir des **attributs normalisés
**, quel que soit le niveau où ils sont posés.

Le diplôme décide ensuite :

- à quels niveaux ils sont autorisés ;
- à quels niveaux ils sont obligatoires ;
- quel niveau est prioritaire ;
- comment ils se propagent.

---

## Risques à anticiper

### 1. Mélange actuel entre modèle, calcul, affichage et validation

Il faudra accepter une phase de coexistence.

### 2. Export métier existant très couplé aux DTO actuels

Prévoir des adaptateurs plutôt qu’une réécriture big bang.

### 3. Cas implicites non documentés

Plusieurs règles sont probablement “cachées” dans :

- entités ;
- contrôleurs ;
- templates ;
- exports ;
- services d’appoint.

Un inventaire réel des règles métier par diplôme est indispensable avant les lots 3 et 4.

---

## Cible minimale recommandée pour un premier incrément

Si vous voulez un premier chantier à forte valeur, je recommande :

1. **Créer `MaquetteTree` / `MaquetteNode`**.
2. **Construire ce tree depuis les entités actuelles**.
3. **Remplacer la gestion codée en dur des choix dans les exports par une traversée récursive générique**.
4. **Extraire les règles de résolution heures/MCCC/ECTS de `GetElementConstitutif` dans des services dédiés**.
5. **Introduire un `DiplomaProfileInterface` sans supprimer encore les handlers existants**.

C’est le meilleur rapport valeur/risque.

---

## Décision d’architecture proposée

### Socle

- arbre générique de nœuds ;
- attributs résolus ;
- moteur de règles ;
- schéma d’affichage.

### Variabilité

- profils de diplôme en PHP ;
- compléments de configuration en base si nécessaire ;
- aucune hypothèse forte sur la hiérarchie dans le socle.

### Migration

- read model d’abord ;
- adaptateurs ensuite ;
- persistance native générique en dernier, si réellement utile.

---

## Fichiers actuels à traiter en priorité

- `src/Service/Parcours/GenereStructureParcours.php`
- `src/DTO/StructureSemestre.php`
- `src/DTO/StructureUe.php`
- `src/DTO/StructureEc.php`
- `src/Classes/GetElementConstitutif.php`
- `src/Entity/Ue.php`
- `src/Controller/ParcoursExportController.php`
- `src/TypeDiplome/Handler/UniversityHandler.php`
- `src/TypeDiplome/Handler/ButHandler.php`
- `src/TypeDiplome/Handler/M2EHandler.php`
- `src/TypeDiplome/Diplomes/*/ValideParcours*.php`
- `src/Service/Validation/SemesterValidationRefresher.php`

---

## Conclusion

Oui, votre besoin appelle clairement une architecture **orientée métamodèle + profils de diplôme**.

La bonne trajectoire n’est pas de multiplier encore les `if` par diplôme dans les entités, DTO, contrôleurs et
templates.
La bonne trajectoire est :

1. **un modèle canonique de maquette**,
2. **des politiques de résolution d’attributs**,
3. **des règles de validation composables**,
4. **des schémas d’affichage**,
5. **des profils de diplôme spécialisés**.

Cette approche vous permettra de gérer :

- les cas actuels ;
- les futurs diplômes ;
- les profondeurs de choix variables ;
- les règles de validation spécifiques ;
- les impacts UI / export ;
- sans rigidifier davantage le socle.

