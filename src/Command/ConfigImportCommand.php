<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\PhpSubprocess;

#[AsCommand(
    name: 'config:import',
    description: 'Import database structure depending on entity definition.',
)]
class ConfigImportCommand extends Command
{
    private OutputStyle $io;

    public function __construct(
        private KernelInterface $appKernel,
        private Filesystem $filesystem,
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('max_execution_time', 0);
        $this->io = new SymfonyStyle($input, $output);

        $process = new PhpSubprocess([
            'bin/console',
            'doctrine:migrations:migrate',
            '--no-interaction',
            '--allow-no-migration'
        ]);
        $process->run();
        if ($process->isSuccessful()) {
            $this->io->success($process->getOutput());
        } else {
            $this->io->error($process->getOutput());
        }

        $process = new PhpSubprocess(['bin/console', 'doctrine:schema:update', '--complete', '--dump-sql']);
        $process->run();
        $sqlToUpdateDb = $process->getOutput();

        if ($sqlToUpdateDb) {
            $this->entityManager->getConnection()->executeQuery($sqlToUpdateDb);
        } else {
            $this->io->success(
                'Nothing to update - your database is already in sync with the current entity metadata.'
            );
        }

        $process = new PhpSubprocess(['bin/console', 'config:dump-import']);
        $process->run();
        if ($process->isSuccessful()) {
            $this->io->success($process->getOutput());
        } else {
            $this->io->error($process->getOutput());
        }

        $this->io->success('Table structure imported successfully.');

        return Command::SUCCESS;
    }
}
