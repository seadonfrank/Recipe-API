<?php

require 'vendor/autoload.php'; // include Composer's autoloader

// get the HTTP method, path and body of the request
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
$input = json_decode(file_get_contents('php://input'),true);

// retrieve the first and second from the path
$params1 = $request[0];
$params2 = $request[1];

// connect to the database
include_once 'database.php';
$database = new Database();
$collection = $database->getCollection();

// API authenticate
include_once 'authenticate.php';
$auth = new Authenticate();
$auth->authenticate($_GET['token'], $method);

//validate parameters and perform operations
$result=array('data'=>array(), 'success'=>false);
if($params1 == 'search'){
    if($method == 'GET'){
        if($params2){
            $data = $collection->find(array('name' => new \MongoDB\BSON\Regex("$params2","i")));
        } else {
            $data = $collection->find();
        }
        foreach($data as $k=>$v){
            $result['data'][$k]['id'] = $v->id;
            $result['data'][$k]['name'] = $v->name;
            $result['data'][$k]['time'] = $v->time;
            $result['data'][$k]['difficulty'] = $v->difficulty;
            $result['data'][$k]['vegetarian'] = $v->vegetarian;
            $result['data'][$k]['rating'] = $v->rating;
        }
        $result['success'] = true;
    }
}else if($params1 == 'rate'){
    if($method == 'POST'){
        if ((int)$input['rate'] < 1 || (int)$input['rate'] > 5) {
            http_response_code(400);
            die(json_encode(['error'=>"Ratings should be a integer between 1 and 5"]));
        }
        $result['data'] = $collection->findOne(array('id' => $params2));
        $result['data']['rating'][] = $input;
        unset($result['data']['_id']);
        $result['success'] = $collection->updateOne(array('id' => $params2), array('$set' => $result['data']))->isAcknowledged();
    }
}else if($params1 == '' || (int)$params1 > 0){
    switch ($method){
        case 'GET':
            if($params1){
                $result['data'] = $collection->findOne(array('id' => $params1));
                unset($result['data']['_id']);
                $result['success'] = true;
            } else {
                $data = $collection->find();
                foreach($data as $k=>$v){
                    $result['data'][$k]['id'] = $v->id;
                    $result['data'][$k]['name'] = $v->name;
                    $result['data'][$k]['time'] = $v->time;
                    $result['data'][$k]['difficulty'] = $v->difficulty;
                    $result['data'][$k]['vegetarian'] = $v->vegetarian;
                    $result['data'][$k]['rating'] = $v->rating;
                }
                $result['success'] = true;
            }
            break;
        case 'PUT':
            $result['data'] = $collection->findOne(array('id' => $params1));
            foreach($input as $k => $v){
                $result['data'][$k] = $v;
            }
            unset($result['data']['_id']);
            $result['success'] = $collection->updateOne(array('id' => $params1), array('$set' => $result['data']))->isAcknowledged();
            break;
        case 'PATCH':
            $result['data'] = $collection->findOne(array('id' => $params1));
            foreach($input as $k => $v){
                $result['data'][$k] = $v;
            }
            unset($result['data']['_id']);
            $result['success'] = $collection->updateOne(array('id' => $params1), array('$set' => $result['data']))->isAcknowledged();
            break;
        case 'POST':
            $result['data'] = array_merge($input, array('id'=>uniqid()));
            $result['success'] = $collection->insertOne($result['data'])->isAcknowledged();
            break;
        case 'DELETE':
            $result['success'] = $collection->deleteOne(array('id'=>$params1))->isAcknowledged();
            break;
    }
}else{
    http_response_code(404);
    die(json_encode(['error'=>'Invalid Parameters']));
}

// printing the output
if (!$result['success']) {
    http_response_code(500);
    die(json_encode(['error'=>"Database Failure"]));
} else {
    http_response_code(200);
    die(json_encode($result));
}