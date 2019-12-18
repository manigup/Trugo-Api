<?php
function Get($tours)
{
    $events = $tours->getEvents();
    $events_arr = array();
    $events_arr["results"] = array();
    while ($row = $events->fetch_assoc()) {
        array_push($events_arr["results"], $row);
    }
    http_response_code(200);
    echo json_encode($events_arr);
}

function Post($tours)
{
    $data = json_decode(file_get_contents("php://input"));
    if ($tours->saveEvents($data)) {
        http_response_code(201);
        echo json_encode(array("message" => "Events saved successfully"));
    }
}
