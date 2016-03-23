/*  msolicitudes.js
    ----------------------------------------------
    Core de la funcionalidad del módulo msolicitudes.
    Autor: David Calzada
*/

var UAIP = window.UAIP || {};

// DOM ready
$(function(){
        
	UAIP.Solicitudes = (function(){

                    // globales
                    var $solicitudes = $("#solicitudes");
                    var archivos_adjuntos_r=[],archivos_adjuntos_vp=[],archivos_adjuntos_cp=[],$ajax_reenviar_correo_proceso=null,comentario_unidad=[],$ajax_envio_correo=null,$ajax_reenviar_correo_prorroga=null,$ajax_canalizar_solicitud_2=null;
                    var $nueva_modal = $("#NuevaModal");
                    var id_estatus=0, id_solicitud=0, cuerpo_correo="", ajax_reenviar_correo_uni=null;
                    var id_status_privado=0, id_tipo_usuario=0, id_unidad_enlace="", fecha_ingreso_inicio='',fecha_ingreso_fin="", jg_pagina=1;
	return{
                            listado: function(){
                                id_tipo_usuario=$(".txt_id_tipo_usuario").get(0).value;
                                
                                $.datepicker.setDefaults($.datepicker.regional['es']);

                                $('.txt_fecha_inicio').datepicker({
                                    changeMonth: true,
                                    changeYear: true,
                                    dateFormat: "dd-mm-yy"
                                });
                                
                                $('.txt_fecha_fin').datepicker({
                                    changeMonth: true,
                                    changeYear: true,
                                    dateFormat: "dd-mm-yy"
                                });
        
        
                                
                                    
		//console.log("msolicitudes");
                            var jqGridOptions = { url:'/index.php/solicitudes/DataSolicitudes',
                                mtype : "post", 
                                datatype: "json",
                                colNames:['Id Solicitud','Contenido','Medio Respuesta','Ingreso','Fecha límite','Fecha Límite Completar','Estatus','Id_Estatus','Fecha Venc. Prorroga',"Nombre Unidad"],
                                colModel:[
                                {name:'id_solicitud',index:'id_solicitud', width: 80},
                                {name:'contenido',index:'contenido', width: 400},
                                {name:'id_medio_respuesta',index:'id_medio_respuesta', width:120 },
                                {name:'fecha_ingreso',index:'fecha_ingreso', width:90 },
                                {name:'fecha_limite_entrega',index:'fecha_limite_entrega', width:90 },
                                {name:'fecha_limite_completar',index:'fecha_limite_completar', width:150 },
                                {name:'id_estatus',index:'id_estatus', width:90 },
                                {name:'id_estatus2',index:'id_estatus2', width:20 },
                                {name:'fecha_prorroga',index:'fecha_prorroga', width:140 },
                                {name:'nombre_unidad_enlace',index:'nombre_unidad_enlace', width:250 }
                                ],
                                rowNum:50,
                                autowidth: true,
                                height:450,
                                rowList:[50,100,200],
                                pager: '#pagerSolicitudes',
                                sortname: 'id_solicitud',
                                viewrecords: true,
                                sortorder: "desc",
                                toolbar: [true,"top"],
                                multiselect: false,
                                shrinkToFit: false,
                                gridview:true,
                                 scroll: 1,
                                 prmNames: { npage: '#pagerSolicitudes' },
                                ondblClickRow: function(id){ UAIP.Solicitudes.consultar_empleado(id); },
                                caption:"Solicitudes"
                                };

                            $solicitudes.jqGrid(jqGridOptions); //Así se ejecuta!!!!!
                            $solicitudes.jqGrid('navGrid','#pagerSolicitudes',{edit:false,add:false,del:false,search: true});
                                $solicitudes.jqGrid('hideCol', ['id_estatus2','fecha_prorroga',"fecha_limite_completar","nombre_unidad_enlace"]);
                                
                                $("#bttn_buscar_ad").on('click', function(e){
					var q = $("#txt_consulta_ad").get(0).value;
                                        var col = $("#slc_columna_ad").get(0).value,todo='';
                                        
                                        if($.trim(q)=='')
                                            alert("Debe ingresar el valor que desea buscar.");
                                        else
                                        {
                                            if(parseInt(id_status_privado)==0)
                                            {
                                                $solicitudes.setGridParam({url:'/index.php/solicitudes/DataSolicitudes?columna=' + col + "&consulta=" + q});
                                            }
                                            else
                                            {
                                                $solicitudes.setGridParam({url:'/index.php/solicitudes/DataSolicitudes?columna=' + col + "&consulta=" + q + "&id_estatus=" +id_status_privado});
                                            }                                        
                                            
                                            $solicitudes.trigger('reloadGrid');
                                        }
				});
                                
                                
                                $(document).on('click', '#ver_todos_ad', function(e){
					var todo = "";
                                        $("#txt_consulta_ad").prop("value",'');
                                        if(parseInt(id_status_privado)==0)
                                        {
                                            $solicitudes.setGridParam({url:'/index.php/solicitudes/DataSolicitudes'});
                                        }
                                        else
                                        {
                                            $solicitudes.setGridParam({url:'/index.php/solicitudes/DataSolicitudes?mis_solicitudes=' + todo + "&id_estatus=" + id_status_privado});
                                        }
					$solicitudes.trigger('reloadGrid');
                                
				});
                                
                                
                                $(document).on('change', '.slc_jefe_unidades', function(e){
                                     fn_filtra_unidades_jefe();
				});
                                
                                $(document).on('click', '.bttn_filtrar_uj', function(e){
                                     fn_filtra_unidades_jefe();
				});
                                
                                $(document).on('click', '.bttn_quitar_filtros_uj', function(e){
                                     $(".slc_jefe_unidades option:first").prop('selected', true);
                                     $(".txt_fecha_inicio").prop("value","");
                                     $(".txt_fecha_fin").prop("value","");
                                     fn_filtra_unidades_jefe();
				});
                                
                                
                                
                                var fn_filtra_unidades_jefe=function(){
					var todo = "";
                                        fecha_ingreso_inicio=$(".txt_fecha_inicio").get(0).value;
                                        fecha_ingreso_fin=$(".txt_fecha_fin").get(0).value;
                                        
                                        
                                        $("#txt_consulta_ad").prop("value",'');
                                        id_unidad_enlace= $(".slc_jefe_unidades").get(0).value;
                                        
                                        if(parseInt(id_status_privado)==0)
                                        {
                                            $solicitudes.setGridParam({url:'/index.php/solicitudes/DataSolicitudes?id_unidad_enlace=' + id_unidad_enlace + "&fecha_ingreso_inicio=" + fecha_ingreso_inicio + "&fecha_ingreso_fin=" + fecha_ingreso_fin});
                                        }
                                        else
                                        {
                                            $solicitudes.setGridParam({url:'/index.php/solicitudes/DataSolicitudes?mis_solicitudes=' + todo + "&id_estatus=" + id_status_privado + "&id_unidad_enlace=" + id_unidad_enlace + "&fecha_ingreso_inicio=" + fecha_ingreso_inicio + "&fecha_ingreso_fin=" + fecha_ingreso_fin});
                                        }
					$solicitudes.trigger('reloadGrid');                                
				};
                                
                              
                                
                                $("#bttn_marcar_improcedente").on("click", function()
                                {
                                    var id = jQuery('#solicitudes').jqGrid('getGridParam','selrow'); 
                                    if(id)
                                    {  
                                        id_estatus=15;
                                        var ret = jQuery('#solicitudes').jqGrid('getRowData',id);
                                        $("#sp_id_solicitud_improcedente").text(ret.id_solicitud);                                        
                                        $("#txt_ob_improcedente").prop("value","");
                                        $("#mod_improcedente").modal("show");                                  
                                    }
                                    else
                                        alert("Para marcar una solicitud como improcedente, debe seleccionar una del listado."); 
                                   
                                });
                                
                                
                                var fn_obtener_correo=function(id,cuerpo_correo)
                                {                                                   
                                    if($ajax_envio_correo==null)
                                    {
                                        var titulo_encabezado="";
                                        $ajax_envio_correo=$.ajax({
                                            type: 'POST',
                                            url: 'ObtenerLeyCorreo',
                                            dataType: 'json',
                                            data: { id: id, id_solicitud:id_solicitud}
                                        }).done(function(r) {

                                            $ajax_envio_correo=null;
                                            switch(id)
                                            {
                                                case 6:
                                                {
                                                    titulo_encabezado="Su solicitud se ha marcado como improcedente. \r\n";
                                                    break;
                                                }                                                    
                                            }
                                             $("#sp_correo_vista").text(r.email);
                                             $("#txt_cuerpo_correo").prop("value",titulo_encabezado + r.mensaje + id_solicitud + ".\r\n\r\nObservaciones. \r\n" + cuerpo_correo);
                                        });
                                    }

                                };
                                
                                $("#btn_pre_correo").on("click", function()
                                {
                                        cuerpo_correo=$.trim($("#txt_ob_improcedente").get(0).value);
                                        if(cuerpo_correo!="")
                                        {
                                            id_solicitud=$("#sp_id_solicitud_improcedente").text();
                                            $("#sp_id_solicitud_correo").text(id_solicitud);                                 
//                                            $("#sp_correo_vista").text();  
                                            $("#mod_improcedente").modal("hide");   
                                            $("#mod_pre_correo").modal("show");                                               
                                            fn_obtener_correo(6,cuerpo_correo,id_solicitud) ;                                            
                                        }
                                        else
                                            alert("El motivo es un dato obligatorio.");                        
                                });
                                
                                
                                
                                $("#btn_enviar_correo").on("click", function()
                                {
                                    $("#lbl_enviar_correo").html("<img src='/media/cargando.gif'> Enviando Correo...");
                                    $("#btn_cerrar_enviar_correo").prop("disabled", true);
                                    $("#btn_enviar_correo").prop("disabled", true);
               
                                    $ajax_envio_correo=$.ajax({
                                        type: 'POST',
                                        url: 'EnviarCuerpoCorreo',
                                        dataType: 'json',
                                        data: { id: id_solicitud, id_estatus: id_estatus,cuerpo_correo: $.trim($("#txt_cuerpo_correo").get(0).value)   }
                                    }).done(function(r) {

                                        switch(r.id_tipo_error)
                                        {
                                            case 1:
                                            {
                                                $("#mod_pre_correo").modal("hide"); 
                                                id_solicitud=0;
                                                id_estatus=0;
                                                
                                                if(id_status_privado==0)
                                                    $solicitudes.setGridParam({url:'DataSolicitudes'});
                                                else                                                
                                                    $solicitudes.setGridParam({url:'DataSolicitudes?id_estatus='+id_status_privado});
                                                
                                                $solicitudes.trigger('reloadGrid');
                                                alert("El correo fue enviado de manera satisfactoria.");
                                                break;
                                            }
                                            case 2:
                                            {
                                                alert(r.mensaje);
                                                break;
                                            }                                                    
                                        }
                                        $("#btn_cerrar_enviar_correo").prop("disabled", false);
                                        $("#btn_enviar_correo").prop("disabled", false);
                                        $("#lbl_enviar_correo").html("");


                                    });
                        
                                });
                                
				$("#t_solicitudes").append("<input id='nuevas' name = 'nuevas' type='button' value='Nuevas' class='btn btn-small'/>");
				$("#t_solicitudes").append("<input id='incompletas' name = 'incompletas' type='button' value='Incompletas' class='btn btn-small'/>");
				$("#t_solicitudes").append("<input id='orientadas' name = 'orientadas' type='button' value='Orientadas' class='btn btn-small'/>");
				$("#t_solicitudes").append("<input id='completadas' name = 'completadas' type='button' value='Completadas' class='btn btn-small'/>");
				$("#t_solicitudes").append("<input id='proceso' name = 'proceso' type='button' value='Proceso' class='btn btn-small'/>");
				$("#t_solicitudes").append("<input id='prorroga' name = 'prorroga' type='button' value='Prórroga' class='btn btn-small'/>");
				$("#t_solicitudes").append("<input id='condicionada' name = 'condicionada' type='button' value='Pago Requerido' class='btn btn-small'/>");
				$("#t_solicitudes").append("<input id='cancelada' name = 'cancelada' type='button' value='Desechadas' class='btn btn-small'/>");
				$("#t_solicitudes").append("<input id='liberada' name = 'liberada' type='button' value='Respondidas' class='btn btn-small'/>");
				$("#t_solicitudes").append("<input id='no_entregada' name = 'no_entregada' type='button' value='Cont/no Entregada' class='btn btn-small'/>");
				//$("#t_solicitudes").append("<input id='rechazada_ue' name = 'rechazada_ue' type='button' value='Rechazada por UE' class='btn'/>");
				$("#t_solicitudes").append("<input id='revision' name = 'revision' type='button' value='Revisión' class='btn btn-small'/>");
                                $("#t_solicitudes").append("<input id='bttn_extemp' data-toggle='modal' name = 'bttn_extemp' type='button' value='Aviso Extemp' class='btn btn-small'/>");                                
                                $("#t_solicitudes").append("<input id='bttn_ver_improcedente' name = 'bttn_ver_improcedente' type='button' value='Imp.' class='btn btn-small'/>");                                
                                $("#t_solicitudes").append("<input id='bttn_ver_detalle' name = 'bttn_ver_improcedente' type='button' value='Ver Solicitud' class='btn btn-small'/>");    
                                
                                $("#bttn_reenviar_correo").hide();
                                $("#bttn_reenviar_correo_proceso").hide();
                                $("#bttn_reenviar_correo_prorroga").hide();
                                
                                if(parseInt(id_tipo_usuario)==6)
                                {
                                    $solicitudes.jqGrid('showCol', ['nombre_unidad_enlace']);
                                    $("#nuevas").hide();
                                    $("#incompletas").hide();
                                    $("#orientadas").hide();
                                    $("#completadas").hide();
                                    $("#proceso").hide();
                                    $("#prorroga").hide();
                                    $("#condicionada").hide();
                                    $("#cancelada").hide();
                                    $("#liberada").hide();
                                    $("#no_entregada").hide();
                                    $("#revision").hide();
                                    $("#bttn_extemp").hide();
                                    $("#bttn_ver_improcedente").hide();
                                    $("#bttn_ver_detalle").show();
                                }
                                else
                                    $("#bttn_ver_detalle").hide();
                                
                                
                                
                                
                                $("#bttn_ver_detalle").on('click',function() {
                                    var id = jQuery('#solicitudes').jqGrid('getGridParam','selrow'); 
                                    if(id)
                                    {  
                                        var ret = jQuery('#solicitudes').jqGrid('getRowData',id); 
                                        UAIP.Solicitudes.consultar_empleado(ret.id_solicitud); 
                                    }
                                    else
                                        alert("Para ver el detalle de una solicitud, debe seleccionar una del listado.");                
                                 });
                                 
                                 
                                 
                                 $("#bttn_extemp").on('click',function() {
                                    var id = jQuery('#solicitudes').jqGrid('getGridParam','selrow'); 
                                    if(id)
                                    {  
                                        var ret = jQuery('#solicitudes').jqGrid('getRowData',id); 
                                        if(ret.id_estatus2==5 || ret.id_estatus2==8)
                                        {
                                            $("#txt_obt_extmp").prop("value",'');
                                            $("#sp_id_solicitud").text(ret.id_solicitud);
                                            $('#md_aviso_extmp').modal();
                                        }
                                        else
                                            alert("Para agregar un aviso de extemporeanidad, la solicitud debe estar en prorroga o respondida.");
                                    }
                                    else
                                        alert("Para agregar un aviso de extemporeanidad, debe seleccionar una solicitud del listado.");                
                                 });
                                 
                                $("#a_guardar_aviso_extmp").on('click',function() {            
                                    var aviso=$("#txt_obj_extmp").get(0).value;
                                    var id=$("#sp_id_solicitud").text();
                                    var no_dias=$("#slc_nd_extmp").get(0).value;
                                    
                                    if($.trim(aviso)=='')
                                        alert("El campo de aviso es obligatorio");
                                    else
                                    {
                                        $("#lbl_agregar_aviso").html("<img src='/media/cargando.gif'> Enviando Datos...");
                                        $.ajax({
                                            type: 'POST',
                                            url: 'AgregarAvisoExtmp',
                                            dataType: 'json',
                                            data: { id: id, aviso: aviso, no_dias:no_dias }
                                        }).done(function(r) {
                                            switch (parseInt(r.tipo_error))
                                            {
                                                case 1:
                                                {
                                                    alert(r.mensaje);
                                                    $("#txt_obj_extmp").prop("value",'');
                                                    $("#sp_id_solicitud").text("");
                                                    $('#md_aviso_extmp').modal('hide');
                                                    $("#lbl_agregar_aviso").html("");
                                                    $("#slc_nd_extmp option:first").prop('selected', true);
                                                    break;
                                                }
                                                case 2:
                                                {
                                                    alert(r.mensaje);
                                                    $("#lbl_agregar_aviso").html("");
                                                    break;
                                                }
                                            }
                                        });
                                        
                                    }
                                 });
	},
                        
                        /*toda: function(){
				$(document).on('click', '#ver_todos_ad', function(e){
					var todo = "";
				    $solicitudes.setGridParam({url:'/index.php/solicitudes/DataSolicitudes'+ todo});
					$solicitudes.trigger('reloadGrid');
				});
			},*/
                        
			nuevas: function(){
				$(document).on('click', '#nuevas', function(e){
					$( "#nuevas" ).addClass( "btn-info" );
					$( "#incompletas" ).removeClass( "btn-info" );
					$( "#completadas" ).removeClass( "btn-info" );
					$( "#orientadas" ).removeClass( "btn-info" );
					$( "#proceso" ).removeClass( "btn-info" );
					$( "#prorroga" ).removeClass( "btn-info" );
					$( "#condicionada" ).removeClass( "btn-info" );
					$( "#cancelada" ).removeClass( "btn-info" );
					$( "#liberada" ).removeClass( "btn-info" );
					$( "#no_entregada" ).removeClass( "btn-info" );
					$( "#revision" ).removeClass( "btn-info" );
                                            $( "#bttn_ver_improcedente" ).removeClass( "btn-info" );
                                        $solicitudes.setGridParam({url:'DataSolicitudes?mis_solicitudes=1'});
					$solicitudes.trigger('reloadGrid');
                                        $solicitudes.jqGrid('hideCol', ['fecha_prorroga','fecha_limite_completar']);
                                        $("#bttn_reenviar_correo").hide();
                                        $("#bttn_reenviar_correo_proceso").hide();
                                        $("#bttn_reenviar_correo_prorroga").hide();
                                        id_status_privado=0;
				});
			},
			incompletas: function(){
				$(document).on('click', '#incompletas', function(e){
					$( "#nuevas" ).removeClass( "btn-info" );
					$( "#incompletas" ).addClass( "btn-info" );
					$( "#completadas" ).removeClass( "btn-info" );
					$( "#orientadas" ).removeClass( "btn-info" );
					$( "#proceso" ).removeClass( "btn-info" );
					$( "#prorroga" ).removeClass( "btn-info" );
					$( "#condicionada" ).removeClass( "btn-info" );
					$( "#cancelada" ).removeClass( "btn-info" );
					$( "#liberada" ).removeClass( "btn-info" );
					$( "#no_entregada" ).removeClass( "btn-info" );
					$( "#revision" ).removeClass( "btn-info" );
                                            $( "#bttn_ver_improcedente" ).removeClass( "btn-info" );
                                        $solicitudes.setGridParam({url:'DataSolicitudes?id_estatus=2'});
					$solicitudes.trigger('reloadGrid');
                                        $solicitudes.jqGrid('hideCol', ['fecha_prorroga']);
                                        $solicitudes.jqGrid('showCol', ['fecha_limite_completar']);
                                        $("#bttn_reenviar_correo").hide();
                                        $("#bttn_reenviar_correo_proceso").hide();
                                        $("#bttn_reenviar_correo_prorroga").hide();
                                        id_status_privado=2;
                                        
				});
			},
			completadas: function(){
				$(document).on('click', '#completadas', function(e){
					$( "#nuevas" ).removeClass( "btn-info" );
					$( "#incompletas" ).removeClass( "btn-info" );
					$( "#completadas" ).addClass( "btn-info" );
					$( "#orientadas" ).removeClass( "btn-info" );
					$( "#proceso" ).removeClass( "btn-info" );
					$( "#prorroga" ).removeClass( "btn-info" );
					$( "#condicionada" ).removeClass( "btn-info" );
					$( "#cancelada" ).removeClass( "btn-info" );
					$( "#liberada" ).removeClass( "btn-info" );
					$( "#no_entregada" ).removeClass( "btn-info" );
					$( "#revision" ).removeClass( "btn-info" );
                                            $( "#bttn_ver_improcedente" ).removeClass( "btn-info" );
                                        $solicitudes.setGridParam({url:'DataSolicitudes?id_estatus=3'});
					$solicitudes.trigger('reloadGrid');
                                        $solicitudes.jqGrid('hideCol', ['fecha_prorroga','fecha_limite_completar']);
                                        $("#bttn_reenviar_correo").hide();
                                        $("#bttn_reenviar_correo_proceso").hide();
                                        $("#bttn_reenviar_correo_prorroga").hide();
                                        id_status_privado=3;
				});
			},
			orientadas: function(){
				$(document).on('click', '#orientadas', function(e){
					$( "#nuevas" ).removeClass( "btn-info" );
					$( "#incompletas" ).removeClass( "btn-info" );
					$( "#completadas" ).removeClass( "btn-info" );
					$( "#orientadas" ).addClass( "btn-info" );
					$( "#proceso" ).removeClass( "btn-info" );
					$( "#prorroga" ).removeClass( "btn-info" );
					$( "#condicionada" ).removeClass( "btn-info" );
					$( "#cancelada" ).removeClass( "btn-info" );
					$( "#liberada" ).removeClass( "btn-info" );
					$( "#no_entregada" ).removeClass( "btn-info" );
					$( "#revision" ).removeClass( "btn-info" );
                                            $( "#bttn_ver_improcedente" ).removeClass( "btn-info" );
				        $solicitudes.setGridParam({url:'DataSolicitudes?id_estatus=13'});
					$solicitudes.trigger('reloadGrid');
                                       $solicitudes.jqGrid('hideCol', ['fecha_prorroga','fecha_limite_completar']);
                                        $("#bttn_reenviar_correo").hide();
                                        $("#bttn_reenviar_correo_proceso").hide();
                                        $("#bttn_reenviar_correo_prorroga").hide();
                                        id_status_privado=1;
				});
			},
			proceso: function(){
				$(document).on('click', '#proceso', function(e){
                                        $( "#nuevas" ).removeClass( "btn-info" );
                                        $( "#incompletas" ).removeClass( "btn-info" );
                                        $( "#completadas" ).removeClass( "btn-info" );
                                        $( "#orientadas" ).removeClass( "btn-info" );
                                        $( "#proceso" ).addClass( "btn-info" );
                                        $( "#prorroga" ).removeClass( "btn-info" );
                                        $( "#condicionada" ).removeClass( "btn-info" );
                                        $( "#cancelada" ).removeClass( "btn-info" );
                                        $( "#liberada" ).removeClass( "btn-info" );
                                        $( "#no_entregada" ).removeClass( "btn-info" );
                                        $( "#revision" ).removeClass( "btn-info" );
                                        $( "#bttn_ver_improcedente" ).removeClass( "btn-info" );
                                        $solicitudes.setGridParam({url:'DataSolicitudes?id_estatus=4'});
                                        $solicitudes.trigger('reloadGrid');
                                        $solicitudes.jqGrid('hideCol', ['fecha_prorroga','fecha_limite_completar']);
                                        $("#bttn_reenviar_correo").hide();
                                        $("#bttn_reenviar_correo_proceso").show();
                                        $("#bttn_reenviar_correo_prorroga").hide();
                                        id_status_privado=4;
					}); 
                                        $("#bttn_reenviar_correo_proceso").on("click", function ()
                                        {
                                            var id = jQuery('#solicitudes').jqGrid('getGridParam','selrow'); 
                                            if(id)
                                            {  
                                                $.nmManual('ReenviarCorreoUnidad?id_solicitud=' + id,
                                                {
							callbacks: {
								afterShowCont: function(nm) {
								UAIP.Solicitudes.reenviar();

								}
							}},
					        {modal: false},
						{ sizes: {
						initW: '100px',
						initH: 	'400px'
					    }});
                                            }
                                            else
                                                alert("Debe seleccionar una solicitud del listado.")
                    
                                        });
                                    },
                                        
                                        reenviar: function(){
				$(document).on('click', '#btn_reenviar_correo_uni', function(e){                                             
                                            var id = jQuery('#solicitudes').jqGrid('getGridParam','selrow'); 
                                            if(id)
                                            {  
                                                var ret = jQuery('#solicitudes').jqGrid('getRowData',id); 
                                                console.log( ret.id_solicitud );
                                                var checkunidad = new Array();
                                                $('input[name="checar_unidad[]"]:checked').each(function() {
                                                        checkunidad.push($(this).val());
                                                });
                                                console.log(checkunidad);
                                                $("#dv_loader").html("<img src='/media/cargando.gif'> Enviando correo, espere...");
                                                
                                                if(ajax_reenviar_correo_uni==null)
                                                {
                                                    ajax_reenviar_correo_uni=$.ajax({
                                                    url: 'ReenviarCorreoProceso',
                                                    data: { id_solicitud: ret.id_solicitud,id_unidad:checkunidad},
                                                    dataType: 'JSON',
                                                    method: 'POST'
                                                    }).done(function(r){
                                                        $("#dv_loader").html("");
                                                        alert(r.mensaje);
                                                        ajax_reenviar_correo_uni=null;
                                                    });
                                                }
                                            }
                                            
                                            
                                    });
			},
			prorroga: function(){
				$(document).on('click', '#prorroga', function(e){
					$( "#nuevas" ).removeClass( "btn-info" );
					$( "#incompletas" ).removeClass( "btn-info" );
					$( "#completadas" ).removeClass( "btn-info" );
					$( "#orientadas" ).removeClass( "btn-info" );
					$( "#proceso" ).removeClass( "btn-info" );
					$( "#prorroga" ).addClass( "btn-info" );
					$( "#condicionada" ).removeClass( "btn-info" );
					$( "#cancelada" ).removeClass( "btn-info" );
					$( "#liberada" ).removeClass( "btn-info" );
					$( "#no_entregada" ).removeClass( "btn-info" );
					$( "#revision" ).removeClass( "btn-info" );
                                            $( "#bttn_ver_improcedente" ).removeClass( "btn-info" );
                                        $solicitudes.setGridParam({url:'DataSolicitudes?id_estatus=5'});
					$solicitudes.trigger('reloadGrid');
                                        $solicitudes.jqGrid('hideCol', ['fecha_limite_completar']);
                                        $solicitudes.jqGrid('showCol', ['fecha_prorroga']);
                                        $("#bttn_reenviar_correo").hide();
                                        $("#bttn_reenviar_correo_proceso").hide();
                                        $("#bttn_reenviar_correo_prorroga").show();
                                        id_status_privado=5;
                                        
                                        $("#bttn_reenviar_correo_prorroga").on("click", function ()
                                        {
                                            var id = jQuery('#solicitudes').jqGrid('getGridParam','selrow'); 
                                            if(id)
                                            {  
                                                var ret = jQuery('#solicitudes').jqGrid('getRowData',id); 
                                                
                                                if($ajax_reenviar_correo_prorroga==null)
                                                {
                                                    $("#dv_loader").html("<img src='/media/cargando.gif'> Enviando correo, espere...");
                                                    $ajax_reenviar_correo_prorroga=$.ajax({
                                                    url: 'ReenviarCorreoProrroga',
                                                    data: { id_solicitud: ret.id_solicitud  },
                                                    dataType: 'JSON',
                                                    method: 'POST'
                                                    }).done(function(r){
                                                            $ajax_reenviar_correo_prorroga=null;
                                                        $("#dv_loader").html("");
                                                        alert(r.mensaje);
                                                    });
                                                }
                  
                                            }
                                            else
                                                alert("Para enviar el correo en prorroga, debe seleccionar una solicitud del listado.");
                    
                                        });
				});
			},
			condicionada: function(){
				$(document).on('click', '#condicionada', function(e){
					$( "#nuevas" ).removeClass( "btn-info" );
					$( "#incompletas" ).removeClass( "btn-info" );
					$( "#completadas" ).removeClass( "btn-info" );
					$( "#orientadas" ).removeClass( "btn-info" );
					$( "#proceso" ).removeClass( "btn-info" );
					$( "#prorroga" ).removeClass( "btn-info" );
					$( "#condicionada" ).addClass( "btn-info" );
					$( "#cancelada" ).removeClass( "btn-info" );
					$( "#liberada" ).removeClass( "btn-info" );
					$( "#no_entregada" ).removeClass( "btn-info" );
					$( "#revision" ).removeClass( "btn-info" );
                                            $( "#bttn_ver_improcedente" ).removeClass( "btn-info" );
				    $solicitudes.setGridParam({url:'DataSolicitudes?id_estatus=6'});
					$solicitudes.trigger('reloadGrid');
                                        $solicitudes.jqGrid('hideCol', ['fecha_prorroga','fecha_limite_completar']);
                                        $("#bttn_reenviar_correo").hide();
                                        $("#bttn_reenviar_correo_proceso").hide();
                                        $("#bttn_reenviar_correo_prorroga").hide();
                                        id_status_privado=6;
				});
			},
			cancelada: function(){
				$(document).on('click', '#cancelada', function(e){
					$( "#nuevas" ).removeClass( "btn-info" );
					$( "#incompletas" ).removeClass( "btn-info" );
					$( "#completadas" ).removeClass( "btn-info" );
					$( "#orientadas" ).removeClass( "btn-info" );
					$( "#proceso" ).removeClass( "btn-info" );
					$( "#prorroga" ).removeClass( "btn-info" );
					$( "#condicionada" ).removeClass( "btn-info" );
					$( "#cancelada" ).addClass( "btn-info" );
					$( "#liberada" ).removeClass( "btn-info" );
					$( "#no_entregada" ).removeClass( "btn-info" );
					$( "#revision" ).removeClass( "btn-info" );
                                            $( "#bttn_ver_improcedente" ).removeClass( "btn-info" );
				    $solicitudes.setGridParam({url:'DataSolicitudes?id_estatus=7'});
					$solicitudes.trigger('reloadGrid');
                                        $solicitudes.jqGrid('hideCol', ['fecha_prorroga','fecha_limite_completar']);
                                        $("#bttn_reenviar_correo").hide();
                                        $("#bttn_reenviar_correo_proceso").hide();
                                        $("#bttn_reenviar_correo_prorroga").hide();
                                        id_status_privado=7;
				});
			},
			liberada: function(){
				$(document).on('click', '#liberada', function(e){
					$( "#nuevas" ).removeClass( "btn-info" );
					$( "#incompletas" ).removeClass( "btn-info" );
					$( "#completadas" ).removeClass( "btn-info" );
					$( "#orientadas" ).removeClass( "btn-info" );
					$( "#proceso" ).removeClass( "btn-info" );
					$( "#prorroga" ).removeClass( "btn-info" );
					$( "#condicionada" ).removeClass( "btn-info" );
					$( "#cancelada" ).removeClass( "btn-info" );
					$( "#liberada" ).addClass( "btn-info" );
					$( "#no_entregada" ).removeClass( "btn-info" );
                                        $( "#revision" ).removeClass( "btn-info" );
                                        $( "#bttn_ver_improcedente" ).removeClass( "btn-info" );
                                        $solicitudes.setGridParam({url:'DataSolicitudes?id_estatus=8'});
                                        $solicitudes.trigger('reloadGrid');
                                        $solicitudes.jqGrid('hideCol', ['fecha_prorroga','fecha_limite_completar']);
                                        $("#bttn_reenviar_correo").show();
                                        $("#bttn_reenviar_correo_proceso").hide();
                                        $("#bttn_reenviar_correo_prorroga").hide();
                                        id_status_privado=8;
                                        
                                    
                                    
                                    $("#bttn_reenviar_correo").on("click", function ()
                                        {
                                            var id = jQuery('#solicitudes').jqGrid('getGridParam','selrow'); 
                                            if(id)
                                            {  
                                                var ret = jQuery('#solicitudes').jqGrid('getRowData',id); 
                                                console.log( ret.id_solicitud );
                                                
                                                $("#dv_loader").html("<img src='/media/cargando.gif'> Enviando correo, espere...");
                                                $.ajax({
                                                url: 'ReenviarCorreo',
                                                data: { id_solicitud: ret.id_solicitud  },
                                                dataType: 'JSON',
                                                method: 'POST'
                                                }).done(function(r){
                                                    $("#dv_loader").html("");
                                                    alert(r.mensaje);
                                                });
                                            }
                                            else
                                                alert("Para enviar el correo de respondido, debe seleccionar una solicitud del listado.")
                    
                                        });
				});
                                },
                                            
			
                        
			no_entregada: function(){
				$(document).on('click', '#no_entregada', function(e){
					$( "#nuevas" ).removeClass( "btn-info" );
					$( "#incompletas" ).removeClass( "btn-info" );
					$( "#completadas" ).removeClass( "btn-info" );
					$( "#orientadas" ).removeClass( "btn-info" );
					$( "#proceso" ).removeClass( "btn-info" );
					$( "#prorroga" ).removeClass( "btn-info" );
					$( "#condicionada" ).removeClass( "btn-info" );
					$( "#cancelada" ).removeClass( "btn-info" );
					$( "#liberada" ).removeClass( "btn-info" );
					$( "#no_entregada" ).addClass( "btn-info" );
					$( "#revision" ).removeClass( "btn-info" );
                                        $( "#bttn_ver_improcedente" ).removeClass( "btn-info" );
                                        $solicitudes.setGridParam({url:'DataSolicitudes?id_estatus=9'});
					$solicitudes.trigger('reloadGrid');
                                        $solicitudes.jqGrid('hideCol', ['fecha_prorroga','fecha_limite_completar']);
                                        $("#bttn_reenviar_correo").hide();
                                        $("#bttn_reenviar_correo_proceso").hide();
                                        $("#bttn_reenviar_correo_prorroga").hide();
                                        id_status_privado=9;
				});
			},
			rechazada_ue: function(){
				$(document).on('click', '#rechazada_ue', function(e){
					$( "#nuevas" ).removeClass( "btn-info" );
					$( "#incompletas" ).removeClass( "btn-info" );
					$( "#completadas" ).removeClass( "btn-info" );
					$( "#orientadas" ).removeClass( "btn-info" );
					$( "#proceso" ).removeClass( "btn-info" );
					$( "#prorroga" ).removeClass( "btn-info" );
					$( "#condicionada" ).removeClass( "btn-info" );
					$( "#cancelada" ).removeClass( "btn-info" );
					$( "#liberada" ).removeClass( "btn-info" );
					$( "#no_entregada" ).addClass( "btn-info" );
					$( "#revision" ).removeClass( "btn-info" );
                                        $( "#bttn_ver_improcedente" ).removeClass( "btn-info" );
                                        $solicitudes.setGridParam({url:'DataSolicitudes?id_estatus=10'});
					$solicitudes.trigger('reloadGrid');
                                        $solicitudes.jqGrid('hideCol', ['fecha_prorroga','fecha_limite_completar']);
                                        $("#bttn_reenviar_correo").hide();
                                        $("#bttn_reenviar_correo_proceso").hide();
                                        $("#bttn_reenviar_correo_prorroga").hide();
                                        id_status_privado=10;
				});
			},
			revision: function(){
				$(document).on('click', '#revision',function(e){
					$( "#nuevas" ).removeClass( "btn-info" );
					$( "#incompletas" ).removeClass( "btn-info" );
					$( "#completadas" ).removeClass( "btn-info" );
					$( "#orientadas" ).removeClass( "btn-info" );
					$( "#proceso" ).removeClass( "btn-info" );
					$( "#prorroga" ).removeClass( "btn-info" );
					$( "#condicionada" ).removeClass( "btn-info" );
					$( "#cancelada" ).removeClass( "btn-info" );
					$( "#liberada" ).removeClass( "btn-info" );
					$( "#no_entregada" ).removeClass( "btn-info" );
                                        $( "#bttn_ver_improcedente" ).removeClass( "btn-info" );
					$( "#revision" ).addClass( "btn-info" );
                                        $solicitudes.setGridParam({url:'DataSolicitudes?id_estatus=11'});
					$solicitudes.trigger('reloadGrid');
                                        $solicitudes.jqGrid('hideCol', ['fecha_prorroga','fecha_limite_completar']);
                                        $("#bttn_reenviar_correo").hide();
                                        $("#bttn_reenviar_correo_proceso").hide();
                                        $("#bttn_reenviar_correo_prorroga").hide();
                                        id_status_privado=11;
				});
			},
			medio_respuesta: function(){
				$(document).on('change', '#medio_respuesta',function(e){
				    var medio_respuesta=$('#medio_respuesta').get(0).value;
				    if(medio_respuesta > 2){
				    	//$('#aparece_envio').load('TipoEnvio', {vacio:0 } );

				    	$( "#aparece_envio" ).show();
				    	$("#tipo_envio").attr("required", "true");
				    }else{
				    	//$('#aparece_envio').load('TipoEnvio', {vacio:1 });
				    	$( "#aparece_envio" ).hide();
				    	$("#tipo_envio").removeAttr("required", "false");

				    }
				});
			},
			inprocedentes: function(){
				$(document).on('click', '#bttn_ver_improcedente', function(e){
					$( "#nuevas" ).removeClass( "btn-info" );
					$( "#incompletas" ).removeClass( "btn-info" );
					$( "#completadas" ).removeClass( "btn-info" );
					$( "#orientadas" ).removeClass( "btn-info" );
					$( "#proceso" ).removeClass( "btn-info" );
					$( "#prorroga" ).removeClass( "btn-info" );
					$( "#condicionada" ).removeClass( "btn-info" );
					$( "#cancelada" ).removeClass( "btn-info" );
					$( "#liberada" ).removeClass( "btn-info" );
					$( "#no_entregada" ).removeClass( "btn-info" );
					$( "#revision" ).removeClass( "btn-info" );
                                        $( "#bttn_ver_improcedente" ).addClass( "btn-info" );
				        $solicitudes.setGridParam({url:'DataSolicitudes?id_estatus=15'});
					$solicitudes.trigger('reloadGrid');
                                        $solicitudes.jqGrid('hideCol', ['fecha_prorroga','fecha_limite_completar']);
                                        $("#bttn_reenviar_correo").hide();
                                        $("#bttn_reenviar_correo_proceso").hide();
                                        $("#bttn_reenviar_correo_prorroga").hide();
                                        id_status_privado=15;
				});
			},
			nueva_solicitud_info: function(){
				$(document).on('click', '#nueva_solicitud', function(e){

					/*$('#aparece_nueva_sol').load('NuevaSolicitud');
					$('#NuevaModal').modal({

						}).css({
						width: '850px',
						'margin-left': function () {
						return -($(this).width() / 2);
						}
					});
					$nueva_modal.modal("show"); */

						$.nmManual('NuevaSolicitud',{
							callbacks: {
								afterShowCont: function(nm) {
								UAIP.Solicitudes.medio_respuesta();
								UAIP.Solicitudes.enviar_formulario();
								}
							}},
						 {modal: false},
						{ sizes: {
						width: '850px',
						'margin-left': function () {
						return -($(this).width() / 2);
						}
					}});
				});
			},
			solicitud_historial: function(){
                    $(document).on('click', '.search-result', function(){
                        console.log($(this).data('id_solicitud'));
                        $('#apareceHistoria').load('/index.php/solicitudes/DetalleSolicitudHistorial', {id_solicitud: $(this).data('id_solicitud')});
                        $('#ModalHistorial').modal({
                                }).css({
                                width: '850px',
                                'margin-left': function () {
                                return -($(this).width() / 2);
                                }
                                });
                        $("#ModalHistorial").modal("show");
                        $('#ModalHistorial').on('shown', function() {

                            });
                    });
            },
			cerrar_modal: function(){
				$(document).on('click', '#cerrar', function(e){

				$.nmTop().close();


				});
			},
			enviar_formulario: function(){
						$("#form_registro").submit(function(e){
                                                        $("#dv_loader").html("<img src='/media/cargando.gif'> Procesando solicitud, espere...");
							$.ajax({
							url: 'AgregarSol',
							type: 'post',
							dataType:'html',   //expect return data as html from server
							data: $("#form_registro").serialize(),
							success: function(response, textStatus, jqXHR){
							$('#nueva_realizado').html(response);   //select the id and put the response in the html
                                                        $("#dv_loader").html("");
							},
							error: function(jqXHR, textStatus, errorThrown){
								alert('error');
							console.log('error(s):'+textStatus, errorThrown);
                                                        $("#dv_loader").html("");
							}
							});
							 e.preventDefault();
							 $.nmTop().close();
							  $solicitudes.trigger('reloadGrid');

							//UAIP.Solicitudes.cerrar_modal();

						});

			},

			consultar_empleado: function(id){
				$('#aparece').load('DetalleSolicitud', {id_solicitud: id});
				$('#myModal').modal({

					}).css({
					width: '850px',
					'margin-left': function () {
					return -($(this).width() / 2);
					}
				});
				$("#myModal").modal("show");
					$('#myModal').on('shown', function() {
                                });
			},
                        

                        
			canalizar: function(){
				$(document).on('click', '#canalizar_sol', function(e){
					var mi_id_solicitud=$('#mi_id_solicitud').get(0).value;
                                        comentario_unidad=[];
					$.nmManual('CanalizarSolicitud?id_solicitud=' + mi_id_solicitud,{
							callbacks: {
								afterShowCont: function(nm) {
								UAIP.Solicitudes.canalizar_guardar();

								}
							}},
						 {modal: false},
						{ sizes: {
						initW: '100px',
						initH: 	'400px'
					    }});
				});
			},
			canalizar_guardar: function(){
						       $(document).on('click', '#btn_agregar_uenlace_comentario', function(e){
                                                        var id=$("#mi_id_solicitud").get(0).value;
                                                        var ue=$("#unidad_enlace").get(0).value;
                                                        var uet=$("#unidad_enlace option:selected").html();
                                                        var come=$("#observaciones_cana").get(0).value;
                                                 
                                                 if(document.getElementById("checar_comentario").checked == true)
                                                           {
                                                      
//                                                                $("#unidad_enlace").prop("value",''); 
                                                                   $("#observaciones_cana").prop("value",'');
                                                         
                                                            }
                                                 
                                                        //$("#tbl_lista_unidades_enlace").html("");  
                                                        //$("#tbl_lista_unidades_enlace").append("<tr><td>Unidad</td><td>Comentario</td></tr>");
                                                        if($.trim(ue)==''||$.trim(come)=='')
                                                            alert("Unidad no especificada o comentario no redactado");
                                                        else
                                                        { 
                                                            var ban_continuar=1;                                                            
                                                            $.each(comentario_unidad, function(i, elem)
                                                            {                                          
                                                               if(ue==elem.cc_id_unidad_enlace)
                                                               {
                                                                   ban_continuar=0;
                                                                     $("#unidad_enlace").prop("value",''); 
//                                                                   $("#observaciones_cana").prop("value",'');
                                                               }
                                                            });
                                                            
                                                            if(ban_continuar==0)
                                                            {
                                                                alert("La unidad de enlace ya ha sido agregada.");
                                                                $("#unidad_enlace").prop("value",''); 
//                                                               $("#observaciones_cana").prop("value",'');
                                                            }
                                                            else
                                                            {
                                                               var contenido_canalizado = {};
                                                               contenido_canalizado.cc_id_solicitud=id;
                                                               contenido_canalizado.cc_id_unidad_enlace=$.trim(ue);
                                                               contenido_canalizado.cc_unidad_enlace=$.trim(uet);
                                                               contenido_canalizado.cc_observaciones=$.trim(come);
                                                               comentario_unidad.push(contenido_canalizado);
                                                               
                                                               $("#tbl_lista_archivo_respuesta").html(""); 
                                                               $("#tbl_lista_unidades_enlace").append("<tr><td>" + ue + "</td><td>" + uet + "</td><td>" + come + "</td><td><a href='#' class='elimina_ucomen' data-id_unidad_enlace='"+ue+"' >Eliminar</a></td></tr>");
                                                               
                                                               $("#unidad_enlace").prop("value",''); 
//                                                               $("#observaciones_cana").prop("value",''); 
                                                            }
                                                        }
//                        
                                                    });
                                                    
                                                    $(document).on('click',"a.elimina_ucomen",function() {
                                                        
                                                          var id_enlace = $(this).data("id_unidad_enlace");
                                                    
                                                            $("#tbl_lista_unidades_enlace").html("");
                                                            $("#tbl_lista_unidades_enlace").append("<td><h5 align='center'>ID</h5></td><td><h5 align='center'>Unidad</h5></td><td><h5 align='center'>Comentario</h5></td><td><h5 align='center'>Acción</h5></td>");
                                                            var archivos_adjuntos_raux=[];                                                                      
                                                          $.each(comentario_unidad, function(i, r)
                                                        {
                                                           
                                                           if(r.cc_id_unidad_enlace!=id_enlace)
                                                           {
                                                               var contenido_canalizado = {};
                                                               contenido_canalizado.cc_id_solicitud=r.cc_id_solicitud;
                                                               contenido_canalizado.cc_id_unidad_enlace=r.cc_id_unidad_enlace;
                                                               contenido_canalizado.cc_unidad_enlace=r.cc_unidad_enlace;
                                                               contenido_canalizado.cc_observaciones=r.cc_observaciones;
                                                               archivos_adjuntos_raux.push(contenido_canalizado);

                                                               $("#tbl_lista_unidades_enlace").append("<tr><td>" +r.cc_id_unidad_enlace+ "</td><td>" + r.cc_unidad_enlace + "</td><td>" + r.cc_observaciones + "</td><td><a href='#' class='elimina_ucomen' data-id_unidad_enlace='"+r.cc_id_unidad_enlace+"' >Eliminar</a></td></tr>");
                                                           }
                                                        });
                                                        comentario_unidad=[];
                                                        comentario_unidad=archivos_adjuntos_raux;
                                                        
//                                                        $("#tbl_lista_unidades_enlace").html("");
//                                                        $("#tbl_lista_unidades_enlace").append("<td><h5 align='center'>ID</h5></td><td><h5 align='center'>Unidad</h5></td><td><h5 align='center'>Comentario</h5></td><td><h5 align='center'>Acción</h5></td>");
                                                        });
                                                    
                                                    $(document).on('click', '#canalizar_guardar', function(e){
                                                        if(comentario_unidad.length>0)
                                                        {
                                                            var id=$("#mi_id_solicitud").get(0).value;
                                                            $("#canalizar_guardar").prop("disabled", true);
//                                                            $("#btn_canalizar_cerrar").prop("disabled", true);
                                                            $("#lbl_lodaer_canalizar").html("<img src='/media/cargando.gif'> Procesando solicitud, espere...");
                                                            
                                                            if($ajax_canalizar_solicitud_2==null)
                                                            {
                                                            $ajax_canalizar_solicitud_2=$.ajax({
                                                            url: 'CanalizarGuardar',
                                                            type: 'post',
                                                            dataType:'json',   
                                                            data: { data:  JSON.stringify(comentario_unidad),id: id }
                                                            }).done(function(r){
//                                                                $("#canalizar_guardar").prop("disabled", false);
//                                                                $("#btn_canalizar_cerrar").prop("disabled", false);
                                                                $ajax_canalizar_solicitud_2=null;                                                            
                                                                switch(r.id_tipo_error)
                                                                {
                                                                    case 1:
                                                                    {
                                                                        $("#dv_loader").html("");
                                                                        comentario_unidad=[];
                                                                        $("#tbl_lista_unidades_enlace").html("");  
                                                                        $("#tbl_lista_unidades_enlace").append("<tr><td>Unidad</td><td>Comentario</td></tr>");
                                                                        $nueva_modal.modal("hide");
                                                                        $.nmTop().close();
                                                                        $solicitudes.trigger('reloadGrid');
                                                                        alert(r.mensaje);
                                                                        break;
                                                                    }
                                                                }
                                                            });
                                                            $("#sp_lodaer_canalizar").html("");
                                                            }
                                                        }
                                                        else
                                                            alert("Para canalizar una solicitud, debe agregar la(s) unidade(s).");
                                                    });
                                                    
                                                     $(document).on('click', '#btn_canalizar_cerrar', function(e){
                                                        $("#dv_loader").html("");
                                                        comentario_unidad=[];
                                                        $("#tbl_lista_unidades_enlace").html("");  
                                                        $("#tbl_lista_unidades_enlace").append("<tr><td>Unidad</td><td>Comentario</td></tr>");
                                                        $nueva_modal.modal("hide");
                                                        $.nmTop().close();
                                                     });
                                                },
                                             
			orientar_sol: function(){
				$(document).on('click', '#btn_orientada_uaip', function(e){
					var mi_id_solicitud=$('#mi_id_solicitud').get(0).value;

					$.nmManual('OrientarSolicitud?id_solicitud=' + mi_id_solicitud,{
							callbacks: {
								afterShowCont: function(nm) {
								UAIP.Solicitudes.orientar_sol_guardar();
								}
							}},
						 {modal: false},
						{ sizes: {
						initW: '100px',
						initH: 	'400px'
					}});
				});
			},
			orientar_sol_guardar: function(){
						$("#form_orientar_sol").submit(function(e){
                                                        $("#dv_loader").html("<img src='/media/cargando.gif'> Procesando solicitud, espere...");
							$.ajax({
							url: 'OrientarGuardar',
							type: 'post',
							dataType:'html',   //expect return data as html from server
							data: $("#form_orientar_sol").serialize(),
							success: function(response, textStatus, jqXHR){
							$('#nueva_realizado').html(response);   //select the id and put the response in the html
                                                        $("#dv_loader").html("");
							},
							error: function(jqXHR, textStatus, errorThrown){
								alert('error');
							console.log('error(s):'+textStatus, errorThrown);
                                                        $("#dv_loader").html("");
							}
							});
							 e.preventDefault();
							 $.nmTop().close();
							  $solicitudes.trigger('reloadGrid');

							//UAIP.Solicitudes.cerrar_modal();

						});

			},
			cancelar_sol:function(){
				$(document).on('click', '#btn_cancelar', function(e){
					var mi_id_solicitud=$('#mi_id_solicitud').get(0).value;

					$.nmManual('CancelarSolicitud?id_solicitud=' + mi_id_solicitud,{
							callbacks: {
								afterShowCont: function(nm) {
								UAIP.Solicitudes.cancelar_guardar();

								}
							}},
						 {modal: false},
						{ sizes: {
						width: '100px',
						heigth: 	'700px'
					    }});

				});
			},
			cancelar_guardar: function(){
						        $("#form_cancelar").submit(function(e){
                                                        $("#dv_loader").html("<img src='/media/cargando.gif'> Procesando solicitud, espere...");
							$.ajax({
							url: 'CancelarGuardar',
							type: 'post',
							dataType:'html',   //expect return data as html from server
							data: $("#form_cancelar").serialize(),
							success: function(response, textStatus, jqXHR){
							$('#nueva_realizado').html(response);   //select the id and put the response in the html
                                                        $("#dv_loader").html("");
							},
							error: function(jqXHR, textStatus, errorThrown){
								alert('error');
							console.log('error(s):'+textStatus, errorThrown);
                                                        $("#dv_loader").html("");
							}
							});
							 e.preventDefault();
							 $.nmTop().close();
							  $solicitudes.trigger('reloadGrid');

							//UAIP.Solicitudes.cerrar_modal();

						});

			},
			incompleta_sol:function(){
				$(document).on('click', '#btn_incompleta', function(e){
					var mi_id_solicitud=$('#mi_id_solicitud').get(0).value;


					$.nmManual('IncompletaSolicitud?id_solicitud=' + mi_id_solicitud,{
							callbacks: {
								afterShowCont: function(nm) {
								UAIP.Solicitudes.incompleta_guardar();

								}
							}},
						 {modal: false},
						{ sizes: {
						width: '100px',
						heigth: 	'700px'
					    }});



				});
			},
			incompleta_guardar: function(){
						$("#form_incompleta").submit(function(e){
                                                        $("#dv_loader").html("<img src='/media/cargando.gif'> Procesando solicitud, espere...");
							$.ajax({
							url: 'IncompletaGuardar',
							type: 'post',
							dataType:'html',   //expect return data as html from server
							data: $("#form_incompleta").serialize(),
							success: function(response, textStatus, jqXHR){
							$('#nueva_realizado').html(response);   //select the id and put the response in the html
                                                        $("#dv_loader").html("");
							},
							error: function(jqXHR, textStatus, errorThrown){
								alert('error');
							console.log('error(s):'+textStatus, errorThrown);
                                                        $("#dv_loader").html("");
							}
							});
							 e.preventDefault();
							 $.nmTop().close();
							  $solicitudes.trigger('reloadGrid');

							//UAIP.Solicitudes.cerrar_modal();

						});

			},
			completar_sol:function(){
				$(document).on('click', '#btn_completar', function(e){
					var mi_id_solicitud=$('#mi_id_solicitud').get(0).value;


					$.nmManual('CompletarSolicitud?id_solicitud=' + mi_id_solicitud,{
							callbacks: {
								afterShowCont: function(nm) {
								UAIP.Solicitudes.completar_guardar();

								}
							}},
						 {modal: false},
						{ sizes: {
						width: '100px',
						heigth: 	'700px'
					    }});



				});
			},
			completar_guardar: function(){
						$("#form_completar").submit(function(e){
                                                        $("#dv_loader").html("<img src='/media/cargando.gif'> Procesando solicitud, espere...");
							$.ajax({
							url: 'CompletarGuardar',
							type: 'post',
							dataType:'html',   //expect return data as html from server
							data: $("#form_completar").serialize(),
							success: function(response, textStatus, jqXHR){
							$('#nueva_realizado').html(response);   //select the id and put the response in the html
                                                        $("#dv_loader").html("");
							},
							error: function(jqXHR, textStatus, errorThrown){
								alert('error');
							console.log('error(s):'+textStatus, errorThrown);
                                                        $("#dv_loader").html("");
							}
							});
							 e.preventDefault();
							 $.nmTop().close();
							  $solicitudes.trigger('reloadGrid');

							//UAIP.Solicitudes.cerrar_modal();

						});

			},
			prorroga_sol:function(){
				$(document).on('click', '#btn_prorroga', function(e){
					var mi_id_solicitud=$('#mi_id_solicitud').get(0).value;


					$.nmManual('ProrrogaSolicitud?id_solicitud=' + mi_id_solicitud,{
							callbacks: {
								afterShowCont: function(nm) {
								UAIP.Solicitudes.prorroga_guardar();

								}
							}},
						 {modal: false},
						{ sizes: {
						width: '100px',
						heigth: '700px'
					    }});



				});
			},
			prorroga_guardar: function(){
						$("#form_prorroga").submit(function(e){
                                                        $("#dv_loader").html("<img src='/media/cargando.gif'> Procesando solicitud, espere...");
							$.ajax({
							url: 'ProrrogaGuardar',
							type: 'post',
							dataType:'html',   //expect return data as html from server
							data: $("#form_prorroga").serialize(),
							success: function(response, textStatus, jqXHR){
							$('#nueva_realizado').html(response);   //select the id and put the response in the html
                                                        $("#dv_loader").html("");
							},
							error: function(jqXHR, textStatus, errorThrown){
								alert('error');
								console.log('error(s):'+textStatus, errorThrown);
                                                                $("#dv_loader").html("");
							}
							});
							 e.preventDefault();
							 $.nmTop().close();
							  $solicitudes.trigger('reloadGrid');

							//UAIP.Solicitudes.cerrar_modal();

						});

			},
			usuario_editar: function(){
						$("#form_editar").submit(function(e){
                                                        $("#dv_loader").html("<img src='/media/cargando.gif'> Procesando solicitud, espere...");
							$.ajax({
							url: 'ActualizarUsuario',
							type: 'post',
							dataType:'html',   //expect return data as html from server
							data: $("#form_editar").serialize(),
							success: function(response, textStatus, jqXHR){
							$('#nueva_realizado').html(response);   //select the id and put the response in the html
                                                        $("#dv_loader").html("");
							},
							error: function(jqXHR, textStatus, errorThrown){
								alert('error');
							console.log('error(s):'+textStatus, errorThrown);
                                                        $("#dv_loader").html("");
							}
							});
							 e.preventDefault();
							 $.nmTop().close();
							  $solicitudes.trigger('reloadGrid');

							//UAIP.Solicitudes.cerrar_modal();

						});

			},

			traer_datos_usr:function(){
				$(document).on('click', '#datos_usr', function(e){
					var id_usuario=$('#id_usuario_sesion').get(0).value;
					var tipo_usuario_sesion=$('#tipo_usuario_sesion').get(0).value;

					$.nmManual('VerDatosUsuario?id_usuario=' + id_usuario + '&tipo_usuario=' + tipo_usuario_sesion,{
							callbacks: {
								afterShowCont: function(nm) {
								UAIP.Solicitudes.estados();
								UAIP.Solicitudes.usuario_editar();
								UAIP.Solicitudes.agregar_fecha_admin();
								}
							}},
						 {modal: false},
						{ sizes: {
						width: '100px',
						heigth: '700px'
					    }});
				});
			},
			agregar_fecha_admin:function(){
				$(document).on('click', '#agregar_fecha', function(e){
					var fecha=$('#dia_inhabil').get(0).value;
					$('#mis_dias').load('AgregarInhabil',{fecha:fecha});


				});
			},

			 estados: function(){
                $(document).on('change', '#pais',function(e){
                    var pais=$('#pais').get(0).value;
                    $('#estados').load('CargaEstados',{id_pais:pais});

                });
            },
			responder_sol:function(){
				$(document).on('click', '#btn_responder_uaip', function(e){
					var mi_id_solicitud=$('#mi_id_solicitud').get(0).value;
					var id_unidad=$('#mi_id_unidad').get(0).value;



					$.nmManual('ResponderSolicitud?id_solicitud=' + mi_id_solicitud,{
							callbacks: {
								afterShowCont: function(nm) {
								UAIP.Solicitudes.responder_guardar();
								UAIP.Solicitudes.cargar_archivos_enlace(mi_id_solicitud,id_unidad);

								}
							}},
						 {modal: false},
						{ sizes: {
						width: '100px',
						heigth: '700px'
					    }});



				});
			},
			responder_guardar: function(){
						$("#form_responder").submit(function(e){
                                                        $("#dv_loader").html("<img src='/media/cargando.gif'> Procesando solicitud, espere...");
							$.ajax({
							url: 'ResponderGuardar',
							type: 'post',
							dataType:'html',   //expect return data as html from server
							data: $("#form_responder").serialize(),
							success: function(response, textStatus, jqXHR){
							$('#nueva_realizado').html(response);   //select the id and put the response in the html
                                                        $("#dv_loader").html("");
							},
							error: function(jqXHR, textStatus, errorThrown){
								alert('error');
							console.log('error(s):'+textStatus, errorThrown);
                                                        $("#dv_loader").html("");
							}
							});
							 e.preventDefault();
							 $.nmTop().close();
							  $solicitudes.trigger('reloadGrid');

							//UAIP.Solicitudes.cerrar_modal();

						});

			},
			agregar_etiqueta:function(){
				$(document).on('click', '#btn_etiqueta', function(e){
					e.preventDefault();
					var mi_id_solicitud=$('#id_solicitud').get(0).value;
					var etiqueta=$('#etiqueta').get(0).value;
					//$('#btn_etiqueta').click(false);

					if(etiqueta){
						$('#cargar_etiquetas').load('AgregarEtiqueta', {id_solicitud: mi_id_solicitud,etiqueta: etiqueta});
					}

				});
			},
			activar_boton:function(){
                            $(document).ready(function(){				     
                                $('#tags').tagsManager();
                            });
			},
			obtenertags:function(){
                            $(document).ready(function(){						
//                                $("#tags").autocomplete({
//                                source: "obtener_tags"
//                                });	

                                $('#tags').typeahead({
                                        source: function (query, process){
                                            $.ajax({
                                                    url: 'obtener_tags',
                                                    data: { term: $('#tags').get(0).value },
                                                    dataType: 'JSON'
                                            }).done(function(response){
                                                process(response);
                                                $("ul.typeahead").find("li").removeClass("active");
                                            });
                                        }
                                    });
                            });
			},

			liberar_sol:function(){
				$(document).on('click', '#btn_liberar', function(e){
                                        archivos_adjuntos_r=[];
					var mi_id_solicitud=$('#mi_id_solicitud').get(0).value;

					$.nmManual('LiberarSolicitud?id_solicitud=' + mi_id_solicitud,{
							callbacks: {
								afterShowCont: function(nm) {
								UAIP.Solicitudes.liberar_guardar();
								UAIP.Solicitudes.agregar_etiqueta();
								UAIP.Solicitudes.activar_boton();
								UAIP.Solicitudes.obtenertags();
								UAIP.Solicitudes.cargar_archivos_respuesta(mi_id_solicitud);
								}
							}},
						 {modal: false},
						{ sizes: {
						width: '100px',
						heigth: '700px'
					    }});
				});
			},
			liberar_guardar: function(){
						$("#form_liberar").submit(function(e){
						$('#nueva_liberar').prepend('<img id="theImg" src="#" />Algo');

							var hddTags = $("[name='hidden-tags']").get(0).value;                                                        
                                                        var dtag=hddTags.split(",");
                                                        
                                                        
                                                        
							var arr = [];                                                   
                                                        $.each(dtag, function(idx, e){
                                                                arr.push(e);
                                                        });
                                
							 var etiquegas = JSON.stringify(arr);

                                                        $("#dv_loader").html("<img src='/media/cargando.gif'> Procesando solicitud, espere...");
							$.ajax({
							url: 'LiberarGuardar',
							type: 'post',
							dataType:'html',   //expect return data as html from server
							data: $("#form_liberar").serialize() + "&etiquetas=" + etiquegas,
							success: function(response, textStatus, jqXHR){
							$('#nueva_realizado').html(response);   //select the id and put the response in the html
                                                        $("#dv_loader").html("");
							},
							error: function(jqXHR, textStatus, errorThrown){
								alert('error');
							console.log('error(s):'+textStatus, errorThrown);
                                                        $("#dv_loader").html("");
							}
							});
							 e.preventDefault();
							 $.nmTop().close();
							 $solicitudes.trigger('reloadGrid');

							//UAIP.Solicitudes.cerrar_modal();

						});

			},
			regresar_ue:function(id_unidad,mi_id_solicitud){
				//$(document).on('click', '#btn_regresaue', function(e){
					//var mi_id_solicitud=$('#mi_id_solicitud').get(0).value;
					//var id_unidad=$('#btn_regresaue').get(0).value;

					$.nmManual('RegresarSolicitud?id_solicitud=' + mi_id_solicitud + '&id_unidad='+id_unidad,{
							callbacks: {
								afterShowCont: function(nm) {
								UAIP.Solicitudes.regresar_ue_guardar();
								}
							}},
						 {modal: false},
						{ sizes: {
						width: '100px',
						heigth: '700px'
					    }});
				//});
			},
			regresar_ue_guardar: function(){
						$("#form_regresar").submit(function(e){

							li = $('.tag-list li')
							arr = []
							li.each(function(index, element){
								arr.push($(element).text());
							});
							 var etiquegas = JSON.stringify(arr);
                                                        
                                                        
                                                        $("#dv_loader").html("<img src='/media/cargando.gif'> Procesando solicitud, espere...");
							$.ajax({
							url: 'RegresarSolicitudGuardar',
							type: 'post',
							dataType:'html',   //expect return data as html from server
							data: $("#form_regresar").serialize() + "&etiquetas=" + etiquegas,
							success: function(response, textStatus, jqXHR){
							$('#nueva_realizado').html(response);   //select the id and put the response in the html
                                                        $("#dv_loader").html("");
							},
							error: function(jqXHR, textStatus, errorThrown){
								alert('error');
							console.log('error(s):'+textStatus, errorThrown);
                                                        $("#dv_loader").html("");
							}
							});
							 e.preventDefault();
							 $.nmTop().close();
							  $solicitudes.trigger('reloadGrid');

							//UAIP.Solicitudes.cerrar_modal();

						});

			},
			mar_entregada:function(){
				$(document).on('click', '#marcar_entregada', function(e){
					var mi_id_solicitud=$('#mi_id_solicitud').get(0).value;	
					$.nmManual('MarcarEntreSolicitud?id_solicitud=' + mi_id_solicitud,{
							callbacks: {
								afterShowCont: function(nm) {
								  UAIP.Solicitudes.marca_e_guardar();

								}
							}},
						 {modal: false},
						{ sizes: {
						width: '100px',
						heigth: '700px'
					    }});

					/*if(confirm('¿Marcar solicitud como Entregada?')){
						$('#nueva_realizado').load('EntregadaGuardar', {id_solicitud: mi_id_solicitud});
					}*/			
						 
				});
			},
			marca_e_guardar: function(){
						$("#form_marca_entrega").submit(function(e){
                                                        $("#dv_loader").html("<img src='/media/cargando.gif'> Procesando solicitud, espere...");
							$.ajax({
							url: 'EntregadaGuardar',
							type: 'post',
							dataType:'html',   //expect return data as html from server
							data: $("#form_marca_entrega").serialize(),
							success: function(response, textStatus, jqXHR){
							$('#nueva_realizado').html(response);   //select the id and put the response in the html
                                                        $("#dv_loader").html("");
							},
							error: function(jqXHR, textStatus, errorThrown){
								alert('error');
							console.log('error(s):'+textStatus, errorThrown);
                                                        $("#dv_loader").html("");
							}
							});
							 e.preventDefault();
							 $.nmTop().close();
							  $solicitudes.trigger('reloadGrid');

							//UAIP.Solicitudes.cerrar_modal();

						});

			},			
			mar_cn_entregada:function(){
				$(document).on('click', '#marcar_cn_entregada', function(e){
					var mi_id_solicitud=$('#mi_id_solicitud').get(0).value;	
					$.nmManual('MarcarNoEntreSolicitud?id_solicitud=' + mi_id_solicitud,{
							callbacks: {
								afterShowCont: function(nm) {
								  UAIP.Solicitudes.marca_ne_guardar();

								}
							}},
						 {modal: false},
						{ sizes: {
						width: '100px',
						heigth: '700px'
					    }});

					/*if(confirm('¿Marcar solicitud como No Entregada?')){
						$('#nueva_realizado').load('NoEntregadaGuardar', {id_solicitud: mi_id_solicitud});
					}*/			
						 
				});
			},
			marca_ne_guardar: function(){
						$("#form_marca_no_entrega").submit(function(e){
                                                        $("#dv_loader").html("<img src='/media/cargando.gif'> Procesando solicitud, espere...");
							$.ajax({
							url: 'NoEntregadaGuardar',
							type: 'post',
							dataType:'html',   //expect return data as html from server
							data: $("#form_marca_no_entrega").serialize(),
							success: function(response, textStatus, jqXHR){
							$('#nueva_realizado').html(response);   //select the id and put the response in the html
                                                        $("#dv_loader").html("");
							},
							error: function(jqXHR, textStatus, errorThrown){
								alert('error');
							console.log('error(s):'+textStatus, errorThrown);
                                                        $("#dv_loader").html("");
							}
							});
							 e.preventDefault();
							 $.nmTop().close();
							  $solicitudes.trigger('reloadGrid');

							//UAIP.Solicitudes.cerrar_modal();

						});

			},
			gen_publica_sol:function(){
				$(document).on('click', '#btn_generar_publica', function(e){
					var mi_id_solicitud=$('#mi_id_solicitud').get(0).value;
                                        archivos_adjuntos_vp=[];
					$.nmManual('GenPublicaSolicitud?id_solicitud=' + mi_id_solicitud,{
							callbacks: {
								afterShowCont: function(nm) {
								UAIP.Solicitudes.gen_publica_guardar();
								UAIP.Solicitudes.archrespuesta_publica(mi_id_solicitud);
								}
							}},
						 {modal: false},
						{ sizes: {
						width: '100px',
						heigth: '700px'
					    }});
                                        
                                        
				});
			},
                        
                        man_archivo_sol:function(){
                            var fn_obtener_archivos=function(id)
                            {
                            $("#lbl_loader_archivos").html("Cargando archivos, espere...");
                            $.ajax({
                                url: 'ObtenerArchivosRespondida',
                                type: 'post',
                                dataType:'json', 
                                data: {id_solicitud: id },
                            }).done(function(r) {
                                    archivos_adjuntos_vp=[];
                                    $("#tbl_lista_archivo_vp").html("");  
                                    $("#tbl_lista_archivo_vp").append("<tr><td>Archivo</td><td>Tipo</td><td>Acción</td></tr>");
                                    
                                    var nombre_carpeta="";
                                    $.each(r, function(i, e)
                                    {
                                        var arch_respuesta = {};
                                        arch_respuesta.arch_id_pertenece = e.arch_id_pertenece;
                                        arch_respuesta.arch_id_pertenece_archivo = e.arch_id_pertenece_archivo;
                                        arch_respuesta.arch_nombre =  e.arch_nombre;
                                        arch_respuesta.arch_tipo =  e.arch_tipo;
                                        arch_respuesta.arch_elimina = "";
                                     
                                        archivos_adjuntos_vp.push(arch_respuesta);
                                        
                                                 nombre_carpeta="RespuestaPublicaFolio";
                                                    if(e.arch_tipo=="Final")
                                                        nombre_carpeta="RespuestaFolio";
                                        
                                        $("#tbl_lista_archivo_vp").append("<tr><td>" + e.arch_nombre + "</td><td>" + e.arch_tipo + "</td><td><a target='_blank' href='/uploads/"+nombre_carpeta+e.arch_id_pertenece+"/"+e.arch_nombre+"' >Descargar</a> <a href='#' class='eliminar_archivo_respuesta' data-nombre-fisico='"+e.arch_nombre+"' data-id_archivo='"+e.arch_id_pertenece_archivo+"' ></a></td></tr>");
                                    });                                                                      
                                });
                                
                                $("#lbl_loader_archivos").html("");
                            }

				$(document).on('click', '#btn_manejar_archivo', function(e){
					var mi_id_solicitud=$('#mi_id_solicitud').get(0).value;
                                        archivos_adjuntos_vp=[];
					$.nmManual('ManArchivo?id_solicitud=' + mi_id_solicitud,{
							callbacks: {
								afterShowCont: function(nm) {
								UAIP.Solicitudes.man_archivo();
                                                                UAIP.Solicitudes.archrespuesta_publica(mi_id_solicitud);
                                                                UAIP.Solicitudes.cargarArchivosCp(mi_id_solicitud);
                                                                fn_obtener_archivos(mi_id_solicitud);
								}
							}},
						 {modal: false},
						{ sizes: {
						width: '100px',
						heigth: '700px'
					    }});
                                        
                                        
				});
			},
                        
			gen_publica_guardar: function(){
						$("#form_gen_publica").submit(function(e){
                                                        $("#dv_loader").html("<img src='/media/cargando.gif'> Procesando solicitud, espere...");
							$.ajax({
							url: 'GenPubGuardar',
							type: 'post',
							dataType:'html',   //expect return data as html from server
							data: $("#form_gen_publica").serialize(),
							success: function(response, textStatus, jqXHR){
							$('#nueva_realizado').html(response);   //select the id and put the response in the html
                                                        $("#dv_loader").html("");
							},
							error: function(jqXHR, textStatus, errorThrown){
								alert('error');
							console.log('error(s):'+textStatus, errorThrown);
                                                        $("#dv_loader").html("");
							}
							});
							 e.preventDefault();
							 $.nmTop().close();
							  $solicitudes.trigger('reloadGrid');

							//UAIP.Solicitudes.cerrar_modal();

						});

			},
                        
                        man_archivo: function(){
						$("#archivo_man").submit(function(e){
                                                        $("#dv_loader").html("<img src='/media/cargando.gif'> Procesando solicitud, espere...");
							$.ajax({
							url: 'GenPubGuardar',
							type: 'post',
							dataType:'html',   //expect return data as html from server
							data: $("#archivo_man").serialize(),
							success: function(response, textStatus, jqXHR){
							$('#nueva_realizado').html(response);   //select the id and put the response in the html
                                                        $("#dv_loader").html("");
							},
							error: function(jqXHR, textStatus, errorThrown){
								alert('error');
							console.log('error(s):'+textStatus, errorThrown);
                                                        $("#dv_loader").html("");
							}
							});
							 e.preventDefault();
							 $.nmTop().close();
							  $solicitudes.trigger('reloadGrid');

							//UAIP.Solicitudes.cerrar_modal();

						});

			},
                        
			condicionar_sol:function(){
				$(document).on('click', '#btn_condicionar', function(e){
					var mi_id_solicitud=$('#mi_id_solicitud').get(0).value;

					$.nmManual('CondicionarSolicitud?id_solicitud=' + mi_id_solicitud,{
							callbacks: {
								afterShowCont: function(nm) {
								UAIP.Solicitudes.condicionar_guardar();
								UAIP.Solicitudes.activar_boton();
								UAIP.Solicitudes.agregar_etiqueta();
								UAIP.Solicitudes.obtenertags();
                                                                UAIP.Solicitudes.cargarArchivosCp(mi_id_solicitud);

								}
							}},
						 {modal: false},
						{ sizes: {
						width: '100px',
						heigth: '700px'
					    }});
				});
			},
                        cargarArchivosCp: function(mi_id_solicitud){
				$(document).ready(function(){
                                    var obs='';
                                    $('.fileupload_cp').fileupload({
                                        formData: { id_solicitud: mi_id_solicitud, id_tipo_solicitud: 1, obs: obs },
                                        dataType: 'json',
                                        progressall: function (e, data) {
                                            $("#lbl_loader_cp").html("<img src='/media/cargando.gif'> Cargando archivo, espere...");
                                        },
                                        done: function (e, response)
                                        {
                                            var r = response.result;

                                            if(parseInt(r.tipo_error)==0)
                                            {
                                                var arch_respuesta = {};
                                                arch_respuesta.arch_id_pertenece = mi_id_solicitud;
                                                arch_respuesta.arch_id_pertenece_archivo = r.id_archivo;
                                                arch_respuesta.arch_nombre =  r.nombre_archivo;
                                                arch_respuesta.arch_tipo =  "Final";
                                                arch_respuesta.arch_elimina= "Eliminar";
                                                archivos_adjuntos_cp.push(arch_respuesta);
                                                archivos_adjuntos_vp.push(arch_respuesta);

                                                $("#tbl_lista_archivo_vp").html("");  
                                                $("#tbl_lista_archivo_vp").append("<tr><td>Archivo</td><td>Tipo</td><td>Acción</td></tr>");

                                                var nombre_carpeta="";                                                
                                                $.each(archivos_adjuntos_vp, function(i, elem)
                                                {
                                                    nombre_carpeta="RespuestaPublicaFolio";
                                                    if(elem.arch_tipo=="Final")
                                                        nombre_carpeta="RespuestaFolio";
                                                    
                                                    $("#tbl_lista_archivo_vp").append("<tr><td>" + elem.arch_nombre + "</td><td>" + elem.arch_tipo + "</td><td><a target='_blank' href='/uploads/"+nombre_carpeta+elem.arch_id_pertenece+"/"+elem.arch_nombre+"' >Descargar</a> <a href='#' class='eliminar_archivo_cp' data-nombre-fisico='"+elem.arch_nombre+"' data-id_archivo='"+elem.arch_id_pertenece_archivo+"' >" + elem.arch_elimina + "</a></td></tr>");
                                                });
                                            }
                                            else
                                                alert(r.mensaje);

                                            $("#lbl_loader_cp").html("");  
                                        }
                                    });

                                    $(document).on('click',"a.eliminar_archivo_cp",function() {
                                        var nombre_archivo_fisico=$(this).data("nombre-fisico");
                                        var id_archivo=$(this).data("id_archivo");

                                        $("#lbl_loader_cp").html("<img src='/media/cargando.gif'> Eliminando archivo, espere...");
                                        $("#lbl_loader_cp").load("eliminar_archivo_respuesta",{id_solicitud:mi_id_solicitud, nombre_archivo_fisico: nombre_archivo_fisico, id_archivo: id_archivo}, function()
                                        {
                                            $("#tbl_lista_archivo_vp").html("");  
                                            $("#tbl_lista_archivo_vp").append("<tr><td>Archivo</td><td>Tipo</td><td>Acción</td></tr>");
                                            var archivos_adjuntos_raux=[];
                                            $.each(archivos_adjuntos_cp, function(i, elem)
                                            {
                                                if($.trim(elem.arch_nombre)!=$.trim(nombre_archivo_fisico))
                                                {
                                                    var arch_respuesta = {};
                                                    arch_respuesta.arch_id_pertenece = mi_id_solicitud;
                                                    arch_respuesta.arch_id_pertenece_archivo = elem.arch_id_pertenece_archivo;
                                                    arch_respuesta.arch_nombre =  elem.arch_nombre;
                                                    arch_respuesta.arch_tipo =  "Final"; 
                                                    arch_respuesta.arch_elimina= "Eliminar";
                                                    archivos_adjuntos_raux.push(arch_respuesta);

                                                    $("#tbl_lista_archivo_vp").append("<tr><td>" + elem.arch_nombre + "</td><td>" + elem.arch_tipo + "</td><td><a target='_blank' href='/uploads/RespuestaFolio"+elem.arch_id_pertenece+"/"+elem.arch_nombre+"' >Descargar</a> <a href='#' class='eliminar_archivo_cp' data-nombre-fisico='"+elem.arch_nombre+"' data-id_archivo='"+elem.arch_id_pertenece_archivo+"' >" + elem.arch_elimina + "</a></td></tr>");
                                                }
                                            });
                                            archivos_adjuntos_cp=[];
                                            archivos_adjuntos_cp=archivos_adjuntos_raux;
                                            
                                            
                                            if(archivos_adjuntos_vp.length>0)
                                            {                                            
                                            archivos_adjuntos_raux=[];
                                            $("#tbl_lista_archivo_vp").html("");  
                                            $("#tbl_lista_archivo_vp").append("<tr><td>Archivo</td><td>Tipo</td><td>Acción</td></tr>");
                                            $.each(archivos_adjuntos_vp, function(i, elem)
                                            {
                                                var nombre_carpeta="";
                                                if($.trim(elem.arch_nombre)!=$.trim(nombre_archivo_fisico))
                                                {
                                                    var arch_respuesta = {};
                                                    arch_respuesta.arch_id_pertenece = mi_id_solicitud;
                                                    arch_respuesta.arch_id_pertenece_archivo = elem.arch_id_pertenece_archivo;
                                                    arch_respuesta.arch_nombre =  elem.arch_nombre;
                                                    arch_respuesta.arch_tipo =  elem.arch_tipo;
                                                    arch_respuesta.arch_elimina= elem.arch_elimina;
                                                    archivos_adjuntos_raux.push(arch_respuesta);

                                                    nombre_carpeta="RespuestaPublicaFolio";
                                                    if(elem.arch_tipo=="Final")
                                                        nombre_carpeta="RespuestaFolio";
                                                    
                                                    $("#tbl_lista_archivo_vp").append("<tr><td>" + elem.arch_nombre + "</td><td>" + elem.arch_tipo + "</td><td><a target='_blank' href='/uploads/"+nombre_carpeta+elem.arch_id_pertenece+"/"+elem.arch_nombre+"' >Descargar</a> <a href='#' class='eliminar_archivo_vp' data-nombre-fisico='"+elem.arch_nombre+"' data-id_archivo='"+elem.arch_id_pertenece_archivo+"' >" + elem.arch_elimina + "</a></td></tr>");
                                                }
                                            });
                                            
                                            archivos_adjuntos_vp=[];
                                            archivos_adjuntos_vp=archivos_adjuntos_raux;
                                            }
                                      
                                        });
                                    });
				});
			},
			condicionar_guardar: function(){
						$("#form_condicionar").submit(function(e){
                                                    	var hddTags = $("[name='hidden-tags']").get(0).value;                                                        
                                                        var dtag=hddTags.split(",");                                                       

							var arr = [];                                                   
                                                        $.each(dtag, function(idx, e){
                                                                arr.push(e);
                                                        });
                                
							 var etiquegas = JSON.stringify(arr);
                                                        
                                                        $("#dv_loader").html("<img src='/media/cargando.gif'> Procesando solicitud, espere...");
							$.ajax({
							url: 'CondicionarGuardar',
							type: 'post',
							dataType:'html',   //expect return data as html from server
							data: $("#form_condicionar").serialize() + "&etiquetas=" + etiquegas,
							success: function(response, textStatus, jqXHR){
							$('#nueva_realizado').html(response);   //select the id and put the response in the html
                                                        $("#dv_loader").html("");
							},
							error: function(jqXHR, textStatus, errorThrown){
								alert('error');
							console.log('error(s):'+textStatus, errorThrown);
                                                        $("#dv_loader").html("");
							}
							});
							 e.preventDefault();
							 $.nmTop().close();
							  $solicitudes.trigger('reloadGrid');
							//UAIP.Solicitudes.cerrar_modal();
						});

			},
			cerrar: function(){
				$(document).on('click', '#salir', function(){
					location.href="/index.php/inicio/cerrar";

				});
			},
			cargar_archivos_enlace: function(id_solicitud,id_unidad){
				$(document).ready(function(){
						$('#file_upload').uploadify({
						'buttonText' : 'Selecciona Archivo',
						'fileSizeLimit' : '10000KB',
						'fileTypeExts' : '*.pdf; *.doc; *.docx; *.xls; *.7z; *.xlsx',
						'formData'      : {'id_solicitud' : id_solicitud, 'id_tipo_solicitud' : 3,'id_unidad': id_unidad},
						'auto'     : false,
						'method'   : 'post',
						'swf'      : '/uploadify/uploadify.swf',
						'uploader' : 'upload',

						'onUploadSuccess' : function(file, data, response) {
						alert(data);
						},
						'onUploadError' : function(file, errorCode, errorMsg, errorString) {
						alert('El archivo ' + file.name + ' no puede subirse: ' + errorString);
						},
						'onUploadStart' : function(file) {
						$("#file_upload").uploadify("settings", "id_solicitud");
						}
						});

				});
			},
			cargar_archivos_respuesta: function(id_solicitud,id_unidad){
				$(document).ready(function(){
                                    
                                $('.fileupload_ar').fileupload({
                                formData: { id_solicitud: id_solicitud, id_tipo_solicitud: 1 },
                                dataType: 'json',
                                progressall: function (e, data) {
                                    $("#lbl_loader_ar").html("<img src='/media/cargando.gif'> Cargando archivo, espere...");
                                },
                                done: function (e, response)
                                {
                                    var r = response.result;
                                    
                                    if(parseInt(r.tipo_error)==0)
                                    {
                                        var arch_respuesta = {};
                                        arch_respuesta.arch_id_pertenece = id_solicitud;
                                        arch_respuesta.arch_id_pertenece_archivo = r.id_archivo;
                                        arch_respuesta.arch_nombre =  r.nombre_archivo;
                                        archivos_adjuntos_r.push(arch_respuesta);

                                        $("#tbl_lista_archivo_respuesta").html("");  
                                        $("#tbl_lista_archivo_respuesta").append("<tr><td>Archivo</td><td>Acción</td></tr>");
                                        
                                        $.each(archivos_adjuntos_r, function(i, elem)
                                        {
                                            $("#tbl_lista_archivo_respuesta").append("<tr><td>" + elem.arch_nombre + "</td><td><a target='_blank' href='/uploads/RespuestaFolio"+elem.arch_id_pertenece+"/"+elem.arch_nombre+"' >Descargar</a></td><td><a href='#' class='eliminar_archivo_respuesta' data-nombre-fisico='"+elem.arch_nombre+"' data-id_archivo='"+elem.arch_id_pertenece_archivo+"' >Eliminar</a></td></tr>");
                                        });
                                    }
                                    else
                                        alert(r.mensaje);

                                    $("#lbl_loader_ar").html("");  

                                }
                                });
                                
                                $(document).on('click',"a.eliminar_archivo_respuesta",function() {
                                    var nombre_archivo_fisico=$(this).data("nombre-fisico");
                                    var id_archivo=$(this).data("id_archivo");
                                    
                                    $("#lbl_loader_ar").html("<img src='/media/cargando.gif'> Eliminando archivo, espere...");
                                    $("#lbl_loader_ar").load("eliminar_archivo_respuesta",{id_solicitud:id_solicitud, nombre_archivo_fisico: nombre_archivo_fisico, id_archivo: id_archivo}, function()
                                    {
                                        $("#tbl_lista_archivo_respuesta").html("");  
                                        $("#tbl_lista_archivo_respuesta").append("<tr><td>Archivo</td><td>Acción</td></tr>");
                                        var archivos_adjuntos_raux=[];
                                        $.each(archivos_adjuntos_r, function(i, elem)
                                        {
                                            if($.trim(elem.arch_nombre)!=$.trim(nombre_archivo_fisico))
                                            {
                                                var arch_respuesta = {};
                                                arch_respuesta.arch_id_pertenece = id_solicitud;
                                                arch_respuesta.arch_id_pertenece_archivo = elem.arch_id_pertenece_archivo;
                                                arch_respuesta.arch_nombre =  elem.arch_nombre;
                                                archivos_adjuntos_raux.push(arch_respuesta);
                                        
                                                $("#tbl_lista_archivo_respuesta").append("<tr><td>" + elem.arch_nombre + "</td><td><a target='_blank' href='/uploads/RespuestaFolio"+elem.arch_id_pertenece+"/"+elem.arch_nombre+"' >Descargar</a></td><td><a href='#' class='eliminar_archivo_respuesta' data-nombre-fisico='"+elem.arch_nombre+"' data-id_archivo='"+elem.arch_id_pertenece_archivo+"' >Eliminar</a></td></tr>");
                                            }
                                        });
                                        
                                        archivos_adjuntos_r=[];
                                        archivos_adjuntos_r=archivos_adjuntos_raux;
                                    });
                                });

				});
			},
                        
			archrespuesta_publica: function(id_solicitud,id_unidad){
				$(document).ready(function(){
                                    var obs='';
                                $('.fileupload_vp').fileupload({
                                    formData: { id_solicitud: id_solicitud, id_tipo_solicitud: 2, obs: obs },
                                    dataType: 'json',
                                    progressall: function (e, data) {
                                        $("#lbl_loader_vp").html("<img src='/media/cargando.gif'> Cargando archivo, espere...");
                                    },
                                    done: function (e, response)
                                    {
                                        
                                        var r = response.result;

                                        if(parseInt(r.tipo_error)==0)
                                        {
                                            var arch_respuesta = {};
                                            arch_respuesta.arch_id_pertenece = id_solicitud;
                                            arch_respuesta.arch_id_pertenece_archivo = r.id_archivo;
                                            arch_respuesta.arch_nombre =  r.nombre_archivo;
                                            arch_respuesta.arch_tipo="Público";
                                            arch_respuesta.arch_elimina= "Eliminar";
                                            archivos_adjuntos_vp.push(arch_respuesta);
                                          

                                            $("#tbl_lista_archivo_vp").html("");  
                                            $("#tbl_lista_archivo_vp").append("<tr><td>Archivo</td><td>Tipo</td><td>Acción</td></tr>");

                                            var nombre_carpeta="";
                                            $.each(archivos_adjuntos_vp, function(i, elem)
                                            {
                                                             nombre_carpeta="RespuestaPublicaFolio";
                                                    if(elem.arch_tipo=="Final")
                                                        nombre_carpeta="RespuestaFolio";
                                                    
                                                $("#tbl_lista_archivo_vp").append("<tr><td>" + elem.arch_nombre + "</td><td>" + elem.arch_tipo + "</td><td><a target='_blank' href='/uploads/"+nombre_carpeta+elem.arch_id_pertenece+"/"+elem.arch_nombre+"' >Descargar</a></td><td><a href='#' class='eliminar_archivo_vp' data-nombre-fisico='"+elem.arch_nombre+"' data-id_archivo='"+elem.arch_id_pertenece_archivo+"' >" + elem.arch_elimina + "</a></td></tr>");
                                            });
                                        }
                                        else
                                            alert(r.mensaje);

                                        $("#lbl_loader_vp").html("");  
                                    }
                                });
                                
                                $(document).on('click',"a.eliminar_archivo_vp",function() {
                                    var nombre_archivo_fisico=$(this).data("nombre-fisico");
                                    var id_archivo=$(this).data("id_archivo");
                                    
                                    //var text = prompt("prompt", "textbox's intial text");
                                    //console.log(text);
                                    
                                    
                                    $("#lbl_loader_vp").html("<img src='/media/cargando.gif'> Eliminando archivo, espere...");
                                    $("#lbl_loader_vp").load("eliminar_archivo_vp",{id_solicitud:id_solicitud, nombre_archivo_fisico: nombre_archivo_fisico, id_archivo: id_archivo}, function()
                                    {
                                        $("#tbl_lista_archivo_vp").html("");  
                                        $("#tbl_lista_archivo_vp").append("<tr><td>Archivo</td><td>Tipo</td><td>Acción</td></tr>");
                                        var archivos_adjuntos_raux=[];
                                        var nombre_carpeta="";
                                        $.each(archivos_adjuntos_vp, function(i, elem)
                                        {
                                            if($.trim(elem.arch_nombre)!=$.trim(nombre_archivo_fisico))
                                            {
                                                var arch_respuesta = {};
                                                arch_respuesta.arch_id_pertenece = id_solicitud;
                                                arch_respuesta.arch_id_pertenece_archivo = elem.arch_id_pertenece_archivo;
                                                arch_respuesta.arch_nombre =  elem.arch_nombre;
                                                arch_respuesta.arch_tipo=elem.arch_tipo;
                                                arch_respuesta.arch_elimina= elem.arch_elimina;
                                                archivos_adjuntos_raux.push(arch_respuesta);
                                        
                                        
                                                    nombre_carpeta="RespuestaPublicaFolio";
                                                    if(elem.arch_tipo=="Final")
                                                        nombre_carpeta="RespuestaFolio";
                                                    
                                                $("#tbl_lista_archivo_vp").append("<tr><td>" + elem.arch_nombre + "</td><td>" + elem.arch_tipo + "</td><td><a target='_blank' href='/uploads/"+nombre_carpeta+elem.arch_id_pertenece+"/"+elem.arch_nombre+"' >Descargar</a></td><td><a href='#' class='eliminar_archivo_vp' data-nombre-fisico='"+elem.arch_nombre+"' data-id_archivo='"+elem.arch_id_pertenece_archivo+"' >" + elem.arch_elimina + "</a></td></tr>");
                                            }
                                        });
                                        
                                        archivos_adjuntos_vp=[];
                                        archivos_adjuntos_vp=archivos_adjuntos_raux;
                                    });
                                });
                                

//						$('#file_upload_vp').uploadify({
//						'buttonText' : 'Selecciona Archivo',
//						'fileSizeLimit' : '10000KB',
//						'fileTypeExts' : '*.pdf; *.doc; *.docx; *.xls; *.7z; *.xlsx',
//						'formData'      : {'id_solicitud' : id_solicitud, 'id_tipo_solicitud' : 2},
//						'auto'     : false,
//						'method'   : 'post',
//						'swf'      : '/uploadify/uploadify.swf',
//						'uploader' : 'upload_respuesta_publica',
//
//						'onUploadSuccess' : function(file, data, response) {
//						alert(data);
//						},
//						'onUploadError' : function(file, errorCode, errorMsg, errorString) {
//						alert('El archivo ' + file.name + ' no puede subirse: ' + errorString);
//						},
//						'onUploadStart' : function(file) {
//						$("#file_upload_vp").uploadify("settings", "id_solicitud");
//						}
//						});

				});
			},

			canalizado: function(){
				$( "#dialog" ).dialog({ autoOpen: true,width: 600,
				close: function(event, ui)
                                {
                                    $solicitudes.trigger('reloadGrid');
                                },
				  buttons: {
						'Aceptar': function() {
							$(this).dialog('close');
						}
				  } });
			},
			avisos_modal: function(){
			$(document).ready(function(){
				$('#avisoModal').modal({

					}).css({
					width: '850px',
					'margin-left': function () {
					return -($(this).width() / 2);
					}
				});
				$("#avisoModal").modal("show");
					$('#avisoModal').on('shown', function() {
					//UAIP.Solicitudes.cargar_tabs();
					});
			});
				//$("#myModal").modal("hide");
			},modal_unidades: function(id_unidad){
				//$(document).on('click', '#edit_unidades', function(){	
					$('#aparece_unidad').load('DetalleUnidad', {id_unidad: id_unidad});			
					$("#myModalUnidad").modal("show");
						$('#myModalUnidad').on('shown', function() {
						//UAIP.Solicitudes.cargar_tabs();
						});
				//});
					//$("#myModal").modal("hide");
			},
			modal_Enlaces: function(id_unidad,tipo){
				//$(document).on('click', '#edit_unidades', function(){	
					$('#aparece_enlaces').load('DetalleUnidadEnlaces', {id_unidad: id_unidad,tipo:tipo});			
					$("#myModalEnlaces").modal("show");
						$('#myModalEnlaces').on('shown', function() {
						//UAIP.Solicitudes.cargar_tabs();
						});
				//});
					//$("#myModal").modal("hide");
			},
                        
                        
			recarga_unidades: function(id_unidad,tipo){				
					$('#carga_unidades').load('RecargaUnidades'); 
			},
                        
                        agregar_nueva_unidades: function(){
				$("#form_nueva_unidad").submit(function(e){             
              		var $form = $('#form_nueva_unidad');  
                        $("#dv_loader").html("<img src='/media/cargando.gif'> Procesando solicitud, espere...");
                        $.ajax({
                            url: 'NuevaUnidadEnlace',
                            type: 'post',
                            dataType:'html',   //expect return data as html from server
                            data: $("#form_nueva_unidad").serialize(),
                            success: function(response, textStatus, jqXHR){
                            $('#mensajes').html(response);   //select the id and put the response in the html
                            $("#dv_loader").html("");
                            },
                            error: function(jqXHR, textStatus, errorThrown){
                              alert(errorThrown);
                            console.log('error(s):'+textStatus, errorThrown);
                            $("#dv_loader").html("");
                        }
                        });
                     e.preventDefault();                     
                     $('#myModalAUnidad').modal('hide');
                     $('#carga_unidades').html('<div><center><img src=\"/media/loader-big.gif\"/><center></div>');                                                        
                  
            });
			},
//                        completar_responsable:function(){
//                        $(document).ready(function(){	
////                        $("#txt_responsable").autocomplete({source:'Autocompleta', minLength:5});
////                      });
////                  },
//                        $('#txt_responsable .typeahead').typeahead({
////                           minLength:5,
//                           source: function(query, process) {
//                               $.ajax({
//                                 url: 'Autocompleta', 
//                                 data: {term: $('#txt_responsable').get(0).value },
//                                 dataType: 'JSON',
//                                 }).done(function(response){
//                                       process(response);
//                                      $("ul.typeahead").find("li").removeClass("active");
//                                 
//                               });
//                           }
//                        });
//                        });
//                        },
                        
		submit_unidades: function(){
				$("#form_edita_unidad").submit(function(e){             
              		var $form = $('#form_edita_unidad');  
                        $("#dv_loader").html("<img src='/media/cargando.gif'> Procesando solicitud, espere...");
                        $.ajax({
                            url: 'CambiarUnidadEnlace',
                            type: 'post',
                            dataType:'html',   //expect return data as html from server
                            data: $("#form_edita_unidad").serialize(),
                            success: function(response, textStatus, jqXHR){
                            $('#mensajes').html(response);   //select the id and put the response in the html
                            $("#dv_loader").html("");
                            },
                            error: function(jqXHR, textStatus, errorThrown){
                              alert(errorThrown);
                            console.log('error(s):'+textStatus, errorThrown);
                            $("#dv_loader").html("");
                        }
                        });
                     e.preventDefault();                     
                     $('#myModalUnidad').modal('hide');
                     $('#carga_unidades').html('<div><center><img src=\"/media/loader-big.gif\"/><center></div>');                                                        
                  
            });
			},
			submit_enlaces: function(){
				$("#form_edita_enlaces").submit(function(e){             
              		var $form = $('#form_edita_enlaces');  
                        $("#dv_loader").html("<img src='/media/cargando.gif'> Procesando solicitud, espere...");
                        $.ajax({
                            url: 'CambiarEnlaceDatos',
                            type: 'post',
                            dataType:'html',   //expect return data as html from server
                            data: $("#form_edita_enlaces").serialize(),
                            success: function(response, textStatus, jqXHR){
                            $('#mensajes').html(response);   //select the id and put the response in the html
                            $("#dv_loader").html("");
                            },
                            error: function(jqXHR, textStatus, errorThrown){
                              alert(errorThrown);
                            console.log('error(s):'+textStatus, errorThrown);
                            $("#dv_loader").html("");
                        }
                        });
                     e.preventDefault();                                   
                     $('#myModalEnlaces').modal('hide');
                     //$('#mensajes').html('<div><center><img src=\"/daa_credencialextension/media/loading.gif\"/><center></div>'); 
                  
            });
			},
                        
                        BuscarUnidad: function(){
                        $(document).on('click', '#bttn_buscar_un', function(e){
                            var q = $("#txt_consulta_un").get(0).value;
                                      
                                        if($.trim(q)=='')
                                        {
                                            alert("Debe ingresar el valor que desea buscar.");
                                        }
                                        else
                                        {   
                                            $("#lbl_loader_unidades").html("<img src='/media/cargando.gif'> Cargando datos...");
                                              $('#carga_unidades').load('RecargaUnidades',{id_solicitud: q},function()
                                              {
                                                 $("#lbl_loader_unidades").html("");
                                              });
                                              
                                              
                                              

                                
                            }
                        });
                        },
                        
                        AgregarNuevaUnidad: function(){
				$(document).on('click', '#nueva_unidad', function(){		                 			
					$('#nueva_aparece_unidad').load('NuevaUnidad');
                                        $("#myModalAUnidad").modal("show");
					
				});					
			},
                        
                        
                        VerTodoUnidad: function(){
				$(document).on('click', '#ver_todos_un', function(){	

                                    $("#txt_consulta_un").prop("value","");   
                                    $("#lbl_loader_unidades").html("<img src='/media/cargando.gif'> Cargando datos...");
                                    $('#carga_unidades').load('RecargaUnidades', function()
                                    {
                                        $("#lbl_loader_unidades").html("");
                                    });
				});					
			},
                        
			GuardarMensaje: function(){
				$(document).on('click', '#btn_guarda_mensaje', function(){		
					var mensaje_vacaciones=$('#mensaje_vacaciones').get(0).value;				
					if(mensaje_vacaciones){
						$('#agrega_mensaje_mensaje').load('GuardaMensajeVacas', {mensaje_vacaciones: mensaje_vacaciones});					
				    }else{
				    	alert('Favor de ingresar el mensaje');
				    }
				});					
			},
			EliminaMensaje: function(){
				$(document).on('click', '#btn_elimina_mensaje', function(){		
						$('#agrega_mensaje_mensaje').load('EliminaMensajeVacas');
				});					
			},
			ImpRepCalidad: function(){
				$(document).on('click', '#bt_reporte_calidad', function(){		
						//alert('Mensaje');
						//$('#aparece_reporte').load('ReporteCalidad');
						var solicitud_inicia=$('#solicitud_inicia').get(0).value;
						var solicitud_finaliza=$('#solicitud_finaliza').get(0).value;
						if(solicitud_inicia && solicitud_finaliza){
							var pag = "http://www.transparencia.ugto.mx/index.php/solicitudes/ReporteCalidad?inicia="+solicitud_inicia+'&finaliza='+solicitud_finaliza;
							$(location).attr('href',pag); 
						}else{
							alert('Favor de ingresar un rango de solicitudes')

						}	
				});					
			},
                        
                        gen_recurso:function(){
				$(document).on('click', '#btn_recurso_inconforme', function(e){
					var mi_id_solicitud=$('#mi_id_solicitud').get(0).value;

					$.nmManual('GenRecurso?id_solicitud=' + mi_id_solicitud,{
							callbacks: {
								afterShowCont: function(nm) {
								UAIP.Solicitudes.gen_recurso_guardar();
								UAIP.Solicitudes.cargar_archivos_respuesta(mi_id_solicitud);
								//UAIP.Solicitudes.archrespuesta_publica(mi_id_solicitud);
								}
							}},
						 {modal: false},
						{ sizes: {
						width: '100px',
						heigth: '700px'
					    }});
				});
			},
                       
                       
			gen_recurso_guardar: function(){
						$("#form_gen_recurso").submit(function(e){
                                                        $("#dv_loader").html("<img src='/media/cargando.gif'> Procesando solicitud, espere...");
							$.ajax({
							url: 'GenRecursoGuardar',
							type: 'post',
							dataType:'html',   //expect return data as html from server
							data: $("#form_gen_recurso").serialize(),
							success: function(response, textStatus, jqXHR){
							$('#nueva_realizado').html(response);   //select the id and put the response in the html
                                                        $("#dv_loader").html("");
							},
							error: function(jqXHR, textStatus, errorThrown){
								alert('error');
							console.log('error(s):'+textStatus, errorThrown);
                                                        $("#dv_loader").html("");
							}
							});
							 e.preventDefault();
							 $.nmTop().close();
							  $solicitudes.trigger('reloadGrid');

							//UAIP.Solicitudes.cerrar_modal();

						});

			},
                        
		GuardarMensajeCanalizar: function(){
				$(document).on('click', '#canalizar_actualizar', function(){		
					var txt_canalizar=$('#txt_canalizar').get(0).value;				
					if(txt_canalizar){
						$('#aparece_algo').load('ActualizaMensaje', {mensaje: txt_canalizar,id:1});					
				    }else{
				    	alert('Favor de ingresar el mensaje');
				    }
				});					
			},
			GuardarMensajeCancelar: function(){
				$(document).on('click', '#cancelar_actualizar', function(){		
					var txt_canalizar=$('#txt_cancelar').get(0).value;				
					if(txt_canalizar){
						$('#aparece_algo').load('ActualizaMensaje', {mensaje: txt_canalizar,id:2});					
				    }else{
				    	alert('Favor de ingresar el mensaje');
				    }
				});					
			},
			GuardarMensajeIncompleta: function(){
				$(document).on('click', '#incompleta_actualizar', function(){		
					var txt_canalizar=$('#txt_incompleta').get(0).value;				
					if(txt_canalizar){
						$('#aparece_algo').load('ActualizaMensaje', {mensaje: txt_canalizar,id:3});					
				    }else{
				    	alert('Favor de ingresar el mensaje');
				    }
				});					
			},
			GuardarMensajeProrroga: function(){
				$(document).on('click', '#prorroga_actualizar', function(){		
					var txt_canalizar=$('#txt_prorroga').get(0).value;				
					if(txt_canalizar){
						$('#aparece_algo').load('ActualizaMensaje', {mensaje: txt_canalizar,id:4});					
				    }else{
				    	alert('Favor de ingresar el mensaje');
				    }
				});					
			},
			ImpRepIACIP: function(){
				$(document).on('click', '#bt_reporte_iacip', function(){		
						//alert('Mensaje');
						//$('#aparece_reporte').load('ReporteCalidad');
						var solicitud_inicia=$('#solicitud_inicia').get(0).value;
						var solicitud_finaliza=$('#solicitud_finaliza').get(0).value;
						if(solicitud_inicia && solicitud_finaliza){
							var pag = "http://www.transparencia.ugto.mx/index.php/solicitudes/ReporteIACIP?inicia="+solicitud_inicia+'&finaliza='+solicitud_finaliza;
							$(location).attr('href',pag); 
						}else{
							alert('Favor de ingresar un rango de solicitudes')

						}	
				});					
			},
			GuardarMensajeCondicionar: function(){
				$(document).on('click', '#condicionar_actualizar', function(){		
					var txt_canalizar=$('#txt_condicionar').get(0).value;				
					if(txt_canalizar){
						$('#aparece_algo').load('ActualizaMensaje', {mensaje: txt_canalizar,id:5});					
				    }else{
				    	alert('Favor de ingresar el mensaje');
				    }
				});					
			},
			DialogAjax: function(){
				$( "#lDialogo" ).dialog({				   
				   draggable: false,
				   modal: true,
				   dialogClass: "alert",
				   resizable: false,
				   closeOnEscape: false,
				   dialogClass: "no-close"
				});
			},
                        CargaInicioUnidades: function(){	
                            $("#lbl_loader_unidades").html("<img src='/media/cargando.gif'> Cargando datos...");
                            $('#carga_unidades').load('RecargaUnidades', function()
                            {
                                $("#lbl_loader_unidades").html("");
                            });								
			}
                        

		};

	})(); //X que es una función anónima
	UAIP.Solicitudes.orientar_sol();
	UAIP.Solicitudes.canalizar();
//        UAIP.Solicitudes.reenviar();
	UAIP.Solicitudes.canalizado();
	UAIP.Solicitudes.listado();
	UAIP.Solicitudes.cerrar();
	UAIP.Solicitudes.nueva_solicitud_info();
	UAIP.Solicitudes.solicitud_historial();
	UAIP.Solicitudes.cerrar_modal();
	UAIP.Solicitudes.cancelar_sol();
	UAIP.Solicitudes.incompleta_sol();
	UAIP.Solicitudes.completar_sol();
	UAIP.Solicitudes.prorroga_sol();
	UAIP.Solicitudes.responder_sol();
	UAIP.Solicitudes.nuevas();
	UAIP.Solicitudes.incompletas();
	UAIP.Solicitudes.completadas();
	UAIP.Solicitudes.orientadas();
	UAIP.Solicitudes.proceso();
	UAIP.Solicitudes.prorroga();
	UAIP.Solicitudes.condicionada();
	UAIP.Solicitudes.cancelada();
	UAIP.Solicitudes.liberada();
	UAIP.Solicitudes.no_entregada();
	UAIP.Solicitudes.rechazada_ue();
	UAIP.Solicitudes.revision();
        UAIP.Solicitudes.inprocedentes();
	UAIP.Solicitudes.liberar_sol();
        UAIP.Solicitudes.man_archivo_sol();
	UAIP.Solicitudes.gen_publica_sol();
	UAIP.Solicitudes.condicionar_sol();
	UAIP.Solicitudes.mar_entregada();
	UAIP.Solicitudes.mar_cn_entregada();
	UAIP.Solicitudes.traer_datos_usr();
	UAIP.Solicitudes.avisos_modal();
        UAIP.Solicitudes.agregar_nueva_unidades();
	UAIP.Solicitudes.submit_unidades();
	UAIP.Solicitudes.submit_enlaces();
	UAIP.Solicitudes.GuardarMensaje();
	UAIP.Solicitudes.EliminaMensaje();
	UAIP.Solicitudes.ImpRepCalidad();
	UAIP.Solicitudes.gen_recurso();
        UAIP.Solicitudes.man_archivo();
	UAIP.Solicitudes.GuardarMensajeCanalizar();
	UAIP.Solicitudes.GuardarMensajeCancelar();
	UAIP.Solicitudes.GuardarMensajeIncompleta();
	UAIP.Solicitudes.GuardarMensajeProrroga();
	UAIP.Solicitudes.GuardarMensajeCondicionar();
	UAIP.Solicitudes.ImpRepIACIP();
        UAIP.Solicitudes.VerTodoUnidad();
        UAIP.Solicitudes.BuscarUnidad();
        UAIP.Solicitudes.AgregarNuevaUnidad();
        UAIP.Solicitudes.CargaInicioUnidades();
        //UAIP.Solicitudes.toda();
	//UAIP.Solicitudes.DialogAjax();	
	// AJAX activity indicator bound to ajax start/stop document events
//	$(document).ajaxStart(function () {        
//	if ($("#lDialogo").dialog ("isOpen")) console.log("Loading Ajax");
//	else $("#lDialogo").dialog ("open");            
//	}).ajaxStop(function () {            
//	$("#lDialogo").dialog ("close");       
$("#lDialogo").hide();         
//	}); 

});