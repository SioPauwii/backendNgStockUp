<?php
require_once($apipath . 'interfaces/staff.php');

class staff implements StaffInterface{
    protected $pdo, $gm;

    public function __construct(\PDO $pdo, ResponseInterface $gm) {
        $this->pdo = $pdo;
        $this->gm = $gm;
    }
    public function getAllItems(){
        $sql = 'SELECT * FROM inventory';
        try{
            $stmt = $this->pdo->prepare($sql);
            if($stmt->execute()){
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                return $this->gm->responsePayload($result, 'success', 'Items retrieved successfully', '200');
            }else{
                return $this->gm->responsePayload(null, 'error', 'Items retrieval failed', '400');
            }
        }catch(PDOException $e){
            return $this->gm->responsePayload(null, 'error', 'An error occurred while retrieving items.', '500');
        }
    }    
    public function updateQuantity($data){
        $sql = "UPDATE inventory SET quantity = ?, status = ? WHERE item_id = ?";

        if ($data->quantity >= 0 && $data->quantity <= 20) {
            $status = 'Very Low';
        } elseif ($data->quantity >= 21 && $data->quantity <= 40) {
            $status = 'Low';
        } elseif ($data->quantity >= 41 && $data->quantity <= 60) {
            $status = 'Average';
        } elseif ($data->quantity >= 61 && $data->quantity <= 80) {
            $status = 'High';
        } else {
            $status = 'Very High';
        }
        
        try{
            $stmt = $this->pdo->prepare($sql);
            if($stmt->execute([$data->quantity, $status, $data->item_id])){
                return $this->gm->responsePayload(null, 'success', 'Item quantity updated successfully', '200');
            }else{
                return $this->gm->responsePayload(null, 'error', 'Item quantity update failed', '400');
            }
        }catch(PDOException $e){
            return $this->gm->responsePayload(null, 'error', $e, '500');
        }
    }
    public function getItemsByCategory($data){
        $allowedCategories = ['Writing Supplies', 'Paper Materials', 'Arts & Crafts', 'Organizational Tools', 'Miscellaneous'];
        $sqlIF = "SELECT * FROM inventory WHERE category = ? ORDER BY category";
        $sqlELSE = "SELECT * FROM inventory ORDER BY category";
        try{
            $stmtIF = $this->pdo->prepare($sqlIF);
            $stmtELSE = $this->pdo->prepare($sqlELSE);

            if(in_array($data->category, $allowedCategories)){
                $stmtIF->execute([$data->category]);
                $result = $stmtIF->fetchAll(\PDO::FETCH_ASSOC);
                return $this->gm->responsePayload($result, 'success', 'Items sorted successfully', '200');
            }else{
                $stmtELSE->execute();
                $result = $stmtELSE->fetchAll(\PDO::FETCH_ASSOC);
                return $this->gm->responsePayload($result, 'success', 'Items sorted successfully', '200');
            }
        }catch(PDOException $e){
            return $this->gm->responsePayload(null, 'error', $e, '500');
        }
    }
    public function getItemsByStatus($data){
        $sql = "SELECT * FROM inventory WHERE status = ?";
        try{
            $stmt = $this->pdo->prepare($sql);
            if($stmt->execute([$data->status])){
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                return $this->gm->responsePayload($result, 'success', 'Items sorted successfully', '200');
            }else{
                return $this->gm->responsePayload(null, 'error', 'Items sorting failed', '400');
            }
        }catch(PDOException $e){
            return $this->gm->responsePayload(null, 'error', $e, '500');    
        }
    }
    public function getItemsByQuantityDesc($data){
        $sql = "SELECT * FROM inventory ORDER BY quantity DESC";
        try{
            $stmt = $this->pdo->prepare($sql);
            if($stmt->execute()){
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                return $this->gm->responsePayload($result, 'success', 'Items sorted successfully', '200');
            }else{
                return $this->gm->responsePayload(null, 'error', 'Items sorting failed', '400');
            }
        }catch(PDOException $e){
            return $this->gm->responsePayload(null, 'error', $e, '500');
        }
    }
    public function getItemsByQuantityAsc($data){
        $sql = "SELECT * FROM inventory ORDER BY quantity ASC";
        try{
            $stmt = $this->pdo->prepare($sql);
            if($stmt->execute()){
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                return $this->gm->responsePayload($result, 'success', 'Items sorted successfully', '200');
            }else{
                return $this->gm->responsePayload(null, 'error', 'Items sorting failed', '400');
            }
        }catch(PDOException $e){
            return $this->gm->responsePayload(null, 'error', $e, '500');
        }
    }

}