<?php
interface ResponseInterface{
    public function responsePayload($payload, $remarks, $message, $code);
    public function notFound();
    public function getIDFromToken($data);
    public function getIDFromTokenBackend();
    public function getUserTypeFromToken($data);
    public function getUserTypeFromTokenBackendHandler();
    public function errorhandling($data);
}