<?php

declare(strict_types=1);

namespace Iquety\Prospection\Domain\Core;

use JsonSerializable;

/**
 * Os objetos de identidade tem o mesmo comportamento e regras dos objetos de valor.
 * A única diferença é que o retorno do método valor() sempre será uma string contendo
 * um valor de identificação.
 */
class IdentityObject
{
    use StateExtraction;

    public function __construct(private int|float|string $identity)
    {
    }

    public function equalTo(IdentityObject $other): bool
    {
        return $this->value() === $other->value();
    }

    public function value(): string
    {
        return (string)$this->identity;
    }

    public function toArray(): array
    {
        return ['identity' => $this->identity ];
    }

    public function __toString(): string
    {
        return $this->extractStateString();
    }
}
