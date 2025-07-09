<?php

namespace Revolut\Wordpress\Infrastructure;

use Revolut\Plugin\Infrastructure\FileSystem\FileSystemInterface;

class FileSystemAdapter implements FileSystemInterface {

    public function readFile($path)
    {
        return file_get_contents( $path );
    }

    public function writeFile(string $path, string $contents): bool
    {
        return file_put_contents( $path, $contents );
    }
    
    public function deleteFile(string $path): bool
    {
       return file_exists( $path ) && unlink( $path );
    }

    public function fileExists(string $path): bool
    {
        return file_exists( $path );
    }

    public function makeDirectory(string $path, int $permissions = 0755): bool
    {
        return mkdir( $path, $permissions );
    }

    public function getRootDir(): string {
        return untrailingslashit( ABSPATH );
    }
}