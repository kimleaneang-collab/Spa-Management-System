<?php
declare(strict_types=1);

// Load application configuration and all controllers used by the route table.
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/controllers/AuthController.php';
require_once __DIR__ . '/app/controllers/DashboardController.php';
require_once __DIR__ . '/app/controllers/AppointmentController.php';
require_once __DIR__ . '/app/controllers/ServiceController.php';
require_once __DIR__ . '/app/controllers/TherapistController.php';
require_once __DIR__ . '/app/controllers/RoomController.php';
require_once __DIR__ . '/app/controllers/CustomerController.php';
require_once __DIR__ . '/app/controllers/MembershipController.php';
require_once __DIR__ . '/app/controllers/InventoryController.php';
require_once __DIR__ . '/app/controllers/ProductController.php';
require_once __DIR__ . '/app/controllers/ReportController.php';
require_once __DIR__ . '/app/controllers/UserController.php';
require_once __DIR__ . '/app/controllers/SettingsController.php';

// Read the requested page; unauthenticated visitors start at the login route.
$route = trim(
    (string) ($_GET['route'] ?? 'login'),
    '/'
);

authorize_route($route);

$auth = new AuthController();

// Send each route to the controller action responsible for that feature.
switch ($route) {

    case '':
    case 'login':
        $auth->login();
        break;

    case 'logout':
        $auth->logout();
        break;

    case 'dashboard':
        (new DashboardController())->index();
        break;

    case 'appointments':
        (new AppointmentController())->index();
        break;

    case 'services':
        (new ServiceController())->index();
        break;

    case 'therapists':
        (new TherapistController())->index();
        break;

    case 'rooms':
        (new RoomController())->index();
        break;

    case 'customers':
        (new CustomerController())->index();
        break;

    case 'memberships':
        (new MembershipController())->index();
        break;

    case 'customer-history':
    case 'history':
        // Both URLs are supported as aliases for customer history.
        (new CustomerController())->history();
        break;

    case 'products':
        (new ProductController())->index();
        break;

    case 'stock':
    case 'stock-management':
        (new InventoryController())->stock();
        break;

    case 'suppliers':
        (new InventoryController())->suppliers();
        break;

    case 'reports':
    case 'report':
        (new ReportController())->index();
        break;

    case 'sales':
        (new ReportController())->sales();
        break;

    case 'inventory-report':
        (new ReportController())->inventory();
        break;

    case 'users':
    case 'users-and-roles':
        // Both URLs are supported as aliases for user and role management.
        (new UserController())->index();
        break;

    case 'settings':
        (new SettingsController())->general();
        break;

    case 'backup':
        (new SettingsController())->backup();
        break;

    default:
        // Unknown routes return a standard HTTP 404 response.
        http_response_code(404);
        echo '404 - Page Not Found';
        break;
    
}