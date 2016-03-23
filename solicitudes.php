<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH.'libraries/swift_mailer/lib/swift_required.php';
class solicitudes extends CI_Controller {


	private $dataDefault = array(
		'title' => 'Unidad de Acceso a la Información Pública',
		'layout' => 'layout/lytAdminDefault',
		'content' => 'vNotFound',
		'scripts' => array()
	);

    public function resultados($strJSONIds = "")
    {
        $solicitudes_ids = $this->input->post('hidden_solicitudes_ids');
        $cadena_sol = "0";
           $data['scripts'] =array('msolicitudespublico','utils','sesion','usuario','msolicitudes3');
		   $data['content'] = 'vListadoPublico';
		   $sol_seleccionadas = json_decode($solicitudes_ids);
		   if(sizeof($sol_seleccionadas)== 0){
				$cadena_sol = '';
		   }else{

			   foreach ($sol_seleccionadas as $arreglo) {
			   	if($arreglo != ''){
			   		$cadena_sol = $cadena_sol.",".$arreglo;
			    }
			   }
		   }
		   $data['mis_solicitudes'] = $cadena_sol;
		   $data = array_merge($this->dataDefault, $data);
		   $this->load->view($data['layout'], $data);
    }
	public function Listado()
	{
            if (!$this->session->userdata('id_usuario')){
                redirect('inicio/cerrar');
            }else{
                
                if ($this->session->userdata('id_tipo_usuario')==6){
                    $this->load->model("Solicitud_model", "solicitud");
                    $unidades=$this->solicitud->getUnidadesEnlaceTodo("id_unidad_enlace ('activo=1')");
                    
                    $data['unidades'] =$unidades;
                }
                
                $data['scripts'] =array('msolicitudes3');
                $data['content'] = 'vListado';
                $data = array_merge($this->dataDefault, $data);
                $this->load->view($data['layout'], $data);
            }
	}
	public function Archivos()
	{

		   $data['scripts'] =array('msolicitudes3');
		   $data['content'] = 'vArchivos';
		   $data = array_merge($this->dataDefault, $data);
		   $this->load->view($data['layout'], $data);



	}
	 public function upload()
    {
    	$this->load->model("Solicitud_model", "solicitud");
    	$id_solicitud = $this->input->post('id_solicitud');
    	$id_unidad = $this->input->post('id_unidad');

    	$id_tipo_adjunto = $this->input->post('id_tipo_solicitud');
    	$nuevo_dir = "Enlace_".$id_solicitud;

    	if(!file_exists($ruta)){

    	mkdir("{$_SERVER['DOCUMENT_ROOT']}/{$this->config->item('dirPrincipal')}/uploads/".$nuevo_dir, 0700);
        }
        //crear la ruta absoluta
        $targetPath = "{$_SERVER['DOCUMENT_ROOT']}/{$this->config->item('dirPrincipal')}/uploads/".$nuevo_dir;
        if (file_exists($targetPath.$_FILES['Filedata']['name'])) {
        	 echo 'El Archivo seleccionado ya existe';
        }else{

        if (!empty($_FILES)) {
            $nombreArchivo = $_FILES['Filedata']['name'];
            $tempFile = $_FILES['Filedata']['tmp_name'];
            $targetFile =  $targetPath."/".$nombreArchivo;
            if(move_uploaded_file($tempFile,$targetFile))
            {
            	$where = "id_solicitud = ".$id_solicitud." and nombre_adjunto like '".$nombreArchivo."' and id_unidad =".$id_unidad;
                 $obten_archivo = $this->solicitud->getArchivosEnlace($where);
                 if(sizeof($obten_archivo)==0){
              	 	  $carga_archivo = $this->solicitud->AgregarArchivos($id_solicitud,$id_tipo_adjunto,$nombreArchivo,$id_unidad);
                 }
 				 //echo 'Archivo Subido Con éxito';
            	 echo "Agregada con exito".$id_tipo_solicitud;
            	 //Copia index.php en blanco a carpeta
					$srcfile="{$_SERVER['DOCUMENT_ROOT']}/{$this->config->item('dirPrincipal')}/uploads/index.php";
					$dstfile=$targetPath."/index.php";
					copy($srcfile, $dstfile);
            }
        }
        }

    }
    
    public function upload_respuesta()
    {
        try
        {
            if (!empty($_FILES)) 
            {
                $nombreArchivo = $_FILES['Filedata']['name'];
                $tempFile = $_FILES['Filedata']['tmp_name'];
                
                
                $nombre=$nombreArchivo;
                
                $nombre=ucfirst(mb_strtolower($nombre,'UTF-8'));
                $nombre=  str_replace(" ", "", $nombre);
                $nombre=  str_replace("_", "", $nombre);
                $nombre=  str_replace("-", "", $nombre);
                $nombre=  str_replace("ñ", "n", $nombre);
                $nombre=  str_replace("á", "a", $nombre);
                $nombre=  str_replace("é", "e", $nombre);
                $nombre=  str_replace("í", "i", $nombre);
                $nombre=  str_replace("ó", "o", $nombre);
                $nombre=  str_replace("ú", "u", $nombre);
                $nombre=  str_replace("ü", "u", $nombre);

                $nombre=  str_replace("Ñ", "n", $nombre);
                $nombre=  str_replace("Á", "A", $nombre);
                $nombre=  str_replace("É", "E", $nombre);
                $nombre=  str_replace("Í", "I", $nombre);
                $nombre=  str_replace("Ó", "O", $nombre);
                $nombre=  str_replace("Ú", "U", $nombre);
                $nombre=  str_replace("Ü", "U", $nombre);  
            
                $nuevonombre=$nombre;                
                $extension=substr(strrchr($_FILES['Filedata']['name'], '.'), 1); // obtenemos la extension                
                $tipo_error=1;
                
                if(in_array(strtolower($extension), array('pdf','doc','docx','xls','xlsx','7z','zip','rar'))  )
                {
                    $id_archivo=0;
                    $this->load->model("Solicitud_model", "solicitud");
                    $id_solicitud = $this->input->post('id_solicitud');
                    $id_unidad = 0;

                    $id_tipo_adjunto = $this->input->post('id_tipo_solicitud');
                    $nuevo_dir = "RespuestaFolio".$id_solicitud;

                    $targetPath = "{$_SERVER['DOCUMENT_ROOT']}/{$this->config->item('dirPrincipal')}/uploads/".$nuevo_dir;

                    if(!file_exists($targetPath))
                        mkdir("{$_SERVER['DOCUMENT_ROOT']}/{$this->config->item('dirPrincipal')}/uploads/".$nuevo_dir, 0700);

                    //crear la ruta absoluta
                    /*if (file_exists($targetPath."/".$nombre))
                    {
                             $mensaje='El Archivo seleccionado ya existe';
                             $tipo_error=1;
                    }
                    else*/
                    {                        
                        $targetFile =  $targetPath."/".$nombreArchivo;
                        if(move_uploaded_file($tempFile,$targetFile))
                        {
                            $id_archivo = $this->solicitud->AgregarArchivos($id_solicitud,$id_tipo_adjunto,$nuevonombre,$id_unidad);
                            //echo 'Archivo Subido Con éxito';
                            $mensaje="Agregado con exito en la solicitud ".$id_solicitud;
                            $tipo_error=0;
                            
                            rename($targetFile,$targetPath."/".$nuevonombre); // Renombra los archivos

                            $srcfile="{$_SERVER['DOCUMENT_ROOT']}/{$this->config->item('dirPrincipal')}/uploads/index.php";
                            $dstfile=$targetPath."/index.php";
                            copy($srcfile, $dstfile);
                        }
                    }
                }
                else
                {
                    $mensaje="Las extensiones de archivo válidas son: .pdf, .doc, .docx, .xls, .xlsx, .zip, .rar y .7z";
                    $tipo_error=1;
                }
            }
        }
        catch(Exception $e)
        {
            $mensaje=  utf8_encode($e->getMessage());
            $tipo_error=1;
        }
        
        echo json_encode(array("mensaje"=>$mensaje,"tipo_error"=>$tipo_error,"nombre_archivo"=>$nuevonombre,"id_archivo"=>$id_archivo));
    }
    
    
    public function eliminar_archivo_respuesta()
    {
        $this->db->where('id_adjunto',$this->input->post('id_archivo'));
        $this->db->delete('uaip_adjuntos');
                
        $id_solicitud = $this->input->post('id_solicitud');
        $targetPath = "{$_SERVER['DOCUMENT_ROOT']}/{$this->config->item('dirPrincipal')}/uploads/RespuestaFolio".$id_solicitud."/".$this->input->post('nombre_archivo_fisico');
        unlink($targetPath);
    }
    
    public function upload_respuesta_publica()
    {
        try
        {
            if (!empty($_FILES)) 
            {
                $nombreArchivo = $_FILES['Filedata']['name'];
                $tempFile = $_FILES['Filedata']['tmp_name'];
                
                $nombre=$nombreArchivo;
                
                $nombre=ucfirst(mb_strtolower($nombre,'UTF-8'));
                $nombre=  str_replace(" ", "", $nombre);
                $nombre=  str_replace("_", "", $nombre);
                $nombre=  str_replace("-", "", $nombre);
                $nombre=  str_replace("ñ", "n", $nombre);
                $nombre=  str_replace("á", "a", $nombre);
                $nombre=  str_replace("é", "e", $nombre);
                $nombre=  str_replace("í", "i", $nombre);
                $nombre=  str_replace("ó", "o", $nombre);
                $nombre=  str_replace("ú", "u", $nombre);
                $nombre=  str_replace("ü", "u", $nombre);

                $nombre=  str_replace("Ñ", "n", $nombre);
                $nombre=  str_replace("Á", "A", $nombre);
                $nombre=  str_replace("É", "E", $nombre);
                $nombre=  str_replace("Í", "I", $nombre);
                $nombre=  str_replace("Ó", "O", $nombre);
                $nombre=  str_replace("Ú", "U", $nombre);
                $nombre=  str_replace("Ü", "U", $nombre);  
            
                $nuevonombre=$nombre;            
                
                $extension=substr(strrchr($_FILES['Filedata']['name'], '.'), 1); // obtenemos la extension                
                $tipo_error=1;
                
                if(in_array(strtolower($extension), array('pdf','doc','docx','xls','xlsx','7z','zip','rar'))  )
                {
                    $id_archivo=0;
                    $this->load->model("Solicitud_model", "solicitud");
                    $id_solicitud = $this->input->post('id_solicitud');
                    $id_unidad = 0;
                    
                    $id_tipo_adjunto = $this->input->post('id_tipo_solicitud');
                    $nuevo_dir = "RespuestaPublicaFolio".$id_solicitud;

                    $targetPath = "{$_SERVER['DOCUMENT_ROOT']}/{$this->config->item('dirPrincipal')}/uploads/".$nuevo_dir;

                    if(!file_exists($targetPath)){
                        mkdir("{$_SERVER['DOCUMENT_ROOT']}/{$this->config->item('dirPrincipal')}/uploads/".$nuevo_dir, 0700);
                    }
                    //crear la ruta absoluta

                    if (file_exists($targetPath.$_FILES['Filedata']['name']))
                    {
                        $mensaje='El Archivo seleccionado ya existe';
                        $tipo_error=1;
                    }
                    else
                    {-
                        $targetFile =  $targetPath."/".$nombreArchivo;
                        if(move_uploaded_file($tempFile,$targetFile))
                        {
                            $id_archivo = $this->solicitud->AgregarArchivos($id_solicitud,$id_tipo_adjunto,$nuevonombre,$id_unidad);
                            //echo 'Archivo Subido Con éxito';
                            $mensaje="Agregado con exito en la solicitud ".$id_solicitud;
                            $tipo_error=0;
                            
                            rename($targetFile,$targetPath."/".$nuevonombre); // Renombra los archivos
                        }
                    }
                }
                else
                {
                    $mensaje="Las extensiones de archivo válidas son: .pdf, .doc, .docx, .xls, .xlsx, .zip, .rar y .7z";
                    $tipo_error=1;
                }
            }
        }
        catch(Exception $e)
        {
            $mensaje=  utf8_encode($e->getMessage());
            $tipo_error=1;
        }
        
        echo json_encode(array("mensaje"=>$mensaje,"tipo_error"=>$tipo_error,"nombre_archivo"=>$nuevonombre,"id_archivo"=>$id_archivo));        
    }
    
    public function eliminar_archivo_vp()
    {
        $this->db->where('id_adjunto',$this->input->post('id_archivo'));
        $this->db->delete('uaip_adjuntos');
                
        $id_solicitud = $this->input->post('id_solicitud');
        $targetPath = "{$_SERVER['DOCUMENT_ROOT']}/{$this->config->item('dirPrincipal')}/uploads/RespuestaPublicaFolio".$id_solicitud."/".$this->input->post('nombre_archivo_fisico');
        unlink($targetPath);
    }

	public function Aparece()
	{
	}
        
