<?php
function Get($tours)
{
    $reviews = $tours->getReviews();
    $reviews_arr = array();
    $reviews_arr["results"] = array();
    while ($row = $reviews->fetch_assoc()) {
        array_push($reviews_arr["results"], $row);
    }
    http_response_code(200); 
    echo json_encode($reviews_arr);
}

function Post($tours)
{
    $data = json_decode(file_get_contents("php://input"));
    if ($tours->saveReviews($data)) {
        http_response_code(201);
        echo json_encode(array("message" => "Reviews saved successfully"));
    }
}
