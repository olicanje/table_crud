<?php
namespace controladores;

class tabla extends \core\Controlador {

	
	
	 /**
	 * Presenta una <table> con las filas de la tabla con igual nombre que la clase.
	 * @param array $datos
	 */
	public function index(array $datos=array()) {
		
		$clausulas['order_by'] = 'nombre';
		//$datos["filas"] = \modelos\consolas::select($clausulas, "consolas"); // Recupera todas las filas ordenadas
		$datos["filas"] = \modelos\Modelo_SQL::table("consolas")->select($clausulas); // Recupera todas las filas ordenadas

//            var_dump($datos);
//            var_dump($datos["filas"]);
//            Mostramos los datos a modificar en formato europeo. Convertimos el formato de MySQL a europeo
//            foreach ($datos["filas"] as $key => $fila) {
//                var_dump($fila['masa_atomica']);
//                $datos['filas'][$key]['masa_atomica']=  \core\Conversiones::decimal_punto_a_coma($fila['masa_atomica']);
//                var_dump($datos['filas'][$key]['masa_atomica']);
//            }
            self::convertir_formato_mysql_a_ususario($datos['filas']);
        
            //var_dump($datos);                
                
		$datos['view_content'] = \core\Vista::generar(__FUNCTION__, $datos);
		$http_body = \core\Vista_Plantilla::generar('plantilla_principal', $datos);
		\core\HTTP_Respuesta::enviar($http_body);
		
	}
	
	/**
        * Presenta un formulario para insertar nuevas filas a la tabla consolas
        * @param array $datos
        */
	public function form_insertar(array $datos=array()) {
		
		$datos["form_name"] = __FUNCTION__;
		$datos['view_content'] = \core\Vista::generar(__FUNCTION__, $datos);
		$http_body = \core\Vista_Plantilla::generar('plantilla_principal', $datos);
		\core\HTTP_Respuesta::enviar($http_body);
		
	}
         /**
         * Función que valida los datos insertados por el usuario. Si valida corectamente mostrará la tabla con 
         * la nueva inserción, sino mostrará los errores que se tienen.
         * @param array $datos
         */
	public function validar_form_insertar(array $datos=array()) {
		
		$validaciones = array(
			 "nombre" =>"errores_requerido && errores_texto && errores_unicidad_insertar:nombre/consolas/nombre"
                        , "fecha_lanzamiento" => "errores_fecha_hora && errores_requerido"
                        , "precio" => "errores_requerido && errores_texto && errores_precio"
                        , "unidades_stock" => "errores_requerido && errores_texto && errores_numero_entero_positivo"
			, "descripcion" => "errores_texto"
		);
                
		if ( ! $validacion = ! \core\Validaciones::errores_validacion_request($validaciones, $datos)){
                    $datos["errores"]["errores_validacion"]="Corrige los errores.";
                }else{
                    //Convertimos a formato MySQL
                    self::convertir_a_formato_mysql($datos['values']);                    
                    //$datos['values']['fecha_salida'] = \core\Conversiones::fecha_es_a_mysql($datos['values']['fecha_salida']);
                    
                    if ( ! $validacion = \modelos\Modelo_SQL::insert($datos["values"], 'consolas')) // Devuelve true o false
                        $datos["errores"]["errores_validacion"]="No se han podido grabar los datos en la bd.";
		}
		if ( ! $validacion) //Devolvemos el formulario para que lo intente corregir de nuevo
			\core\Distribuidor::cargar_controlador('consolas', 'form_insertar', $datos);
		else
		{
			// Se ha grabado la modificación. Devolvemos el control al la situacion anterior a la petición del form_modificar
			//$datos = array("alerta" => "Se han grabado correctamente los detalles");
			// Definir el controlador que responderá después de la inserción
			//\core\Distribuidor::cargar_controlador('consolas', 'index', $datos);
			$_SESSION["alerta"] = "Se han grabado correctamente los detalles";
			//header("Location: ".\core\URL::generar("consolas/index"));
			\core\HTTP_Respuesta::set_header_line("location", \core\URL::generar("tabla"));
			\core\HTTP_Respuesta::enviar();
		}
	}

	
	
