<?php

namespace App\Controllers\Api;

use App\Models\SubjectModel;
use CodeIgniter\HTTP\IncomingRequest;

class SubjectController extends BaseApiController
{
    protected SubjectModel $subjectModel;
    protected IncomingRequest $req;

    public function __construct()
    {
        parent::__construct();
        $this->subjectModel = new SubjectModel();
        $this->req = service('request');
    }

    // -------------------------------------------------------
    // 📋 GET: List all Subject records (Public Allowed)
    // -------------------------------------------------------
    public function index()
    {
        if ($resp = $this->checkPermission('subjects', 'index', true))
            return $resp;

        try {
            $query = $this->subjectModel->where('deleted_at', null);

            // Optional filters
            $filters = ['school_id', 'branch_id', 'class_id', 'faculty_id'];
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
            ], 'Subjects fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[SUBJECT][INDEX] ' . $e->getMessage());
            return $this->respondError('Failed to fetch subjects ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // 📋 GET: Single Subject record
    // -------------------------------------------------------
    public function show($id = null)
    {
        if ($resp = $this->checkPermission('subjects', 'show', true))
            return $resp;

        try {
            $record = $this->subjectModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Subject not found', 404);
            }

            return $this->respondSuccess($record, 'Subject fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[SUBJECT][SHOW] ' . $e->getMessage());
            return $this->respondError('Failed to fetch subject ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ➕ POST: Create Subject (Restricted)
    // -------------------------------------------------------
    public function create()
    {
        if ($resp = $this->checkPermission('subjects', 'create'))
            return $resp;

        try {
            $data = $this->input();

            if (empty($data['name']) || empty($data['code'])) {
                return $this->respondError('Subject name and code are required', 422);
            }

            if (!$this->subjectModel->insert($data)) {
                return $this->respondError(
                    $this->subjectModel->errors() ?: 'Failed to create subject',
                    422
                );
            }

            $id = $this->subjectModel->getInsertID();
            $record = $this->subjectModel->find($id);

            return $this->respondSuccess($record, 'Subject created successfully', 201);

        } catch (\Throwable $e) {
            log_message('error', '[SUBJECT][CREATE] ' . $e->getMessage());
            return $this->respondError('Server error while creating subject ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ✏️ PUT: Update Subject (Restricted)
    // -------------------------------------------------------
    public function update($id = null)
    {
        if ($resp = $this->checkPermission('subjects', 'update'))
            return $resp;

        try {
            $record = $this->subjectModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Subject not found', 404);
            }

            $data = $this->input();
            $data['updated_at'] = date('Y-m-d H:i:s');

            if (!$this->subjectModel->update($id, $data)) {
                return $this->respondError(
                    $this->subjectModel->errors() ?: 'Failed to update subject',
                    422
                );
            }

            $updated = $this->subjectModel->find($id);
            return $this->respondSuccess($updated, 'Subject updated successfully');

        } catch (\Throwable $e) {
            log_message('error', '[SUBJECT][UPDATE] ' . $e->getMessage());
            return $this->respondError('Server error while updating subject ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ❌ DELETE: Soft Delete Subject (Restricted)
    // -------------------------------------------------------
    public function delete($id = null)
    {
        if ($resp = $this->checkPermission('subjects', 'delete'))
            return $resp;

        try {
            $record = $this->subjectModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Subject not found', 404);
            }

            $this->subjectModel->delete($id);

            return $this->respondSuccess(['id' => $id], 'Subject deleted successfully');

        } catch (\Throwable $e) {
            log_message('error', '[SUBJECT][DELETE] ' . $e->getMessage());
            return $this->respondError('Failed to delete subject ' . $e->getMessage(), 500);
        }
    }
}
