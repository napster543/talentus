<?php

namespace App\config;

use App\config\config;

class myaplication{
	
	public function uploader(){
		return config::RUTA_SUBIR;
	}
	public function RutaImagenes(){
		return config::RUTA_IMG_PRODUCTO;
	}
	public function BorrarArchivo($archivo){
		unlink($archivo);
		return $archivo;
	}
	
}
 
?>