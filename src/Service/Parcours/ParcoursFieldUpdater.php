<?php

namespace App\Service\Parcours;

use App\Entity\Parcours;
use App\Enums\ModaliteEnseignementEnum;
use App\Enums\NiveauLangueEnum;
use App\Enums\RegimeInscriptionEnum;
use App\Repository\VilleRepository;
use App\Repository\UserRepository;
use App\Repository\RythmeFormationRepository;
use App\Repository\ComposanteRepository;

final readonly class ParcoursFieldUpdater
{
    public function __construct(
        private UserRepository            $userRepo,
        private VilleRepository           $villeRepo,
        private RythmeFormationRepository $rythmeRepo,
        private ComposanteRepository      $composanteRepo,
    )
    {
    }

    public function applyForTab(Parcours $parcours, string $tabKey, string $field, mixed $value): void
    {
        $allowed = $this->allowedFieldsByTab();
        if (!\in_array($field, $allowed[$tabKey] ?? [], true)) {
            throw new \InvalidArgumentException("Field not allowed for tab $tabKey: $field");
        }

        $handlers = $this->handlers();
        if (!isset($handlers[$field])) {
            throw new \InvalidArgumentException('Field not supported for autosave: ' . $field);
        }

        $handlers[$field]($parcours, $value);
    }

    /** @return array<string, callable(Parcours, mixed): void> */
    private function handlers(): array
    {
        return [
            // ----------------- STEP 1 (presentation) -----------------
            'parcours_step1[respParcours]' => function (Parcours $p, $v) {
                $p->setRespParcours($this->toEntity($this->userRepo, $v));
            },
            'parcours_step1[coResponsable]' => function (Parcours $p, $v) {
                $p->setCoResponsable($this->toEntity($this->userRepo, $v));
            },
            'parcours_step1[objectifsParcours]' => fn(Parcours $p, $v) => $p->setObjectifsParcours($this->toString($v)),
            'parcours_step1[motsCles]' => fn(Parcours $p, $v) => $p->setMotsCles($this->toString($v)),
            'parcours_step1[resultatsAttendus]' => fn(Parcours $p, $v) => $p->setResultatsAttendus($this->toString($v)),
            'parcours_step1[contenuFormation]' => fn(Parcours $p, $v) => $p->setContenuFormation($this->toString($v)),
            'parcours_step1[rythmeFormation]' => function (Parcours $p, $v) {
                $p->setRythmeFormation($this->toEntity($this->rythmeRepo, $v));
            },
            'parcours_step1[rythmeFormationTexte]' => fn(Parcours $p, $v) => $p->setRythmeFormationTexte($this->toString($v)),
            'parcours_step1[localisation]' => function (Parcours $p, $v) {
                $p->setLocalisation($this->toEntity($this->villeRepo, $v));
            },

            // ----------------- STEP 2 (descriptif) -----------------
            // EnumType : si ton setter attend un enum, adapte via toEnum()
            'parcours_step2[modalitesEnseignement]' => function (Parcours $p, $v) {
                $p->setModalitesEnseignement($this->toEnumOrNull(ModaliteEnseignementEnum::class, $v));
            },
            // YesNoType -> bool|null
            'parcours_step2[hasStage]' => fn(Parcours $p, $v) => $p->setHasStage($this->toBoolOrNull($v)),
            'parcours_step2[stageText]' => fn(Parcours $p, $v) => $p->setStageText($this->toString($v)),
            'parcours_step2[nbHeuresStages]' => fn(Parcours $p, $v) => $p->setNbHeuresStages($this->toFloatOrNull($v)),

            'parcours_step2[hasProjet]' => fn(Parcours $p, $v) => $p->setHasProjet($this->toBoolOrNull($v)),
            'parcours_step2[projetText]' => fn(Parcours $p, $v) => $p->setProjetText($this->toString($v)),
            'parcours_step2[nbHeuresProjet]' => fn(Parcours $p, $v) => $p->setNbHeuresProjet($this->toFloatOrNull($v)),

            'parcours_step2[hasMemoire]' => fn(Parcours $p, $v) => $p->setHasMemoire($this->toBoolOrNull($v)),
            'parcours_step2[memoireText]' => fn(Parcours $p, $v) => $p->setMemoireText($this->toString($v)),

            'parcours_step2[hasSituationPro]' => fn(Parcours $p, $v) => $p->setHasSituationPro($this->toBoolOrNull($v)),
            'parcours_step2[nbHeuresSituationPro]' => fn(Parcours $p, $v) => $p->setNbHeuresSituationPro($this->toFloatOrNull($v)),
            'parcours_step2[situationProText]' => fn(Parcours $p, $v) => $p->setSituationProText($this->toString($v)),

            // ----------------- STEP 3 (Structure maquette) -----------------
            'parcours_step3[semestreDebut]' => fn(Parcours $p, $v) => $p->setSemestreDebut($this->toIntOrNull($v)),
            'parcours_step3[semestreFin]' => fn(Parcours $p, $v) => $p->setSemestreFin($this->toIntOrNull($v)),

            // ----------------- STEP 5 (admission) -----------------
            'parcours_step5[niveauFrancais]' => function (Parcours $p, $v) {
                $p->setNiveauFrancais($this->toEnumOrNull(NiveauLangueEnum::class, $v));
            },
            'parcours_step5[prerequis]' => fn(Parcours $p, $v) => $p->setPrerequis($this->toString($v)),
            'parcours_step5[composanteInscription]' => function (Parcours $p, $v) {
                $p->setComposanteInscription($this->toEntity($this->composanteRepo, $v));
            },
            // regimeInscription[] : array de valeurs (checkboxes)
            'parcours_step5[regimeInscription][]' => function (Parcours $p, $v) {
                $p->setRegimeInscription($this->toEnumArray(RegimeInscriptionEnum::class, $v));
            },
            'parcours_step5[modalitesAlternance]' => fn(Parcours $p, $v) => $p->setModalitesAlternance($this->toString($v)),
            'parcours_step5[coordSecretariat]' => fn(Parcours $p, $v) => $p->setCoordSecretariat($this->toString($v)),
            'parcours_step5[modalitesAdmission]' => fn(Parcours $p, $v) => $p->setModalitesAdmission($this->toString($v)),

            // ----------------- STEP 6 (et_apres) -----------------
            'parcours_step6[poursuitesEtudes]' => fn(Parcours $p, $v) => $p->setPoursuitesEtudes($this->toString($v)),
            'parcours_step6[debouches]' => fn(Parcours $p, $v) => $p->setDebouches($this->toString($v)),
        ];
    }

    /** @return array<string, string[]> */
    private function allowedFieldsByTab(): array
    {
        return [
            'presentation' => [
                'parcours_step1[respParcours]',
                'parcours_step1[coResponsable]',
                'parcours_step1[objectifsParcours]',
                'parcours_step1[motsCles]',
                'parcours_step1[resultatsAttendus]',
                'parcours_step1[contenuFormation]',
                'parcours_step1[rythmeFormation]',
                'parcours_step1[rythmeFormationTexte]',
                'parcours_step1[localisation]',
            ],
            'descriptif' => [
                'parcours_step2[modalitesEnseignement]',
                'parcours_step2[hasStage]',
                'parcours_step2[stageText]',
                'parcours_step2[nbHeuresStages]',
                'parcours_step2[hasProjet]',
                'parcours_step2[projetText]',
                'parcours_step2[nbHeuresProjet]',
                'parcours_step2[hasMemoire]',
                'parcours_step2[memoireText]',
                'parcours_step2[hasSituationPro]',
                'parcours_step2[nbHeuresSituationPro]',
                'parcours_step2[situationProText]',
            ],
            'maquette' => [
                'parcours_step3[semestreDebut]',
                'parcours_step3[semestreFin]',
            ],
            'admission' => [
                'parcours_step5[niveauFrancais]',
                'parcours_step5[prerequis]',
                'parcours_step5[composanteInscription]',
                'parcours_step5[regimeInscription][]',
                'parcours_step5[modalitesAlternance]',
                'parcours_step5[coordSecretariat]',
                'parcours_step5[modalitesAdmission]',
            ],
            'et_apres' => [
                'parcours_step6[poursuitesEtudes]',
                'parcours_step6[debouches]',
            ],
        ];
    }

    // ----------------- helpers -----------------

    private function toString(mixed $v): string
    {
        return \is_string($v) ? $v : (string)($v ?? '');
    }

    private function toStringOrNull(mixed $v): ?string
    {
        $s = $this->toString($v);
        return trim($s) === '' ? null : $s;
    }

    private function toFloatOrNull(mixed $v): ?float
    {
        if ($v === null) {
            return null;
        }
        if (\is_string($v) && trim($v) === '') {
            return null;
        }
        return (float)$v;
    }

    private function toBoolOrNull(mixed $v): ?bool
    {
        if ($v === null) {
            return null;
        }
        if (\is_string($v) && trim($v) === '') {
            return null;
        }

        $true = [true, 1, '1', 'on', 'true', 'yes', 'oui'];
        $false = [false, 0, '0', 'off', 'false', 'no', 'non'];

        if (\in_array($v, $true, true)) {
            return true;
        }
        if (\in_array($v, $false, true)) {
            return false;
        }

        return null;
    }

    private function toArray(mixed $v): array
    {
        if (\is_array($v)) {
            return array_values(array_filter($v, fn($x) => $x !== null && $x !== ''));
        }
        if (!\is_string($v)) {
            return [];
        }

        $s = trim($v);
        if ($s === '') {
            return [];
        }

        // accepte JSON
        if ($s[0] === '[') {
            $decoded = json_decode($s, true);
            return \is_array($decoded) ? array_values($decoded) : [];
        }

        // accepte CSV "A,B,C"
        return array_values(array_filter(array_map('trim', explode(',', $s))));
    }

    private function toEntity(object $repo, mixed $v): ?object
    {
        if ($v === null) {
            return null;
        }
        if (\is_string($v) && trim($v) === '') {
            return null;
        }

        $id = (int)$v;
        if ($id <= 0) {
            return null;
        }

        // repo Doctrine classique : find($id)
        return $repo->find($id);
    }

    private function toEnumOrNull(string $enumClass, mixed $v): ?\BackedEnum
    {
        if ($v === null) {
            return null;
        }
        if (\is_string($v) && trim($v) === '') {
            return null;
        }

        if (!\is_subclass_of($enumClass, \BackedEnum::class)) {
            throw new \InvalidArgumentException("$enumClass is not a BackedEnum");
        }

        /** @var class-string<\BackedEnum> $enumClass */
        return $enumClass::tryFrom((string)$v);
    }

    /**
     * @return array<\BackedEnum>
     */
    private function toEnumArray(string $enumClass, mixed $v): array
    {
        $values = $this->toArray($v); // JSON array / array / csv

        if (!\is_subclass_of($enumClass, \BackedEnum::class)) {
            throw new \InvalidArgumentException("$enumClass is not a BackedEnum");
        }

        /** @var class-string<\BackedEnum> $enumClass */
        $out = [];
        foreach ($values as $raw) {
            if ($raw === null || $raw === '') {
                continue;
            }
            $e = $enumClass::tryFrom((string)$raw);
            if ($e !== null) {
                $out[] = $e;
            }
        }

        // Unicité
        $uniq = [];
        foreach ($out as $e) {
            $uniq[$e->value] = $e;
        }

        return array_values($uniq);
    }

    private function toIntOrNull(mixed $v): ?int
    {
        if ($v === null) {
            return null;
        }
        if (\is_string($v) && trim($v) === '') {
            return null;
        }
        return (int)$v;
    }

}
