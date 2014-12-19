<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ty
 * Date: 14-10-22
 * Time: 上午9:11
 * To change this template use File | Settings | File Templates.
 */

class Zy_Rewrite {
    public function __construct(){
        add_action( 'add_meta_boxes', array($this,'page_template_redirect'));
        add_action("init",array($this,"rewrite"));
    }
    public function page_template_redirect(){
        if(isset($_GET["post"])){
            $post_id=$_GET["post"];
            if(strpos(get_post($post_id)->post_mime_type,"zyslide")!==false){
                wp_redirect(admin_url()."edit.php?page=zy_slide_menu&post_id=$post_id");
                exit();
            }
        }
    }

    function rewrite(){

        //添加展示媒体文件的链接地址重写
        add_rewrite_rule('show_media/(\d+)/(\w+)$','index.php?pagename=show_media&zy_post_id=$matches[1]&zy_media_id=$matches[2]','top');
        //如果不加下面两句，wordpress无法识别到自定义的参数
        add_rewrite_tag('%zy_post_id%','([^&]+)');
        add_rewrite_tag('%zy_media_id%','([^&]+)');

        //添加下载音乐的链接地址重写
        add_rewrite_rule('download_music/(\d+)/(.+)$','index.php?pagename=download_music&music_id=$matches[1]','top');
        add_rewrite_tag('%music_id%','([^&]+)');

        //添加展示图片的链接地址重写
        add_rewrite_rule('show_image/(.+)$','index.php?pagename=show_image&zy_image_url=$matches[1]','top');
        add_rewrite_tag('%zy_image_url%','([^&]+)');
    }

}