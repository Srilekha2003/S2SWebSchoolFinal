<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

$routes->get('/', 'Home::index');

$routes->group('api/v1', ['namespace' => 'App\Controllers\Api'], function ($routes) {

    // Token Generated (requires valid credentials)
    $routes->post('token/generate', 'UsersController::generateToken');

    // Token refresh (requires Bearer token)
    $routes->post('token/refresh', 'UsersController::refreshToken');

    // Public: login/logout
    $routes->post('users/login', 'UsersController::login');
    $routes->post('users/logout', 'UsersController::logout');

    // Public: index/show
    $publicControllers = [
        'modules'               => 'ModulesController',
        'module-permissions'    => 'ModulePermissionsController',
        'roles'                 => 'RolesController',
        'users'                 => 'UsersController',
        'schools'               => 'SchoolsController',
        'branches'              => 'BranchesController',
        'faculty'               => 'FacultyController',
        'classes'               => 'ClassesController',
        'students'              => 'StudentsController',
        'timetable'             => 'TimetableController',
        'attendance'            => 'AttendanceController',
        'exam'                  => 'ExamController',
        'exam_scores'           => 'ExamScoreController',
        'progress_cards'        => 'ProgressCardController',
        'subjects'              => 'SubjectController',
        'assignments_homework'  => 'AssignmentsHomeworkController',
        'notifications'         => 'NotificationsController',
        'cultural_activities'   => 'CulturalActivitiesController',
        'fee_management'        => 'FeeManagementController',
        'library_books'        => 'LibraryBooksController',
        'hostel_rooms'        => 'HostelRoomsController',
        'hostel_allocations'       => 'HostelAllocationsController',
        'library_book_issue'       => 'LibraryBookIssueController',
        'transport'                => 'TransportController',
        'medical_records'          => 'MedicalRecordsController',
        'gallery'                  => 'GalleryController',
        'student_leave_management'         => 'StudentLeaveManagementController',
        'faculty_leave_management'         => 'FacultyLeaveManagementController',
        'faculty_salary'         => 'FacultySalaryController',
        'school_calendar'         => 'SchoolCalendarController',
        'discipline_performance'         => 'DisciplinePerformanceController',

    ];

    foreach ($publicControllers as $route => $controller) {
        $routes->resource($route, ['controller' => $controller, 'only' => ['index', 'show']]);
    }

    // Protected (Auth)
    $routes->group('', ['filter' => 'auth'], function ($routes) use ($publicControllers) {

        foreach ($publicControllers as $route => $controller) {
            $routes->post($route, "$controller::create");
            $routes->put("$route/(:num)", "$controller::update/$1");
            $routes->delete("$route/(:num)", "$controller::delete/$1");
        }
    });
});