	public function NuevaSolicitud()
	{
		$this->load->model("Solicitud_model", "solicitud");
		$query = $this->solicitud->getTipoSolicitud();
		$medio = $this->solicitud->getMedioRespuesta();
		$envio = $this->solicitud->getMedioEnvio();
		//$data['scripts'] =array('msolicitudes');

		$data['tipo_solicitud'] = $query;
		$data['medio'] = $medio;
		$data['envio'] = $envio;
		$data['content'] = 'vFormNuevaSolicitud';
		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);


	}
	public function TipoEnvio()
	{
		$this->load->model("Solicitud_model", "solicitud");
		$vacio = $this->input->post('vacio');
		$envio = $this->solicitud->getMedioEnvio();
		$data['scripts'] =array('msolicitudes3');
		$data['envio'] = $envio;
		if($vacio == 0){
		   $data['content'] = 'vFormTipoEnvio';
	    }else{
	    	$data['content'] = 'vFormTipoEnvioVacio';
	    }
            
            $data = array_merge($this->dataDefault, $data);
            $this->load->view($data['layout'], $data);
	}
	public function EjemploFecha()
	{
		 $this->load->library('nohabil');
		 $limite = 5;
		 	/*$hora1 = strtotime( "15:30" );
			$hora2 = strtotime(date("H:i:s"));
			if( $hora2 > $hora1 ) {
			//print('$hora1 es mayor a $hora2');
			$fecha_ingreso = $this->nohabil->CalculaFecha(0);
			$fecha_ingreso = date('Y-d-m H:i:s',strtotime($fecha_ingreso));
			//$limite = $limite +1;
			//print($fecha_ingreso);
			} else {
			//print('$hora2 es mayor a $hora1');
			$fecha_ingreso = date('Y-d-m H:i:s');
			//print($fecha_ingreso);
			}*/
		$fecha_ingreso = $this->nohabil->CalculaFecha(0);
		$fecha_limite = $this->nohabil->CalculaFecha($limite);
		print(date('Y-d-m H:i:s',strtotime($fecha_ingreso))." ->".date('Y-d-m H:i:s',strtotime($fecha_limite)));
		
	}

	public function AgregarSol()
	{
		 $this->load->model("Solicitud_model", "solicitud");
		 $this->load->library('nohabil');
                 
		$tipo_solicitud = $this->input->post('tipo_solicitud');
		$contenido_sol = $this->input->post('contenido_sol');
		$medio_respuesta = $this->input->post('medio_respuesta');
		$tipo_envio = $this->input->post('tipo_envio');
		switch ($tipo_solicitud) {
			case 1:
				$limite = 5;
				break;
			case 2:
				$limite = 20;
				break;
			case 3:
				$limite = 30;
				break;
			case 4:
				$limite = 30;
				break;
		}

                  
			/*$hora1 = strtotime( "15:30" );
			$hora2 = strtotime(date("H:i:s"));
			if( $hora2 > $hora1 ) {
			//print('$hora1 es mayor a $hora2');
			$fecha_ingreso = $this->nohabil->CalculaFecha(1);
			$fecha_ingreso = date('Y-d-m H:i:s',strtotime($fecha_ingreso));
			$limite = $limite +1;
			//print($fecha_ingreso);
			} else {
			//print('$hora2 es mayor a $hora1');
			$fecha_ingreso = date('Y-d-m H:i:s');
			//print($fecha_ingreso);
			}*/
		$fecha_ingreso = $this->nohabil->CalculaFecha(0);
		$fecha_limite = $this->nohabil->CalculaFecha($limite);

		$medio_respuesta = $this->solicitud->InsertaMedioRespuesta($tipo_solicitud,$contenido_sol,$medio_respuesta,$tipo_envio);
		$id_medio_respuesta = $medio_respuesta->id_medio_respuesta;

		$NuevaSolicitud = $this->solicitud->InsertaSolicitud($id_medio_respuesta,$contenido_sol,$tipo_solicitud,$fecha_limite,$fecha_ingreso);

		$id_solicitud = $NuevaSolicitud->id_solicitud;
		$estatus = 1;
		$observaciones_cana = "nueva solicitud";
		$id_unidad_enlace = 0;
		$historial = $this->solicitud->GuardarHistorial($id_solicitud,$estatus,$unidad_enlace,$observaciones_cana);

		$data['exito'] =1;
		$data['layout'] = 'layout/lytVerificando';
		$data['content'] = 'vNuevaSolicitud';
		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);
	}
	public function DetalleSolicitudHistorial()
	{
                $data['fecha_respuesta'] =0;
                $data['fecha_proceso'] =0;
                $data['fecha_incompleta'] =0;
                $data['fecha_completada'] = 0;
                $data['fecha_prorroga'] = 0;
                $data['fecha_condicionada'] = 0;

		$this->load->model("Solicitud_model", "solicitud");
		//$data['scripts'] = array('lib/jquery-ui-1.10.3.custom','lib/jquery.jqGrid.min','config','utils','sesion','usuario','mbusqueda','msolicitudes');
		$data['content'] = 'vDetalleSolicitudHistorial';
                $data['contenido_class'] = 'ALGO';
		$data['id_solicitud'] = $this->input->post('id_solicitud');
		//$id_solicitud = $this->input->post('id_solicitud');

		$where = "id_solicitud = ".$this->input->post('id_solicitud')." and id_estatus in (8,9) and contenido_publico is not null";
		$whereFinal = "id_solicitud = ".$this->input->post('id_solicitud')." and id_tipo_adjunto = 2";
		$whereRespuesta = "id_solicitud = ".$this->input->post('id_solicitud')."";
		$query = $this->solicitud->getUnaSolicitudHistorial($where);
		$id_estatus = $query->id_estatus;
		$whereEstatus = "id_estatus = ".$id_estatus;

		$data['info_solicitud'] =$query;

		$query2 = $this->solicitud->getHistorialRespuesta($whereRespuesta);
		$archivosRespuesta = $this->solicitud->getArchivosRespuesta($whereFinal);
		$data['archivos_respuesta'] = $archivosRespuesta;
		if($query2){
			//$data['fecha_respuesta'] =$query2;
			foreach ($query2 as $fecha) {
				if($fecha->id_estatus == 8){
				  $data['fecha_respuesta'] = $fecha->fecha;
			    }
			}
	    }

		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}


	public function DetalleSolicitud()
	{
			$data['fecha_respuesta'] =0;
			$data['fecha_proceso'] =0;
			$data['fecha_incompleta'] =0;
			$data['fecha_completada'] = 0;
			$data['fecha_prorroga'] = 0;
			$data['fecha_condicionada'] = 0;

		$this->load->model("Solicitud_model", "solicitud");
                
		$data['content'] = 'vDetalleSolicitud';
		$data['layout'] = 'layout/lytVacio';
		$data['id_solicitud'] = $this->input->post('id_solicitud');
		//$id_solicitud = $this->input->post('id_solicitud');

		$where = "id_solicitud = ".$this->input->post('id_solicitud');
		$whereEnlace = "id_solicitud = ".$this->input->post('id_solicitud')." and id_tipo_adjunto = 3";
		$whereFinal = "id_solicitud = ".$this->input->post('id_solicitud')." and id_tipo_adjunto = 1";
		$whereRespuesta = "id_solicitud = ".$this->input->post('id_solicitud')."";
		
                $query = $this->solicitud->getUnaSolicitud($where);
                
		$id_estatus = $query->id_estatus;
		$id_usuario = $query->id_usuario;
		$id_medio_respuesta = $query->id_medio_respuesta;
		$whereEstatus = "id_estatus = ".$id_estatus;

		$data['info_solicitud'] =$query;
                
		$query2 = $this->solicitud->getHistorialRespuesta($whereRespuesta);
		$mi_medio_respuesta = $this->solicitud->getMedioRespuestaUna($id_medio_respuesta);
		$queryFlujo = $this->solicitud->getFlujoSolicitud($whereEstatus);
		$queryUsuario = $this->solicitud->getUsuarioSolicitud($id_usuario);
		$idestado=$queryUsuario->estado;
		$tipo_persona=$queryUsuario->id_tipo_persona;
		$repre=$queryUsuario->id_representante;
		$idpais=$queryUsuario->pais;
		if($idpais){
		$mipais = $this->solicitud->getUnPais($idpais);
		$pais = $mipais->dscpais;
	    }else{
	    $pais = "";
	    }
	    if($idestado){
				$miestado = $this->solicitud->getUnEstado($idestado);
				$estado = $miestado->dscestado;
	    }else{
	    	$estado = "";
	    }
	    if($repre){
				$rep = $this->solicitud->getRepresentante($repre);
				$representante = $rep->nombre_completo." ".$rep->ap_paterno." ".$rep->ap_materno;
	    }else{
				$representante = $rep->dscestado;
	    }

        $persona = $this->solicitud->getUnTipoPersona($tipo_persona);
        $mitipo_persona = $persona->dsc_tipo_persona;
        $estado = $miestado->dscestado;
		$queryUE= $this->solicitud->getUnidadEnlace($where);
		$ArchivosUE= $this->solicitud->getArchivosEnlace($whereEnlace);
		$archivosRespuesta = $this->solicitud->getArchivosRespuesta($whereFinal);
		$data['flujo'] =$queryFlujo;
		$data['usuario'] = $queryUsuario;
		$data['enlace'] = $queryUE;
		$data['archivos_ue'] = $ArchivosUE;
		$data['archivos_respuesta'] = $archivosRespuesta;
		$data['historial'] = $query2;
		$data['medio_respuesta'] = $mi_medio_respuesta;
		$data['pais'] = $pais;
		$data['estado'] = $estado;
		$data['tipo_persona'] = $mitipo_persona;
		$data['representante'] = $representante;
		if($query2){
			//$data['fecha_respuesta'] =$query2;
			foreach ($query2 as $fecha) {
				if($fecha->id_estatus == 8){
				  $data['fecha_respuesta'] = $fecha->fecha;
			    }
			    if($fecha->id_estatus == 7){
				  $data['fecha_cancelada'] = $fecha->fecha;

			    }
			    if($fecha->id_estatus == 4){
			    	if($this->session->userdata('id_tipo_usuario') == 2 || $this->session->userdata('id_tipo_usuario') == 4 ){
			    		if($fecha->id_estatus == 4 && $fecha->id_unidad_enlace ==$this->session->userdata('id_unidad_enlace'))
				  			$data['fecha_proceso'] = $fecha->fecha;
				  }else{
				  	$data['fecha_proceso'] = $fecha->fecha;
				  }
			    }
			    if($fecha->id_estatus == 2){
				  $data['fecha_incompleta'] = $fecha->fecha;
			    }
			    if($fecha->id_estatus == 3){
				  $data['fecha_completada'] = $fecha->fecha;
				  $data['complemento'] = $fecha->observaciones;
			    }
			    if($fecha->id_estatus == 5){
				  $data['fecha_prorroga'] = $fecha->fecha;
			    }
			    if($fecha->id_estatus == 6){
				  $data['fecha_condicionada'] = $fecha->fecha;
			    }
			}
	    }

		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);


	}
	public function imprimir_historial()
	{
			$data['fecha_respuesta'] =0;
			$data['fecha_proceso'] =0;
			$data['fecha_incompleta'] =0;
			$data['fecha_completada'] = 0;
			$data['fecha_prorroga'] = 0;
			$data['fecha_condicionada'] = 0;

		$this->load->model("Solicitud_model", "solicitud");
		$data['content'] = 'vImprimeSolicitud';
		$data['layout'] = 'layout/lytImprimirDefault';
		$data['id_solicitud'] = $this->input->get('id_solicitud');

		$where = "id_solicitud = ".$this->input->get('id_solicitud');
		$whereEnlace = "id_solicitud = ".$this->input->get('id_solicitud')." and id_tipo_adjunto = 3";
		$whereFinal = "id_solicitud = ".$this->input->get('id_solicitud')." and id_tipo_adjunto = 1";
		$query = $this->solicitud->getUnaSolicitud($where);
		$id_estatus = $query->id_estatus;
		$id_usuario = $query->id_usuario;
		$id_medio_respuesta = $query->id_medio_respuesta;
		$whereEstatus = "id_estatus = ".$id_estatus;

		$query2 = $this->solicitud->getHistorialRespuesta($where);
		$vista_hist = $this->solicitud->getVistaHistorial($where);
		$mi_medio_respuesta = $this->solicitud->getMedioRespuestaUna($id_medio_respuesta);
		$queryUE= $this->solicitud->getUnidadEnlace($where);
		if($query2){
			//$data['fecha_respuesta'] =$query2;
			foreach ($query2 as $fecha) {
				if($fecha->id_estatus == 8){
				  $data['fecha_respuesta'] = $fecha->fecha;
			    }
			    if($fecha->id_estatus == 7){
				  $data['fecha_cancelada'] = $fecha->fecha;

			    }
			    if($fecha->id_estatus == 4){
				  	$data['fecha_proceso'] = $fecha->fecha;
			    }
			    if($fecha->id_estatus == 2){
				  $data['fecha_incompleta'] = $fecha->fecha;
			    }
			    if($fecha->id_estatus == 3){
				  $data['fecha_completada'] = $fecha->fecha;
				  $data['complemento'] = $fecha->observaciones;
			    }
			    if($fecha->id_estatus == 5){
				  $data['fecha_prorroga'] = $fecha->fecha;
			    }
			    if($fecha->id_estatus == 6){
				  $data['fecha_condicionada'] = $fecha->fecha;
			    }
			}
	    }
		$archivosRespuesta = $this->solicitud->getArchivosRespuesta($whereFinal);

		$data['archivos_respuesta'] = $archivosRespuesta;
		$data['historial'] = $query2;
		$data['vista_historial'] = $vista_hist;
		$data['enlace'] = $queryUE;
		$data['medio_respuesta'] = $mi_medio_respuesta;
		$data['info_solicitud'] =$query;
		$mi_medio_respuesta = $this->solicitud->getMedioRespuestaUna($id_medio_respuesta);
		$data['medio_respuesta'] = $mi_medio_respuesta;


		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}
	public function OrientarSolicitud()
	{
		$this->load->model("Solicitud_model", "solicitud");
		
		$data['content'] = 'vOrientarSolicitud';
		$data['id_solicitud'] = $this->input->get('id_solicitud');
		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}
	public function CanalizarSolicitud()
	{
		$this->load->model("Solicitud_model", "solicitud");

		$query = $this->solicitud->getUnidadesEnlace();
		$data['unidades_enlace'] =$query;

		$data['content'] = 'vCanalizarSolicitud';
		$data['id_solicitud'] = $this->input->get('id_solicitud');
		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}
	public function OrientarGuardar()
	{
		$this->load->model("Solicitud_model", "solicitud");
		$id_solicitud = $this->input->post('mi_id_solicitud');
		$unidad_enlace = 0;
		$observaciones = $this->input->post('observaciones_orientar');
		$estatus = 13;
		$where = "id_solicitud = ".$id_solicitud;

		$query = $this->solicitud->ActualizarEstatus($id_solicitud,$estatus);
		$query2 = $this->solicitud->GuardarHistorial($id_solicitud,$estatus,$unidad_enlace,$observaciones);
		
		$trae_solicitud = $this->solicitud->getUnaSolicitud($where);
		$id_usuario = $trae_solicitud->id_usuario;
		$trae_datos_usuario = $this->solicitud->getDatosUsuario($id_usuario);
		$correo_usr = $trae_datos_usuario->email;
		
		
		//Enviar correo electrónico
		$transport = Swift_SmtpTransport::newInstance()
		->setHost($this->config->item('smtp_host'))
		->setPort($this->config->item('puerto'))
		->setEncryption($this->config->item('encriptacion'))
		->setUsername($this->config->item('usuario'))
		->setPassword($this->config->item('contraseña'));

		//Create the Mailer using your created Transport
		$mailer = Swift_Mailer::newInstance($transport);

		//Pass it as a parameter when you create the message
		$message = Swift_Message::newInstance();
		//Give the message a subject
		//Or set it after like this
		$message->setSubject('Unidad de Acceso a la Información Pública: UG');
		//no_reply@ugto.mx
		$message->setFrom(array($this->config->item('usuario') => 'Solicitud de Información '.$id_solicitud));
                //$message->addTo(trim("sortiza@ugto.mx"));
                $message->addTo(trim($correo_usr));
		$message->addBcc("uaip21@gmail.com");		             		              
		$message->addBcc("uaip@ugto.mx");

		//Add alternative parts with addPart()
		$message->addPart("
		          	<b>Su solicitud ".$id_solicitud." se ha marcado como orientada.</b> <br><br>
				  	".$observaciones."		          
					<br><br><br>
					<center>
					Atentamente <br><br><br>
					<p> Lic. Miriam Contreras Ortiz<br>
					Titular de la Unidad de Acceso a la Información Pública</p>

					<p>Lascuráin de Retana No. 5 Centro, <br>
					Guanajuato, Gto., México C.P. 36000  </p>

					<p>Tel.: (473) 7320006 <br>
					Exts.: 2042, 3102, 2058, 2043</p>

					<p>
					<a href=\"uaip@ugto.mx\">uaip@ugto.mx</a><br>
					<a href=\"m.contrerasortiz@ugto.mx\">m.contrerasortiz@ugto.mx</a>
					</p>
					<p>Horario de Atención: 8:30 am. a 3:30 pm.</p>
					 </center>
		", 'text/html');
		
                //@$result = $mailer->send($message);

		$data['content'] = 'vCanalizarGuardar';
		$data['accion'] = 15;

		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}
	public function CanalizarGuardar()
	{
		$this->load->model("Solicitud_model", "solicitud");
		$ya_existe_canalizada = 0;
		$id_solicitud = $this->input->post('id');
	
		$estatus = 4;
		$where = "id_solicitud = ".$id_solicitud;                
		$MensajeCorreo = $this->solicitud->getMensajeCorreo(1);
		$existeCanalizada =$this->solicitud->getHistorialRespuesta($where);
                $query = $this->solicitud->ActualizarEstatus($id_solicitud,$estatus);
                foreach ($existeCanalizada as $canalizada) {
                    if($canalizada->id_estatus == 4)
                        $ya_existe_canalizada = 1;                
                }
                
                $data = json_decode($this->input->post('data'));
                
                $correo_titular=array();
                $correo_responsable=array();     
                $correo_alterno=array();
                $correo_jefe_varias_unidades=array();
                $n=0;
                foreach ($data as $d)
                {
                    $query2 = $this->solicitud->GuardarHistorial($id_solicitud,$estatus,$d->cc_id_unidad_enlace,$d->cc_observaciones);
                    $trae_correo_titular = $this->solicitud->getTitularEnlace($d->cc_id_unidad_enlace);
                    $correo_titular[] = $trae_correo_titular->email;             

                    $trae_correo_enlace = $this->solicitud->getResponsableEnlace($d->cc_id_unidad_enlace);
                    $correo_responsable[] = $trae_correo_enlace->email;          
                    $correo_alterno[] = $trae_correo_enlace->email_alterno;  
                    $n++;
                    
                    $checa_canalizada = $this->solicitud->checarCanalizada($id_solicitud,$d->cc_id_unidad_enlace);
                    if($checa_canalizada->id_solicitud==''){
                            $query3 = $this->solicitud->GuardarUnidad($id_solicitud,$d->cc_id_unidad_enlace);
                    }
                    
                    $correos_jefe_vu=array();
                    $qJefe=$this->solicitud->getUnidadesCargo("00000",$d->cc_id_unidad_enlace);
                    if(count($qJefe)>0)
                    {
                    $dCorreos = json_decode(file_get_contents($this->config->item('url_ws_nue').trim($qJefe->id_empleado)));      
                    foreach($dCorreos as $dc)
                    {
                        $correos_jefe_vu[]=trim($dc->email);
                    }
                    }
                    
                    
                    $correo_jefe_varias_unidades[]=$correos_jefe_vu;                    
                }
	

		$trae_solicitud = $this->solicitud->getUnaSolicitud($where);
		$id_usuario = $trae_solicitud->id_usuario;
		$trae_datos_usuario = $this->solicitud->getDatosUsuario($id_usuario);
		$correo_usr = $trae_datos_usuario->email;
		if(number_format($ya_existe_canalizada,0,'','') == 0)
                {
                    //Enviar correo electrónico
                    $transport = Swift_SmtpTransport::newInstance()
                    ->setHost($this->config->item('smtp_host'))
                    ->setPort($this->config->item('puerto'))
                    ->setEncryption($this->config->item('encriptacion'))
                    ->setUsername($this->config->item('usuario'))
                    ->setPassword($this->config->item('contraseña'));

                    //Create the Mailer using your created Transport
                    $mailer = Swift_Mailer::newInstance($transport);

                    //Pass it as a parameter when you create the message
                    $message = Swift_Message::newInstance();
                    //Give the message a subject
                    //Or set it after like this
                    $message->setSubject('Unidad de Acceso a la Información Pública: UG');
                    //no_reply@ugto.mx
                    $message->setFrom(array($this->config->item('usuario') => 'Solicitud de Información '.$id_solicitud));
                    //$message->addTo(trim("sortiza@ugto.mx"));		              
                    $message->addTo(trim($correo_usr));
                    $message->addBcc("uaip21@gmail.com");		             		              
                    $message->addBcc("uaip@ugto.mx");

                    //Add alternative parts with addPart()
                    $message->addPart("
                    <b>Su solicitud ".$id_solicitud." se ha puesto en proceso.</b> <br><br>
                    ".$MensajeCorreo->mensaje."

                    <br><br><br>
                    <center>
                    Atentamente <br><br><br>
                    <p> Lic. Miriam Contreras Ortiz<br>
                    Titular de la Unidad de Acceso a la Información Pública</p>

                    <p>Lascuráin de Retana No. 5 Centro, <br>
                    Guanajuato, Gto., México C.P. 36000  </p>

                    <p>Tel.: (473) 7320006 <br>
                    Exts.: 2042, 3102, 2058, 2043</p>

                    <p>
                    <a href=\"uaip@ugto.mx\">uaip@ugto.mx</a><br>
                    <a href=\"m.contrerasortiz@ugto.mx\">m.contrerasortiz@ugto.mx</a>
                    </p>
                    <p>Horario de Atención: 8:30 am. a 3:30 pm.</p>
                    </center>
                    ", 'text/html');
                    //@$result = $mailer->send($message);
		 }
                 
                  for($i=0;$i<$n;$i++)
                  {
                    $transport = Swift_SmtpTransport::newInstance()
                    ->setHost($this->config->item('smtp_host'))
                    ->setPort($this->config->item('puerto'))
                    ->setEncryption($this->config->item('encriptacion'))
                    ->setUsername($this->config->item('usuario'))
                    ->setPassword($this->config->item('contraseña'));

                    //Create the Mailer using your created Transport
                    $mailer = Swift_Mailer::newInstance($transport);
                    //Pass it as a parameter when you create the message
                    $message = Swift_Message::newInstance();
                    //Give the message a subject
                    //Or set it after like this
                    $message->setSubject('Unidad de Acceso a la Información Pública: UG');
                    //no_reply@ugto.mx
                    $message->setFrom(array($this->config->item('usuario') => 'Solicitud de Información '.$id_solicitud));
                    //$message->addTo("sortiza@ugto.mx");
                    $message->addTo(trim($correo_titular[$i]));
                    $message->addBcc("uaip21@gmail.com");
                    $message->addBcc("uaip@ugto.mx");
                   
                    if(trim($correo_responsable[$i]) == ''){
                        $correo_responsable[$i] = $correo_titular[$i];
                    }
           
                    $message->addTo(trim($correo_responsable[$i]));
                                       
                                   
                    if(trim($correo_alterno[$i]!=''))
                        $message->addTo(trim($correo_alterno[$i]));
                    
                    $correos_jvu=$correo_jefe_varias_unidades[$i];
                    foreach($correos_jvu as $c)
                    {
                        if(trim($c)!='')
                            $message->addBcc(trim($c));
                    }
                
                    $message->addPart("									
                    <b>Se ha canalizado una nueva Solicitud de Información a su Unidad con folio ".$id_solicitud."</b><br><br>
                    La cual se detalla a continuación:<br><br>".
                    $trae_solicitud->contenido
                    ."<br><br>
                    Para conocer el contenido, el plazo para dar respuesta y comenzar a dar trámite a la solicitud de información le pedimos ingresar al Sistema de Solicitudes de Información Pública (SSIP) en el siguiente link http://www.transparencia.ugto.mx
                    <br><br>
                    Le recordamos que el oficio final de respuesta tendrá que incluir el número de folio de la solicitud.
                    <br><br>
                    Para cualquier duda o aclaración favor de comunicarse a la Unidad de Acceso a la Información Pública al teléfono (473) 7320006 extensiones 3102, 2042 o 2043. Agradecemos su colaboración.

                    <br><br><br>
                    <center>
                    Atentamente <br><br><br>
                    <p> Lic. Miriam Contreras Ortiz<br>
                    Titular de la Unidad de Acceso a la Información Pública</p>

                    <p>Lascuráin de Retana No. 5 Centro, <br>
                    Guanajuato, Gto., México C.P. 36000  </p>

                    <p>Tel.: (473) 7320006 <br>
                    Exts.: 2042, 3102, 2058, 2043</p>

                    <p>
                    <a href=\"uaip@ugto.mx\">uaip@ugto.mx</a><br>
                    <a href=\"m.contrerasortiz@ugto.mx\">m.contrerasortiz@ugto.mx</a>
                    </p>
                    <p>Horario de Atención: 8:30 am. a 3:30 pm.</p>
                    </center>
                    ", 'text/html');
		    //@$result = $mailer->send($message);
		 }

                
                $r=array("id_tipo_error"=>1,"mensaje"=>"La solicitud ha sido canalizada de manera satisfactoria.");
                echo json_encode($r);
	}
	public function CancelarSolicitud()
	{

		$data['content'] = 'vCancelarSolicitud';
		$data['id_solicitud'] = $this->input->get('id_solicitud');
		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}

	public function CancelarGuardar()
	{
		$this->load->model("Solicitud_model", "solicitud");
		$id_solicitud = $this->input->post('id_solicitud');
		$observaciones_cancelar = $this->input->post('observaciones_cancelar');
		$estatus = 7;
		$where = "id_solicitud = ".$id_solicitud;
		$unidad_enlace = 0;
		$data['accion'] = 2;

		$query = $this->solicitud->ActualizarEstatus($id_solicitud,$estatus);
		$query2 = $this->solicitud->GuardarHistorial($id_solicitud,$estatus,$unidad_enlace,$observaciones_cancelar);


		$trae_solicitud = $this->solicitud->getUnaSolicitud($where);
		$id_usuario = $trae_solicitud->id_usuario;
		$trae_datos_usuario = $this->solicitud->getDatosUsuario($id_usuario);
		$correo_usr = $trae_datos_usuario->email;
		$MensajeCorreo = $this->solicitud->getMensajeCorreo(2);

		 //Enviar correo electrónico
        $transport = Swift_SmtpTransport::newInstance()
              ->setHost($this->config->item('smtp_host'))
              ->setPort($this->config->item('puerto'))
              ->setEncryption($this->config->item('encriptacion'))
              ->setUsername($this->config->item('usuario'))
              ->setPassword($this->config->item('contraseña'));

              //Create the Mailer using your created Transport
              $mailer = Swift_Mailer::newInstance($transport);

              //Pass it as a parameter when you create the message
              $message = Swift_Message::newInstance();
        //Give the message a subject
        //Or set it after like this
              $message->setSubject('Unidad de Acceso a la Información Pública: UG');
              //no_reply@ugto.mx
              $message->setFrom(array($this->config->item('usuario') => 'Solicitud de Información '.$id_solicitud));
              //$message->addTo(trim("sortiza@ugto.mx"));
              $message->addTo(trim($correo_usr));
                $message->addBcc("uaip21@gmail.com");
		$message->addBcc("uaip@ugto.mx");
              //Add alternative parts with addPart()
              $message->addPart("
                          <b>Su solicitud ".$id_solicitud." ha sido desechada.</b> <br><br>
                          <b>Observaciones:</b><br>
                          ".$observaciones_cancelar."
                          <br><br>
                          ".$MensajeCorreo->mensaje."
                          <br><br><br>
                           <center>
                           Atentamente <br><br><br>
							<p> Lic. Miriam Contreras Ortiz<br>
							Titular de la Unidad de Acceso a la Información Pública</p>

							<p>Lascuráin de Retana No. 5 Centro, <br>
							Guanajuato, Gto., México C.P. 36000  </p>

							<p>Tel.: (473) 7320006 <br>
							Exts.: 2042, 3102, 2058, 2043</p>

							<p>
							<a href=\"uaip@ugto.mx\">uaip@ugto.mx</a><br>
							<a href=\"m.contrerasortiz@ugto.mx\">m.contrerasortiz@ugto.mx</a>
							</p>
							<p>Horario de Atención: 8:30 am. a 3:30 pm.</p>
                             </center>
              ", 'text/html');
              //@$result = $mailer->send($message);


		//$data['unidades_enlace'] =$query;

		$data['content'] = 'vCanalizarGuardar';

		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}
	public function IncompletaSolicitud()
	{

		$data['content'] = 'vIncompletaSolicitud';
		$data['id_solicitud'] = $this->input->get('id_solicitud');
		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}

	public function IncompletaGuardar()
	{
		$this->load->model("Solicitud_model", "solicitud");
		$this->load->library('nohabil');
		$id_solicitud = $this->input->post('id_solicitud');
		$observaciones_incompleta = $this->input->post('observaciones_incompleta');
		$estatus = 2;
		$where = "id_solicitud = ".$id_solicitud;
		$unidad_enlace = 0;
		$data['accion'] = 3;
		$limite = 3;

		$fecha_limite = $this->nohabil->CalculaFecha($limite,1);

		$solicitud = $this->solicitud->FechaLimiteCompletar($id_solicitud,$fecha_limite);
		$query = $this->solicitud->ActualizarEstatus($id_solicitud,$estatus);
		$query2 = $this->solicitud->GuardarHistorial($id_solicitud,$estatus,$unidad_enlace,$observaciones_incompleta);

		$trae_solicitud = $this->solicitud->getUnaSolicitud($where);
		$id_usuario = $trae_solicitud->id_usuario;
		$trae_datos_usuario = $this->solicitud->getDatosUsuario($id_usuario);
		$correo_usr = $trae_datos_usuario->email;
		$MensajeCorreo = $this->solicitud->getMensajeCorreo(3);

		 //Enviar correo electrónico
        $transport = Swift_SmtpTransport::newInstance()
              ->setHost($this->config->item('smtp_host'))
              ->setPort($this->config->item('puerto'))
              ->setEncryption($this->config->item('encriptacion'))
              ->setUsername($this->config->item('usuario'))
              ->setPassword($this->config->item('contraseña'));

              //Create the Mailer using your created Transport
              $mailer = Swift_Mailer::newInstance($transport);

              //Pass it as a parameter when you create the message
              $message = Swift_Message::newInstance();
        //Give the message a subject
        //Or set it after like this
              $message->setSubject('Unidad de Acceso a la Información Pública: UG');
              //no_reply@ugto.mx
              $message->setFrom(array($this->config->item('usuario') => 'Solicitud de Información '.$id_solicitud));
//$message->addTo(trim("sortiza@ugto.mx"));              
$message->addTo(trim($correo_usr));
			  $message->addBcc("uaip21@gmail.com");
		$message->addBcc("uaip@ugto.mx");
              //Add alternative parts with addPart()
              $message->addPart("
                          <b>Su solicitud ".$id_solicitud." está incompleta.</b> <br><br>
                          <b>Observaciones:</b><br>
                          ".$observaciones_incompleta."
                          <br><br>
                         ".$MensajeCorreo->mensaje."
                          <br><br><br>
                           <center>
                           Atentamente <br><br><br>
							<p> Lic. Miriam Contreras Ortiz<br>
							Titular de la Unidad de Acceso a la Información Pública</p>

							<p>Lascuráin de Retana No. 5 Centro, <br>
							Guanajuato, Gto., México C.P. 36000  </p>

							<p>Tel.: (473) 7320006 <br>
							Exts.: 2042, 3102, 2058, 2043</p>

							<p>
							<a href=\"uaip@ugto.mx\">uaip@ugto.mx</a><br>
							<a href=\"m.contrerasortiz@ugto.mx\">m.contrerasortiz@ugto.mx</a>
							</p>
							<p>Horario de Atención: 8:30 am. a 3:30 pm.</p>
                             </center>
              ", 'text/html');
              //@$result = $mailer->send($message);

		$data['content'] = 'vCanalizarGuardar';

		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}
	public function CompletarSolicitud()
	{

		$data['content'] = 'vCompletarSolicitud';
		$data['id_solicitud'] = $this->input->get('id_solicitud');
		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}

	public function CompletarGuardar()
	{
		$this->load->model("Solicitud_model", "solicitud");
		$this->load->library('nohabil');
		$id_solicitud = $this->input->post('id_solicitud');
		$observaciones_completar = $this->input->post('observaciones_completar');
		$estatus = 3;
		$whereRespuesta= "id_solicitud =".$id_solicitud;
		$trae_historia = $this->solicitud->getHistorialRespuesta($whereRespuesta);
                foreach ($trae_historia as $fecha) {
			    if($fecha->id_estatus == 2){
				  $fecha_incompleta = $fecha->fecha;
			    }
			}

		$trae_solicitud = $this->solicitud->getUnaSolicitud($whereRespuesta);
		$fecha_limite_entregar = $trae_solicitud->fecha_limite_entrega;

 			//new DateTime('2009-10-11');

		$fecha_incompleta = date('Y-m-d',strtotime($fecha_incompleta));
		$fecha_limite_entregar = date('Y-m-d',strtotime($fecha_limite_entregar));

		$c = strtotime($fecha_limite_entregar) - strtotime($fecha_incompleta);
		$nuevos = floor($c/86400);

                $limite = $nuevos;
                $fecha_ingreso = $this->nohabil->CalculaFecha(0);
		$fecha_limite = $this->nohabil->CalculaFecha($limite);

		$where = "id_solicitud = ".$id_solicitud;
		$unidad_enlace = 0;
		$data['accion'] = 4;
                
                //$fecha_ingreso_comp = $this->nohabil->CalculaFecha(0);

		$query = $this->solicitud->ActualizarEstatus($id_solicitud,$estatus);
		$query2 = $this->solicitud->GuardarHistorial($id_solicitud,$estatus,$unidad_enlace,$observaciones_completar);
		$NuevaFechaLimite = $this->solicitud->ActualizarFechaLimiteNueva($id_solicitud,$fecha_limite,$fecha_ingreso,$fecha_limite_entregar);

		//$data['unidades_enlace'] =$query;

		$data['content'] = 'vCanalizarGuardar';

		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}
	public function RegresarSolicitud()
	{

		$data['content'] = 'vRegresarSolicitud';
		$data['id_solicitud'] = $this->input->get('id_solicitud');
		$data['id_unidad'] = $this->input->get('id_unidad');
		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}

	public function RegresarSolicitudGuardar()
	{
		$this->load->model("Solicitud_model", "solicitud");
		$id_solicitud = $this->input->post('id_solicitud');
		$unidad_enlace = $this->input->post('id_unidad');
		$observaciones_regresar = "UAIP->".$this->input->post('observaciones_regresar');
		$estatus = 11;
		$whereRespuesta= "id_solicitud =".$id_solicitud;
		$where = "id_solicitud = ".$id_solicitud;
		$data['accion'] = 12;

		$query = $this->solicitud->ActualizarEstatus($id_solicitud,$estatus);
		$query2 = $this->solicitud->GuardarHistorial($id_solicitud,$estatus,$unidad_enlace,$observaciones_regresar);

		$data['content'] = 'vCanalizarGuardar';

		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}

	public function MarcarEntreSolicitud()
	{

		$data['content'] = 'vMarcaEntregaSol';
		$data['id_solicitud'] = $this->input->get('id_solicitud');
		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}
	public function MarcarNoEntreSolicitud()
	{

		$data['content'] = 'vMarcaNoEntregaSol';
		$data['id_solicitud'] = $this->input->get('id_solicitud');
		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}

	public function EntregadaGuardar()
	{
		$this->load->model("Solicitud_model", "solicitud");
		$id_solicitud = $this->input->post('id_solicitud');
		$observaciones_marcae = $this->input->post('observaciones_marcae');
		$estatus = 8;
		$whereRespuesta= "id_estatus = 6 and id_solicitud =".$id_solicitud;
		$unidad_enlace = 0;
		$data['accion'] = 10;
		$observaciones_completar = $observaciones_marcae;
		$trae_solicitud = $this->solicitud->getUnaHistorial($whereRespuesta);
		$fecha_condicionada = $trae_solicitud->fecha;

		$query = $this->solicitud->ActualizarEstatus($id_solicitud,$estatus);
		$query2 = $this->solicitud->GuardarHistorialEntregada($id_solicitud,$estatus,$unidad_enlace,$observaciones_completar,$fecha_condicionada);
		$data['content'] = 'vCanalizarGuardar';
		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}
	public function NoEntregadaGuardar()
	{
		$this->load->model("Solicitud_model", "solicitud");
		$id_solicitud = $this->input->post('id_solicitud');
		$observaciones_marcane = $this->input->post('observaciones_marcane');
		$estatus = 9;
		$whereRespuesta= "id_estatus = 6 and id_solicitud =".$id_solicitud;
		$unidad_enlace = 0;
		$data['accion'] = 11;
		$observaciones_completar = $observaciones_marcane;
		$trae_solicitud = $this->solicitud->getUnaHistorial($whereRespuesta);
		$fecha_condicionada = $trae_solicitud->fecha;

		$query = $this->solicitud->ActualizarEstatus($id_solicitud,$estatus);
		$query2 = $this->solicitud->GuardarHistorialEntregada($id_solicitud,$estatus,$unidad_enlace,$observaciones_completar,$fecha_condicionada);
		$data['content'] = 'vCanalizarGuardar';
		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}
	public function ProrrogaSolicitud()
	{

		$data['content'] = 'vProrrogaSolicitud';
		$data['id_solicitud'] = $this->input->get('id_solicitud');
		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}

	public function ProrrogaGuardar()
	{
		$this->load->library('nohabil');
		$this->load->model("Solicitud_model", "solicitud");
		$id_solicitud = $this->input->post('id_solicitud');
		$observaciones_prorroga = $this->input->post('observaciones_prorroga');
		$unidad_enlace = $this->session->userdata('id_unidad_enlace');
		$estatus = 5;
		$where = "id_solicitud = ".$id_solicitud;
		//$unidad_enlace = 0;
		$data['accion'] = 5;

		$limite = 3;
                $prorroga_time=1;
		$fecha_limite = $this->nohabil->CalculaFecha($limite,$prorroga_time);
		$query = $this->solicitud->InsertarProrroga($id_solicitud,$fecha_limite);

		$query = $this->solicitud->ActualizarEstatus($id_solicitud,$estatus);
		$query2 = $this->solicitud->GuardarHistorial($id_solicitud,$estatus,$unidad_enlace,$observaciones_prorroga);

		$trae_solicitud = $this->solicitud->getUnaSolicitud($where);
		$id_usuario = $trae_solicitud->id_usuario;
		$trae_datos_usuario = $this->solicitud->getDatosUsuario($id_usuario);
		$correo_usr = $trae_datos_usuario->email;
		$MensajeCorreo = $this->solicitud->getMensajeCorreo(4);

		  //Enviar correo electrónico
        $transport = Swift_SmtpTransport::newInstance()
              ->setHost($this->config->item('smtp_host'))
              ->setPort($this->config->item('puerto'))
              ->setEncryption($this->config->item('encriptacion'))
              ->setUsername($this->config->item('usuario'))
              ->setPassword($this->config->item('contraseña'));

              //Create the Mailer using your created Transport
              $mailer = Swift_Mailer::newInstance($transport);

              //Pass it as a parameter when you create the message
              $message = Swift_Message::newInstance();
        //Give the message a subject
        //Or set it after like this
              $message->setSubject('Unidad de Acceso a la Información Pública: UG');
              //no_reply@ugto.mx
              $message->setFrom(array($this->config->item('usuario') => 'Solicitud de Información '.$id_solicitud));
            //$message->addTo(trim("sortiza@ugto.mx"));
            $message->addTo(trim($correo_usr));
            $message->addBcc("uaip21@gmail.com");            
            $message->addBcc("uaip@ugto.mx");
			
              //Add alternative parts with addPart()
              $message->addPart("
                          <b>Su solicitud ".$id_solicitud." se ha puesto en prórroga.</b> <br><br>
                          <b>Observaciones:</b><br>
                          ".$observaciones_prorroga."
                          <br><br>
                          ".$MensajeCorreo->mensaje."
                          <br><br><br>
                           <center>
                           Atentamente <br><br><br>
							<p> Lic. Miriam Contreras Ortiz<br>
							Titular de la Unidad de Acceso a la Información Pública</p>

							<p>Lascuráin de Retana No. 5 Centro, <br>
							Guanajuato, Gto., México C.P. 36000  </p>

							<p>Tel.: (473) 7320006 <br>
							Exts.: 2042, 3102, 2058, 2043</p>

							<p>
							<a href=\"uaip@ugto.mx\">uaip@ugto.mx</a><br>
							<a href=\"m.contrerasortiz@ugto.mx\">m.contrerasortiz@ugto.mx</a>
							</p>
							<p>Horario de Atención: 8:30 am. a 3:30 pm.</p>
                             </center>
              ", 'text/html');
              //@$result = $mailer->send($message);

		$data['content'] = 'vCanalizarGuardar';

		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}
	public function ResponderSolicitud()
	{

		$data['content'] = 'vResponderSolicitud';
		$data['id_solicitud'] = $this->input->get('id_solicitud');
		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}
	public function VerDatosUsuario()
	{
		$this->load->model("Solicitud_model", "solicitud");
		$id_usuario = $this->input->post('id_usuario');
		$mi_tipo_usuario = $this->input->post('tipo_usuario');
		if($id_usuario=='')
			$id_usuario = $this->input->get('id_usuario');
		if($mi_tipo_usuario=='')
			$mi_tipo_usuario = $this->input->get('tipo_usuario');


		if($mi_tipo_usuario == 1){
			$usuario = $this->solicitud->getUsuarioSolicitudDetalle($id_usuario,$mi_tipo_usuario);
			$tipo_usuario = $this->solicitud->getTipoUsuario($mi_tipo_usuario);

		}
		if($mi_tipo_usuario == 2 || $mi_tipo_usuario == 4){
			$usuario = $this->solicitud->getUsuarioSolicitudDetalle($id_usuario,$mi_tipo_usuario);
			$tipo_usuario = $this->solicitud->getTipoUsuario($mi_tipo_usuario);
			$fechas = $this->solicitud->getFechasInhabiles();
			$data['fechas'] =$fechas;

		}
		if($mi_tipo_usuario == 3){
			$fechas = $this->solicitud->getFechasInhabiles();
			$usuario = $this->solicitud->getUsuarioSolicitudDetalle($id_usuario,$mi_tipo_usuario);
			$tipo_usuario = $this->solicitud->getTipoUsuario($mi_tipo_usuario);
			$data['fechas'] =$fechas;
		}

		$idestado=$usuario->estado;
		$tipo_persona=$usuario->id_tipo_persona;
		$mi_usuario=$usuario->usuario;
		$repre=$usuario->id_representante;
		$idpais=$usuario->pais;
		if($idpais){
		$mipais = $this->solicitud->getUnPais($idpais);
		$pais = $mipais->dscpais;
	    }else{
	    $pais = "";
	    }
	    if($idestado){
				$miestado = $this->solicitud->getUnEstado($idestado);
				$estado = $miestado->dscestado;
	    }else{
	    	$estado = "";
	    }
	    if($repre){
				$rep = $this->solicitud->getRepresentante($repre);
				$representante = $rep->nombre_completo." ".$rep->ap_paterno." ".$rep->ap_materno;
				$nombre_rep =$rep->nombre_completo;
				$paterno = $rep->ap_paterno;
				$materno = $rep->ap_materno;
	    }else{
				$representante = "";
				$nombre_rep ="";
				$paterno = "";
				$materno = "";
	    }
	    if($tipo_persona!= ''){
        	$persona = $this->solicitud->getUnTipoPersona($tipo_persona);
        	$mitipo_persona = $persona->dsc_tipo_persona;
        }else{
        	$mitipo_persona = "";
        }

		$query = $this->solicitud->getPais();
		$query2 = $this->solicitud->getEstados();

		$data['mi_pais'] = $query;
		$data['mi_estado'] = $query2;

        $estado = $miestado->dscestado;
        $data['layout'] = 'layout/lytVacio';
		$data['usuario'] =$usuario;
		$data['tipo_usuario'] =$tipo_usuario;
		$data['pais'] = $pais;
		$data['estado'] = $estado;
		$data['tipo_persona'] = $mitipo_persona;
		$data['representante'] = $representante;
				$data['nombre_rep']=$nombre_rep;
				$data['paterno']=$paterno;
				$data['materno']=$materno;
				$data['id_usuario']=$id_usuario;
				$data['usuario_usr']=$mi_usuario;

		$data['content'] = 'vDetalleUsuario';
		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);


	}
	public function ActualizarUsuario()
	{
		$this->load->model("Solicitud_model", "solicitud");

		$nombre = $this->input->post('nombre');
		$id_usuario = $this->input->post('id_usuario');
		$paterno = $this->input->post('paterno');
		$materno = $this->input->post('materno');
		$calle = $this->input->post('calle');
		$colonia = $this->input->post('colonia');
		$ciudad = $this->input->post('ciudad');
		$pais = $this->input->post('pais');
		$estados = $this->input->post('estados');
		$contrasenia = $this->input->post('contrasenia');
		$tipo_persona = $this->input->post('tipo_persona');
		$razon = $this->input->post('razon');
		$curp = $this->input->post('curp');
		$lada = $this->input->post('lada');
		$telefono = $this->input->post('telefono');
		$correo = $this->input->post('correo');
		$ocupacion = $this->input->post('ocupacion');
		$representante = $this->input->post('representante');
		$paterno_representante = $this->input->post('paterno_representante');
		$materno_representante = $this->input->post('materno_representante');

		$actualizar = $this->solicitud->ActualizarUsuario($id_usuario,$nombre,$paterno,$materno,$calle,$colonia,$ciudad,$pais,$estados,$tipo_persona,$razon,$curp,$lada,$telefono,$correo,$ocupacion);
		if($representante!=''){
			//insertaRepresentante($representante_tiene,$nombre_repre,$paterno_repre,$materno_repre,$id_usuario)
			//$actualizarRepre = $this->solicitud->ActualizarRepre($id_usuario,$representante,$paterno_representante,$materno_representante);
		}
		if($contrasenia != ''){
			$actualizarContra = $this->solicitud->actualizarContra($id_usuario,$contrasenia);
		}


		$data['accion'] = 13;
		$data['content'] = 'vCanalizarGuardar';
		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}
	public function AgregarInhabil()
	{
		$this->load->model("Solicitud_model", "solicitud");
		$fecha = $this->input->post('fecha');
		$fechasComprueba = $this->solicitud->getNoHabiles($fecha);

		if($fechasComprueba->id_festivo== ''){
			$insertar = $this->solicitud->insertFechasInhabiles($fecha);
	    }
		$fechas = $this->solicitud->getFechasInhabiles();
		$data['fechas'] =$fechas;

		$data['layout'] = 'layout/lytVacio';
		$data['content'] = 'vInhabiles';
		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);
	}
	public function CargaEstados(){
		$this->load->model("Solicitud_model", "solicitud");
		$id_pais = $this->input->post('id_pais');
		$query2 = $this->solicitud->getEstado($id_pais);

		$data['estado'] = $query2;
		$data['layout'] = 'layout/lytVacio';
		$data['content'] = 'vEstados';
		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);
	}

	public function ResponderGuardar()
	{
		$this->load->model("Solicitud_model", "solicitud");
		$id_solicitud = $this->input->post('id_solicitud');
		$observaciones_responder = "UE->".$this->input->post('observaciones_responder');
		$unidad_enlace = $this->session->userdata('id_unidad_enlace');
		$estatus = 11;
		$where = "id_solicitud = ".$id_solicitud;
		//$unidad_enlace = 0;
		$data['accion'] = 6;

		$query = $this->solicitud->ActualizarEstatus($id_solicitud,$estatus);
		$query2 = $this->solicitud->GuardarHistorial($id_solicitud,$estatus,$unidad_enlace,$observaciones_responder);

		//$data['unidades_enlace'] =$query;

		$data['content'] = 'vCanalizarGuardar';

		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}
	public function LiberarSolicitud()
	{
		$this->load->model("Solicitud_model", "solicitud");
		$data['content'] = 'vLiberarSolicitud';
		$data['id_solicitud'] = $this->input->get('id_solicitud');
		$temas = $this->solicitud->getTemas();
		$tipo_info = $this->solicitud->getTipoInfo();

		//$data['tags'] =json_encode($tags);
		$data['temas'] =$temas;
		$data['tipo_info'] =$tipo_info;		

		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}
	public function obtener_tags()
	{
		/*$this->load->model("Solicitud_model", "solicitud");
		$term = $this->input->post('term');
		$tags = $this->solicitud->getTags();
		print($term);
		echo json_encode($tags);*/


		$this->load->model("Solicitud_model", "solicitud");
		if (isset($_GET['term'])){
			$q = strtolower($_GET['term']);
			$this->solicitud->getTags($q);
		}
    }



	public function LiberarGuardar()
	{
		$this->load->model("Solicitud_model", "solicitud");
		$id_solicitud = $this->input->post('id_solicitud');
		$observaciones_liberar = $this->input->post('observaciones_liberar');
		$tema = $this->input->post('temas');
		$tipo_info = $this->input->post('tipo_info');
		$etiquetas = $this->input->post('etiquetas');
		$mis_etiquetas = json_decode($etiquetas);

		$unidad_enlace = 0;
		$estatus = 8;
		$where = "id_solicitud = ".$id_solicitud;
		//$unidad_enlace = 0;
		$data['accion'] = 7;
		$fecha_limite = 0;
		foreach ($mis_etiquetas as $etiqueta) {
			//$mi_etiqueta = explode(" X", $etiqueta);
			//print($mi_etiqueta[0]);

			$buscar = $this->solicitud->buscarTag($etiqueta);
			if($buscar){
				//print($buscar->dsc_tag."existe");
			    $id_tag = $buscar->id_tag;
			}
			else{
				$guardartag = $this->solicitud->GuardarTag($etiqueta);
				$id_tag = $guardartag->id_tag;
				//print($guardartag->id_tag."-> ".$guardartag->dsc_tag);
			}

			$etiqueta_sol = $this->solicitud->AgregarEtiquetaSolicitud($id_solicitud,$id_tag);
		}
		$query = $this->solicitud->LiberarSolicitud($id_solicitud,$estatus,$observaciones_liberar,$tema,$tipo_info,$fecha_limite);
		$query2 = $this->solicitud->GuardarHistorial($id_solicitud,$estatus,$unidad_enlace,$observaciones_liberar);

		$data['content'] = 'vCanalizarGuardar';

		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

		$trae_solicitud = $this->solicitud->getUnaSolicitud($where);
		$id_usuario = $trae_solicitud->id_usuario;
		$trae_datos_usuario = $this->solicitud->getDatosUsuario($id_usuario);
		$correo_usr = $trae_datos_usuario->email;
		$whereFinal = "id_solicitud = ".$id_solicitud." and id_tipo_adjunto = 1";
		$archivosRespuesta = $this->solicitud->getArchivosRespuesta($whereFinal);

		 //Enviar correo electrónico
              $transport = Swift_SmtpTransport::newInstance()
              ->setHost($this->config->item('smtp_host'))
              ->setPort($this->config->item('puerto'))
              ->setEncryption($this->config->item('encriptacion'))
              ->setUsername($this->config->item('usuario'))
              ->setPassword($this->config->item('contraseña'));

              //Create the Mailer using your created Transport
              $mailer = Swift_Mailer::newInstance($transport);

              //Pass it as a parameter when you create the message
              $message = Swift_Message::newInstance();
        //Give the message a subject
        //Or set it after like this
              $message->setSubject('Unidad de Acceso a la Información Pública: UG');
              //no_reply@ugto.mx
              $message->setFrom(array($this->config->item('usuario') => 'Solicitud de Información '.$id_solicitud));
              //$message->addTo(trim("sortiza@ugto.mx"));
              $message->addTo(trim($correo_usr));
              $message->addBcc("uaip21@gmail.com");
              $message->addBcc("uaip@ugto.mx");
              //Add alternative parts with addPart()
              $message->addPart("
                          <b>Respuesta Solicitud ".$id_solicitud.".</b> <br><br>
                          ".$observaciones_liberar."
                          <br><br>
                           <center>
                           Atentamente <br><br><br>
							<p> Lic. Miriam Contreras Ortiz<br>
							Titular de la Unidad de Acceso a la Información Pública</p>

							<p>Lascuráin de Retana No. 5 Centro, <br>
							Guanajuato, Gto., México C.P. 36000  </p>

							<p>Tel.: (473) 7320006 <br>
							Exts.: 2042, 3102, 2058, 2043</p>

							<p>
							<a href=\"uaip@ugto.mx\">uaip@ugto.mx</a><br>
							<a href=\"m.contrerasortiz@ugto.mx\">m.contrerasortiz@ugto.mx</a>
							</p>
							<p>Horario de Atención: 8:30 am. a 3:30 pm.</p>
                             </center>
              ", 'text/html');
            // Optionally add any attachments
            foreach ($archivosRespuesta as $archivo) {
                if($archivo->nombre_adjunto!= '')
                    $message->attach(Swift_Attachment::fromPath("{$_SERVER['DOCUMENT_ROOT']}/{$this->config->item('dirPrincipal')}/uploads/RespuestaFolio".$id_solicitud."/".$archivo->nombre_adjunto));
            }
            
            //@$result = $mailer->send($message);
	}

	public function GenPublicaSolicitud()
	{
                $this->load->model("Solicitud_model", "solicitud");
		$data['content'] = 'vGenPubSolicitud';
		$data['id_solicitud'] = $this->input->get('id_solicitud');
		$where = "id_solicitud =".$this->input->get('id_solicitud');
		$datos_sol = $this->solicitud->getUnaSolicitud($where);
                $respuesta = $this->solicitud->getUnidadesRespuesta($where);
		$data['solicitud'] =$datos_sol;
                $data['respuesta'] =$respuesta;


		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);
	}

        public function ManArchivo()
	{
               $this->load->model("Solicitud_model", "solicitud");
		$data['content'] = 'vEdArchSolicitud';
		$data['id_solicitud'] = $this->input->get('id_solicitud');
		$where = "id_solicitud =".$this->input->get('id_solicitud');
		$datos_sol = $this->solicitud->getUnaSolicitud($where);
                $respuesta = $this->solicitud->getUnidadesRespuesta($where);
		$data['solicitud'] =$datos_sol;
                $data['respuesta'] =$respuesta;


		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);
	}
        
	public function GenPubGuardar()
	{
		$this->load->model("Solicitud_model", "solicitud");
		$id_solicitud = $this->input->post('id_solicitud');
		$contenido_publico = $this->input->post('contenido_publico');
		$respuesta_publica = $this->input->post('respuesta_publica');


		$where = "id_solicitud = ".$id_solicitud;
		//$unidad_enlace = 0;
		$data['accion'] = 8;

		$query = $this->solicitud->GenerarPubSolicitud($id_solicitud,$contenido_publico,$respuesta_publica);


		//$data['unidades_enlace'] =$query;

		$data['content'] = 'vCanalizarGuardar';

		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}
	public function AgregarEtiqueta()
	{
		$this->load->model("Solicitud_model", "solicitud");
		$id_solicitud = $this->input->post('id_solicitud');
		$etiqueta = $this->input->post('etiqueta');
        $agrega_etiqueta = $this->solicitud->AgregarEtiqueta($etiqueta);
        $tag = $agrega_etiqueta->id_tag;

        //$addetiqueta_sol = $this->solicitud->AgregarEtiquetaSolicitud($id_solicitud,$tag);
		//$obtieneEtiqueta = $this->solicitud->getEtiquetaSol($id_solicitud);
        $data['etiquetas_solicitud'] =$addetiqueta_sol;
		$data['content'] = 'vAgregarEtiquetas';
		$data['layout'] = 'layout/lytVacio';
		$data['scripts'] = 'msolicitudes3';
		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);


	}
	public function CondicionarSolicitud()
	{
		$this->load->model("Solicitud_model", "solicitud");
		$data['content'] = 'vCondicionarSolicitud';
		$data['id_solicitud'] = $this->input->get('id_solicitud');
		$temas = $this->solicitud->getTemas();
		$tipo_info = $this->solicitud->getTipoInfo();
		$tipo_medio = $this->solicitud->getTipoMedio();
		$data['temas'] =$temas;
		$data['tipo_info'] =$tipo_info;
		$data['tipo_medio'] =$tipo_medio;

		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}

	public function CondicionarGuardar()
	{
		$this->load->model("Solicitud_model", "solicitud");
		$this->load->library('nohabil');
		$id_solicitud = $this->input->post('id_solicitud');
		$observaciones_condicionar = $this->input->post('observaciones_condicionar');
		$tema = $this->input->post('temas');
		$tipo_info = $this->input->post('tipo_info');
		$costo_envio = $this->input->post('costo_envio');
		$costo_total = $this->input->post('costo_total');
		$tipo_medio = $this->input->post('tipo_medio');
		$no_copias = $this->input->post('no_copias');
		$etiquetas = $this->input->post('etiquetas');
		$mis_etiquetas = json_decode($etiquetas);

		foreach ($mis_etiquetas as $etiqueta) {
			//$mi_etiqueta = explode(" X", $etiqueta);
			//print($mi_etiqueta[0]);

			$buscar = $this->solicitud->buscarTag($etiqueta);
			if($buscar){
				//print($buscar->dsc_tag."existe");
			    $id_tag = $buscar->id_tag;
			}
			else{
				$guardartag = $this->solicitud->GuardarTag($etiqueta);
				$id_tag = $guardartag->id_tag;
				//print($guardartag->id_tag."-> ".$guardartag->dsc_tag);
			}

			$etiqueta_sol = $this->solicitud->AgregarEtiquetaSolicitud($id_solicitud,$id_tag);
		}


		$unidad_enlace = 0;
		$limite = 10;
		$estatus = 6;
		$where = "id_solicitud = ".$id_solicitud;
		//$unidad_enlace = 0;
		$fecha_limite = $this->nohabil->CalculaFecha($limite);
		$datos_sol = $this->solicitud->getUnaSolicitud($where);
		$id_medio_respuesta = $datos_sol->id_medio_respuesta;
		$medio_respuesta = $this->solicitud->UpdateMedioRespuesta($id_medio_respuesta,$costo_envio,$costo_total,$tipo_medio,$fecha_limite,$no_copias);
		$query = $this->solicitud->LiberarSolicitud($id_solicitud,$estatus,$observaciones_condicionar,$tema,$tipo_info,$fecha_limite);
		$query2 = $this->solicitud->GuardarHistorial($id_solicitud,$estatus,$unidad_enlace,$observaciones_condicionar);

		$trae_solicitud = $this->solicitud->getUnaSolicitud($where);
		$id_usuario = $trae_solicitud->id_usuario;
		$trae_datos_usuario = $this->solicitud->getDatosUsuario($id_usuario);
		$correo_usr = $trae_datos_usuario->email;
		$MensajeCorreo = $this->solicitud->getMensajeCorreo(5);

		 //Enviar correo electrónico
        $transport = Swift_SmtpTransport::newInstance()
              ->setHost($this->config->item('smtp_host'))
              ->setPort($this->config->item('puerto'))
              ->setEncryption($this->config->item('encriptacion'))
              ->setUsername($this->config->item('usuario'))
              ->setPassword($this->config->item('contraseña'));

              //Create the Mailer using your created Transport
              $mailer = Swift_Mailer::newInstance($transport);

              //Pass it as a parameter when you create the message
              $message = Swift_Message::newInstance();
        //Give the message a subject
        //Or set it after like this
              $message->setSubject('Unidad de Acceso a la Información Pública: UG');
              //no_reply@ugto.mx
              $message->setFrom(array($this->config->item('usuario') => 'Solicitud de Información '.$id_solicitud));
              //$message->addTo(trim("sortiza@ugto.mx"));
            $message->addTo(trim($correo_usr));
			  $message->addBcc("uaip21@gmail.com");
			  $message->addBcc("uaip@ugto.mx");
              //Add alternative parts with addPart()
              $message->addPart("
                          <b>Requerimiento de pago de soporte material para la Solicitud ".$id_solicitud.".</b> <br><br>
                          <b>Observaciones:</b><br>
                          ".$observaciones_condicionar."<br>
                          <b>No. Copias:</b> ".$no_copias."<br>
							<b>Costo Envío:</b>".$costo_envio."<br>
							<b>Total a pagar: </b>".$costo_total."<br>

                          <b></b>
                          <br><br>
							".$MensajeCorreo->mensaje."
                          <br><br><br>
                           <center>
                           Atentamente <br><br><br>
							<p> Lic. Miriam Contreras Ortiz<br>
							Titular de la Unidad de Acceso a la Información Pública</p>

							<p>Lascuráin de Retana No. 5 Centro, <br>
							Guanajuato, Gto., México C.P. 36000  </p>

							<p>Tel.: (473) 7320006 <br>
							Exts.: 2042, 3102, 2058, 2043</p>

							<p>
							<a href=\"uaip@ugto.mx\">uaip@ugto.mx</a><br>
							<a href=\"m.contrerasortiz@ugto.mx\">m.contrerasortiz@ugto.mx</a>
							</p>
							<p>Horario de Atención: 8:30 am. a 3:30 pm.</p>
                             </center>
              ", 'text/html');
              //@$result = $mailer->send($message);

		//$data['unidades_enlace'] =$query;

		$data['content'] = 'vCanalizarGuardar';
		$data['accion'] = 9;
		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}


	public function DataSolicitudes()
	{
		$this->load->model("Solicitud_model", "solicitud");

		$page = isset($_POST['page'])?$_POST['page']:1;
		$limit = isset($_POST['rows'])?$_POST['rows']:10;
		$sidx = isset($_POST['sidx'])?$_POST['sidx']:'name';
		$sord = isset($_POST['sord'])?$_POST['sord']:'';
		$start = $limit*$page - $limit;
                
                $id_unidad_enlace=$this->input->get('id_unidad_enlace');
                $fecha_ingreso_inicio=$this->input->get('fecha_ingreso_inicio');
                $fecha_ingreso_fin=$this->input->get('fecha_ingreso_fin');
                
		//echo $start."-".$limit."-".$page;
		$start = ($start<0)?0:$start;
               
		$id_estatus = $this->input->get('id_estatus');

		if ($this->session->userdata('id_tipo_usuario')== 1){
			if($id_estatus == ''){
			 $where = "id_usuario = ".$this->session->userdata('id_usuario');
		     }else{
		     $where = "id_usuario = ".$this->session->userdata('id_usuario')." and id_estatus = ".$id_estatus;
		     }
		 }
	    if($this->session->userdata('id_tipo_usuario')== 2){
	    	if($id_estatus == ''){
	    	  $where = "id_unidad_enlace = ".$this->session->userdata('id_unidad_enlace');
	    	}else{
	    		$where = "id_unidad_enlace = ".$this->session->userdata('id_unidad_enlace')." and id_estatus = ".$id_estatus;
	    	}
	    }
	    if($this->session->userdata('id_tipo_usuario')== 3){
	    	if($id_estatus == ''){
	    		$where = "id_estatus in(1,3)";
	    	}else{
	    		$where = "id_estatus = ".$id_estatus;
	    	}
	    }
	    if($this->session->userdata('id_tipo_usuario')== 4){
	    	if($id_estatus == ''){
	    		$where = "id_unidad_enlace = ".$this->session->userdata('id_unidad_enlace');
	    	}else{
	    		$where = "id_unidad_enlace = ".$this->session->userdata('id_unidad_enlace')." and id_estatus = ".$id_estatus;
	    	}
	    }

            
            if($this->session->userdata('id_tipo_usuario')==6){
	           
                if($id_unidad_enlace!='')
                    $where = "id_unidad_enlace =".$id_unidad_enlace;
                else
                    $where = "id_unidad_enlace in (".$this->session->userdata('id_unidad_enlaces').")";     	
                
                if($fecha_ingreso_inicio!='' and $fecha_ingreso_fin!='')
                {
                    $where=$where." and (fecha_ingreso>='".$fecha_ingreso_inicio." 00:00:00' and fecha_ingreso<='".$fecha_ingreso_fin." 23:59:59')";
                }	 
	    }

		$searchField = isset($_POST['searchField']) ? $_POST['searchField'] : false;

		$searchOper = isset($_POST['searchOper']) ? $_POST['searchOper']: false;
		$searchString = isset($_POST['searchString']) ? $_POST['searchString'] : false;

    if ($_POST['_search'] == 'true') {
        $ops = array(
        'eq'=>'=',
        'ne'=>'<>',
        'lt'=>'<',
        'le'=>'<=',
        'gt'=>'>',
        'ge'=>'>=',
        'bw'=>'LIKE',
        'bn'=>'NOT LIKE',
        'in'=>'LIKE',
        'ni'=>'NOT LIKE',
        'ew'=>'LIKE',
        'en'=>'NOT LIKE',
        'cn'=>'LIKE',
        'nc'=>'NOT LIKE'
        );
    }

            switch($_GET['columna'])
        {
        case "id_solicitud":
        {
            $where=$where." and id_solicitud=".$_GET['consulta'];
            break;
        }
        case "contenido":
        {
            $where=$where." and contenido like '%".($_GET['consulta'])."%'";
            break;
        }
        
        foreach ($ops as $key=>$value){
            if ($searchOper==$key) {
                $ops = $value;
            }
        }
        if($searchOper == 'eq' ) $searchString = $searchString;
        if($searchOper == 'bw' || $searchOper == 'bn') $searchString .= '%';
        if($searchOper == 'ew' || $searchOper == 'en' ) $searchString = '%'.$searchString;
        if($searchOper == 'cn' || $searchOper == 'nc' || $searchOper == 'in' || $searchOper == 'ni') $searchString = '%'.$searchString.'%';

        $where = "$searchField $ops '$searchString' ";

    }

    if(!$sidx)
        $sidx =1;
    if($this->session->userdata('id_tipo_usuario') == 2 || $this->session->userdata('id_tipo_usuario') == 4 || $this->session->userdata('id_tipo_usuario') == 6){
        $this->db->where($where);
        $count = $this->db->count_all_results('vw_uaip_info_multi_solicitudes');
    }else{
    	$this->db->where($where);
        $count = $this->db->count_all_results('vw_uaip_info_solicitudes');
    }
    if( $count > 0 ) {
        $total_pages = ceil($count/$limit);
    } else {
        $total_pages = 0;
    }

    if ($page > $total_pages)
        $page=$total_pages;
    if($this->session->userdata('id_tipo_usuario') == 2 || $this->session->userdata('id_tipo_usuario') == 4 || $this->session->userdata('id_tipo_usuario') == 6){
    	$query = $this->solicitud->getAllDataMultiple($start,$limit,$sidx,$sord,$where,$page);
    }else{
    	$query = $this->solicitud->getAllData($start,$limit,$sidx,$sord,$where,$page);
    }

    $response->page = $page;
    $response->total = $total_pages;
    $response->records = $count;
    $i=0;
    foreach($query as $row) {        
        $response->rows[$i]['id']=$row->id_solicitud;
        $response->rows[$i]['cell']=array($row->id_solicitud,$row->contenido,$row->dsc_tipo_medio,date('d/m/Y',strtotime($row->fecha_ingreso)),date('d/m/Y',strtotime($row->fecha_limite_entrega)),date('d/m/Y',strtotime($row->fecha_limite_completar)),$row->dsc_estatus,$row->id_estatus,$row->fecha_limite_prorroga,$row->nombre_unidad_enlace);
        $i++;
    }

    //$trans_responce = iconv('UTF-8', 'ASCII//TRANSLIT', $response);
    echo json_encode($response);

	}
        
    public function DataSolicitudesPublico()
    {

		$this->load->model("Solicitud_model", "solicitud");

		$page = isset($_POST['page'])?$_POST['page']:1;
		$limit = isset($_POST['rows'])?$_POST['rows']:10;
		$sidx = isset($_POST['sidx'])?$_POST['sidx']:'name';
		$sord = isset($_POST['sord'])?$_POST['sord']:'';
		$start = $limit*$page - $limit;
		//echo $start."-".$limit."-".$page;
		$start = ($start<0)?0:$start;
		$mis_solicitudes = $this->input->get('mis_solicitudes');
		if($mis_solicitudes != ''){
			$where = "id_solicitud in(".$mis_solicitudes.")";
	    }else{
	    	$where = "id_estatus in(8,9) and contenido_publico is not null";
	    }
		$searchField = isset($_POST['searchField']) ? $_POST['searchField'] : false;

		$searchOper = isset($_POST['searchOper']) ? $_POST['searchOper']: false;
		$searchString = isset($_POST['searchString']) ? $_POST['searchString'] : false;

//    if ($_POST['_search'] == 'true') {
//        $ops = array(
//        'eq'=>'=',
//        'ne'=>'<>',
//        'lt'=>'<',
//        'le'=>'<=',
//        'gt'=>'>',
//        'ge'=>'>=',
//        'bw'=>'LIKE',
//        'bn'=>'NOT LIKE',
//        'in'=>'LIKE',
//        'ni'=>'NOT LIKE',
//        'ew'=>'LIKE',
//        'en'=>'NOT LIKE',
//        'cn'=>'LIKE',
//        'nc'=>'NOT LIKE'
//        );
//        foreach ($ops as $key=>$value){
//            if ($searchOper==$key) {
//                $ops = $value;
//            }
//        }
//        if($searchOper == 'eq' ) $searchString = $searchString;
//        if($searchOper == 'bw' || $searchOper == 'bn') $searchString .= '%';
//        if($searchOper == 'ew' || $searchOper == 'en' ) $searchString = '%'.$searchString;
//        if($searchOper == 'cn' || $searchOper == 'nc' || $searchOper == 'in' || $searchOper == 'ni') $searchString = '%'.$searchString.'%';
//
//        $where = "$searchField $ops '$searchString' ";
//
//    }
    
    switch($_GET['columna'])
    {
        case "id_solicitud":
        {
            $where=$where." and id_solicitud=".$_GET['consulta'];
            break;
        }
        case "contenido":
        {
            $where=$where." and contenido like '%".($_GET['consulta'])."%'";
            break;
        }
    }

    if(!$sidx)
        $sidx =1;
    	$this->db->where($where);
        $count = $this->db->count_all_results('vw_uaip_info_solicitudes');
    if( $count > 0 ) {
        $total_pages = ceil($count/$limit);
    } else {
        $total_pages = 0;
    }

    if ($page > $total_pages)
        $page=$total_pages;
    
    $query = $this->solicitud->getAllData($start,$limit,$sidx,$sord,$where,$page);

    $response->page = $page;
    $response->total = $total_pages;
    $response->records = $count;
    $i=0;
    foreach($query as $row) {
        $response->rows[$i]['id']=$row->id_solicitud;
        $response->rows[$i]['cell']=array($row->id_solicitud,$row->contenido,date('d/m/Y',strtotime($row->fecha_ingreso)),$row->respuesta_publica);
        $i++;
    }

    //$trans_responce = iconv('UTF-8', 'ASCII//TRANSLIT', $response);
    echo json_encode($response);
    }
        
	public function ListadoOrientadas()
	{
		if (!$this->session->userdata('id_usuario')){
           redirect('inicio/cerrar');
        }else{
		   $data['scripts'] =array('msolicitudesOrientadas');
		   $data['content'] = 'vListadoOrientadas';
		   $data = array_merge($this->dataDefault, $data);
		   $this->load->view($data['layout'], $data);
	    }
	}
	public function DataSolicitudesOrientadas()
	{

		$this->load->model("Solicitud_model", "solicitud");

		$page = isset($_POST['page'])?$_POST['page']:1;
		$limit = isset($_POST['rows'])?$_POST['rows']:10;
		$sidx = isset($_POST['sidx'])?$_POST['sidx']:'id_orientada';
		$sord = isset($_POST['sord'])?$_POST['sord']:'';
		$start = $limit*$page - $limit;
		//echo $start."-".$limit."-".$page;
		$start = ($start<0)?0:$start;
		$where = "1 = 1";		

		$searchField = isset($_POST['searchField']) ? $_POST['searchField'] : false;

		$searchOper = isset($_POST['searchOper']) ? $_POST['searchOper']: false;
		$searchString = isset($_POST['searchString']) ? $_POST['searchString'] : false;

    if ($_POST['_search'] == 'true') {
        $ops = array(
        'eq'=>'=',
        'ne'=>'<>',
        'lt'=>'<',
        'le'=>'<=',
        'gt'=>'>',
        'ge'=>'>=',
        'bw'=>'LIKE',
        'bn'=>'NOT LIKE',
        'in'=>'LIKE',
        'ni'=>'NOT LIKE',
        'ew'=>'LIKE',
        'en'=>'NOT LIKE',
        'cn'=>'LIKE',
        'nc'=>'NOT LIKE'
        );
        foreach ($ops as $key=>$value){
            if ($searchOper==$key) {
                $ops = $value;
            }
        }
        if($searchOper == 'eq' ) $searchString = $searchString;
        if($searchOper == 'bw' || $searchOper == 'bn') $searchString .= '%';
        if($searchOper == 'ew' || $searchOper == 'en' ) $searchString = '%'.$searchString;
        if($searchOper == 'cn' || $searchOper == 'nc' || $searchOper == 'in' || $searchOper == 'ni') $searchString = '%'.$searchString.'%';

        $where = "$searchField $ops '$searchString' ";

    }

    if(!$sidx)
        $sidx =1;

    	$this->db->where($where);		
        $count = $this->db->count_all_results('uaip_sol_orientadas');
		
    if( $count > 0 ) {
        $total_pages = ceil($count/$limit);
    } else {
        $total_pages = 0;
    }

    if ($page > $total_pages)
        $page=$total_pages;

    	$query = $this->solicitud->getOrientadas($start,$limit,$sidx,$sord,$where,$page);
		
    $response->page = $page;
    $response->total = $total_pages;
    $response->records = $count;
    $i=0;
    foreach($query as $row) {
    	if($row->fecha_respuesta){
    		$f_respuesta=date('d/m/Y',strtotime($row->fecha_respuesta));	
    	}else{
    		$f_respuesta='';
    	}		
    	
        $response->rows[$i]['id']=$row->id_orientada;
        $response->rows[$i]['cell']=array($row->folio,$row->contenido,date('d/m/Y',strtotime($row->fecha_ingreso)),$row->solicitante,$row->correo,$row->area_responde,$row->respuesta,$f_respuesta);
        $i++;
    }

    //$trans_responce = iconv('UTF-8', 'ASCII//TRANSLIT', $response);
    echo json_encode($response);

	}
	public function NuevaSolicitudOrientada()
	{
		//$this->load->model("Solicitud_model", "solicitud");		
		//$data['scripts'] =array('msolicitudes');
		
		$data['content'] = 'vFormNuevaOrientada';
		//$data['scripts'] =array('msolicitudesOrientadas');
		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);
	}
	public function AgregarOrientada()
	{
		 $this->load->model("Solicitud_model", "solicitud");

		$nombre = $this->input->post('nombre');
		$correo = $this->input->post('correo');
		$fecha_ingreso = $this->input->post('fecha_ingreso');
		$contenido = $this->input->post('contenido');
		$fecha_respuesta = $this->input->post('fecha_respuesta');
		$area_responde = $this->input->post('area_responde');
		$respuesta = $this->input->post('respuesta');
		$ano = date('Y');
		$ultima_orientada = $this->solicitud->getUltimaOrientada($ano);	

		if(!$ultima_orientada){
			$consecutivo = 1;
			$folio= $consecutivo."-".$ano;
			
		}else{
			$consecutivo = $ultima_orientada->consecutivo+1;
			$folio = $folio= $consecutivo."-".$ano;
		}
		if($fecha_respuesta!=''){			
			$orientada = array(
                    'folio' => $folio,
                    'contenido' => $contenido,
                    'correo' => $correo,
                    'area_responde' => $area_responde,                    
                    'fecha_ingreso' => date('Y-d-m H:i:s',strtotime($fecha_ingreso)),
                    'fecha_respuesta' => date('Y-d-m H:i:s',strtotime($fecha_respuesta)),
                    'solicitante' => $nombre,
                    'respuesta' => $respuesta,
                    'ano' => $ano,
                    'consecutivo' => $consecutivo
                 );
		}else{			 $orientada = array(
                    'folio' => $folio,
                    'contenido' => $contenido,
                    'correo' => $correo,
                    'area_responde' => $area_responde,                    
                    'fecha_ingreso' => date('Y-d-m H:i:s',strtotime($fecha_ingreso)),                    
                    'solicitante' => $nombre,
                    'respuesta' => $respuesta,
                    'ano' => $ano,
                    'consecutivo' => $consecutivo
                 );
		}
		 //die(print_r($orientada));
             $nueva_orientada = $this->solicitud->insertarOrientada($orientada);
		//die(print($folio));
		$data['scripts'] =array('msolicitudesOrientadas');
		$data['exito'] =1;
		$data['layout'] = 'layout/lytVerificando';
		$data['content'] = 'vNuevaSolicitudOrientada';
		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}
	public function EditaSolicitudOrientada()
	{
		//$this->load->model("Solicitud_model", "solicitud");		
		//$data['scripts'] =array('msolicitudes');
		$id = $this->input->get('id');
		$this->load->model("Solicitud_model", "solicitud");
		$trae_orientada = $this->solicitud->getOrientada($id);	
		//die(print($id));
		$data['orientada'] = $trae_orientada;
		$data['content'] = 'vFormEditaOrientada';
		//$data['scripts'] =array('msolicitudesOrientadas');
		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);
	}
	public function EditaOrientada()
	{
		$this->load->model("Solicitud_model", "solicitud");

		$id_orientada = $this->input->post('id_orientada');
		$nombre = $this->input->post('nombre');
		$correo = $this->input->post('correo');
		$fecha_ingreso = $this->input->post('fecha_ingreso');
		$contenido = $this->input->post('contenido');
		$fecha_respuesta = $this->input->post('fecha_respuesta');
		$area_responde = $this->input->post('area_responde');
		$respuesta = $this->input->post('respuesta');		
				
			$orientada = array(                    
                    'contenido' => $contenido,
                    'correo' => $correo,
                    'area_responde' => $area_responde,
                    'respuesta' => $respuesta,
                    'fecha_ingreso' => date('Y-d-m H:i:s',strtotime($fecha_ingreso)),
                    'fecha_respuesta' => date('Y-d-m H:i:s',strtotime($fecha_respuesta)),
                    'solicitante' => $nombre                   
                 );		
		 
             $nueva_orientada = $this->solicitud->actualizaOrientada($orientada,$id_orientada);
		//die(print($folio));
		$data['scripts'] =array('msolicitudesOrientadas');
		$data['exito'] =1;
		$data['layout'] = 'layout/lytVerificando';
		$data['content'] = 'vNuevaSolicitudOrientada';
		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}
	public function AdministradorUaip()
	{
		if (!$this->session->userdata('id_usuario')){
           redirect('inicio/cerrar');
        }else{
           $this->load->model("Solicitud_model", "solicitud");
           $ue = $this->solicitud->getUnidadesEnlaceTodo();
           $vacaciones = $this->solicitud->getMensajesVacaciones();
		   
		   $data['canalizar'] = $this->solicitud->getMensajeCorreo(1);
           $data['cancelar'] = $this->solicitud->getMensajeCorreo(2);
           $data['incompleta'] = $this->solicitud->getMensajeCorreo(3);
           $data['prorroga'] = $this->solicitud->getMensajeCorreo(4);
           $data['condicionar'] = $this->solicitud->getMensajeCorreo(5);
		   
           $data['vacaciones'] = $vacaciones;
           $data['unidades'] = $ue;
		   $data['scripts'] =array('msolicitudes3');
		   $data['content'] = 'vAdministradorIndex';
		   $data = array_merge($this->dataDefault, $data);
		   $this->load->view($data['layout'], $data);
	    }
	}
        
        
        
	public function RecargaUnidades()
	{
            
	if (!$this->session->userdata('id_usuario')){
           redirect('inicio/cerrar');
        }else{
           $this->load->model("Solicitud_model", "solicitud");
           
           $id_solicitud = $this->input->post('id_solicitud');
           
           if(trim($id_solicitud)!='')
                $ue = $this->solicitud->getUnidadesEnlaceEsp($id_solicitud);
           else            
                $ue = $this->solicitud->getUnidadesEnlaceTodo();
           
           
           $data['unidades'] = $ue;
		   //$data['scripts'] =array('msolicitudes');
		   $data['layout'] = 'layout/lytVerificando';
		   $data['content'] = 'vRecargaUnidades';
		   $data = array_merge($this->dataDefault, $data);
		   $this->load->view($data['layout'], $data);
	    }
	}
        
        public function NuevaUnidad()
	{
        $this->load->model("Solicitud_model", "solicitud");
           $id_unidad = $this->input->post('id_unidad');
           $data['layout'] = 'layout/lytVerificando';
		   //$data['scripts'] =array('msolicitudes');
		   $data['content'] = 'vNuevaUnidadEnlace';
		   $data = array_merge($this->dataDefault, $data);
		   $this->load->view($data['layout'], $data);
  
	}
        
	public function DetalleUnidad()
	{
		if (!$this->session->userdata('id_usuario')){
           redirect('inicio/cerrar');
        }else{
           $this->load->model("Solicitud_model", "solicitud");
           $id_unidad = $this->input->post('id_unidad');
           $ue = $this->solicitud->getUnidadEnlaceUna($id_unidad);
           $data['unidad'] = $ue;
           $data['layout'] = 'layout/lytVerificando';
		   //$data['scripts'] =array('msolicitudes');
		   $data['content'] = 'vUnidadEnlaceEdita';
		   $data = array_merge($this->dataDefault, $data);
		   $this->load->view($data['layout'], $data);
	    }

	}
	public function CambiarUnidadEnlace()
	{
		   $this->load->model("Solicitud_model", "solicitud");     
           $unidad_enlace = array(                    
	            'sigla' => $this->input->post('sigla'),  
	            'nombre' => $this->input->post('nombre'),                                        
	            'clave_tel' => $this->input->post('clave'),
	            'numero_tel' => $this->input->post('telefono'),
	            'ext' =>$this->input->post('ext'),
	            'fax' => $this->input->post('text'),
	            'activo' => $this->input->post('activo')                    
             );
           //die(print_r($unidad_enlace));
            $unidad_de_enlace = $this->solicitud->ActualizarUnidad($unidad_enlace,$this->input->post('id_unidad_enlace'));
           
           $data['unidad'] = $ue;
           $data['layout'] = 'layout/lytVerificando';
		   //$data['scripts'] =array('msolicitudes');
		   $data['content'] = 'vUnidadEditaSuccess';
		   $data = array_merge($this->dataDefault, $data);
		   $this->load->view($data['layout'], $data);

	}
	public function DetalleUnidadEnlaces()
	{
		if (!$this->session->userdata('id_usuario')){
           redirect('inicio/cerrar');
        }else{
           $this->load->model("Solicitud_model", "solicitud");
           $id_unidad = $this->input->post('id_unidad');
           $tipo = $this->input->post('tipo');
		   $data['tipo']=$this->input->post('tipo');
           $data['id_unidad']=$this->input->post('id_unidad');
           $ue = $this->solicitud->getEnlacesUAIP($id_unidad,$tipo);
           //die(print_r($ue));
           $data['enlaces'] = $ue;
           $data['layout'] = 'layout/lytVerificando';
		   //$data['scripts'] =array('msolicitudes');
		   $data['content'] = 'vUnidadEnlaces';
		   $data = array_merge($this->dataDefault, $data);
		   $this->load->view($data['layout'], $data);
	    }


	}
	public function CambiarEnlaceDatos()
	{
		  $this->load->model("Solicitud_model", "solicitud"); 			   
		  $contrasena =$this->input->post('contrasena');
		  if($contrasena!=''){
		  	$enlacesEdita = array(                    
	            'ap_materno' => $this->input->post('ap_materno'),  
	            'ap_paterno' => $this->input->post('ap_paterno'),                                        
	            'nombre_usuario' => $this->input->post('nombre'),
	            'email' => $this->input->post('emailusr'),
                    'email_alterno' => $this->input->post('emailusralt'),
	            'usuario' =>$this->input->post('usuario'),
	            'contrasena' => md5($this->input->post('contrasena')),
	            'titulo' => $this->input->post('titulo'),
	            'cargo' => $this->input->post('cargo')	            
             );
		  }else{
		  	$enlacesEdita = array(                    
	            'ap_materno' => $this->input->post('ap_materno'),  
	            'ap_paterno' => $this->input->post('ap_paterno'),                                        
	            'nombre_usuario' => $this->input->post('nombre'),
	            'email' => $this->input->post('emailusr'),
                    'email_alterno' => $this->input->post('emailusralt'),
	            'usuario' =>$this->input->post('usuario'),	           
	            'titulo' => $this->input->post('titulo'),
	            'cargo' => $this->input->post('cargo')	            
             );
		  }
           //die(print_r($unidad_enlace));
		  if($this->input->post('id_usuario')!=''){
		  	$enlace = $this->solicitud->ActualizarEnlace($enlacesEdita,$this->input->post('id_usuario'));
		  }else{
		  	$enlacesEdita = array(                    
	            'ap_materno' => $this->input->post('ap_materno'),  
	            'ap_paterno' => $this->input->post('ap_paterno'),                                        
	            'nombre_usuario' => $this->input->post('nombre'),
	            'email' => $this->input->post('emailusr'),
                    'email_alterno' => $this->input->post('emailusralt'),
	            'usuario' =>$this->input->post('usuario'),
	            'contrasena' => md5($this->input->post('contrasena')),
	            'titulo' => $this->input->post('titulo'),
	            'cargo' => $this->input->post('cargo'),
	            'id_tipo_persona' =>1,
	            'id_tipo_usuario' => $this->input->post('tipo'),
	            'id_unidad_enlace'=>$this->input->post('id_unidad'),
	            'estatus' => 1,
	            'inicio_sesion' => 0
             );
		  	$enlace = $this->solicitud->insertarEnlace($enlacesEdita);
		  }
           
           $data['unidad'] = $ue;
           $data['layout'] = 'layout/lytVerificando';
		   //$data['scripts'] =array('msolicitudes');
		   $data['content'] = 'vUnidadEditaSuccess';
		   $data = array_merge($this->dataDefault, $data);
		   $this->load->view($data['layout'], $data);

	}
	
	public function GuardaMensajeVacas()
	{
		   $this->load->model("Solicitud_model", "solicitud");     
           $mensaje = array(                    
	            'mensaje' => $this->input->post('mensaje_vacaciones'),  
	            'activo' => 1
             );
           //die(print_r($unidad_enlace));
            $guardado = $this->solicitud->GuardaMensaVacas($mensaje);
           
           $data['unidad'] = $ue;
           $data['layout'] = 'layout/lytVerificando';
           $data['alerta'] = 'Mensaje activado con éxito';
		   //$data['scripts'] =array('msolicitudes');
		   $data['content'] = 'vMensajeSuccess';
		   $data = array_merge($this->dataDefault, $data);
		   $this->load->view($data['layout'], $data);

	}
	public function EliminaMensajeVacas()
	{
		   $this->load->model("Solicitud_model", "solicitud");     
           $mensaje = array(                    	            
	            'activo' => 0
             );
           //die(print_r($unidad_enlace));
            $guardado = $this->solicitud->ActualizaMensaVacas($mensaje);
           
           $data['unidad'] = $ue;
           $data['layout'] = 'layout/lytVerificando';
		   //$data['scripts'] =array('msolicitudes');
		   $data['alerta'] = 'Mensaje desactivado con éxito';
		   $data['content'] = 'vMensajeSuccess';
		   $data = array_merge($this->dataDefault, $data);
		   $this->load->view($data['layout'], $data);

	}
	public function ReporteCalidad()
  	{
                 //Carga las librerías
                $this->load->library('excel');
                 // Propiedades del Documento
                $this->excel->getProperties()->setCreator("Universidad de Guanajuato")
                ->setLastModifiedBy("Universidad de Guanajuato")
                ->setTitle('Reporte Calidad') 
                ->setSubject('Subject'); 

                $inicia = $this->input->get('inicia');
                $finaliza = $this->input->get('finaliza');

                $this->load->model("Solicitud_model", "solicitud");
				$solicitudes = $this->solicitud->ObtieneSolicitudesCalidad($inicia, $finaliza);

                //Cargamos el template del encabezado
                $objReader = PHPExcel_IOFactory::createReader('Excel5');
                $this->excel = $objReader->load('uploads/ReporteCalidad.xls');//Abrimos el excel en dónde escribiremos la información generada
                $objWorksheet = $this->excel->getActiveSheet()->toArray(null,null,null,null,null,null);

                 $columnas=array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z",
                 "AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ",
                 "BA","BB","BC","BD","BE","BF","BG","BH","BI","BJ","BK","BL","BM","BN","BO","BP","BQ","BR","BS","BT","BU","BV","BW","BX","BY","BZ",
                 "CA","CB","CC","CD","CE","CF","CG","CH","CI","CJ","CK","CL","CM","CN","CO","CP","CQ","CR","CS","CT","CU","CV","CW","CX","CY","CZ",
                 "DA","DB","DC","DD","DE","DF","DG","DH","DI","DJ","DK","DL","DM","DN","DO","DP","DQ","DR","DS","DT","DU","DV","DW","DX","DY","DZ",
                 "EA","EB","EC","ED","EE","EF","EG","EH","EI","EJ","EK","EL","EM","EN","EO","EP","EQ","ER","ES","ET","EU","EV","EW","EX","EY","EZ",
                 "FA","FB","FC","FD","FE","FF","FG","FH","FI","FJ","FK","FL","FM","FN","FO","FP","FQ","FR","FS","FT","FU","FV","FW","FX","FY","FZ",
                 "GA","GB","GC","GD","GE","GF","GG","GH","GI","GJ","GK","GL","GM","GN","GO","GP","GQ","GR","GS","GT","GU","GV","GW","GX","GY","GZ",
                 "HA","HB","HC","HD","HE","HF","HG","HH","HI","HJ","HK","HL","HM","HN","HO","HP","HQ","HR","HS","HT","HU","HV","HW","HX","HY","HZ",
                 "IA","IB","IC","ID","IE","IF","IG","IH","II","IJ","IK","IL","IM","IN","IO","IP","IQ","IR","IS","IT","IU","IV","IW","IX","IY","IZ");
         
                 $inicioy=2; //Renglon a partir del que se inicia a incluir registros en la hoja de excel
                 $i=0;
                 //$cell_existe = 16; 
                 foreach ($solicitudes as $sol) {
                 	 $total=0;
                 	 $mi_sujeto ="";
                 	 $mi_complemento = "";
                 	 $estatus = "3";
                 	 $complemento = $this->solicitud->ObtieneHistorialReporte($sol->id_solicitud,$estatus);
                 	 if(sizeof($complemento)>0){
                 	 	//die(print_r($complemento));
                 	 	foreach ($complemento as $c) {
                 	 		$mi_complemento = $c->observaciones;
                 	 	}                 	 	
                 	 }else{
                 	 	$mi_complemento = "";
                 	 }
                 	 $estatus = "8,9";
                 	 $fecha_r = $this->solicitud->ObtieneHistorialReporte($sol->id_solicitud,$estatus);
                 	 if(sizeof($fecha_r)>0){
                 	 	//die(print_r($complemento));
                 	 	foreach ($fecha_r as $c) {
                 	 		$mi_fecha_respuesta = $c->fecha;
                 	 	}                 	 	
                 	 }else{
                 	 	$mi_fecha_respuesta = "";
                 	 }
                 	 $sujeto = $this->solicitud->ObtieneSujetoReporte($sol->id_solicitud);
                 	 if(sizeof($sujeto)>0){
                 	 	//die(print_r($complemento));
                 	 	foreach ($sujeto as $c) {
                 	 		$mi_sujeto =$mi_sujeto." ".$c->nombre;
                 	 	}                 	 	
                 	 }else{
                 	 	$mi_sujeto = "";
                 	 }
                 	 if($sol->id_estatus==8 || $sol->id_estatus == 9){
                 	 //Cuenta los días en que se dió respuesta a una solicitud desde que ingresó
							$fecha_i = $sol->fecha_ingreso;
							$fecha_f = $mi_fecha_respuesta;

								$cuenta =$dia=0;
								$dias	= (strtotime($fecha_i)-strtotime($fecha_f))/86400;
								$dias 	= abs($dias); $dias = floor($dias);		

								$fecha1 = strtotime($fecha_i); 
								$fecha2 = strtotime($fecha_f); 
								for($fecha1;$fecha1<=$fecha2;$fecha1=strtotime('+1 day ' . date('Y-m-d',$fecha1))){ 
									$dia++;
									if(strcmp(date('D',$fecha1),'Sun')==0){
										$cuenta++;										
									}
									if(strcmp(date('D',$fecha1),'Sat')==0){
										$cuenta++;
									}	
									  $nuevafecha = $fecha1;
					                  $nuevafecha = date ( 'Y-m-d' , $nuevafecha );
					                  $FechaFinal= $nuevafecha;					                  

									$existeFecha = $this->solicitud->getNoHabilesReporte($FechaFinal);									
									if($existeFecha){
										$cuenta++;
									}
								}
								$total = $dias-$cuenta;
					 }



                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[0].$inicioy, $sol->fecha_ingreso);//Agregamos cadena a campo date(format)el documento                     
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[1].$inicioy, $sol->dsc_tema);
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[2].$inicioy, $sol->tema_anterior);
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[3].$inicioy, $sol->id_solicitud);
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[4].$inicioy, $sol->contenido);
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[5].$inicioy, $mi_complemento);
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[6].$inicioy, $mi_sujeto);
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[7].$inicioy, $sol->dsc_estatus);
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[8].$inicioy, $sol->fecha_limite_entrega);
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[9].$inicioy, $sol->dsc_tipo_solicitud);
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[10].$inicioy, $sol->respuesta);
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[11].$inicioy, $mi_fecha_respuesta);
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[12].$inicioy, $total);
                    $this->excel->getActiveSheet()->getRowDimension($inicioy)->setRowHeight(18);//Ajustamos el alto de las celdas
                    $inicioy++;
                 }               
         //Fecha y número aleatorio para nombrar al excel dentro del zip         
       $filename = "ReporteCalidad";
       header('Content-Type: application/vnd.ms-excel'); //mime type
       header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
       header('Cache-Control: max-age=0'); //no cache
       
       //save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' (and adjust the filename extension, also the header mime type)
       //if you want to save it as .XLSX Excel 2007 format
       $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
       //force user to download the Excel file without writing it to server's HD
        $objWriter->save('php://output');
       //$objWriter->save('uploads/rep_excel/'.$filename);
  }
  public function ContarDias()
  {
  	$cuenta=0;
  	$this->load->model("Solicitud_model", "solicitud");

  	$fecha_i = "2014-07-03";
	$fecha_f = "2014-07-22";

			$cuenta =$dia=0;
			$dias	= (strtotime($fecha_i)-strtotime($fecha_f))/86400;
			$dias 	= abs($dias); $dias = floor($dias);		

			$fecha1 = strtotime($fecha_i); 
			$fecha2 = strtotime($fecha_f); 
			for($fecha1;$fecha1<=$fecha2;$fecha1=strtotime('+1 day ' . date('Y-m-d',$fecha1))){ 
				$dia++;
				if(strcmp(date('D',$fecha1),'Sun')==0){
					$cuenta++;
					echo date('Y-m-d',$fecha1)."<br>";
					//die($cuenta);
				}
				if(strcmp(date('D',$fecha1),'Sat')==0){
					$cuenta++;
					echo date('Y-m-d',$fecha1)."<br>";
					//die($cuenta);
				}	
				  $nuevafecha = $fecha1;
                  $nuevafecha = date ( 'Y-m-d' , $nuevafecha );
                  $FechaFinal= $nuevafecha;
                  //die(print($FechaFinal));

				$existeFecha = $this->solicitud->getNoHabilesReporte($FechaFinal);
				//print_r($existeFecha);
				//echo $existeFecha->id_fecha;
				if($existeFecha){
					//die(print($fecha_i));

					$cuenta++;
					echo date('Y-m-d',$fecha1)."Festivo<br>";
				}
			}
			$total = $dias-$cuenta;
			die(print($dias." ".$cuenta." ".$dia." Total->".$total));
			//print($dias-$cuenta);
		
		// Salida : 17
  }
  public function GenRecurso()
	{
		$this->load->model("Solicitud_model", "solicitud");
		$data['content'] = 'vGenRecurso';
		$data['id_solicitud'] = $this->input->get('id_solicitud');
		$where = "id_solicitud =".$this->input->get('id_solicitud');
		$datos_sol = $this->solicitud->getUnaSolicitud($where);
		$data['solicitud'] =$datos_sol;


		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}
