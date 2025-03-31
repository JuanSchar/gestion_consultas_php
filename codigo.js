$('#formLogin').submit(function(e){
    e.preventDefault();
    var usuario = $.trim($("#usuario").val());    
    var password =$.trim($("#password").val());    
    if(usuario.length == "" || password == ""){
       Swal.fire({
           type:'warning',
           title:'Debe ingresar un usuario y/o password',
       });
       return false; 
     } else{
         $.ajax({
            url:"bd/login.php",
            type:"POST",
            datatype: "json",
            data: {usuario:usuario, password:password}, 
            success:function(data){
                if(data.includes('false')){
                    Swal.fire({
                        type:'error',
                        title:'Usuario y/o password incorrecta',
                    });
                } else{
                    window.location.href = "vistas/dashboard.php";
                }
            }    
         });
     }     
 });

 $('#accionBoton').submit(function(e){
    e.preventDefault();
    var fila = document.activeElement.dataset.fila;    
    var accion = document.activeElement.dataset.accion; 
    $.ajax({
        url:"../controller/accion_consulta_pendiente.php",
        type:"POST",
        datatype: "text",
        data: {fila:fila, accion:accion}, 
        success:function(data){
            if(data == 0){
                Swal.fire({
                    type:'error',
                    title:'Error en la base de datos.',
                });
            } else{
                $.ajax({
                    type: "POST",
                    url: "../controller/enviar_mail.php",
                    data: {fila:fila, accion:accion}, 
                    async: true,
                    success: function (data) {
                         
                    }
                }); 
                window.location.href = "listado_consultas.php";
            }
        }    
    });
 });

 $('#accionBotonAdmin').submit(function(e){
    e.preventDefault();
    var fila = document.activeElement.dataset.fila;    
    var accion = document.activeElement.dataset.accion; 
    $.ajax({
        url:"../controller/accion_consulta_pendiente_admin.php",
        type:"POST",
        datatype: "text",
        data: {fila:fila, accion:accion}, 
        success:function(data){
            if(data == 0){
                Swal.fire({
                    type:'error',
                    title:'Error en la base de datos.',
                });
            } else{
                window.location.href = "listado_consultas_admin.php";
            }
        }    
    });
 });