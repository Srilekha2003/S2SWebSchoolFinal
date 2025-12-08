<?php

namespace App\Controllers\Api;

use App\Models\UsersModel;
use App\Models\VillagesModel;
use App\Models\RolesModel;
use App\Models\ModulePermissionsModel;
use App\Models\ModulesModel;
use CodeIgniter\HTTP\IncomingRequest;

class UsersController extends BaseApiController
{
    protected UsersModel $usersModel;
    protected VillagesModel $villagesModel;
    protected ModulesModel $modulesModel;
    protected ModulePermissionsModel $modulePermModel;

    protected IncomingRequest $req;

    public function __construct()
    {
        parent::__construct();

        helper(['auth', 'api']);

        $this->usersModel       = new UsersModel();
        $this->villagesModel    = new VillagesModel();
        $this->rolesModel       = new RolesModel();
        $this->modulesModel     = new ModulesModel();
        $this->modulePermModel  = new ModulePermissionsModel();
        $this->req              = service('request'); // For getFile()
    }

    // -----------------------------------------------------------------------------
    // GET: List Users
    // -----------------------------------------------------------------------------
    public function index()
    {
        if ($resp = $this->checkPermission('users', 'index', true)) return $resp;

        try {
            $users = $this->usersModel
                ->select('users.*, roles.role_name')
                ->join('roles', 'roles.id = users.role_id', 'left')
                ->where('users.deleted_at', null)
                ->orderBy('users.id', 'DESC')
                ->findAll();

            return $this->respondSuccess([
                'count' => count($users),
                'data'  => $users
            ], 'Users fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[UsersController::index] ' . $e->getMessage());
            return $this->respondError('Failed to fetch users', 500);
        }
    }

    // -----------------------------------------------------------------------------
    // GET: Single user
    // -----------------------------------------------------------------------------
    public function show($id = null)
    {
        if ($resp = $this->checkPermission('users', 'show', true)) return $resp;

        try {
            $user = $this->usersModel
                ->select('users.*, roles.role_name')
                ->join('roles', 'roles.id = users.role_id', 'left')
                ->where('users.id', $id)
                ->where('users.deleted_at', null)
                ->first();

            if (!$user) return $this->respondError('User not found', 404);

            return $this->respondSuccess($user, 'User fetched successfully');
        } catch (\Throwable $e) {
            log_message('error', '[UsersController::show] ' . $e->getMessage());
            return $this->respondError('Failed to fetch user', 500);
        }
    }

    // -----------------------------------------------------------------------------
    // POST: Create user
    // -----------------------------------------------------------------------------
    public function create()
    {
        if ($resp = $this->checkPermission('users', 'create')) return $resp;

        try {
            $data = $this->input();

            if (empty($data['name']) || empty($data['email'])) {
                return $this->respondError('Name & Email required', 422);
            }

            if ($this->usersModel->where('email', $data['email'])->first()) {
                return $this->respondError('Email already exists', 422);
            }

            // Validate role
            if (!isset($data['role_id']) || !$this->rolesModel->find($data['role_id'])) {
                return $this->respondError('Invalid role_id', 422);
            }

            // Upload profile pic
            $file = $this->req->getFile('profile_pic');
            if ($file && $file->isValid()) {

                $path = uploadFile($file, 'users', ['jpg', 'jpeg', 'png', 'webp']);
                if (!$path) return $this->respondError('Upload failed', 422);

                $data['profile_pic'] = $path;
            }

            $id = $this->usersModel->insert($data);
            if (!$id) return $this->respondError('Failed to create user', 500);

            $user = $this->usersModel->find($id);
            return $this->respondSuccess($user, 'User created', 201);

        } catch (\Throwable $e) {
            log_message('error', '[UsersController::create] ' . $e->getMessage());
            return $this->respondError('Error creating user', 500);
        }
    }

