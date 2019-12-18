<?php
class Tours
{
    public $db;
    public $destination;
    public $package_name;
    public $package_type;
    public $starting_city;
    public $ending_city;
    public $budget_per_day;
    public $budget;
    public $departure_date;
    public $tour_duration;
    public $discount;
    public $package_info;
    public $package_image_name;
    public $discount_budget;
    public $package_no;
    public $popular;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function Authenticate($data)
    {
        $auth = explode(",", $data);
        $email = htmlspecialchars(strip_tags($auth[0]));
        $password = htmlspecialchars(strip_tags($auth[1]));
        $sql = "SELECT userid , type , name , email from " . $this->db->userTable . " WHERE email ='$email' and password = '$password'";
        $result = mysqli_query($this->db->conn, $sql);
        if (!$result) {
            http_response_code(503);
            die(json_encode(
                array("message" => "Internal Server Error")
            ));
        } else {
            return $result;
        }
    }

    public function SendEmailToSubscribed($data)
    {
        $subject = $data->subject;
        $message = $data->body;
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: <contact@trugo.co.in>' . "\r\n";
        $selectQuery = "SELECT email FROM " . $this->db->subscribeTable;
        $result = mysqli_query($this->db->conn, $selectQuery);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                mail($row["email"], $subject, $message, $headers);
            }
        }
    }

    public function sanitize($data)
    {
        $this->discount = $data->discount === "" ? 0 : $data->discount;
        $this->budget = $data->actual_budget;
        $this->budget_per_day = $data->budget_per_day;
        $this->departure_date = $data->departure_date;
        $this->tour_duration = $data->duration;
        $this->package_type = $data->package_type;
        $this->destination = $data->destination;
        $this->package_info = htmlspecialchars($data->package_info);

        $this->package_name = strip_tags(mysqli_real_escape_string($this->db->conn, $data->package_name));
        $this->starting_city = strtolower(mysqli_real_escape_string($this->db->conn, strip_tags($data->start_city)));
        $this->ending_city = strtolower(mysqli_real_escape_string($this->db->conn, strip_tags($data->end_city)));

        $this->popular = $data->popular === true ? '1' : '0';
        $this->package_image_name = $data->package_image_name;
        $this->discount_budget = $this->discount !== 0 ? $this->budget - ($this->discount / 100 * $this->budget) : 0;

        foreach ($data->itinerary as $itinerary) {
            $itinerary->title = mysqli_real_escape_string($this->db->conn, str_replace('\'', '', $itinerary->title));
            $itinerary->description = htmlspecialchars($itinerary->description);
        }
        foreach ($data->tourist_attractions as $attractions) {
            $attractions->title = mysqli_real_escape_string($this->db->conn, str_replace('\'', '', $attractions->title));
        }

        $this->itinerary = stripslashes(json_encode(($data->itinerary)));
        $this->attractions = json_encode($data->tourist_attractions);
        $this->carousel = json_encode($data->carousel);
        $this->facilities = json_encode($data->facilities);
    }

    public function create($data)
    {
        $selectQuery = "SELECT * FROM " . $this->db->toursTable . " ORDER BY id DESC LIMIT 1";
        $result = mysqli_query($this->db->conn, $selectQuery);
        if ($result->num_rows  > 0) {
            while ($row = $result->fetch_assoc()) {
                $packageno = intval(substr($row["package_no"], 3)) + 1;
                $this->package_no = "TNR" . $packageno;
            }
        } else {
            $this->package_no = "TNR1";
        }

        $this->sanitize($data);

        $query = "INSERT into " . $this->db->toursTable . "( package_no , user_id , destination , package_name ,
        package_type , budget_per_day , actual_budget , discount_budget , start_city , end_city ,
        departure_date , duration , discount , package_info , popular ,package_image_name ) 
        values( '$this->package_no' , '$data->user' , '$this->destination' , '$this->package_name' , '$this->package_type' , 
            $this->budget_per_day , $this->budget , $this->discount_budget , '$this->starting_city' , 
            '$this->ending_city' , '$this->departure_date' , $this->tour_duration , $this->discount, 
            '$this->package_info' , $this->popular , '$this->package_image_name')";
        $result = mysqli_query($this->db->conn, $query);
        if ($result) {
            $query = "INSERT into " . $this->db->itineraryTable . "( package_no , itinerary , tourist_attractions , 
            carousel , facilities) values( '$this->package_no' , '$this->itinerary' , '$this->attractions' , 
            '$this->carousel' , '$this->facilities')";
            $result = mysqli_query($this->db->conn, $query);
            if ($result) {
                return true;
            } else {
                http_response_code(503);
                die(json_encode(
                    array("message" => "Error : " . mysqli_error($this->db->conn))
                ));
            }
        } else {
            http_response_code(503);
            die(json_encode(
                array("message" => "Error : " . mysqli_error($this->db->conn))
            ));
        }
    }

    public function edit($data)
    {
        $this->sanitize($data);

        $query = "UPDATE " . $this->db->toursTable . " set destination = '$this->destination', package_name = '$this->package_name',
        package_type = '$this->package_type' , budget_per_day = $this->budget_per_day , actual_budget = $this->budget , 
        discount_budget = $this->discount_budget , start_city = '$this->starting_city' , end_city = '$this->ending_city' , 
        departure_date = '$this->departure_date' , duration = $this->tour_duration , discount = $this->discount , 
        package_info = '$this->package_info' , popular = $this->popular ,package_image_name = '$this->package_image_name'
        WHERE package_no = '$data->package_no'";
        $result = mysqli_query($this->db->conn, $query);
        if ($result) {
            $query = "UPDATE " . $this->db->itineraryTable . " set itinerary = '$this->itinerary', carousel = '$this->carousel',
            tourist_attractions = '$this->attractions', facilities = '$this->facilities' WHERE package_no = '$data->package_no'";
            $result = mysqli_query($this->db->conn, $query);
            if ($result) {
                return true;
            } else {
                http_response_code(503);
                die(json_encode(
                    array("message" => "Error : " . mysqli_error($this->db->conn))
                ));
            }
        } else {
            http_response_code(503);
            die(json_encode(
                array("message" => "Error : " . mysqli_error($this->db->conn))
            ));
        }
    }

    public function getReviews()
    {
        $selectQuery = "SELECT * FROM " . $this->db->reviewsTable . " ORDER BY id";
        $result = mysqli_query($this->db->conn, $selectQuery);
        if (!$result) {
            http_response_code(503);
            die(json_encode(
                array("message" => "Internal Server Error")
            ));
        } else {
            return $result;
        }
    }

    private function insertReviews($data)
    {
        $query = "INSERT into " . $this->db->reviewsTable . "( customer_name , customer_designation , customer_rating , customer_review ,
        customer_image ) values( '$data->customer_name' , '$data->customer_designation' , '$data->customer_rating' , 
        '$data->customer_review' , '$data->customer_image')";
        $result = mysqli_query($this->db->conn, $query);
        if (!$result) {
            http_response_code(503);
            die(json_encode(
                array("message" => "Error : " . mysqli_error($this->db->conn))
            ));
        } else {
            return $result;
        }
    }

    public function saveReviews($data)
    {
        if (isset($data)) {
            // delete all the rows from reviews table
            $query = "DELETE FROM  " . $this->db->reviewsTable . "";
            mysqli_query($this->db->conn, $query);
            $result = false;
            for ($i = 0; $i < sizeof($data); $i++) {
                $data[$i]->customer_name = strip_tags(mysqli_real_escape_string($this->db->conn, $data[$i]->customer_name));
                $data[$i]->customer_designation = strip_tags(mysqli_real_escape_string($this->db->conn, $data[$i]->customer_designation));
                $data[$i]->customer_review = strip_tags(mysqli_real_escape_string($this->db->conn, $data[$i]->customer_review));
                $result = $this->insertReviews($data[$i]);
                if ($result) {
                    continue;
                } else {
                    break;
                }
            }
            if ($result) {
                return true;
            } else {
                http_response_code(503);
                die(json_encode(
                    array("message" => "Error : " . mysqli_error($this->db->conn))
                ));
            }
        } else {
            return true;
        }
    }

    public function sanitizeprofile($data)
    {
        $this->name = strip_tags(mysqli_real_escape_string($this->db->conn, $data->name));
        $this->email = strip_tags(mysqli_real_escape_string($this->db->conn, $data->email));
        $this->password = strip_tags(mysqli_real_escape_string($this->db->conn, $data->password));
        $this->type = $data->type;
    }

    public function newUser($data)
    {
        $selectQuery = "SELECT * FROM " . $this->db->userTable . " ORDER BY id DESC LIMIT 1";
        $result = mysqli_query($this->db->conn, $selectQuery);
        if ($result->num_rows  > 0) {
            while ($row = $result->fetch_assoc()) {
                $userno = intval(substr($row["userid"], 1)) + 1;
                $this->userid = "U" . $userno;
            }
        } else {
            $this->userid = "U1";
        }
        $this->sanitizeprofile($data);

        $query = "INSERT into " . $this->db->userTable . "(userid , password , name , email , type) 
        values( '$this->userid' , '$this->password' , '$this->name' , '$this->email' , '$this->type')";
        $result = mysqli_query($this->db->conn, $query);
        if ($result) {
            return true;
        } else {
            http_response_code(503);
            die(json_encode(
                array("message" => "Error : " . mysqli_error($this->db->conn))
            ));
        }
    }

    public function editUser($data)
    {
        $this->sanitizeprofile($data);
        $this->userid = $data->userid;
        if ($this->password !== "") {
            $query = "UPDATE " . $this->db->userTable . " set password = '$this->password', name = '$this->name' WHERE userid = '$this->userid'";
        } else {
            $query = "UPDATE " . $this->db->userTable . " set name = '$this->name' WHERE userid = '$this->userid'";
        }
        $result = mysqli_query($this->db->conn, $query);
        if ($result) {
            return true;
        } else {
            http_response_code(503);
            die(json_encode(
                array("message" => "Error : " . mysqli_error($this->db->conn))
            ));
        }
    }

    public function Subscribe($email)
    {
        $validate = true;
        $selectQuery = "SELECT email FROM " . $this->db->subscribeTable;
        $result = mysqli_query($this->db->conn, $selectQuery);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                if ($row["email"] === $email) {
                    $validate = false;
                }
            }
        }
        if ($validate) {
            $query = "INSERT into " . $this->db->subscribeTable . "(email) values('$email')";
            return mysqli_query($this->db->conn, $query) ? array("status" => "200", "message" => "You are successfully subscribed")
                : array("status" => "406", "message" => "Please try again");
        } else {
            return array("status" => "406", "message" => "Already subscribed with this email");
        }
    }

    public function delete($no)
    {
        $query = "DELETE FROM " . $this->db->itineraryTable . " WHERE package_no = '$no'";
        $result = mysqli_query($this->db->conn, $query);
        if ($result) {
            $query = "DELETE FROM " . $this->db->toursTable . " WHERE package_no = '$no'";
            $result = mysqli_query($this->db->conn, $query);
            if ($result) {
                return true;
            } else {
                http_response_code(503);
                die(json_encode(
                    array("message" => "Error : " . mysqli_error($this->db->conn))
                ));
            }
        } else {
            http_response_code(503);
            die(json_encode(
                array("message" => "Error : " . mysqli_error($this->db->conn))
            ));
        }
    }

    public function getDestinations($filters)
    {
        switch ($filters) {
            case "":
                $selectQuery = "SELECT destination , image_name , status FROM " . $this->db->destinationTable . " WHERE status = 1 ORDER BY id";
                break;
            case "A":
                $selectQuery = "SELECT destination , image_name FROM " . $this->db->destinationTable . " ORDER BY id";
                break;
            case "D":
                $selectQuery = "SELECT destination , image_name , detail , status FROM " . $this->db->destinationTable . " ORDER BY id";
                break;
            default:
                $selectQuery = "SELECT detail FROM " . $this->db->destinationTable . " WHERE destination = " . "'$filters'";
                break;
        }
        $result = mysqli_query($this->db->conn, $selectQuery);
        if (!$result) {
            http_response_code(503);
            die(json_encode(
                array("message" => "Internal Server Error")
            ));
        } else {
            return $result;
        }
    }

    private function insertDestinations($data)
    {
        $query = "INSERT into " . $this->db->destinationTable . "( destination , detail ,image_name , status ) values
        ( '$data->destination' , '$data->detail' , '$data->image_name' , '$data->status')";
        $result = mysqli_query($this->db->conn, $query);
        if (!$result) {
            http_response_code(503);
            die(json_encode(
                array("message" => "Error : " . mysqli_error($this->db->conn))
            ));
        } else {
            return $result;
        }
    }

    public function saveDestinations($data)
    {
        // delete all the rows from destination table
        $query = "DELETE FROM  " . $this->db->destinationTable . "";
        mysqli_query($this->db->conn, $query);
        $result = false;
        for ($i = 0; $i < sizeof($data); $i++) {
            $data[$i]->destination = strip_tags(mysqli_real_escape_string($this->db->conn, $data[$i]->destination));
            $data[$i]->detail = htmlspecialchars($data[$i]->detail);
            $result = $this->insertDestinations($data[$i]);
            if ($result) {
                continue;
            } else {
                break;
            }
        }
        if ($result) {
            return true;
        } else {
            http_response_code(503);
            die(json_encode(
                array("message" => "Error : " . mysqli_error($this->db->conn))
            ));
        }
    }

    public function saveTopDestinations($data)
    {
        $result = false;
        for ($i = 0; $i < sizeof($data); $i++) {
            $status = $data[$i]->status;
            $destination = $data[$i]->destination;
            $query = "UPDATE " . $this->db->destinationTable . " set status = '$status' WHERE destination = '$destination'";
            $result = mysqli_query($this->db->conn, $query);
            if ($result) {
                continue;
            } else {
                break;
            }
        }
        if ($result) {
            return true;
        } else {
            http_response_code(503);
            die(json_encode(
                array("message" => "Error : " . mysqli_error($this->db->conn))
            ));
        }
    }

    public function getEvents()
    {
        $selectQuery = "SELECT event_no , event_name , detail , tours , image_name , attractions
         FROM " . $this->db->eventsTable . " ORDER BY id DESC";
        $result = mysqli_query($this->db->conn, $selectQuery);
        if (!$result) {
            http_response_code(503);
            die(json_encode(
                array("message" => "Internal Server Error")
            ));
        } else {
            return $result;
        }
    }

    private function insertEvents($data, $eventno)
    {
        $query = "INSERT into " . $this->db->eventsTable . "( event_no , event_name , detail , tours , attractions , image_name ) values
        ('$eventno' ,'$data->event_name' , '$data->detail' , '$data->tours' , '$data->attractions' , '$data->image_name')";
        $result = mysqli_query($this->db->conn, $query);
        if (!$result) {
            http_response_code(503);
            die(json_encode(
                array("message" => "Error : " . mysqli_error($this->db->conn))
            ));
        } else {
            return $result;
        }
    }

    public function saveEvents($data)
    {
        // delete all the rows from events table
        $query = "DELETE FROM  " . $this->db->eventsTable . "";
        mysqli_query($this->db->conn, $query);
        $result = false;
        for ($i = 0; $i < sizeof($data); $i++) {
            $data[$i]->event_name = strip_tags(mysqli_real_escape_string($this->db->conn, $data[$i]->event_name));
            $data[$i]->detail = htmlspecialchars($data[$i]->detail);
            $data[$i]->tours = json_encode($data[$i]->tours);
            $data[$i]->attractions = json_encode($data[$i]->attractions);
            $eno = $i + 1;
            $result = $this->insertEvents($data[$i], "EVN" . $eno);
            if ($result) {
                continue;
            } else {
                break;
            }
        }
        if ($result) {
            return true;
        } else {
            http_response_code(503);
            die(json_encode(
                array("message" => "Error : " . mysqli_error($this->db->conn))
            ));
        }
    }

    public function getTours($filters, $key)
    {
        switch ($filters) {
            case 'T':
                $selectQuery = "SELECT package_no , destination , package_name , package_type , start_city , 
                end_city , departure_date , duration , discount ,package_image_name , actual_budget , discount_budget  
                FROM " . $this->db->toursTable . " WHERE package_type != 'honeymoon' AND duration != 2 AND duration != 3 ORDER BY id DESC LIMIT 4";
                break;
            case 'W':
                $selectQuery = "SELECT package_no , destination , package_name , package_type ,  start_city , 
                end_city , departure_date , duration , discount ,package_image_name , actual_budget , discount_budget  
                FROM " . $this->db->toursTable . " WHERE duration = 2 or duration = 3 ORDER BY id DESC LIMIT 5";
                break;
            case 'H':
                $selectQuery = "SELECT package_no , destination , package_name , package_type ,  start_city , 
                end_city , departure_date , duration , discount ,package_image_name , actual_budget , discount_budget  
                FROM " . $this->db->toursTable . " WHERE package_type = 'honeymoon' ORDER BY id DESC LIMIT 5";
                break;
            case 'P':
                $selectQuery = "SELECT id , package_no , destination , package_name , package_type , budget_per_day , actual_budget , 
                discount_budget , start_city , end_city , departure_date , duration , discount ,package_image_name
                FROM " . $this->db->toursTable . " WHERE popular = 1 ORDER BY id";
                break;
            case 'D':
                $selectQuery = "SELECT tours.id , tours.package_no , destination , package_name , package_type , budget_per_day , actual_budget , 
                discount_budget , start_city , end_city , departure_date , duration , discount , package_info , popular , 
                package_image_name , itinerary , carousel , facilities , tourist_attractions FROM " . $this->db->itineraryTable . " 
                JOIN tours ON tours.package_no = itinerary.package_no ORDER BY id DESC";
                break;
            case 'E':
                $tours = json_decode($key);
                $where = "";
                for ($i = 0; $i < sizeof($tours); $i++) {
                    if ($i === 0) {
                        $where .= " WHERE package_no = '" . $tours[$i]->tour .  "'";
                    } else {
                        $where .= " OR package_no = '" . $tours[$i]->tour .  "'";
                    }
                }
                $selectQuery = "SELECT package_no , destination , package_name , package_type , start_city , 
                end_city , departure_date , duration , discount ,package_image_name , actual_budget , discount_budget  
                FROM " . $this->db->toursTable . $where . " ORDER BY id DESC";
                break;
            case 'N':
                $selectQuery = "SELECT package_no FROM " . $this->db->toursTable . " ORDER BY id";
                break;
        }
        $result = mysqli_query($this->db->conn, $selectQuery);
        if (!$result) {
            http_response_code(503);
            die(json_encode(
                array("message" => "Internal Server Error")
            ));
        } else {
            return $result;
        }
    }

    public function getTour($name)
    {
        $selectQuery = "SELECT tours.id , tours.package_no , destination , package_name , package_type , budget_per_day , 
        actual_budget , discount_budget , start_city , end_city , departure_date , duration , discount , package_info ,package_image_name , 
        itinerary , carousel , facilities , tourist_attractions FROM " . $this->db->itineraryTable . " JOIN 
        tours ON tours.package_no = itinerary.package_no WHERE tours.package_name = '$name'";
        $result = mysqli_query($this->db->conn, $selectQuery);
        if (!$result) {
            http_response_code(503);
            die(json_encode(
                array("message" => "Internal Server Error")
            ));
        } else {
            return $result;
        }
    }

    public function filtertours(
        $tag,
        $tour_duration,
        $budget,
        $startcity,
        $endcity,
        $destination,
        $date
    ) {
        $where = "";
        if (strlen($tag) > 0) {
            $alltags = explode(",", $tag);
            $where .= " WHERE package_type = ";
            for ($i = 0; $i < sizeof($alltags); $i++) {
                if ($i > 0) {
                    $where .= "OR package_type = ";
                }
                $where .= "'" . $alltags[$i] .  "'";
            }
        }
        if (strlen($destination) > 0) {
            if (strpos($where, 'WHERE')) {
                $where .= " OR destination = '" . $destination .  "'";
            } else {
                $where .= " WHERE destination = '" . $destination .  "'";
            }
        }
        if (strlen($date) > 0) {
            if (strpos($where, 'WHERE')) {
                $where .= " OR departure_date = '" . $date .  "'";
            } else {
                $where .= " WHERE departure_date = '" . $date .  "'";
            }
        }
        if (strlen($startcity) > 0) {
            if (strpos($where, 'WHERE')) {
                $where .= " OR start_city = '" . $startcity .  "'";
            } else {
                $where .= " WHERE start_city = '" . $startcity .  "'";
            }
        }
        if (strlen($endcity) > 0) {
            if (strpos($where, 'WHERE')) {
                $where .= " OR end_city = '" . $endcity .  "'";
            } else {
                $where .= " WHERE end_city = '" . $endcity .  "'";
            }
        }
        if (strlen($tour_duration) > 0) {
            if (strpos($where, 'WHERE')) {
                $where .= " OR duration BETWEEN 0 AND " . $tour_duration .  "";
            } else {
                $where .= " WHERE duration BETWEEN 0 AND " . $tour_duration .  "";
            }
        }
        if (strlen($budget) > 0) {
            if (strpos($where, 'WHERE')) {
                $where .= " OR budget_per_day BETWEEN 0 AND " . $budget .  "";
            } else {
                $where .= " WHERE budget_per_day BETWEEN 0 AND " . $budget .  "";
            }
        }
        $selectQuery = "SELECT id , package_no , destination , package_name , package_type , budget_per_day , 
        actual_budget , discount_budget , start_city , end_city , departure_date , duration , discount ,
       package_image_name FROM " . $this->db->toursTable . $where . " ORDER BY id";
        $result = mysqli_query($this->db->conn, $selectQuery);
        if (!$result) {
            http_response_code(503);
            die(json_encode(
                array("message" => "Internal Server Error")
            ));
        } else {
            return $result;
        }
    }
}
