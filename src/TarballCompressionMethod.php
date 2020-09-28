<?php

/**
 * TarballCompressionMethod.php
 *
 * Copyright 2020 Danny Damsky
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package coffeephp\tarball
 * @author Danny Damsky <dannydamsky99@gmail.com>
 * @since 2020-09-09
 */

declare(strict_types=1);

namespace CoffeePhp\Tarball;

use CoffeePhp\CompressionMethod\AbstractCompressionMethod;
use CoffeePhp\FileSystem\Contract\Data\Path\DirectoryInterface;
use CoffeePhp\FileSystem\Contract\Data\Path\FileInterface;
use CoffeePhp\FileSystem\Contract\Data\Path\PathNavigatorInterface;
use CoffeePhp\Tarball\Contract\TarballCompressionMethodInterface;
use CoffeePhp\Tarball\Exception\TarballCompressException;
use CoffeePhp\Tarball\Exception\TarballUncompressException;
use PharData;
use Throwable;

/**
 * Class TarballCompressionMethod
 * @package coffeephp\tarball
 * @author Danny Damsky <dannydamsky99@gmail.com>
 * @since 2020-09-09
 */
final class TarballCompressionMethod extends AbstractCompressionMethod implements TarballCompressionMethodInterface
{
    /**
     * @inheritDoc
     */
    public function compressDirectory(DirectoryInterface $uncompressedDirectory): FileInterface
    {
        try {
            $absolutePath = (string)$uncompressedDirectory;
            if (!$uncompressedDirectory->exists()) {
                throw new TarballCompressException("The given directory does not exist: {$absolutePath}");
            }
            $fullPath = $this->getFullPath($absolutePath, self::EXTENSION);
            $pathNavigator = $this->getAvailablePath($fullPath);
            return $this->handleLowLevelCompression($uncompressedDirectory, $pathNavigator);
        } catch (TarballCompressException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new TarballCompressException(
                "Unexpected Compression Exception: {$e->getMessage()}",
                (int)$e->getCode(),
                $e
            );
        }
    }

    /**
     * @param DirectoryInterface $directory
     * @param PathNavigatorInterface $destination
     * @return FileInterface
     * @psalm-suppress UndefinedVariable
     */
    private function handleLowLevelCompression(
        DirectoryInterface $directory,
        PathNavigatorInterface $destination
    ): FileInterface {
        try {
            $tar = new PharData((string)$destination);
            $tar->buildFromDirectory((string)$directory);
            unset($tar);
            return $this->fileManager->getFile($destination);
        } finally {
            if (isset($tar)) { // @phpstan-ignore-line
                unset($tar);
                if ($destination->exists()) {
                    $this->fileManager->getPath($destination)->delete();
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function uncompressDirectory(FileInterface $compressedDirectory): DirectoryInterface
    {
        try {
            $absolutePath = (string)$compressedDirectory;
            if (!$compressedDirectory->exists()) {
                throw new TarballUncompressException("The given archive does not exist: {$absolutePath}");
            }
            $extension = self::EXTENSION;
            if (!$this->isFullPath($absolutePath, $extension)) {
                throw new TarballUncompressException(
                    "Directory archive '{$absolutePath}' does not have the extension: {$extension}"
                );
            }
            $originalPath = $this->getOriginalPath($absolutePath, $extension);
            $pathNavigator = $this->getAvailablePath($originalPath);
            return $this->handleLowLevelUncompression($compressedDirectory, $pathNavigator);
        } catch (TarballUncompressException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new TarballUncompressException(
                "Unexpected Uncompression Exception: {$e->getMessage()}",
                (int)$e->getCode(),
                $e
            );
        }
    }

    /**
     * @param FileInterface $file
     * @param PathNavigatorInterface $destination
     * @return DirectoryInterface
     * @psalm-suppress UndefinedVariable
     */
    private function handleLowLevelUncompression(
        FileInterface $file,
        PathNavigatorInterface $destination
    ): DirectoryInterface {
        try {
            $tar = new PharData((string)$file);
            $tar->extractTo((string)$destination);
            unset($tar);
            return $this->fileManager->getDirectory($destination);
        } finally {
            if (isset($tar)) { // @phpstan-ignore-line
                unset($tar);
                if ($destination->exists()) {
                    $this->fileManager->getPath($destination)->delete();
                }
            }
        }
    }
}
