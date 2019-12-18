<?php
function Post($tours)
{
    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data)) {
        if ($data->flag === 'X') {
            if ($tours->newUser($data)) {
                http_response_code(201);
                echo json_encode(array("message" => "New User was created successfully"));
            }
        } else {
            if ($tours->editUser($data)) {
                http_response_code(201);
                echo json_encode(array("message" => "Your profile was edited successfully"));
            }
        }
    }
}
