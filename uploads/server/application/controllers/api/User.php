<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
use Respect\Validation\Validator as v;

//For client side
class User extends REST_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('valigo_account');
        $this->load->helper('valigo_account');
        $this->load->model('valigo_file');
        $this->load->library('email');
        $this->load->helper('auth');
    }
    
    public function index_get($user_uid, $section = '')
    {
        ensureAuthorized($this, $user_uid);

        $valid = v::numeric()->validate($user_uid);
        if (!$valid) {
            throw new BadRequestException('user_uid is empty');
        }

        switch ($section) {
            case 'files':
                $files = $this->valigo_file->getFileByUserUid($user_uid);
                $this->response($files);
                break;

            default:
                $user = $this->valigo_account->gen($user_uid);
                $this->response($user);
        }
    }
    
    public function login_post()
    {
        $email = $this->post('email');
        $password = $this->post('password');
        
        $user = $this->valigo_account->selectAccountByEmailPassword($email, $password);
        $this->response($user);
    }
    
    public function register_post()
    {
        $response = new Response();

        $email = $this->post('email');
        $password = $this->post('password');
        $fullname = $this->post('fullname');
        // $id_card = $this->post('id_card');
        $country_id = $this->post('country_id');

        $createUser = new Valigo_account();
        $createUser->email = $email;
        $createUser->password = $password;
        $createUser->fullname = $fullname;
        // $createUser->id_card = $id_card;
        $createUser->country_id = $country_id;
        
        $user = $createUser->insert();
        $response->addData('user', $user);

        $sent = sendVerificationEmail($this, $email, $user);
        if ($sent) {
            $response->addMessage('email sent');
        } else {
            $response->addMessage('email cannot be sent');
        }
        $this->response($response);
    }

    public function verify_check_post()
    {
        $response = new Response();

        $email = $this->post('email');
        $user = $this->valigo_account->selectAccountByEmail($email);

        $response->addData('verified', (bool)$user->verified);
        $this->response($response);
    }

    public function verify_post()
    {
        $response = new Response();

        $email = $this->post('email');
        $verifyCode = $this->post('verifyCode');

        $this->valigo_account->verifyAccount($email, $verifyCode);

        $response->addMessage("verify successfully");
        $this->response($response);
    }

    public function resend_verify_post()
    {
        $response = new Response();

        $email = $this->post('email');
        $user = $this->valigo_account->selectAccountByEmail($email);

        $sent = sendVerificationEmail($this, $email, $user);
        if ($sent) {
            $response->addMessage('email sent');
        } else {
            $response->addMessage('email cannot be sent');
        }
        $this->response($response);
    }

    public function reset_password_post()
    {
        $user_uid = $this->post('user_uid');
        ensureAuthorized($this, $user_uid);

        $response = new Response();
        $newPassword = $this->post('new_password');

        $this->valigo_account->resetPasswordById($user_uid, $newPassword);
        $response->addMessage('password is updated');
        $this->response($response);
    }
    
    public function forget_password_post()
    {
        $response = new Response();

        $email = $this->post('email');

        $newPassword = $this->valigo_account->resetPasswordByEmail($email);

        $sent = sendForgetPasswordEmail($this, $email, $newPassword);
        if ($sent) {
            $response->addMessage('email sent');
        } else {
            $response->addMessage('email cannot be sent');
        }
        $this->response($response);
    }
}
