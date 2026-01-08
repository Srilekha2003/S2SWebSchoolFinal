<?php

namespace App\Controllers\Api;

use App\Models\AssignmentsHomeworkModel;
use CodeIgniter\HTTP\IncomingRequest;

class AssignmentsHomeworkController extends BaseApiController
{
    protected AssignmentsHomeworkModel $assignmentsHomeworkModel;
    protected IncomingRequest $req;

    public function __construct()
    {
        parent::__construct();
        $this->assignmentsHomeworkModel = new AssignmentsHomeworkModel();
        $this->req = service('request');   // ✔ Always works for getFile()
    }

    // -------------------------------------------------------
    // 📋 GET: List all Assignments/Homework records (Public Allowed)
    // -------------------------------------------------------
    public function index()
    {
        if ($resp = $this->checkPermission('assignments_homework', 'index', true))
            return $resp;

        try {
            $query = $this->assignmentsHomeworkModel->where('deleted_at', null);

            // Optional filters
            $filters = ['school_id', 'branch_id', 'class_id', 'faculty_id', 'submission_status'];
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
            ], 'Assignments/Homework records fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[ASSIGNMENTS_HOMEWORK][INDEX] ' . $e->getMessage());
            return $this->respondError('Failed to fetch records ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // 📋 GET: Single record
    // -------------------------------------------------------
    public function show($id = null)
    {
        if ($resp = $this->checkPermission('assignments_homework', 'show', true))
            return $resp;

        try {
            $record = $this->assignmentsHomeworkModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Record not found', 404);
            }

            return $this->respondSuccess($record, 'Record fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[ASSIGNMENTS_HOMEWORK][SHOW] ' . $e->getMessage());
            return $this->respondError('Failed to fetch record ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ➕ POST: Create record (Restricted)
    // -------------------------------------------------------
    public function create()
    {
        if ($resp = $this->checkPermission('assignments_homework', 'create'))
            return $resp;

        try {
            $data = $this->input();

            if (empty($data['school_id']) || empty($data['class_id']) || empty($data['title'])) {
                return $this->respondError('school_id, class_id and title are required', 422);
            }

            // ✔ File Upload
            $file = $this->req->getFile('attachment');
            if ($file && $file->isValid()) {

                // 🔥 FIX: Get MIME type BEFORE move()
                $mime = $file->getMimeType();

                $path = uploadFile($file, 'assignments_homework', [
                    'jpg','jpeg','png','webp',
                    'mp4','avi',
                    'pdf','doc','docx'
                ]);

                if (!$path) {
                    return $this->respondError('Invalid file or upload failed', 422);
                }

                $data['attachment'] = $path;

                if (str_contains($mime, 'image'))        $data['attachment_type'] = 'image';
                elseif (str_contains($mime, 'video'))   $data['attachment_type'] = 'video';
                else                                     $data['attachment_type'] = 'document';
            }

            if (!$this->assignmentsHomeworkModel->insert($data)) {
                return $this->respondError(
                    $this->assignmentsHomeworkModel->errors() ?: 'Failed to create record',
                    422
                );
            }

            $id = $this->assignmentsHomeworkModel->getInsertID();
            $record = $this->assignmentsHomeworkModel->find($id);

            return $this->respondSuccess($record, 'Record created successfully', 201);

        } catch (\Throwable $e) {
            log_message('error', '[ASSIGNMENTS_HOMEWORK][CREATE] ' . $e->getMessage());
            return $this->respondError('Server error ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ✏️ PUT: Update record (Restricted)
    // -------------------------------------------------------
    public function update($id = null)
    {
        if ($resp = $this->checkPermission('assignments_homework', 'update'))
            return $resp;

        try {
            $record = $this->assignmentsHomeworkModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Record not found', 404);
            }

            $data = $this->input();

            // ✔ File Upload only if provided
            $file = $this->req->getFile('attachment');
            if ($file && $file->isValid()) {

                $mime = $file->getMimeType();
                $path = uploadFile($file, 'assignments_homework', [
                    'jpg','jpeg','png','webp',
                    'mp4','avi',
                    'pdf','doc','docx'
                ]);

                if (!$path) {
                    return $this->respondError('Invalid file or upload failed', 422);
                }

                if (!empty($record['attachment'])) {
                    $old = realpath(FCPATH) . $record['attachment'];
                    if (is_file($old)) @unlink($old);
                }

                $data['attachment'] = $path;

                if (str_contains($mime, 'image'))        $data['attachment_type'] = 'image';
                elseif (str_contains($mime, 'video'))   $data['attachment_type'] = 'video';
                else                                     $data['attachment_type'] = 'document';
            }

            if (!$this->assignmentsHomeworkModel->update($id, $data)) {
                return $this->respondError(
                    $this->assignmentsHomeworkModel->errors() ?: 'Failed to update record',
                    422
                );
            }

            return $this->respondSuccess(
                $this->assignmentsHomeworkModel->find($id),
                'Record updated successfully'
            );

        } catch (\Throwable $e) {
            log_message('error', '[ASSIGNMENTS_HOMEWORK][UPDATE] ' . $e->getMessage());
            return $this->respondError('Server error ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ❌ DELETE: Soft Delete (Restricted)
    // -------------------------------------------------------
    public function delete($id = null)
    {
        if ($resp = $this->checkPermission('assignments_homework', 'delete'))
            return $resp;

        try {
            $record = $this->assignmentsHomeworkModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Record not found', 404);
            }

            if (!empty($record['attachment'])) {
                $old = realpath(FCPATH) . $record['attachment'];
                if (is_file($old)) @unlink($old);
            }

            $this->assignmentsHomeworkModel->delete($id);

            return $this->respondSuccess(['id' => $id], 'Record deleted successfully');

        } catch (\Throwable $e) {
            log_message('error', '[ASSIGNMENTS_HOMEWORK][DELETE] ' . $e->getMessage());
            return $this->respondError('Failed to delete record ' . $e->getMessage(), 500);
        }
    }
}
