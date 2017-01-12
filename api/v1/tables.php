<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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

$app->get('/tables/{tableId}', function(Request $request, Response $response) {
    $not_authorized = checkLogin($request, $response, false);
    if (!is_null($not_authorized)){
        return $not_authorized;
    }

    $tableId = $request->getAttribute('tableId');
    $sql = "select id, size, num_members from tables where id=? LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->bind_param('i', $tableId);
    $stmt->execute();
    $result = $stmt->get_result();
    $table = $result->fetch_assoc();

    $json = array();
    if ($table){
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

$app->put('/tables/{tableId}', function(Request $request, Response $response) {
    $not_authorized = checkLogin($request, $response, false);
    if (!is_null($not_authorized)){
    	return $not_authorized;
    }
    $id = $_SESSION['id'];
    $tableId = $request->getAttribute('tableId');
    
    $json = array();
    //begin transaction
    $this->db->autocommit(FALSE);
    $sql = "SELECT table_num FROM users WHERE id=?";
    $stmt = $this->db->prepare($sql);
    $stmt->bind_param('i', $id);
    if ($stmt->execute()){
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $oldTableNum = $user['table_num'];
        if ($oldTableNum == $tableId){
            //same table
            //ditch this transaction
            $this->db->rollback();
            $stmt->close();

            $json["status"] = "error";
            $json["message"] = "You are already part of this table.";      
            return $response->withJson($json);
        }

        $sql = "UPDATE users INNER JOIN tables ON tables.id=? AND tables.num_members < tables.size SET users.table_num=?, tables.num_members = tables.num_members + 1 WHERE users.id=?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('iii', $tableId, $tableId, $id);
        if ($stmt->execute()){
            if (isset($oldTableNum)) {
                $sql = "UPDATE tables SET num_members = num_members - 1 WHERE id=?";
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param('i', $oldTableNum);
            }

            if (!isset($oldTableNum) || $stmt->execute()){
                //transaction worked
                $this->db->commit();
                $stmt->close();
                
                $json["status"] = "success";
                $json["message"] = "Successfully added you to the table";
                return $response->withJson($json);
            }
        }    
    } 

    //transaction failed
    $this->db->rollback();
    $stmt->close();

    $json["status"] = "error";
    $json["message"] = "We couldn't fit you into that table!";      
    return $response->withJson($json);
});

$app->delete('/tables', function(Request $request, Response $response) {
    $not_authorized = checkLogin($request, $response, false);
    if (!is_null($not_authorized)){
        return $not_authorized;
    }

    $id = $_SESSION['id'];
    
    $json = array();
    //begin transaction
    $this->db->autocommit(FALSE);
    $sql = "SELECT table_num FROM users WHERE id=?";
    $stmt = $this->db->prepare($sql);
    $stmt->bind_param('i', $id);
    if ($stmt->execute()){
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $tableNum = $user['table_num'];

        $sql = "UPDATE users SET table_num = NULL WHERE id=?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        if ($stmt->execute()){
            $sql = "UPDATE tables SET num_members = num_members - 1 WHERE id=?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $tableNum);
            
            if ($stmt->execute()){
                //transaction worked
                $this->db->commit();
                $stmt->close();
                
                $json["status"] = "success";
                $json["message"] = "Successfully removed you from the table";
                return $response->withJson($json);
            }
        }    
    }
    //transaction failed
    $this->db->rollback();
    $stmt->close();

    $json["status"] = "error";
    $json["message"] = "We couldn't remove you from the table at this time. Please try again later.";      
    return $response->withJson($json);
});

?>