<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ty
 * Date: 14-10-22
 * Time: 上午9:20
 * To change this template use File | Settings | File Templates.
 */
include(get_template_directory()."/zy_pages/view/class_zy_box.php");
class Zy_Post_Helper {
    const COMPRESS_SUFFIX="_zy_compress"; //常量，代表压缩文件的后缀

    public function __construct(){
        add_action("admin_init",array($this,"delete_tmp"));
        add_action('trashed_post',array($this,'trash_post'));

        add_action("pre_post_update",array($this,"check_lock"));
        add_action("before_delete_post",array($this,"check_lock"));

        add_action('deleted_post', array($this,'delete_post'));

        add_action("wp_print_scripts",array($this,"disable_autosave"));
        add_action("publish_post",array($this,"delete_autodraft"));

        remove_action("pre_post_update","wp_save_post_revision");

        add_action("add_meta_boxes",array($this,'add_box'));
    }

    /**
     * 获取幻灯片已经上传的内容
     * @param int $slide_id 幻灯片id
     */
    public function get_slide_medias($slide_id){
        if($slide_id){

            /*
             * 需要通过获取内容中的media_id，搜索出所有的绑定了媒体文件，
             * 这个顺序才是页面上已上传列表的顺序
             * */
            $doc=new DOMDocument();
            $content=html_entity_decode(get_post($slide_id)->post_content);
            $doc->loadXML("<as>".$content."</as>");
            $imgs=$doc->getElementsByTagName("a");
            $results_ids=array();

            foreach($imgs as $i){
                $img=$i->getElementsByTagName("img")->item(0);
                $img_id=$img->getAttribute("data-zy-media-id");
                $results_ids[$img_id]=json_decode(get_post_meta($slide_id,$img_id,true),true);
            }

            //按照顺序循环输出内容
            foreach($results_ids as $key=>$value_array){
                $iframe_page_src="";

                //获取要跳转的iframe页面,strpos这里返回值是0，出错的情况是衡等于false
                if(strpos($key,"zy_location_")!==false){
                    $iframe_page_src=get_template_directory_uri()."/zy_pages/zy_set_location_video.html?$key";
                }else if(strpos($key,"zy_3d_")!==false){
                    $iframe_page_src=get_template_directory_uri()."/zy_pages/zy_set_3d.html?$key";
                }else if(strpos($key,"zy_network_")!==false){
                    $iframe_page_src=get_template_directory_uri()."/zy_pages/zy_set_network_video.html?$key";
                }else if(strpos($key,"zy_ppt_")!==false){
                    $iframe_page_src=get_template_directory_uri()."/zy_pages/zy_set_ppt.html?$key";
                }else if(strpos($key,"zy_image_")!==false){
                    $iframe_page_src=get_template_directory_uri()."/zy_pages/zy_set_image.html?$key";
                }

                //获取类型和缩略图等
                $media_type=$value_array["zy_media_type"];
                $media_name=$value_array["zy_media_filename"]?$value_array["zy_media_filename"]:$value_array["zy_media_thumb_filename"];
                $media_name=htmlentities($media_name,ENT_QUOTES,"UTF-8");

                //显示压缩的图
                $thumb_path=$value_array["zy_media_thumb_filepath"];
                $pathinfo=pathinfo($thumb_path);
                $dir=$pathinfo["dirname"];

                //中文为自首的文件会是空
                $filename=substr($thumb_path, strrpos($thumb_path,"/")+1,strrpos($thumb_path, '.')-strrpos($thumb_path,"/")-1);

                $ext=$pathinfo["extension"];
                $zy_old_thumb_compress=$filename.self::COMPRESS_SUFFIX.".".$ext;
                $media_thumb_filepath=$dir."/".$zy_old_thumb_compress;

                echo '<li><a class="zy_media_list" data-zy-media-type="'.$media_type.'" data-zy-media-id="'.$key.'" href="'.$iframe_page_src.'" target="zy_media_iframe">'.
                    '<img class="zy_small_thumb" src="'.$media_thumb_filepath.'">'.
                    '<span title="'.$media_name.'" draggable="true" class="zy_media_filename">'.$media_name.'</span><span class="zy_media_delete"></span></a></li>';
            }

            $filter_medias_string=json_encode($results_ids);

            if($filter_medias_string!="[]"){
                echo "<script type='text/javascript'>zy_uploaded_medias=$filter_medias_string</script>";

                //代表原来有媒体文件，保存的时候需要获取出来和提交的比较
                echo "<input type='hidden' name='zy_old_medias' value='1'> ";
            }
        }
    }

