<?php
function Get($tours)
{
    if (isset($_REQUEST["key"])) {
        $tour = $tours->getTour($_REQUEST["key"]);
        while ($row = $tour->fetch_assoc()) {
            echo json_encode($row);
        }
        http_response_code(200);
    } else {
        http_response_code(400);
        die(json_encode(
            array("message" => "Invalid Request")
        ));
    }
}
