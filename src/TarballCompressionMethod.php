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
            $tar = new PharData((string)$pathNavigator);
            $tar->buildFromDirectory($absolutePath);
            return $this->fileManager->getFile($pathNavigator);
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
            $tar = new PharData($absolutePath);
            $tar->extractTo((string)$pathNavigator);
            return $this->fileManager->getDirectory($pathNavigator);
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
}
