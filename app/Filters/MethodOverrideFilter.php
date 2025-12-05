<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;

class MethodOverrideFilter implements FilterInterface
{
    public function before($request, $arguments = null)
    {
        $method = $request->getPost('_method')
                  ?? $request->getHeaderLine('X-HTTP-Method-Override');

        if ($method) {
            $_SERVER['REQUEST_METHOD'] = strtoupper($method);
        }
    }

    public function after($request, $response, $arguments = null)
    {
        // nothing
    }
}
