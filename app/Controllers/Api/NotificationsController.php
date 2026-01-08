<?php

namespace App\Controllers\Api;

use App\Models\NotificationsModel;
use CodeIgniter\HTTP\IncomingRequest;

class NotificationsController extends BaseApiController
{
    protected NotificationsModel $notificationsModel;
    protected IncomingRequest $req;

    public function __construct()
    {
        parent::__construct();
        $this->notificationsModel = new NotificationsModel();
        $this->req = service('request');
    }

    // -------------------------------------------------------
    // 📋 GET: List all Notifications (Public Allowed)
    // -------------------------------------------------------
    public function index()
    {
        if ($resp = $this->checkPermission('notifications', 'index', true))
            return $resp;

        try {
            $query = $this->notificationsModel->where('deleted_at', null);

            // Optional filters
            $filters = ['school_id', 'branch_id', 'class_id', 'recipient_type', 'type', 'status'];
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
            ], 'Notifications fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[NOTIFICATIONS][INDEX] ' . $e->getMessage());
            return $this->respondError('Failed to fetch notifications ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // 📋 GET: Single Notification
    // -------------------------------------------------------
    public function show($id = null)
    {
        if ($resp = $this->checkPermission('notifications', 'show', true))
            return $resp;

        try {
            $record = $this->notificationsModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Notification not found', 404);
            }

            return $this->respondSuccess($record, 'Notification fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[NOTIFICATIONS][SHOW] ' . $e->getMessage());
            return $this->respondError('Failed to fetch notification ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ➕ POST: Create Notification (Restricted)
    // -------------------------------------------------------
    public function create()
    {
        if ($resp = $this->checkPermission('notifications', 'create'))
            return $resp;

        try {
            $data = $this->input();

            if (
                empty($data['school_id']) || empty($data['branch_id']) ||  empty($data['title']) ||  empty($data['message']) || empty($data['date_time']) ||
                empty($data['recipient_type']) ||  empty($data['type'])
            ) {
                return $this->respondError('Required fields are missing', 422);
            }

            if (!$this->notificationsModel->insert($data)) {
                return $this->respondError(
                    $this->notificationsModel->errors() ?: 'Failed to create notification',
                    422
                );
            }

            $id = $this->notificationsModel->getInsertID();
            $record = $this->notificationsModel->find($id);

            return $this->respondSuccess($record, 'Notification created successfully', 201);

        } catch (\Throwable $e) {
            log_message('error', '[NOTIFICATIONS][CREATE] ' . $e->getMessage());
            return $this->respondError('Server error while creating notification ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ✏️ PUT: Update Notification (Restricted)
    // -------------------------------------------------------
    public function update($id = null)
    {
        if ($resp = $this->checkPermission('notifications', 'update'))
            return $resp;

        try {
            $record = $this->notificationsModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Notification not found', 404);
            }

            $data = $this->input();
            $data['updated_at'] = date('Y-m-d H:i:s');

            if (!$this->notificationsModel->update($id, $data)) {
                return $this->respondError(
                    $this->notificationsModel->errors() ?: 'Failed to update notification',
                    422
                );
            }

            $updated = $this->notificationsModel->find($id);

            return $this->respondSuccess($updated, 'Notification updated successfully');

        } catch (\Throwable $e) {
            log_message('error', '[NOTIFICATIONS][UPDATE] ' . $e->getMessage());
            return $this->respondError('Server error while updating notification ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ❌ DELETE: Soft Delete Notification (Restricted)
    // -------------------------------------------------------
    public function delete($id = null)
    {
        if ($resp = $this->checkPermission('notifications', 'delete'))
            return $resp;

        try {
            $record = $this->notificationsModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Notification not found', 404);
            }

            $this->notificationsModel->delete($id);

            return $this->respondSuccess(['id' => $id], 'Notification deleted successfully');

        } catch (\Throwable $e) {
            log_message('error', '[NOTIFICATIONS][DELETE] ' . $e->getMessage());
            return $this->respondError('Failed to delete notification ' . $e->getMessage(), 500);
        }
    }
}
