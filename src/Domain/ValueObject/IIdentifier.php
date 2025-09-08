<?php
declare(strict_types=1);

namespace App\Domain\ValueObject;

interface IIdentifier
{
    public static function fromString(string $string): self;

    public function equals(?self $other): bool;

    public function toString(): string;

    public function __toString(): string;
}