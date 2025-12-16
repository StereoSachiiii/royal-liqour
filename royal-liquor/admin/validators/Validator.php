<?php
declare(strict_types=1);

require_once __DIR__ . '/../exceptions/ValidationException.php';

interface ValidatorInterface {
    public static function validateCreate(array $data): void;
    public static function validateUpdate(array $data): void;
}

final class ValidationRunner
{
    /**
     * @param callable(array): array $hooks Each hook returns an associative array of field => error message
     */
    public static function run(array $hooks, array $data, string $message): void
    {
        $errors = [];

        foreach ($hooks as $hook) {
            $result = $hook($data);
            if (!empty($result)) {
                $errors = array_merge($errors, $result);
            }
        }

        if ($errors) {
            throw new ValidationException($message, ['errors' => $errors]);
        }
    }
}

?>