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

namespace Chevere\Parameter;

use Chevere\Parameter\Interfaces\ParameterInterface;
use Chevere\Parameter\Interfaces\ReflectionParameterTypedInterface;
use InvalidArgumentException;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Throwable;
use TypeError;
use function Chevere\Message\message;

final class ReflectionParameterTyped implements ReflectionParameterTypedInterface
{
    private ReflectionNamedType $type;

    private ParameterInterface $parameter;

    public function __construct(
        private ReflectionParameter $reflection
    ) {
        $this->type = $this->getType();
        $parameter = toParameter($this->type->getName());

        try {
            $attribute = reflectedParameterAttribute('parameter', $reflection);
            $parameter = $attribute->parameter();
        } catch (Throwable) {
        }
        if ($this->reflection->isDefaultValueAvailable()
            && method_exists($parameter, 'withDefault')
        ) {
            /** @var ParameterInterface $parameter */
            $parameter = $parameter
                ->withDefault(
                    $this->reflection->getDefaultValue()
                );
        }
        $this->parameter = $parameter;
    }

    public function parameter(): ParameterInterface
    {
        return $this->parameter;
    }

    private function getType(): ReflectionNamedType
    {
        $reflectionType = $this->reflection->getType();
        if ($reflectionType === null) {
            throw new TypeError(
                (string) message(
                    'Missing type declaration for parameter `%parameter%`',
                    parameter: '$' . $this->reflection->getName()
                )
            );
        }
        if ($reflectionType instanceof ReflectionNamedType) {
            return $reflectionType;
        }
        $name = '$' . $this->reflection->getName();
        $type = $this->getReflectionType($reflectionType);

        throw new InvalidArgumentException(
            (string) message(
                'Parameter %name% of type %type% is not supported',
                name: $name,
                type: $type
            )
        );
    }

    /**
     * @infection-ignore-all
     */
    private function getReflectionType(mixed $reflectionType): string
    {
        return match (true) {
            $reflectionType instanceof ReflectionUnionType => 'union',
            $reflectionType instanceof ReflectionIntersectionType => 'intersection',
            default => 'unknown',
        };
    }
}
