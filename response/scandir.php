<?php
function Get($tours)
{
    $files = scandir('C:\xampp7\htdocs\admin\content/');
    $images = array();
    $images["results"] = array();
    for ($i = 2; $i < sizeof($files); $i++) {
        array_push($images["results"], $files[$i]);
    }
    http_response_code(200);
    echo json_encode($images);
}
