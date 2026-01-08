<?php

namespace App\Controllers\Api;

use App\Models\FacultyModel;
use CodeIgniter\HTTP\IncomingRequest;

class FacultyController extends BaseApiController
{
    protected FacultyModel $facultyModel;
    protected IncomingRequest $req;

    public function __construct()
    {
        parent::__construct();
        $this->facultyModel = new FacultyModel();
        $this->req = service('request');
    }

    // -------------------------------------------------------
    // 📋 GET: List all Faculty (Public Allowed)
    // -------------------------------------------------------
    public function index()
    {
        if ($resp = $this->checkPermission('faculty', 'index', true))
            return $resp;

        try {
            $query = $this->facultyModel->where('deleted_at', null);

            // Optional filters
            $filters = ['school_id', 'branch_id', 'status'];
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
            ], 'Faculty records fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[FACULTY][INDEX] ' . $e->getMessage());
            return $this->respondError('Failed to fetch faculty records ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // 📋 GET: Single Faculty
    // -------------------------------------------------------
    public function show($id = null)
    {
        if ($resp = $this->checkPermission('faculty', 'show', true))
            return $resp;

        try {
            $record = $this->facultyModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record)
                return $this->respondError('Faculty record not found', 404);

            return $this->respondSuccess($record, 'Faculty record fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[FACULTY][SHOW] ' . $e->getMessage());
            return $this->respondError('Failed to fetch faculty record' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ➕ POST: Create Faculty
    // -------------------------------------------------------
    public function create()
    {
        if ($resp = $this->checkPermission('faculty', 'create'))
            return $resp;

        try {
            $data = $this->input();

            if (empty($data['school_id']) || empty($data['first_name'])) {
                return $this->respondError('school_id and first_name are required', 422);
            }

            // (Optional) Photo upload using same style as Agriculture
            $photo = $this->req->getFile('photo');
            if ($photo && $photo->isValid()) {
                $path = uploadFile($photo, 'faculty', ['jpg','jpeg','png','webp']);
                if (!$path) {
                    return $this->respondError('Invalid photo or upload failed', 422);
                }
                $data['photo'] = $path;
            }

            if (!$this->facultyModel->insert($data)) {
                return $this->respondError(
                    $this->facultyModel->errors() ?: 'Failed to create faculty record',
                    422
                );
            }

            $id = $this->facultyModel->getInsertID();
            $record = $this->facultyModel->find($id);

            return $this->respondSuccess($record, 'Faculty created successfully', 201);

        } catch (\Throwable $e) {
            log_message('error', '[FACULTY][CREATE] ' . $e->getMessage());
            return $this->respondError('Server error while creating faculty ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ✏️ PUT: Update Faculty
    // -------------------------------------------------------
    public function update($id = null)
    {
        if ($resp = $this->checkPermission('faculty', 'update'))
            return $resp;

        try {
            $record = $this->facultyModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record)
                return $this->respondError('Faculty record not found', 404);

            $data = $this->input();

            // Photo Upload (optional)
            $photo = $this->req->getFile('photo');
            if ($photo && $photo->isValid()) {

                $path = uploadFile($photo, 'faculty', ['jpg','jpeg','png','webp']);
                if (!$path) {
                    return $this->respondError('Invalid photo or upload failed', 422);
                }

                // delete old
                if (!empty($record['photo'])) {
                    $old = FCPATH . $record['photo'];
                    if (is_file($old)) @unlink($old);
                }

                $data['photo'] = $path;
            }

            $data['updated_at'] = date('Y-m-d H:i:s');

            if (!$this->facultyModel->update($id, $data)) {
                return $this->respondError(
                    $this->facultyModel->errors() ?: 'Failed to update faculty',
                    422
                );
            }

            $updated = $this->facultyModel->find($id);

            return $this->respondSuccess($updated, 'Faculty updated successfully');

        } catch (\Throwable $e) {
            log_message('error', '[FACULTY][UPDATE] ' . $e->getMessage());
            return $this->respondError('Server error while updating faculty ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ❌ DELETE: Soft Delete Faculty
    // -------------------------------------------------------
    public function delete($id = null)
    {
        if ($resp = $this->checkPermission('faculty', 'delete'))
            return $resp;

        try {
            $record = $this->facultyModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record)
                return $this->respondError('Faculty record not found', 404);

            // delete old photo
            if (!empty($record['photo'])) {
                $old = FCPATH . $record['photo'];
                if (is_file($old)) @unlink($old);
            }

            $this->facultyModel->delete($id);

            return $this->respondSuccess(['id' => $id], 'Faculty deleted successfully');

        } catch (\Throwable $e) {
            log_message('error', '[FACULTY][DELETE] ' . $e->getMessage());
            return $this->respondError('Failed to delete faculty ' . $e->getMessage(), 500);
        }
    }
}
