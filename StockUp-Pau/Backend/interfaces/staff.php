<?php
interface StaffInterface{
    public function getAllItems($data);
    public function updateQuantity($data);
    public function getItemsByCategory($data);
    public function getItemsByStatus($data);
    public function getItemsByQuantityDesc($data);
    public function getItemsByQuantityAsc($data);
} 