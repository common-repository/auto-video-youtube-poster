<?php
/*
Plugin Name: Auto Video Youtube Poster
Plugin URI: http://tendenziasmedia.com/avy-poster/
Description: Use your images to auto-create a video with music and upload to your youtube account. It uses the Titles for the titles of the images in the video.
Version: 1.3
Author: @gsmetana & @retamal1990
Author URI: http://tendenziasmedia.com/avy-poster/
*/

if (is_admin()){
    wp_register_style( 'vp_style.css', plugin_dir_url( __FILE__ ) . 'vp_style.css', array(), "1.1" );
    wp_enqueue_style( 'vp_style.css');
}

if(!function_exists(file_get_html)) {
    include_once( plugin_dir_path( __FILE__ ) . "includes/simple_html_dom.php");
}


if (!class_exists("AutoVideoYoutubePublisher")) {

    class AutoVideoYoutubePublisher
    {

        private $urlTV;
        private $songs;
        private $allowedRoles;

        public function __construct()
        {
            $this->urlTV = "http://cangurotv.com";
            $this->debug=0;
            if (strpos($_SERVER['HTTP_HOST'], 'localtz.com') !== false) {
                $this->urlTV = "http://cangurodev.tv"; // Just for debug
                $this->debug=1;
            }
            $this->songs = array("dance", "funky", "pop", "scifi", "sweet");
            $this->frames = array("1", "2", "3", "4", "5","6");
            $this->allowedRoles = array("administrator", "editor", "author");
        }

        function create_menu()
        {
            add_options_page('Auto Video Youtube Poster', 'Auto Video Youtube Poster', 'edit_pages', 'autovideoyoutubepub', array($this, 'options_page'));
        }

        function options_page()
        {
            ?>
            <div class="wrap">
                <h2>AVY Poster (Auto Video Youtube Poster)</h2>
                <p class="vp_introduccion">Convert and send automatically the images of your posts on videos on <span class="vp_youtube">You<span class="vp_tube">Tube</span></span></p>
                <h3>Credentials</h3>
                <form method="post" id="images_to_youtube_form"
                      action="options-general.php?page=autovideoyoutubepub&savedata=true">

                    <table class="vp_tabla">
                        <tr>
                            <td><span class="vp_numero">1.</span>Plugin's credential
                                <span class="vp_boton_ayuda">?
                                    <span class="vp_mensaje">Introduce your unique ID.<a href="http://tendenziasmedia.com/avy-poster/" target="_blank">Get it here!</a></span>
                                </span>
                            </td>
                            <td><input type="text" name="autovideoyoutubepub[credential_plugin]"
                                       value="<?php echo get_option("credentials_images_to_youtube"); ?>" size="50"/>
                            </td>
                        </tr>
                        <tr>
                            <td><span class="vp_numero">2.</span>Authorize plugin on Youtube
                                <span class="vp_boton_ayuda">?
                                    <span class="vp_mensaje">Allows AVY Poster access to your Youtube account</span>
                                </span>
                            </td>
                            <td>
                                <a class="vp_boton vp_verde" href="#" onclick="window.open('https://accounts.google.com/o/oauth2/auth?response_type=code&amp;redirect_uri=urn%3Aietf%3Awg%3Aoauth%3A2.0%3Aoob&amp;client_id=799398898373-get2hcg0fifthb4fb7b2b0t9dgnneknr.apps.googleusercontent.com&amp;scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fyoutube&amp;access_type=offline&amp;approval_prompt=force','Autorizacion','width=600,height=600')">Authorize to publisher</a>
                            </td>
                        </tr>
                        <tr>
                            <td><span class="vp_numero">3.</span> Code Google (Get in step 2)
                                <span class="vp_boton_ayuda">?
                                    <span class="vp_mensaje">Introduce Auth Code get it in step 2 and "Save Changes"</span>
                                </span>
                            </td>
                            <td><input type="text" name="autovideoyoutubepub[code_images_to_youtube]"
                                       value="<?php echo get_option("code_images_to_youtube"); ?>" size="100"/></td>
                        </tr>
                        <tr class="vp_linea_tabla"><td colspan="2"></td></tr>
                        <tr>
                            <td colspan="2" style="text-align: right">
                                <p class="submit" style="text-align: center"><input type="submit" name="submit_autovideoyoutubepub"
                                                                                    class="button-primary vp_boton vp_azul" value="<?php _e('Save Changes') ?>"/>
                                </p>
                            </td>
                        </tr>
                        <tbody class="vp_sub_tabla">
                        <tr class="vp_sub_tabla_header"><td colspan="2">Custom your videos</td></tr>
                        <tr>
                            <td class="vp_td">Intro image (recommended 1280x768)</td>
                            <td><input placeholder="http://tudominio.com/wp-content/uploads/2016/06/tuintro.jpg" type="text" name="autovideoyoutubepub[intro_image_to_youtube]"
                                       value="<?php echo get_option("intro_image_to_youtube"); ?>" size="100"/></td>
                        </tr>
                        <tr>
                            <td class="vp_td">
                                Who can create videos:
                            </td>
                            <td>
                                <p>
                                    <?php
                                    $permiso_crear=get_option("permisos_crear_videos","administrator");
                                    ?>
                                    <select name="autovideoyoutubepub[permisos_crear_videos]">
                                        <option value="administrator" <?php if ($permiso_crear == "administrator") echo "selected"; ?> style="background-color: #a8ca57;color: white;">Administrator</option>
                                        <option value="editor" <?php if ($permiso_crear == "editor") echo "selected"; ?> >Editor</option>
                                        <option value="author" <?php if ($permiso_crear == "author") echo "selected"; ?> >Author</option>
                                    </select>
                                </p>
                            </td>
                        </tr>
                        </tbody>
                        <tr>
                            <td colspan="2" style="text-align: right">
                                <p class="submit" style="text-align: center"><input type="submit" name="submit_autovideoyoutubepub"
                                                                                    class="button-primary vp_boton vp_azul" value="<?php _e('Save Changes') ?>"/>
                                </p>
                            </td>
                        </tr>
                    </table>
                </form>

                <div id="vp_muestra_canciones" class="vp_panel_musica">
                    <h3><span>&#9835;</span>Available songs</h3>
                    <h4>Dance</h4>
                    <audio controls>
                        <source src="http://cangurotv.com/videos/dance.mp3" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>
                    <h4>Funky</h4>
                    <audio controls>
                        <source src="http://cangurotv.com/videos/funky.mp3" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>
                    <h4>Pop</h4>
                    <audio controls>
                        <source src="http://cangurotv.com/videos/pop.mp3" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>
                    <h4>Sweet</h4>
                    <audio controls>
                        <source src="http://cangurotv.com/videos/sweet.mp3" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>
                    <h4>Scifi</h4>
                    <audio controls>
                        <source src="http://cangurotv.com/videos/scifi.mp3" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>
                </div>
                <div class="vp_panel_status vp_panel_imagenes color_claro">
                    <h3><span>&#x272A;</span>Edit image's title from post</h3>
                    <input type="hidden" id="url_img_post" name="url_img_post" value="<?php echo plugins_url( '' , __FILE__ ) ."/obtener_img_post.php"; ?>">
                    <input type="hidden" id="url_act_img_post" name="url_img_post" value="<?php echo plugins_url( '' , __FILE__ ) ."/actualizar_img_post.php"; ?>">
                    <div id="contenedor_editar_img">
                        <input placeholder="ID post: 9773" name="id_post_candidato" id="id_post_candidato" type="number" value="">
                        <div class="boton_post_candidato" onclick="obtener_ajax_img_post();">Search</div>
                    </div>
                    <div id="respuesta_ajax">

                    </div>
                    <script>
                        function obtener_ajax_img_post(){
                            var id_articulo_candidato = jQuery("#id_post_candidato").val();
                            var ruta_obtener_img = jQuery("#url_img_post").val();
                            jQuery.ajax({
                                type       : "POST",
                                data       : {postID: id_articulo_candidato},
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
                    </script>
                    <?php

                    ?>
                </div>
                <div id="vp_muestra_frames" class="vp_panel_status vp_panel_imagenes color_morado">
                <h3><span>&#10066;</span>Available Frames</h3>
                    <div style="text-align: center">
                        <select id="select_frames">
                            <option value="1">Frame 1</option>
                            <option value="2">Frame 2</option>
                            <option value="3">Frame 3</option>
                            <option value="4">Frame 4</option>
                            <option value="5">Frame 5</option>
                            <option value="6">Frame 6</option>
                        </select>
                    </div>
                    <div class="marco_vista_previa" style="border-radius:4px;width:355px; height:200px;background-color: #243f6b;margin: auto;margin-top: 20px;margin-bottom: 20px">
                    </div>
                    <script>
                        jQuery(".marco_vista_previa").css("background-image","url('<?php echo plugins_url( 'images/', __FILE__ ); ?>marco"+jQuery('#select_frames').val()+".jpg')");

                        jQuery('#select_frames').change(function () {
//                            alert(jQuery('#select_frames').val());
                            jQuery(".marco_vista_previa").css("background-image","url('<?php echo plugins_url( 'images/', __FILE__ ); ?>marco"+jQuery('#select_frames').val()+".jpg')");

                        });
                    </script>

                </div>
                <div class="vp_panel_status">
                    <h3><span>&#9757;</span>Uploaded videos</h3>
                    <div id="boton_log_videos" onclick="mostrar_ocultar_filas();">
                        Show more
                    </div>
                    <table id="tabla_log_videos">
                        <thead>
                        <tr class="vp_cabecera">
                            <th>
                                TITLE
                            </th>
                            <th>
                                SONG
                            </th>
                            <th>
                                STATUS
                            </th>
                            <th>
                                TXT
                            </th>
                        </tr>
                        </thead>
                        <?php

                        if ($this->debug==0){
                            $dataUserJson = file_get_contents($this->urlTV . "/getDataForUser.php?credential=". get_option("credentials_images_to_youtube"));
                            $dataUser = json_decode($dataUserJson);
                        }else{
                            $dataUser=array();
                        }
                        $par=true;
                        foreach($dataUser as $data){
                            $par=!$par;
                            if ($par){
                                $fondo=" style='background-color:#f5f5fb;' ";
                            }else{
                                $fondo="";
                            }
                            echo "<tr $fondo >";
                            echo "<td><a href='". home_url("/") . "?p=". $data->post_id ."'>". $data->video_title ."</a></td>";

                            echo "<td>". $data->song ."</td>";
                            if($data->on_youtube == 1){
                                $estilo = "font-weight: bold;background-color: #0cd40c;padding: 2px;color: white;";
                                $status = "Success";
                            }else{
                                if($data->error_uploading == 3){
                                    $status = "Error uploading";
                                    $statusText = $data->error_uploading_text;
                                    $estilo = "font-weight: bold;background-color: #e60606;padding: 2px;color: white;";
                                }else{
                                    $status = "Processing";
                                    $statusText = "";
                                    $estilo = "font-weight: bold;background-color: #e68d06;padding: 2px;color: white;";
                                }
                            }

                            echo "<td style='". $estilo ."'>". $status ."</td>";
                            echo "<td style='width: 20%;'>". $statusText ."</td>";
                            echo "</tr>";
                        }
                        ?>
                    </table>
                    <script>
                        var numero_filas = document.getElementById("tabla_log_videos").rows.length;
                        var mostrar_todas_filas=false;
                        var filas_a_mostrar = 10;
                        if (numero_filas > filas_a_mostrar){
                            jQuery('#boton_log_videos').css("display","block");
                            var i = filas_a_mostrar + 1;
                            for (i ; i <= numero_filas; i++){
                                jQuery('#tabla_log_videos tr:nth-child('+i+')').css("display","none");
                            }
                        }
                        function mostrar_ocultar_filas(){
                            mostrar_todas_filas = !mostrar_todas_filas;
                            var i = filas_a_mostrar + 1;
                            if (mostrar_todas_filas){
                                for (i ; i <= numero_filas; i++){
                                    jQuery('#tabla_log_videos tr:nth-child('+i+')').css("display","table-row");
                                    jQuery('#boton_log_videos').html("Show less");
                                }
                            }else{
                                for (i ; i <= numero_filas; i++){
                                    jQuery('#tabla_log_videos tr:nth-child('+i+')').css("display","none");
                                    jQuery('#boton_log_videos').html("Show more");
                                }
                            }
                        }
                    </script>
                </div>


            </div>
            <?php
        }

        function add_custom_meta_box()
        {
            $permiso_plugin = get_option("permisos_crear_videos","administrator");
            $usuario_actual = $this->get_current_user_role_vp();

            $puede_editar=false;
            if ($usuario_actual == "administrator"){
                $puede_editar=true;
            }else if ($usuario_actual == "editor" && $permiso_plugin != "administrator"){
                $puede_editar=true;
            }else if ($usuario_actual == "author" && $permiso_plugin == "author"){
                $puede_editar=true;
            }

            if ($puede_editar){
                add_meta_box("images-to-youtube-box", "AVY Poster", array(&$this, 'images_to_video_meta_box_markup'), "post", "side", "high", null);
            }
        }


        function images_to_video_meta_box_markup($object)
        {
            wp_nonce_field(basename(__FILE__), "meta-box-nonce");

            ?>

            <?php
            $sended = get_post_meta($object->ID, "sended-post-to-youtube", true);

            if($sended){ ?>
                <br/>
                <div class="vp_mensaje_enviado">¡Video sent!</div>
            <?php } ?>
            <div class="vp_caja_opciones">
                <label for="link_youtube_desc">Add link to description in YouTube</label>
                <input class="checkbox_youtube" name="link_youtube_desc" type="checkbox" checked/>

                <br/><br/>

                <label for="tags_youtube">Tags from post for tags on Youtube</label>
                <input class="checkbox_youtube" name="tags_youtube" type="checkbox" checked/>

                <br/><br/>

                <label for="programar_videos">Publication date schedule</label>
                <input onclick="marcar_checkbox2()" id="programar_videos" name="programar_videos" type="checkbox"/>

                <div id="programacion_vid">
                    <label for="programar_videos_fecha">Date and time</label>
                    <input id="programar_videos_fecha" type="datetime-local" name="programar_videos_fecha">
                </div>

                <script>
                    if (document.getElementById("programar_videos").checked == true){
                        document.getElementById("programacion_vid").style.display="block";
                    }
                    function marcar_checkbox2(){
                        if (document.getElementById("programar_videos").checked == true){
                            document.getElementById("programacion_vid").style.display="block";
                        }else{
                            document.getElementById("programacion_vid").style.display="none";
                            document.getElementById("programar_videos_fecha").defaultValue="";
                            document.getElementById("programar_videos_fecha").value="";
                        }
                    }
                </script>

                <br/><br/>

                <?php
                 $titulo_post_para_youtube = get_post_meta($object->ID, "titulo_alternativo_texto", true);
                ?>
                <label for="titulo_alternativo">Modify video's article title</label>
                <input onclick="marcar_checkbox3()" id="titulo_alternativo" name="titulo_alternativo" type="checkbox" <?php if ($titulo_post_para_youtube != "") echo "checked"; ?> />

                <div id="cambiar_titulo_articulo">
                    <label for="titulo_alternativo_texto">New title only for video</label>
                    <input maxlength="50" id="titulo_alternativo_texto" type="text" name="titulo_alternativo_texto" <?php if ($titulo_post_para_youtube != "") echo 'value="'.$titulo_post_para_youtube.'"'; ?> >
                </div>

                <script>
                    if (document.getElementById("titulo_alternativo").checked == true){
                        document.getElementById("cambiar_titulo_articulo").style.display="block";
                    }
                    function marcar_checkbox3(){
                        if (document.getElementById("titulo_alternativo").checked == true){
                            document.getElementById("cambiar_titulo_articulo").style.display="block";
                        }else{
                            document.getElementById("cambiar_titulo_articulo").style.display="none";
                        }
                    }
                </script>

                <br/><br/>

                <label for="meta-box-dropdown">Songs</label>
                <select class="vp_canciones_select ajustar_select" name="songs-to-youtube">
                    <?php
                    foreach($this->songs as $key => $value)
                    {
                        if($value == get_post_meta($object->ID, "songs-to-youtube", true))
                        {
                            ?>
                            <option selected><?php echo $value; ?></option>
                            <?php
                        }
                        else
                        {
                            ?>
                            <option value="<?php echo $value; ?>"><?php echo $value; ?></option>
                            <?php
                        }
                    }
                    ?>
                </select>
                <div class="vp_boton_canciones">?
                    <div class="vp_canciones_mensaje">
                        Listen the songs available <span><a target="_blank" href="/wp-admin/options-general.php?page=autovideoyoutubepub#vp_muestra_canciones">here</a></span>
                    </div>
                </div>


<!--FRAMESSSS-->
                <br/><br/>

                <label for="meta-box-dropdown">Frame Style</label>
                <select class="vp_canciones_select" name="frames-to-youtube">
                    <?php

                    if (get_post_meta($object->ID, "frames-to-youtube", true)==""){
                        $post_sin_frame_seleccionado=true;
                    } else{
                        $post_sin_frame_seleccionado=false;
                    }
                    foreach($this->frames as $key => $value)
                    {
                        if($value == get_post_meta($object->ID, "frames-to-youtube", true))
                        {
                            ?>
                            <option selected><?php echo $value; ?></option>
                            <?php
                        }
                        else
                        {
                            if ($post_sin_frame_seleccionado && $value=="5" ){
                                ?>
                                <option selected value="<?php echo $value; ?>"><?php echo $value; ?> (Default)</option>
                                <?php
                            }else {
                                ?>
                                <option value="<?php echo $value; ?>"><?php echo $value; ?></option>
                                <?php
                            }

                        }
                    }
                    ?>
                </select>
                <div class="vp_boton_canciones">?
                    <div class="vp_canciones_mensaje">
                        See the entire frames collection <span><a target="_blank" href="/wp-admin/options-general.php?page=autovideoyoutubepub#vp_muestra_frames">here</a></span>
                    </div>
                </div>


            </div>
            <div class="vp_enviar_yt">
                <div class="vp_boton_ayuda2">?
                    <div class="vp_mensaje2">Check this tick <b>and update the post</b>, the video will send to Youtube.</div>
                </div>
                <label onclick="marcar_checkbox()" for="meta-box-checkbox">Send to <span class="vp_youtube">You<span class="vp_tube">Tube</span></span></label>
                <input id="check_enviar_yt" name="send-post-to-youtube" type="checkbox"/>
                <script>
                    function marcar_checkbox(){
                        if (document.getElementById("check_enviar_yt").checked == true){
                            document.getElementById("check_enviar_yt").checked = false;
                        }else{
                            document.getElementById("check_enviar_yt").checked = true;
                        }
                    }
                </script>
            </div>

            <?php
        }

        function save_custom_meta_box($post_id, $post, $update)
        {
            if (!isset($_POST["meta-box-nonce"]) || !wp_verify_nonce($_POST["meta-box-nonce"], basename(__FILE__)))
                return $post_id;

            if(!current_user_can("edit_post", $post_id))
                return $post_id;

            if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
                return $post_id;

            $slug = "post";
            if($slug != $post->post_type)
                return $post_id;

            if(isset($_POST["send-post-to-youtube"])){

                if(isset($_POST["songs-to-youtube"])){
                    $songToYoutube = sanitize_text_field($_POST["songs-to-youtube"]);
                    update_post_meta($post_id, "songs-to-youtube", $songToYoutube);

                    if(!in_array($songToYoutube, $this->songs)){
                        $songToYoutube = "dance";
                    }
                }

                if(isset($_POST["frames-to-youtube"])){
                    $frameToYoutube = sanitize_text_field($_POST["frames-to-youtube"]);
                    update_post_meta($post_id, "frames-to-youtube", $frameToYoutube);

                    if(!in_array($frameToYoutube, $this->frames)){
                        $frameToYoutube = "5";
                    }
                }


                //Alterar titulo del video

                if(isset($_POST["titulo_alternativo"])){

                    if ($_POST["titulo_alternativo"] == "on"){
                        //la casilla de alterar titulo ha sido marcada.
                        update_post_meta($post_id, "titulo_alternativo_texto", $_POST["titulo_alternativo_texto"]);
                    }
                }


                $link_youtube_desc = filter_var($_POST["link_youtube_desc"], FILTER_SANITIZE_NUMBER_INT);
                update_post_meta($post_id, "link_youtube_desc", $link_youtube_desc);

                $tags_youtube = filter_var($_POST["tags_youtube"], FILTER_SANITIZE_NUMBER_INT);
                update_post_meta($post_id, "tags_youtube", $tags_youtube);


                //schedule publication date
                if(isset($_POST["programar_videos"])){
                    $publish_date=filter_var($_POST["programar_videos_fecha"],FILTER_SANITIZE_SPECIAL_CHARS);
                    if ($publish_date == ""){
                        $publish_date=date('Y-m-d_H:i:s');
                    }
                    $publish_date=date('Y-m-d_H:i:s', strtotime($publish_date));
                }else{
                    $publish_date=date('Y-m-d_H:i:s');
                }


                // Ask tendenziastv to get images from API REST
//                file_get_contents($this->urlTV . "/requestPostForVideo.php?url=" . urlencode(home_url("")) . "&post_id=" . $post_id . "&music=". $songToYoutube ."&credential=" . get_option("credentials_images_to_youtube") . "&link_youtube_desc=". $link_youtube_desc . "&tags_youtube=". $tags_youtube);


//                file_get_contents($this->urlTV . "/requestPostForVideo.php?url=" . urlencode(home_url("")) . "&post_id=" . $post_id . "&music=". $songToYoutube ."&credential=" . get_option("credentials_images_to_youtube") . "&link_youtube_desc=". $link_youtube_desc . "&tags_youtube=". $tags_youtube . "&schedule_date=".$publish_date);
                file_get_contents($this->urlTV . "/requestPostForVideo.php?url=" . urlencode(home_url("")) . "&post_id=" . $post_id . "&music=". $songToYoutube ."&credential=" . get_option("credentials_images_to_youtube") . "&link_youtube_desc=". $link_youtube_desc . "&tags_youtube=". $tags_youtube . "&schedule_date=".$publish_date."&frame_youtube=".$frameToYoutube);


                // Lo marcamos como enviado
                update_post_meta($post_id, "sended-post-to-youtube", true);
            }

            return $post_id;
        }


        function save_options()
        {
            $data = $_POST['autovideoyoutubepub'];

            $credentialPlugin = sanitize_text_field($data["credential_plugin"]);
            $codeGoogle = sanitize_text_field($data["code_images_to_youtube"]);
            $introImage = esc_url($data["intro_image_to_youtube"]);
            $permiso_crear_vid = sanitize_text_field($data["permisos_crear_videos"]);

            if(!in_array($permiso_crear_vid, $this->allowedRoles)){
                $permiso_crear_vid = "administrator";
            }

            update_option("credentials_images_to_youtube", $credentialPlugin);
            update_option("code_images_to_youtube", $codeGoogle);
            update_option("intro_image_to_youtube", $introImage);
            update_option("permisos_crear_videos", $permiso_crear_vid);

            $response = file_get_contents($this->urlTV . "/saveUserData.php?credential=" . $credentialPlugin . "&code=" . $codeGoogle . "&introImage=". $introImage);
        }

        function get_current_user_role_vp () {
            global $current_user;
            wp_get_current_user();
            $user_roles = $current_user->roles;
            $user_role = array_shift($user_roles);
            return $user_role;
        }
    }

    $autoVideoYoutubePublisher = new AutoVideoYoutubePublisher();

    if (isset($autoVideoYoutubePublisher)) {
        // create the menu
        add_action('admin_menu', array($autoVideoYoutubePublisher, 'create_menu'));

        // if submitted, process the data
        if (isset($_POST['autovideoyoutubepub'])) {
            add_action('admin_init', array($autoVideoYoutubePublisher, 'save_options'));
        }

        add_action("add_meta_boxes", array($autoVideoYoutubePublisher, 'add_custom_meta_box'));

        add_action("save_post", array($autoVideoYoutubePublisher, "save_custom_meta_box"), 10, 3);
    }


    /******************** Funciones REST API *************************/

    add_action('rest_api_init', function () {
        register_rest_route('video-producer/v1', '/images_from_post/(?P<post_id>(.*)+)', array(
            'methods' => 'GET',
            'callback' => 'avy_get_images_from_post',
        ));

        register_rest_route('video-producer/v1', '/data_from_post/(?P<post_id>(.*)+)', array(
            'methods' => 'GET',
            'callback' => 'avy_get_data_from_post',
        ));
    });

    function avy_get_images_from_post($data)
    {

        $post_id_value = urldecode($data['post_id']);

        // Check if it's an ID
        $post_id = intval( $post_id_value );
        if ( !$post_id ) {
            return;
        }

        $content_post = get_post($post_id);
        $content = $content_post->post_content;

        $html = str_get_html($content);

        $htmlImages = $html->find("img");

        // Get attachments
        $arrImages =& get_children('post_type=attachment&post_mime_type=image&post_parent=' . $post_id );

        $i = 0;
        $images = array();

        foreach ($arrImages as $attachment) {

            $alt = $attachment->post_title;
            $title = $attachment->post_title;
            $src = $attachment->guid;

            $imageName = $attachment->post_name;

            foreach($htmlImages as $htmlImage){
                $srcImage = $htmlImage->src;
                preg_match_all('/'. $imageName .'(\\.(jpg|jpeg|bmp|gif|png)|(-[0-9]{3}x[0-9]{3}\\.(jpg|jpeg|bmp|gif|png)))/', $srcImage, $matches);

                if(count($matches) > 0 && $matches[0][0] != ""){
                    if($htmlImage->alt != "")
                        $alt = $htmlImage->alt;
                    if($htmlImage->title != "")
                        $title = $htmlImage->title;
                    $srcImagen = $htmlImage->src;

                    if($src == NULL || $src == ""){
                        $src = $srcImagen;
                    }
                }
            }

            $images[$i]["src"] = $src ;
            $images[$i]["alt"] = $alt;
            $images[$i]["title"] = $title;
            $i++;
        }

        return $images;
    }

    function avy_get_data_from_post($data)
    {

        $post_id_value = urldecode($data['post_id']);

        // Check if it's an ID
        $post_id = intval( $post_id_value );
        if ( !$post_id ) {
            return;
        }

        $post = get_post($post_id);

        $data = array();
        $data["title"] = $post->post_title;
        $data["description"] = avy_pd_get_excerpt_from_content($post->post_content, 500) . "...";
        $data["url"] = get_permalink($post_id);




        $data["titulo_alternativo_texto"] = get_post_meta( $post_id, 'titulo_alternativo_texto', true );


        $tags = wp_get_post_tags($post->ID);

        $tagText = "";
        foreach ($tags as $tag){
            $tagText .= $tag->name . ",";
        }

        $data["tags"] = substr($tagText, 0, -1);

        return $data;
    }


    function avy_pd_get_excerpt_from_content($postcontent, $length)
    {

        $this_excerpt = strip_shortcodes($postcontent);
        $this_excerpt = strip_tags($this_excerpt);
        $this_excerpt = substr($this_excerpt, 0, $length);

        return $this_excerpt;
    }
}