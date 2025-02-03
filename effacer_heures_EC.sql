-- Suppression des heures des EC (après copie)
-- en excluant les BUT
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
AND heures_enfants_identiques != 1
AND parcours_id IN (SELECT id
                    FROM parcours
                    WHERE formation_id IN (SELECT id
                                           FROM formation
                                           WHERE type_diplome_id IN (SELECT id
                                                                     FROM type_diplome
                                                                     WHERE libelle_court != "BUT"
                                                                    )
                                        )
                    );

-- Suppression des ECTS des EC (après copie)
-- en excluant les BUT
UPDATE element_constitutif
SET ects = NULL
WHERE fiche_matiere_id IS NOT NULL
AND ects_specifiques IS NULL
AND id NOT IN (SELECT ec_parent_id 
			   FROM element_constitutif
			   WHERE ec_parent_id IS NOT NULL
			   )
AND parcours_id IN (SELECT id
                    FROM parcours
                    WHERE formation_id IN (SELECT id
                                           FROM formation
                                           WHERE type_diplome_id IN (SELECT id
                                                                     FROM type_diplome
                                                                     WHERE libelle_court != "BUT"
                                                                    )
                                        )
                    );

-- Suppression des MCCC des EC (après copie)
-- en excluant les BUT
UPDATE mccc
SET ec_id = NULL
WHERE ec_id IN (SELECT id
                FROM element_constitutif
                WHERE mccc_specifiques IS NULL
                AND mccc_enfants_identique != 1
                AND fiche_matiere_id IS NOT NULL
               )
AND ec_id IN (SELECT id 
              FROM element_constitutif
              WHERE parcours_id IN (SELECT id
                                    FROM parcours
                                    WHERE formation_id IN (SELECT id
                                                           FROM formation
                                                           WHERE type_diplome_id IN (SELECT id 
                                                                                     FROM type_diplome
                                                                                     WHERE libelle_court != "BUT"
                                                                                    )
                                                          )
                                   )
             );