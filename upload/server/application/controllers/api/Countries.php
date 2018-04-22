<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
use Ramsey\Uuid\Uuid;

//Common
class Countries extends REST_Controller
{
    
    function __construct()
    {
        parent::__construct();
        $this->load->model('valigo_countries');
    }
    
    public function index_get()
    {
        $response = new Response();
        $countries = $this->valigo_countries->selectAll();
        $response->addData('countries', $countries);
        $this->response($response);
    }
}
