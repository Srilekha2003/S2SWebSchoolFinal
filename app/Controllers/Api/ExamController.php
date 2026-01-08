<?php

namespace App\Controllers\Api;

use App\Models\ExamModel;
use CodeIgniter\HTTP\IncomingRequest;

class ExamController extends BaseApiController
{
    protected ExamModel $examModel;
    protected IncomingRequest $req;

    public function __construct()
    {
        parent::__construct();
        $this->examModel = new ExamModel();
        $this->req = service('request');
    }

    // -------------------------------------------------------
    // 📋 GET: List all Exams (Public Allowed)
    // -------------------------------------------------------
    public function index()
    {
        if ($resp = $this->checkPermission('exam', 'index', true))
            return $resp;

        try {
            $query = $this->examModel->where('deleted_at', null);

            // Optional filters
            $filters = [
                'school_id',
                'branch_id',
                'class_id',
                'faculty_id',
                'exam_type',
                'subject',
                'status'
            ];

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
            ], 'Exams fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[EXAM][INDEX] ' . $e->getMessage());
            return $this->respondError('Failed to fetch exams ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // 📋 GET: Single Exam
    // -------------------------------------------------------
    public function show($id = null)
    {
        if ($resp = $this->checkPermission('exam', 'show', true))
            return $resp;

        try {
            $record = $this->examModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Exam not found', 404);
            }

            return $this->respondSuccess($record, 'Exam fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[EXAM][SHOW] ' . $e->getMessage());
            return $this->respondError('Failed to fetch exam ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ➕ POST: Create Exam (Restricted)
    // -------------------------------------------------------
    public function create()
    {
        if ($resp = $this->checkPermission('exam', 'create'))
            return $resp;

        try {
            $data = $this->input();

            if (
                empty($data['school_id']) ||
                empty($data['exam_name']) ||
                empty($data['class_id']) ||
                empty($data['subject'])
            ) {
                return $this->respondError(
                    'school_id, exam_name, class_id and subject are required',
                    422
                );
            }

            if (!$this->examModel->insert($data)) {
                return $this->respondError(
                    $this->examModel->errors() ?: 'Failed to create exam',
                    422
                );
            }

            $id = $this->examModel->getInsertID();
            $record = $this->examModel->find($id);

            return $this->respondSuccess($record, 'Exam created successfully', 201);

        } catch (\Throwable $e) {
            log_message('error', '[EXAM][CREATE] ' . $e->getMessage());
            return $this->respondError('Server error while creating exam ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ✏️ PUT: Update Exam (Restricted)
    // -------------------------------------------------------
    public function update($id = null)
    {
        if ($resp = $this->checkPermission('exam', 'update'))
            return $resp;

        try {
            $record = $this->examModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Exam not found', 404);
            }

            $data = $this->input();
            $data['updated_at'] = date('Y-m-d H:i:s');

            if (!$this->examModel->update($id, $data)) {
                return $this->respondError(
                    $this->examModel->errors() ?: 'Failed to update exam',
                    422
                );
            }

            $updated = $this->examModel->find($id);
            return $this->respondSuccess($updated, 'Exam updated successfully');

        } catch (\Throwable $e) {
            log_message('error', '[EXAM][UPDATE] ' . $e->getMessage());
            return $this->respondError('Server error while updating exam ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ❌ DELETE: Soft Delete Exam (Restricted)
    // -------------------------------------------------------
    public function delete($id = null)
    {
        if ($resp = $this->checkPermission('exam', 'delete'))
            return $resp;

        try {
            $record = $this->examModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Exam not found', 404);
            }

            $this->examModel->delete($id);
            return $this->respondSuccess(['id' => $id], 'Exam deleted successfully');

        } catch (\Throwable $e) {
            log_message('error', '[EXAM][DELETE] ' . $e->getMessage());
            return $this->respondError('Failed to delete exam ' . $e->getMessage(), 500);
        }
    }
}
