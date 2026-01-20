<?php

namespace App\Controllers\Api;

use App\Models\StudentLeaveManagementModel;
use CodeIgniter\HTTP\IncomingRequest;

class StudentLeaveManagementController extends BaseApiController
{
    protected StudentLeaveManagementModel $studentLeaveManagementModel;
    protected IncomingRequest $req;

    public function __construct()
    {
        parent::__construct();
        $this->studentLeaveManagementModel = new StudentLeaveManagementModel();
        $this->req = service('request');   // ✔ Always works for getFile()
    }

    // -------------------------------------------------------
    // 📋 GET: List all Leave records (Public Allowed)
    // -------------------------------------------------------
    public function index()
    {
        if ($resp = $this->checkPermission('student_leave_management', 'index', true))
            return $resp;

        try {
            $query = $this->studentLeaveManagementModel->where('deleted_at', null);

            // Optional filters
            $filters = ['school_id', 'student_id', 'class_id', 'approval_status', 'status'];
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
            ], 'Student leave records fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[STUDENT_LEAVE][INDEX] ' . $e->getMessage());
            return $this->respondError('Failed to fetch student leave records' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // 📋 GET: Single Leave record
    // -------------------------------------------------------
    public function show($id = null)
    {
        if ($resp = $this->checkPermission('student_leave_management', 'show', true))
            return $resp;

        try {
            $record = $this->studentLeaveManagementModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Student leave record not found', 404);
            }

            return $this->respondSuccess($record, 'Student leave record fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[STUDENT_LEAVE][SHOW] ' . $e->getMessage());
            return $this->respondError('Failed to fetch student leave record' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ➕ POST: Create Leave record (Restricted)
    // -------------------------------------------------------
    public function create()
    {
        if ($resp = $this->checkPermission('student_leave_management', 'create'))
            return $resp;

        try {
            $data = $this->input();

            if (empty($data['student_id']) || empty($data['leave_type'])) {
                return $this->respondError('student_id and leave_type are required', 422);
            }

            if (!$this->studentLeaveManagementModel->insert($data)) {
                return $this->respondError(
                    $this->studentLeaveManagementModel->errors() ?: 'Failed to create student leave record',
                    422
                );
            }

            $id = $this->studentLeaveManagementModel->getInsertID();
            $record = $this->studentLeaveManagementModel->find($id);

            return $this->respondSuccess($record, 'Student leave record created successfully', 201);

        } catch (\Throwable $e) {
            log_message('error', '[STUDENT_LEAVE][CREATE] ' . $e->getMessage());
            return $this->respondError('Server error while creating student leave record' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ✏️ PUT: Update Leave record (Restricted)
    // -------------------------------------------------------
    public function update($id = null)
    {
        if ($resp = $this->checkPermission('student_leave_management', 'update'))
            return $resp;

        try {
            $record = $this->studentLeaveManagementModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Student leave record not found', 404);
            }

            $data = $this->input();
            $data['updated_at'] = date('Y-m-d H:i:s');

            if (!$this->studentLeaveManagementModel->update($id, $data)) {
                return $this->respondError(
                    $this->studentLeaveManagementModel->errors() ?: 'Failed to update student leave record',
                    422
                );
            }

            $updated = $this->studentLeaveManagementModel->find($id);

            return $this->respondSuccess($updated, 'Student leave record updated successfully');

        } catch (\Throwable $e) {
            log_message('error', '[STUDENT_LEAVE][UPDATE] ' . $e->getMessage());
            return $this->respondError('Server error while updating student leave record' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ❌ DELETE: Soft Delete (Restricted)
    // -------------------------------------------------------
    public function delete($id = null)
    {
        if ($resp = $this->checkPermission('student_leave_management', 'delete'))
            return $resp;

        try {
            $record = $this->studentLeaveManagementModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Student leave record not found', 404);
            }

            // Use CI4 soft delete
            $this->studentLeaveManagementModel->delete($id);

            return $this->respondSuccess(['id' => $id], 'Student leave record deleted successfully');

        } catch (\Throwable $e) {
            log_message('error', '[STUDENT_LEAVE][DELETE] ' . $e->getMessage());
            return $this->respondError('Failed to delete student leave record' . $e->getMessage(), 500);
        }
    }
}
