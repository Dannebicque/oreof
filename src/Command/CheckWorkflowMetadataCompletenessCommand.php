<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/oreofv2/src/Command/CheckWorkflowMetadataCompletenessCommand.php
 * @author davidannebicque
 * @project oreofv2
 * @lastUpdate 09/05/2026 08:11
 */

declare(strict_types=1);

namespace App\Command;

use App\Classes\AbstractValidationProcess;
use App\Entity\ChangeRf;
use App\Entity\DpeParcours;
use App\Entity\FicheMatiere;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Workflow\Registry;

#[AsCommand(
    name: 'app:workflow:check-metadata-completeness',
    description: 'Verifie la completude des metadonnees workflow (places/transitions).'
)]
final class CheckWorkflowMetadataCompletenessCommand extends Command
{
    public function __construct(
        private readonly Registry $workflowRegistry,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $workflowChecks = [
            [
                'name' => 'dpeParcours',
                'subject' => new DpeParcours(),
            ],
            [
                'name' => 'fiche',
                'subject' => new FicheMatiere(),
            ],
            [
                'name' => 'changeRf',
                'subject' => new ChangeRf(),
            ],
        ];

        $issues = [];

        foreach ($workflowChecks as $workflowCheck) {
            $workflowName = $workflowCheck['name'];
            $workflow = $this->workflowRegistry->get($workflowCheck['subject'], $workflowName);
            $definition = $workflow->getDefinition();
            $metadataStore = $workflow->getMetadataStore();

            foreach (array_keys($definition->getPlaces()) as $placeName) {
                $meta = $metadataStore->getPlaceMetadata($placeName);
                $missingKeys = $this->findMissingKeys($meta, AbstractValidationProcess::REQUIRED_PLACE_META_KEYS);

                if ($missingKeys !== []) {
                    $issues[] = [
                        'workflow' => $workflowName,
                        'type' => 'place',
                        'name' => $placeName,
                        'missing' => implode(', ', $missingKeys),
                    ];
                }
            }

            foreach ($definition->getTransitions() as $transition) {
                $meta = $metadataStore->getTransitionMetadata($transition);
                $missingKeys = $this->findMissingKeys($meta, AbstractValidationProcess::REQUIRED_TRANSITION_META_KEYS);

                if ($missingKeys !== []) {
                    $issues[] = [
                        'workflow' => $workflowName,
                        'type' => 'transition',
                        'name' => $transition->getName(),
                        'missing' => implode(', ', $missingKeys),
                    ];
                }
            }
        }

        if ($issues === []) {
            $io->success('workflow.yaml est complet pour les cles obligatoires definies par ValidationProcess.');

            return Command::SUCCESS;
        }

        $io->warning(sprintf('%d element(s) incomplet(s) detecte(s) dans workflow.yaml.', count($issues)));
        $io->table(
            ['Workflow', 'Type', 'Element', 'Cles manquantes'],
            array_map(static fn(array $issue): array => [
                $issue['workflow'],
                $issue['type'],
                $issue['name'],
                $issue['missing'],
            ], $issues)
        );

        $io->note('Ajoutez les cles manquantes dans config/packages/workflow.yaml ou adaptez le contrat de metadonnees.');

        return Command::FAILURE;
    }

    /**
     * @param array<string, mixed> $metadata
     * @param array<int, string> $requiredKeys
     *
     * @return array<int, string>
     */
    private function findMissingKeys(array $metadata, array $requiredKeys): array
    {
        $presentKeys = array_keys($metadata);

        return array_values(array_diff($requiredKeys, $presentKeys));
    }
}

