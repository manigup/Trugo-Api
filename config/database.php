<?php
class Database
{
    public $toursTable = "tours";
    public $itineraryTable = "itinerary";
    public $destinationTable = "destinations";
    public $reviewsTable = "reviews";
    public $userTable = "users";
    public $subscribeTable = "subscribe";
    public $eventsTable = "events";
    private $host = "localhost";
    private $db_name = "trugo";
    private $username = "root";
    private $password = "";

    public function __construct()
    {
        $this->conn = mysqli_connect($this->host, $this->username, $this->password, $this->db_name);
        if (mysqli_connect_errno()) {
            http_response_code(503);
            die(json_encode(
                array("message" => "Internal Server Error")
            ));
        } else {
            return $this->conn;
        }
    }
}
