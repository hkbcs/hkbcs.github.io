<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
use Ramsey\Uuid\Uuid;

//For client side
class File extends REST_Controller
{
    
    function __construct()
    {
        parent::__construct();
        $this->load->model('valigo_file');
        $this->load->helper('valigo_file');
        $this->load->helper('auth');
    }
    
    public function index_get($file_uid)
    {
        $file = $this->valigo_file->gen($file_uid);
        ensureAuthorized($this, $file->user_uid);

        $this->response($file);
    }

    public function download_get($file_uid)
    {
        $fileRecord = $this->valigo_file->gen($file_uid);
        ensureAuthorized($this, $fileRecord->user_uid);

        $file = UPLOAD_PATH.$fileRecord->fileName;

        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: '.$fileRecord->mimetype);
            header('Content-Disposition: attachment; filename="'.$fileRecord->displayName.'"');
            header('Access-Control-Expose-Headers: Content-Disposition');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        }
    }
    
    public function index_post()
    {
        $response = new Response();
        $user_uid = $this->post('user_uid');
        ensureAuthorized($this, $user_uid);

        $this->load->library('upload');

        $config['upload_path'] = UPLOAD_PATH;
        $config['allowed_types'] = array("pdf", "jpeg", "jpg", "png");
        $config['max_size'] = UPLOAD_FILE_SIZE_MAX;

        // Check user total size
        // $fileSize = $_FILES['file']['size'];
        // $uploadedFileRecords = $this->valigo_file->getFileByUserUid($user_uid);
        // $totalUploadedSize = 0;
        // foreach ($uploadedFileRecords as $uploadedFileRecord) {
        //     $totalUploadedSize += $uploadedFileRecord->size;
        // }
        // if ($totalUploadedSize > UPLOAD_TOTAL_FILE_SIZE_MAX_BYTE) {
        //     throw new BadRequestException('exceeded total file size');
        // }

        // Generate file name
        $filename = $_FILES['file']['name'];
        $uuid4 = Uuid::uuid4();
        $newFilename = time().'-'.$uuid4.'-'.$filename;
        
        $config['file_name'] = $newFilename;
        $this->upload->initialize($config);

        if (! $this->upload->do_upload('file')) {
            $error = array('error' => $this->upload->display_errors());
            $this->response($error);
            return;
        } else {
            $fileRecord = new Valigo_file();
            $fileRecord->fileName = $this->upload->data('file_name');
            $fileRecord->mimetype = $this->upload->data('file_type');
            $fileRecord->size = $this->upload->data('file_size')*1024;
            $fileRecord->displayName = $this->upload->data('client_name');
            $fileRecord->user_uid = $user_uid;

            $storedRecord = $fileRecord->insert();
            $response->addData('file', $storedRecord);
        }
        $this->response($response, 201);
    }
    
    public function index_delete($file_uid)
    {
        $response = new Response();
        $file = $this->valigo_file->gen($file_uid);
        ensureAuthorized($this, $file->user_uid);

        if ((string)$file->status != FileStatus::Pending()) {
            throw new BadRequestException('approved or rejected file cannot be deleted');
        }

        $this->valigo_file->delete($file_uid);

        $response->addMessage('file is deleted');
        $this->response($response, 204);
    }
}
