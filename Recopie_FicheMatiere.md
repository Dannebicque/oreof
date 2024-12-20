# Recopie des données sur les Fiches Matières

## Mise en place de la base de données

La recopie des données sur les fiches matières nécessite
une base de données de destination.

On peut dupliquer la base de données, ou bien créer un dump
et le restaurer pour créer la nouvelle base.

### Configuration de l'URL de la nouvelle base de données

Pour que symfony puisse accéder à cette nouvelle base de données,
il faut configurer l'URL dans le fichier **.env.local** :

`PARCOURS_COPY_DATABASE_URL=<URL SYMFONY>`

Une fois la base de données de destination mise en place, il faut créer
les nouvelles colonnes qui mémorisent les données spécifiques.

### Nouvelles colonnes spécifiques

Ces colonnes sont à créer **sur les deux bases de données (source et résultat)**.

`ALTER TABLE element_constitutif
ADD heures_specifiques TINYINT(1) DEFAULT NULL,
ADD mccc_specifiques TINYINT(1) DEFAULT NULL,
ADD ects_specifiques TINYINT(1) DEFAULT NULL;`

## Lancement de la copie

Une fois la base de données mise en place, on peut lancer la commande
qui se chargera de la copie.

`php bin/console app:parcours-copy-data --test-copy-database`

Des barres de chargement indiquent l'avancée du processus.

## Tests de cohérence du résultat

Sur la base de données de destination, les données sont récupérées en priorité sur la fiche matière.

3 nouvelles méthodes ont été créées :

- `GetElementConstitutif::getFicheMatiereHeures()`
- `GetElementConstitutif::getFicheMatiereEcts()`
- `GetElementConstitutif::getMcccsFromFicheMatiere()`

Les comparaisons se font, pour les même éléments, entre les données habituelles et les nouvelles données issues de ces fonctions pour le résultat.

### Base de données entière

Une commande est disponible pour comparer la source et le résultat de la copie.
Il faut préciser la donnée que l'on souhaite comparer, avec comme choix possibles : **hours**, **ects**, **mccc**

Si des erreurs sont constatées, les parcours sont affichés
en fin d'exécution.

Ainsi, les commandes sont :

#### Heures

`php bin/console app:parcours-copy-data --compare-two-databases hours`

#### ECTS

`php bin/console app:parcours-copy-data --compare-two-databases ects`

#### MCCC

`php bin/console app:parcours-copy-data --compare-two-databases mccc`

### Parcours unique

Les commandes suivantes permettent de comparer la source et le résultat
d'un seul parcours.

Il faut fournir l'identifiant du parcours pour cette commande.

Il n'est pas nécessaire de préciser l'option `--from-copy` pour les MCCC ou les ECTS

#### Heures

`php bin/console app:parcours-copy-data --compare-two-dto <ID PARCOURS> --from-copy`

#### ECTS

`php bin/console app:parcours-copy-data --compare-two-dto <ID PARCOURS> --ects`

#### MCCC

`php bin/console app:parcours-copy-data --compare-two-dto <ID PARCOURS> --mccc`

## Correction manuelle des erreurs

Si après la copie il existe des incohérences et qu'elles ne sont pas trop nombreuses, il est possible de les rectifier manuellement en base de données.

## Export d'une maquette depuis la copie

Il est possible d'exporter au format PDF la maquette d'un parcours,
à l'aide la commande :

`php bin/console app:parcours-copy-data --dto-pdf-export <ID PARCOURS>`

Pour obtenir ce fichier à partir de la base de données de copie, il faut rajouter l'option `--from-copy`

Par exemple, pour la maquette du parcours 405 issu de la copie la commande est :

`php bin/console app:parcours-copy-data --dto-pdf-export 405 --from-copy`

Le fichier est généré dans le dossier `export` à la racine de l'application.

On peut alors comparer la source et le résultat manuellement