    /**
     * 获取图文混排已经上传的内容
     * @param int $post_id 文章id
     */
    public function get_post_medias($post_id){
        $old_medias=get_post_meta($post_id);
        $filter_medias=array();

        foreach($old_medias as $key=>$value){
            if(strpos($key,"zy_location_")!==false||strpos($key,"zy_3d_")!==false||strpos($key,"zy_ppt_")!==false||strpos($key,"zy_network_")!==false){
                $filter_medias[$key]=json_decode($value[0],true);
            }
        }

        $filter_medias_string=json_encode($filter_medias);
        if($filter_medias_string!="[]"){
            echo "<script type='text/javascript'>zy_uploaded_medias=$filter_medias_string</script>";
            echo "<input type='hidden' name='zy_old_medias' value='1'> ";
        }
    }

    /**
     * 获取封面图路径(这里主要是指系统压缩后的图片路径)
     * @param string $filepath 封面图路径
     */
    public function get_compress_thumb($filepath){
        $pathinfo=pathinfo($filepath);
        $dir=$pathinfo["dirname"];

        //中文为自首的文件会是空
        $filename=substr($filepath,strrpos($filepath, '/')+1,strrpos($filepath, '.')-strrpos($filepath, '/')-1);

        $ext=$pathinfo["extension"];
        $zy_old_thumb_compress=$filename.self::COMPRESS_SUFFIX.".".$ext;
        echo $dir."/".$zy_old_thumb_compress;
    }

    /**
     * 获取原来的封面图
     * @param int $post_id 文章或者幻灯片id
     * @return array|mixed 封面图数据的数组或者空字符
     */
    public function get_old_thumb($post_id){

        //获取原来的缩略图，如果不存在返回空字符
        $zy_old_thumb=get_post_meta($post_id,"zy_thumb",true);
        $zy_thumb_filename="";

        if($zy_old_thumb){
            $zy_old_thumb=json_decode($zy_old_thumb,true);
            $zy_thumb_filename=$zy_old_thumb["filename"];
            echo "<input type='hidden' name='zy_old_thumb' value='$zy_thumb_filename'>";
        }

        //将值设置为原始值，不存在的话也会是空
        echo "<input type='hidden' value='$zy_thumb_filename' name='zy_thumb' id='zy_thumb'>";

        return $zy_old_thumb;
    }

    /**
     * 获取幻灯片原来绑定的标签
     * @param int $post_id 文章或者幻灯片id
     */
    public function get_tags($post_id){
        $tags=wp_get_object_terms($post_id,"post_tag");

        foreach($tags as $tag){
            echo "<span class='zy_tag'><input type='hidden' name='zy_tags[]' value='".$tag->name."'> <a class='zy_tag_delete'>X</a><span class='zy_tag_name'>".$tag->name."</span></span>";
        }

    }

    /**
     * 获取原来的背景图
     * @param int $post_id 文章或者幻灯片id
     */
    public function get_old_background($post_id){
        $zy_old_background=get_post_meta($post_id,"zy_background",true);
        $zy_background_filename="";

        if($zy_old_background){
            $zy_old_background=json_decode($zy_old_background,true);
            $zy_background_filename=$zy_old_background["filename"];
            echo "<input type='hidden' name='zy_old_background' value='$zy_background_filename'>";
        }

        if($zy_old_background){
            $filepath=$zy_old_background["filepath"];
            if($zy_old_background["type"]=="mp4"){
                echo "<video id='zy_background_content'  class='zy_background' controls><source src='$filepath' type='video/mp4' /></video>";
            }else{
                echo "<img id='zy_background_content' class='zy_background' src='$filepath'>";
            }
        }else{
            echo "<img id='zy_background_content' class='zy_background' src='".get_template_directory_uri()."/images/app/zy_default_background.png'>";
        }

        //将值设置为原始值，不存在的话也会是空
        echo "<input type='hidden' value='$zy_background_filename' name='zy_background' id='zy_background'>";
    }

