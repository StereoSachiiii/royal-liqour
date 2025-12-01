<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../repositories/AdminViewRepository.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

class AdminViewController
{
    private AdminViewRepository $repo;
    private Session $session;

    public function __construct()
    {
        $this->repo = new AdminViewRepository();
        $this->session = Session::getInstance();
    }

    private function success(string $message, $data = [], int $code = 200): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'code'    => $code,
            'context' => []
        ];
    }

    private function logError(Throwable $e, array $context = []): void
    {
        error_log(sprintf(
            "[%s] AdminViewController Error: %s | File: %s:%d | Context: %s",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            json_encode($context)
        ));
    }

    private function error(Throwable $e): array
    {
        $code = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
        $context = method_exists($e, 'getContext') ? $e->getContext() : [];

        $this->logError($e, $context);

        return [
            'success' => false,
            'message' => $e->getMessage(),
            'code'    => $code,
            'context' => $context
        ];
    }

    private function handle(callable $callback): array
    {
        try {
            return $callback();
        } catch (ValidationException | NotFoundException | DatabaseException $e) {
            return $this->error($e);
        } catch (Throwable $e) {
            return $this->error(new Exception('Unexpected error: ' . $e->getMessage(), 500));
        }
    }

    /**
     * Get list view (for table)
     */
    public function getList(string $entity, int $limit = 50, int $offset = 0, ?string $search = null): array
    {
        return $this->handle(function () use ($entity, $limit, $offset, $search) {
            $data = $this->repo->getList($entity, $limit, $offset, $search);
            $total = $this->repo->getCount($entity, $search);
            
            return $this->success('List retrieved', [
                'entity' => $entity,
                'items' => $data,
                'pagination' => [
                    'total' => $total,
                    'limit' => $limit,
                    'offset' => $offset,
                    'pages' => ceil($total / $limit)
                ]
            ]);
        });
    }

    /**
     * Get detail view (for modal)
     */
    public function getDetail(string $entity, int $id): array
    {
        return $this->handle(function () use ($entity, $id) {
            $data = $this->repo->getDetail($entity, $id);
            
            if (!$data) {
                throw new NotFoundException(ucfirst($entity) . ' not found');
            }
            
            return $this->success('Detail retrieved', [
                'entity' => $entity,
                'id' => $id,
                'data' => $data
            ]);
        });
    }

    /**
     * Get dashboard stats
     */
    public function getDashboardStats(): array
    {
        return $this->handle(function () {
            $stats = $this->repo->getDashboardStats();
            return $this->success('Dashboard stats retrieved', $stats);
        });
    }
}