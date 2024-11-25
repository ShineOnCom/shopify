<?php

namespace Dan\Shopify\Exceptions;

use Exception;

/**
 * Class InvalidGraphQLCallException.
 */
class InvalidGraphQLCallException extends Exception
{
    public function __construct($message = 'Invalid GraphQL Call exception', $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
