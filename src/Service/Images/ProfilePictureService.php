<?php

namespace App\Service\Images;

use App\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProfilePictureService
{
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    private const MAX_FILE_SIZE = 2097152;

    public function attachProfilePicture(
        User $user,
        ?UploadedFile $file
    ): bool {
        if ($file === null) {
            return true;
        }

        if (!$this->isValid($file)) {
            return false;
        }

        $content = file_get_contents($file->getRealPath());

        if ($content === false) {
            return false;
        }

        $user->setPhoto($content);

        return true;
    }

    private function isValid(UploadedFile $file): bool
    {
        if (
            !in_array(
                $file->getMimeType(),
                self::ALLOWED_MIME_TYPES,
                true
            )
        ) {
            return false;
        }

        if ($file->getSize() > self::MAX_FILE_SIZE) {
            return false;
        }

        return true;
    }
}
