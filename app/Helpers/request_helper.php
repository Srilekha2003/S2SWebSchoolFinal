<?php

if (!function_exists('getRequestData')) {
    /**
     * Universal input reader for JSON, form-data, and raw input
     * 
     * @param \CodeIgniter\HTTP\IncomingRequest|null $request
     * @param bool $includeFiles Whether to include uploaded files
     * @return array
     */
    function getRequestData($request = null, bool $includeFiles = true): array
    {
        $request ??= service('request');

        // Determine Content-Type
        $contentType = $request->getHeaderLine('Content-Type');
        $data = [];

        // 0️⃣ GET values (Query String)
        $getData = $request->getGet();
        if (!empty($getData)) {
            $data = array_merge($data, $getData);
        }

        // 1️⃣ Handle JSON input
        if (strpos($contentType, 'application/json') !== false) {
            $json = $request->getJSON(true);
            if (is_array($json)) {
                $data = array_merge($data, $json);
            }
        }

        // 2️⃣ Handle form-data / x-www-form-urlencoded
        $postData = $request->getPost();
        if (!empty($postData)) {
            $data = array_merge($data, $postData);
        }

        // 3️⃣ Handle raw input (PUT, PATCH)
        $rawInput = $request->getRawInput();
        if (!empty($rawInput)) {
            $data = array_merge($data, $rawInput);
        }

        // 4️⃣ Handle uploaded files
        if ($includeFiles) {
            $files = [];
            foreach ($request->getFiles() as $key => $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $files[$key] = $file;
                }
            }
            if (!empty($files)) {
                $data['_files'] = $files; // Keep separate to avoid confusion with text fields
            }
        }

        return $data;
    }
}
