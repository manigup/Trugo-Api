<?php
function Post($tours)
{
    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data)) {
        require_once('vendor/autoload.php');

        \Stripe\Stripe::setApiKey('sk_test_Gh30APewOZ8BZNrX7Es3jbmy004ATRFYWQ');

        $customer = \Stripe\Customer::create(array(
            "email" => $data->email,
            "source" => $data->token
        ));

        $budget = $data->discount_budget !== "0" ? $data->discount_budget : $data->actual_budget;

        $charge = \Stripe\Charge::create(array(
            "amount" => $data->members * $budget,
            "currency" => "INR",
            "description" => $data->package_name . "(" . $data->package_no . ")",
            "customer" => $customer->id
        ));

        die(json_encode(
            array("message" => $charge->status)
        ));
    }
}
