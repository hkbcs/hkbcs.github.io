<?php
class Response
{
    public $messages = array();
    public $data = array();

    public function __construct() {
    }

    public function addMessage($message) {
        array_push($this->messages, $message);
    }

    public function addData($key, $data) {
        $this->data[$key] = $data;
    }
}