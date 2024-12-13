<?php
require_once($apipath . 'interfaces/admin.php');

class admin implements AdminInterface {
    protected $pdo, $gm;

    public function __construct(\PDO $pdo, ResponseInterface $gm) {
        $this->pdo = $pdo;
        $this->gm = $gm;
    }
    public function getAllArchivedItem(){
        $sql = "SELECT * FROM archive_items ORDER BY archived_at DESC";
        try{
            $stmt = $this->pdo->prepare($sql);
            if($stmt->execute()){
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                return $this->gm->responsePayload($result, 'success', 'Items retrieved successfully', '200');
            }else{
                return $this->gm->responsePayload(null, 'error', 'Items retrieval failed', '400');
            }
        }catch(PDOException $e){
            return $this->gm->responsePayload(null, 'error', $e, '500');
        }
    }
    public function deleteArchivedItem($data){
        $sql = "DELETE FROM archive_items WHERE item_id = :item_id";
        try{
            $stmt = $this->pdo->prepare($sql);
            if($stmt->execute(['item_id' => $data->item_id])){
                return $this->gm->responsePayload(null, 'success', 'Item deleted successfully', '200');
            }else{
                return $this->gm->responsePayload(null, 'error', 'Item deletion failed', '400');
            }
        }catch(PDOException $e){
            return $this->gm->responsePayload(null, 'error', $e, '500');
        }
    }
    public function retrieveArchivedItem($data){
        $sqlSelect = "SELECT * FROM archive_items WHERE item_id = ?";
        $sqlInsert = "INSERT INTO inventory (item_id, item_name, category, quantity, status) VALUES (?, ?, ?, ?, ?)";
        $sqlDelete = "DELETE FROM archive_items WHERE item_id = :item_id";
        try{
            $stmt = $this->pdo->prepare($sqlSelect);
            if($stmt->execute([$data->item_id])){
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                $stmt = $this->pdo->prepare($sqlInsert);
                if($stmt->execute([$result['item_id'], $result['item_name'], $result['category'], $result['quantity'], $result['status']])){
                    $stmt = $this->pdo->prepare($sqlDelete);
                    if($stmt->execute(['item_id' => $data->item_id])){
                        return $this->gm->responsePayload(null, 'success', 'Item retrieved successfully', '200');
                    }else{
                        return $this->gm->responsePayload(null, 'error', 'Item retrieval failed', '400');
                    }
                }else{
                    return $this->gm->responsePayload(null, 'error', 'Item insertion failed', '400');
                }
            }else{
                return $this->gm->responsePayload(null, 'error', 'Item retrieval failed', '400');
            }
        }catch(PDOException $e){
            return $this->gm->responsePayload(null, 'error', $e, '500');
        }
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
        $sql = "SELECT * FROM inventory WHERE item_id = ?";
        $moveToArchive = "INSERT INTO archive_items (item_id, item_name, category, quantity, status) VALUES (?, ?, ?, ?, ?)";
        try{
            $stmt = $this->pdo->prepare($sql);
            if($stmt->execute([$data->item_id])){
                $item = $stmt->fetchall()[0];
                $stmt = $this->pdo->prepare($moveToArchive);
                if($stmt->execute([$data->item_id, $item['item_name'], $item['category'], $item['quantity'], $item['status']])){
                    $sql = "DELETE FROM inventory WHERE item_id = ?";
                    $stmt = $this->pdo->prepare($sql);
                    if($stmt->execute([$data->item_id])){
                        return $this->gm->responsePayload(null, 'success', 'Item deleted successfully', '200');
                    }else{
                        return $this->gm->responsePayload(null, 'error', 'Item deletion failed', '400');
                    }
                }else{
                    return $this->gm->responsePayload(null, 'error', 'Item deletion failed', '400');
                }
            }else{
                return $this->gm->responsePayload(null, 'error', 'Item deletion failed', '400');
            }
        }catch(PDOException $e){
            return $this->gm->responsePayload(null, 'error', $e, '500');
        }
    }
    public function sortItemsByCategory($data){
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
    public function getAllUserLogs($data){
        $sql = "SELECT * FROM user_logs ORDER BY timestamp DESC";
        try{
            $stmt = $this->pdo->prepare($sql);
            if($stmt->execute()){
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                return $this->gm->responsePayload($result, 'success', 'User logs retrieved successfully', '200');
            }else{
                return $this->gm->responsePayload(null, 'error', 'User logs retrieval failed', '400');
            }
        }catch(PDOException $e){
            return $this->gm->responsePayload(null, 'error', $e, '500');
        }
    }
}