<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require_once (APPPATH . 'core/encrypt.php');

//For admin side
class AdminAccount extends REST_Controller
{
    
    function __construct()
    {
        parent::__construct();
        $this->load->model('valigo_account');
        $this->load->helper('auth');
        $this->load->helper('valigo_account');
    }

    public function login_post()
    {
        $username = $this->post('username');
        $password = $this->post('password');

        if ($username !== ADMIN_USERNAME || !verify(ADMIN_PASSWORD, $password)) {
            throw new BadRequestException('username or password is incorrect');
        }

        $response = new Response();
        $response->addMessage('login success');
        $this->response($response);
    }

    public function index_get()
    {
        ensureAuthorizedAdmin($this);

        $response = new Response();
        $users = $this->valigo_account->selectAll();
        $response->addData('users', $users);
        $this->response($response);
    }

    public function user_get($user_uid) {
        ensureAuthorizedAdmin($this);

        $user = $this->valigo_account->gen($user_uid);
        $response = new Response();
        $response->addData('user', $user);
        $this->response($response);
    }
    
    public function user_edit_post($user_uid) {
        ensureAuthorizedAdmin($this);

        $fullname = $this->post('fullname');
        $country_id = $this->post('country_id');

        $updateUser = $this->valigo_account->gen($user_uid);
        $updateUser->fullname = $fullname;
        $updateUser->country_id = $country_id;

        $user = $updateUser->update();
        $response = new Response();
        $response->addData('user', $user);
        $this->response($response);
    }

    public function user_files_get($user_uid) {
        ensureAuthorizedAdmin($this);

        $this->load->model('valigo_file');
        $files = $this->valigo_file->getFileByUserUid($user_uid);
        $response = new Response();
        $response->addData('files', $files);
        $this->response($response);
    }

    public function user_verify_post($user_uid) {
        ensureAuthorizedAdmin($this);

        $updateUser = $this->valigo_account->gen($user_uid);
        $updateUser->admin_verified = 1;

        $user = $updateUser->update();
        $response = new Response();
        $response->addData('user', $user);

        $sent = sendAdminVerificationEmail($this, $user);
        if ($sent) {
            $response->addMessage('email sent');
        } else {
            $response->addMessage('email cannot be sent');
        }

        $this->response($response);
    }

    // public function create_user_post()
    // {
    //     ensureAuthorizedAdmin($this);
    //     $response = new Response();

    //     $email = $this->post('email');
    //     $password = $this->post('password');
    //     $fullname = $this->post('fullname');
    //     $id_card = $this->post('id_card');
    //     $country_id = $this->post('country_id');

    //     $createUser = new Valigo_account();
    //     $createUser->email = $email;
    //     $createUser->password = $password;
    //     $createUser->fullname = $fullname;
    //     $createUser->id_card = $id_card;
    //     $createUser->country_id = $country_id;
    //     $createUser->verified = true;

    //     $user = $createUser->insert();
    //     $response->addData('user', $user);
    //     $this->response($response);
    // }
}
