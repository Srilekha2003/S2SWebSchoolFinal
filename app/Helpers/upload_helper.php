<?php

if (!function_exists('uploadFile')) {
    /**
     * Handles file upload safely
     *
     * @param \CodeIgniter\HTTP\Files\UploadedFile $file
     * @param string $folder
     * @param array $allowedTypes
     * @return string|null Path to uploaded file or null on failure
     */
    function uploadFile($file, string $folder = 'uploads', array $allowedTypes = ['jpg','jpeg','png','pdf','docx','mp4']): ?string
    {
        if (!$file->isValid() || $file->hasMoved()) {
            return null;
        }

        $ext = strtolower($file->getClientExtension());
        if (!in_array($ext, $allowedTypes)) {
            return null;
        }

        $newName = $file->getRandomName();
        $publicPath = realpath(FCPATH);
        $uploadPath = $publicPath . '/uploads/' . $folder;

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $file->move($uploadPath, $newName);
        return 'uploads/' . $folder . '/' . $newName;
    }
}