    // -----------------------------------------------------------------------------
    // PUT: Update user
    // -----------------------------------------------------------------------------
    public function update($id = null)
    {
        if ($resp = $this->checkPermission('users', 'update')) return $resp;

        try {
            $existing = $this->usersModel->find($id);
            if (!$existing) return $this->respondError('User not found', 404);

            $data = $this->input();

            // Prevent email duplicate
            if (!empty($data['email'])) {
                $exists = $this->usersModel->where('email', $data['email'])->where('id !=', $id)->first();
                if ($exists) return $this->respondError('Email already exists', 422);
            }

            // Upload file
            $file = $this->req->getFile('profile_pic');
            if ($file && $file->isValid()) {

                $path = uploadFile($file, 'users', ['jpg', 'jpeg', 'png', 'webp']);
                if (!$path) return $this->respondError('Upload failed', 422);

                // Delete old file
                if (!empty($module['profile_pic'])) {
                    $publicPath = realpath(FCPATH);
                    $old = $publicPath . $module['profile_pic'];
                    if (is_file($old)) @unlink($old);
                }

                $data['profile_pic'] = $path;
            }

            $this->usersModel->update($id, $data);

            $user = $this->usersModel->find($id);
            return $this->respondSuccess($user, 'User updated');

        } catch (\Throwable $e) {
            log_message('error', '[UsersController::update] ' . $e->getMessage());
            return $this->respondError('Failed to update user', 500);
        }
    }

    // -----------------------------------------------------------------------------
    // DELETE: Soft delete user
    // -----------------------------------------------------------------------------
    public function delete($id = null)
    {
        if ($resp = $this->checkPermission('users', 'delete')) return $resp;

        try {
            $user = $this->usersModel->find($id);
            if (!$user) return $this->respondError('User not found', 404);

            // Delete old file
            if (!empty($item['profile_pic'])) {
                $publicPath = realpath(FCPATH);
                $old = $publicPath . $item['profile_pic'];
                if (is_file($old)) @unlink($old);
            }

            $this->usersModel->delete($id);
            return $this->respondSuccess(null, 'User deleted');

        } catch (\Throwable $e) {
            log_message('error', '[UsersController::delete] ' . $e->getMessage());
            return $this->respondError('Failed to delete user', 500);
        }
    }

    // -----------------------------------------------------------------------------
    // POST: Login (JWT + Permissions)
    // -----------------------------------------------------------------------------
    public function login()
    {
        try {
            $data = $this->input();
            $email    = $data['email'] ?? null;
            $password = $data['password'] ?? null;

            if (!$email || !$password) {
                return $this->respondError('Email & Password required', 422);
            }

            $user = $this->usersModel
                ->select("users.*, roles.role_name")
                ->join("roles", "roles.id = users.role_id", "left")
                ->where("users.email", $email)
                ->where("users.deleted_at", null)
                ->first();

            if (!$user || !password_verify($password, $user['password'])) {
                return $this->respondError('Invalid credentials', 401);
            }

            // -------------------------------
            // 🔥 Load dynamic permissions
            // -------------------------------
            $permissions = $this->modulePermModel->getPermissionsForRole($user['role_id']) ?? [];

            // -------------------------------
            // 🔐 Generate Access + Refresh Tokens
            // -------------------------------
            $payload = [
                'id'        => (int) $user['id'],
                'role_id'   => (int) $user['role_id'],
                'role_name' => $user['role_name']
            ];

            // Access Token - 1 hour
            $accessToken = createAccessToken($payload, 3600);

            // Refresh Token - 30 days
            $refreshToken = createRefreshToken(['id' => $user['id']], 60 * 60 * 24 * 30);

            // Save refresh token in DB (optional but recommended)
            $this->usersModel->update($user['id'], [
                'last_login'     => date('Y-m-d H:i:s'),
                'last_ip'        => $this->request->getIPAddress(),
                'refresh_token'  => $refreshToken
            ]);

            $user = $this->usersModel->find($user['id']);

            return $this->respondSuccess([
                'access_token'  => $accessToken,
                'refresh_token' => $refreshToken,
                'user'          => $user,
                'permissions'   => $permissions
            ], 'Login successful');

        } catch (\Throwable $e) {
            log_message('error', '[UsersController::login] ' . $e->getMessage());
            return $this->respondError('Error during login', 500);
        }
    }


