<?php

/**
 * TarballCompressionMethodTest.php
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
 * @since 2020-09-10
 */

declare(strict_types=1);

namespace CoffeePhp\Tarball\Test\Unit;

use CoffeePhp\FileSystem\Data\Path\PathNavigator;
use CoffeePhp\FileSystem\FileManager;
use CoffeePhp\Tarball\TarballCompressionMethod;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;

/**
 * Class TarballCompressionMethodTest
 * @package coffeephp\tarball
 * @author Danny Damsky <dannydamsky99@gmail.com>
 * @since 2020-09-10
 * @see TarballCompressionMethod
 */
final class TarballCompressionMethodTest extends TestCase
{
    /**
     * @see TarballCompressionMethod::compressDirectory()
     * @see TarballCompressionMethod::uncompressDirectory()
     * @noinspection PhpUndefinedMethodInspection
     */
    public function testPathCompressionMethod(): void
    {
        $fileManager = new FileManager();
        $method = new TarballCompressionMethod($fileManager);

        $dir = (new PathNavigator(__DIR__))->abc();
        $file = (clone $dir)->def()->ghi()->jkl()->mno()->pqr()->stu()->vwx()->yz()->down('file.txt');
        $fileModel = $fileManager->createFile($file);
        $fileModel->write('abc');
        $dirModel = $fileManager->getDirectory($dir);

        $tarFile = $method->compressDirectory($dirModel);

        assertSame(
            "{$dir}.tar",
            (string)$tarFile
        );

        $dirModel->delete();

        assertFalse($dirModel->exists() || $fileModel->exists());

        $method->uncompressDirectory($tarFile);

        assertTrue($dirModel->exists() && $fileModel->exists() && $fileModel->read() === 'abc');

        $tarFile->delete();
        $dirModel->delete();
    }
}
