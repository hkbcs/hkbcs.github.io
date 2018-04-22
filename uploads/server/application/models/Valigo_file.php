<?php

use MyCLabs\Enum\Enum;

class FileStatus extends Enum
{
    const Pending = 0;
    const Approved = 1;
    const Rejected = -1;
}

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class Valigo_file extends CI_Model
{
    
    private $table_name = 'files';
    
    public $id;
    public $user_uid;
    public $displayName;
    public $fileName;
    public $size;
    public $mimetype;

    /** @InsertIgnore */
    public $created_at;
    
    /** @InsertIgnore */
    public $expiry_at;
    
    /** @InsertIgnore */
    public $status;

    /** @InsertIgnore */
    public $deleted;

    function __construct()
    {
        parent::__construct();
        $this->load->helper('custom_db');
    }

    // ===================
    // Generate
    // ===================
    function mapPropTypes($obj) {
        $obj->id = (int)$obj->id;
        $obj->user_uid = (int)$obj->user_uid;
        $obj->size = (int)$obj->size;
        $obj->status = (int)$obj->status;
        $obj->deleted = (int)$obj->deleted;
        return $obj;
    }

    function gen($id)
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
        return cast($this, $row);
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
        $obj = getAllProperties($this);
        $this->db->insert($this->table_name, $obj);
        $insert_id = $this->db->insert_id();
        return $this->gen($insert_id);
    }

    function update()
    {
        $id = $this->id;
        $obj = getAllProperties($this, 'update');
        $this->db->where('id', $id)->update($this->table_name, $obj);
        return $this->gen($id);
    }

    function delete($id)
    {
        $this->db->from($this->table_name)
                ->set('deleted', 1)
                ->where('id', $id)
                ->update();
    }

    // ===================
    // Controller functions
    // ===================
    function getFileByUserUid($userUid)
    {
        $files = $this->db->where('user_uid', $userUid)
            ->where('deleted', 0)
            ->get($this->table_name);

        $rows = array();
        if ($files->num_rows() > 0) {
            foreach ($files->result() as $row) {
                $row = $this->mapPropTypes($row);
                $rows[] = $row;
            }
        }

        return $rows;
    }

    // function getFileByFileUid($fileUid)
    // {
    //     $files = $this->db->where('id', $fileUid)
    //         ->where('deleted', 0)
    //         ->get($this->table_name);
    //     if ($files->num_rows() == 0) {
    //         return null;
    //     }
    //     return $files->row();
    // }

    // function createFileRecord($filename, $client_file_name, $mimetype, $userUid)
    // {
    //     $data = array(
    //         'filename' => $filename,
    //         'originalname' => $client_file_name,
    //         'mimetype' => $mimetype,
    //         'user_uid' => $userUid
    //     );

    //     $this->db->insert($this->table_name, $data);

    //     // Get the record
    //     $files = $this->db->where('id', $this->db->insert_id())->get($this->table_name);
    //     return $files->row();
    // }

    // function deleteFileByFileUid($fileUid)
    // {
    //     $this->db->set('deleted', 1)
    //         ->where('id', $fileUid)
    //         ->update($this->table_name);

    //     return true;
    // }
}
