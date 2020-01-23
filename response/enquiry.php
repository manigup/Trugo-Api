<?php
function Post($tours)
{
    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data)) {
        $subject = "Trugo - Luxury Travels : Thanks for contacting us";
        $message = Usermsg($data);
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: <contact@trugo.co.in>' . "\r\n";
        if (mail(
            $data->email,
            $subject,
            $message,
            $headers
        )) {
            $subject = "Trugo - Luxury Travels : New enquiry";
            $message = Adminmsg($data);
            if (mail(
                "contact@trugo.co.in",
                $subject,
                $message,
                $headers
            )) {
                http_response_code(200);
                echo json_encode(array("message" => "Thanks for contacting us"));
            }
        } else {
            http_response_code(503);
            die(json_encode(array("message" => "Error! Try Again")));
        }
    } else {
        http_response_code(406);
        die(json_encode(
            array("message" => "Invalid Request")
        ));
    }
}

function Usermsg($data)
{
    $msg = "Dear " . $data->person . ",";
    $msg .= "<b>Thanks for contacting us</b>";
    $msg .= "<p>One of our team member get back to you shortly</p>";
    $msg .= "<h4>Regards</h4>";
    $msg .= "<p>Trugo - Luxury Travels</p>";
    return $msg;
}

function Adminmsg($data)
{
    $date = date("Y/m/d") . " , " . date("h:i:sa");
    $msg = "<p><b>Date/Time :</b> $date</p>";
    $msg .= "<p><b>Person name :</b> $data->person</p>";
    $msg .= "<p><b>Email :</b> $data->email</p>";
    $msg .= "<p><b>Destination :</b> $data->destination</p>";
    $msg .= "<p><b>Duration :</b> $data->duration</p>";
    $msg .= "<p><b>Members :</b> $data->members</p>";
    $msg .= "<p><b>Details :</b> $data->details</p>";
    $msg .= "<h4>Regards</h4>";
    $msg .= "<p>Trugo - Luxury Travels</p>";
    return $msg;
}
