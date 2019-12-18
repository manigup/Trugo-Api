<?php
function Get($tours)
{
    $filtertours = $tours->filtertours(
        $_REQUEST["tag"],
        $_REQUEST["duration"],
        $_REQUEST["budget"],
        strtolower($_REQUEST["starting_city"]),
        strtolower($_REQUEST["ending_city"]),
        strtolower($_REQUEST["destination"]),
        $_REQUEST["departure_date"]
    );
    $num = $filtertours->num_rows;
    $tours_arr = array();
    $tours_arr["results"] = array();
    $notice = $num > 1 ? " results found" : " result found";
    array_push($tours_arr, $num . $notice);
    while ($row = $filtertours->fetch_assoc()) {
        array_push($tours_arr["results"], $row);
    }
    http_response_code(200); // set response code - 200 OK
    echo json_encode($tours_arr);
}
