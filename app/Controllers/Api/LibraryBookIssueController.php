<?php

namespace App\Controllers\Api;

use App\Models\LibraryBookIssueModel;
use CodeIgniter\HTTP\IncomingRequest;

class LibraryBookIssueController extends BaseApiController
{
    protected LibraryBookIssueModel $libraryBookIssueModel;
    protected IncomingRequest $req;

    public function __construct()
    {
        parent::__construct();
        $this->libraryBookIssueModel = new LibraryBookIssueModel();
        $this->req = service('request');
    }

    // -------------------------------------------------------
    // 📋 GET: List all Library Book Issue records (Public Allowed)
    // -------------------------------------------------------
    public function index()
    {
        if ($resp = $this->checkPermission('library_book_issue', 'index', true))
            return $resp;

        try {
            $query = $this->libraryBookIssueModel->where('deleted_at', null);

            // Optional filters
            $filters = ['school_id', 'branch_id', 'student_id', 'book_id', 'return_status'];
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
            ], 'Library book issue records fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[LIBRARY_BOOK_ISSUE][INDEX] ' . $e->getMessage());
            return $this->respondError('Failed to fetch library book issue records ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // 📋 GET: Single Library Book Issue record
    // -------------------------------------------------------
    public function show($id = null)
    {
        if ($resp = $this->checkPermission('library_book_issue', 'show', true))
            return $resp;

        try {
            $record = $this->libraryBookIssueModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Library book issue record not found', 404);
            }

            return $this->respondSuccess($record, 'Library book issue record fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[LIBRARY_BOOK_ISSUE][SHOW] ' . $e->getMessage());
            return $this->respondError('Failed to fetch library book issue record ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ➕ POST: Create Library Book Issue record (Restricted)
    // -------------------------------------------------------
    public function create()
    {
        if ($resp = $this->checkPermission('library_book_issue', 'create'))
            return $resp;

        try {
            $data = $this->input();

            if (empty($data['book_id']) || empty($data['student_id']) || empty($data['issue_date']) || empty($data['due_date'])) {
                return $this->respondError('book_id, student_id, issue_date and due_date are required', 422);
            }

            if (!$this->libraryBookIssueModel->insert($data)) {
                return $this->respondError(
                    $this->libraryBookIssueModel->errors() ?: 'Failed to create library book issue record',
                    422
                );
            }

            $id = $this->libraryBookIssueModel->getInsertID();
            $record = $this->libraryBookIssueModel->find($id);

            return $this->respondSuccess($record, 'Library book issue record created successfully', 201);

        } catch (\Throwable $e) {
            log_message('error', '[LIBRARY_BOOK_ISSUE][CREATE] ' . $e->getMessage());
            return $this->respondError('Server error while creating library book issue record ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ✏️ PUT: Update Library Book Issue record (Restricted)
    // -------------------------------------------------------
    public function update($id = null)
    {
        if ($resp = $this->checkPermission('library_book_issue', 'update'))
            return $resp;

        try {
            $record = $this->libraryBookIssueModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Library book issue record not found', 404);
            }

            $data = $this->input();
            $data['updated_at'] = date('Y-m-d H:i:s');

            if (!$this->libraryBookIssueModel->update($id, $data)) {
                return $this->respondError(
                    $this->libraryBookIssueModel->errors() ?: 'Failed to update library book issue record',
                    422
                );
            }

            $updated = $this->libraryBookIssueModel->find($id);

            return $this->respondSuccess($updated, 'Library book issue record updated successfully');

        } catch (\Throwable $e) {
            log_message('error', '[LIBRARY_BOOK_ISSUE][UPDATE] ' . $e->getMessage());
            return $this->respondError('Server error while updating library book issue record ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ❌ DELETE: Soft Delete (Restricted)
    // -------------------------------------------------------
    public function delete($id = null)
    {
        if ($resp = $this->checkPermission('library_book_issue', 'delete'))
            return $resp;

        try {
            $record = $this->libraryBookIssueModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Library book issue record not found', 404);
            }

            $this->libraryBookIssueModel->delete($id);

            return $this->respondSuccess(['id' => $id], 'Library book issue record deleted successfully');

        } catch (\Throwable $e) {
            log_message('error', '[LIBRARY_BOOK_ISSUE][DELETE] ' . $e->getMessage());
            return $this->respondError('Failed to delete library book issue record ' . $e->getMessage(), 500);
        }
    }
}
