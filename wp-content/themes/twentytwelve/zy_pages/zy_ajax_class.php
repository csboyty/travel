<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ty
 * Date: 13-6-26
 * Time: 下午12:03
 * 一些ajax处理函数
 */
include ("zy_image_class.php");
class zy_ajax_class{

    const ZY_COMPRESS_SUFFIX="_zy_compress"; //常量，代表压缩文件的后缀
    const ZY_TOP_POST_COUNT=4;
    const ZY_TOP_TERM_ID=11; //本地为8

    /*
     *ajax 上传文件函数
     */
    public function zy_action_uploadfile(){

        //最先判断是否登录


        $image=new zy_image_class();

        $dir=wp_upload_dir();

        //存储的时候存储到文件系统，返回的时候要返回路径
        $user_id=$_POST["user_id"];
        $post_id=$_POST["post_id"];

        //文件代表的类型，如果是content_img表示是要显示到内容中的图片，zy_thumb表示文章的封面，zy_background表示背景
        $file_use_type=isset($_POST["file_type"])?$_POST["file_type"]:"";


        $filename=$_FILES["file"]["name"];
        $pathinfo=pathinfo($filename);

        //中文为自首的文件会是空
        $pathinfo["filename"]=substr($filename, 0,strrpos($filename, '.'));
        $filetype =$pathinfo["extension"];//获取后缀

        //判断缩略图是否为1：1
        if($file_use_type=="zy_thumb"){
            $attr=getimagesize($_FILES["file"]["tmp_name"]);
            if($attr[0]!=$attr[1]){

                //如果不是1：1的图片报错
                $obj=array("message"=>"图片不是1：1比例！");
                wp_send_json_error($obj);
            }
        }


        //判断背景图是否为1280宽
        if($filetype!="mp4"&&$file_use_type=="zy_background"){
            $attr=getimagesize($_FILES["file"]["tmp_name"]);
            if($attr[0]!=1280||$attr[1]!=720){

                //如果不是1：1的图片报错
                $obj=array("message"=>"图片宽度不是1280或者高度不是720！");
                wp_send_json_error($obj);
            }
        }

        $tmp_dir=$dir["basedir"]."/tmp";
        $target_dir=$tmp_dir."/".$user_id;

        //判断图片是否存在，如果存在，则重命名，加上当前时间搓
        if($file_use_type!="zy_music"){
            if(is_file($target_dir . "/".$filename)||(!empty($post_id)&&is_file($dir["basedir"]."/".$post_id."/".$filename))){
                $filename=$pathinfo["filename"]."_".time().".".$filetype;
            }
        }


        //创建中转文件夹
        if(!is_dir($tmp_dir)){
            if(!mkdir($tmp_dir)){
                $obj=array("message"=>"创建临时文件夹失败！");
                wp_send_json_error($obj);
            }
        }

        //创建目标文件夹
        if(!is_dir($target_dir)){
            if(!mkdir($target_dir)){
                $obj=array("message"=>"创建文件夹失败！");
                wp_send_json_error($obj);
            }
        }


        //此处需要文件转码才能支持中文
        if(move_uploaded_file($_FILES["file"]["tmp_name"],$target_dir . "/".$filename)){

            //如果是zip文件，需要解压，如果解压不成功，返回false，这里看是在什么时候解压（上传、放到最终目录时）
            if($filetype=="zip"){
                //$zipdir=$pathinfo["filename"];//获取除后缀名外的文件名部分，中文名字有bug采用下面的方法

                $zipdir=substr($filename, 0, strrpos($filename, "."));

                //创建文件夹
                if(!is_dir($target_dir."/".$zipdir)){
                    if(!mkdir($target_dir."/".$zipdir)){
                        $obj=array("message"=>"创建压缩文件夹失败！");
                        wp_send_json_error($obj);
                    }
                }

                //开始解压zip
                $zip = new ZipArchive();
                $rs = $zip->open($target_dir."/".$filename);
                if($rs !== TRUE)
                {

                    //如果为成功，直接返回
                    $obj=array("message"=>"解压zip文件出错");
                    wp_send_json_error($obj);
                }

                //解压到哪个文件夹下
                $zip->extractTo($target_dir."/".$zipdir);
                //$zip->extractTo($target_dir);
                $zip->close();

                //返回结果
                $obj=array("url"=>$dir["baseurl"]."/tmp/".$user_id."/".$zipdir,"filename"=>$filename);
                //$obj=array("url"=>"ssss");
                wp_send_json_success($obj);

            }

            //压缩幻灯片中每个媒体文件图片(图片本身、媒体文件缩略图)
            if($file_use_type=="zy_content_img"){
                $image->resize($target_dir . "/".$filename,400,600);
            }

            //压缩整篇文章的封面图
            if($file_use_type=="zy_thumb"){
                $image->resize($target_dir . "/".$filename,400,400);
            }

            //返回结果
            $obj=array("url"=>$dir["baseurl"]."/tmp/".$user_id."/".$filename,"filename"=>$filename);
            wp_send_json_success($obj);

        }else{

            //返回结果（错误）
            $obj=array("message"=>"文件上传失败，请稍后重试");
            wp_send_json_error($obj);
        }
    }

