<?php
function Get($tours)
{
    if ($tours->delete($_REQUEST["key"])) {
        http_response_code(200);
        echo json_encode(array("message" => "Tour " . $_REQUEST["no"] . " deleted successfully"));
    }
}
