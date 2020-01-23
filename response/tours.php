<?php
function Get($tours)
{
    if (isset($_REQUEST["filters"])) {
        $tour = $tours->getTours($_REQUEST["filters"], isset($_REQUEST["key"]) ? $_REQUEST["key"] : "");
        $tour_arr = array();
        $tour_arr["results"] = array();
        while ($row = $tour->fetch_assoc()) {
            array_push($tour_arr["results"], $row);
        }
        http_response_code(200);
        echo json_encode($tour_arr);
    } else {
        http_response_code(400);
        die(json_encode(
            array("message" => "Invalid Request")
        ));
    }
}
