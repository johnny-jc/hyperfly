<?php
declare(strict_types=1);

namespace Api\ApiService\Exception;

use Hyperf\Server\Exception\ServerException;
use Throwable;

/**
 * Class ApiException
 * @package Api\ApiService\Exception
 */
class ApiException extends ServerException
{
    /**
     * ApiException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}