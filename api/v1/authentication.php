<?php 

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

function checkLogin(Request $request, Response $response, $for_admins){
    if (!isset($_SESSION)) {
        session_start();
    }

    $responseCode = 401;
    $id = $request->getAttribute('id');
    if (!isset($_SESSION) || !isset($_SESSION['id'])) { //check if they are logged in
        $json['status'] = "error";
        $json['message'] = 'You must be logged in to access this page';
        $json['redirect'] = 'login';
        return $response->withJson($json, 401);
    } else if ($for_admins && (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin'])) { //check to see if the user is an admin if its an admin page
        $json['status'] = "error";
        $json['message'] = 'You must be an admin to access this page';
        $json['redirect'] = 'login';
        return $response->withJson($json, 403);
    } else if (!$_SESSION['is_admin'] && ($_SESSION['id'] != $id)) { //if not an admin check to see if its their own information
        $json['status'] = "error";
        $json['message'] = 'You are not authorized to view this page';
        $json['redirect'] = 'login';
        return $response->withJson($json, 403);
    } 
    return NULL;
}

$app->get('/session', function(Request $request, Response $response) {
    $session = session::getSession();

    $json = array(
                'id' => $session['id'],
            );
    return $response->withJson($json);
});

$app->post('/login', function(Request $request, Response $response) {
    $r = json_decode($request->getBody());
    //TODO: serverside verification of request
    
    $password = $r->user->password;
    $ticketNum = $r->user->ticketNum;
    //$ticketNum = 0;
    //$password = "n5RK9J2uNxTf";
    $year = date("Y");
    $sql = "select id, password, is_admin, is_activated from users where ticket_num=? and dinnerdance_year=? LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->bind_param('ii', $ticketNum, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $json = array();
    if ($user != NULL) {
        if(password::check_password($user['password'],$password)){
            if (!isset($_SESSION)) {
                session_start();
            }
            $_SESSION['id'] = $user['id'];
            $_SESSION['is_admin'] = $user['is_admin'];

            if(!$user['is_activated']){
                $json['status'] = "success";
                $json['message'] = 'Logged in successfully. Please set a new password';
                $json['redirect'] = 'activate';
            } else {
                $json['status'] = "success";
                $json['message'] = 'Logged in successfully.';
                $json['redirect'] = 'dashboard';
            }
        } else {
            $json['status'] = "error";
            $json['message'] = 'Login failed. Incorrect credentials';
        }
    } else {
        $json['status'] = "error";
        $json['message'] = 'No such user is registered';
    }

    return $response->withJson($json);
});

$app->post('/signUp', function(Request $request, Response $response) {

    //TODO: check if session is admin session
    $r = json_decode($request->getBody());

    //TODO: serverside verification of request
    $ticketNum = $r->user->ticketNum;
    $email = $r->user->email;
    $firstName = $r->user->firstName;
    $lastName = $r->user->lastName;
    $year = date("Y");
    $displayName = trim($firstName) . " " . trim($lastName);

    $sql = "select 1 from users where ticket_num=? AND dinnerdance_year=? LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->bind_param("ii", $ticketNum, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $isUserExists = $result->fetch_assoc();
    $stmt->close();

    $json = array();
    $responseCode = 200;
    if(!$isUserExists){
        $password = password::generate_password();

        //TODO: send mail
        $this->logger->addInfo($ticketNum . ", " . $password);
        $password_hash = password::hash($password);
        $sql = "INSERT INTO users (ticket_num, dinnerdance_year, email, first_name, last_name, display_name, password) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iisssss", $ticketNum, $year, $email, $firstName, $lastName, $displayName, $password_hash);
        if ($stmt->execute()) {
            $json["status"] = "success";
            $json["message"] = "User account created successfully";
        } else {
            $json["status"] = "error";
            $json["message"] = "Failed to create user. Please try again"; 
            $responseCode = 201;
        }   
        $stmt->close();
    }else{
        $json["status"] = "error";
        $json["message"] = "A user already exists with that ticket number.";
        $responseCode = 201;
    }
    return $response->withJson($json, $responseCode);
});

$app->get('/logout', function(Request $request, Response $response) {
    $session = session::destroySession();
    $json = array(
                "status" => "info",
                "message" => "Logged out successfully"
            );
    return $response->withJson($json);
});
?>