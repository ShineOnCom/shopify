<?php

namespace Dan\Shopify\Exceptions;

use Exception;

/**
 * Class GraphQLEnabledWithMissingQueriesException.
 */
class GraphQLEnabledWithMissingQueriesException extends Exception
{
     public function __construct($message = 'GraphQL is enabled for this endpoint but is currently not supported in this version.', $code = 0, Exception $previous = null)
     {
         parent::__construct($message, $code, $previous);
     }
}
