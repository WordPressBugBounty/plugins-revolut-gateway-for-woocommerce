<?php

namespace Revolut\Plugin\Infrastructure\FileSystem;

interface FileSystemInterface
{
    public function writeFile(string $path, string $contents): bool;
    public function readFile($path);
    public function fileExists(string $path): bool;
    public function deleteFile(string $path): bool;
    public function makeDirectory(string $path, int $permissions = 0755): bool;
    public function getRootDir(): string;
}
