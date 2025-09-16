<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(name: 'config:dump-import', description: 'Import configuration from YAML file')]
class ConfigDumpImportCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config = Yaml::parseFile(__DIR__ . '/../../config/dump_config.yml');
        foreach ($config as $key => $conf) {
            $entityClass = $conf['entity'] ?? null;
            $importFields = $conf['importFields'] ?? [];
            $filterFields = $conf['filterFields'] ?? [];
            if (!$entityClass || empty($importFields) || empty($filterFields)) {
                $output->writeln("<error>Invalid configuration for '$key' in dump_config.yml</error>");
                continue;
            }
            $dumpFile = __DIR__ . "/../../config/dump/{$key}.yml";
            if (!file_exists($dumpFile)) {
                $output->writeln("<comment>Skip: {$dumpFile} not found</comment>");
                continue;
            }
            $items = Yaml::parseFile($dumpFile);
            $repo = $this->entityManager->getRepository($entityClass);
            $imported = 0;
            foreach ($items as $item) {
                $criteria = [];
                foreach ($filterFields as $field) {
                    if (isset($item[$field])) {
                        $criteria[$field] = $item[$field];
                    }
                }
                $entity = $repo->findOneBy($criteria);
                if (!$entity) {
                    $entity = new $entityClass();
                }
                foreach ($importFields as $field) {
                    $setter = 'set' . ucfirst($field);
                    if (method_exists($entity, $setter) && isset($item[$field])) {
                        $entity->$setter($item[$field]);
                    }
                }
                $this->entityManager->persist($entity);
                $imported++;
            }
            $this->entityManager->flush();
            $output->writeln("<info>Imported {$imported} items for {$key}</info>");
        }
        return Command::SUCCESS;
    }
}
