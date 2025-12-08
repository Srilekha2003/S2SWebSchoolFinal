<?php

namespace App\Controllers\Api;

use App\Models\ModulesModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\IncomingRequest;

class ModulesController extends BaseApiController
{
    use ResponseTrait;

    protected ModulesModel $modulesModel;
    protected IncomingRequest $req;

    public function __construct()
    {
        parent::__construct();
        $this->modulesModel = new ModulesModel();
        $this->req = service('request'); // ✔ Always works for getFile()
    }

    // -------------------------------------------------------
    // GET: /api/v1/modules
    // -------------------------------------------------------
    public function index()
    {
        try {
            $status = $this->input('status');

            $query = $this->modulesModel->where('deleted_at', null);

            if ($status) {
                $query->where('status', $status);
            }

            $modules = $query->orderBy('id', 'ASC')->findAll();

            return $this->respondSuccess([
                'count' => count($modules),
                'data'  => $modules
            ], 'Modules fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[ModulesController::index] ' . $e->getMessage());
            return $this->respondError('Failed to fetch modules', 500);
        }
    }

    // -------------------------------------------------------
    // GET: /api/v1/modules/{id}
    // -------------------------------------------------------
    public function show($id = null)
    {
        try {
            $module = $this->modulesModel
                ->where('id', $id)
                ->where('deleted_at', null)
                ->first();

            if (!$module) {
                return $this->respondError('Module not found', 404);
            }

            return $this->respondSuccess($module, 'Module fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[ModulesController::show] ' . $e->getMessage());
            return $this->respondError('Failed to fetch module', 500);
        }
    }

    // -------------------------------------------------------
    // POST: /api/v1/modules
    // -------------------------------------------------------
    public function create()
    {
        try {
            $data = $this->input();

            if (empty($data['module_key']) || empty($data['module_name'])) {
                return $this->respondError('module_key and module_name are required', 422);
            }

            $exists = $this->modulesModel
                ->where('module_key', strtolower($data['module_key']))
                ->where('deleted_at', null)
                ->first();

            if ($exists) {
                return $this->respondError('Module key already exists', 422);
            }

            // Convert settings array to JSON
            if (isset($data['settings']) && is_array($data['settings'])) {
                $data['settings'] = json_encode($data['settings'], JSON_UNESCAPED_UNICODE);
            }

            // File upload
            $file = $this->req->getFile('module_image');

            if ($file && $file->isValid()) {

                $path = uploadFile($file, 'modules', [
                    'jpg','jpeg','png','webp']);

                if (!$path) {
                    return $this->respondError('Invalid file or upload failed', 422);
                }

                $data['module_image'] = $path;
            }

            $data['module_key'] = strtolower(trim($data['module_key']));
            $data['created_at'] = date('Y-m-d H:i:s');

            $id = $this->modulesModel->insert($data);

            if (!$id) {
                return $this->respondError('Failed to create module', 500);
            }

            return $this->respondSuccess(
                $this->modulesModel->find($id),
                'Module created successfully',
                201
            );

        } catch (\Throwable $e) {
            log_message('error', '[ModulesController::create] ' . $e->getMessage());
            return $this->respondError('Server error while creating module', 500);
        }
    }

    // -------------------------------------------------------
    // PUT: /api/v1/modules/{id}
    // -------------------------------------------------------
    public function update($id = null)
    {
        try {
            $module = $this->modulesModel
                ->where('id', $id)
                ->where('deleted_at', null)
                ->first();

            if (!$module) {
                return $this->respondError('Module not found', 404);
            }

            // Don't allow system module key change
            if ($module['is_system'] == 1) {
                unset($_POST['module_key']);
            }

            $data = $this->input();

            if (isset($data['settings']) && is_array($data['settings'])) {
                $data['settings'] = json_encode($data['settings'], JSON_UNESCAPED_UNICODE);
            }

            // File upload (optional)
            $file = $this->req->getFile('module_image');

            if ($file && $file->isValid()) {

                $path = uploadFile($file, 'modules', [
                    'jpg','jpeg','png','webp']);

                if (!$path) {
                    return $this->respondError('Invalid file or upload failed', 422);
                }

                // Delete old file
                if (!empty($module['module_image'])) {
                    $publicPath = realpath(FCPATH);
                    $old = $publicPath . $module['module_image'];
                    if (is_file($old)) @unlink($old);
                }

                $data['module_image'] = $path;

            }

            $data['updated_at'] = date('Y-m-d H:i:s');

            $this->modulesModel->update($id, $data);

            return $this->respondSuccess(
                $this->modulesModel->find($id),
                'Module updated successfully'
            );

        } catch (\Throwable $e) {
            log_message('error', '[ModulesController::update] ' . $e->getMessage());
            return $this->respondError('Server error while updating module', 500);
        }
    }

    // -------------------------------------------------------
    // DELETE: /api/v1/modules/{id}
    // -------------------------------------------------------
    public function delete($id = null)
    {
        try {
            $module = $this->modulesModel
                ->where('id', $id)
                ->where('deleted_at', null)
                ->first();

            if (!$module) {
                return $this->respondError('Module not found', 404);
            }

            if ($module['is_system'] == 1) {
                return $this->respondError('System modules cannot be deleted', 403);
            }

            if (!empty($module['module_image'])) {
                $publicPath = realpath(FCPATH);
                $file = $publicPath . $module['module_image'];
                if (is_file($file)) @unlink($file);
            }

            $this->modulesModel->delete($id);

            return $this->respondSuccess(['id' => $id], 'Module deleted successfully');

        } catch (\Throwable $e) {
            log_message('error', '[ModulesController::delete] ' . $e->getMessage());
            return $this->respondError('Failed to delete module', 500);
        }
    }
}
