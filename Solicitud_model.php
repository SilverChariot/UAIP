<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Solicitud_model extends CI_Model {

    function __construct()
    {
        parent::__construct();


         $this->db = $this->load->database('test', TRUE);
//         $this->db_cunico = $this->load->database('c_unico', TRUE);
//         $this->db_escolar = $this->load->database('escolar', TRUE);

    }

    function buscar($palabras_clave = array())
    {
        /* Configuración */
        $max_items_busqueda = 5;
        $solicitudes_encontradas = array('solicitudes' => array(), 'ids' => array());
        $solicitudes_etiqueta_result = array();
        $id_solicitudes_encontradas = array();
        $id_solicitudes_result = array();

        /*
           Realizar la búsqueda en la BD:
           1. Buscar en las etiquetas
              1.1 Buscar las solicitudes que tengan relacionadas esas etiquetas.
           2. Buscar en el campo contenido de solicitud
           3. Mezclar los resultados de las solicitudes y solicitudes relacionadas con etiquetas
           4. Hacer un barrido del total de coincidencias y ordenar por prioridad.
           5. Buscar las solicitudes

           Nota: Se debe moditicar la "intercalación" de las tablas por Modern_Spanish_CI_AI para
           ignorar los acentos en las búsquedas.

        */

        /* 1. Buscar en las etiquetas */

        $this->db->select('id_tag');
        foreach($palabras_clave as $palabra_clave):
            $this->db->or_like('dsc_tag', $palabra_clave);
        endforeach;
        $etiquetas_result = $this->db->get('uaip_tag')
                                          ->result();
        if($etiquetas_result):

          $ids_etiquetas = array();
          foreach($etiquetas_result as $etiqueta_result):
            $ids_etiquetas[] = $etiqueta_result->id_tag;
          endforeach;

          /* 1.1 Buscar las solicitudes que tengan relación con esas etiquetas. */

          $this->db->select('id_solicitud')
                  // ->limit($max_items_busqueda)
                   ->where_in('id_tag', $ids_etiquetas);
          $solicitudes_etiqueta_result =  $this->db->get('uaip_tag_solicitud')->result();

        endif;

        /* 2. Buscar en el campo contenido de solicitud */

        $this->db->select('id_solicitud');
                 //->limit($max_items_busqueda);
        foreach($palabras_clave as $palabra_clave):
            $this->db->or_like('contenido_publico', $palabra_clave);
        endforeach;
        $solicitudes_result = $this->db->get('uaip_solicitud')->result();

        /* 3 Mezclar los resultados de las solicitudes y solicitudes relacionadas con etiquetas. */

        if($solicitudes_etiqueta_result):
          foreach($solicitudes_etiqueta_result as $solicitud_etiqueta):
            $id_solicitudes_result[] = $solicitud_etiqueta->id_solicitud;
          endforeach;
        endif;

        if($solicitudes_result):
          foreach($solicitudes_result as $solicitud_result):
            $id_solicitudes_result[] = $solicitud_result->id_solicitud;
          endforeach;

        endif;

        /* 4. Hacer un barrido del total de coincidencias y ordenar por prioridad.*/

        if($id_solicitudes_result):

          $solicitudes_prioridad = array();
          foreach($id_solicitudes_result as $id_solicitud):
            if(!isset($solicitudes_prioridad[$id_solicitud])):
              $solicitudes_prioridad[$id_solicitud] = 1;
            else:
              $solicitudes_prioridad[$id_solicitud] += 1;
            endif;
          endforeach;

          arsort($solicitudes_prioridad);
          $all_id_solicitudes_encontradas = $id_solicitudes_result;
          $solicitudes_prioridad = array_slice($solicitudes_prioridad, 0, $max_items_busqueda, true); // Ordenamiento de mayor a menor (Descendente).

          foreach($solicitudes_prioridad as $id_solicitud => $solicitud_prioridad):
            $id_solicitudes_encontradas[] = $id_solicitud;
          endforeach;

        endif;

        /* 5. Buscar las solicitudes. */

        if($id_solicitudes_encontradas):

          $this->db->select('us.id_solicitud, us.contenido_publico, (SUBSTRING(us.contenido_publico,0,200) + \'...\') as contenido_extracto, ut.dsc_tema')
                   ->from('uaip_solicitud us')
                   ->join('uaip_tema ut', 'us.id_tema = ut.id_tema')
                   ->where_in('id_solicitud', $id_solicitudes_encontradas);
          $solicitudes_encontradas['solicitudes'] = $this->db->get()->result();
          $solicitudes_encontradas['ids'] = $all_id_solicitudes_encontradas;

        endif;

        return $solicitudes_encontradas;

    }

    function getAllData($start,$limit,$sidx,$sord,$where,$page){

	    //$this->db->select('id_solicitud,contenido,id_medio_respuesta,fecha_ingreso,fecha_limite_entrega,id_estatus');
	    /*Se hace así porque el sql server chafa no soporta limit ni offset */
	    $query = $this->db->query("SELECT TOP ".$limit." * FROM(SELECT ROW_NUMBER() OVER ( ORDER BY ".$sidx." ".$sord.") as id_column,
	     * FROM vw_uaip_info_solicitudes where ".$where.") as table1 where table1.id_column > ".($page-1)*$limit." ");
            
            
            /*$query=$this->db->query("SELECT * FROM (SELECT TOP ".$limit." * FROM (SELECT TOP ".($limit * ($page))." * FROM vw_uaip_info_solicitudes "
            . "WHERE ".$where." ORDER BY id_solicitud desc) AS inner_tbl ORDER BY inner_tbl.id_solicitud ASC) "
            . "AS outer_tbl ORDER BY outer_tbl.id_solicitud DESC");*/
            
           // echo $query;
	    //$this->db->from('uaip_solicitud');
	    //$this->db->limit($limit);//$start
	    //$this->db->offset($start);//$start

	    /*if($where != NULL)
	        $this->db->where($where,NULL,FALSE);
	    $this->db->order_by($sidx,$sord);*/
	   // $query = $this->db->get('uaip_solicitud', $limit, $start);
	    //$query = $this->db->get();

	    return $query->result();

	}

	 function getAllDataMultiple($start,$limit,$sidx,$sord,$where,$page){
	    $query = $this->db->query("SELECT TOP ".$limit." * FROM(SELECT ROW_NUMBER() OVER ( ORDER BY ".$sidx." ".$sord.") as id_column,
	     * FROM vw_uaip_info_multi_solicitudes where ".$where.") as table1 where table1.id_column > ".($page-1)*$limit." ");
            
            /* $query=$this->db->query("SELECT * FROM (SELECT TOP ".$limit." * FROM (SELECT TOP ".($limit * ($page))." * FROM vw_uaip_info_multi_solicitudes "
            . "WHERE ".$where." ORDER BY id_solicitud desc) AS inner_tbl ORDER BY inner_tbl.id_solicitud ASC) "
            . "AS outer_tbl ORDER BY outer_tbl.id_solicitud DESC");*/
                     

	    return $query->result();

	}
	function InsertarProrroga($id_solicitud,$fecha_limite){
     $dataUpdate = array(
                         'fecha_limite_prorroga' => date('Y-d-m',strtotime($fecha_limite))
                     );

                     $this->db->where('id_solicitud', $id_solicitud);
                     $this->db->update('uaip_solicitud', $dataUpdate);
          return 1;


  }
function getTitularEnlace($id_unidad){
    $where = "id_tipo_usuario = 4 and id_unidad_enlace = ".$id_unidad;
    $this->db->select('*');
    $this->db->from('uaip_usuario');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();

    return $query->row();


  }
  function getResponsableEnlace($id_unidad)
  {
          $where = "id_tipo_usuario = 2 and id_unidad_enlace = ".$id_unidad;
          $this->db->select('*');
          $this->db->from('uaip_usuario');
          if($where != NULL)
          $this->db->where($where,NULL,FALSE);
          $query = $this->db->get();

          return $query->row();

  }

  function ActualizarEstatus($id_solicitud, $estatus){
               $dataUpdate = array(
                   'id_estatus' => $estatus
               );

               $this->db->where('id_solicitud', $id_solicitud);
               $this->db->update('uaip_solicitud', $dataUpdate);
    return 1;
  }
  function ActualizarFechaLimiteNueva($id_solicitud,$fecha_limite,$fecha_ingreso,$fecha_limite_anterior){
    $dataUpdate = array(
                   'fecha_limite_entrega' => date('Y-d-m',strtotime($fecha_limite)),
                   'fecha_ingreso' => date('Y-d-m',strtotime($fecha_ingreso)),
                    'fecha_limite_entrega_anterior' => date('Y-d-m',strtotime($fecha_limite_anterior))
               );

               $this->db->where('id_solicitud', $id_solicitud);
               $this->db->update('uaip_solicitud', $dataUpdate);
    return 1;

  }
  function LiberarSolicitud($id_solicitud,$estatus,$observaciones_liberar,$tema,$tipo_info,$fecha_limite){
    if($fecha_limite == 0){
       $dataUpdate = array(
                   'id_estatus' => $estatus,
                    'respuesta' => $observaciones_liberar,
                    'id_tema' => $tema,
                    'id_tipo_info' => $tipo_info

               );

    }else{
     $dataUpdate = array(
                   'id_estatus' => $estatus,
                    'respuesta' => $observaciones_liberar,
                    'id_tema' => $tema,
                    'id_tipo_info' => $tipo_info,
                    'fecha_limite_pagar' =>date('Y-d-m',strtotime($fecha_limite))
               );
       }
               $this->db->where('id_solicitud', $id_solicitud);
               $this->db->update('uaip_solicitud', $dataUpdate);

    return 1;

  }

  function GuardarHistorial($id_solicitud,$estatus,$unidad_enlace,$observaciones_cana){

              $dataHistorial = array(
                   'id_estatus' => $estatus,
                   'id_solicitud' => $id_solicitud,
                   'fecha' => date('Y-d-m H:i:s'),
                   'id_unidad_enlace' => intval($unidad_enlace),
                   'ip' => getenv('REMOTE_ADDR'),
                   'observaciones' => "'{$observaciones_cana}'"
               );

              //$query = $this->db->query("insert into uaip_historial values(".$id_solicitud.", ".$estatus." ,'".date('Y-m-d H:i:s')."', ".$unidad_enlace." ,'".getenv('REMOTE_ADDR')."' ,'".utf8_decode($observaciones_cana)."')");


               $this->db->insert('uaip_historial',$dataHistorial);
    return 1;
  }
  function GuardarHistorialEntregada($id_solicitud,$estatus,$unidad_enlace,$observaciones_cana,$fecha_condicionada){
    $dataHistorial = array(
                   'id_estatus' => $estatus,
                   'id_solicitud' => $id_solicitud,
                   'fecha' => date('Y-d-m H:i:s',strtotime($fecha_condicionada)),
                   'id_unidad_enlace' => intval($unidad_enlace),
                   'ip' => getenv('REMOTE_ADDR'),
                   'observaciones' => "'{$observaciones_cana}'"
               );

              //$query = $this->db->query("insert into uaip_historial values(".$id_solicitud.", ".$estatus." ,'".date('Y-m-d H:i:s')."', ".$unidad_enlace." ,'".getenv('REMOTE_ADDR')."' ,'".utf8_decode($observaciones_cana)."')");


               $this->db->insert('uaip_historial',$dataHistorial);
    return 1;

  }
  function GuardarUnidad($id_solicitud,$unidad_enlace){
              $dataUnidad = array(

                   'id_solicitud' => $id_solicitud,
                   'id_unidad_enlace' => number_format($unidad_enlace,0,"","")
               );
               $this->db->insert('uaip_unidad_enlace_sol', $dataUnidad);
    return 1;
  }
  function checarCanalizada($id_solicitud,$unidad_enlace){
    $where = "id_solicitud =".$id_solicitud." and id_unidad_enlace =".$unidad_enlace;
    $this->db->select('*');
    $this->db->from('uaip_unidad_enlace_sol');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();

    return $query->row();
  }

  function getNoHabiles($FechaFinal){

    $where = "CONVERT(VARCHAR(10), fecha_festivo, 120) = '".date('Y-m-d',strtotime($FechaFinal))."'";
    $this->db->select('id_festivo, fecha_festivo');
    $this->db->from('uaip_dias_festivos');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();

    return $query->row();


  }
  function getFechasInhabiles(){
    $where = "CONVERT(VARCHAR(10), fecha_festivo, 120) > '".date('Y-m-d')."'";
    $this->db->select('id_festivo, fecha_festivo');
    $this->db->from('uaip_dias_festivos');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();

   return $query->result();

  }
  function getNoHabilesReporte($fecha){
    $where = "CONVERT(VARCHAR(10), fecha_festivo, 120) = '".$fecha."'";
    $this->db->select('id_festivo, fecha_festivo');
    $this->db->from('uaip_dias_festivos');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();
    //die(print($query->fecha));    
   
   return $query->row();
  }
  function insertFechasInhabiles($fecha){
      $dataRepre = array(
                   'fecha_festivo' => date('Y-d-m',strtotime($fecha))
               );
               $this->db->insert('uaip_dias_festivos',$dataRepre);
               return 1;
  }


  function getUnaSolicitud($where)
  {
            $this->db->select('id_solicitud,contenido,dsc_tipo_medio,fecha_ingreso,fecha_limite_entrega,fecha_limite_completar,fecha_limite_pagar,dsc_estatus,id_estatus,respuesta,id_usuario,dsc_tipo_solicitud,id_medio_respuesta,fecha_limite_prorroga,fecha_recurso,observaciones,recurso_activo');
            $this->db->from('vw_uaip_info_solicitudes');
            if($where != NULL)
            $this->db->where($where,NULL,FALSE);
            $query = $this->db->get();

            return $query->row();
  }
       
  function getUnidadesRespuesta($where)
  {
	    $query = $this->db->query("select nombre from uaip_unidad_enlace where id_unidad_enlace in (select id_unidad_enlace from uaip_historial where $where)");
	    $unidades= $query->result();           
            
            $cad="";
            foreach ($unidades as $u)
            $cad=$cad.($u->nombre).",";
            
            return substr($cad, 0,-1);
  }
  
  function getUnaHistorial($where){
    $this->db->select('fecha,id_unidad_enlace');
    $this->db->from('uaip_historial');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();

    return $query->row();

  }
  function getDatosUsuario($id_usuario){
    $where = "id_usuario = ".$id_usuario;
    $this->db->select('*');
    $this->db->from('uaip_usuario');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();

    return $query->row();

  }
  function getUnaSolicitudHistorial($where){

    $this->db->select('id_solicitud,fecha_ingreso,dsc_estatus,id_estatus,contenido_publico,respuesta_publica,fecha_recurso,observaciones,recurso_activo');
    $this->db->from('vw_uaip_info_solicitudes');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();

    return $query->row();

  }
  function getUsuarioSolicitud($id_usuario){
    $where = 'id_tipo_usuario = 1 and id_usuario ='.$id_usuario;
    $this->db->select('ap_paterno,ap_materno, nombre_usuario,calle,colonia,ciudad,estado,pais,id_tipo_persona,razon_social,curp,clave_tel,numero_tel,email,id_sexo,ocupacion');
    $this->db->from('uaip_usuario');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();

    return $query->row();

  }
  function getUsuarioSolicitudDetalle($id_usuario,$mi_tipo_usuario){
    $where = 'id_tipo_usuario = '.$mi_tipo_usuario.' and id_usuario ='.$id_usuario;
    $this->db->select('ap_paterno,ap_materno, nombre_usuario,calle,colonia,ciudad,estado,pais,id_tipo_persona,razon_social,curp,clave_tel,numero_tel,email,id_sexo,ocupacion');
    $this->db->from('uaip_usuario');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();

    return $query->row();

  }
  function getTipoUsuario($id_tipo_usuario){
    $where = 'id_tipo_usuario ='.$id_tipo_usuario;
    $this->db->select('*');
    $this->db->from('uaip_tipo_usuario');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();

    return $query->row();

  }

  function getUnidadEnlace($where){

    $this->db->select('*');
    $this->db->from('vw_uaip_solicitud_unidad');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();

    return $query->result();


  }
  function getNivelEducativo(){
    $where = "";
    $this->db->select('*');
    $this->db->from('uaip_nivel_educativo');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();

    return $query->result();

  }
  function getCompruebaUsuario($usuario ){
     $where = "usuario = '".$usuario."'";
    $this->db->select('usuario');
    $this->db->from('uaip_usuario');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();

    return $query->row();

  }
	function getHistorialRespuesta($where){

		$this->db->select('id_estatus,fecha,observaciones,id_unidad_enlace');
		$this->db->from('uaip_historial');
                $this->db->order_by("fecha", "desc");
		if($where != NULL)
	        $this->db->where($where,NULL,FALSE);
		$query = $this->db->get();

		return $query->result();

	}
  function getVistaHistorial($where){
    $this->db->select('*');
    $this->db->from('vw_uaip_historial');
    $this->db->order_by("fecha", "asc");
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();

    return $query->result();

  }

	function getFlujoSolicitud($where){

		$this->db->select('id_estatus,id_estatus_siguiente');
		$this->db->from('uaip_flujo');
		if($where != NULL)
	        $this->db->where($where,NULL,FALSE);
		$query = $this->db->get();

		return $query->result();

	}
	function getUnidadesEnlace(){
		$where = "activo = 1";
		$this->db->select('id_unidad_enlace,sigla,nombre');
		$this->db->from('uaip_unidad_enlace');
		if($where != NULL)
	        $this->db->where($where,NULL,FALSE);
		$query = $this->db->get();

		return $query->result();

	}
  function getUnidadesEnlaceTodo($where = ""){
    $this->db->select('id_unidad_enlace,sigla,nombre,activo');
    $this->db->from('uaip_unidad_enlace');
    if($where != NULL)
    {
        $this->db->where($where,NULL,FALSE);
        $this->db->order_by('nombre');
    }
    $query = $this->db->get();

    return $query->result();

  }
  function getUnidadEnlaceUna($id_unidad){
    $where = "id_unidad_enlace=".$id_unidad;
    $this->db->select('*');
    $this->db->from('uaip_unidad_enlace');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();

    return $query->row();

  }
  function getTemas(){
    $where = "id_tema <= 15";
    $this->db->select('id_tema,dsc_tema');
    $this->db->from('uaip_tema');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();

    return $query->result();

  }
  function getTipoInfo(){
    $where = "";
    $this->db->select('id_tipo_info,dsc_tipo_info');
    $this->db->from('uaip_tipo_info');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();

    return $query->result();
  }

	function getLogin($usuario,$clave){
		$clave = md5($clave);
		$where = "usuario = '".$usuario."' and contrasena = '".$clave."'";
		$this->db->select('*');
		$this->db->from('uaip_usuario');
		if($where != NULL)
	        $this->db->where($where,NULL,FALSE);
		$query = $this->db->get();

		return $query->row();

	}
  function getLoginDiablo($usuario){
      
    $where = "usuario = '".$usuario."'";
    $this->db->select('*');
    $this->db->from('uaip_usuario');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();
  
    return $query->row();

  }
  function getPais(){

    $where = "estatus = 1";
    $this->db_cunico->select('idpais,dscpais');
    $this->db_cunico->from('view_pais');
    $this->db_cunico->order_by("dscpais", "asc");
    if($where != NULL)
          $this->db_cunico->where($where,NULL,FALSE);
    $query = $this->db_cunico->get();

    return $query->result();

  }
  function getEstado($id_pais){

    $where = "estatus = 1 and idpais = ".$id_pais;
    $this->db_cunico->select('idestado,dscestado');
    $this->db_cunico->from('view_estado');
    if($where != NULL)
          $this->db_cunico->where($where,NULL,FALSE);
    $query = $this->db_cunico->get();

    return $query->result();

  }
  function getEstados(){

    $where = "estatus = 1";
    $this->db_cunico->select('idestado,dscestado');
    $this->db_cunico->from('view_estado');
    if($where != NULL)
          $this->db_cunico->where($where,NULL,FALSE);
    $query = $this->db_cunico->get();

    return $query->result();

  }
  function getUnPais($idpais){
    $where = "idpais = ".$idpais;
    $this->db_cunico->select('idpais,dscpais');
    $this->db_cunico->from('view_pais');
    if($where != NULL)
          $this->db_cunico->where($where,NULL,FALSE);
    $query = $this->db_cunico->get();

   return $query->row();
  }
  function getUnEstado($idestado){
    $where = "idestado = ".$idestado;
    $this->db_cunico->select('idestado,dscestado');
    $this->db_cunico->from('view_estado');
    if($where != NULL)
          $this->db_cunico->where($where,NULL,FALSE);
    $query = $this->db_cunico->get();

    return $query->row();
  }
  function getUnTipoPersona($tipo_persona){
     $where = "id_tipo_persona = ".$tipo_persona;
    $this->db->select('id_tipo_persona,dsc_tipo_persona');
    $this->db->from('uaip_tipo_persona');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();

    return $query->row();

  }
  function getRepresentante($repre){
     $where = "id_representante = ".$repre;
    $this->db->select('nombre_completo,ap_paterno,ap_materno');
    $this->db->from('uaip_representante');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();

    return $query->row();

  }
  function getTipoSolicitud(){
    $where = "";
    $this->db->select('id_tipo_solicitud,dsc_tipo_solicitud');
    $this->db->from('uaip_tipo_solicitud');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();

    return $query->result();
  }
  function getMedioRespuesta(){
    $where = "";
    $this->db->select('id_tipo_medio,dsc_tipo_medio');
    $this->db->from('uaip_tipo_medio');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();

    return $query->result();

  }
  function getMedioRespuestaUna($id_medio_respuesta){
     $where = "id_medio_respuesta =".$id_medio_respuesta;
    $this->db->select('*');
    $this->db->from('uaip_medio_respuesta');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();

    return $query->row();

  }
  function getMedioEnvio(){
    $where = "";
    $this->db->select('id_tipo_envio,dsc_tipo_envio');
    $this->db->from('uaip_medio_tipo_envio');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();

    return $query->result();
  }
  function getTipoMedio(){
    $where = "";
    $this->db->select('*');
    $this->db->from('uaip_tipo_medio');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();

    return $query->result();

  }
  function getArchivosEnlace($where){
      $this->db->select('*');
      $this->db->from('uaip_adjuntos');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();

    return $query->result();
  }
  function getArchivosRespuesta($where){
      $this->db->select('*');
      $this->db->from('uaip_adjuntos');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();

    return $query->result();
  }
  function getTags($q){


    $this->db->select('top 10 *');
    $this->db->like('dsc_tag', $q);
    $query = $this->db->get('uaip_tag');
    if($query->num_rows > 0){
      foreach ($query->result_array() as $row){
        //$new_row['label']=htmlentities(stripslashes($row['dsc_tag']));
        //$new_row['value']=htmlentities(stripslashes($row['dsc_tag']));
        $row_set[]=(stripslashes($row['dsc_tag']));
        //$row_set[] = $new_row; //build an array
      }
      echo json_encode($row_set); //format the array into json data
    }

   /* $where = "";
      $this->db->select('*');
      $this->db->from('uaip_tag');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();

    return $query->result(); */
  }
  
  
  
    function getTags2()
    {
        $this->db->select('*');
        //$this->db->like('dsc_tag', $q);
        $query = $this->db->get('uaip_tag');

        $tags="";
        if($query->num_rows > 0){
          foreach ($query->result_array() as $row){
            $tags=$tags.'"'.htmlentities(stripslashes($row['dsc_tag'])).'",';
          }
          return substr($tags, 0,-1); //format the array into json data
        }
    }


  function NuevoUsuario($nombre_usuario,$ap_paterno, $ap_materno,$calle,$colonia, $pais,$estado,$ciudad,$tipo_persona, $razon,
    $sexo, $edad, $curp, $ocupacion, $telefono, $correo, $autorizacion, $usuario, $contrasenia,$lada,$nivel_educativo){

    $dataRegistro = array(
                   'nombre_usuario' => $nombre_usuario,
                   'ap_paterno' => $ap_paterno,
                   'ap_materno' => $ap_materno,
                   'calle' => $calle,
                   'colonia' => $colonia,
                   'pais' => $pais,
                   'estado' => $estado,
                   'ciudad' => $ciudad,
                   'id_tipo_persona' => $tipo_persona,
                   'razon_social' => $razon,
                   'id_sexo' => $sexo,
                   'edad' => $edad,
                   'curp' => $curp,
                   'ocupacion' => $ocupacion,
                   'clave_tel' => $lada,
                   'numero_tel' => $telefono,
                   'email' => $correo,
                   'autorizacion' => $autorizacion,
                   'usuario' => $usuario,
                   'contrasena' => md5($contrasenia),
                   'inicio_sesion' => 1,
                   'id_tipo_usuario' => 1,
                   'estatus'=> 1,
                   'id_nivel_educativo'=> $nivel_educativo


               );


               $this->db->insert('uaip_usuario',$dataRegistro);


               $where = "";
               $this->db->select('top 1 *');
               $this->db->order_by("id_usuario", "desc");
               $this->db->from('uaip_usuario');

               if($where != NULL)
               $this->db->where($where,NULL,FALSE);

               $query = $this->db->get();

               return $query->row();


  }
  function InsertaMedioRespuesta($tipo_solicitud,$contenido_sol,$medio_respuesta,$tipo_envio){


                $dataMedioRespuesta = array(

                   'id_tipo_envio' => number_format($tipo_envio,0,"",""),
                   'id_tipo_medio' => number_format($medio_respuesta,0,"","")

               );
               $this->db->insert('uaip_medio_respuesta', $dataMedioRespuesta);

               $where = "";
               $this->db->select('top 1 *');
               $this->db->order_by("id_medio_respuesta", "desc");
               $this->db->from('uaip_medio_respuesta');

               if($where != NULL)
               $this->db->where($where,NULL,FALSE);

               $query = $this->db->get();

               return $query->row();

  }
  function getIdSolicitud(){    
    $this->db->select('top 1 *');
    $this->db->order_by("id_solicitud", "desc");
    $this->db->from('uaip_solicitud');
    $query = $this->db->get();
    return $query->row()->id_solicitud;
  }
  function InsertaSolicitud($id_medio_respuesta,$contenido_sol,$tipo_solicitud,$fecha_limite,$fecha_ingreso){
      $id_solicitud=$this->getIdSolicitud() + 1;      
      $dataSolicitud = array(
                   'id_solicitud' => $id_solicitud,
                   'id_estatus' => 1, 
                   'id_tipo_solicitud' => number_format($tipo_solicitud,0,"",""),
                   'id_origen' => 1,
                   'id_medio_respuesta' => number_format($id_medio_respuesta,0,"",""),
                   'contenido' => $contenido_sol,
                   'fecha_ingreso' => date('Y-d-m',strtotime($fecha_ingreso)),
                   'fecha_limite_entrega' => date('Y-d-m',strtotime($fecha_limite)),
                   'id_usuario' => $this->session->userdata('id_usuario')

               );
               $this->db->insert('uaip_solicitud', $dataSolicitud);

              $where = "";
               $this->db->select('top 1 *');
               $this->db->order_by("id_solicitud", "desc");
               $this->db->from('uaip_solicitud');

               if($where != NULL)
               $this->db->where($where,NULL,FALSE);

               $query = $this->db->get();

               return $query->row();


  }
  function insertaRepresentante($representante_tiene,$nombre_repre,$paterno_repre,$materno_repre,$id_usuario)
  {
              $dataRepre = array(
                   'nombre_completo' => $nombre_repre,
                   'ap_paterno' => $paterno_repre,
                   'ap_materno' => $materno_repre,
                   'id_usuario' => $id_usuario

               );

               $this->db->insert('uaip_representante',$dataRepre);


               return 1;



  }
  function FechaLimiteCompletar($id_solicitud,$fecha_limite){

       $dataUpdate = array(
                         'fecha_limite_completar' => date('Y-d-m',strtotime($fecha_limite)),
                         "fecha_marcada_incompleta"=> date('Y-d-m H:i:s')
                     );

                     $this->db->where('id_solicitud', $id_solicitud);
                     $this->db->update('uaip_solicitud', $dataUpdate);
          return 1;
  }
  function ActualizarUsuario($id_usuario,$nombre,$paterno,$materno,$calle,$colonia,$ciudad,$pais,$estados,$tipo_persona,$razon,$curp,$lada,$telefono,$correo,$ocupacion){
    $dataRegistro = array(
                   'nombre_usuario' => $nombre,
                   'ap_paterno' => $paterno,
                   'ap_materno' => $materno,
                   'calle' => $calle,
                   'colonia' => $colonia,
                   'pais' => $pais,
                   'estado' => $estados,
                   'ciudad' => $ciudad,
                   'id_tipo_persona' => $tipo_persona,
                   'razon_social' => $razon,
                   'curp' => $curp,
                   'ocupacion' => $ocupacion,
                   'clave_tel' => $lada,
                   'numero_tel' => $telefono,
                   'email' => $correo
               );
            $this->db->where('id_usuario', $id_usuario);
            $this->db->update('uaip_usuario', $dataRegistro);
          return 1;

  }
  function actualizarContra($id_usuario,$contrasenia){
    $dataRegistro = array(
                   'contrasena' => md5($contrasenia)
               );
            $this->db->where('id_usuario', $id_usuario);
            $this->db->update('uaip_usuario', $dataRegistro);
          return 1;

  }
  function UpdateMedioRespuesta($id_medio_respuesta,$costo_envio,$costo_total,$tipo_medio,$fecha_limite,$no_copias){
     $dataUpdate = array(
                        'id_tipo_medio' => $tipo_medio,
                        'costo_total' => $costo_total,
                        'costo_envio' => $costo_envio,
                        'fecha_limite_medio' => date('Y-d-m',strtotime($fecha_limite)),
                        'no_copias' => $no_copias,
                     );

                     $this->db->where('id_medio_respuesta', $id_medio_respuesta);
                     $this->db->update('uaip_medio_respuesta', $dataUpdate);
          return 1;

  }
  function AgregarArchivos($id_solicitud,$id_tipo_adjunto,$nombreArchivo,$id_unidad){

    $dataArchivo = array(
                   'id_tipo_adjunto' => $id_tipo_adjunto,
                   'id_solicitud' => $id_solicitud,
                   'nombre_adjunto' => $nombreArchivo,
                   'id_unidad' => $id_unidad
               );
               $this->db->insert('uaip_adjuntos',$dataArchivo);

               return $this->db->insert_id();

  }
  function AgregarEtiqueta($etiqueta){
    $dataEtiqueta = array(
                   'dsc_tag' => $etiqueta
               );
               $this->db->insert('uaip_tag',$dataEtiqueta);

               $where = "";
               $this->db->select('top 1 *');
               $this->db->order_by("id_tag", "desc");
               $this->db->from('uaip_tag');

               if($where != NULL)
               $this->db->where($where,NULL,FALSE);

               $query = $this->db->get();

               return $query->row();

  }
  function AgregarEtiquetaSolicitud($id_solicitud,$tag){
    $dataEtiqueta = array(
                   'id_solicitud' => $id_solicitud,
                   'id_tag' => $tag
               );
               $this->db->insert('uaip_tag_solicitud',$dataEtiqueta);



               return 1;

  }
  function GuardarTag($etiqueta){

     $dataArchivo = array(
                   'dsc_tag' => $etiqueta
               );

               $this->db->insert('uaip_tag',$dataArchivo);
                $where = "";
               $this->db->select('top 1 *');
               $this->db->order_by("id_tag", "desc");
               $this->db->from('uaip_tag');

               if($where != NULL)
               $this->db->where($where,NULL,FALSE);

               $query = $this->db->get();

               return $query->row();
  }
  function buscarTag($etiqueta){
    $where = "dsc_tag like '".$etiqueta."'";
    $this->db->select('id_tag,dsc_tag');
      $this->db->from('uaip_tag');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();

     return $query->row();
  }
  function GenerarPubSolicitud($id_solicitud,$contenido_publico,$respuesta_publica){
    $dataUpdate = array(
                         'contenido_publico' => $contenido_publico,
                         'respuesta_publica' => $respuesta_publica,
                         'fecha_gen_publica' => date('d-m-Y H:i:s')
                     );

                     $this->db->where('id_solicitud', $id_solicitud);
                     $this->db->update('uaip_solicitud', $dataUpdate);
          return 1;

  }
  function GuardarAnexoC($division, $g_sexo, $edad, $estado_civil, $hijas, $a0_5a, $mas19a, $a6_12a, $a13_15a, $a16_19a, $grado_academico, $otro_grado_academico, $nombramiento, $nombramiento_anos, $nombramiento_meses, $alimentos_horas, $compra_horas, $limpieza_horas, $ropa_horas, $pagos_horas, $cuidado_horas, $transporte_horas, $recreacion_horas, $recreacion_deportes, $cuidado_hijos, $cuidado_ninos, $cuidado_adultos_mayores, $cuidado_enfermas, $cuidado_discapacidad, $cuidado_otras_personas, $cuidado_especificar_otras_personas, $ambiente_laboral, $reconocimiento_esfuerzo, $puesto_actual, $salario_actual, $derechos_laborales, $sindicato, $herramientas, $capacitacion, $discriminacion, $intereses, $inconformidades, $mas_esfuerzo, $exclusion, $especificar_exclusion, $profesores_comentarios_orientacion, $profesores_comentarios_mujeres, $profesores_comentarios_hombres, $profesoras_comentarios_orientacion, $profesoras_comentarios_mujeres, $profesoras_comentarios_hombres, $administrativos_comentarios_orientacion, $administrativos_comentarios_mujeres, $administrativos_comentarios_hombres, $administrativas_comentarios_orientacion, $administrativas_comentarios_mujeres, $administrativas_comentarios_hombres, $cartel, $cartel_quien_fue, $piropos, $piropos_quien_fue, $miradas, $miradas_quien_fue, $burlas, $burlas_quien_fue, $presion, $presion_quien_fue, $mensajes, $mensajes_quien_fue, $amenazas, $amenazas_quien_fue, $roces, $roces_quien_fue, $relaciones, $relaciones_quien_fue, $fuerza_fisica, $fuerza_fisica_quien_fue, $que_hizo, $otra_que_hizo, $que_hizo_nada, $otra_que_hizo_nada, $obstaculos_mujeres, $obstaculos_mujeres_cuales, $obstaculos_hombres, $obstaculos_hombres_cuales)
  {
   $dataEtiqueta = array(
                   //'id_solicitud' => $id_solicitud,
                   'division'=> $division,
                   'g_sexo'=> $g_sexo,
                   'edad'=> $edad,
                   'estado_civil'=> $estado_civil,
                   'hijas'=> $hijas,
                   'a0_5a'=> $a0_5a,
                   'mas19a'=> $mas19a,
                   'a6_12a'=> $a6_12a,
                   'a13_15a'=> $a13_15a,
                   'a16_19a'=> $a16_19a,
                   'grado_academico'=> $grado_academico,
                   'otro_grado_academico'=> $otro_grado_academico,
                   'nombramiento'=> $nombramiento,
                   'nombramiento_anos'=> $nombramiento_anos,
                   'nombramiento_meses'=> $nombramiento_meses,
                   'alimentos_horas'=> $alimentos_horas,
                   'compra_horas'=> $compra_horas,
                   'limpieza_horas'=> $limpieza_horas,
                   'ropa_horas'=> $ropa_horas,
                   'pagos_horas'=> $pagos_horas,
                   'cuidado_horas'=> $cuidado_horas,
                   'transporte_horas'=> $transporte_horas,
                   'recreacion_horas'=> $recreacion_horas,
                   'recreacion_deportes'=> $recreacion_deportes,
                   'cuidado_hijos'=> $cuidado_hijos,
                   'cuidado_ninos'=> $cuidado_ninos,
                   'cuidado_adultos_mayores'=> $cuidado_adultos_mayores,
                   'cuidado_enfermas'=> $cuidado_enfermas,
                   'cuidado_discapacidad'=> $cuidado_discapacidad,
                   'cuidado_otras_personas'=> $cuidado_otras_personas,
                   'cuidado_especificar_otras_personas'=> $cuidado_especificar_otras_personas,
                   'ambiente_laboral'=> $ambiente_laboral,
                   'reconocimiento_esfuerzo'=> $reconocimiento_esfuerzo,
                   'puesto_actual'=> $puesto_actual,
                   'salario_actual'=> $salario_actual,
                   'derechos_laborales'=> $derechos_laborales,
                   'sindicato'=> $sindicato ,
                   'herramientas'=> $herramientas,
                   'capacitacion'=> $capacitacion,
                   'discriminacion'=> $discriminacion,
                   'intereses'=>$intereses,
                   'inconformidades'=>$inconformidades,
                   'mas_esfuerzo'=>$mas_esfuerzo,
                   'exclusion'=>$exclusion,
                   'especificar_exclusion'=>$especificar_exclusion,
                   'profesores_comentarios_orientacion'=>$profesores_comentarios_orientacion,
                   'profesores_comentarios_mujeres'=>$profesores_comentarios_mujeres,
                   'profesores_comentarios_hombres'=>$profesores_comentarios_hombres,
                   'profesoras_comentarios_orientacion'=>$profesoras_comentarios_orientacion,
                   'profesoras_comentarios_mujeres'=>$profesoras_comentarios_mujeres,
                   'profesoras_comentarios_hombres'=>$profesoras_comentarios_hombres,
                   'administrativos_comentarios_orientacion'=>$administrativos_comentarios_orientacion,
                   'administrativos_comentarios_mujeres'=>$administrativos_comentarios_mujeres,
                   'administrativos_comentarios_hombres'=>$administrativos_comentarios_hombres,
                   'administrativas_comentarios_orientacion'=>$administrativas_comentarios_orientacion,
                   'administrativas_comentarios_mujeres'=>$administrativas_comentarios_mujeres,
                   'administrativas_comentarios_hombres'=>$administrativas_comentarios_hombres,
                   'cartel'=>$cartel,
                   'cartel_quien_fue'=>$cartel_quien_fue,
                   'piropos'=>$piropos,
                   'piropos_quien_fue'=>$piropos_quien_fue,
                   'miradas'=>$miradas,
                   'miradas_quien_fue'=>$miradas_quien_fue,
                   'burlas'=>$burlas,
                   'burlas_quien_fue'=>$burlas_quien_fue,
                   'presion'=>$presion,
                   'presion_quien_fue'=>$presion_quien_fue,
                   'mensajes'=>$mensajes,
                   'mensajes_quien_fue'=>$mensajes_quien_fue,
                   'amenazas'=>$amenazas,
                   'amenazas_quien_fue'=>$amenazas_quien_fue,
                   'roces'=>$roces,
                   'roces_quien_fue'=>$roces_quien_fue,
                   'relaciones'=>$relaciones,
                   'relaciones_quien_fue'=>$relaciones_quien_fue,
                   'fuerza_fisica'=>$fuerza_fisica,
                   'fuerza_fisica_quien_fue'=>$fuerza_fisica_quien_fue,
                   'que_hizo'=>$que_hizo,
                   'otra_que_hizo'=>$otra_que_hizo,
                   'que_hizo_nada'=>$que_hizo_nada,
                   'otra_que_hizo_nada'=>$otra_que_hizo_nada,
                   'obstaculos_mujeres'=>$obstaculos_mujeres,
                   'obstaculos_mujeres_cuales'=>$obstaculos_mujeres_cuales,
                   'obstaculos_hombres'=>$obstaculos_hombres,
                   'obstaculos_hombres_cuales'=>$obstaculos_hombres_cuales,
               );
               $this->db->insert('uaip_anexoc',$dataEtiqueta);
               return 1;
  
  }

function GuardarAnexoB($division, $g_sexo, $edad, $estado_civil, $hijas, $a0_5a, $mas19a, $a6_12a, $a13_15a, $a16_19a, $grado_academico, $otro_grado_academico, $programa_academico, $promedio, $beca, $cual_beca, $cual_beca2, $trabaja, $trabajo_dificulta_estudios, $cuidado_hijos, $cuidado_ninos, $cuidado_adultos_mayores, $cuidado_enfermas, $cuidado_discapacidad, $cuidado_otras_personas, $cuidado_otras_personas_especifique, $organos_colegiados, $cuales_organos_colegiados, $comisiones1, $comisiones2, $comisiones3, $comisiones4, $eleccion_carrera, $cual_eleccion_carrera, $alimentos_horas, $compra_horas, $limpieza_horas, $ropa_horas, $pagos_horas, $cuidado_horas, $transporte_horas, $recreacion_horas, $recreacion_deporte, $discriminacion, $exclusion, $exclusion_cual, $profesores_comentarios_orientacion, $profesores_comentarios_mujeres, $profesores_comentarios_hombres, $profesoras_comentarios_orientacion, $profesoras_comentarios_mujeres, $profesoras_comentarios_hombres, $administrativos_comentarios_orientacion, $administrativos_comentarios_mujeres, $administrativos_comentarios_hombres, $administrativas_comentarios_orientacion, $administrativas_comentarios_mujeres, $administrativas_comentarios_hombres, $ideas_hombres, $ideas_mujeres, $tomar_cuenta_hombres, $tomar_cuenta_profesoras, $tomar_cuenta_alumnas, $tomar_cuenta_alumnos, $cartel, $cartel_quien, $piropos, $piropos_quien, $miradas, $miradas_quien, $burlas, $burlas_quien, $presion, $presion_quien, $mensajes, $mensajes_quien, $amenazas, $amenazas_quien, $roces, $roces_quien, $relaciones, $relaciones_quien, $fuerza_fisica, $fuerza_fisica_quien, $que_hizo, $otra_que_hizo, $que_hizo_nada, $otra_que_hizo_nada, $obstaculos_mujeres, $cuales_obstaculos_mujeres, $obstaculos_hombres, $cuales_obstaculos_hombres,$inscripcion_folio, $folio)
  {
   $dataEtiqueta = array(
                   //'id_solicitud' => $id_solicitud,
                    'division' => $division,
                    'g_sexo' => $g_sexo,
                    'edad' => $edad,
                    'estado_civil' => $estado_civil,
                    'hijas' => $hijas,
                    'a0_5a' => $a0_5a,
                    'mas19a' => $mas19a,
                    'a6_12a' => $a6_12a,
                    'a13_15a' => $a13_15a,
                    'a16_19a' => $a16_19a,
                    'grado_academico' => $grado_academico,
                    'otro_grado_academico' => $otro_grado_academico,
                    'programa_academico' => $programa_academico,
                    'promedio' => $promedio,
                    'beca' => $beca,
                    'cual_beca' => $cual_beca,
                    'cual_beca2' => $cual_beca2,
                    'trabaja' => $trabaja,
                    'trabajo_dificulta_estudios' => $trabajo_dificulta_estudios,
                    'cuidado_hijos' => $cuidado_hijos,
                    'cuidado_ninos' => $cuidado_ninos,
                    'cuidado_adultos_mayores' => $cuidado_adultos_mayores,
                    'cuidado_enfermas' => $cuidado_enfermas,
                    'cuidado_discapacidad' => $cuidado_discapacidad,
                    'cuidado_otras_personas' => $cuidado_otras_personas,
                    'cuidado_otras_personas_especifique' => $cuidado_otras_personas_especifique,
                    'organos_colegiados' => $organos_colegiados,
                    'cuales_organos_colegiados' => $cuales_organos_colegiados,
                    'comisiones1' => $comisiones1,
                    'comisiones2' => $comisiones2,
                    'comisiones3' => $comisiones3,
                    'comisiones4' => $comisiones4,
                    'eleccion_carrera' => $eleccion_carrera,
                    'cual_eleccion_carrera' => $cual_eleccion_carrera,
                    'alimentos_horas' => $alimentos_horas,
                    'compra_horas' => $compra_horas,
                    'limpieza_horas' => $limpieza_horas,
                    'ropa_horas' => $ropa_horas,
                    'pagos_horas' => $pagos_horas,
                    'cuidado_horas' => $cuidado_horas,
                    'transporte_horas' => $transporte_horas,
                    'recreacion_horas' => $recreacion_horas,
                    'recreacion_deporte' => $recreacion_deporte,
                    'discriminacion' => $discriminacion,
                    'exclusion' => $exclusion,
                    'exclusion_cual' => $exclusion_cual,
                    'profesores_comentarios_orientacion' => $profesores_comentarios_orientacion,
                    'profesores_comentarios_mujeres' => $profesores_comentarios_mujeres,
                    'profesores_comentarios_hombres' => $profesores_comentarios_hombres,
                    'profesoras_comentarios_orientacion' => $profesoras_comentarios_orientacion,
                    'profesoras_comentarios_mujeres' => $profesoras_comentarios_mujeres,
                    'profesoras_comentarios_hombres' => $profesoras_comentarios_hombres,
                    'administrativos_comentarios_orientacion' => $administrativos_comentarios_orientacion,
                    'administrativos_comentarios_mujeres' => $administrativos_comentarios_mujeres,
                    'administrativos_comentarios_hombres' => $administrativos_comentarios_hombres,
                    'administrativas_comentarios_orientacion' => $administrativas_comentarios_orientacion,
                    'administrativas_comentarios_mujeres' => $administrativas_comentarios_mujeres,
                    'administrativas_comentarios_hombres' => $administrativas_comentarios_hombres,
                    'ideas_hombres' => $ideas_hombres,
                    'ideas_mujeres' => $ideas_mujeres,
                    'tomar_cuenta_hombres' => $tomar_cuenta_hombres,
                    'tomar_cuenta_profesoras' => $tomar_cuenta_profesoras,
                    'tomar_cuenta_alumnas' => $tomar_cuenta_alumnas,
                    'tomar_cuenta_alumnos' => $tomar_cuenta_alumnos,
                    'cartel' => $cartel,
                    'cartel_quien' => $cartel_quien,
                    'piropos' => $piropos,
                    'piropos_quien' => $piropos_quien,
                    'miradas' => $miradas,
                    'miradas_quien' => $miradas_quien,
                    'burlas' => $burlas,
                    'burlas_quien' => $burlas_quien,
                    'presion' => $presion,
                    'presion_quien' => $presion_quien,
                    'mensajes' => $mensajes,
                    'mensajes_quien' => $mensajes_quien,
                    'amenazas' => $amenazas,
                    'amenazas_quien' => $amenazas_quien,
                    'roces' => $roces,
                    'roces_quien' => $roces_quien,
                    'relaciones' => $relaciones,
                    'relaciones_quien' => $relaciones_quien,
                    'fuerza_fisica' => $fuerza_fisica,
                    'fuerza_fisica_quien' => $fuerza_fisica_quien,
                    'que_hizo' => $que_hizo,
                    'otra_que_hizo' => $otra_que_hizo,
                    'que_hizo_nada' => $que_hizo_nada,
                    'otra_que_hizo_nada' => $otra_que_hizo_nada,
                    'obstaculos_mujeres' => $obstaculos_mujeres,
                    'cuales_obstaculos_mujeres' => $cuales_obstaculos_mujeres,
                    'obstaculos_hombres' => $obstaculos_hombres,
                    'cuales_obstaculos_hombres' => $cuales_obstaculos_hombres,
                    'inscripcion' => $inscripcion_folio,
                    'folio' => $folio

                   );
               $this->db->insert('uaip_anexob',$dataEtiqueta);
               return 1;
  
  }

  function GuardarAnexoA($division, $g_sexo, $edad, $estado_civil, $hijas, $a0_5, $maas19, $a6_12, $a13_15, $a16_19, $grado_academico, $otro_grado_academico, $nombramiento, $antiguedad_anos, $antiguedad_meses, $beca, $beca_nivel, $sni, $sni_nivel, $organos_colegiados, $consejos, $comisiones1, $comisiones2, $comisiones3, $comisiones4, $colaboracion, $colaboracion_nacional, $colaboracion_extranjeros, $alimentos, $compra, $limpieza, $ropa, $servicios, $cuidado, $transporte, $recreacion, $deportes, $cuidado_hijos, $cuidado_ninos, $cuidado_adultos_mayores, $cuidado_enfermas, $cuidado_discapacidad, $cuidado_otras_personas, $cuidado_otras_personas2, $ambiente, $reconocimiento, $puesto, $salario, $derechos, $sindicato, $herramientas, $capacitacion, $intereses, $inconformidades, $esfuerzo, $discriminacion, $exclusion, $especifique_exclusion, $evaluacion, $profesores_comentarios_orientacion, $profesores_comentarios_mujeres, $profesores_comentarios_hombres, $profesoras_comentarios_orientacion, $profesoras_comentarios_mujeres, $profesoras_comentarios_hombres, $administrativos_comentarios_orientacion, $administrativos_comentarios_mujeres, $administrativos_comentarios_hombres, $administrativas_comentarios_orientacion, $administrativas_comentarios_mujeres, $administrativas_comentarios_hombres, $cartel, $cartel_quien, $piropos, $piropos_quien, $miradas, $miradas_quien, $burlas, $burlas_quien, $presion, $presion_quien, $mensajes, $mensajes_quien, $amenazas, $amenazas_quien, $roces, $roces_quien, $relaciones, $relaciones_quien, $fuerza_fisica, $fuerza_fisica_quien, $que_hizo, $otra_que_hizo, $que_hizo_nada, $otra_que_hizo_nada, $obstaculos_mujeres, $obstaculos_mujeres_cuales, $obstaculos_hombres, $obstaculos_hombres_cuales)

  {
   $dataEtiqueta = array(
                    //'id_solicitud' => $id_solicitud,
                    'division' => $division,
                    'g_sexo' => $g_sexo,
                    'edad' => $edad,
                    'estado_civil' => $estado_civil,
                    'hijas' => $hijas,
                    'a0_5' => $a0_5,
                    'maas19' => $maas19,
                    'a6_12' => $a6_12,
                    'a13_15' => $a13_15,
                    'a16_19' => $a16_19,
                    'grado_academico' => $grado_academico,
                    'otro_grado_academico' => $otro_grado_academico,
                    'nombramiento' => $nombramiento,
                    'antiguedad_anos' => $antiguedad_anos,
                    'antiguedad_meses' => $antiguedad_meses,
                    'beca' => $beca,
                    'beca_nivel' => $beca_nivel,
                    'sni' => $sni,
                    'sni_nivel' => $sni_nivel,
                    'organos_colegiados' => $organos_colegiados,
                    'consejos' => $consejos,
                    'comisiones1' => $comisiones1,
                    'comisiones2' => $comisiones2,
                    'comisiones3' => $comisiones3,
                    'comisiones4' => $comisiones4,
                    'colaboracion' => $colaboracion,
                    'colaboracion_nacional' => $colaboracion_nacional,
                    'colaboracion_extranjeros' => $colaboracion_extranjeros,
                    'alimentos' => $alimentos,
                    'compra' => $compra,
                    'limpieza' => $limpieza,
                    'ropa' => $ropa,
                    'servicios' => $servicios,
                    'cuidado' => $cuidado,
                    'transporte' => $transporte,
                    'recreacion' => $recreacion,
                    'deportes' => $deportes,
                    'cuidado_hijos' => $cuidado_hijos,
                    'cuidado_ninos' => $cuidado_ninos,
                    'cuidado_adultos_mayores' => $cuidado_adultos_mayores,
                    'cuidado_enfermas' => $cuidado_enfermas,
                    'cuidado_discapacidad' => $cuidado_discapacidad,
                    'cuidado_otras_personas' => $cuidado_otras_personas,
                    'cuidado_otras_personas2' => $cuidado_otras_personas2,
                    'ambiente' => $ambiente,
                    'reconocimiento' => $reconocimiento,
                    'puesto' => $puesto,
                    'salario' => $salario,
                    'derechos' => $derechos,
                    'sindicato' => $sindicato,
                    'herramientas' => $herramientas,
                    'capacitacion' => $capacitacion,
                    'intereses' => $intereses,
                    'inconformidades' => $inconformidades,
                    'esfuerzo' => $esfuerzo,
                    'discriminacion' => $discriminacion,
                    'exclusion' => $exclusion,
                    'especifique_exclusion' => $especifique_exclusion,
                    'evaluacion' => $evaluacion,
                    'profesores_comentarios_orientacion' => $profesores_comentarios_orientacion,
                    'profesores_comentarios_mujeres' => $profesores_comentarios_mujeres,
                    'profesores_comentarios_hombres' => $profesores_comentarios_hombres,
                    'profesoras_comentarios_orientacion' => $profesoras_comentarios_orientacion,
                    'profesoras_comentarios_mujeres' => $profesoras_comentarios_mujeres,
                    'profesoras_comentarios_hombres' => $profesoras_comentarios_hombres,
                    'administrativos_comentarios_orientacion' => $administrativos_comentarios_orientacion,
                    'administrativos_comentarios_mujeres' => $administrativos_comentarios_mujeres,
                    'administrativos_comentarios_hombres' => $administrativos_comentarios_hombres,
                    'administrativas_comentarios_orientacion' => $administrativas_comentarios_orientacion,
                    'administrativas_comentarios_mujeres' => $administrativas_comentarios_mujeres,
                    'administrativas_comentarios_hombres' => $administrativas_comentarios_hombres,
                    'cartel' => $cartel,
                    'cartel_quien' => $cartel_quien,
                    'piropos' => $piropos,
                    'piropos_quien' => $piropos_quien,
                    'miradas' => $miradas,
                    'miradas_quien' => $miradas_quien,
                    'burlas' => $burlas,
                    'burlas_quien' => $burlas_quien,
                    'presion' => $presion,
                    'presion_quien' => $presion_quien,
                    'mensajes' => $mensajes,
                    'mensajes_quien' => $mensajes_quien,
                    'amenazas' => $amenazas,
                    'amenazas_quien' => $amenazas_quien,
                    'roces' => $roces,
                    'roces_quien' => $roces_quien,
                    'relaciones' => $relaciones,
                    'relaciones_quien' => $relaciones_quien,
                    'fuerza_fisica' => $fuerza_fisica,
                    'fuerza_fisica_quien' => $fuerza_fisica_quien,
                    'que_hizo' => $que_hizo,
                    'otra_que_hizo' => $otra_que_hizo,
                    'que_hizo_nada' => $que_hizo_nada,
                    'otra_que_hizo_nada' => $otra_que_hizo_nada,
                    'obstaculos_mujeres' => $obstaculos_mujeres,
                    'obstaculos_mujeres_cuales' => $obstaculos_mujeres_cuales,
                    'obstaculos_hombres' => $obstaculos_hombres,
                    'obstaculos_hombres_cuales' => $obstaculos_hombres_cuales,

                   );
               $this->db->insert('uaip_anexoa',$dataEtiqueta);
               return 1;
  
  }
  function getAlumnosCorreo(){
    $where = "1 = 1";
    $this->db_escolar->select('top 2 *');
    $this->db_escolar->from('vw_control_ingreso_alumno');
    if($where != NULL)
          $this->db_escolar->where($where,NULL,FALSE);
    $query = $this->db_escolar->get();

   return $query->result();

  }
  function getOrientadas($start,$limit,$sidx,$sord,$where,$page){
      
      /*Se hace así porque el sql server chafa no soporta limit ni offset */
      $query = $this->db->query("SELECT TOP ".$limit." * FROM(SELECT ROW_NUMBER() OVER ( ORDER BY ".$sidx." ".$sord.") as id_column,
       * FROM uaip_sol_orientadas where ".$where.") as table1 where table1.id_column > ".($page-1)*$limit." ");
      return $query->result();

  }
  function getUltimaOrientada($ano){
    $where = "ano = ".$ano;
    $this->db->select('top 1 *');
    $this->db->order_by("id_orientada", "desc");
    $this->db->from('uaip_sol_orientadas');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();

   return $query->row();

  }

  function getOrientada($id){
    $where = "id_orientada = ".$id;
    $this->db->select('*');
    //$this->db->order_by("id_orientada", "desc");
    $this->db->from('uaip_sol_orientadas');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();

   return $query->row();

  }
  function getOrientadaTodas(){
    $where = "";
    $this->db->select('*');    
    $this->db->from('uaip_sol_orientadas');
    if($where != NULL)
          $this->db->where($where,NULL,FALSE);
    $query = $this->db->get();

   return $query->result();

  }

  public function insertarOrientada($serv = array()){
        $this->db->trans_begin();
         $this->db->insert('uaip_sol_orientadas', $serv);

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                return false;
            }
            else {
                $this->db->trans_commit();
                $insert_id = $this->db->insert_id();
                return $insert_id;
                //return true;
            }
    }
    public function actualizaOrientada($serv = array(),$id_orientada){
        $this->db->trans_begin();
          $this->db->where('id_orientada', $id_orientada);          
          $this->db->update('uaip_sol_orientadas', $serv);

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                return false;
            }
            else {
                $this->db->trans_commit();                
                return true;
            }
    }
    public function ActualizarUnidad($serv = array(),$id_unidad_enlace)
    {
      $this->db->trans_begin();
          $this->db->where('id_unidad_enlace', $id_unidad_enlace);          
          $this->db->update('uaip_unidad_enlace', $serv);

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                return false;
            }
            else {
                $this->db->trans_commit();   

                return true;
            }

    }
    function getEnlacesUAIP($id_unidad,$tipo){
        $where = "id_unidad_enlace=".$id_unidad." and id_tipo_usuario = ".$tipo;
        $this->db->select('*');
        $this->db->from('uaip_usuario');
        if($where != NULL)
              $this->db->where($where,NULL,FALSE);
        $query = $this->db->get();

        return $query->row();

    }
    public function ActualizarEnlace($serv = array(),$id_usuario)
    {
      $this->db->trans_begin();
          $this->db->where('id_usuario', $id_usuario);          
          $this->db->update('uaip_usuario', $serv);

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                return false;
            }
            else {
                $this->db->trans_commit();  
                return true;
            }
    }
    public function InsertarEnlace($serv = array())
    {
      $this->db->trans_begin();
          //$this->db->where('id_usuario', $id_usuario);          
          $this->db->insert('uaip_usuario', $serv);

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                return false;
            }
            else {
                $this->db->trans_commit();  
                return true;
            }
    }
    public function GuardaMensaVacas($serv = array())
    {
       $this->db->trans_begin();
         $this->db->insert('uaip_periodo_vacas', $serv);

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                return false;
            }
            else {
                $this->db->trans_commit();
                $insert_id = $this->db->insert_id();
                return $insert_id;
                //return true;
            }
    }
    public function ActualizaMensaVacas($serv = array())
    {
       $this->db->trans_begin();
       $this->db->where('activo = 1'); 
         $this->db->update('uaip_periodo_vacas', $serv);

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                return false;
            }
            else {
                $this->db->trans_commit();
                $insert_id = $this->db->insert_id();
                return $insert_id;
                //return true;
            }
    }
    public function getMensajesVacaciones()
    {
      $where = "activo = 1";
      $this->db->select('*');
      //$this->db->order_by("id_orientada", "desc");
      $this->db->from('uaip_periodo_vacas');
      if($where != NULL)
            $this->db->where($where,NULL,FALSE);
      $query = $this->db->get();

     return $query->row();

    }
    public function ObtieneSolicitudesCalidad($inicia, $finaliza)
    {
      $where = "id_solicitud >= ".$inicia." and id_solicitud <= ".$finaliza;
      $this->db->select('*');
      $this->db->order_by("id_solicitud", "asc");
      $this->db->from('vw_Informe_calidad');
      if($where != NULL)
            $this->db->where($where,NULL,FALSE);
      $query = $this->db->get();
      return $query->result();
    }
    public function ObtieneSolicitudesIacip($inicia, $finaliza)
    {
      $where = "id_solicitud >= ".$inicia." and id_solicitud <= ".$finaliza;
      $this->db->select('*');
      $this->db->order_by("id_solicitud", "asc");
      $this->db->from('vw_iacip_reporte');
      if($where != NULL)
            $this->db->where($where,NULL,FALSE);
      $query = $this->db->get();
      return $query->result();
    }
    public function ObtieneHistorialReporte($id_solicitud,$estatus){
        $where = "id_solicitud = ".$id_solicitud." and id_estatus in (".$estatus.")";
      $this->db->select('*');
      $this->db->order_by("fecha", "asc");
      $this->db->from('uaip_historial');
      if($where != NULL)
            $this->db->where($where,NULL,FALSE);
      $query = $this->db->get();
      return $query->result();

    }
    public function ObtieneSujetoReporte($id_solicitud)
    {
      $where = "id_solicitud = ".$id_solicitud;
      $this->db->select('*');      
      $this->db->from('vw_uaip_solicitud_unidad');
      if($where != NULL)
            $this->db->where($where,NULL,FALSE);
      $query = $this->db->get();
      return $query->result();
    }
    function GenerarRecurso($id_solicitud,$respuesta_publica){
      $dataUpdate = array(
                         'fecha_recurso' => date('Y-d-m H:i:s'),
                         'observaciones' => $respuesta_publica,
                         'recurso_activo' => 1
                     );

                     $this->db->where('id_solicitud', $id_solicitud);
                     $this->db->update('uaip_solicitud', $dataUpdate);
          return 1;

  }
  function getMensajeCorreo($id){
    $where = "id_mensaje_correo = ".$id;
      $this->db->select('*');      
      $this->db->from('uaip_mensajes_correo');
      if($where != NULL)
            $this->db->where($where,NULL,FALSE);
      $query = $this->db->get();
      return $query->row();

  }
  function ActualizaMensaje($mensaje,$id)
  {
    $dataUpdate = array(                         
                         'mensaje' => $mensaje                         
                     );

                     $this->db->where('id_mensaje_correo', $id);
                     $this->db->update('uaip_mensajes_correo', $dataUpdate);
          return 1;

  }

    function getArchivosRespondida($id=0)
    {       
      $this->db->select('*');      
      $this->db->from('uaip_adjuntos'); 
      $this->db->where("id_solicitud=".$id,NULL,FALSE);
      $query = $this->db->get();
      return $query->result();
  }
  
  public function AgregarUnidad($serv = array())
    {
      $this->db->trans_begin();
          $this->db->where('id_unidad_enlace +1');          
          $this->db->insert('uaip_unidad_enlace', $serv);

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                return false;
            }
            else {
                $this->db->trans_commit();   

                return true;
            }

    }
  
   function getUnidadesEnlaceEsp($n=array())
    {       
        $this->db->select('id_unidad_enlace,sigla,nombre,activo');
        $this->db->from('uaip_unidad_enlace');
        $this->db->where("nombre like '%".$n."%' or sigla like '%".$n."%'");
        $query = $this->db->get();
        return $query->result();
  }
  
    function getLeyCorreo($id)
    {       
        $this->db->select('*');
        $this->db->from('uaip_mensajes_correo');
        $this->db->where("id_mensaje_correo=".$id);
        $query = $this->db->get();
        return $query->row();
  }
  
   function getUnidadProceso($id)
    {              
        $this->db->select('c.id_unidad_enlace,nombre');
        $this->db->from('uaip_unidad_enlace c,uaip_historial a ');
        $this->db->where("c.id_unidad_enlace=a.id_unidad_enlace and a.id_solicitud = ".$id." and a.id_estatus=4");
        $query = $this->db->get();
        return $query->result(); //Obtiene todas las filas
  }
  
     function getTotalVisitas()
    {              
        $this->db->select('count(id_conteo_pk) as cantidad');
        $this->db->from('uaip_conteo_visitas');
        $query = $this->db->get();
        return $query->row();//Obtien solo una fila
  }
  
       function getTotalVisitasMes()
    {              
        $this->db->select('count(id_conteo_pk) as cantidad');
        $this->db->from('uaip_conteo_visitas');
        $this->db->where("year(fecha_visita)=".date("Y")."  and  month(fecha_visita)=".date("m"));
        $query = $this->db->get();
        return $query->row();//Obtien solo una fila
  }
  
       function getTotalVisitasDia()
    {              
        $this->db->select('count(id_conteo_pk) as cantidad');
        $this->db->from('uaip_conteo_visitas');
        $this->db->where("month(fecha_visita)=".date("m")." and   day(fecha_visita)=".date("d"));
        $query = $this->db->get();
        return $query->row();//Obtien solo una fila
  }
  function InsertaVisita($estado="",$ciudad="",$pais="")
  {      
      	$browser=array("IE","OPERA","MOZILLA","NETSCAPE","FIREFOX","SAFARI","CHROME");
	$os=array("WIN","MAC","LINUX"); 

	# definimos unos valores por defecto para el navegador y el sistema operativo
	$info['browser'] = "OTHER";
	$info['os'] = "OTHER";
 

	# buscamos el navegador con su sistema operativo
	foreach($browser as $parent)
	{
		$s = strpos(strtoupper($_SERVER['HTTP_USER_AGENT']), $parent);
		$f = $s + strlen($parent);
		$version = substr($_SERVER['HTTP_USER_AGENT'], $f, 15);
		$version = preg_replace('/[^0-9,.]/','',$version);

		if ($s)
		{
                    $info['browser'] = $parent;
                    $info['version'] = $version;
		}
	} 

	# obtenemos el sistema operativo
	foreach($os as $val)
	{
            if (strpos(strtoupper($_SERVER['HTTP_USER_AGENT']),$val)!==false)
                $info['os'] = $val;
	}
        
        $this->db->select('count(id_conteo_pk) as cantidad');
        $this->db->from('uaip_conteo_visitas');
        $this->db->where("(fecha_visita>='".date('d/m/Y 00:00:00')."' and fecha_visita<='".date('d/m/Y 23:59:59')."') and ip ='".getenv('REMOTE_ADDR')."'");
        $query = $this->db->get();
        $dataC=$query->row();
        
        if(number_format($dataC->cantidad,0,'','')==0)
        {
            $data = array(
                'ip' => getenv('REMOTE_ADDR'),
                'os' => $info['os'],
                'navegador' => $info['browser'],
                'version_navegador' => $info['version'],
                "estado"=>$estado,
                "ciudad"=>$ciudad,
                "pais"=>$pais
            );
            $this->db->insert('uaip_conteo_visitas',$data);
            return 1; 
        }
  }

  
