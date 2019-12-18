<?php
function Post($tours)
{
    $target_dir = 'C:\xampp7\htdocs\admin\content/';
    foreach ($_FILES['file']['name'] as $key => $val) {
        $file = $_FILES['file']['name'][$key];
        $path = pathinfo($file);
        $ext = $path['extension'];
        if (move_uploaded_file($_FILES['file']['tmp_name'][$key],  $target_dir . $path['filename'] . "." . $ext)) {
            http_response_code(200);
            echo json_encode(
                array("message" => "Images uploaded successfully")
            );
        } else {
            http_response_code(400);
            die(json_encode(
                array("message" => "Error while uploding images")
            ));
        }
    }
}
