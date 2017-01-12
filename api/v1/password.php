<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

function checkIfLoggedIn(Request $request, Response $response){
    if (!isset($_SESSION)) {
        session_start();
    }

    if (isset($_SESSION) && isset($_SESSION['id'])) { //check if they are logged in
        $json['status'] = "error";
        $json['message'] = 'You are already loggedin.';
        $json['redirect'] = 'login';
        return $response->withJson($json, 201);
    }
    return null;
}

$app->get('/password/reset/{resetLink}', function(Request $request, Response $response) {
    $loggedin = checkIfLoggedIn($request, $response);
    if ($loggedin != null){
        return $loggedin;
    }

    $json = array();

    $resetLink = $request->getAttribute('resetLink');

    if ($resetLink == null) {
        $json["status"] = "error";
        $json["message"] = "This link is invalid. Woo";
        $json["redirect"] = "login";
    } else {
        $sql = "select id, ticket_num, reset_time from users where reset_link=? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $resetLink);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if($user){
            $date = new DateTime();
            $reset_expire_time = DateTime::createFromFormat('Y-m-d H:i:s', $user['reset_time']);
            if ($date < $reset_expire_time) {
                $password = password::generate_password();
                $password_hash = password::hash($password);
                $this->logger->addInfo("ticket_num=" . $user['ticket_num'] . ", password=" . $password);
                //TODO: send mail
                $sql = "UPDATE users SET password=? ,reset_link=null, reset_time=null, is_activated=0 where id=? LIMIT 1";
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("si", $password_hash, $user['id']);
                if ($stmt->execute()){    
                    $json["status"] = "success";
                    $json["message"] = "We have reset your password. Please check your email for the details.";
                } else {
                    $json["status"] = "error";
                    $json["message"] = "We could not reset your password at this time";
                    $json["redirect"] = "login";
                }
                $stmt->close();

            } else {
                //destroy the reset_link and reset_time
                $sql = "UPDATE users SET reset_link=null, reset_time=null where id=? LIMIT 1";
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("i", $user['id']);
                $stmt->execute();
                $stmt->close();

                $json["status"] = "error";
                $json["message"] = "This link has expired";
                $json["redirect"] = "login";
            }
        }else{
            $json["status"] = "error";
            $json["message"] = "This link is invalid";
            $json["redirect"] = "login";
        }
    }
    return $response->withJson($json);
});

$app->post('/password/reset', function(Request $request, Response $response) {
    $loggedin = checkIfLoggedIn($request, $response);
    if ($loggedin != null){
        return $loggedin;
    }

    $r = json_decode($request->getBody());

    $ticketNum = $r->user->ticketNum;
    $email = $r->user->email;
    $dinnerdance_year = date("Y");

    $sql = "select id from users where ticket_num=? and email=? and dinnerdance_year=? LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->bind_param("isi", $ticketNum, $email, $dinnerdance_year);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    $json = array();
    if($user['id']){
        $resetLink = password::generate_password(40) . $user['id'];
        $date = new DateTime();
        $date->add(new DateInterval('PT1H'));
        $resetTime = $date->format('Y-m-d H:i:s');

        $this->logger->addInfo("id=" . $user['id'] . ", reset_link=" . $resetTime);

        $sql = "UPDATE users SET reset_link=?, reset_time=? where id=? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssi", $resetLink, $resetTime, $user['id']);
        if ($stmt->execute()){

            //TODO: send mail
            $json["status"] = "success";
            $json["message"] = "We have sent you a password reset request. Please check your email. This request will expire in an hour.";
        } else {
            $json["status"] = "error";
            $json["message"] = "We could not reset your password at this time";
        }
        $stmt->close();
    }else{
        $json["status"] = "error";
        $json["message"] = "We don't have a user registered under those credentials";
    }
    return $response->withJson($json);
});

$app->put('/password/{id}', function(Request $request, Response $response) {
    $not_authorized = checkLogin($request, $response, false);
    if (!is_null($not_authorized)){
        return $not_authorized;
    }
    
    $r = json_decode($request->getBody());

    $currentPass = $r->credentials->currentPass;
    $newPass = $r->credentials->newPass;

    $id = $_SESSION['id'];

    $sql = "select password from users where id=? LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    $json = array();
    if($user){
        if(password::check_password($user['password'],$currentPass)){
            $password_hash = password::hash($newPass);
            $sql = "UPDATE users SET password=?, is_activated=1 WHERE id=?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("si", $password_hash, $id);
            if ($stmt->execute()) {
                $json["status"] = "success";
                $json["message"] = "Successfully updated password";
                $json["redirect"] = "dashboard";
            } else {
                $json["status"] = "error";
                $json["message"] = "Failed to update password"; 
            }   
        $stmt->close();   
        } else {
            $json['status'] = "error";
            $json['message'] = 'Current password is incorrect';
        }
    }else{
        $json["status"] = "error";
        $json["message"] = "We could not verify you at this time";
    }
    return $response->withJson($json);
});

?>
