-- HEURES SPECIFIQUES

-- DE-IPA - Matière d'anglais UE 3.1
UPDATE element_constitutif
SET heures_specifiques = 1
WHERE id IN (38916, 38921, 38926);

-- Parcours 159 - UE 1.5 EC 1/2/3.D
-- Modification des heures de la fiche matière
-- et mise en spécifique de l'élément restant
UPDATE fiche_matiere
SET volume_cm_presentiel = 6,
	volume_td_presentiel = 0
WHERE id = 1685;

UPDATE element_constitutif
SET heures_specifiques = 1
WHERE id = 37794;

UPDATE element_constitutif
SET heures_specifiques = NULL
WHERE id IN (37774, 37781, 37788);

-- Parcours 113
UPDATE fiche_matiere 
SET volume_td_presentiel = 16.5,
	volume_cm_presentiel = 12.5
WHERE fiche_matiere.id = 1346;

UPDATE element_constitutif
SET heures_specifiques = NULL
WHERE id = 31750;

-- Parcours 159 - Fiche matière 1069
UPDATE element_constitutif
SET volume_cm_presentiel = 1,
	volume_td_presentiel = 19,
	volume_te = 4
WHERE id IN (37773, 37780, 37787);

-- Parcours 159 - Fiche matière 1096
UPDATE element_constitutif
SET volume_td_presentiel = 5,
	volume_tp_presentiel = 3,
	heures_specifiques = 1
WHERE id IN (37772, 37779, 37786);

-- Parcours 425 - Fiche matière 10079
UPDATE element_constitutif
SET volume_cm_presentiel = 18,
	volume_td_presentiel = 2,
	volume_te = 210
WHERE id = 30106;

-- Parcours 425 - Fiche matière 10081
UPDATE element_constitutif
SET volume_cm_presentiel = 16,
	volume_td_presentiel = 4,
	heures_specifiques = 1
WHERE id = 30107;



-- ECTS SPECIFIQUES

-- Semestre 2 Anglais Maison des Langues - Fiche Matière ID 16951
-- EC correspondant en ECTS spécifiques
UPDATE element_constitutif
SET ects_specifiques = 1
WHERE id IN (33712, 33963, 36465, 38625, 38627);

-- Matière d'anglais UE 3.1 - DE IPA
UPDATE fiche_matiere
SET ects = 3
WHERE id = 20425;

-- Matière Biotechnologie Santé - Parcours 113 : Biochimie 
UPDATE fiche_matiere 
SET ects = 3 
WHERE fiche_matiere.id = 1270;

UPDATE element_constitutif
SET ects_specifiques = NULL
WHERE id IN (1898, 1900, 2093, 38411);

-- Matière Didactique de la langue Française (LETTRES) - FM ID 1044
-- & Matière Littérature Jeunesse - FM ID 1045
-- & Matière Français oral/écrit : les registres de langue - FM ID 1052
UPDATE fiche_matiere
SET ects = 3
WHERE id IN (1044, 1045, 1052);

UPDATE element_constitutif
SET ects_specifiques = NULL
WHERE id IN (6517, 25452, 27714, 6518, 25453, 27715, 1393, 6523, 27719);

-- Matière Connaissance des publics et du système éducatif
-- FM ID : 16352
UPDATE fiche_matiere 
SET ects = 3
WHERE fiche_matiere.id = 16352;