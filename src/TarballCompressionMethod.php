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
use CoffeePhp\Tarball\Contract\TarballCompressionMethodInterface;
use CoffeePhp\Tarball\Exception\TarballCompressException;
use CoffeePhp\Tarball\Exception\TarballUncompressException;
use PharData;
use Throwable;

use function is_dir;
use function is_file;
use function pathinfo;

use const DIRECTORY_SEPARATOR;

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
    public function compressDirectory(string $uncompressedDirectoryPath): string
    {
        if (!is_dir($uncompressedDirectoryPath)) {
            throw new TarballCompressException('The given directory is invalid or does not exist');
        }
        $destination = $this->getAvailablePath($uncompressedDirectoryPath . '.' . self::EXTENSION);
        try {
            $tar = new PharData($destination);
            $tar->buildFromDirectory($uncompressedDirectoryPath);
            return $destination;
        } catch (Throwable $e) {
            throw new TarballCompressException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function uncompressDirectory(string $compressedDirectoryFilePath): string
    {
        if (!is_file($compressedDirectoryFilePath)) {
            throw new TarballUncompressException('The given archive file is invalid or does not exist');
        }
        $pathInfo = pathinfo($compressedDirectoryFilePath);
        if (($pathInfo['extension'] ?? '') !== self::EXTENSION) {
            throw new TarballUncompressException('The given file is not a tarball archive');
        }
        $destination = $this->getAvailablePath($pathInfo['dirname'] . DIRECTORY_SEPARATOR . $pathInfo['filename']);
        try {
            $tar = new PharData($compressedDirectoryFilePath);
            $tar->extractTo($destination);
            return $destination;
        } catch (Throwable $e) {
            throw new TarballUncompressException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }
}
