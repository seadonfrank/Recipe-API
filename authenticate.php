<?php
class Authenticate{

    // specify authentication criteria
    private $token = array('hello@123', 'fresh@123');
    private $method = array('POST', 'PUT', 'PATCH', 'DELETE');

    // authenticate API
    public function authenticate($token, $method) {
        if(!in_array($token, $this->token) && in_array($method, $this->method)) {
            http_response_code(401);
            die(json_encode(['error'=>'Access Denied']));
        }
    }
}
?>