    /**
     *打包程序返回接口,打包成功后的反馈
     */
    public function zy_pack_unlock_callback(){
        global $wpdb;
        $post_id=$_POST['docId'];
        $success_flag=$_POST['packed_status'];
        $tablename=$wpdb->prefix."pack_ids";
        $zy_packing_ids=$wpdb->get_col("SELECT post_id FROM $tablename WHERE post_id=$post_id");

        //解锁文章
        if(count($zy_packing_ids)){

            //重置数据库记录,需要记录的id在数据库中
            if($success_flag=="true"){

                //设置数据库标志,表示已经打包
                if($wpdb->update($wpdb->prefix."pack_ids",array("pack_lock"=>1),array("post_id"=>$post_id),array("%d"))){
                    echo "success";
                    die();
                }else{

                    //返回failture打包程序将帮助将标志置位1
                    echo "failure";
                    die();
                }
            }else{

                //去掉数据库的打包时间，不锁定文章，这里不进行update判断，因为不通知打包程序来重置时间
                $wpdb->update($wpdb->prefix."pack_ids",array("pack_time"=>NULL),array("post_id"=>$post_id),array("%s"));
                echo "success";
                die();
            }
        }else{

            //如果id在数据库中不存在，不做任何操作
            echo "success";
            die();
        }
    }

    /**
     *获取音乐接口
     */
    public function zy_get_music(){
        $program_id=$_REQUEST["programId"];//项目id比如新疆分类的term_id
        $posts=get_posts(array("posts_per_page"=>-1,
            'category'=>"$program_id",
            'post_type'=>"zy_music"
        ));
	    $dir=wp_upload_dir();
        $result_obj=array();

        //组装数据
        foreach($posts as $post){
            $post_author=get_user_by("id",$post->post_author)->user_nicename;
            $music_size=get_post_meta($post->ID,"zy_music_size",true);
            $obj=array("music_id"=>$post->ID,"music_name"=>$post->post_content,
                "music_path"=>$dir["baseurl"]."/".$post->ID."/".$post->post_content,
                "music_size"=>$music_size,"music_author"=>$post_author,"music_title"=>$post->post_title);
            array_push($result_obj,$obj);
        }

        //设置每个域都可以访问
        header('Access-Control-Allow-Origin: *');
        wp_send_json_success($result_obj);
    }

    /**
     * 获取文章
     */
    public function zy_get_posts(){
        global $wpdb;
        $category_id=$_POST["categoryId"];
        $limit=$_POST["limit"];
        $lastDate=$_POST["lastDate"];
        $view=$wpdb->prefix."posts_view";

        //排序和搜索条件
        $date_filter=$lastDate?" post_date<'$lastDate'":"1=1";



        //使用了视图，在functions.php中定义
        $results=$wpdb->get_results("SELECT * FROM $view WHERE term_id=$category_id AND $date_filter LIMIT $limit");

        if($results===null){

            //返回空数组
            $results=array();
        }else{
            //刷选结果
            foreach($results as $value){
                $value->post_full_date=$value->post_date;
                $value->post_date=explode(" ",$value->post_date);
                $value->post_date=$value->post_date[0];
                $value->thumb=json_decode($value->thumb,true);
                $value->thumb=$value->thumb["filepath"];

                //获取压缩后的图
                $thumb_path=pathinfo($value->thumb);

                //中文为自首的文件会是空
                $thumb_path["filename"]=substr($value->thumb, strrpos($value->thumb,"/")+1,strrpos($value->thumb, '.')-strrpos($value->thumb,"/")-1);

                $thumb_compress=$thumb_path["filename"].self::ZY_COMPRESS_SUFFIX.".".$thumb_path["extension"];
                $thumb_array=explode("/",$value->thumb);
                $thumb_array[count($thumb_array)-1]=$thumb_compress;
                $value->thumb=implode("/",$thumb_array);

                //设置文章类型
                $value->post_mime_type=$value->post_mime_type?$value->post_mime_type:"zypost";

                //获取文章的背景
                $background=get_post_meta($value->post_id,"zy_background",true);
                $value->background=json_decode($background,true);

                $old_medias=get_post_meta($value->post_id);
                foreach($old_medias as $key=>$keyValue){
                    if(strpos($key,"zy_location_")!==false){
                        $value->hasVideo=true;
                        break;
                    }
                }

            }
        }

        //设置每个域都可以访问
        header('Access-Control-Allow-Origin: *');
        wp_send_json_success($results);
    }

