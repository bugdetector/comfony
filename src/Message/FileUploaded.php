<?php

namespace App\Message;

final class FileUploaded
{
    public function __construct(
        public int $fileId,
    ) {
    }
}
