<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(name: 'config:dump', description: 'Dump configuration table as YAML')]
class ConfigDumpCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private KernelInterface $kernel,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config = Yaml::parseFile($this->kernel->getProjectDir() . '/config/dump_config.yml');
        $dumpDir = $this->kernel->getProjectDir() . '/config/dump';
        if (!is_dir($dumpDir)) {
            mkdir($dumpDir, 0777, true);
        }
        foreach ($config as $key => $conf) {
            $entityClass = $conf['entity'] ?? null;
            $fields = $conf['importFields'] ?? [];
            $relations = $conf['relations'] ?? [];
            if (!$entityClass || empty($fields)) {
                $output->writeln("<error>Invalid configuration for '$key' in dump_config.yml</error>");
                continue;
            }
            $repo = $this->entityManager->getRepository($entityClass);
            $all = $repo->findAll();
            $data = [];
            foreach ($all as $item) {
                $row = [];
                foreach ($fields as $field) {
                    $getter = 'get' . ucfirst($field);
                    $row[$field] = method_exists($item, $getter) ? $item->$getter() : null;
                }
                if ($relations) {
                    foreach ($relations as $referenceField => $referenceEntityConf) {
                        $getter = 'get' . ucfirst($referenceField);
                        $referenceEntities = method_exists($item, $getter) ? $item->$getter() : null;
                        $row[$referenceField] = [];
                        if ($referenceEntities) {
                            // Handle both single and multiple relations
                            if ($referenceEntities instanceof \Traversable || is_array($referenceEntities)) {
                                foreach ($referenceEntities as $referenceEntity) {
                                    $referenceEntityData = [];
                                    foreach ($referenceEntityConf['fields'] ?? [] as $referenceEntityField) {
                                        $fieldGetter = 'get' . ucfirst($referenceEntityField);
                                        $referenceEntityData[$referenceEntityField] =
                                            method_exists($referenceEntity, $fieldGetter) ?
                                            $referenceEntity->$fieldGetter() : null;
                                    }
                                    $row[$referenceField][] = $referenceEntityData;
                                }
                            } else {
                                // Single relation (not a collection)
                                $referenceEntityData = [];
                                foreach ($referenceEntityConf['fields'] ?? [] as $referenceEntityField) {
                                    $fieldGetter = 'get' . ucfirst($referenceEntityField);
                                    $referenceEntityData[$referenceEntityField] =
                                        method_exists($referenceEntities, $fieldGetter) ?
                                        $referenceEntities->$fieldGetter() : null;
                                }
                                $row[$referenceField][] = $referenceEntityData;
                            }
                        }
                    }
                }
                $data[] = $row;
            }
            $yaml = Yaml::dump($data, 2);
            $dumpFile = $dumpDir . "/{$key}.yml";
            file_put_contents($dumpFile, $yaml);
            $output->writeln("<info>Configuration dumped to config/dump/{$key}.yml</info>");
        }
        return Command::SUCCESS;
    }
}