public function getAutocompleta($q)
{
    $q=utf8_encode($q);      
    $q= str_replace("ã¡", "á", $q);
    $q= str_replace("ã‰", "É", $q);
    $q= str_replace("ã©", "é", $q);
    //      $q= str_replace("Ã", "Í", $q);
    //      $q= str_replace("Ã", "í", $q);
    $q= str_replace("ã“", "Ó", $q);
    $q= str_replace("ã³", "ó", $q);
    $q= str_replace("ãš", "Ú", $q);
    $q= str_replace("ãº", "ú", $q);
    $q= str_replace("ã‘", "Ñ", $q);
    $q= str_replace("ã±", "ñ", $q);
    $q= str_replace("ã", "Á", $q);

    $q= str_replace("Ã¡", "á", $q);
    $q= str_replace("Ã‰", "É", $q);
    $q= str_replace("Ã©", "é", $q);
    //      $q= str_replace("Ã", "Í", $q);
    //      $q= str_replace("Ã", "í", $q);
    $q= str_replace("Ã“", "Ó", $q);
    $q= str_replace("Ã³", "ó", $q);
    $q= str_replace("Ãš", "Ú", $q);
    $q= str_replace("Ãº", "ú", $q);
    $q= str_replace("Ã‘", "Ñ", $q);
    $q= str_replace("Ã±", "ñ", $q);
    $q= str_replace("Ã", "Á", $q);
    
    $this->db->select('top 10 id_unidad_responsable_pk,nombre');
    $this->db->from('uaip_unidad_responsable');
    $this->db->where("nombre like '%".$q."%'");
    $query = $this->db->get();

    if($query->num_rows > 0){
        foreach ($query->result_array() as $row)
            $row_set[]=(($row['id_unidad_responsable_pk'])).'-'.(($row['nombre']));

        echo json_encode($row_set); 
    }   
}