    /**
     *首页获取顶部4个
     */
    public function zy_get_top_posts(){
        global $wpdb;
        $program_id=$_POST["programId"];//项目id比如新疆分类的term_id
        $view=$wpdb->prefix."posts_view";

        //使用了视图,头条文章，采用标签，使用的是标签的id匹配
        $results=$wpdb->get_results("SELECT * FROM $view  WHERE term_id=$program_id AND post_id in (SELECT post_id FROM $view WHERE term_id="
        .self::ZY_TOP_TERM_ID.") LIMIT ".self::ZY_TOP_POST_COUNT);

        if($results===null){
            $results=array();
        }else{
            //筛选数据
            foreach($results as $value){
                $value->post_date=explode(" ",$value->post_date);
                $value->post_date=$value->post_date[0];
                $value->thumb=json_decode($value->thumb,true);
                $value->thumb=$value->thumb["filepath"];

                //获取压缩后的图
                $thumb_path=pathinfo($value->thumb);

                //中文为自首的文件会是空
                $thumb_path["filename"]=substr($value->thumb, strrpos($value->thumb,"/")+1,strrpos($value->thumb, '.')-strrpos($value->thumb,"/")-1);

                $thumb_compress=$thumb_path["filename"].self::ZY_COMPRESS_SUFFIX.".".$thumb_path["extension"];
                $thumb_array=explode("/",$value->thumb);
                $thumb_array[count($thumb_array)-1]=$thumb_compress;
                $value->thumb=implode("/",$thumb_array);

                //设置文章类型
                $value->post_mime_type=$value->post_mime_type?$value->post_mime_type:"zypost";

                //获取文章的背景
                $background=get_post_meta($value->post_id,"zy_background",true);
                $value->background=json_decode($background,true);

                $old_medias=get_post_meta($value->post_id);
                foreach($old_medias as $key=>$keyValue){
                    if(strpos($key,"zy_location_")!==false){
                        $value->hasVideo=true;
                        break;
                    }
                }
            }
        }



        //设置每个域都可以访问
        header('Access-Control-Allow-Origin: *');
        wp_send_json_success($results);
    }

    /**
     *获取文章的详细信息
     */
    public function zy_get_post_detail(){
        $post_id=$_POST['post_id'];
        $post=get_post($post_id);

        //有可能在点击前文章被管理员删除了
        if($post===null){

            //设置每个域都可以访问
            header('Access-Control-Allow-Origin: *');
            wp_send_json_success(array("message"=>"文件已经被删除。"));
        }

        $author=get_user_by("id",$post->post_author)->display_name;
        $categories=wp_get_post_categories($post_id);
        $category=get_category(max($categories[1],$categories[0]))->name;
        $date_string=$post->post_date;
        $date_array=explode(" ",$date_string);
        $date=$date_array[0];
        $thumb=get_post_meta($post_id,"zy_thumb",true);
        $thumb_array=json_decode($thumb,true);

        //替换内容中不需要的字符串
        $content=str_replace("width","data-width",$post->post_content);
        $content=str_replace("height","data-height",$content);
        $content=preg_replace("/[\[]caption.+?]/","<figcaption>",$content);
        $content=str_replace("[/caption]","</figcaption>",$content);

        $post_slides=array();

        //筛选数据
        if($post->post_mime_type=="zyslide"){

            //如果是幻灯片类型，要组装好每个幻灯片页数据
            $doc=new DOMDocument();
            $content=html_entity_decode($post->post_content);
            $doc->loadXML("<as>".$content."</as>");
            $imgs=$doc->getElementsByTagName("a");
            foreach($imgs as $i){
                $class=$i->getAttribute("class");
                $big_img=$i->getAttribute("href");
                $img=$i->getElementsByTagName("img")->item(0);
                $img_id=$img->getAttribute("data-zy-media-id");
                $img_src=$img->getAttribute("src");
                $info=get_post_meta($post_id,$img_id,true);
                $info_array=json_decode($info,true);
                $title=$info_array["zy_media_title"];
                $memo=$info_array["zy_media_memo"];

                $class_string="";
                if($class){
                    $class_string="class='$class'";
                }

                array_push($post_slides,array(
                    "content"=>"<a $class_string href='$big_img'><img src='$img_src' data-zy-media-id='$img_id'></a>",
                    "title"=>$title,
                    "memo"=>$memo
                ));
            }
        }

        //组装返回结果
        $results=array("post_title"=>$post->post_title,
            "post_type"=>$post->post_mime_type?$post->post_mime_type:"zypost",
            "post_excerpt"=>$post->post_excerpt,
            "post_author"=>$author,
            "post_category"=>$category,
            "post_date"=>$date,
            "post_content"=>$content,
            "post_slides"=>$post_slides,
            "post_thumb"=>$thumb_array["filepath"]
        );

        //设置每个域都可以访问
        header('Access-Control-Allow-Origin: *');
        wp_send_json_success($results);
    }
}