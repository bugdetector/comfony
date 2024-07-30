<?php

namespace App\Command;

use App\Entity\File\FileStatus;
use App\Repository\FileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zenstruck\ScheduleBundle\Attribute\AsScheduledTask;

#[AsCommand(
    name: 'app:clear-temporary-files',
    description: 'Clears expired temporary files (2 hours)',
)]
#[AsScheduledTask('#hourly', 'Clears expired temporary files (2 hours)')]
class ClearTemporaryFilesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FileRepository $fileRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $expiredFiles = $this->fileRepository->createQueryBuilder("f")
            ->where('f.status IN (:statuses)')
            ->andWhere('f.updatedAt <= :two_hours_ago')
            ->setParameter('statuses', [
                FileStatus::Temporary,
                FileStatus::Deleted,
            ])
            ->setParameter('two_hours_ago', date('Y-m-d H:i:s', strtotime("-2 hours")))
            ->getQuery()->execute();

        $successCount = 0;
        foreach ($expiredFiles as $file) {
            try {
                $this->entityManager->remove($file);
                $this->entityManager->flush();
                $successCount++;
            } catch (Exception $ex) {
                $io->error($ex->getMessage());
            }
        }

        $io->success("Removed {$successCount} files");

        return Command::SUCCESS;
    }
}
