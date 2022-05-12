<?php
require 'vendor/autoload.php';

$config = ['settings' => [
    'addContentLengthHeader' => false,
	'displayErrorDetails' => true,
	'determineRouteBeforeAppMiddleware' => true
]];

use App\config\bd;

$app = new \Slim\App($config);

$loggedInMiddleware = function ($request, $response, $next) {
	
	if (!isset($_SESSION['xusuario']))
		return $response->withRedirect('/login');    
	$response = $next($request, $response);
	
	return $response;
};

$app->group('/api', function ($group)  {	

	
	$group->get('/autentificacion', function ($request, $response, $args)  {	
		$Auth = new \App\models\Auth;
		$usuario  = 'eduardo';
		$password = '123456';
		
		
		if($usuario === 'eduardo' && $password === '123456')
		{
			
			$payload = json_encode($Auth::SignIn([
				'id' => 1,
				'name' => 'Eduardo'
			]));
			
	
		};
		
		$response->getBody()->write($payload);
		return $response
		->withHeader('Content-Type', 'application/json');

	});

	$group->get('/recuperar/{token}', function ($request, $response, $args) {	
		$Auth = new \App\models\Auth;
		$time = time();
		
		$token = $args['token'];
		$payload = json_encode($Auth::GetData($token));
		

		$response->getBody()->write($payload);
		return $response
			->withHeader('content-type', 'application/json')
			->withStatus($code);
		
		 $response->getBody()->write();
	});

	$group->post('/tipo_usuario', function ($request, $response, $args) {	
		$Auth = new \App\models\Auth;
		$bd = new App\config\bd();
		
		$json = $request->getBody();
		
		
		
		$token =  $request->getHeader("Authorization");
		$tokens = explode(" ",$token[0])[1];
		
		if (validaToken($tokens)->success == true){
			try {
				
			
			$_input = json_decode($json, true); 		
			$_nombre = $_input["nombre"];	
			$_tipo_documento = $_input["tpdocumento"];	
			$_numero_documento = $_input["numdoc"];	
			$_correo = $_input["correo"];	
			
			if(validarusuario($_numero_documento)){
				$data = Array(
					"success" => false,
					"data" => [],
					"msg" => "Registro el tipo documento ya esta registrado"
				);
				$mydata = json_encode($data);
				$code = 500;
			}else{

				$param = array($_nombre, $_tipo_documento, $_numero_documento, $_correo);
				$SQL =  "INSERT INTO tipo_usuario(nombre, tipo_documento, numero_documento, correo) ";
				$SQL .= "VALUES (?, ?, ?, ?)";
				$registro = $bd->run($SQL, $param);

				if($registro->rowCount() > 0){
					$data = Array(
						"success" => true,
						"data" => [],
						"msg" => "Registro guardado correctamente"
					);
					$mydata = json_encode($data);
					$code = 200;
				}else{
					$data = Array(
						"success" => true,
						"data" => [],
						"msg" => "No se puedo guardar el registro"
					);
					$mydata = json_encode($data);
					$code = 500;
				}
			}
		
		} catch (Exception $th) {
			$data = Array(
				"success" => true,
				"data" => [],
				"msg" => "No se puedo guardar el registro"
			);
			$mydata = json_encode($data);
			$code = 500;
		}

			
		}else{
			$mydata = json_encode(validaToken($tokens));
			$code = 500;
		
		
		}

		$response->getBody()->write($mydata);
		 return $response
		 	->withHeader('content-type', 'application/json')
		 	->withStatus($code);
		
	});

	$group->post('/tipo_oferta', function ($request, $response, $args) {	
		$Auth = new \App\models\Auth;
		$bd = new App\config\bd();
		
		$json = $request->getBody();
		
		
		
		$token =  $request->getHeader("Authorization");
		$tokens = explode(" ",$token[0])[1];
		
		if (validaToken($tokens)->success == true){
			try {
				
			
			$_input = json_decode($json, true); 		
			$_nombre_oferta = $_input["nombre_oferta"];	
			$_usuario_asociado = $_input["usuario_asociado"];	
			$_estado = $_input["estado"];	
			
			if(validaUsuPostulacion($_usuario_asociado)){
				$data = Array(
					"success" => false,
					"data" => [],
					"msg" => "Usuario ya a sido postulado"
				);
				$mydata = json_encode($data);
				$code = 500;
				
			}else{

				$param = array($_nombre_oferta, $_usuario_asociado, $_estado);
				$SQL =  "INSERT INTO oferta_laboral(nombre_oferta, usuario_asociado, estado) ";
				$SQL .= "VALUES (?, ?, ?)";
				$registro = $bd->run($SQL, $param);

				if($registro->rowCount() > 0){
					$data = Array(
						"success" => true,
						"data" => [],
						"msg" => "Registro guardado correctamente (oferta laboral)"
					);
					$mydata = json_encode($data);
					$code = 200;
				}else{
					$data = Array(
						"success" => true,
						"data" => [],
						"msg" => "No se puedo guardar el registro (oferta laboral)"
					);
					$mydata = json_encode($data);
					$code = 500;
				}
			}
		
		} catch (Exception $e) {
			$data = Array(
				"success" => true,
				"data" => [],
				"msg" => "Error: " . $e->getMessage()
			);
			$mydata = json_encode($data);
			$code = 500;
		}

			
		}else{
			$mydata = json_encode(validaToken($tokens));
			$code = 500;
		
		
		}

		$response->getBody()->write($mydata);
		 return $response
		 	->withHeader('content-type', 'application/json')
		 	->withStatus($code);
		
	});

	$group->post('/candidatos', function ($request, $response, $args) {	
		$Auth = new \App\models\Auth;
		$bd = new App\config\bd();
				
		$token =  $request->getHeader("Authorization");
		$tokens = explode(" ",$token[0])[1];
		
		if (validaToken($tokens)->success == true){
			   
			try {				
				
				$SQL  = "SELECT t.tipo_documento, t.numero_documento, t.nombre, t.correo, l.nombre_oferta  FROM tipo_usuario t ";
				$SQL .= "INNER JOIN oferta_laboral l ON t.numero_documento = l.usuario_asociado ";
				$SQL .= "WHERE l.estado = 1";

				$registro = $bd->run($SQL);
				$dtcliente = $registro->fetchAll();

				if($registro->rowCount() > 0){
					$data = Array(
						"success" => true,
						"data" => $dtcliente,
						"msg" => "Cantidad de registro: "
					);
					$mydata = json_encode($data);
					$code = 200;
				}else{
					$data = Array(
						"success" => true,
						"data" => $dtcliente,
						"msg" => "No se puedo guardar el registro (oferta laboral)"
					);
					$mydata = json_encode($data);
					$code = 500;
				}
					
			} catch (Exception $e) {
				$data = Array(
					"success" => false,
					"data" => $SQL,
					"msg" => "Errors: " //. $e->getMessage()
				);
				$mydata = json_encode($data);
				$code = 500;
			}

			
		}else{
			$mydata = json_encode(validaToken($tokens));
			$code = 500;		
		}

		$response->getBody()->write($mydata);
		 return $response
		 	->withHeader('content-type', 'application/json')
		 	->withStatus($code);
		
	});

	function validarusuario($num_usuario){
		$bd = new App\config\bd();
		$SQL = "SELECT * FROM tipo_usuario WHERE numero_documento= '".$num_usuario."'";
		$registro = $bd->run($SQL);
		$dtcliente = $registro->fetch();

		$existe = false;
		if($registro->rowCount() > 0){
			$existe = true;
		}
		return $existe;
	}
	function validaUsuPostulacion($num_usuario){
		$bd = new App\config\bd();
		$SQL = "SELECT * FROM oferta_laboral WHERE usuario_asociado= '".$num_usuario."'";
		$registro = $bd->run($SQL);
		$dtcliente = $registro->fetch();

		$existe = false;
		if($registro->rowCount() > 0){
			$existe = true;
		}
		return $existe;
	}

	function validaToken($token){
		$Auth = new \App\models\Auth;
		$payload = json_encode($Auth::GetData($token));
		$data = json_decode($payload);
		
		$code = 200;
		return $data;
	}
	$group->get('/checktoken/{token}', function ($request, $response, $args)  {
		$Auth = new \App\models\Auth;
		$time = time();
		
		$token = $args['token'];
		
		$payload = json_encode($Auth::Check($token));
		$code = 200;

		$response->getBody()->write($payload);
		return $response
			->withHeader('content-type', 'application/json')
			->withStatus($code);
		
		 $response->getBody()->write();
	});

});


$app->run();
 
 
?>