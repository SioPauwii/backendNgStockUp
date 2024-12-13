<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Max-Age: 86400');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header('Access-Control-Allow-Origin: '. ($_SERVER['HTTP_ORIGIN'] ?? '*'));
    header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
    exit;
}

date_default_timezone_set("Asia/Manila");
set_time_limit(1000);

$rootPath = $_SERVER["DOCUMENT_ROOT"];
$apipath = $rootPath . "/StockUp-Pau/Backend/";

require_once($apipath . '/configs/Connection.php');
require_once($apipath . '/models/global.model.php');
require_once($apipath . '/models/auth.model.php');
require_once($apipath . '/models/admin.model.php');
require_once($apipath . '/models/staff.model.php');

$db = new Connection();
$pdo = $db->connect();
$rm = new ResponseMethod();
$auth = new Auth($pdo, $rm);
$admin = new Admin($pdo, $rm);
$staff = new Staff($pdo, $rm);

$data = json_decode(file_get_contents("php://input"));

$req = [];
if (isset($_REQUEST['request']))
    $req = explode('/', rtrim($_REQUEST['request'], '/'));
else $req = array("errorcatcher");

try{
    switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if($req[0] == "admin"){
            if($req[1] == 'getallitem'){echo json_encode($admin->getAllItems());return;}
        }
        break;

    case 'POST':
        if($req[0] == "login"){
            if($req[1] == "admin"){echo json_encode($auth->adminlogin($data));return;}
            if($req[1] == "staff"){echo json_encode($auth->stafflogin($data));return;}
        }

        if($req[0] == "logout"){echo json_encode($auth->logout());return;}

        if($req[0] == "register"){echo json_encode($auth->register($data));return;}

        if($req[0] == "admin"){
            if($req[1] == "createitem"){echo json_encode($admin->addItem($data));return;}
            if($req[1] == "updateitemquantity"){echo json_encode($admin->updateQuantity($data));return;}
        }

        $rm->notFound();
        break;

    case 'PUT':
        
        break;

    case 'DELETE':
        
        break;

    default:
        $rm->notFound();
        break;
    }
}catch(exception $e){
    $response = $rm->errorhandling($e);
    echo json_encode($response);   
}