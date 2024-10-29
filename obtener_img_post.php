<?php
require_once( '../../../wp-load.php' );

$id_del_post = $_POST['postID'];
if ($id_del_post == ""){
    echo "<div class='respuesta'>POST ID EMPTY</div>";
}else{
    $args = array( 'post_type' => 'attachment', 'numberposts' => -1, 'post_status' => null, 'post_mime_type' => 'image', 'post_parent' => $id_del_post );
    $attachments = get_posts( $args );

    $i=1;

    if (count($attachments)==0){
        echo "<div class='respuesta'>Images not found in the post</div>";
    }else{
        echo "<select id='modificar_alt' name='modificar_alt'>";
        echo "<option  value='0' selected> NO modify alts in articles";
        echo "<option  value='1'>YES modify alts in articles";
        echo "</select>";
        echo "<table>";
        echo "<input name='num_attachments_post' id='num_attachments_post' type='hidden' value='".count($attachments)."'>";
        foreach ($attachments as $imagen_candidata){
            echo "<tr>";
            echo "<td><b>".$i."-</b></td>";
            echo "<td class='trigger_vista_previa'>"."<div class='imagen_vista_previa'><img src='".wp_get_attachment_url( $imagen_candidata->ID )."'></div>"."<span class='ver_span'>PREVIEW</span><a target='_blank' href='".get_home_url()."/wp-admin/upload.php?item=".$imagen_candidata->ID."' >".$imagen_candidata->ID."</a></td>";
            echo "<input type='hidden' class='avy_num_".$i."' value='".$imagen_candidata->ID."'>";
            echo "<td>"."<input class='entrada_input_title_edit avy_tit_".$i."' type='text' value='".$imagen_candidata->post_title."'>"."</td>";
            echo "</tr>";
            $i++;
        }
        echo "</table>";
        echo "<div onclick='guardar_img_ajax();' class='boton_post_candidato boton_verde_ajax'>Save</div>";
    }

}
?>

<script>
    function guardar_img_ajax(){
        var modificar_alt = jQuery("#modificar_alt").val();
        var ruta_obtener_img = jQuery("#url_act_img_post").val();
        var numero_imagenes_articulo= jQuery('#num_attachments_post').val();
        if (numero_imagenes_articulo >0){
            var array_envio_ajax = "";
            for (var i = 1;i<=numero_imagenes_articulo;i++){
                var id_articulo=jQuery(".avy_num_"+i).val();
                var nombre_tit=jQuery(".avy_tit_"+i).val();
                array_envio_ajax+=id_articulo+";;;"+nombre_tit+":::";
            }
            array_envio_ajax+="FIN";
            array_envio_ajax=array_envio_ajax.replace(":::FIN","");

            jQuery.ajax({
                type       : "POST",
                data       : {array_ajax: array_envio_ajax, modificar_alt: modificar_alt},
                dataType   : "html",
                url        : ruta_obtener_img,
                beforeSend : function(){},
                success    : function(data){
                    jQuery('#respuesta_ajax').html(data);
                },
                error     : function(jqXHR, textStatus, errorThrown) {
                    alert(jqXHR + " :: " + textStatus + " :: " + errorThrown);
                }
            });

        }

    }
</script>
