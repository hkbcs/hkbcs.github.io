<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class Valigo_countries extends CI_Model
{
    private $table_name = 'countries';
    
    public $id;
    public $displayName;

    function __construct()
    {
        parent::__construct();
    }

    function mapPropTypes($obj) {
        $obj->id = (int)$obj->id;
        $obj->displayName = trim($obj->displayName);
        return $obj;
    }

    function selectAll()
    {
        $query = $this->db->from($this->table_name)->order_by('displayName', 'ASC')->get();
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
}
