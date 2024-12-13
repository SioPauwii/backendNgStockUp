<?php
require_once($apipath . 'interfaces/staff.php');

class staff implements StaffInterface{
    protected $pdo, $gm;

    public function __construct(\PDO $pdo, ResponseInterface $gm) {
        $this->pdo = $pdo;
        $this->gm = $gm;
    }
    public function getAllItems($data){

    }
    public function updateQuantity($data){
        
    }
    public function getItemsByCategory($data){
        
    }
    public function getItemsByStatus($data){
        
    }
    public function getItemsByQuantityDesc($data){
        
    }
    public function getItemsByQuantityAsc($data){
        
    }
}