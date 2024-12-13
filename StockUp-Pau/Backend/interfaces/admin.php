<?php

interface AdminInterface{
    public function retrievedArchivedItem($data);
    public function deleteArchivedItem($data);
    public function getAllArchivedItem($data);
    public function addItem($data);
    public function getAllItems();
    public function updateQuantity($data);
    public function deleteItem($data);
    public function sortItemsByCategory($data);
    public function getItemsByStatus($data);
    public function getItemsByQuantityDesc($data);
    public function getItemsByQuantityAsc($data);
    public function getAllUserLogs($data);
}