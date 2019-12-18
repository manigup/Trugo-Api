<?php
function Post($tours)
{
    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data)) {
        if ($data->flag === 'create') {
            if ($tours->create($data)) {
                http_response_code(201);
                echo json_encode(array("message" => "New Tour was created with Tour no " . $tours->package_no));
            }
        } else {
            if ($tours->edit($data)) {
                http_response_code(201);
                echo json_encode(array("message" => "Tour " . $data->package_no . " edit successfully"));
            }
        }
    }
}
