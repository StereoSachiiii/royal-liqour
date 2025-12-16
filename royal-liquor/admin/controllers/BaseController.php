<?php

declare(strict_types=1);

require_once __DIR__ . '/../exceptions/BaseException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

abstract class BaseController
{
    /**
     * Execute a controller action with unified exception handling.
     *
     * @param callable $callback
     * @return array
     * @throws BaseException
     */
    protected function handle(callable $callback): array
    {
        try {
            return $callback();
        } catch (BaseException $e) {
            // Let domain exceptions bubble up to the global handler
            throw $e;
        } catch (Throwable $e) {
            // Wrap unexpected errors into a DatabaseException for now
            throw new DatabaseException(
                'Unexpected error: ' . $e->getMessage(),
                ['previous' => $e->getMessage()],
                500,
                $e
            );
        }
    }

    /**
     * Helper to build a standard success response array.
     */
    protected function success(string $message, mixed $data = null, int $code = 200, ?array $meta = null): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'errors'  => [],
            'meta'    => $meta,
            'code'    => $code,
        ];
    }

    /**
     * Helper for paginated responses.
     */
    protected function paginated(
        string $message,
        array $items,
        int $total,
        int $limit,
        int $offset
    ): array {
        return $this->success($message, $items, 200, [
            'total'  => $total,
            'limit'  => $limit,
            'offset' => $offset,
            'count'  => count($items),
        ]);
    }
}
