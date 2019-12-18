<?php
function Get($tours)
{
    $destination = isset($_REQUEST["filters"]) ? $_REQUEST["filters"] : "";
    $destinations = $tours->getDestinations($destination);
    $destination_arr = array();
    $destination_arr["results"] = array();
    while ($row = $destinations->fetch_assoc()) {
        array_push($destination_arr["results"], $row);
    }
    http_response_code(200);
    echo json_encode($destination_arr);
}

function Post($tours)
{
    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data)) {
        if ($_REQUEST["key"] === 'Top') {
            if ($tours->saveTopDestinations($data)) {
                http_response_code(201);
                echo json_encode(array("message" => "Destinations saved successfully"));
            }
        } else {
            if ($tours->saveDestinations($data)) {
                http_response_code(201);
                echo json_encode(array("message" => "Destinations saved successfully"));
            }
        }
    }
}
