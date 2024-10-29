<?php
require_once( '../../../wp-load.php' );

$string_array_ajax = $_POST['array_ajax'];
$modificar_alt = $_POST['modificar_alt'];

if ($string_array_ajax == ""){
    echo "<div class='respuesta'></div>";
}else{

    $array_separado = explode(":::",$string_array_ajax);



    echo "<table><tr>
        <th>Attachment ID</th>
        <th>New title</th>
        <th>Title updated</th>
        <th>Alt updated in post</th>
    </tr>";

    $par=false;
    foreach($array_separado as $string_imagen){
        $par=!$par;
        $array_imagen=explode(";;;",$string_imagen);
        $id_attachment=$array_imagen[0];
        $title_attachment=$array_imagen[1];



        $attachment_original = get_post($id_attachment);
        $titulo_original = $attachment_original->post_title;


        $id_post_padre = $attachment_original->post_parent;
        $post_padre = get_post($id_post_padre);
        $contenido_post_padre = $post_padre->post_content;
        $nuevo_alt = 'alt="'.$title_attachment.'"';
        $contenido_post_padre_reemplazado = str_replace('alt="'.$titulo_original.'"','alt="'.$title_attachment.'"',$contenido_post_padre);


        $imagen_alterada=false;
        if ($titulo_original!=$title_attachment) $imagen_alterada=true;

        $attachment_nuevo = array(
            'ID'           => $id_attachment,
            'post_title'   => $title_attachment,
        );
        $respuesta = wp_update_post( $attachment_nuevo );


        $title_reemplazado=false;
        $alt_reemplazado_en_post=false;

        if (($respuesta!=0)&&($imagen_alterada)){
            $title_reemplazado=true;
            if ($modificar_alt==1){
                $post_padre_nuevo = array(
                    'ID'           => $id_post_padre,
                    'post_content'   => $contenido_post_padre_reemplazado,
                );
                $respuesta = wp_update_post( $post_padre_nuevo );
                if($respuesta!=0){
                    if (contiene_string($nuevo_alt,$contenido_post_padre_reemplazado)){
                        $alt_reemplazado_en_post=true;
                    }
                }
            }
        }

        if ($title_reemplazado) $title_reemplazado="<span class='tick'>&#x2714;</span>"; else $title_reemplazado="<span class='cross'>&#x2718;</span>"; ;
        if ($alt_reemplazado_en_post) $alt_reemplazado_en_post="<span class='tick'>&#x2714;</span>"; else $alt_reemplazado_en_post="<span class='cross'>&#x2718;</span>"; ;
        if (!$imagen_alterada){
            $title_reemplazado="-";
            $alt_reemplazado_en_post="-";
        }
        if ($modificar_alt==0) $alt_reemplazado_en_post="-";

        echo "<tr ".(($par)?'style="background-color: rgba(50, 193, 134, 0.17);"':"")." >
            <td>".$id_attachment."</td>
            <td>".$title_attachment."</td>
            <td>".$title_reemplazado."</td>
            <td>".$alt_reemplazado_en_post."</td>
            </tr>";
    }
    echo "</table>";
}


function contiene_string($aguja,$pajar){
    if (strpos($pajar, $aguja) !== false) {
        return true;
    }else{
        return false;
    }
}
?>

<script>
    function guardar_img_ajax(){
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
            alert(array_envio_ajax);

        }

    }
</script>
