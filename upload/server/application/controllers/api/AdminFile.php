<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
use Ramsey\Uuid\Uuid;

//For client side
class AdminFile extends REST_Controller
{
    
    function __construct()
    {
        parent::__construct();
        $this->load->model('valigo_file');
        $this->load->helper('auth');
    }

    public function download_user_get($user_uid)
    {
        ensureAuthorizedAdmin($this);

        // get file records
        $fileRecords = $this->valigo_file->getFileByUserUid($user_uid);
        $valid_files = array();
        foreach ($fileRecords as $fileRecord) {
            //make sure the file exists
            $filePath = UPLOAD_PATH.$fileRecord->fileName;
            if (file_exists($filePath)) {
                $valid_files[] = $fileRecord;
            }
        }

        if (!count($valid_files)) {
            throw new BadRequestException('no files in the file system');
        }

        $uuid4 = Uuid::uuid4();
        $zipFileDisplayName = 'user_'.$user_uid.'.zip';
        $zipFilePath = ARCHIVE_PATH.time().$uuid4.$zipFileDisplayName;

        $zip = new ZipArchive;
        $opened = $zip->open($zipFilePath, ZipArchive::CREATE);
        if (!$opened) {
            throw new RestException('zip cannot be created');
        }
        $i = 0;
        foreach ($valid_files as $fileRecord) {
            $i++;
            $filePath = UPLOAD_PATH.$fileRecord->fileName;
            $filename = $i.'_'.$fileRecord->displayName;
            $success = $zip->addFile($filePath, $filename);
            if (!$success) {
                throw new RestException('add file to zip failed');
            }
        }
        $zip->close();

        if (!file_exists($zipFilePath)) {
            throw new RestException('zip file does not exists');
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.$zipFileDisplayName.'"');
        header('Access-Control-Expose-Headers: Content-Disposition');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($zipFilePath));
        // ob_end_clean();
        readfile($zipFilePath);
        exit;
    }

    public function download_get($fileId)
    {
        ensureAuthorizedAdmin($this);
        $fileRecord = $this->valigo_file->gen($fileId);

        $file = UPLOAD_PATH.$fileRecord->fileName;

        if (!file_exists($file)) {
            throw new BadRequestException('file does not exists in file system');
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.$fileRecord->displayName.'"');
        header('Access-Control-Expose-Headers: Content-Disposition');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }

    public function approve_post($fileId)
    {
        ensureAuthorizedAdmin($this);
        $fileRecord = $this->valigo_file->gen($fileId);
        $fileRecord->status = FileStatus::Approved;
        $fileRecord->update();

        $response = new Response();
        $response->addMessage('file is approved');
        $this->response($response);
    }

    public function reject_post($fileId)
    {
        ensureAuthorizedAdmin($this);
        $fileRecord = $this->valigo_file->gen($fileId);
        $fileRecord->status = FileStatus::Rejected;
        $fileRecord->update();

        $response = new Response();
        $response->addMessage('file is rejected');
        $this->response($response);
    }
}
