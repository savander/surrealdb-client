<?php

declare(strict_types=1);

namespace Savander\SurrealdbClient\Exceptions;

class RequiredOptionArgumentMissingException extends \Exception
{
    public function __construct(string $fieldName)
    {
        parent::__construct("The `$fieldName` option is required.");
    }
}
