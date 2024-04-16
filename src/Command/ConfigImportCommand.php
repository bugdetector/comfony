<?php

namespace App\Command;

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
        private Filesystem $filesystem
    ) {
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $process = new PhpSubprocess(['bin/console', 'make:migration']);
        $process->run();

        $process = new PhpSubprocess(['bin/console', 'doctrine:migration:migrate']);
        $process->run();

        $this->clearMigrationsDirectory($output);

        $this->io->success('Table structure imported successfully.');

        return Command::SUCCESS;
    }

    private function clearMigrationsDirectory(OutputInterface $output)
    {
        $directory = $this->appKernel->getProjectDir() . "/migrations/";
        $finder = new Finder();
        $finder->files()->name('Version*');
        foreach ($finder->in($directory) as $file) {
            $this->filesystem->remove($file->getPathname());
        }
    }
}
