<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevere.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Tests;

use PHPUnit\Framework\TestCase;
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\regex;

final class FunctionsArrayStringTest extends TestCase
{
    public function testArraypString(): void
    {
        $string = regex();
        $parameter = arrayp(a: $string);
        $this->assertCount(1, $parameter->parameters());
        $this->assertSame($string, $parameter->parameters()->get('a'));
        $this->assertTrue($parameter->parameters()->requiredKeys()->contains('a'));
    }
}
