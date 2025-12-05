<?php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use App\Libraries\JWTService;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): ?ResponseInterface
    {
        $method = strtoupper($request->getMethod());

        // ✅ Allow GET and OPTIONS (for public and CORS preflight)
        if (in_array($method, ['GET', 'OPTIONS'])) {
            return null;
        }

        // ✅ Allow requests without Authorization header only for public routes
        $authHeader = $request->getHeaderLine('Authorization');
        if (empty($authHeader)) {
            return $this->unauthorizedResponse('Missing Authorization header');
        }

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->unauthorizedResponse('Invalid Authorization header format');
        }

        $token = $matches[1];

        // ✅ Check blacklisted tokens (logout)
        if (function_exists('isTokenBlacklisted') && isTokenBlacklisted($token)) {
            return $this->unauthorizedResponse('Token expired or logged out');
        }

        $jwt = new JWTService();
        $decoded = $jwt->decodeToken($token);

        if (!$decoded) {
            return $this->unauthorizedResponse('Invalid or expired token');
        }

        // ✅ Attach user to request
        $request->user = $decoded;

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
        // nothing
    }

    private function unauthorizedResponse(string $message): ResponseInterface
    {
        return service('response')
            ->setStatusCode(401)
            ->setJSON([
                'success' => false,
                'status'  => 401,
                'message' => "Unauthorized: {$message}",
                'data'    => null,
            ]);
    }
}
