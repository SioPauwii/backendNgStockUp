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
            $tokenRes = $auth->verifyTokenBackend("admin");
                if($tokenRes['is_valid'] == true){
                if($req[1] == 'getallitem'){echo json_encode($admin->getAllItems());return;}
                if($req[1] == 'getitembycategory'){echo json_encode($admin->sortItemsByCategory($data));return;}
                if($req[1] == 'getitembystatus'){echo json_encode($admin->getItemsByStatus($data));return;}
                if($req[1] == 'getitembyquantitydesc'){echo json_encode($admin->getItemsByQuantityDesc($data));return;}
                if($req[1] == 'getitembyquantityasc'){echo json_encode($admin->getItemsByQuantityAsc($data));return;}
                if($req[1] == 'getalluserlogs'){echo json_encode($admin->getAllUserLogs($data));return;}    
                if($req[1] == 'getallarchiveditems'){echo json_encode($admin->getAllArchivedItem());return;}
            }
        }

        if($req[0] == "staff"){
            $tokenRes = $auth->verifyTokenBackend("staff");
            if($tokenRes['is_valid'] == true){
                if($req[1] == 'getallitem'){echo json_encode($staff->getAllItems());return;}
                if($req[1] == 'getitembycategory'){echo json_encode($staff->getItemsByCategory($data));return;}
                if($req[1] == 'getitembystatus'){echo json_encode($staff->getItemsByStatus($data));return;}
                if($req[1] == 'getitembyquantitydesc'){echo json_encode($staff->getItemsByQuantityDesc($data));return;}
                if($req[1] == 'getitembyquantityasc'){echo json_encode($staff->getItemsByQuantityAsc($data));return;}
            }
        }

        $rm->notFound();
        break;

    case 'POST':
        if($req[0] == "login"){
            if(isset($_COOKIE['Authorization']) && $_COOKIE['Authorization'] !== ''){
                echo json_encode(($rm->responsePayload(null, "failed", "Already logged in.", 403)));
                return;
            }
            if($req[1] == "admin"){echo json_encode($auth->adminlogin($data));return;}
            if($req[1] == "staff"){echo json_encode($auth->stafflogin($data));return;}
        }

        if($req[0] == "logout"){echo json_encode($auth->logout());return;}

        if($req[0] == "register"){echo json_encode($auth->register($data));return;}

        if($req[0] == "admin"){
            $tokenRes = $auth->verifyTokenBackend("admin");
            if($tokenRes['is_valid'] == true){
                if($req[1] == "createitem"){echo json_encode($admin->addItem($data));return;}
                if($req[1] == "updateitemquantity"){echo json_encode($admin->updateQuantity($data));return;}
                if($req[1] == "deleteitem"){echo json_encode($admin->deleteItem($data));return;}
                if($req[1] == "retrievearchiveditem"){echo json_encode($admin->retrieveArchivedItem($data));return;}
            }
        }

        if($req[0] == "staff"){
            $tokenRes = $auth->verifyTokenBackend("staff");
            if($tokenRes['is_valid'] == true){
                if($req[1] == "updatequantity"){echo json_encode($staff->updateQuantity($data));return;}
            }
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