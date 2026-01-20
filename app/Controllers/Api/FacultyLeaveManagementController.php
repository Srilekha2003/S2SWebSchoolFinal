<?php

namespace App\Controllers\Api;

use App\Models\FacultyLeaveManagementModel;
use CodeIgniter\HTTP\IncomingRequest;

class FacultyLeaveManagementController extends BaseApiController
{
    protected FacultyLeaveManagementModel $facultyLeaveModel;
    protected IncomingRequest $req;

    public function __construct()
    {
        parent::__construct();
        $this->facultyLeaveModel = new FacultyLeaveManagementModel();
        $this->req = service('request');
    }

    // -------------------------------------------------------
    // 📋 GET: List all Faculty Leave records (Public Allowed)
    // -------------------------------------------------------
    public function index()
    {
        if ($resp = $this->checkPermission('faculty_leave_management', 'index', true))
            return $resp;

        try {
            $query = $this->facultyLeaveModel->where('deleted_at', null);

            $filters = ['school_id','branch_id','faculty_id','leave_type','approval_status','status'];
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
            ], 'Faculty leave records fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[FACULTY_LEAVE][INDEX] ' . $e->getMessage());
            return $this->respondError('Failed to fetch faculty leave records' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // 📋 GET: Single Faculty Leave record
    // -------------------------------------------------------
    public function show($id = null)
    {
        if ($resp = $this->checkPermission('faculty_leave_management', 'show', true))
            return $resp;

        try {
            $record = $this->facultyLeaveModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Faculty leave record not found', 404);
            }

            return $this->respondSuccess($record, 'Faculty leave record fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[FACULTY_LEAVE][SHOW] ' . $e->getMessage());
            return $this->respondError('Failed to fetch faculty leave record' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ➕ POST: Create Faculty Leave record (Restricted)
    // -------------------------------------------------------
    public function create()
    {
        if ($resp = $this->checkPermission('faculty_leave_management', 'create'))
            return $resp;

        try {
            $data = $this->input();

            if (!$this->facultyLeaveModel->insert($data)) {
                return $this->respondError(
                    $this->facultyLeaveModel->errors() ?: 'Failed to create faculty leave record',
                    422
                );
            }

            $id = $this->facultyLeaveModel->getInsertID();
            $record = $this->facultyLeaveModel->find($id);

            return $this->respondSuccess($record, 'Faculty leave record created successfully', 201);

        } catch (\Throwable $e) {
            log_message('error', '[FACULTY_LEAVE][CREATE] ' . $e->getMessage());
            return $this->respondError('Server error while creating faculty leave record' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ✏️ PUT: Update Faculty Leave record (Restricted)
    // -------------------------------------------------------
    public function update($id = null)
    {
        if ($resp = $this->checkPermission('faculty_leave_management', 'update'))
            return $resp;

        try {
            $record = $this->facultyLeaveModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Faculty leave record not found', 404);
            }

            $data = $this->input();
            $data['updated_at'] = date('Y-m-d H:i:s');

            if (!$this->facultyLeaveModel->update($id, $data)) {
                return $this->respondError(
                    $this->facultyLeaveModel->errors() ?: 'Failed to update faculty leave record',
                    422
                );
            }

            $updated = $this->facultyLeaveModel->find($id);
            return $this->respondSuccess($updated, 'Faculty leave record updated successfully');

        } catch (\Throwable $e) {
            log_message('error', '[FACULTY_LEAVE][UPDATE] ' . $e->getMessage());
            return $this->respondError('Server error while updating faculty leave record' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ❌ DELETE: Soft Delete (Restricted)
    // -------------------------------------------------------
    public function delete($id = null)
    {
        if ($resp = $this->checkPermission('faculty_leave_management', 'delete'))
            return $resp;

        try {
            $record = $this->facultyLeaveModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Faculty leave record not found', 404);
            }

            $this->facultyLeaveModel->delete($id);

            return $this->respondSuccess(['id' => $id], 'Faculty leave record deleted successfully');

        } catch (\Throwable $e) {
            log_message('error', '[FACULTY_LEAVE][DELETE] ' . $e->getMessage());
            return $this->respondError('Failed to delete faculty leave record' . $e->getMessage(), 500);
        }
    }
}