	public function form_modificar(array $datos = array()) {
		
		$datos["form_name"] = __FUNCTION__;
                
                If (\core\HTTP_Requerimiento::method()== 'GET'){
                    $datos['mensaje']="No me añadas, ningun id a la URL";
                    \core\Distribuidor::cargar_controlador('mensajes', 'mensaje', $datos);
                }
		
		if ( ! isset($datos["errores"])) { // Si no es un reenvío desde una validación fallida
			$validaciones=array(
				"id" => "errores_requerido && errores_numero_entero_positivo && errores_referencia:id/consolas/id"
			);
			if ( ! $validacion = ! \core\Validaciones::errores_validacion_request($validaciones, $datos)) {
				$datos['mensaje'] = 'Datos erróneos para identificar el artículo a modificar';
				\core\Distribuidor::cargar_controlador('mensajes', 'mensaje', $datos);
				return;
			}
			else {
				$clausulas['where'] = " id = {$datos['values']['id']} ";
				if ( ! $filas = \modelos\Datos_SQL::select( $clausulas, 'consolas')) {
					$datos['mensaje'] = 'Error al recuperar la fila de la base de datos';
					\core\Distribuidor::cargar_controlador('mensajes', 'mensaje', $datos);
					return;
				}
				else {
					$datos['values'] = $filas[0];
                                        
                                        // Mostramos los datos que necesitan conversiones
                                        $datos['values']['fecha_lanzamiento'] = \core\Conversiones::fecha_hora_mysql_a_es($datos['values']['fecha_lanzamiento']);
                                        $datos['values']['precio'] = \core\Conversiones::decimal_punto_a_coma($datos['values']['precio']);

                                        $clausulas = array('order_by' => "nombre");
                                        $datos['categorias'] = \modelos\Datos_SQL::table("consolas")->select( $clausulas);
					
				}
			}
		}
		
                // Envía el nombre del formulario
                $datos['form_name'] = __FUNCTION__;
        
		$datos['view_content'] = \core\Vista::generar(__FUNCTION__, $datos);
		$http_body = \core\Vista_Plantilla::generar('plantilla_principal', $datos);
		\core\HTTP_Respuesta::enviar($http_body);
	}

	
	
	
	
	public function validar_form_modificar(array $datos=array()) {	
		
		$validaciones = array(
                        "id" => "errores_requerido && errores_numero_entero_positivo && errores_referencia:id/consolas/id"
			,"nombre" => "errores_requerido && errores_texto && errores_unicidad_modificar:id,nombre/consolas/id,nombre"
                        , "fecha_lanzamiento" => "errores_fecha_hora && errores_requerido"
                        , "precio" => "errores_requerido && errores_texto && errores_decimal"
                        , "unidades_stock" => "errores_requerido && errores_texto && errores_numero_entero_positivo"
			, "descripcion" => "errores_texto"
		);
                
		if ( ! $validacion = ! \core\Validaciones::errores_validacion_request($validaciones, $datos)) {
			
            $datos["errores"]["errores_validacion"] = "Corrige los errores.";
		}
                else {
                    //Convertimos a formato MySQL
                    self::convertir_a_formato_mysql($datos['values']); 
			
			if ( ! $validacion = \modelos\Datos_SQL::update($datos["values"], 'consolas')) // Devuelve true o false
					
				$datos["errores"]["errores_validacion"]="No se han podido grabar los datos en la bd.";
				
		}
		if ( ! $validacion) //Devolvemos el formulario para que lo intente corregir de nuevo
			\core\Distribuidor::cargar_controlador('consolas', 'form_modificar', $datos);
		else {
			$datos = array("alerta" => "Se han modificado correctamente.");
			// Definir el controlador que responderá después de la inserción
			//\core\Distribuidor::cargar_controlador('consolas', 'index', $datos);
                        \core\HTTP_Respuesta::set_header_line("location", \core\URL::generar("tabla"));
			\core\HTTP_Respuesta::enviar();
		}
		
	}

	
	
