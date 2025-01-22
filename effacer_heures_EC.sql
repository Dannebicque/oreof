-- Suppression des heures des EC (après copie)
UPDATE element_constitutif
SET volume_cm_presentiel = null,
	volume_td_presentiel = null,
    volume_tp_presentiel = null,
    volume_cm_distanciel = null,
    volume_td_distanciel = null,
    volume_tp_distanciel = null,
    volume_te = null
WHERE heures_specifiques IS NULL
AND fiche_matiere_id IS NOT NULL
AND heures_enfants_identiques != 1;

-- Suppression des ECTS des EC (après copie)
UPDATE element_constitutif
SET ects = NULL
WHERE fiche_matiere_id IS NOT NULL
AND ects_specifiques IS NULL
AND id NOT IN (SELECT ec_parent_id 
			   FROM element_constitutif
			   WHERE ec_parent_id IS NOT NULL
			   );