public function GenRecursoGuardar()
	{
		$this->load->model("Solicitud_model", "solicitud");
		$id_solicitud = $this->input->post('id_solicitud');		
		$respuesta_publica = $this->input->post('respuesta_publica');


		$where = "id_solicitud = ".$id_solicitud;
		//$unidad_enlace = 0;
		$data['accion'] = 14;

		$query = $this->solicitud->GenerarRecurso($id_solicitud,$respuesta_publica);


		//$data['unidades_enlace'] =$query;

		$data['content'] = 'vCanalizarGuardar';

		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}
	public function ActualizaMensaje()
	{
		$this->load->model("Solicitud_model", "solicitud");
		$mensaje = $this->input->post('mensaje');
		//die($mensaje);		
		$id = $this->input->post('id');
		$where = "id_mensaje_correo = ".$id;
		//$unidad_enlace = 0;
		$data['accion'] = 15;
		//$data['layout'] = 'layout/lytVerificando';
		$query = $this->solicitud->ActualizaMensaje($mensaje,$id);
		$data['content'] = 'vCanalizarGuardar';
		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}
	public function ReporteOrientadas()
  	{
                 //Carga las librerías
                $this->load->library('excel');
                 // Propiedades del Documento
                $this->excel->getProperties()->setCreator("Universidad de Guanajuato")
                ->setLastModifiedBy("Universidad de Guanajuato")
                ->setTitle('Reporte Solicitudes Orientadas') 
                ->setSubject('Subject'); 
                
                $this->load->model("Solicitud_model", "solicitud");
				$solicitudes = $this->solicitud->getOrientadaTodas();

                //Cargamos el template del encabezado
                $objReader = PHPExcel_IOFactory::createReader('Excel5');
                $this->excel = $objReader->load('uploads/ReporteOrientadas.xls');//Abrimos el excel en dónde escribiremos la información generada
                $objWorksheet = $this->excel->getActiveSheet()->toArray(null,null,null,null,null,null);

                 $columnas=array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z",
                 "AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ",
                 "BA","BB","BC","BD","BE","BF","BG","BH","BI","BJ","BK","BL","BM","BN","BO","BP","BQ","BR","BS","BT","BU","BV","BW","BX","BY","BZ",
                 "CA","CB","CC","CD","CE","CF","CG","CH","CI","CJ","CK","CL","CM","CN","CO","CP","CQ","CR","CS","CT","CU","CV","CW","CX","CY","CZ",
                 "DA","DB","DC","DD","DE","DF","DG","DH","DI","DJ","DK","DL","DM","DN","DO","DP","DQ","DR","DS","DT","DU","DV","DW","DX","DY","DZ",
                 "EA","EB","EC","ED","EE","EF","EG","EH","EI","EJ","EK","EL","EM","EN","EO","EP","EQ","ER","ES","ET","EU","EV","EW","EX","EY","EZ",
                 "FA","FB","FC","FD","FE","FF","FG","FH","FI","FJ","FK","FL","FM","FN","FO","FP","FQ","FR","FS","FT","FU","FV","FW","FX","FY","FZ",
                 "GA","GB","GC","GD","GE","GF","GG","GH","GI","GJ","GK","GL","GM","GN","GO","GP","GQ","GR","GS","GT","GU","GV","GW","GX","GY","GZ",
                 "HA","HB","HC","HD","HE","HF","HG","HH","HI","HJ","HK","HL","HM","HN","HO","HP","HQ","HR","HS","HT","HU","HV","HW","HX","HY","HZ",
                 "IA","IB","IC","ID","IE","IF","IG","IH","II","IJ","IK","IL","IM","IN","IO","IP","IQ","IR","IS","IT","IU","IV","IW","IX","IY","IZ");
         
                 $inicioy=2; //Renglon a partir del que se inicia a incluir registros en la hoja de excel
                 $i=0;
                 //$cell_existe = 16; 
                 foreach ($solicitudes as $sol) {
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[0].$inicioy, $sol->id_orientada);//Agregamos cadena a campo date(format)el documento                     
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[1].$inicioy, $sol->folio);
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[2].$inicioy, $sol->contenido);
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[3].$inicioy, $sol->correo);
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[4].$inicioy, $sol->area_responde);
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[5].$inicioy, $sol->respuesta);
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[6].$inicioy, $sol->fecha_ingreso);
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[7].$inicioy, $sol->fecha_respuesta);
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[8].$inicioy, $sol->solicitante);
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[9].$inicioy, $sol->consecutivo);
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[10].$inicioy, $sol->ano);                   
                    $this->excel->getActiveSheet()->getRowDimension($inicioy)->setRowHeight(18);//Ajustamos el alto de las celdas
                    $inicioy++;
                 }               
         //Fecha y número aleatorio para nombrar al excel dentro del zip         
       $filename = "ReporteOrientadas";
       header('Content-Type: application/vnd.ms-excel'); //mime type
       header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
       header('Cache-Control: max-age=0'); //no cache
       
       //save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' (and adjust the filename extension, also the header mime type)
       //if you want to save it as .XLSX Excel 2007 format
       $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
       //force user to download the Excel file without writing it to server's HD
        $objWriter->save('php://output');
       //$objWriter->save('uploads/rep_excel/'.$filename);
  }
  public function ReporteIACIP()
  	{
                 //Carga las librerías
                $this->load->library('excel');
                 // Propiedades del Documento
                $this->excel->getProperties()->setCreator("Universidad de Guanajuato")
                ->setLastModifiedBy("Universidad de Guanajuato")
                ->setTitle('Reporte Calidad') 
                ->setSubject('Subject'); 

                $inicia = $this->input->get('inicia');
                $finaliza = $this->input->get('finaliza');

                $this->load->model("Solicitud_model", "solicitud");
				$solicitudes = $this->solicitud->ObtieneSolicitudesIacip($inicia, $finaliza);

                //Cargamos el template del encabezado
                $objReader = PHPExcel_IOFactory::createReader('Excel5');
                $this->excel = $objReader->load('uploads/ReporteIACIP.xls');//Abrimos el excel en dónde escribiremos la información generada
                $objWorksheet = $this->excel->getActiveSheet()->toArray(null,null,null,null,null,null);

                 $columnas=array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z",
                 "AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ",
                 "BA","BB","BC","BD","BE","BF","BG","BH","BI","BJ","BK","BL","BM","BN","BO","BP","BQ","BR","BS","BT","BU","BV","BW","BX","BY","BZ",
                 "CA","CB","CC","CD","CE","CF","CG","CH","CI","CJ","CK","CL","CM","CN","CO","CP","CQ","CR","CS","CT","CU","CV","CW","CX","CY","CZ",
                 "DA","DB","DC","DD","DE","DF","DG","DH","DI","DJ","DK","DL","DM","DN","DO","DP","DQ","DR","DS","DT","DU","DV","DW","DX","DY","DZ",
                 "EA","EB","EC","ED","EE","EF","EG","EH","EI","EJ","EK","EL","EM","EN","EO","EP","EQ","ER","ES","ET","EU","EV","EW","EX","EY","EZ",
                 "FA","FB","FC","FD","FE","FF","FG","FH","FI","FJ","FK","FL","FM","FN","FO","FP","FQ","FR","FS","FT","FU","FV","FW","FX","FY","FZ",
                 "GA","GB","GC","GD","GE","GF","GG","GH","GI","GJ","GK","GL","GM","GN","GO","GP","GQ","GR","GS","GT","GU","GV","GW","GX","GY","GZ",
                 "HA","HB","HC","HD","HE","HF","HG","HH","HI","HJ","HK","HL","HM","HN","HO","HP","HQ","HR","HS","HT","HU","HV","HW","HX","HY","HZ",
                 "IA","IB","IC","ID","IE","IF","IG","IH","II","IJ","IK","IL","IM","IN","IO","IP","IQ","IR","IS","IT","IU","IV","IW","IX","IY","IZ");
         
                 $inicioy=2; //Renglon a partir del que se inicia a incluir registros en la hoja de excel
                 $i=0;
                 //$cell_existe = 16; 
                 foreach ($solicitudes as $sol) {
                 	 $total=0;
                 	 $mi_sujeto ="";
                 	 $mi_complemento = "";
                 	 $estatus = "3";
                 	 $complemento = $this->solicitud->ObtieneHistorialReporte($sol->id_solicitud,$estatus);
                 	 if(sizeof($complemento)>0){
                 	 	//die(print_r($complemento));
                 	 	foreach ($complemento as $c) {
                 	 		$mi_complemento = $c->observaciones;
                 	 	}                 	 	
                 	 }else{
                 	 	$mi_complemento = "";
                 	 }
                 	 $estatus = "8,9";
                 	 $fecha_r = $this->solicitud->ObtieneHistorialReporte($sol->id_solicitud,$estatus);
                 	 if(sizeof($fecha_r)>0){
                 	 	//die(print_r($complemento));
                 	 	foreach ($fecha_r as $c) {
                 	 		$mi_fecha_respuesta = $c->fecha;
                 	 	}                 	 	
                 	 }else{
                 	 	$mi_fecha_respuesta = "";
                 	 }
                 	 $sujeto = $this->solicitud->ObtieneSujetoReporte($sol->id_solicitud);
                 	 if(sizeof($sujeto)>0){
                 	 	//die(print_r($complemento));
                 	 	foreach ($sujeto as $c) {
                 	 		$mi_sujeto =$mi_sujeto." ".$c->nombre;
                 	 	}                 	 	
                 	 }else{
                 	 	$mi_sujeto = "";
                 	 }
                 	 if($sol->id_estatus==8 || $sol->id_estatus == 9){
                 	 //Cuenta los días en que se dió respuesta a una solicitud desde que ingresó
							$fecha_i = $sol->fecha_ingreso;
							$fecha_f = $mi_fecha_respuesta;

								$cuenta =$dia=0;
								$dias	= (strtotime($fecha_i)-strtotime($fecha_f))/86400;
								$dias 	= abs($dias); $dias = floor($dias);		

								$fecha1 = strtotime($fecha_i); 
								$fecha2 = strtotime($fecha_f); 
								for($fecha1;$fecha1<=$fecha2;$fecha1=strtotime('+1 day ' . date('Y-m-d',$fecha1))){ 
									$dia++;
									if(strcmp(date('D',$fecha1),'Sun')==0){
										$cuenta++;										
									}
									if(strcmp(date('D',$fecha1),'Sat')==0){
										$cuenta++;
									}	
									  $nuevafecha = $fecha1;
					                  $nuevafecha = date ( 'Y-m-d' , $nuevafecha );
					                  $FechaFinal= $nuevafecha;					                  

									$existeFecha = $this->solicitud->getNoHabilesReporte($FechaFinal);									
									if($existeFecha){
										$cuenta++;
									}
								}
								$total = $dias-$cuenta;
					 }
					 if($sol->id_sexo==1)
					 {
					 	$sexo = "Hombre";
					 }else
					 {	
					 	$sexo = "Mujer";
					 }
					 switch ($sol->edad) {
					 	case 1:
					 		$edad = "18-30 años";
					 		break;
					 	case 2:
					 		$edad = "31-40 años";
					 		break;
					 	case 3:
					 		$edad = "41-55 años";
					 		break;
					 	case 4:
					 		$edad = "56 o más años";
					 		break;
					 	
					 	default:
					 		$edad = "";
					 		break;
					 }

					$this->excel->setActiveSheetIndex(0)->setCellValue($columnas[0].$inicioy, $mi_sujeto);
					$this->excel->setActiveSheetIndex(0)->setCellValue($columnas[1].$inicioy, $sol->contenido);
					$this->excel->setActiveSheetIndex(0)->setCellValue($columnas[2].$inicioy, $sol->id_solicitud);
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[3].$inicioy, $sol->fecha_ingreso);
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[4].$inicioy, $sol->dsc_tipo_solicitud); 
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[5].$inicioy, $mi_fecha_respuesta);
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[6].$inicioy, $sol->respuesta);                   
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[7].$inicioy, $sol->dsc_tema);
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[8].$inicioy, $sol->tema_anterior);
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[9].$inicioy, $sol->costo_total);
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[10].$inicioy, $total);
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[11].$inicioy, $sexo);
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[12].$inicioy, $sol->ocupacion);
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[13].$inicioy, $edad);
                    $this->excel->setActiveSheetIndex(0)->setCellValue($columnas[14].$inicioy, $sol->dsc_estatus);                    
                    
                    

                    $this->excel->getActiveSheet()->getRowDimension($inicioy)->setRowHeight(18);//Ajustamos el alto de las celdas
                    $inicioy++;
                 }               
         //Fecha y número aleatorio para nombrar al excel dentro del zip         
       $filename = "ReporteCalidad";
       header('Content-Type: application/vnd.ms-excel'); //mime type
       header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
       header('Cache-Control: max-age=0'); //no cache
       
       //save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' (and adjust the filename extension, also the header mime type)
       //if you want to save it as .XLSX Excel 2007 format
       $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
       //force user to download the Excel file without writing it to server's HD
        $objWriter->save('php://output');
       //$objWriter->save('uploads/rep_excel/'.$filename);
  }
    public function FechasVacas()
  	{
				$this->load->library('nohabil');
			$limite = 1;

			$fecha_limite = $this->nohabil->CalculaFecha($limite);
			die(print($fecha_limite));
	}
  
  
    public function AgregarAvisoExtmp()
    {
        try
        {
            $this->load->library('nohabil');

            $aviso = $this->input->post('aviso');
            $id = $this->input->post('id');
            $no_dias = $this->input->post('no_dias');

    
            $fecha_limite = $this->nohabil->CalculaFecha($no_dias);            
        
            $fechas=explode("-",$fecha_limite);
            $fecha=$fechas[2]."-".$fechas[1]."-".$fechas[0];
            
            $data = array(
            'id_solicitud' => $id,
            'id_estatus' => 14,
            'fecha' => $fecha,
            'id_unidad_enlace' => 0,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'observaciones' => $aviso
            );

            $this->db->insert('uaip_historial',$data);

            $mensaje="Se agrego el aviso de extemporaneidad.";
            $tipo_error=1;
        }
         catch (Exception $e)
         {
             $tipo_error=2;
             $mensaje=  utf8_encode($e->getMessage());             
         }
       
       echo json_encode(array("tipo_error"=>1,"mensaje"=>$mensaje));
        
    }
    
    	public function ReenviarCorreo()
	{
            try
            {
                $id_solicitud = $this->input->post('id_solicitud');
                $this->load->model("Solicitud_model", "solicitud");
                $whereFinal = "id_solicitud = ".$id_solicitud." and id_tipo_adjunto = 1";
                $archivosRespuesta = $this->solicitud->getArchivosRespuesta($whereFinal);

                $where = "id_solicitud =".$id_solicitud;
                $this->db->select('respuesta, id_usuario');
                $this->db->from('uaip_solicitud');
                $this->db->where($where,NULL,FALSE);
                $query = $this->db->get();
                $rsData=$query->result();
    
                $observaciones="";
                $id_usuario=0;
                foreach ($rsData as $d)
                {
                    $observaciones=$d->respuesta;
                    $id_usuario=$d->id_usuario;
                }
                
                
                $where = "id_usuario =".$id_usuario;
                $this->db->select('email');
                $this->db->from('uaip_usuario');
                $this->db->where($where,NULL,FALSE);
                $query = $this->db->get();
                $rsData=$query->result();
                
                $correo_usr="";
                foreach ($rsData as $d)
                    $correo_usr=$d->email;
                
                //Enviar correo electrónico
                $transport = Swift_SmtpTransport::newInstance()
                ->setHost($this->config->item('smtp_host'))
                ->setPort($this->config->item('puerto'))
                ->setEncryption($this->config->item('encriptacion'))
                ->setUsername($this->config->item('usuario'))
                ->setPassword($this->config->item('contraseña'));

                //Create the Mailer using your created Transport
                $mailer = Swift_Mailer::newInstance($transport);

                //Pass it as a parameter when you create the message
                $message = Swift_Message::newInstance();
                //Give the message a subject
                //Or set it after like this
                $message->setSubject('Unidad de Acceso a la Información Pública: UG');
                //no_reply@ugto.mx
                $message->setFrom(array($this->config->item('usuario') => 'Solicitud de Información '.$id_solicitud));
                //$message->addTo(trim("sortiza@ugto.mx"));
                $message->addTo(trim($correo_usr));
                $message->addBcc("uaip21@gmail.com");
                $message->addBcc("uaip@ugto.mx");
                //Add alternative parts with addPart()
    
                  $message->addPart("
                              <b>Respuesta Solicitud ".$id_solicitud.".</b> <br><br>
                              ".  $observaciones."
                              <br><br>
                               <center>
                               Atentamente <br><br><br>
                                                            <p> Lic. Miriam Contreras Ortiz<br>
                                                            Titular de la Unidad de Acceso a la Información Pública</p>

                                                            <p>Lascuráin de Retana No. 5 Centro, <br>
                                                            Guanajuato, Gto., México C.P. 36000  </p>

                                                            <p>Tel.: (473) 7320006 <br>
                                                            Exts.: 2042, 3102, 2058, 2043</p>

                                                            <p>
                                                            <a href=\"uaip@ugto.mx\">uaip@ugto.mx</a><br>
                                                            <a href=\"m.contrerasortiz@ugto.mx\">m.contrerasortiz@ugto.mx</a>
                                                            </p>
                                                            <p>Horario de Atención: 8:30 am. a 3:30 pm.</p>
                                 </center>
                  ", 'text/html');
                // Optionally add any attachments
                foreach ($archivosRespuesta as $archivo) {
                    if($archivo->nombre_adjunto!= '')
                        $message->attach(Swift_Attachment::fromPath("{$_SERVER['DOCUMENT_ROOT']}/{$this->config->item('dirPrincipal')}/uploads/RespuestaFolio".$id_solicitud."/".$archivo->nombre_adjunto));
                }

                //@$result = $mailer->send($message);
                
                $mensaje="El correo fue enviado de manera satisfactoria";
                $tipo_error=1;
            }
            catch(Exception $e)
            {
                $mensaje=  utf8_encode($e->getMessage());
                $tipo_error=2;
            }
            
            echo json_encode(array("tipo_error"=>$tipo_error,"mensaje"=>$mensaje));
        }
        
        public function ReenviarCorreoProceso()
	{
            try
            {
                $this->load->model("Solicitud_model", "solicitud");
                $id_solicitud = $this->input->post('id_solicitud');
                $where = "id_solicitud = ".$id_solicitud;

                //$rsData = $this->solicitud->getHistorialRespuesta("id_estatus=4 and id_solicitud=".$id_solicitud);
                
                $n=0;
                $correo_titular=array();
                $correo_responsable=array();
                $correo_alterno=array();
                $correo_jefe_varias_unidades=array();
                foreach ($_POST['id_unidad'] as $d)
                {
                    $unidad_enlace = $d;
                    $trae_correo_titular = $this->solicitud->getTitularEnlace($unidad_enlace);
                    $correo_titular[] = $trae_correo_titular->email;
                    $trae_correo_enlace = $this->solicitud->getResponsableEnlace($unidad_enlace);
                    $correo_responsable[] = $trae_correo_enlace->email;
                    $correo_alterno[] = trim($trae_correo_enlace->email_alterno);
                    
                    
                    $correos_jefe_vu=array();
                    $qJefe=$this->solicitud->getUnidadesCargo("00000",$unidad_enlace);
                    $dCorreos = json_decode(file_get_contents($this->config->item('url_ws_nue').trim($qJefe->id_empleado)));      
                    foreach($dCorreos as $dc)
                    {
                        
                        $correos_jefe_vu[]=trim($dc->email);
                    }
                    
                    $correo_jefe_varias_unidades[]=$correos_jefe_vu;
                    $n++;
                }                
                
		$MensajeCorreo = $this->solicitud->getMensajeCorreo(1);

                $trae_solicitud = $this->solicitud->getUnaSolicitud($where);
                $id_usuario = $trae_solicitud->id_usuario;
                $trae_datos_usuario = $this->solicitud->getDatosUsuario($id_usuario);
                $correo_usr = $trae_datos_usuario->email;
                

                    $transport = Swift_SmtpTransport::newInstance()
                    ->setHost($this->config->item('smtp_host'))
                    ->setPort($this->config->item('puerto'))
                    ->setEncryption($this->config->item('encriptacion'))
                    ->setUsername($this->config->item('usuario'))
                    ->setPassword($this->config->item('contraseña'));

                    //Create the Mailer using your created Transport
                    $mailer = Swift_Mailer::newInstance($transport);
                    //Pass it as a parameter when you create the message
                    $message = Swift_Message::newInstance();
                    //Give the message a subject
                    //Or set it after like this
                    $message->setSubject('Unidad de Acceso a la Información Pública: UG');
                    //no_reply@ugto.mx
                    $message->setFrom(array($this->config->item('usuario') => 'Solicitud de Información '.$id_solicitud));
                    //$message->addTo(trim("sortiza@ugto.mx"));		              
                    $message->addTo(trim($correo_usr));
                    $message->addBcc("uaip21@gmail.com");		             		              
                    $message->addBcc("uaip@ugto.mx");

                    //Add alternative parts with addPart()
                    $message->addPart("<b>Su solicitud ".$id_solicitud." se ha puesto en proceso.</b> <br><br>
                                          ".$MensajeCorreo->mensaje."

                    <br><br><br>
                    <center>
                    Atentamente <br><br><br>
                                                <p> Lic. Miriam Contreras Ortiz<br>
                                                Titular de la Unidad de Acceso a la Información Pública</p>

                                                <p>Lascuráin de Retana No. 5 Centro, <br>
                                                Guanajuato, Gto., México C.P. 36000  </p>

                                                <p>Tel.: (473) 7320006 <br>
                                                Exts.: 2042, 3102, 2058, 2043</p>

                                                <p>
                                                <a href=\"uaip@ugto.mx\">uaip@ugto.mx</a><br>
                                                <a href=\"m.contrerasortiz@ugto.mx\">m.contrerasortiz@ugto.mx</a>
                                                </p>
                                                <p>Horario de Atención: 8:30 am. a 3:30 pm.</p>
                     </center>
                    ", 'text/html');
                    //@$result = $mailer->send($message);
                
                 
		//if($correo_titular != '' || $correo_responsable != '')
                for($i=0;$i<$n;$i++)
                {
                    //Enviar correo electrónico
                    $transport = Swift_SmtpTransport::newInstance()
                    ->setHost($this->config->item('smtp_host'))
                    ->setPort($this->config->item('puerto'))
                    ->setEncryption($this->config->item('encriptacion'))
                    ->setUsername($this->config->item('usuario'))
                    ->setPassword($this->config->item('contraseña'));

                    //Create the Mailer using your created Transport
                    $mailer = Swift_Mailer::newInstance($transport);

                    //Pass it as a parameter when you create the message
                    $message = Swift_Message::newInstance();
                    //Give the message a subject
                    //Or set it after like this
                    $message->setSubject('Unidad de Acceso a la Información Pública: UG');
                    //no_reply@ugto.mx
                    $message->setFrom(array($this->config->item('usuario') => 'Solicitud de Información '.$id_solicitud));
                    //$message->addTo("sortiza@ugto.mx");
                    $message->addTo(trim($correo_titular[$i]));
                    $message->addBcc("uaip21@gmail.com");
                    $message->addBcc("uaip@ugto.mx");
                    
                    
                    if(trim($correo_responsable[$i]) == ''){
                        $correo_responsable[$i] = $correo_titular[$i];
                    }

                    $message->addTo(trim($correo_responsable[$i]));                                     
                                   
                    if(trim($correo_alterno[$i])!='')
                        $message->addTo(trim($correo_alterno[$i]));
        
                    
                    $correos_jvu=$correo_jefe_varias_unidades[$i];
                    foreach($correos_jvu as $c)
                    {
                        if(trim($c)!='')
                            $message->addBcc(trim($c));
                    }
                    
                    //Add alternative parts with addPart()
                    $message->addPart("									
                    <b>Se ha canalizado una nueva Solicitud de Información a su Unidad con folio ".$id_solicitud."</b><br><br>
                    La cual se detalla a continuación:<br><br><div style='word-break:break-all' >".
                    $trae_solicitud->contenido
                    ."</div><br><br>
                                      Para conocer el contenido, el plazo para dar respuesta y comenzar a dar trámite a la solicitud de información le pedimos ingresar al Sistema de Solicitudes de Información Pública (SSIP) en el siguiente link http://www.transparencia.ugto.mx
                                            <br><br>
                    Le recordamos que el oficio final de respuesta tendrá que incluir el número de folio de la solicitud.
                    <br><br>
                    Para cualquier duda o aclaración favor de comunicarse a la Unidad de Acceso a la Información Pública al teléfono (473) 7320006 extensiones 3102, 2042 o 2043. Agradecemos su colaboración.

                    <br><br><br>
                    <center>
                    Atentamente <br><br><br>
                                            <p> Lic. Miriam Contreras Ortiz<br>
                                            Titular de la Unidad de Acceso a la Información Pública</p>

                                            <p>Lascuráin de Retana No. 5 Centro, <br>
                                            Guanajuato, Gto., México C.P. 36000  </p>

                                            <p>Tel.: (473) 7320006 <br>
                                            Exts.: 2042, 3102, 2058, 2043</p>

                                            <p>
                                            <a href=\"uaip@ugto.mx\">uaip@ugto.mx</a><br>
                                            <a href=\"m.contrerasortiz@ugto.mx\">m.contrerasortiz@ugto.mx</a>
                                            </p>
                                            <p>Horario de Atención: 8:30 am. a 3:30 pm.</p>
                    </center>
                    ", 'text/html');
                    //@$result = $mailer->send($message);
		 }
                 
                $tipo_error=1;
                $mensaje=  "El correo fue enviado de manera satisfactoria.";    
            }
            catch(Exception $e)
            {
                $tipo_error=2;
                $mensaje=  utf8_encode($e->getMessage());                
            }
            echo json_encode(array("tipo_error"=>$tipo_error,"mensaje"=>$mensaje));
        }
        
        public function ObtenerArchivosRespondida()
	{
            $this->load->model("Solicitud_model", "solicitud");
            $id_solicitud = $this->input->post('id_solicitud');
            $rsData = $this->solicitud->getArchivosRespondida($id_solicitud);
            
            $r=array();
            foreach ($rsData as $d)
            {
                $d=(object)$d;
                
                $dsc_tipo="Final";
                if($d->id_tipo_adjunto==2)
                    $dsc_tipo="Público";
                
                $r[]=array("arch_id_pertenece"=>$d->id_solicitud,"arch_id_pertenece_archivo"=>$d->id_adjunto,"arch_nombre"=>$d->nombre_adjunto,"arch_tipo"=>$dsc_tipo);
            }
            
            echo json_encode($r);            
        }
       
        public function NuevaUnidadEnlace()
	{
		   $this->load->model("Solicitud_model", "solicitud");     
                   $unidad_enlace = array(           
                    //'id_unidad_enlace' =>$this->input->post('id_unidad_enlace + 1'),
	            'sigla' => $this->input->post('sigla'),  
	            'nombre' => $this->input->post('nombre'),                                        
	            'clave_tel' => $this->input->post('clave'),
	            'numero_tel' => $this->input->post('telefono'),
	            'ext' =>$this->input->post('ext'),
	            'fax' => $this->input->post('fax'),
	            'activo' => $this->input->post('activo')                    
             );
           //die(print_r($unidad_enlace));
            $unidad_de_enlace = $this->solicitud->AgregarUnidad($unidad_enlace);
           
           $data['unidad'] = $ue;
           $data['layout'] = 'layout/lytVerificando';
		   //$data['scripts'] =array('msolicitudes');
		   $data['content'] = 'vUnidadEditaSuccess';
		   $data = array_merge($this->dataDefault, $data);
		   $this->load->view($data['layout'], $data);

	}
        
         public function ObtenerUnidadEsp()
	{
            $this->load->model("Solicitud_model", "solicitud");
            $id_solicitud = $this->input->post('id_solicitud');
            $rsData = $this->solicitud->getUnidadesEnlaceEsp($id_solicitud);
            
            $r=array();
             foreach ($rsData as $d)
            {

                $d=(object)$d;

                $r[]=array("unidad_id_enlace"=>$d->id_unidad_enlace,"unidad_sigla"=>$d->sigla,"unidad_nombre"=>$d->nombre,"unidad_activo"=>$d->activo);
            }

            echo json_encode($r);             
        }
        
         public function ObtenerLeyCorreo()
	{
            $this->load->model("Solicitud_model", "solicitud");
            $id_estatus = $this->input->post('id');
            $id_solicitud = $this->input->post('id_solicitud');
            
            $rsData = $this->solicitud->getLeyCorreo($id_estatus);            
         
            $where = "id_solicitud = ".$id_solicitud;
            $trae_solicitud = $this->solicitud->getUnaSolicitud($where);
            $id_usuario = $trae_solicitud->id_usuario;
            $trae_datos_usuario = $this->solicitud->getDatosUsuario($id_usuario);

            $r=array("mensaje"=>$rsData->mensaje,"email"=>$trae_datos_usuario->email);
            echo json_encode($r);
        }
        
        
        public function EnviarCuerpoCorreo()
	{
            try
            {
            $this->load->model("Solicitud_model", "solicitud");
            $id_solicitud = $this->input->post('id');
            $id_estatus = $this->input->post('id_estatus');
            $cuerpo = $this->input->post('cuerpo_correo');
            
            $where = "id_solicitud = ".$id_solicitud;
            $trae_solicitud = $this->solicitud->getUnaSolicitud($where);
            $id_usuario = $trae_solicitud->id_usuario;
            $trae_datos_usuario = $this->solicitud->getDatosUsuario($id_usuario);
            $correo_usr = $trae_datos_usuario->email;
            
            
            //actualizamos el estatus de la solicitud
            $query = $this->solicitud->ActualizarEstatus($id_solicitud,$id_estatus);
            $query2 = $this->solicitud->GuardarHistorial($id_solicitud,$id_estatus,"0",$cuerpo);
            
            $transport = Swift_SmtpTransport::newInstance()
            ->setHost($this->config->item('smtp_host'))
            ->setPort($this->config->item('puerto'))
            ->setEncryption($this->config->item('encriptacion'))
            ->setUsername($this->config->item('usuario'))
            ->setPassword($this->config->item('contraseña'));

            //Create the Mailer using your created Transport
            $mailer = Swift_Mailer::newInstance($transport);

            //Pass it as a parameter when you create the message
            $message = Swift_Message::newInstance();
            //Give the message a subject
            //Or set it after like this
            $message->setSubject('Unidad de Acceso a la Información Pública: UG');
            //no_reply@ugto.mx
            $message->setFrom(array($this->config->item('usuario') => 'Solicitud de Información '.$id_solicitud));
            //$message->addTo("sortiza@ugto.mx");
            $message->addTo(trim($correo_usr));
            $message->addBcc("uaip@ugto.mx");
                    
            //Add alternative parts with addPart()
            $message->addPart(nl2br($cuerpo)."
            <br><br><br>
            <center>
                    Atentamente <br><br>
                    <p> Lic. Miriam Contreras Ortiz<br>
                    Titular de la Unidad de Acceso a la Información Pública</p>

                    <p>
                    Universidad de Guanajuato
                    Lascuráin de Retana No. 5 Centro, <br>
                    Guanajuato, Gto., México C.P. 36000  </p>

                    <p>Tel.: (473) 7320006 <br>
                    Exts.: 2042, 3102, 2058, 2043</p>

                    <p>
                    <a href=\"uaip@ugto.mx\">uaip@ugto.mx</a><br>
                    <a href=\"m.contrerasortiz@ugto.mx\">m.contrerasortiz@ugto.mx</a>
                    </p>
                    <p>Horario de Atención: 8:30 am. a 3:30 pm.</p>
            </center>
            ", 'text/html');
            //@$result = $mailer->send($message);

            $id_tipo_error=1;
            $mensaje="El correo fue enviado de manera satisfactoria";

            }
             catch (Exception $e)
             {
                 $id_tipo_error=2;
                 $mensaje=  utf8_encode($e->getMessage());
             }
             
            $r=array("id_tipo_error"=>$id_tipo_error,"mensaje"=>$mensaje);

            echo json_encode($r);             
        }
        
        
        public function ReenviarCorreoProrroga()
	{
            try
            {
            $this->load->model("Solicitud_model", "solicitud");
            $id_solicitud = $this->input->post('id_solicitud');

            $observaciones_prorroga = "";
            $unidad_enlace = number_format($this->session->userdata('id_unidad_enlace'),0,'','');

            $where = "id_solicitud = ".$id_solicitud;
            $query = $this->solicitud->getHistorialRespuesta("id_estatus=5 and id_solicitud=".$id_solicitud." and  id_unidad_enlace=".$unidad_enlace);

            foreach ($query as $q)
                $observaciones_prorroga=$q->observaciones;
            
            $trae_solicitud = $this->solicitud->getUnaSolicitud($where);
            $id_usuario = $trae_solicitud->id_usuario;
            $trae_datos_usuario = $this->solicitud->getDatosUsuario($id_usuario);
            $correo_usr = $trae_datos_usuario->email;
            $MensajeCorreo = $this->solicitud->getMensajeCorreo(4);

            //Enviar correo electrónico
            $transport = Swift_SmtpTransport::newInstance()
            ->setHost($this->config->item('smtp_host'))
            ->setPort($this->config->item('puerto'))
            ->setEncryption($this->config->item('encriptacion'))
            ->setUsername($this->config->item('usuario'))
            ->setPassword($this->config->item('contraseña'));

            //Create the Mailer using your created Transport
            $mailer = Swift_Mailer::newInstance($transport);
            $message = Swift_Message::newInstance();
            $message->setSubject('Unidad de Acceso a la Información Pública: UG');
            $message->setFrom(array($this->config->item('usuario') => 'Solicitud de Información '.$id_solicitud));
            //$message->addTo(trim("sortiza@ugto.mx"));
            $message->addTo(trim($correo_usr));
            $message->addBcc("uaip21@gmail.com");
	    $message->addBcc("uaip@ugto.mx");

              //Add alternative parts with addPart()
              $message->addPart("
                          <b>Su solicitud ".$id_solicitud." se ha puesto en prórroga.</b> <br><br>
                          <b>Observaciones:</b><br>
                          ".$observaciones_prorroga."
                          <br><br>
                          ".$MensajeCorreo->mensaje."
                          <br><br><br>
                           <center>
                           Atentamente <br><br><br>
							<p> Lic. Miriam Contreras Ortiz<br>
							Titular de la Unidad de Acceso a la Información Pública</p>

							<p>Lascuráin de Retana No. 5 Centro, <br>
							Guanajuato, Gto., México C.P. 36000  </p>

							<p>Tel.: (473) 7320006 <br>
							Exts.: 2042, 3102, 2058, 2043</p>

							<p>
							<a href=\"uaip@ugto.mx\">uaip@ugto.mx</a><br>
							<a href=\"m.contrerasortiz@ugto.mx\">m.contrerasortiz@ugto.mx</a>
							</p>
							<p>Horario de Atención: 8:30 am. a 3:30 pm.</p>
                             </center>
              ", 'text/html');
              //@$result = $mailer->send($message);
              
              $id_tipo_error=1;
              $mensaje="El correo ha sido reenviado.";
            }
            catch(Exception $ex)
            {
                $id_tipo_error=2;
                $mensaje="Ocurrio un error:".utf8_encode($ex->getMessage());
            }
             
            $r=array("id_tipo_error"=>$id_tipo_error,"mensaje"=>$mensaje);

            echo json_encode($r);             
        }

        

public function AutocompletaEmp()
    {
        $this->load->model("Solicitud_model", "solicitud");
        if (isset($_GET['term'])){
                $a = strtolower($_GET['term']);
                $this->solicitud->getAutocompletaEmp($a);
        }
    }

    public function GuardarEmp()
    {
       $id_jefe= number_format($this->input->post('id'),0,'','');
       $resp = json_decode($this->input->post('cadena_emp'));
       $this->load->model("Solicitud_model", "solicitud");
       $tipo_error=1;
       $mensaje="los cambios fueron efectuados de manera satisfactoria.";
       
       foreach ($resp as $d)
                {
                   $dataEmp = array(
                   
                   'id_empleado' =>$d->id_empleado,
                   'nombre_empleado' => $d->nombre_completo,
                   'id_unidad_enlace_fk' => $d->id_unidad
                    );
                  $this->db->insert('uaip_jefe_unidades',$dataEmp);
                }
        echo json_encode(array("mensaje"=>$mensaje,"tipo_error"=>$tipo_error));
    }
    
    public function ObEmpleado()
    {
        $this->load->model("Solicitud_model", "solicitud");
        $id_unidad = $this->input->post('id_unidad');
        $respuesta=$this->solicitud->obtenerEmpleado();
        
        $data=array();
        foreach ($respuesta as $r)
        {        
                $data[]=array("id_empleado"=>$r->id_empleado,
                    "nombre_completo"=>$r->nombre_empleado,
                    "id_unidad"=>$r->id_unidad_enlace_fk);   
        }
        echo json_encode($data);
    }
    
        public function ReenviarCorreoUnidad()
	{
		$this->load->model("Solicitud_model", "solicitud");
                $id_solicitud = $_GET['id_solicitud'];
		$query = $this->solicitud->getUnidadProceso($id_solicitud);
                //$query = $this->solicitud->getUnidadesEnlace();
		$data['unidades_enlace'] =$query;

		$data['content'] = 'vCorreoUnidad';
		$data['id_solicitud'] = $this->input->get('id_solicitud');
		$data = array_merge($this->dataDefault, $data);
		$this->load->view($data['layout'], $data);

	}
        
        
        public function obtener_usuarios()
	{
            $this->load->model("Solicitud_model", "solicitud");
            if (isset($_GET['term'])){
                $q = strtolower($_GET['term']);
                $this->solicitud->getUsuarios($q);
            }
        }
       

        public function guardarRespSeccionUnidades()
	{
            $this->load->model("Solicitud_model", "solicitud");
            $id_unidad = $this->input->post('id_unidad');
            $usuarios = json_decode($this->input->post('datos'));
       
            $respuesta=$this->solicitud->guardaRespSeccionUnidades($id_unidad,$usuarios);
            echo json_encode($respuesta);
        }
        
        public function obtenerRespSeccionUnidades()
	{
            $this->load->model("Solicitud_model", "solicitud");
            $id_unidad = $this->input->post('id_unidad');
       
            $respuesta=$this->solicitud->obtenerRespSeccionUnidades($id_unidad);
            
            $data=array();
            $id_usuario_pk_array=array();
            foreach ($respuesta as $r)
            {
                if(!in_array(number_format($r->id_usuario_pk,0,'',''), $id_usuario_pk_array))
                {
                    $data[]=array("id_unidad_responsable_pk"=>number_format($r->id_unidad_responsable_pk,0,'',''),"id_usuario"=>number_format($r->id_usuario_pk,0,'',''),"nombre_usuario"=>($r->nombre),"ban_en_uso"=>  number_format($r->en_uso,0,'',''));
                    $id_usuario_pk_array[]=number_format($r->id_usuario_pk,0,'','');
                }
                
            }
            echo json_encode($data);
        }
        
        public function obtenerSolicitudesEntrantes()
        {
            $this->load->model("Solicitud_model", "solicitud");
            $numero_solicitudes = $this->input->post('numero_solicitudes');
       
            $r=$this->solicitud->getObtenerSolicitudesEntrantes();
           


            $data=array("numero_solicitudes"=>number_format($r->numero_solicitudes,0,'',''));

            echo json_encode($data);
        }
}



/* End of file inicio.php */
/* Location: ./application/controllers/inicio.php */