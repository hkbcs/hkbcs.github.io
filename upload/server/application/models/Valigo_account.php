<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once (APPPATH . 'core/encrypt.php');
use Ramsey\Uuid\Uuid;

class Valigo_account extends CI_Model
{
    private $table_name = 'users';

    public $id;
    public $email;
    public $password = null;
    public $fullname;
    public $country_id;
    public $ico_project_id = 1;
    // public $id_card;
    public $admin_verified = 0;
    public $verified = 0;
    public $verifyCode;

    /** @InsertIgnore */
    public $created_at;

    /** @InsertIgnore */
    public $updated_at;

    /** @InsertIgnore */
    public $deleted;
    
    function __construct()
    {
        parent::__construct();
        $this->load->helper('valigo_account');
        $this->load->helper('custom_db');
    }

    // ===================
    // Generate
    // ===================
    function mapPropTypes($obj)
    {
        $obj->id = (int)$obj->id;
        $obj->country_id = (int)$obj->country_id;
        $obj->ico_project_id = (int)$obj->ico_project_id;
        $obj->admin_verified = (int)$obj->admin_verified;
        $obj->verified = (int)$obj->verified;
        $obj->deleted = (int)$obj->deleted;
        return $obj;
    }

    function gen($id, $withPassword = false)
    {
        $query = $this->db
                    ->where('id', $id)
                    ->where('deleted', false)
                    ->get($this->table_name);

        $num_rows = $query->num_rows();
        if ($num_rows == 0) {
            throw new NotFoundException('Id('.$id.') not found for table('.$this->table_name.')');
        } elseif ($num_rows > 1) {
            throw new BadRequestException('Multiple id('.$id.') for table('.$this->table_name.')');
        }
        $row = $query->row();
        $row = $this->mapPropTypes($row);
        $data = cast($this, $row);
        if (!$withPassword) {
            unset($data->password);
        }
        return $data;
    }

    function selectIdQuery()
    {
        return $this->db->select('id')->from($this->table_name)->where('deleted', false);
    }

    // ===================
    // General functions
    // ===================
    function insert()
    {
        if ($this->has_email($this->email)) {
            throw new BadRequestException('email already used');
        }

        $this->verifyCode = generateVerifyCode();

        $obj = getAllProperties($this);
        $this->db->insert($this->table_name, $obj);
        $insert_id = $this->db->insert_id();
        return $this->gen($insert_id);
    }

    function update()
    {
        $id = $this->id;
        unset($this->password);
        $obj = getAllProperties($this, 'update');
        $this->db->where('id', $id)->update($this->table_name, $obj);
        return $this->gen($id);
    }

    function selectAll()
    {
        $query = $this->db->from($this->table_name)->get();
        $rows = array();
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                unset($row->password);
                $row = $this->mapPropTypes($row);
                $rows[] = $row;
            }
        }
        return $rows;
    }

    function whereEmail($email)
    {
        return $this->selectIdQuery()->where('email', $email)->get();
    }

    function has_email($email)
    {
        return hasRow($this->whereEmail($email));
    }

    function ensureHasEmail($email, $message)
    {
        return ensureHasRow($this->whereEmail($email), $message);
    }

    // ===================
    // Controller functions
    // ===================
    function selectAccountByEmail($email)
    {
        $userIdQuery = $this->selectIdQuery()
                            ->where('email', $email)
                            ->get();
        ensureHasRow($userIdQuery, 'No account with such email');
        return $this->gen($userIdQuery->row()->id);
    }

    function selectAccountByEmailPassword($email, $password)
    {
        $errorMessage = 'No account with such email and password';

        // Fetch by email
        $userIdQuery = $this->selectIdQuery()
                            ->where('email', $email)
                            ->get();
        ensureHasRow($userIdQuery, $errorMessage);

        // Get user password hash
        $user = $this->gen($userIdQuery->row()->id, true);
        $password_hash = $user->password;
        
        $verified = verify($password, $password_hash);
        if (!$verified) {
            throw new NotFoundException($errorMessage);
        }
        return $user;
    }

    function resetPasswordByEmail($email, $newPassword = null)
    {
        $this->ensureHasEmail($email, 'email not found');

        $userIdQuery = $this->selectIdQuery()->where('email', $email)->get();
        $userId = $userIdQuery->row()->id;

        return $this->resetPasswordById($userId, null);
    }

    function resetPasswordById($userId, $newPassword = null)
    {
        $encryptedPassword;
        if ($newPassword == null) {
            $newPassword = generatePassword();
            $encryptedPassword = encrypt($newPassword);
        } else {
            $encryptedPassword = $newPassword;
        }

        $this->db->from($this->table_name)
            ->where('id', $userId)
            ->set('password', $encryptedPassword)
            ->update();

        return $newPassword;
    }

    function verifyAccount($email, $verifyCode)
    {
        $this->ensureHasEmail($email, 'email not found');

        $userIdQuery = $this->selectIdQuery()->where('email', $email)->get();
        $user = $this->gen($userIdQuery->row()->id);

        if ($user->verified) {
            throw new BadRequestException('user already verified');
        }

        $expectedVerifyCode = $user->verifyCode;
        if ($expectedVerifyCode != $verifyCode) {
            throw new BadRequestException('verify code is incorrect');
        }

        $this->db->from($this->table_name)
            ->where('id', $user->id)
            ->set('verified', true)
            ->update();
    }
}
