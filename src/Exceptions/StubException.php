<?php

namespace Lab2view\RepositoryGenerator\Exceptions;

use Exception;

class StubException extends Exception
{
    public static function fileNotFound($file)
    {
        return new static('Stub file does not exists: ' . $file);
    }
}
