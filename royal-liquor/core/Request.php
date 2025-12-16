<?php

declare(strict_types=1);

namespace Core;

/**
 * HTTP Request Object
 * 
 * Encapsulates HTTP request data and provides convenient access methods
 */
class Request
{
    private string $method;
    private string $uri;
    private array $headers;
    private array $query;
    private array $body;
    private array $route;
    private array $server;
    private array $files;
    private ?object $user = null;

    public function __construct(
        string $method,
        string $uri,
        array $headers = [],
        array $query = [],
        array $body = [],
        array $server = [],
        array $files = []
    ) {
        $this->method = strtoupper($method);
        $this->uri = $uri;
        $this->headers = $headers;
        $this->query = $query;
        $this->body = $body;
        $this->route = [];
        $this->server = $server;
        $this->files = $files;
    }

    /**
     * Create Request from PHP globals
     *
     * @return self
     */
    public static function createFromGlobals(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

        // Normalize URI to be relative to the router's base path
        // Remove base directory path (e.g. /royal-liquor) from URI
        // so Router patterns like '/api/v1/products' match URLs such as '/royal-liquor/api/v1/products'
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        
        // Extract base path from script name (e.g. /royal-liquor from /royal-liquor/admin/api/index.php)
        $basePath = '';
        if ($scriptName !== '') {
            // Get the directory part up to but not including /admin/api
            if (preg_match('#^(.*?)/(admin/api|api)/#', $scriptName, $matches)) {
                $basePath = $matches[1];
            }
        }
        
        // Strip base path from URI if present
        if ($basePath !== '' && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
            if ($uri === '' || $uri === false) {
                $uri = '/';
            }
        }

        // Parse headers
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headerKey = str_replace('_', '-', substr($key, 5));
                $headers[$headerKey] = $value;
            }
        }
        
        // Add content type if present
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
        }

        // Parse body based on content type
        $body = [];
        $contentType = $headers['Content-Type'] ?? '';
        
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            if (str_contains($contentType, 'application/json')) {
                $rawBody = file_get_contents('php://input');
                $body = json_decode($rawBody, true) ?? [];
            } else {
                $body = $_POST;
            }
        }

        return new self(
            $method,
            $uri,
            $headers,
            $_GET,
            $body,
            $_SERVER,
            $_FILES
        );
    }

    /**
     * Get HTTP method
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Get request URI
     *
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Get a header value
     *
     * @param string $name
     * @return string|null
     */
    public function getHeader(string $name): ?string
    {
        // Case-insensitive header lookup
        $name = strtoupper(str_replace('-', '_', $name));
        
        foreach ($this->headers as $key => $value) {
            if (strtoupper(str_replace('-', '_', $key)) === $name) {
                return $value;
            }
        }
        
        return null;
    }

    /**
     * Get all headers
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get a query parameter
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getQuery(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * Get all query parameters
     *
     * @return array
     */
    public function getAllQuery(): array
    {
        return $this->query;
    }

    /**
     * Get a body parameter
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getBody(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    /**
     * Get all body parameters
     *
     * @return array
     */
    public function getAllBody(): array
    {
        return $this->body;
    }

    /**
     * Get a route parameter
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getRouteParam(string $key, mixed $default = null): mixed
    {
        return $this->route[$key] ?? $default;
    }

    /**
     * Set route parameters (used by router)
     *
     * @param array $params
     * @return void
     */
    public function setRouteParams(array $params): void
    {
        $this->route = $params;
    }

    /**
     * Get all route parameters
     *
     * @return array
     */
    public function getRouteParams(): array
    {
        return $this->route;
    }

    /**
     * Get all parameters (query + body + route)
     *
     * @return array
     */
    public function all(): array
    {
        return array_merge($this->query, $this->body, $this->route);
    }

    /**
     * Get client IP address
     *
     * @return string
     */
    public function ip(): string
    {
        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];

        foreach ($headers as $header) {
            if (isset($this->server[$header])) {
                $ip = $this->server[$header];
                // Handle comma-separated list of IPs
                if (str_contains($ip, ',')) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                return $ip;
            }
        }

        return '0.0.0.0';
    }

    /**
     * Check if request is AJAX
     *
     * @return bool
     */
    public function isAjax(): bool
    {
        return $this->getHeader('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * Check if request is JSON
     *
     * @return bool
     */
    public function isJson(): bool
    {
        $contentType = $this->getHeader('Content-Type') ?? '';
        return str_contains($contentType, 'application/json');
    }

    /**
     * Get authenticated user (set by authentication middleware)
     *
     * @return object|null
     */
    public function getUser(): ?object
    {
        return $this->user;
    }

    /**
     * Set authenticated user (used by authentication middleware)
     *
     * @param object $user
     * @return void
     */
    public function setUser(object $user): void
    {
        $this->user = $user;
    }

    /**
     * Check if user is authenticated
     *
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return $this->user !== null;
    }

    /**
     * Get uploaded file
     *
     * @param string $key
     * @return array|null
     */
    public function getFile(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    /**
     * Get all uploaded files
     *
     * @return array
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * Check if file was uploaded
     *
     * @param string $key
     * @return bool
     */
    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }
}
