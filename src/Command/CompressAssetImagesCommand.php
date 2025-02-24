<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'app:compress-asset-images',
    description: 'Compress asset images in given directory',
)]
class CompressAssetImagesCommand extends Command
{
    public function __construct(
        private KernelInterface $kernel,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('directory', InputArgument::REQUIRED, 'Directory Name in project folder');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $projectDir = $this->kernel->getProjectDir();
        $directory = $input->getArgument('directory');
        $compressPath = $projectDir . $directory;
        if (!is_dir($compressPath)) {
            $io->error('Directory not found');
            return Command::FAILURE;
        }

        $io->title('Searching images in ' . $directory);

        $finder = new Finder();
        $finder
            ->files()
            ->in($compressPath)
            ->name(['*.jpg', '*.jpeg', '*.png'])
            ->ignoreUnreadableDirs(true)
            ->ignoreDotFiles(true)
            ->depth('< 5');

        $excludePath = $projectDir . '/public/uploads';

        $relativeDirectory = str_replace($compressPath, '', $excludePath);
        $finder->exclude(str_replace('/', '', $relativeDirectory));

        $io->title('Found  images: ' . $finder->count());

        $io->title('Compressing images in ' . $directory);
        // iterator_to_array($finder)
        foreach ($finder as $imageInfo) {
            $imagePath = $imageInfo->getPathname();
            $image = imagecreatefromstring(file_get_contents($imagePath));
            if (!$image) {
                $io->error(
                    "Image create failed. Skipping." . $imagePath,
                );
                continue;
            }
            $mimeType = mime_content_type($imagePath);
            if ($mimeType == 'image/png') {
                imagesavealpha($image, true);
                imagepng($image, $imagePath, 9);
            } elseif ($mimeType == 'image/jpg') {
                imagejpeg($image, $imagePath, 75);
            }

            imagedestroy($image);

            $io->success('Compressed ' . $imagePath);
        }

        $io->success('Images compressed successfully. Total: ' . $finder->count());

        return Command::SUCCESS;
    }
}
