<?php
require_once __DIR__ . '/BaseException.php';

class DatabaseException extends BaseException
{
    protected int $statusCode = 500;

    public function __construct(
        string $message = "A database error occurred",
        array $context = [],
        int $code = 0,
        ?Throwable $previous = null
    ) {
        // include PDO error info if previous exception is PDOException
        if ($previous instanceof PDOException) {
            $context['pdo_error_info'] = $previous->errorInfo ?? null;
        }

        parent::__construct($message, $context, $code, $previous);
    }
}
