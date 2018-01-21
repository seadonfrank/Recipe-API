<?php

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
$link = $database->getConnection();
$recipe_table = 'recipe';
$rate_table = 'rate';

// API authenticate
include_once 'authenticate.php';
$auth = new Authenticate();
$auth->authenticate($_GET['token'], $method);

// escape the columns and values from the input object
$columns = preg_replace('/[^a-z0-9_]+/i','',array_keys($input));
$values = array_map(function ($value) use ($link) {
    if ($value===null) return null;
    return mysqli_real_escape_string($link,(string)$value);
},array_values($input));

// build the SET part of the SQL command
$set = '';
for ($i=0;$i<count($columns);$i++) {
    $set.=($i>0?',':'').'`'.$columns[$i].'`=';
    $set.=($values[$i]===null?'NULL':'"'.$values[$i].'"');
}

//input validations to the route
if($params1 == 'search'){
    if($method == 'GET'){
        $sql = "select * from `$recipe_table`".($params2?" WHERE name like '%$params2%'":'');
    }
}else if($params1 == 'rate'){
    switch ($method) {
        case 'GET':
            $sql = "select * from `$recipe_table` WHERE id=$params2";
            $sql_rate = "select * from `$rate_table` WHERE recipe_id=$params2"; break;
        case 'POST':
            $sql = "insert into `$rate_table` set $set"; break;
    }
}else if($params1 == '' || (int)$params1 > 0){
    switch ($method){
        case 'GET':
            $sql = "select * from `$recipe_table`".($params1?" WHERE id=$params1":''); break;
        case 'PUT':
            $sql = "update `$recipe_table` set $set where id=$params1"; break;
        case 'PATCH':
            $sql = "update `$recipe_table` set $set where id=$params1"; break;
        case 'POST':
            $sql = "insert into `$recipe_table` set $set"; break;
        case 'DELETE':
            $sql = "delete `$recipe_table` where id=$params1"; break;
    }
}else{
    http_response_code(404);
    die(json_encode(['error'=>'Invalid Parameters']));
}

// execute SQL statement
$result = mysqli_query($link,$sql);
if($params1 == 'rate' && $sql_rate != ''){
    $result_rate = mysqli_query($link,$sql_rate);
}

// die if SQL statement failed
if (!$result) {
    http_response_code(404);
    die(json_encode(['error'=>mysqli_error()]));
}

// print results, insert id or affected row count
if ($method == 'GET') {
    $results['recipe'] = array();
    for ($i=0;$i<mysqli_num_rows($result);$i++) {
        $results['recipe'][] = mysqli_fetch_object($result);
    }
    if($params1 == 'rate'){
        $results['rate'] = array();
        for ($i=0;$i<mysqli_num_rows($result_rate);$i++) {
            $results['rate'][] = mysqli_fetch_object($result_rate);
        }
    }
    echo json_encode($results);
} elseif ($method == 'POST') {
    echo mysqli_insert_id($link);
} else {
    echo mysqli_affected_rows($link);
}

// close mysql connection
mysqli_close($link);