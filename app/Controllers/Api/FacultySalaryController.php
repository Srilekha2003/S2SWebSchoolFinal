<?php

namespace App\Controllers\Api;

use App\Models\FacultySalaryModel;
use CodeIgniter\HTTP\IncomingRequest;

class FacultySalaryController extends BaseApiController
{
    protected FacultySalaryModel $facultySalaryModel;
    protected IncomingRequest $req;

    public function __construct()
    {
        parent::__construct();
        $this->facultySalaryModel = new FacultySalaryModel();
        $this->req = service('request');
    }

    // -------------------------------------------------------
    // 📋 GET: List all Faculty Salary records (Public Allowed)
    // -------------------------------------------------------
    public function index()
    {
        if ($resp = $this->checkPermission('faculty_salary', 'index', true))
            return $resp;

        try {
            $query = $this->facultySalaryModel->where('deleted_at', null);

            // Optional filters
            $filters = ['school_id','branch_id','faculty_id','salary_month','status'];
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
            ], 'Faculty salary records fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[FACULTY_SALARY][INDEX] ' . $e->getMessage());
            return $this->respondError('Failed to fetch faculty salary records' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // 📋 GET: Single Faculty Salary record
    // -------------------------------------------------------
    public function show($id = null)
    {
        if ($resp = $this->checkPermission('faculty_salary', 'show', true))
            return $resp;

        try {
            $record = $this->facultySalaryModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Faculty salary record not found', 404);
            }

            return $this->respondSuccess($record, 'Faculty salary record fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[FACULTY_SALARY][SHOW] ' . $e->getMessage());
            return $this->respondError('Failed to fetch faculty salary record' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ➕ POST: Create Faculty Salary record (Restricted)
    // -------------------------------------------------------
    public function create()
    {
        if ($resp = $this->checkPermission('faculty_salary', 'create'))
            return $resp;

        try {
            $data = $this->input();

            if (empty($data['school_id']) || empty($data['faculty_id']) || empty($data['salary_month'])) {
                return $this->respondError('school_id, faculty_id and salary_month are required', 422);
            }

            if (!$this->facultySalaryModel->insert($data)) {
                return $this->respondError(
                    $this->facultySalaryModel->errors() ?: 'Failed to create faculty salary record',
                    422
                );
            }

            $id = $this->facultySalaryModel->getInsertID();
            $record = $this->facultySalaryModel->find($id);

            return $this->respondSuccess($record, 'Faculty salary record created successfully', 201);

        } catch (\Throwable $e) {
            log_message('error', '[FACULTY_SALARY][CREATE] ' . $e->getMessage());
            return $this->respondError('Server error while creating faculty salary record' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ✏️ PUT: Update Faculty Salary record (Restricted)
    // -------------------------------------------------------
    public function update($id = null)
    {
        if ($resp = $this->checkPermission('faculty_salary', 'update'))
            return $resp;

        try {
            $record = $this->facultySalaryModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Faculty salary record not found', 404);
            }

            $data = $this->input();
            $data['updated_at'] = date('Y-m-d H:i:s');

            if (!$this->facultySalaryModel->update($id, $data)) {
                return $this->respondError(
                    $this->facultySalaryModel->errors() ?: 'Failed to update faculty salary record',
                    422
                );
            }

            $updated = $this->facultySalaryModel->find($id);

            return $this->respondSuccess($updated, 'Faculty salary record updated successfully');

        } catch (\Throwable $e) {
            log_message('error', '[FACULTY_SALARY][UPDATE] ' . $e->getMessage());
            return $this->respondError('Server error while updating faculty salary record' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ❌ DELETE: Soft Delete (Restricted)
    // -------------------------------------------------------
    public function delete($id = null)
    {
        if ($resp = $this->checkPermission('faculty_salary', 'delete'))
            return $resp;

        try {
            $record = $this->facultySalaryModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Faculty salary record not found', 404);
            }

            // Use CI4 soft delete
            $this->facultySalaryModel->delete($id);

            return $this->respondSuccess(['id' => $id], 'Faculty salary record deleted successfully');

        } catch (\Throwable $e) {
            log_message('error', '[FACULTY_SALARY][DELETE] ' . $e->getMessage());
            return $this->respondError('Failed to delete faculty salary record' . $e->getMessage(), 500);
        }
    }
}
