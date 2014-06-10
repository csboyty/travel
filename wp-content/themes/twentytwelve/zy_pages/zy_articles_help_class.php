<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ty
 * Date: 13-6-20
 * Time: 上午9:54
 * 主要是获取一些数据，比如说修改的时候要刷出来的数据
 */
class zy_articles_help_class
{

    const ZY_COMPRESS_SUFFIX="_zy_compress"; //常量，代表压缩文件的后缀

    /**
     * 获取幻灯片已经上传的内容
     * @param int $slide_id 幻灯片id
     */
    public function zy_get_slide_medias($slide_id){
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
                $zy_old_thumb_compress=$filename.self::ZY_COMPRESS_SUFFIX.".".$ext;
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
    public function zy_get_post_medias($post_id){
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
    public function zy_get_compress_thumb($filepath){
        $pathinfo=pathinfo($filepath);
        $dir=$pathinfo["dirname"];

        //中文为自首的文件会是空
        $filename=substr($filepath,strrpos($filepath, '/')+1,strrpos($filepath, '.')-strrpos($filepath, '/')-1);

        $ext=$pathinfo["extension"];
        $zy_old_thumb_compress=$filename.self::ZY_COMPRESS_SUFFIX.".".$ext;
        echo $dir."/".$zy_old_thumb_compress;
    }

    /**
     * 获取原来的封面图
     * @param int $post_id 文章或者幻灯片id
     * @return array|mixed 封面图数据的数组或者空字符
     */
    public function zy_get_old_thumb($post_id){

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
    public function zy_get_tags($post_id){
        $tags=wp_get_object_terms($post_id,"post_tag");

        foreach($tags as $tag){
            echo "<span class='zy_tag'><input type='hidden' name='zy_tags[]' value='".$tag->name."'> <a class='zy_tag_delete'>X</a><span class='zy_tag_name'>".$tag->name."</span></span>";
        }

    }

    /**
     * 获取原来的背景图
     * @param int $post_id 文章或者幻灯片id
     */
    public function zy_get_old_background($post_id){
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
}