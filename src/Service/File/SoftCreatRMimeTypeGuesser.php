<?php

namespace App\Service\File;

use SoftCreatR\MimeDetector\MimeDetector;
use Symfony\Component\Mime\MimeTypeGuesserInterface;

final class SoftCreatRMimeTypeGuesser implements MimeTypeGuesserInterface
{
    public function isGuesserSupported(): bool
    {
        return true;
    }

    public function isGuaranteed(): bool
    {
        return true;
    }

    public function guessMimeType(string $path): ?string
    {
        if (!is_file($path) || !is_readable($path)) {
            return null;
        }

        try {
            $detector = new MimeDetector($path);
            $mime = $detector->getMimeType();

            if (\is_array($mime)) {
                $mime = $mime['mime'] ?? null;
            }

            if (\is_string($mime) && '' !== $mime && 'application/octet-stream' !== $mime) {
                return $mime;
            }

            // Metin tabanlı CSV dosyaları için fallback kontrolü
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if ('csv' === $extension) {
                $finfo = new \finfo(\FILEINFO_MIME_TYPE);
                $finfoMime = $finfo->file($path);
                if (false !== $finfoMime && str_starts_with($finfoMime, 'text/')) {
                    return 'text/csv';
                }
            }
        } catch (\Throwable) {
            // Hata durumunda ikincil tahmincilere devret
        }

        return null;
    }
}
