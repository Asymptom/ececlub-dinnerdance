<?php 

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/tables', function(Request $request, Response $response) {
    $not_authorized = checkLogin($request, $response, false);
    if (!is_null($not_authorized)){
    	return $not_authorized;
    }

    $year = date("Y");
    $sql = "SELECT tables.id, users.display_name FROM tables LEFT JOIN users ON tables.id = users.table_num AND users.dinnerdance_year=? order by tables.id";
    $stmt = $this->db->prepare($sql);
    $stmt->bind_param('i', $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $tables = array();
    $currentTableId = null;
    $prevTableId = null;
    $table = null;
    while($row = $result->fetch_assoc()) {
        $prevTableId = $currentTableId;
         $currentTableId = $row['id'];
         if ($currentTableId != $prevTableId){
             $table = array(
                 "id" => $row['id'],
                 "users" => array()
             );
             if (isset($row['display_name'])){
                 array_push($table['users'], $row['display_name']);    
             }
             array_push($tables, $table);
         } else {
             $temp = &$tables[key($tables)];
             array_push($temp['users'], $row['display_name']);
         }
    }
    $stmt->close();

    $json = array();
    $json["status"] = "success";
    $json["message"] = "Tables successfully retrieved";
    $json["tables"] = $tables;
    return $response->withJson($json);
});

$app->get('/table/{tableId}', function(Request $request, Response $response) {
    $not_authorized = checkLogin($request, $response, false);
    if (!is_null($not_authorized)){
        return $not_authorized;
    }

    $tableId = $request->getAttribute('tableId');
    $sql = "select id, size from tables where id=? LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->bind_param('i', $tableId);
    $stmt->execute();
    $result = $stmt->get_result();
    $table = $result->fetch_assoc();

    $json = array();
    if ($row){
        $json["status"] = "success";
        $json["message"] = "Successfully retrieved table";
        $json["table"] = $table;
    } else {
        $json["status"] = "error";
        $json["message"] = "The requested table does not exist";
    }
    
    $stmt->close();
    
    return $response->withJson($json);
});

$app->put('/table/{tableId}', function(Request $request, Response $response) {
    $not_authorized = checkLogin($request, $response, false);
    if (!is_null($not_authorized)){
    	return $not_authorized;
    }

    //TODO: check if session is admin session
    $r = json_decode($request->getBody());

    //TODO: serverside verification of request
    $id = $_SESSION['id'];
    $tableId = $request->getAttribute('tableId');
    
    //begin transaction
    $this->db->autocommit(FALSE);

    $json = array();
    $responseCode = 200;
    //TODO:

    return $response->withJson($json, $responseCode);

});

?>