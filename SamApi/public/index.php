<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require '../includes/operations.php';
$app = AppFactory::create();
$basePath = str_replace('/' . basename(__FILE__), '', $_SERVER['SCRIPT_NAME']);
$app = $app->setBasePath($basePath);
/*
endpoint: createuser
parameters: name,email,password
method: POST
*/
$name = null;
	$email = null;
	$password = null;
$app->post('/createuser', function(Request $request, Response $response){
if (!EmptyParameters(array('name','email','password'), $response)) {
	$request_data = $request->getParsedBody();

	$name = $request_data['name'];
	$email = $request_data['email']; 
	$password = $request_data['password'];
    
    $hash_password = password_hash($password, PASSWORD_DEFAULT);

	$db = new DbOperations;

	$result = $db->createuser($name,$email,$hash_password); 
	if ($result == USER_CREATED) {

		$message = array();
		$message['error'] = false;
		$message['message'] = 'User created successful';

		echo json_encode($message);
		return $response
		->withHeader('Content-type', 'application/json') 
		->withStatus(201);
	}elseif ($result == USER_FAILURE) {
		$message = array();
		$message['error'] = true;
		$message['message'] = 'User already created';

		echo json_encode($message);
		
		return $response
		->withHeader('Content-type', 'application/json')
		->withStatus(422);
	}elseif ($result == USER_EXISTS) {
			$message = array();
		$message['error'] = true;
		$message['message'] = 'User already created';

		// $response->write(json_encode($message));
		echo json_encode($message);

		return $response
		->withHeader('Content-type', 'application/json')
		->withStatus(422);
	}

}
return $response
		->withHeader('Content-type', 'application/json')
		->withStatus(422);
}); 

$app->post('/userlogin',function(Request $request,Response $response)
{
	if (!EmptyParameters(array('email','password'), $response)) {
	$request_data = $request->getParsedBody();	
	$email = $request_data['email']; 
	$password = $request_data['password'];
	$db = new DbOperations;

	$result = $db->userlogin($email,$password);
	if ($result== USER_AUTHENTICATED) {
		$user = $db->getUserByEmail($email);
		$response_data = array();
		$response_data['error'] = false;
		$response_data['message'] = 'Login successful';
		$response_data['user'] = $user;
		echo json_encode($response_data);
		return $response
		->withHeader('Content-type', 'application/json')
		->withStatus(200);

	}elseif ($result == USER_NOT_FOUND) {
		$response_data = array();
		$response_data['error'] = true;
		$response_data['message'] = 'User not exist';
	
		echo json_encode($response_data);
		return $response
		->withHeader('Content-type', 'application/json')
		->withStatus(200);

	}elseif ($result == USER_PASSWORD_DO_NOT_MATCH){
			$response_data = array();
		$response_data['error'] = true;
		$response_data['message'] = 'Invalid credentials';
	
		echo json_encode($response_data);
		return $response
		->withHeader('Content-type', 'application/json')
		->withStatus(200);  
	}
	}
		return $response
		->withHeader('Content-type', 'application/json')
		->withStatus(422);
});

function EmptyParameters($required_params, $response){
	$error = false;
	$error_params = '';
	$request_params = $_REQUEST;

	foreach ($required_params as $param) {
		if (!isset($request_params[$param]) || strlen($request_params[$param])<=0) {
		$error = true;
		$error_params .= $param . ', ';
		}
	}
	if ($error) {
		$error_detail = array();
		$error_detail['error'] = true;
		$error_detail['message'] = 'Required parameters' . substr($error_params, 0, -2) . 'are missing or empty';
		echo json_encode($error_detail);
	}
	 return $error; 
}
$app->run();