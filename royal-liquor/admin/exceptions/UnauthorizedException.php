<?php
require_once __DIR__ . '/BaseException.php';

class UnauthorizedException extends BaseException
{
    protected int $statusCode = 401;

    /**
     * Constructor
     *
     * @param string $message Optional error message
     * @param array $context Optional context array (fields, values, etc.)
     * @param int $code Optional internal exception code
     * @param Throwable|null $previous Optional previous exception for chaining
     */
    public function __construct(
        string $message = "Unauthorized: Access is denied",
        array $context = [],
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $context, $code, $previous);
    }
}
