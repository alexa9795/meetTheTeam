<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploaderHelper
{
    private $uploadPath;

    public function __construct(string $uploadPath)
    {
        $this->uploadPath = $uploadPath;
    }

    public function uploadImage(UploadedFile $uploadedFile): string
    {
        $destination = $this->uploadPath . '/avatars';

        $fileType = $uploadedFile->getClientOriginalExtension();
        $filename = uniqid(time()). '.' . $fileType;

        $uploadedFile->move($destination,$filename);

        return $filename;
    }
}
