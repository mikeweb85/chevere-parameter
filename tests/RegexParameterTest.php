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

use Chevere\Parameter\Interfaces\RegexParameterInterface;
use Chevere\Parameter\RegexParameter;
use Chevere\Regex\Regex;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use TypeError;
use function Chevere\Parameter\regex;

final class RegexParameterTest extends TestCase
{
    public function testConstruct(): void
    {
        $regex = RegexParameterInterface::PATTERN_DEFAULT;
        $parameter = new RegexParameter();
        $this->assertSame(null, $parameter->default());
        $this->assertSame('', $parameter->description());
        $this->assertEquals($parameter, regex());
        $this->assertSame($regex, $parameter->regex()->__toString());
        $this->assertSame([
            'type' => 'string',
            'description' => '',
            'default' => null,
            'regex' => $parameter->regex()->noDelimiters(),
        ], $parameter->schema());
        $description = 'ola k ase';
        $parameter = new RegexParameter($description);
        $this->assertSame($description, $parameter->description());
    }

    public function testWithRegex(): void
    {
        $regex = new Regex('/^[0-9+]$/');
        $parameter = (new RegexParameter())->withRegex($regex);
        $this->assertSame($regex->__toString(), $parameter->regex()->__toString());
        $this->assertSame([
            'type' => 'string',
            'description' => '',
            'default' => null,
            'regex' => $regex->noDelimiters(),
        ], $parameter->schema());
    }

    public function testWithDescription(): void
    {
        $parameter = new RegexParameter();
        $try = 'description';
        $this->assertSame('', $parameter->description());
        $parameterWith = $parameter->withDescription($try);
        $this->assertNotSame($parameter, $parameterWith);
        $this->assertSame($try, $parameterWith->description());
    }

    public function testWithDefault(): void
    {
        $default = 'some value';
        $parameter = new RegexParameter('test');
        $parameterWithDefault = $parameter->withDefault($default);
        (new ParameterHelper())->testWithParameterDefault(
            primitive: 'string',
            parameter: $parameter,
            default: $default,
            parameterWithDefault: $parameterWithDefault
        );
    }

    public function testWithDefaultRegexAware(): void
    {
        $parameter = (new RegexParameter('test'))->withDefault('a');
        $parameterWithRegex = $parameter
            ->withRegex(new Regex('/^a|b$/'));
        $this->assertNotSame($parameter, $parameterWithRegex);
        $this->expectException(InvalidArgumentException::class);
        $parameterWithRegex->withDefault('');
    }

    public function testAssertCompatible(): void
    {
        $regex = new Regex('/^[0-9+]$/');
        $regexAlt = new Regex('/^[a-z+]$/');
        $parameter = (new RegexParameter())->withRegex($regex);
        $compatible = (new RegexParameter())->withRegex($regex);
        $parameter->assertCompatible($compatible);
        $compatible->assertCompatible($parameter);
        $notCompatible = (new RegexParameter())->withRegex($regexAlt);
        $this->expectException(InvalidArgumentException::class);
        $parameter->assertCompatible($notCompatible);
    }

    public function testInvoke(): void
    {
        $parameter = new RegexParameter();
        $parameter('100');
        $this->expectException(TypeError::class);
        $parameter(false);
    }
}
