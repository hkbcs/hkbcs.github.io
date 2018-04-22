<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function uploadFile() {
    //TODO
}

function uploadPhoto() {
    
    $targetFolderForUploadedFile = '/folder/upload/';
    
    if (!empty($_FILES['file']['name'])) {
        $allowedExts = array("gif", "jpeg", "jpg", "png");
        $temp = explode(".", $_FILES["file"]["name"]);
        $extension = end($temp);
        //echo $_FILES["file"]["type"];
        if (( ($_FILES["file"]["type"] == "image/gif") || ($_FILES["file"]["type"] == "image/jpeg") || ($_FILES["file"]["type"] == "image/jpg") || ($_FILES["file"]["type"] == "image/pjpeg") || ($_FILES["file"]["type"] == "image/x-png") || ($_FILES["file"]["type"] == "image/png") || ($_FILES["file"]["type"] == "image/PNG") || ($_FILES["file"]["type"] == "application/octet-stream")
                )
                //&& ($_FILES["file"]["size"] < 20000)
                && in_array($extension, $allowedExts)) {
            if ($_FILES["file"]["error"] > 0) {
                $status = "Error: " . $_FILES["file"]["error"];
            } else {
                // rename the file name (Developing)
                $new_file = time() . "." . pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);

                if (file_exists($targetFolderForUploadedFile . $new_file)) {
                    $status = $new_file . " already exists.";
                } else {
                    $file_path = $_SERVER['DOCUMENT_ROOT'] . $targetFolderForUploadedFile;
                    $file_path = $file_path . $new_file;

                    if (move_uploaded_file($_FILES["file"]["tmp_name"], $file_path)) {
                        //$sql = "UPDATE `usr_user` SET `user_pic` = '$new_file' WHERE `driver_id` = '$driver_id'";
                        //mysql_query($sql);

                        $status = $new_file;
                    } else {
                        $status = "Upload failed";
                    }
                }
            }
        } else {

            $status = "Invalid file";
        }
    } else {

        $status = "N/A";
    }

    $result = array(
        "status" => "$status"
    );
    $output = json_encode($result);
    echo $output;
    if (isset($debug)) {
        echo "<br><br>";
        print_r(json_decode($output));
    }
}

?>