<?php
http_response_code(400);
die(json_encode(
    array("message" => "Invalid Request")
));
