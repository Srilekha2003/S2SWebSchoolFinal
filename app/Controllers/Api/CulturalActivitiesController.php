<?php

namespace App\Controllers\Api;

use App\Models\CulturalActivitiesModel;
use CodeIgniter\HTTP\IncomingRequest;

class CulturalActivitiesController extends BaseApiController
{
    protected CulturalActivitiesModel $culturalActivitiesModel;
    protected IncomingRequest $req;

    public function __construct()
    {
        parent::__construct();
        $this->culturalActivitiesModel = new CulturalActivitiesModel();
        $this->req = service('request'); // ✔ Always works for getFile()
    }

    // -------------------------------------------------------
    // 📋 GET: List all Cultural Activities (Public Allowed)
    // -------------------------------------------------------
    public function index()
    {
        if ($resp = $this->checkPermission('cultural_activities', 'index', true))
            return $resp;

        try {
            $query = $this->culturalActivitiesModel->where('deleted_at', null);

            // Optional filters
            $filters = ['school_id', 'branch_id', 'class_id', 'status', 'category'];
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
            ], 'Cultural activities fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[CULTURAL][INDEX] ' . $e->getMessage());
            return $this->respondError('Failed to fetch cultural activities' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // 📋 GET: Single Cultural Activity
    // -------------------------------------------------------
    public function show($id = null)
    {
        if ($resp = $this->checkPermission('cultural_activities', 'show', true))
            return $resp;

        try {
            $record = $this->culturalActivitiesModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Cultural activity not found', 404);
            }

            return $this->respondSuccess($record, 'Cultural activity fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[CULTURAL][SHOW] ' . $e->getMessage());
            return $this->respondError('Failed to fetch cultural activity' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ➕ POST: Create Cultural Activity (Restricted)
    // -------------------------------------------------------
    public function create()
    {
        if ($resp = $this->checkPermission('cultural_activities', 'create'))
            return $resp;

        try {
            $data = $this->input();

            if (empty($data['school_id']) || empty($data['branch_id']) || empty($data['event_name'])) {
                return $this->respondError('school_id, branch_id and event_name are required', 422);
            }

            // ✔ File Upload
            $file = $this->req->getFile('attachment');
            if ($file && $file->isValid()) {

                // 🔥 FIX: Get MIME type BEFORE move()
                $mime = $file->getMimeType();

                $path = uploadFile($file, 'cultural_activities', [
                    'jpg','jpeg','png','webp',
                    'mp4','avi',
                    'pdf','doc','docx'
                ]);

                if (!$path) {
                    return $this->respondError('Invalid file or upload failed', 422);
                }

                $data['attachment'] = $path;

                // Auto-detect attachment type
                if (str_contains($mime, 'image'))      $data['attachment_type'] = 'image';
                elseif (str_contains($mime, 'video'))  $data['attachment_type'] = 'video';
                else                                   $data['attachment_type'] = 'document';
            }

            if (!$this->culturalActivitiesModel->insert($data)) {
                return $this->respondError(
                    $this->culturalActivitiesModel->errors() ?: 'Failed to create cultural activity',
                    422
                );
            }

            $id = $this->culturalActivitiesModel->getInsertID();
            $record = $this->culturalActivitiesModel->find($id);

            return $this->respondSuccess($record, 'Cultural activity created successfully', 201);

        } catch (\Throwable $e) {
            log_message('error', '[CULTURAL][CREATE] ' . $e->getMessage());
            return $this->respondError('Server error while creating cultural activity' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ✏️ PUT: Update Cultural Activity (Restricted)
    // -------------------------------------------------------
    public function update($id = null)
    {
        if ($resp = $this->checkPermission('cultural_activities', 'update'))
            return $resp;

        try {
            $record = $this->culturalActivitiesModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Cultural activity not found', 404);
            }

            $data = $this->input();

            // ✔ File Upload (only if provided)
            $file = $this->req->getFile('attachment');
            if ($file && $file->isValid()) {

                $mime = $file->getMimeType();
                $path = uploadFile($file, 'cultural_activities', [
                    'jpg','jpeg','png','webp',
                    'mp4','avi',
                    'pdf','doc','docx'
                ]);

                if (!$path) {
                    return $this->respondError('Invalid file or upload failed', 422);
                }

                // Delete old file
                if (!empty($record['attachment'])) {
                    $old = realpath(FCPATH) . $record['attachment'];
                    if (is_file($old)) @unlink($old);
                }

                $data['attachment'] = $path;

                if (str_contains($mime, 'image'))      $data['attachment_type'] = 'image';
                elseif (str_contains($mime, 'video'))  $data['attachment_type'] = 'video';
                else                                   $data['attachment_type'] = 'document';
            }

            if (!$this->culturalActivitiesModel->update($id, $data)) {
                return $this->respondError(
                    $this->culturalActivitiesModel->errors() ?: 'Failed to update cultural activity',
                    422
                );
            }

            $updated = $this->culturalActivitiesModel->find($id);
            return $this->respondSuccess($updated, 'Cultural activity updated successfully');

        } catch (\Throwable $e) {
            log_message('error', '[CULTURAL][UPDATE] ' . $e->getMessage());
            return $this->respondError('Server error while updating cultural activity' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ❌ DELETE: Soft Delete (Restricted)
    // -------------------------------------------------------
    public function delete($id = null)
    {
        if ($resp = $this->checkPermission('cultural_activities', 'delete'))
            return $resp;

        try {
            $record = $this->culturalActivitiesModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Cultural activity not found', 404);
            }

            if (!empty($record['attachment'])) {
                $old = realpath(FCPATH) . $record['attachment'];
                if (is_file($old)) @unlink($old);
            }

            $this->culturalActivitiesModel->delete($id);

            return $this->respondSuccess(['id' => $id], 'Cultural activity deleted successfully');

        } catch (\Throwable $e) {
            log_message('error', '[CULTURAL][DELETE] ' . $e->getMessage());
            return $this->respondError('Failed to delete cultural activity' . $e->getMessage(), 500);
        }
    }
}
