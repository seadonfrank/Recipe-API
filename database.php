<?php
class Database{

    // specify your own database credentials
    private $host = "localhost";
    private $port = "27017";
    private $db_name = "hellofresh";
    private $collection_name = "recipes";

    // connect to a database and return collection
    public function getCollection(){
        $client = new MongoDB\Client("mongodb://".$this->host.":".$this->port);
        $db = $client->selectDatabase($this->db_name);
        $collection = $db->selectCollection($this->collection_name);
        return $collection;
    }
}
?>