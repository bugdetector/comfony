<?php

namespace App\Command;

use App\Entity\File\FileStatus;
use App\Repository\FileRepository;
use App\Service\FileManager;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:compress-image-files',
    description: 'Compress uncompressed image files',
)]
class CompressImageFilesCommand extends Command
{
    public function __construct(
        private FileRepository $fileRepository,
        private FileManager $fileManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $criteria = new Criteria();
        $criteria->where(Criteria::expr()->eq('status', FileStatus::Permanent));
        $criteria->andWhere(Criteria::expr()->startsWith('mime_type', 'image/'));
        $criteria->andWhere(Criteria::expr()->eq('compressed', false));
        $files = $this->fileRepository->matching($criteria);

        foreach ($files as $file) {
            $this->fileManager->compressImage($file);
            $io->success('Compressed file ' . $file->getFileName());
        }

        $io->success($files->count() . ' files compressed.');

        return Command::SUCCESS;
    }
}
