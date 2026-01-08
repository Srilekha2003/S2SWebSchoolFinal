<?php

namespace App\Controllers\Api;

use App\Models\SchoolsModel;
use CodeIgniter\HTTP\IncomingRequest;

class SchoolsController extends BaseApiController
{
    protected SchoolsModel $schoolsModel;
    protected IncomingRequest $req;

    public function __construct()
    {
        parent::__construct();
        $this->schoolsModel = new SchoolsModel();
        $this->req = service('request');   // For getFile()
    }

    // -------------------------------------------------------
    // 📋 GET: List all Schools (Public Allowed)
    // -------------------------------------------------------
    public function index()
    {
        // if ($resp = $this->checkPermission('school', 'index', true))
        //     return $resp;

        try {
            $query = $this->schoolsModel->where('deleted_at', null);

            // Optional filters
            $filters = ['type', 'management', 'status'];
            foreach ($filters as $f) {
                $val = $this->input($f);
                if (!empty($val)) {
                    $query->where($f, $val);
                }
            }

            $records = $query->orderBy('id', 'DESC')->findAll();

            return $this->respondSuccess([
                'count' => count($records),
                'data'  => $records
            ], 'School records fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[SCHOOL][INDEX] ' . $e->getMessage());
            return $this->respondError('Failed to fetch school records: ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // 📋 GET: Single School record
    // -------------------------------------------------------
    public function show($id = null)
    {
        if ($resp = $this->checkPermission('school', 'show', true))
            return $resp;

        try {
            $record = $this->schoolsModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('School record not found', 404);
            }

            return $this->respondSuccess($record, 'School record fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[SCHOOL][SHOW] ' . $e->getMessage());
            return $this->respondError('Failed to fetch school record: ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ➕ POST: Create School record (Restricted)
    // -------------------------------------------------------
    public function create()
    {
        if ($resp = $this->checkPermission('school', 'create'))
            return $resp;

        try {
            $data = $this->input();

            if (empty($data['school_name'])) {
                return $this->respondError('school_name is required', 422);
            }

            // ✔ File Upload (logo)
            $file = $this->req->getFile('logo');
            if ($file && $file->isValid()) {

                $mime = $file->getMimeType();

                $path = uploadFile($file, 'schools', [
                    'jpg','jpeg','png','webp'
                ]);

                if (!$path) {
                    return $this->respondError('Invalid logo file or upload failed', 422);
                }

                $data['logo'] = $path;
            }

            if (!$this->schoolsModel->insert($data)) {
                    $errors = $this->schoolsModel->errors();
                    return $this->respondError(
                        $errors ? implode(", ", $errors) : 'Failed to create school record',
                        422
                    );
                }

            $id = $this->schoolsModel->getInsertID();
            $record = $this->schoolsModel->find($id);

            return $this->respondSuccess($record, 'School record created successfully', 201);

        } catch (\Throwable $e) {
            log_message('error', '[SCHOOL][CREATE] ' . $e->getMessage());
            return $this->respondError('Server error while creating school record: ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ✏️ PUT: Update School record (Restricted)
    // -------------------------------------------------------
    public function update($id = null)
    {
        if ($resp = $this->checkPermission('school', 'update'))
            return $resp;

        try {
            $record = $this->schoolsModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('School record not found', 404);
            }

            $data = $this->input();

            // ✔ Handle update logo
            $file = $this->req->getFile('logo');
            if ($file && $file->isValid()) {

                $mime = $file->getMimeType();

                $path = uploadFile($file, 'schools', [
                    'jpg','jpeg','png','webp'
                ]);

                if (!$path) {
                    return $this->respondError('Invalid logo file or upload failed', 422);
                }

                // Delete old logo
                if (!empty($record['logo'])) {
                    $publicPath = realpath(FCPATH);
                    $old = $publicPath . $record['logo'];
                    if (is_file($old)) @unlink($old);
                }

                $data['logo'] = $path;
            }

            $data['updated_at'] = date('Y-m-d H:i:s');

            if (!$this->schoolsModel->update($id, $data)) {
                return $this->respondError(
                    $this->schoolsModel->errors() ?: 'Failed to update school record',
                    422
                );
            }

            $updated = $this->schoolsModel->find($id);

            return $this->respondSuccess($updated, 'School record updated successfully');

        } catch (\Throwable $e) {
            log_message('error', '[SCHOOL][UPDATE] ' . $e->getMessage());
            return $this->respondError('Server error while updating school record: ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ❌ DELETE: Soft Delete School (Restricted)
    // -------------------------------------------------------
    public function delete($id = null)
    {
        if ($resp = $this->checkPermission('school', 'delete'))
            return $resp;

        try {
            $record = $this->schoolsModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('School record not found', 404);
            }

            // Delete old logo
            if (!empty($record['logo'])) {
                $publicPath = realpath(FCPATH);
                $old = $publicPath . $record['logo'];
                if (is_file($old)) @unlink($old);
            }

            $this->schoolsModel->delete($id);

            return $this->respondSuccess(['id' => $id], 'School record deleted successfully');

        } catch (\Throwable $e) {
            log_message('error', '[SCHOOL][DELETE] ' . $e->getMessage());
            return $this->respondError('Failed to delete school record: ' . $e->getMessage(), 500);
        }
    }
}
