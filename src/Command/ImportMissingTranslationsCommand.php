<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Command/ImportMissingTranslationsCommand.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 15/02/2026 13:59
 */

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(name: 'app:translations:import-missing', description: 'Importe les traductions manquantes depuis un fichier de log et ajoute des entrées dans les fichiers translations/*.{locale}.yaml')]
final class ImportMissingTranslationsCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('logfile', InputArgument::OPTIONAL, 'Chemin du fichier de log à parser', '%kernel.project_dir%/var/log/dev.translations-2026-02-15.log')
            ->addOption('translations-dir', null, InputOption::VALUE_REQUIRED, 'Répertoire des fichiers de traduction', '%kernel.project_dir%/translations')
            ->addOption('simulate', null, InputOption::VALUE_NONE, 'Ne pas écrire les fichiers, afficher les changements')
            ->addOption('overwrite', null, InputOption::VALUE_NONE, 'Écraser les clés existantes (attention)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $logfile = str_replace('%kernel.project_dir%', getcwd(), $input->getArgument('logfile'));
        $translationsDir = str_replace('%kernel.project_dir%', getcwd(), $input->getOption('translations-dir'));
        $simulate = (bool)$input->getOption('simulate');
        $overwrite = (bool)$input->getOption('overwrite');

        if (!file_exists($logfile)) {
            $io->error('Fichier de log introuvable: ' . $logfile);
            return Command::FAILURE;
        }

        if (!is_dir($translationsDir)) {
            $io->warning('Répertoire de traductions introuvable, création: ' . $translationsDir);
            if (!$simulate) {
                if (!mkdir($translationsDir, 0775, true) && !is_dir($translationsDir)) {
                    $io->error('Impossible de créer le répertoire de traductions: ' . $translationsDir);
                    return Command::FAILURE;
                }
            }
        }

        $content = file_get_contents($logfile);
        $lines = preg_split('/\r?\n/', $content);

        $missing = []; // keyed by domain.locale => set of keys

        foreach ($lines as $line) {
            if (!trim($line)) continue;
            // cherche le JSON-like context {"id":"...","domain":"...","locale":"fr"}
            if (preg_match('/\{"id":"(?P<id>[^\"]+)","domain":"(?P<domain>[^\"]*)","locale":"(?P<locale>[^\"]+)"}/', $line, $m)) {
                $id = $m['id'];
                $domain = $m['domain'] ?: 'messages';
                $locale = $m['locale'] ?: 'fr';
                $key = $domain . '.' . $locale;
                $missing[$key][$id] = $id; // valeur = id for now
            }
        }

        if (count($missing) === 0) {
            $io->warning('Aucune clé manquante trouvée dans le fichier de log.');
            return Command::SUCCESS;
        }

        foreach ($missing as $dk => $keys) {
            [$domain, $locale] = explode('.', $dk, 2);
            $fileCandidates = [
                $translationsDir . DIRECTORY_SEPARATOR . $domain . "." . $locale . ".yaml",
                $translationsDir . DIRECTORY_SEPARATOR . $domain . "." . $locale . ".yml",
            ];

            $file = null;
            foreach ($fileCandidates as $c) {
                if (file_exists($c)) {
                    $file = $c;
                    break;
                }
            }

            if ($file === null) {
                // use first candidate
                $file = $fileCandidates[0];
                $existing = [];
            } else {
                $existing = Yaml::parseFile($file) ?: [];
            }

            $toAdd = [];
            foreach ($keys as $id => $val) {
                if (array_key_exists($id, $existing) && !$overwrite) {
                    continue;
                }
                $toAdd[$id] = $val;
            }

            if (count($toAdd) === 0) {
                $io->text(sprintf('Aucune nouvelle clé à ajouter pour %s (%s)', $domain, $locale));
                continue;
            }

            $io->section(sprintf('%s (%s) -> %s : %d clés à ajouter', $domain, $locale, $file, count($toAdd)));

            // merge and sort
            $merged = $existing + $toAdd;
            ksort($merged);

            if ($simulate) {
                $io->listing(array_map(fn($k) => $k, array_keys($toAdd)));
            } else {
                // backup du fichier existant avant écriture
                if (file_exists($file)) {
                    $backupFile = $file . '.bak.' . date('Ymd_His');
                    if (!copy($file, $backupFile)) {
                        $io->error(sprintf('Impossible de créer la sauvegarde du fichier %s', $file));
                        return Command::FAILURE;
                    }
                    $io->text(sprintf('Backup créé : %s', $backupFile));
                }

                $yaml = Yaml::dump($merged, 2, 4, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
                file_put_contents($file, $yaml . "\n");
                $io->success(sprintf('Fichier mis à jour : %s', $file));
            }
        }

        $io->success('Import terminé.');
        return Command::SUCCESS;
    }
}