	public function form_borrar(array $datos=array()) {
		
		$datos["form_name"] = __FUNCTION__;
		$validaciones=array(
			"id" => "errores_requerido && errores_numero_entero_positivo && errores_referencia:id/consolas/id"
		);
		if ( ! $validacion = ! \core\Validaciones::errores_validacion_request($validaciones, $datos)) {
			$datos['mensaje'] = 'Datos erróneos para identificar el artículo a borrar';
			$datos['url_continuar'] = \core\URL::http('?menu=consolas');
			\core\Distribuidor::cargar_controlador('mensajes', 'mensaje', $datos);
			return;
		}
		else {
			$clausulas['where'] = " id = {$datos['values']['id']} ";
			if ( ! $filas = \modelos\Datos_SQL::select( $clausulas, 'consolas')) {
				$datos['mensaje'] = 'Error al recuperar la fila de la base de datos';
				\core\Distribuidor::cargar_controlador('mensajes', 'mensaje', $datos);
				return;
			}
			else {
				$datos['values'] = $filas[0];
                                
                                // Mostramos los datos que necesitan conversiones
                                $datos['values']['fecha_lanzamiento'] = \core\Conversiones::fecha_hora_mysql_a_es($datos['values']['fecha_lanzamiento']);
                                $datos['values']['precio'] = \core\Conversiones::decimal_punto_a_coma($datos['values']['precio']);

			}
		}
		
                $datos['form_name'] = __FUNCTION__;
                $datos['view_content'] = \core\Vista::generar(__FUNCTION__, $datos);
                $http_body = \core\Vista_Plantilla::generar('plantilla_principal', $datos);
                \core\HTTP_Respuesta::enviar($http_body);
	}

	
	
	
	
	
	public function validar_form_borrar(array $datos=array()) {	
		
		$validaciones=array(
			 "id" => "errores_requerido && errores_numero_entero_positivo && errores_referencia:id/consolas/id"
		);
		if ( ! $validacion = ! \core\Validaciones::errores_validacion_request($validaciones, $datos)) {
			$datos['mensaje'] = 'Datos erróneos para identificar el artículo a borrar';
			$datos['url_continuar'] = \core\URL::http('?menu=consolas');
			\core\Distribuidor::cargar_controlador('mensajes', 'mensaje', $datos);
			return;
		}
		else
		{
			if ( ! $validacion = \modelos\Datos_SQL::delete($datos["values"], 'consolas')) {// Devuelve true o false
				$datos['mensaje'] = 'Error al borrar en la bd';
				$datos['url_continuar'] = \core\URL::http('?menu=consolas');
				\core\Distribuidor::cargar_controlador('mensajes', 'mensaje', $datos);
				return;
			}
			else
			{
			$datos = array("alerta" => "Se borrado correctamente.");
//			\core\Distribuidor::cargar_controlador('consolas', 'index', $datos);
                        \core\HTTP_Respuesta::set_header_line("location", \core\URL::generar("tabla"));
			\core\HTTP_Respuesta::enviar();
			}
		}
		
	}
	
        /**
         * Fución que realiza las conversiones de los campos usados en está aplicación al formato utilizado por MySQL.
         * Convertimos a formato MySQL
         * 
         * @param array $param Se corresponderá por regla general con datos['values'] y lo pasamos por referencia, para que modificque el valor
         */
        private static function convertir_a_formato_mysql(array &$param) {  //$param = datos['values'] y lo pasamos por referencia, para que modificque el valor        
            $param['precio'] = \core\Conversiones::decimal_puntoOcoma_a_punto($param['precio']);
            $param['fecha_lanzamiento'] = \core\Conversiones::fecha_es_a_mysql($param['fecha_lanzamiento']);
        }	
        
    /**
     * Fución que realiza las conversiones de los campos que muestran las tablas del formato utilizado por MySQL al formato europeo.
     * Convertimos a formato MySQL
     * 
     * @param array $param Se corresponderá por regla general con datos['values'] y lo pasamos por referencia, para que modificque el valor
     */
    private static function convertir_formato_mysql_a_ususario(array &$param) {  //$param = datos['values'] o $param = datos['filas'] si enviamos toda la tabla, y lo pasamos por referencia, para que modifique el valor
        
        //var_dump($param);
        if(!isset($param['id'])){   //Si existe $param['id'], es que vienen varias filas 0,1,2...,n, es decir no viene de intentar modificar o borrar ua única fila
            foreach ($param as $key => $fila) {
                $param[$key]['precio']=  \core\Conversiones::decimal_punto_a_coma($fila['precio']);
                $param[$key]['fecha_lanzamiento']=  \core\Conversiones::fecha_mysql_a_es($param[$key]['fecha_lanzamiento']);
            }
        }else{
            $param['precio']=  \core\Conversiones::decimal_punto_a_coma($param['precio']);            
            if(preg_match("/MSIE/", $_SERVER['HTTP_USER_AGENT'])){
                $param['fecha_lanzamiento']=  \core\Conversiones::fecha_mysql_a_es($param['fecha_lanzamiento']);
            }
            //fecha_entrada es readOnly en los formularios, por lo que no es necesario realizar la conversión.
        }
        
    }       
        
