<?php
class Database{

    // specify your own database credentials
    private $host = "localhost";
    private $db_name = "hellofresh";
    private $username = "root";
    private $password = "scorpio21189";

    // get the database connection
    public function getConnection(){
        $db = mysqli_connect($this->host, $this->username, $this->password, $this->db_name);
        if (mysqli_connect_errno()) {
            http_response_code(500);
            die(json_encode(['error'=>mysqli_connect_error()]));
        }
        mysqli_set_charset($db,'utf8');

        return $db;
    }
}
?>