    // -----------------------------------------------------------------------------
    // POST: Logout
    // -----------------------------------------------------------------------------
    public function logout()
    {
        try {
            $token = getBearerToken();
            if ($token && function_exists('addTokenToBlacklist')) {
                addTokenToBlacklist($token);
            }

            return $this->respondSuccess(null, 'Logged out');
        } catch (\Throwable $e) {
            log_message('error', '[UsersController::logout] ' . $e->getMessage());
            return $this->respondError('Error during logout', 500);
        }
    }

    /* ==========================================================================
        MOBILE USERS
       ========================================================================== */

    /** Create Mobile User */
    public function createMobile()
    {
        try {
            $data = $this->input();

            // Validate role
            if (empty($data['village_id']) || !$this->villagesModel->find($data['village_id'])) {
                return $this->respondError('Invalid village_id', 422);
            }

            if (empty($data['name']) || empty($data['phone'])) {
                return $this->respondError('Name & Phone number required', 422);
            }

            if ($this->usersModel->where('phone', $data['phone'])->countAllResults() > 0) {
                return $this->respondError('Phone already exists', 422);
            }

            $role = $this->rolesModel->where('role_name', 'villager')->first();
            if (!$role) {
                return $this->respondError('Villager role not found in database', 500);
            }
        
            $data['role_id'] = $role['id'];

            if (!$this->usersModel->insert($data)) {
                return $this->respondError('Failed to create user', 422);
            }

            $user = $this->usersModel->find($this->usersModel->getInsertID());

            return $this->respondSuccess($user, 'Mobile user created', 201);

        } catch (\Throwable $e) {
            log_message('error', '[UsersController::createMobile] '.$e->getMessage());
            return $this->respondError('Error creating mobile user'.$e->getMessage(), 500);
        }
    }

    /** Login Mobile User */
    public function loginMobile()
    {
        try {
            $data = $this->input();
            $phone      = trim($data['phone'] ?? '');
            $village_id = $data['village_id'] ?? null;

            if (empty($phone) || empty($village_id)) {
                return $this->respondError('Phone number and village_id are required', 422);
            }

            if (!$this->villagesModel->find($village_id)) {
                return $this->respondError('Invalid village_id', 422);
            }

            $user = $this->usersModel
                ->select("users.*, roles.role_name")
                ->join("roles", "roles.id = users.role_id", "left")
                ->where("users.phone", $phone)
                ->where("users.village_id", $village_id)
                ->where("users.deleted_at", null)
                ->first();

            if (!$user) {
                return $this->respondError('Invalid credentials', 404);
            }

            if ($user['is_verified'] !== 'yes') {
                return $this->respondError(
                    'Your account is not approved yet. Please wait for admin approval.',403);
            }

            return $this->respondSuccess([
                'user'  => $user
            ], 'Login successful');

        } catch (\Throwable $e) {
            log_message('error', '[UsersController::loginMobile] '.$e->getMessage());
            return $this->respondError('Error during mobile login', 500);
        }
    }

    /* ==========================================================================
        TOKEN MANAGEMENT (JWT)
       ========================================================================== */

