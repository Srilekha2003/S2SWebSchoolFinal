<?php

namespace App\Controllers\Api;

use App\Models\ClassesModel;
use CodeIgniter\HTTP\IncomingRequest;

class ClassesController extends BaseApiController
{
    protected ClassesModel $classesModel;
    protected IncomingRequest $req;

    public function __construct()
    {
        parent::__construct();
        $this->classesModel = new ClassesModel();
        $this->req = service('request');
    }

    // -------------------------------------------------------
    // 📋 GET: List all Class records
    // -------------------------------------------------------
    public function index()
    {
        if ($resp = $this->checkPermission('classes', 'index', true))
            return $resp;

        try {
            $query = $this->classesModel->where('deleted_at', null);

            // Optional filters
            $filters = ['school_id', 'branch_id', 'faculty_id', 'status'];
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
            ], 'Class records fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[CLASSES][INDEX] ' . $e->getMessage());
            return $this->respondError('Failed to fetch class records: ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // 📋 GET: Single Class record
    // -------------------------------------------------------
    public function show($id = null)
    {
        if ($resp = $this->checkPermission('classes', 'show', true))
            return $resp;

        try {
            $record = $this->classesModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Class record not found', 404);
            }

            return $this->respondSuccess($record, 'Class record fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[CLASSES][SHOW] ' . $e->getMessage());
            return $this->respondError('Failed to fetch class record: ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ➕ POST: Create Class record
    // -------------------------------------------------------
    public function create()
    {
        if ($resp = $this->checkPermission('classes', 'create'))
            return $resp;

        try {
            $data = $this->input();

            if (empty($data['school_id']) || empty($data['branch_id']) || empty($data['class_name'])) {
                return $this->respondError('school_id, branch_id, class_name are required', 422);
            }

            if (!$this->classesModel->insert($data)) {
                return $this->respondError(
                    $this->classesModel->errors() ?: 'Failed to create class record',
                    422
                );
            }

            $id = $this->classesModel->getInsertID();
            $record = $this->classesModel->find($id);

            return $this->respondSuccess($record, 'Class record created successfully', 201);

        } catch (\Throwable $e) {
            log_message('error', '[CLASSES][CREATE] ' . $e->getMessage());
            return $this->respondError('Server error while creating class record: ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ✏️ PUT: Update Class record
    // -------------------------------------------------------
    public function update($id = null)
    {
        if ($resp = $this->checkPermission('classes', 'update'))
            return $resp;

        try {
            $record = $this->classesModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Class record not found', 404);
            }

            $data = $this->input();
            $data['updated_at'] = date('Y-m-d H:i:s');

            if (!$this->classesModel->update($id, $data)) {
                return $this->respondError(
                    $this->classesModel->errors() ?: 'Failed to update class record',
                    422
                );
            }

            $updated = $this->classesModel->find($id);
            return $this->respondSuccess($updated, 'Class record updated successfully');

        } catch (\Throwable $e) {
            log_message('error', '[CLASSES][UPDATE] ' . $e->getMessage());
            return $this->respondError('Server error while updating class record: ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ❌ DELETE: Soft Delete
    // -------------------------------------------------------
    public function delete($id = null)
    {
        if ($resp = $this->checkPermission('classes', 'delete'))
            return $resp;

        try {
            $record = $this->classesModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Class record not found', 404);
            }

            $this->classesModel->delete($id);

            return $this->respondSuccess(['id' => $id], 'Class record deleted successfully');

        } catch (\Throwable $e) {
            log_message('error', '[CLASSES][DELETE] ' . $e->getMessage());
            return $this->respondError('Failed to delete class record: ' . $e->getMessage(), 500);
        }
    }
}