	public function listado_pdf(array $datos=array()) {
		
		$validaciones = array(
			"nombre" => "errores_texto"
		);
		\core\Validaciones::errores_validacion_request($validaciones, $datos);
		if (isset($datos['values']['nombre'])) 
			$select['where'] = " nombre like '%{$datos['values']['nombre']}%'";
		$select['order_by'] = 'nombre';
		$datos['filas'] = \modelos\Datos_SQL::select( $select, 'consolas');		
		
		$datos['html_para_pdf'] = \core\Vista::generar(__FUNCTION__, $datos);
		
		require_once(PATH_APP."lib/php/dompdf/dompdf_config.inc.php");

		$html =
		  '<html><body>'.
		  '<p>Put your html here, or generate it with your favourite '.
		  'templating system.</p>'.
		  '</body></html>';

		$dompdf = new \DOMPDF();
		$dompdf->load_html($datos['html_para_pdf']);
		$dompdf->render();
		$dompdf->stream("sample.pdf", array("Attachment" => 0));
		
		// \core\HTTP_Respuesta::set_mime_type('application/pdf');
		// $http_body = \core\Vista_Plantilla::generar('plantilla_principal', $datos);
		// \core\HTTP_Respuesta::enviar($datos, 'plantilla_pdf');
		
	}
	

	/**
	 * Genera una respuesta json.
	 * 
	 * @param array $datos
	 */
	public function listado_js(array $datos=array()) {
		
		$validaciones = array(
			"nombre" => "errores_texto"
		);
		\core\Validaciones::errores_validacion_request($validaciones, $datos);
		if (isset($datos['values']['nombre'])) 
			$select['where'] = " nombre like '%{$datos['values']['nombre']}%'";
		$select['order_by'] = 'nombre';
		$datos['filas'] = \modelos\Datos_SQL::select($select, 'consolas');
				
		$datos['contenido_principal'] = \core\Vista::generar(__FUNCTION__, $datos);
		
		\core\HTTP_Respuesta::set_mime_type('text/json');
		$http_body = \core\Vista_Plantilla::generar('plantilla_json', $datos);
		\core\HTTP_Respuesta::enviar($http_body);
		
	}
	
	/**
	 * Genera una respuesta json con un array que contiene objetos, siendo cada objeto una fila.
	 * @param array $datos
	 */
	public function listado_js_array(array $datos=array()) {
		
		$validaciones = array(
			"nombre" => "errores_texto"
		);
		\core\Validaciones::errores_validacion_request($validaciones, $datos);
		if (isset($datos['values']['nombre'])) 
			$select['where'] = " nombre like '%{$datos['values']['nombre']}%'";
		$select['order_by'] = 'nombre';
		$datos['filas'] = \modelos\Datos_SQL::select( $select, 'consolas');
				
		$datos['contenido_principal'] = \core\Vista::generar(__FUNCTION__, $datos);
		
		\core\HTTP_Respuesta::set_mime_type('text/json');
		$http_body = \core\Vista_Plantilla::generar('plantilla_json', $datos);
		\core\HTTP_Respuesta::enviar($http_body);
		
	}
	
	
	/**
	 * Genera una respuesta xml.
	 * 
	 * @param array $datos
	 */
	public function listado_xml(array $datos=array()) {
		
		$validaciones = array(
			"nombre" => "errores_texto"
		);
		\core\Validaciones::errores_validacion_request($validaciones, $datos);
		if (isset($_datos['values']['nombre'])) 
			$select['where'] = " nombre like '%{$_datos['values']['nombre']}%'";
		$select['order_by'] = 'nombre';
		$datos['filas'] = \modelos\Datos_SQL::select( $select, 'consolas');
				
		$datos['contenido_principal'] = \core\Vista::generar(__FUNCTION__, $datos);
		
		\core\HTTP_Respuesta::set_mime_type('text/xml');
		$http_body = \core\Vista_Plantilla::generar('plantilla_xml', $datos);
		\core\HTTP_Respuesta::enviar($http_body);
		
	}
	
	
	
	
	/**
	 * Genera una respuesta excel.
	 * @param array $datos
	 */
	public function listado_xls(array $datos=array()) {
		
		$validaciones = array(
			"nombre" => "errores_texto"
		);
		\core\Validaciones::errores_validacion_request($validaciones, $datos);
		if (isset($_datos['values']['nombre'])) 
			$select['where'] = " nombre like '%{$_datos['values']['nombre']}%'";
		$select['order_by'] = 'nombre';
		$datos['filas'] = \modelos\Datos_SQL::select( $select, 'consolas');
				
		$datos['contenido_principal'] = \core\Vista::generar(__FUNCTION__, $datos);
		
		\core\HTTP_Respuesta::set_mime_type('application/excel');
		$http_body = \core\Vista_Plantilla::generar('plantilla_xls', $datos);
		\core\HTTP_Respuesta::enviar($http_body);
		
	}
	
	
} // Fin de la clase