<?php
interface AuthInterface{
    public function login($sql, $data, $role);
    public function logout();
    public function register($data);
    public function adminlogin($data);
    public function stafflogin($data);
}