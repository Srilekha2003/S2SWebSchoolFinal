<?php
// app/Helpers/api_helper.php

if (!function_exists('apiResponse')) {
    /**
     * Standardized API JSON response
     *
     * @param mixed $data
     * @param int $statusCode HTTP status code
     * @param string|null $message Optional message
     * @param bool|null $success Optional success override
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    function apiResponse(mixed $data = null, int $statusCode = 200, ?string $message = null, ?bool $success = null)
    {
        $isSuccess = $success ?? ($statusCode >= 200 && $statusCode < 300);

        $responseData = [
            'success' => $isSuccess,
            'status'  => $statusCode,
            'message' => $message ?? ($isSuccess ? 'OK' : 'Error'),
            'data'    => $data
        ];

        return \Config\Services::response()
            ->setStatusCode($statusCode)
            ->setJSON($responseData);
    }
}

if (!function_exists('apiSuccess')) {
    /**
     * Quick success response
     */
    function apiSuccess(mixed $data = null, string $message = 'Success', int $statusCode = 200)
    {
        return apiResponse($data, $statusCode, $message, true);
    }
}

if (!function_exists('apiError')) {
    /**
     * Quick error response
     */
    function apiError(string $message = 'Error', int $statusCode = 400, mixed $data = null)
    {
        return apiResponse($data, $statusCode, $message, false);
    }
}
