<?php
require_once($apipath . 'interfaces/admin.php');

class admin implements AdminInterface {
    protected $pdo, $gm;

    public function __construct(\PDO $pdo, ResponseInterface $gm) {
        $this->pdo = $pdo;
        $this->gm = $gm;
    }
    public function retrievedArchivedItem($data){
        
    }
    public function deleteArchivedItem($data){
        
    }
    public function getAllArchivedItem($data){
        
    }
    public function addItem($data){
        $sql = "INSERT INTO inventory (item_name, category, $data->quantity) VALUES (?, ?, ?)";
        try{
            $stmt = $this->pdo->prepare($sql);
            if($stmt->execute([$data->item_name, $data->category, $data->$data->quantity])){
                return $this->gm->responsePayload(null, 'success', 'Item added successfully', '200');
            }else{
                return $this->gm->responsePayload(null, 'error', 'Item addition failed', '400');
            }
        }catch(PDOException $e){
            return $this->gm->responsePayload(null, 'error', 'An error occurred while adding item.', '500');
        }
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
    public function deleteItem($data){
        
    }
    public function sortItemsByCategory($data){
        
    }
    public function getItemsByStatus($data){
        
    }
    public function getItemsByQuantityDesc($data){
        
    }
    public function getItemsByQuantityAsc($data){
        
    }
    public function getAllUserLogs($data){
        
    }
}