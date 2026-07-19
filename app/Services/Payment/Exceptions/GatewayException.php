<?php

namespace App\Services\Payment\Exceptions;

class GatewayException extends \RuntimeException
{
    public function __construct(
        string $message,
        public readonly array $context = [],
    ) {
        parent::__construct($message);
    }
}
