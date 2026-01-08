<?php

namespace App\Controllers\Api;

use App\Models\ExamScoreModel;
use CodeIgniter\HTTP\IncomingRequest;

class ExamScoreController extends BaseApiController
{
    protected ExamScoreModel $examScoreModel;
    protected IncomingRequest $req;

    public function __construct()
    {
        parent::__construct();
        $this->examScoreModel = new ExamScoreModel();
        $this->req = service('request');
    }

    // -------------------------------------------------------
    // 📋 GET: List all Exam Scores (Public Allowed)
    // -------------------------------------------------------
    public function index()
    {
        if ($resp = $this->checkPermission('exam_score', 'index', true))
            return $resp;

        try {
            $query = $this->examScoreModel->where('deleted_at', null);

            // Optional filters
            $filters = ['school_id', 'branch_id', 'exam_id', 'student_id', 'result_status'];
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
            ], 'Exam scores fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[EXAM_SCORE][INDEX] ' . $e->getMessage());
            return $this->respondError('Failed to fetch exam scores' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // 📋 GET: Single Exam Score
    // -------------------------------------------------------
    public function show($id = null)
    {
        if ($resp = $this->checkPermission('exam_scores', 'show', true))
            return $resp;

        try {
            $record = $this->examScoreModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Exam score not found', 404);
            }

            return $this->respondSuccess($record, 'Exam score fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[EXAM_SCORE][SHOW] ' . $e->getMessage());
            return $this->respondError('Failed to fetch exam score' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ➕ POST: Create Exam Score (Restricted)
    // -------------------------------------------------------
    public function create()
    {
        if ($resp = $this->checkPermission('exam_scores', 'create'))
            return $resp;

        try {
            $data = $this->input();

            $required = ['school_id','branch_id','exam_id','student_id','marks_obtained','result_status'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return $this->respondError("$field is required", 422);
                }
            }

            if (!$this->examScoreModel->insert($data)) {
                return $this->respondError(
                    $this->examScoreModel->errors() ?: 'Failed to create exam score',
                    422
                );
            }

            $id = $this->examScoreModel->getInsertID();
            $record = $this->examScoreModel->find($id);

            return $this->respondSuccess($record, 'Exam score created successfully', 201);

        } catch (\Throwable $e) {
            log_message('error', '[EXAM_SCORE][CREATE] ' . $e->getMessage());
            return $this->respondError('Server error while creating exam score' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ✏️ PUT: Update Exam Score (Restricted)
    // -------------------------------------------------------
    public function update($id = null)
    {
        if ($resp = $this->checkPermission('exam_scores', 'update'))
            return $resp;

        try {
            $record = $this->examScoreModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Exam score not found', 404);
            }

            $data = $this->input();
            $data['updated_at'] = date('Y-m-d H:i:s');

            if (!$this->examScoreModel->update($id, $data)) {
                return $this->respondError(
                    $this->examScoreModel->errors() ?: 'Failed to update exam score',
                    422
                );
            }

            $updated = $this->examScoreModel->find($id);

            return $this->respondSuccess($updated, 'Exam score updated successfully');

        } catch (\Throwable $e) {
            log_message('error', '[EXAM_SCORE][UPDATE] ' . $e->getMessage());
            return $this->respondError('Server error while updating exam score' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ❌ DELETE: Soft Delete (Restricted)
    // -------------------------------------------------------
    public function delete($id = null)
    {
        if ($resp = $this->checkPermission('exam_scores', 'delete'))
            return $resp;

        try {
            $record = $this->examScoreModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Exam score not found', 404);
            }

            $this->examScoreModel->delete($id);

            return $this->respondSuccess(['id' => $id], 'Exam score deleted successfully');

        } catch (\Throwable $e) {
            log_message('error', '[EXAM_SCORE][DELETE] ' . $e->getMessage());
            return $this->respondError('Failed to delete exam score' . $e->getMessage(), 500);
        }
    }
}