    public function delete_tmp(){
        global $user_ID;
        $currentTimeS=time();
        $target_dir=wp_upload_dir();
        $target_dir=$target_dir["basedir"]."/tmp/".$user_ID;
        if(is_dir($target_dir)){
            $fileTimeS=filemtime($target_dir);
            if($currentTimeS-$fileTimeS>12*60*60){
                Zy_Util::deldir($target_dir);
            }
        }
    }

    public function trash_post($post_id){
        header("content-type:text/html; charset=utf-8");

        //只有文章和幻灯片才发送请求去打包程序
        if(get_post($post_id)->post_type=="post"){
            //发送数据给打包程序，删除zip包
            $url=get_site_url()."/bundle-app/removeBundle";
            $zy_http_result=false;

            for($i=0;$i<3;$i++){
                if(Zy_Util::http_send($post_id,$url)){
                    $zy_http_result=true;
                    break;//跳出循环
                }
            }

            //设置数据库的值
            global $wpdb;
            if($zy_http_result){
                if($wpdb->update($wpdb->prefix."pack_ids",array("pack_lock"=>0,"pack_time"=>NULL),array("post_id"=>$post_id),array("%d","%s"))===false){
                    die("重置打包数据库失败，请将文章id".$post_id."告诉开发人员！");
                }
            }else{
                die("删除打包文件失败，请将文章id".$post_id."告诉开发人员！");
            }
        }
    }

    public function check_lock($post_id){
        global $wpdb;
        $tablename=$wpdb->prefix."pack_ids";
        $zy_pack=$wpdb->get_row("SELECT * FROM $tablename WHERE post_id=$post_id");
        if($zy_pack->pack_time){
            if(time()-$zy_pack->pack_time<1800&&$zy_pack->pack_lock==0){

                //打包时间在30分钟内，并且还没有设置打包标志为1的需要锁定
                header("content-type:text/html; charset=utf-8");
                die("文章正在被打包，请稍后进行操作，<a href='javascript:history.back()'>返回</a>进行其他操作");
            }
        }

        //如果提交的edit_lock和数据库中保存的不一样，那么要阻止提交
        $current_edit_lock=get_post_meta($post_id,"_edit_lock",true);
        $edit_lock=$_POST["_edit_lock"];
        if($current_edit_lock!=$edit_lock&&$edit_lock){
            header("content-type:text/html; charset=utf-8");
            die("其他人以先于你提交更改，请重新编辑后再提交，<a href='".site_url()."/wp-admin/edit.php'>返回</a>");
        }
    }

    function delete_post($post_id){

        header("content-type:text/html; charset=utf-8");
        global $wpdb;
        if(get_post($post_id)->post_type=="post"){

            //如果是文章或者幻灯片类型（post）
            $targetDir=wp_upload_dir();

            /*不管删除打包文件是否成功，都删除服务器的内容*/

            //删除打包表中的数据
            $sql_result=$wpdb->delete($wpdb->prefix."pack_ids",array("post_id"=>$post_id));
            $delete_file_result=true;
            if(is_dir($targetDir["basedir"]."/".$post_id)){
                $delete_file_result=Zy_Util::deldir($targetDir["basedir"]."/".$post_id);
            }

            //如果成功删除媒体文件夹
            if(!$delete_file_result||$sql_result===false){
                die("删除文件或者打包数据表记录失败，请将文章id".$post_id."告诉开发人员！");
            }

        }

    }

    public function disable_autosave(){
        wp_deregister_script("autosave");
    }

    public function delete_autodraft(){
        global $wpdb;
        //在发布文章的时候删除掉除自己外的其他垃圾文章，除自己外是因为当没填写任何内容发布时，状态也是auto-draft
        $wpdb->query("DELETE FROM $wpdb->posts WHERE post_status = 'auto-draft'");
    }

    public function add_box(){

        //add_meta_box("zy_thumb_id","缩略图",array($zy_post_box,'zy_post_thumb_box'),'post','side');
        add_meta_box("zy_thumb_id","缩略图",array("Zy_box",'post_thumb_box'),'post','side');
        add_meta_box("zy_background_id","背景",array("Zy_box",'post_background_box'),'post','side');
    }
}