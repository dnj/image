<?php

namespace dnj\Image\Exceptions;

use dnj\Filesystem\Contracts\IFile;
use Exception;

class InvalidImageFileException extends Exception
{
    /** @var IFile is the invalid image file */
    protected $invalidImageFile;

    public function __construct(IFile $invalidImageFile, string $message = '')
    {
        $this->invalidImageFile = $invalidImageFile;
        if (!$message) {
            $message = "{$invalidImageFile->getPath()} is an invalid image";
        }
        parent::__construct($message);
    }

    public function getInvalidFile(): IFile
    {
        return $this->invalidImageFile;
    }
}
