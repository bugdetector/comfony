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
            $relations = $conf['relations'] ?? [];

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

                // Set basic fields
                foreach ($importFields as $field) {
                    $setter = 'set' . ucfirst($field);
                    if (method_exists($entity, $setter) && isset($item[$field])) {
                        $entity->$setter($item[$field]);
                    }
                }

                // Handle relations
                if ($relations) {
                    foreach ($relations as $relationField => $relationConf) {
                        if (!isset($item[$relationField]) || !is_array($item[$relationField])) {
                            continue;
                        }

                        $relationEntityClass = $relationConf['entity'] ?? null;
                        $relationFields = $relationConf['fields'] ?? [];
                        $relationFilterFields = $relationConf['filterFields'] ?? $relationFields;

                        if (!$relationEntityClass || empty($relationFields)) {
                            continue;
                        }

                        $relationRepo = $this->entityManager->getRepository($relationEntityClass);
                        $relatedEntities = [];

                        foreach ($item[$relationField] as $relationData) {
                            if (!is_array($relationData)) {
                                continue;
                            }

                            // Find or create related entity
                            $relationCriteria = [];
                            foreach ($relationFilterFields as $filterField) {
                                if (isset($relationData[$filterField])) {
                                    $relationCriteria[$filterField] = $relationData[$filterField];
                                }
                            }

                            $relatedEntity = null;
                            if (!empty($relationCriteria)) {
                                $relatedEntity = $relationRepo->findOneBy($relationCriteria);
                            }

                            if (!$relatedEntity) {
                                $relatedEntity = new $relationEntityClass();
                            }

                            // Set fields on related entity
                            foreach ($relationFields as $relationFieldName) {
                                $relationSetter = 'set' . ucfirst($relationFieldName);
                                if (
                                    method_exists($relatedEntity, $relationSetter) &&
                                    isset($relationData[$relationFieldName])
                                ) {
                                    $relatedEntity->$relationSetter($relationData[$relationFieldName]);
                                }
                            }
                            $relatedEntities[] = $relatedEntity;
                        }

                        // Set the relation on main entity
                        if (!empty($relatedEntities)) {
                            $relationSetter = 'set' . ucfirst($relationField);
                            $relationAdder = 'add' . ucfirst(rtrim($relationField, 's')); // Remove 's' for adder method
                            $relationClearer = 'clear' . ucfirst($relationField);

                            if (method_exists($entity, $relationClearer)) {
                                // Collection relation - clear and add all
                                $entity->$relationClearer();
                            }
                            if (method_exists($entity, $relationSetter) && count($relatedEntities) === 1) {
                                // Single relation - set the first (and only) entity
                                $entity->$relationSetter($relatedEntities[0]);
                            }
                            foreach ($relatedEntities as $relatedEntity) {
                                if (method_exists($entity, $relationAdder)) {
                                    $entity->$relationAdder($relatedEntity);
                                }
                            }
                        }
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
