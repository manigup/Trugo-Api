<?php
function Post($tours)
{
    $data = json_decode(file_get_contents("php://input"));
    if (isset($_REQUEST["key"])) {
        $tours->SendEmailToSubscribed($data);
        http_response_code(201);
        echo json_encode(
            array("message" => "Email send successfully")
        );
    } else {
        $subscribe = $tours->Subscribe($data->email);
        http_response_code($subscribe["status"]);
        echo json_encode(
            array("message" => $subscribe["message"])
        );
    }
}
