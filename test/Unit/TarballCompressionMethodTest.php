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
 * @noinspection StaticInvocationViaThisInspection
 */

declare(strict_types=1);

namespace CoffeePhp\Tarball\Test\Unit;

use CoffeePhp\QualityTools\TestCase;
use CoffeePhp\Tarball\TarballCompressionMethod;

use function file_get_contents;
use function implode;
use function mkdir;

use const PHP_EOL;

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
     */
    public function testPathCompressionMethod(): void
    {
        $method = new TarballCompressionMethod();

        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'temp';
        $this->assertTrue(mkdir($dir));
        $file = $dir . DIRECTORY_SEPARATOR . 'file.txt';
        $contents = implode(PHP_EOL, $this->getFaker()->paragraphs(50));
        $this->assertNotFalse(file_put_contents($file, $contents));

        $tarFile = $method->compressDirectory($dir);

        $this->assertSame("$dir.tar", $tarFile);

        $this->assertTrue(unlink($file));
        $this->assertTrue(rmdir($dir));
        $this->assertFileDoesNotExist($file);
        $this->assertDirectoryDoesNotExist($dir);

        $dir2 = $method->uncompressDirectory($tarFile);
        $this->assertSame($dir, $dir2);

        $this->assertDirectoryExists($dir);
        $this->assertFileExists($file);
        $this->assertSame($contents, file_get_contents($file));

        $this->assertTrue(unlink($file));
        $this->assertTrue(rmdir($dir));
        $this->assertTrue(unlink($tarFile));
        $this->assertFileDoesNotExist($file);
        $this->assertDirectoryDoesNotExist($dir);
        $this->assertFileDoesNotExist($tarFile);
    }
}