    /** Generate JWT Token */
    public function generateToken()
    {
        try {
            $data = $this->input();
            $userId = $data['userId'] ?? null;

            if (empty($userId)) {
                return $this->respondError('User ID is required', 422);
            }

            $user = $this->usersModel
                ->select("users.*, roles.role_name")
                ->join("roles", "roles.id = users.role_id", "left")
                ->where("users.id", $userId)
                ->where("users.deleted_at", null)
                ->first();

            if (!$user) {
                return $this->respondError('User not found', 404);
            }

            // Mobile users normally have very limited permissions
            $permissions = $this->modulePermModel->getPermissionsForRole($user['role_id']) ?? [];

            // -------------------------------
            // 🔐 Generate Tokens
            // -------------------------------
            $payload = [
                'id'        => (int) $user['id'],
                'role_id'   => (int) $user['role_id'],
                'role_name' => $user['role_name']
            ];

            // Access Token – 1 hour
            $accessToken = createAccessToken($payload, 3600);

            // Refresh Token – **180 days for mobile**
            $refreshToken = createRefreshToken(
                ['id' => $user['id']],
                60 * 60 * 24 * 180
            );

            // Save refresh token
            $this->usersModel->update($user['id'], [
                'last_login'     => date('Y-m-d H:i:s'),
                'last_ip'        => $this->request->getIPAddress(),
                'refresh_token'  => $refreshToken
            ]);

            $user = $this->usersModel->find($user['id']);

            return $this->respondSuccess([
                'access_token'  => $accessToken,
                'refresh_token' => $refreshToken,
                'user'          => $user,
                'permissions'   => $permissions
            ], 'Token generated successfully');

        } catch (\Throwable $e) {
            log_message('error', '[UsersController::generateToken] ' . $e->getMessage());
            return $this->respondError('Failed to generate token', 500);
        }
    }

    /** Refresh Token */
    public function refreshToken()
    {
        try {
            $data = $this->input();
            $refreshToken = $data['refresh_token'] ?? null;

            if (!$refreshToken) {
                return $this->respondError('refresh_token is required', 422);
            }

            // Decode refresh token
            $jwt = new \App\Libraries\JWTService();
            $decoded = $jwt->decodeToken($refreshToken);

            if (!$decoded || empty($decoded->id)) {
                return $this->respondError('Invalid or expired refresh token', 401);
            }

            // Fetch user
            $user = $this->usersModel
                ->select('users.*, roles.role_name')
                ->join('roles', 'roles.id = users.role_id', 'left')
                ->where('users.id', $decoded->id)
                ->where('users.deleted_at', null)
                ->first();
                
            if (!$user) {
                return $this->respondError('User not found', 404);
            }

            if ($user['status'] !== 'active') {
                return $this->respondError('Account is inactive', 403);
            }

            // If user is villager, they need approval
            if (isset($user['is_verified']) && $user['is_verified'] !== 'yes') {
                return $this->respondError('Account is not verified by admin', 403);
            }

            // Validate refresh token with stored DB value
            if (!isset($user['refresh_token']) || $user['refresh_token'] !== $refreshToken) {
                return $this->respondError('Refresh token mismatch', 401);
            }

            // Load dynamic permissions
            $permissions = $this->modulePermModel->getPermissionsForRole($user['role_id']) ?? [];

            // --------------------------------------
            // 🔥 Generate New Tokens
            // --------------------------------------

            // Access Token – 1 hour
            $accessToken = createAccessToken([
                'id'        => (int) $user['id'],
                'role_id'   => (int) $user['role_id'],
                'role_name' => $user['role_name']
            ], 3600);

            // Refresh Token – 180 days (mobile safe)
            $newRefreshToken = createRefreshToken(
                ['id' => $user['id']],
                60 * 60 * 24 * 180
            );

            // Save new refresh token
            $this->usersModel->update($user['id'], [
                'refresh_token' => $newRefreshToken,
                'last_login'    => date('Y-m-d H:i:s'),
                'last_ip'       => $this->request->getIPAddress()
            ]);

            $user = $this->usersModel->find($user['id']);

            return $this->respondSuccess([
                'access_token'  => $accessToken,
                'refresh_token' => $newRefreshToken,
                'user'          => $user,
                'permissions'   => $permissions
            ], 'Token refreshed successfully');

        } catch (\Throwable $e) {
            log_message('error', '[UsersController::refreshToken] '.$e->getMessage());
            return $this->respondError('Error refreshing token'.$e->getMessage(), 500);
        }
    }
}
