<?php

/**
 * TarballComponentRegistrarTest.php
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
 * @since 2020-09-11
 */

declare(strict_types=1);

namespace CoffeePhp\Tarball\Test\Integration;

use CoffeePhp\ComponentRegistry\ComponentRegistry;
use CoffeePhp\Di\Container;
use CoffeePhp\Tarball\Contract\TarballCompressionMethodInterface;
use CoffeePhp\Tarball\Integration\TarballComponentRegistrar;
use CoffeePhp\Tarball\TarballCompressionMethod;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;

/**
 * Class TarballComponentRegistrarTest
 * @package coffeephp\tarball
 * @author Danny Damsky <dannydamsky99@gmail.com>
 * @since 2020-09-11
 * @see TarballComponentRegistrar
 */
final class TarballComponentRegistrarTest extends TestCase
{
    /**
     * @see TarballComponentRegistrar::register()
     */
    public function testRegister(): void
    {
        $di = new Container();
        $componentRegistry = new ComponentRegistry($di);
        $componentRegistry->register(TarballComponentRegistrar::class);

        assertTrue(
            $di->has(TarballCompressionMethodInterface::class)
        );
        assertTrue(
            $di->has(TarballCompressionMethod::class)
        );
        assertInstanceOf(
            TarballCompressionMethod::class,
            $di->get(TarballCompressionMethodInterface::class)
        );
        assertSame(
            $di->get(TarballCompressionMethod::class),
            $di->get(TarballCompressionMethodInterface::class)
        );
    }
}
