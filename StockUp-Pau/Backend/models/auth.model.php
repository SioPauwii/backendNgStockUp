<?php
require_once($apipath . 'interfaces/auth.php');

class Auth implements AuthInterface {
    protected $pdo, $gm;

    public function __construct(\PDO $pdo, ResponseInterface $gm) {
        $this->pdo = $pdo;
        $this->gm = $gm;
    }
    public function adminlogin($data){
        $sql = "SELECT * FROM users WHERE username = ?";
        return $this->login($sql, $data, "admin");
    }
    public function stafflogin($data){
        $sql = "SELECT * FROM users WHERE username = ?";
        return $this->login($sql, $data, "staff");
    }
    public function login($sql, $data, $role){
        $startSession = 'INSERT INTO user_sessions (session_id, user_id, time_in, time_out, is_logged_in) VALUES (default, ?, ?, ?, ?)';
        $getID = 'SELECT user_id FROM users WHERE username = ?';
        $checkLogged = "SELECT * FROM user_sessions WHERE user_id = ? AND is_logged_in = 1";
        try{
            $getID = $this->pdo->prepare($getID);
            $getID->execute([$data->username]);
            $user_id = $getID->fetchall()[0]['user_id'];

            $checkLogged = $this->pdo->prepare($checkLogged);
            $checkLogged->execute([$user_id]);
            
            if($checkLogged->fetch()){
                return $this->gm->responsePayload(null, 'error', 'User already logged in', '401');
            }

            $stmt = $this->pdo->prepare($sql);

            if(isset($data->username)){
                $stmt->execute([$data->username]);

                if($stmt->rowCount() > 0){
                    $user = $stmt->fetchall()[0];
                    $stmt->closeCursor();
                    if(password_verify($data->password, $user['password'])){
                        $getID = $this->pdo->prepare($getID);
                        $getID->execute([$data->username]);
                        $user_id = $getID->fetchall()[0]['user_id'];
                        $session = $this->pdo->prepare($startSession);
                        $session->execute([$user_id, date('Y-m-d H:i:s'), null, 1]);
                        $token = $this->tokenGen(['user_id' => $user_id, 'role' => $role, 'username' => $user['username'], 'full_name' => $user['full_name']]);
                        setcookie("Authorization", 'Bearer ' . $token['token'],["expires" => time() + (86400 * 7),"path" => "/", "domain" => 'localhost', "secure" => true, "httponly" => false, "samesite" => "none"]);
                        return $this->gm->responsePayload($token, 'success', 'Login successful', '200');
                    }else{
                        return $this->gm->responsePayload(null, 'error', 'Invalid username or password', '401');
                    }
                }else{
                    return $this->gm->responsePayload(null, 'error', 'Invalid username or password', "401"); 
                }
            }else{
                return $this->gm->responsePayload(null, 'error', 'Invalid username or password', '401');
            }
        }catch(PDOException $e){
            echo $e->getMessage();
        }
    }
    
    public function logout(){
        $token = $_COOKIE['Authorization'];

        if (!$token) {
            return $this->gm->responsePayload(null, "failed", "No token found for logout.", 400);
        }

        if(setcookie('Authorization', '', time() - 3600, '/', '', true, true)){
            return $this->gm->responsePayload(null, "success", "Logged out successfully.", 200);
        }
    }
    public function register($data){
        $sql = "INSERT INTO users (full_name, username, password, created_at, role) VALUES (?, ?, ?, ?, ?)";
        $checkDupli = 'SELECT * FROM users WHERE username = ?';
        $hashedPassword = password_hash($data->password, PASSWORD_BCRYPT);
        try{
            $stmt = $this->pdo->prepare($sql);
            $checkDupli = $this->pdo->prepare($checkDupli);
            $checkDupli->execute([$data->username]);
            if($checkDupli->rowCount() > 0){
                return $this->gm->responsePayload(null, 'error', 'Username already exists', '400');
            }

            if($stmt->execute([$data->full_name, $data->username, $hashedPassword, date('Y-m-d H:i:s'), 'staff'])){
                return $this->gm->responsePayload(null, 'success', 'Registration successful', '200');
            }else{
                return $this->gm->responsePayload(null, 'error', 'Registration failed', '400');
            }                
        }catch(PDOException $e){
            return $this->gm->responsePayload(null, 'error', 'An error occurred while registering.', '500');
        }
    }
    public function tokenGen($tokenData = null)
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode(['token_data' => $tokenData, 'exp' => date("Y-m-d", strtotime('+7 days')), 'jti' => bin2hex(random_bytes(16))]);
        
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, SECRET_KEY,true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
        
        return array("token" => $jwt);
    }

    public function tokenPayload($payload, $is_valid = false){
        return array(
            "payload"=>$payload,
            "is_valid"=>$is_valid
        );
    }

    public function verifyToken($token, $requiredUserType = null) {
        $authHeader = $token->Token;


        if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
            return $this->tokenPayload('1', false);
        }

        $token = substr($authHeader, 7); // Strip "Bearer " prefix

        $decoded = explode(".", $token);
        if (count($decoded) !== 3) {
            // error_log('Token parts: ' . print_r($decoded, true));
            return $this->tokenPayload('3', false);
        }

        $payload = json_decode(base64_decode($decoded[1]));
        if (!$payload) {
            return $this->tokenPayload('4', true);
        }

        if (isset($payload->exp) && time() > $payload->exp) {
            return $this->tokenPayload('5', false);
        }

        $signature = hash_hmac('sha256', $decoded[0] . "." . $decoded[1], SECRET_KEY, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        if ($base64UrlSignature !== $decoded[2]) {
            return $this->tokenPayload('6', false);
        }

        if ($requiredUserType && isset($payload->token_data->user_type) && $payload->token_data->user_type !== $requiredUserType) {
            return $this->tokenPayload('7', false);
        }

        if (!isset($payload->token_data->User_ID)) {
            return $this->tokenPayload('8', false);
        }

        return $this->tokenPayload($payload, true);
    }

    public function verifyTokenBackend( $requiredUserType = null) {
        $authHeader = rawurldecode($_COOKIE['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? null);


        if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
            return $this->tokenPayload(null, false);
        }

        $token = substr($authHeader, 7); 

        $decoded = explode(".", $token);
        if (count($decoded) !== 3) {
            return $this->tokenPayload(null, false);
        }

        $payload = json_decode(base64_decode($decoded[1]));
        if (!$payload) {
            return $this->tokenPayload(null, true);
        }

        if (isset($payload->exp) && time() > $payload->exp) {
            return $this->tokenPayload(null, false);
        }

        $signature = hash_hmac('sha256', $decoded[0] . "." . $decoded[1], SECRET_KEY, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        if ($base64UrlSignature !== $decoded[2]) {
            return $this->tokenPayload(null, false);
        }

        if ($requiredUserType && isset($payload->token_data->user_type) && $payload->token_data->user_type !== $requiredUserType) {
            return $this->tokenPayload(null, false);
        }

        if ($requiredUserType && isset($payload->token_data->user_type) && $payload->token_data->user_type === $requiredUserType) {
            return $this->tokenPayload($payload, true);
        }

        if (!isset($payload->token_data->User_ID)) {
            return $this->tokenPayload(null, false);
        }

        return $this->tokenPayload($payload, true);
    }
}