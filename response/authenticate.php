<?php
function Post($tours)
{
    session_start();
    if ($_POST["data"]) {
        $valiadtion_result = $tours->Authenticate($_POST["data"]);
        $num = $valiadtion_result->num_rows;
        if ($num > 0) {
            while ($row = $valiadtion_result->fetch_assoc()) {
                $row['sessionid'] = session_id();
                $data = $row;
            }
            http_response_code(200);
            echo json_encode($data);
        } else {
            http_response_code(503);
            echo json_encode(
                array("message" => "Invalid Email or Password")
            );
        }
    } else {
        session_destroy();
        session_write_close();
    }
}
