<?php

namespace App\Controllers\Api;

use App\Models\LibraryBooksModel;
use CodeIgniter\HTTP\IncomingRequest;

class LibraryBooksController extends BaseApiController
{
    protected LibraryBooksModel $libraryBooksModel;
    protected IncomingRequest $req;

    public function __construct()
    {
        parent::__construct();
        $this->libraryBooksModel = new LibraryBooksModel();
        $this->req = service('request');
    }

    // -------------------------------------------------------
    // 📋 GET: List all Library Books (Public Allowed)
    // -------------------------------------------------------
    public function index()
    {
        if ($resp = $this->checkPermission('library_books', 'index', true))
            return $resp;

        try {
            $query = $this->libraryBooksModel->where('deleted_at', null);

            // Optional filters
            $filters = ['school_id', 'branch_id', 'category', 'availability_status'];
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
            ], 'Library books fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[LIBRARY_BOOKS][INDEX] ' . $e->getMessage());
            return $this->respondError(
                'Failed to fetch library books' . $e->getMessage(),
                500
            );
        }
    }

    // -------------------------------------------------------
    // 📋 GET: Single Library Book record
    // -------------------------------------------------------
    public function show($id = null)
    {
        if ($resp = $this->checkPermission('library_books', 'show', true))
            return $resp;

        try {
            $record = $this->libraryBooksModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Library book not found', 404);
            }

            return $this->respondSuccess(
                $record,
                'Library book fetched successfully'
            );

        } catch (\Throwable $e) {
            log_message('error', '[LIBRARY_BOOKS][SHOW] ' . $e->getMessage());
            return $this->respondError(
                'Failed to fetch library book' . $e->getMessage(),
                500
            );
        }
    }

    // -------------------------------------------------------
    // ➕ POST: Create Library Book record (Restricted)
    // -------------------------------------------------------
    public function create()
    {
        if ($resp = $this->checkPermission('library_books', 'create'))
            return $resp;

        try {
            $data = $this->input();

            if (
                empty($data['school_id']) ||
                empty($data['branch_id']) ||
                empty($data['title']) ||
                empty($data['author'])
            ) {
                return $this->respondError(
                    'school_id, branch_id, title and author are required',
                    422
                );
            }

            if (!$this->libraryBooksModel->insert($data)) {
                return $this->respondError(
                    $this->libraryBooksModel->errors()
                        ?: 'Failed to create library book',
                    422
                );
            }

            $id = $this->libraryBooksModel->getInsertID();
            $record = $this->libraryBooksModel->find($id);

            return $this->respondSuccess(
                $record,
                'Library book created successfully',
                201
            );

        } catch (\Throwable $e) {
            log_message('error', '[LIBRARY_BOOKS][CREATE] ' . $e->getMessage());
            return $this->respondError(
                'Server error while creating library book' . $e->getMessage(),
                500
            );
        }
    }

    // -------------------------------------------------------
    // ✏️ PUT: Update Library Book record (Restricted)
    // -------------------------------------------------------
    public function update($id = null)
    {
        if ($resp = $this->checkPermission('library_books', 'update'))
            return $resp;

        try {
            $record = $this->libraryBooksModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Library book not found', 404);
            }

            $data = $this->input();
            $data['updated_at'] = date('Y-m-d H:i:s');

            if (!$this->libraryBooksModel->update($id, $data)) {
                return $this->respondError(
                    $this->libraryBooksModel->errors()
                        ?: 'Failed to update library book',
                    422
                );
            }

            $updated = $this->libraryBooksModel->find($id);

            return $this->respondSuccess(
                $updated,
                'Library book updated successfully'
            );

        } catch (\Throwable $e) {
            log_message('error', '[LIBRARY_BOOKS][UPDATE] ' . $e->getMessage());
            return $this->respondError(
                'Server error while updating library book' . $e->getMessage(),
                500
            );
        }
    }

    // -------------------------------------------------------
    // ❌ DELETE: Soft Delete (Restricted)
    // -------------------------------------------------------
    public function delete($id = null)
    {
        if ($resp = $this->checkPermission('library_books', 'delete'))
            return $resp;

        try {
            $record = $this->libraryBooksModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Library book not found', 404);
            }

            // Use CI4 soft delete
            $this->libraryBooksModel->delete($id);

            return $this->respondSuccess(
                ['id' => $id],
                'Library book deleted successfully'
            );

        } catch (\Throwable $e) {
            log_message('error', '[LIBRARY_BOOKS][DELETE] ' . $e->getMessage());
            return $this->respondError(
                'Failed to delete library book' . $e->getMessage(),
                500
            );
        }
    }
}
