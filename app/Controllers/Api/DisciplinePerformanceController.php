<?php

namespace App\Controllers\Api;

use App\Models\DisciplinePerformanceModel;
use CodeIgniter\HTTP\IncomingRequest;

class DisciplinePerformanceController extends BaseApiController
{
    protected DisciplinePerformanceModel $disciplinePerformanceModel;
    protected IncomingRequest $req;

    public function __construct()
    {
        parent::__construct();
        $this->disciplinePerformanceModel = new DisciplinePerformanceModel();
        $this->req = service('request');   // ✔ Always works for getFile()
    }

    // -------------------------------------------------------
    // 📋 GET: List all DisciplinePerformance records (Public Allowed)
    // -------------------------------------------------------
    public function index()
    {
        if ($resp = $this->checkPermission('discipline_performance', 'index', true))
            return $resp;

        try {
            $query = $this->disciplinePerformanceModel->where('deleted_at', null);

            // Optional filters
            $filters = ['school_id', 'branch_id', 'student_id', 'faculty_id', 'category', 'status'];
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
            ], 'Discipline/Performance records fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[DISCIPLINE_PERFORMANCE][INDEX] ' . $e->getMessage());
            return $this->respondError('Failed to fetch records: ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // 📋 GET: Single DisciplinePerformance record
    // -------------------------------------------------------
    public function show($id = null)
    {
        if ($resp = $this->checkPermission('discipline_performance', 'show', true))
            return $resp;

        try {
            $record = $this->disciplinePerformanceModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Record not found', 404);
            }

            return $this->respondSuccess($record, 'Record fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[DISCIPLINE_PERFORMANCE][SHOW] ' . $e->getMessage());
            return $this->respondError('Failed to fetch record: ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ➕ POST: Create DisciplinePerformance record (Restricted)
    // -------------------------------------------------------
    public function create()
    {
        if ($resp = $this->checkPermission('discipline_performance', 'create'))
            return $resp;

        try {
            $data = $this->input();

            if (empty($data['remarks']) || empty($data['category'])) {
                return $this->respondError('category and remarks are required', 422);
            }

            if (!$this->disciplinePerformanceModel->insert($data)) {
                return $this->respondError(
                    $this->disciplinePerformanceModel->errors() ?: 'Failed to create record',
                    422
                );
            }

            $id = $this->disciplinePerformanceModel->getInsertID();
            $record = $this->disciplinePerformanceModel->find($id);

            return $this->respondSuccess($record, 'Record created successfully', 201);

        } catch (\Throwable $e) {
            log_message('error', '[DISCIPLINE_PERFORMANCE][CREATE] ' . $e->getMessage());
            return $this->respondError('Server error while creating record: ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ✏️ PUT: Update DisciplinePerformance record (Restricted)
    // -------------------------------------------------------
    public function update($id = null)
    {
        if ($resp = $this->checkPermission('discipline_performance', 'update'))
            return $resp;

        try {
            $record = $this->disciplinePerformanceModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Record not found', 404);
            }

            $data = $this->input();
            $data['updated_at'] = date('Y-m-d H:i:s');

            if (!$this->disciplinePerformanceModel->update($id, $data)) {
                return $this->respondError(
                    $this->disciplinePerformanceModel->errors() ?: 'Failed to update record',
                    422
                );
            }

            $updated = $this->disciplinePerformanceModel->find($id);

            return $this->respondSuccess($updated, 'Record updated successfully');

        } catch (\Throwable $e) {
            log_message('error', '[DISCIPLINE_PERFORMANCE][UPDATE] ' . $e->getMessage());
            return $this->respondError('Server error while updating record: ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ❌ DELETE: Soft Delete (Restricted)
    // -------------------------------------------------------
    public function delete($id = null)
    {
        if ($resp = $this->checkPermission('discipline_performance', 'delete'))
            return $resp;

        try {
            $record = $this->disciplinePerformanceModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Record not found', 404);
            }

            $this->disciplinePerformanceModel->delete($id);

            return $this->respondSuccess(['id' => $id], 'Record deleted successfully');

        } catch (\Throwable $e) {
            log_message('error', '[DISCIPLINE_PERFORMANCE][DELETE] ' . $e->getMessage());
            return $this->respondError('Failed to delete record: ' . $e->getMessage(), 500);
        }
    }
}
