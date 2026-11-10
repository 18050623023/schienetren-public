<?php

namespace App\DTOs;

class FeatureCheckResult
{
    public const CODE_PASSED = 'PASSED';
    public const CODE_DISABLED = 'FEATURE_DISABLED';
    public const CODE_LIMIT_EXCEEDED = 'LIMIT_EXCEEDED';

    public function __construct(
        public readonly bool $passed,
        public readonly string $code,
        public readonly ?string $message = null
    ) {}

    public static function passed(): self
    {
        return new self(true, self::CODE_PASSED);
    }

    public static function disabled(string $message): self
    {
        return new self(false, self::CODE_DISABLED, $message);
    }

    public static function limitExceeded(string $message): self
    {
        return new self(false, self::CODE_LIMIT_EXCEEDED, $message);
    }
}
