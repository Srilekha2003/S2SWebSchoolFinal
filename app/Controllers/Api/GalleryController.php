<?php

namespace App\Controllers\Api;

use App\Models\GalleryModel;
use CodeIgniter\HTTP\IncomingRequest;

class GalleryController extends BaseApiController
{
    protected GalleryModel $galleryModel;
    protected IncomingRequest $req;

    public function __construct()
    {
        parent::__construct();
        $this->galleryModel = new GalleryModel();
        $this->req = service('request');   // ✔ Always works for getFile()
    }

    // -------------------------------------------------------
    // 📋 GET: List all Gallery records (Public Allowed)
    // -------------------------------------------------------
    public function index()
    {
        if ($resp = $this->checkPermission('gallery', 'index', true))
            return $resp;

        try {
            $query = $this->galleryModel->where('deleted_at', null);

            // Optional filters
            $filters = ['school_id', 'branch_id', 'event_name', 'status'];
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
            ], 'Gallery records fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[GALLERY][INDEX] ' . $e->getMessage());
            return $this->respondError('Failed to fetch gallery records' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // 📋 GET: Single Gallery record
    // -------------------------------------------------------
    public function show($id = null)
    {
        if ($resp = $this->checkPermission('gallery', 'show', true))
            return $resp;

        try {
            $record = $this->galleryModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Gallery record not found', 404);
            }

            return $this->respondSuccess($record, 'Gallery record fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[GALLERY][SHOW] ' . $e->getMessage());
            return $this->respondError('Failed to fetch gallery record' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ➕ POST: Create Gallery record (Restricted)
    // -------------------------------------------------------
    public function create()
{
    if ($resp = $this->checkPermission('gallery', 'create'))
        return $resp;

    try {
        $data = $this->input();

        if (empty($data['school_id']) || empty($data['event_name'])) {
            return $this->respondError('school_id and event_name are required', 422);
        }

        // ✅ AUTO SET uploaded_by (logged-in user)
       $data['uploaded_by'] = $this->user->id;     // ✅ correct
          // 🔥 IMPORTANT

        // ✔ File Upload
        $file = $this->req->getFile('attachment');
        if ($file && $file->isValid()) {

            $mime = $file->getMimeType();

            $path = uploadFile($file, 'gallery', [
                'jpg','jpeg','png','webp'
            ]);

            if (!$path) {
                return $this->respondError('Invalid file or upload failed', 422);
            }

            $data['attachment'] = $path;
            $data['attachment_type'] = str_contains($mime, 'image') ? 'image' : null;
        }

        if (!$this->galleryModel->insert($data)) {
            return $this->respondError(
                $this->galleryModel->errors() ?: 'Failed to create gallery record',
                422
            );
        }

        return $this->respondSuccess(
            $this->galleryModel->find($this->galleryModel->getInsertID()),
            'Gallery record created successfully',
            201
        );

    } catch (\Throwable $e) {
        log_message('error', '[GALLERY][CREATE] ' . $e->getMessage());
        return $this->respondError(
            'Server error while creating gallery record' . $e->getMessage(),
            500
        );
    }
}


    // -------------------------------------------------------
    // ✏️ PUT: Update Gallery record (Restricted)
    // -------------------------------------------------------
    public function update($id = null)
    {
        if ($resp = $this->checkPermission('gallery', 'update'))
            return $resp;

        try {
            $record = $this->galleryModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Gallery record not found', 404);
            }

            $data = $this->input();

            $file = $this->req->getFile('attachment');
            if ($file && $file->isValid()) {

                $mime = $file->getMimeType();
                $path = uploadFile($file, 'gallery', [
                    'jpg','jpeg','png','webp'
                ]);

                if (!$path) {
                    return $this->respondError('Invalid file or upload failed', 422);
                }

                if (!empty($record['attachment'])) {
                    $old = FCPATH . $record['attachment'];
                    if (is_file($old)) @unlink($old);
                }

                $data['attachment'] = $path;
                $data['attachment_type'] = 'image';
            }

            $data['updated_at'] = date('Y-m-d H:i:s');

            if (!$this->galleryModel->update($id, $data)) {
                return $this->respondError(
                    $this->galleryModel->errors() ?: 'Failed to update gallery record',
                    422
                );
            }

            return $this->respondSuccess(
                $this->galleryModel->find($id),
                'Gallery record updated successfully'
            );

        } catch (\Throwable $e) {
            log_message('error', '[GALLERY][UPDATE] ' . $e->getMessage());
            return $this->respondError('Server error while updating gallery record' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ❌ DELETE: Soft Delete (Restricted)
    // -------------------------------------------------------
    public function delete($id = null)
    {
        if ($resp = $this->checkPermission('gallery', 'delete'))
            return $resp;

        try {
            $record = $this->galleryModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Gallery record not found', 404);
            }

            if (!empty($record['attachment'])) {
                $old = FCPATH . $record['attachment'];
                if (is_file($old)) @unlink($old);
            }

            $this->galleryModel->delete($id);

            return $this->respondSuccess(['id' => $id], 'Gallery record deleted successfully');

        } catch (\Throwable $e) {
            log_message('error', '[GALLERY][DELETE] ' . $e->getMessage());
            return $this->respondError('Failed to delete gallery record' . $e->getMessage(), 500);
        }
    }
}