public function getAutocompletaEmp($q)
{
    $q=utf8_encode($q);      
    $q= str_replace("ã¡", "á", $q);
    $q= str_replace("ã‰", "É", $q);
    $q= str_replace("ã©", "é", $q);
    //      $q= str_replace("Ã", "Í", $q);
    //      $q= str_replace("Ã", "í", $q);
    $q= str_replace("ã“", "Ó", $q);
    $q= str_replace("ã³", "ó", $q);
    $q= str_replace("ãš", "Ú", $q);
    $q= str_replace("ãº", "ú", $q);
    $q= str_replace("ã‘", "Ñ", $q);
    $q= str_replace("ã±", "ñ", $q);
    $q= str_replace("ã", "Á", $q);

    $q= str_replace("Ã¡", "á", $q);
    $q= str_replace("Ã‰", "É", $q);
    $q= str_replace("Ã©", "é", $q);
    //      $q= str_replace("Ã", "Í", $q);
    //      $q= str_replace("Ã", "í", $q);
    $q= str_replace("Ã“", "Ó", $q);
    $q= str_replace("Ã³", "ó", $q);
    $q= str_replace("Ãš", "Ú", $q);
    $q= str_replace("Ãº", "ú", $q);
    $q= str_replace("Ã‘", "Ñ", $q);
    $q= str_replace("Ã±", "ñ", $q);
    $q= str_replace("Ã", "Á", $q);
    
    $this->db->select('top 10 *');
    $this->db->from('View_Empleados');
    $this->db->where("nombre_completo like '%".$q."%'");
    $query = $this->db->get();

    if($query->num_rows > 0){
        foreach ($query->result_array() as $row)
            $row_set[]=(($row['id_empleado'])).'-'.(($row['nombre_completo']));

        echo json_encode($row_set); 
    }   
}

  function getUsuarios($q)
  {
    $q=utf8_encode($q);      
    $q= str_replace("ã¡", "á", $q);
    $q= str_replace("ã‰", "É", $q);
    $q= str_replace("ã©", "é", $q);
    //      $q= str_replace("Ã", "Í", $q);
    //      $q= str_replace("Ã", "í", $q);
    $q= str_replace("ã“", "Ó", $q);
    $q= str_replace("ã³", "ó", $q);
    $q= str_replace("ãš", "Ú", $q);
    $q= str_replace("ãº", "ú", $q);
    $q= str_replace("ã‘", "Ñ", $q);
    $q= str_replace("ã±", "ñ", $q);
    $q= str_replace("ã", "Á", $q);

    $q= str_replace("Ã¡", "á", $q);
    $q= str_replace("Ã‰", "É", $q);
    $q= str_replace("Ã©", "é", $q);
    //      $q= str_replace("Ã", "Í", $q);
    //      $q= str_replace("Ã", "í", $q);
    $q= str_replace("Ã“", "Ó", $q);
    $q= str_replace("Ã³", "ó", $q);
    $q= str_replace("Ãš", "Ú", $q);
    $q= str_replace("Ãº", "ú", $q);
    $q= str_replace("Ã‘", "Ñ", $q);
    $q= str_replace("Ã±", "ñ", $q);
    $q= str_replace("Ã", "Á", $q);
      
    $this->db->select('top 10 *');
    $this->db->like('nombre_completo', $q);
    $query = $this->db->get('view_usuario');
    if($query->num_rows > 0){
      foreach ($query->result_array() as $row){
        $row_set[]=  number_format($row['id_usuario'],0,'','')."-".$row['nombre_completo']."-(".$row['usuario'].")";
      }
      echo json_encode($row_set); //format the array into json data
    }
  }
  
  function guardaRespSeccionUnidades($id_unidad,$usuarios)
  {
      $r=array();
      try
      {
        $this->db->trans_begin();
        
        $cad_eliminar="0,";
        foreach ($usuarios as $d)
        {            
            if(number_format($d->ban_en_uso,0,'','')!=0)
                $cad_eliminar=$cad_eliminar.$d->id_unidad_responsable_pk.",";
        }
        
        $cad_eliminar=  substr($cad_eliminar, 0,-1);
        $this->db->where("id_unidad_enlace_pk=".$id_unidad." and id_unidad_responsable_pk not in(".$cad_eliminar.")");
        $this->db->delete('uaip_unidad_responsable');
            
            
        foreach ($usuarios as $d)
        {            
            if(number_format($d->ban_en_uso,0,'','')==0)
            {         
                $data = array(
                'id_unidad_enlace_pk' => $id_unidad,
                'id_usuario_pk' => $d->id_usuario,
                'nombre' => $d->nombre_usuario
                );
                $this->db->insert('uaip_unidad_responsable',$data);
            }
        }  
        
        $this->db->trans_commit();    
        $r=array("tipo_error"=>"0","mensaje"=>"Los cambios se realizarón de manera satisfactoria.");
      } 
      catch (Exception $ex) 
      {
        $this->db->trans_rollback();
        $r=array("tipo_error"=>"1","mensaje"=>  utf8_encode($ex->getMessage()));
      }
      return $r;
  }
  
  function obtenerRespSeccionUnidades($id_unidad)
  {
    $this->db->select('id_unidad_responsable_pk,id_usuario_pk,nombre,en_uso');
    $this->db->from('view_uaip_unidad_responsable');
    $this->db->where("id_unidad_enlace_pk=".$id_unidad);
    $query = $this->db->get();
    return $query->result(); //Obtiene todas las filas
  }
  
  function obtenerRespSeccion($id)
  {
    $this->db->select('id_responsable_fraccion_pk,id_informacion_pk,id_unidad_responsable_pk,nombre');
    $this->db->from('uaip_responsable_fraccion');
    $this->db->where("id_informacion_pk=".$id);
    $query = $this->db->get();
    return $query->result(); //Obtiene todas las filas
  }
  
  function obtenerRespSeccionUnidadesUno($id_usuario)
  {
    $this->db->select('id_unidad_responsable_pk,id_usuario_pk,nombre,en_uso');
    $this->db->from('view_uaip_unidad_responsable');
    $this->db->where("en_uso<>0 and id_usuario_pk=".$id_usuario);
    $query = $this->db->get();
    return $query->row(); 
  }
  
   function obtenerEmpleado()
  {
    $this->db->select('id_empleado, id_unidad_enlace_fk, nombre_empleado');
    $this->db->from('uaip_jefe_unidades');
//    $this->db->where("en_uso<>0 and id_usuario_pk=".$id_usuario);
    $query = $this->db->get();
    return $query->row(); 
  }
  
  
  function getUnidadesCargo($id_empleado, $id_unidad="")
  {
    $where="id_empleado='".$id_empleado."'";
    if($id_unidad!='')
    {
        //Obtenemos el padre
         $where="id_unidad_enlace_fk=".$id_unidad;
                    
        $this->db->select(' id_unidad_enlace, id_padre, id_tipo');
        $this->db->from('uaip_unidad_enlace');
        $this->db->where("id_unidad_enlace=".$id_unidad);
        $query = $this->db->get();
        $data= $query->row(); 

        //Por el momento solo es para las secretarias
        $id_padre=number_format($data->id_padre,0,'','');
        if(number_format($data->id_tipo,0,'','')==2)
        {
            $where="id_unidad_enlace_fk=".$data->id_unidad_enlace;
        }
        else
        {
            $this->db->select(' id_unidad_enlace, id_padre, id_tipo');
            $this->db->from('uaip_unidad_enlace');
            $this->db->where("id_unidad_enlace=".$id_padre);
            $query = $this->db->get();
            $data= $query->row(); 

            $id_padre=number_format($data->id_padre,0,'','');
             if(number_format($data->id_tipo,0,'','')==2)
             {
                 $where="id_unidad_enlace_fk=".$data->id_unidad_enlace;
             }
             else
             {
                    $this->db->select(' id_unidad_enlace, id_padre, id_tipo');
                    $this->db->from('uaip_unidad_enlace');
                    $this->db->where("id_unidad_enlace=".$id_padre);
                    $query = $this->db->get();
                    $data= $query->row(); 
                    
                    $id_padre=number_format($data->id_padre,0,'','');
                    if(number_format($data->id_tipo,0,'','')==2)
                    {
                        $where="id_unidad_enlace_fk=".$data->id_unidad_enlace;
                    }
                    else
                    {
                    $this->db->select(' id_unidad_enlace, id_padre, id_tipo');
                    $this->db->from('uaip_unidad_enlace');
                    $this->db->where("id_unidad_enlace=".$id_padre);
                    $query = $this->db->get();
                    $data= $query->row(); 
                    
                    $id_padre=number_format($data->id_padre,0,'','');
                    if(number_format($data->id_tipo,0,'','')==2)
                    {
                        $where="id_unidad_enlace_fk=".$data->id_unidad_enlace;
                    }
                    }
             }             
        }
     
    }

      
    $this->db->select('id_empleado,nombre_empleado, id_unidad_enlace_fk');
    $this->db->from('uaip_jefe_unidades');
    $this->db->where($where);
    $query = $this->db->get();
    return $query->row(); 
  }
  
  
  function getUnidadesCargoTodas($id_unidad,$cadena_unidades="")
  {
    $this->db->select('id_unidad_enlace');
    $this->db->from('uaip_unidad_enlace');
    $this->db->where("id_padre=".$id_unidad);
    $query = $this->db->get();
    $data=$query->result();    

    foreach ($data as $d)
    {
        $cadena_unidades=$cadena_unidades.number_format($d->id_unidad_enlace,0,'','').",";
        $cadena_unidades=$cadena_unidades.$this->getUnidadesCargoTodas($d->id_unidad_enlace,"");
    }
    
    return $cadena_unidades;                                
  }
  
  function getObtenerSolicitudesEntrantes($numero_solicitudes=0)
  {
    $this->db->select('count(id_solicitud) as numero_solicitudes');
    $this->db->from('uaip_solicitud');
    $query = $this->db->get();    
    return $query->row();                             
  }
  
}
