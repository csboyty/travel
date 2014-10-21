<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ty
 * Date: 14-10-5
 * Time: 上午10:46
 * To change this template use File | Settings | File Templates.
 */

class Zy_Resource {
    private $template_url;
    public function __construct(){
        $this->template_url=get_template_directory_uri();
        add_action('admin_enqueue_scripts',array( $this, 'load_all_resource' ));
    }
    public function load_music_resource(){
        global $user_ID;//当前用户id
        wp_enqueue_script("plupload");
        wp_enqueue_script("plupload-html5");
        //加载音乐js
        wp_enqueue_script("zy_music_js",$this->template_url.'/js/app/zy_music.js');
        //引入自定义的css
        wp_enqueue_style("zy_music_css",$this->template_url.'/css/app/zy_music.css');
        //刷出用户id，火狐上传的时候无法获取id
        echo "<script type='text/javascript'>
            var zy_config={
                 zy_user_id:'$user_ID',
                 zy_mp3_upload_size:'200mb'
             };
             Object.freeze(zy_config);
            </script>";
    }
    public function load_post_resource(){
        global $user_ID;//当前用户id

        wp_enqueue_script("zy_common_js",$this->template_url.'/js/app/zy_common.js');
        //引入文章页面的js
        wp_enqueue_script("zy_post_js",$this->template_url.'/js/app/zy_post.js');
        //引入自定义的css
        wp_enqueue_style("zy_post_css",$this->template_url.'/css/app/zy_post.css');

        echo "<script type='text/javascript'>
            var zy_uploaded_medias={};
            var zy_config={
                    zy_template_url:'$this->template_url',
                    zy_user_id:'$user_ID',
                    zy_img_upload_size:'2mb',
                    zy_media_upload_size:'200mb',
                    zy_compress_suffix:'_zy_compress'
                };
            Object.freeze(zy_config);
            </script>";
    }
    public function load_slide_resource(){
        global $user_ID;//当前用户id

        //幻灯片新增和修改
        $name=get_user_by("id",$user_ID)->user_nicename;

        wp_enqueue_script("jquery-ui-autocomplete");//标签自动匹配需要用到

        echo "<script type='text/javascript'>
        var zy_uploaded_medias={},
            zy_config={
                zy_template_url:'$this->template_url',
                zy_user_id:'$user_ID',
                zy_current_author:'$name',
                zy_img_upload_size:'2mb',
                zy_media_upload_size:'200mb',
                zy_compress_suffix:'_zy_compress'
            };
            Object.freeze(zy_config); //锁定对象
        </script>";

        wp_enqueue_script("zy_common_js",$this->template_url.'/js/app/zy_common.js');
        //上传插件
        wp_enqueue_script("plupload");
        wp_enqueue_script("plupload-html5");

        //引入文章页面的js
        wp_enqueue_script("zy_juicer_js",$this->template_url.'/js/lib/juicer-min.js');
        //引入文章页面的js
        wp_enqueue_script("zy_slide_js",$this->template_url.'/js/app/zy_slide.js');
        //引入自定义的css
        wp_enqueue_style("zy_slide_css",$this->template_url.'/css/app/zy_slide.css');
    }
    public function hide_edit_page_toolbar(){
        echo "<style type='text/css'>
            .row-actions .inline,.row-actions .view{display: none};
        </style>";
    }
    public function load_all_resource($hook){
        //新增、修改音乐
        if(($_GET["post_type"]=="zy_music"&&$hook=="post-new.php")||($hook=="post.php"&&get_post($_GET["post"])->post_type=="zy_music")){
            $this->load_music_resource();

        }else if(($hook=="post-new.php"&&!isset($_GET["post_type"]))||($hook=="post.php"&&get_post($_GET["post"])->post_type=="post")){
            //新增、修改图文混排
            $this->load_post_resource();

        }else if($_GET["page"]=="zy_slide_menu"){
            //幻灯片新增和修改
            $this->load_slide_resource();

        }else if($hook=="edit.php"){
            //禁用所有列表页的快速编辑、查看
            $this->hide_edit_page_toolbar();
        }
    }
}
