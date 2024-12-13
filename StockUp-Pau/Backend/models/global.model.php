<?php
require_once($apipath . '/interfaces/response.php');

class ResponseMethod implements ResponseInterface
{
    public function responsePayload($payload, $remarks, $message, $code){
        $status = array("remarks" => $remarks, "message" => $message);
        http_response_code($code);
        return array("status" => $status, "payload" => $payload, "timestamp" => date('Y-m-d H:i:s'), "prepared_by" => "Olympus Dev. Team");
    }

    public function notFound(){
        echo json_encode([
            "msg"=>"Your endpoint does not exist"
        ]);
        http_response_code(403);
    }

    public function getIDFromTokenBackend(){ // WILL RETRIEVE DATA FROM COOKIE
        if (isset($_COOKIE['Authorization'])) {
            $jwt = explode(' ', $_COOKIE['Authorization']);
            
            if ($jwt[0] === 'Bearer' && isset($jwt[1])) {
                $token = $jwt[1];
                
                $decoded = explode(".", $token);
                
                $payload = json_decode(base64_decode($decoded[1]));
                
                $signature = hash_hmac('sha256', $decoded[0] . "." . $decoded[1], SECRET_KEY, true);
                $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
                
                if ($base64UrlSignature === $decoded[2]) {
                    if (isset($payload->token_data->User_ID)) {
                        return $payload->token_data->User_ID;
                    }
                }
            }
        }
    }

    public function getIDFromToken($data){ // WILL RETRIEVE DATA FROM COOKIE
        $token = $data->Token;
        if ($token) {
            $jwt = explode(' ', $token);
            
            if ($jwt[0] === 'Bearer' && isset($jwt[1])) {
                $token = $jwt[1];
                
                $decoded = explode(".", $token);
                
                $payload = json_decode(base64_decode($decoded[1]));
                
                $signature = hash_hmac('sha256', $decoded[0] . "." . $decoded[1], SECRET_KEY, true);
                $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
                
                if ($base64UrlSignature === $decoded[2]) {
                    if (isset($payload->token_data->User_ID)) {
                        return $payload->token_data->User_ID;
                    }
                }
            }
        }
    }
    public function getUserTypeFromToken($data) {
        // Initialize $token variable in case no token is passed
        $token = $data->Token;        
        // If no token is passed, check for the Authorization cookie
        if (isset($token)) {
            $jwt = explode(' ', $token);
            if ($jwt[0] === 'Bearer' && isset($jwt[1])) {
                $token = $jwt[1];
            }
        }
    
        // If token is still null, return null
        if (!$token) {
            return null;
        }
    
        // Decode the token
        $decoded = explode(".", $token);
        if (count($decoded) !== 3) {
            return null;// Invalid token format
        }
    
        $payload = json_decode(base64_decode($decoded[1]));
        if (!$payload) {
            return null;
        }
    
        // Verify the token signature
        $signature = hash_hmac('sha256', $decoded[0] . "." . $decoded[1], SECRET_KEY, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
        if ($base64UrlSignature !== $decoded[2]) {
            return null; // Signature does not match
        }
    
        // Retrieve the user type from the token payload
        if (isset($payload->token_data->user_type)) {
            return $payload->token_data->user_type;
        }
        
        return null; // User type not found in the token
    }

    public function getUserTypeFromTokenBackendHandler() {
        // Initialize $token variable in case no token is passed
        $token = $_COOKIE['Authorization'];        
        // If no token is passed, check for the Authorization cookie
        if (isset($token)) {
            $jwt = explode(' ', $token);
            if ($jwt[0] === 'Bearer' && isset($jwt[1])) {
                $token = $jwt[1];
            }
        }
    
        // If token is still null, return null
        if (!$token) {
            return null;
        }
    
        // Decode the token
        $decoded = explode(".", $token);
        if (count($decoded) !== 3) {
            return null;// Invalid token format
        }
    
        $payload = json_decode(base64_decode($decoded[1]));
        if (!$payload) {
            return null;
        }
    
        // Verify the token signature
        $signature = hash_hmac('sha256', $decoded[0] . "." . $decoded[1], SECRET_KEY, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
        if ($base64UrlSignature !== $decoded[2]) {
            return null; // Signature does not match
        }
    
        // Retrieve the user type from the token payload
        if (isset($payload->token_data->user_type)) {
            return $payload->token_data->user_type;
        }
        
        return null; // User type not found in the token
    }
    

    function errorhandling($data) {
        $error = error_get_last();
        if ($error !== null) {
            $error_details = [
                E_ERROR => 'Fatal error',
                E_WARNING => 'Warning',
                E_PARSE => 'Parse error',
                E_NOTICE => 'Notice',
                E_CORE_ERROR => 'Core error',
                E_CORE_WARNING => 'Core warning',
                E_COMPILE_ERROR => 'Compile error',
                E_COMPILE_WARNING => 'Compile warning',
                E_USER_ERROR => 'User error',
                E_USER_WARNING => 'User warning',
                E_USER_NOTICE => 'User notice',
                E_STRICT => 'Strict error',
                E_RECOVERABLE_ERROR => 'Recoverable error',
                E_DEPRECATED => 'Deprecated error',
                E_USER_DEPRECATED => 'User deprecated error',
            ];
    
            $error_code = $error['type'];
            $error_message = $error['message'];
            $error_file = $error['file'];
            $error_line = $error['line'];
    
            $response = [
                'status' => 'error',
                'error_code' => $error_code,
                'message' => $error_message,
                'file' => $error_file,
                'line' => $error_line,
                'details' => isset($error_details[$error_code]) ? $error_details[$error_code] : 'Unknown error'
            ];
        } else {
            $response = $data;
        }
    
        return $response;
    }
}