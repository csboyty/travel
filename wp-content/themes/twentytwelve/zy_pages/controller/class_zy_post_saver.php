<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ty
 * Date: 14-10-6
 * Time: 上午8:53
 * To change this template use File | Settings | File Templates.
 */

class Zy_Post_Saver {
    private $dir,$target_dir,$from_dir;

    const COMPRESS_SUFFIX="_zy_compress"; //常量，代表压缩文件的后缀

    public function __construct(){
        add_action('publish_post', array($this,'data_save'));
    }

    /**
     * 移动媒体文件
     * @param string $from_dir 存放文件的tmp路径
     * @param string $target_dir 最终保存文件的路径
     * @param array $value 媒体文件数据的数组
     * {
    zy_media_type:
    zy_media_filename:
    zy_media_filepath:
    zy_media_thumb_filename:
    zy_media_thumb_filepath:
    zy_media_title:
    zy_media_memo
    }
     * @return bool true|false 保存是否成功
     */
    public function move_media_files($from_dir,$target_dir,$value){

        //获取文件相关信息
        $filename=$value["zy_media_filename"];
        $thumb_filename=$value["zy_media_thumb_filename"];
        $media_type=$value["zy_media_type"];

        //非网络视频，需要移动媒体文件本身
        if($media_type!="zy_network_video"){

            //移动文件,需要判断文件是否完整
            if(is_file($from_dir."/".$filename)){
                if(!rename($from_dir."/".$filename,$target_dir."/".$filename)){
                    return false;
                }
            }

            //移动zip解压后的文件夹，3d文件和ppt文件才移动文件夹
            if($media_type=="zy_3d"||$media_type=="zy_ppt"){
                $zipdir=substr($filename, 0, strrpos($filename, "."));
                if(is_dir($from_dir."/".$zipdir)){
                    if(!rename($from_dir."/".$zipdir,$target_dir."/".$zipdir)){
                        return false;
                    }
                }
            }
        }

        //移动媒体文件的缩略图文件,图文混排文章的媒体文件缩略图默认为空
        if($thumb_filename!=""){

            //移动缩略图文件（如果媒体就是图片，那么媒体的缩略图也是此图片，会在上一步中文件就被移走）
            if(is_file($from_dir."/".$thumb_filename)){
                if(!rename($from_dir."/".$thumb_filename,$target_dir."/".$thumb_filename)){
                    return false;
                }
            }

            //移动缩略图的系统自动压缩文件
            $pathinfo=pathinfo($thumb_filename);

            //中文为自首的文件会是空
            $pathinfo["filename"]=substr($thumb_filename, 0,strrpos($thumb_filename, '.'));


            $zy_compress=$pathinfo["filename"].self::COMPRESS_SUFFIX.".".$pathinfo["extension"];
            if(is_file($from_dir."/".$zy_compress)){
                if(!rename($from_dir."/".$zy_compress,$target_dir."/".$zy_compress)){
                    return false;
                }
            }
        }

        //返回值
        return true;
    }

    /**
     * 创建目标文件夹,并且给私有变量路径赋值
     * @param int $post_id 文章或者幻灯片id
     * @return bool true|false 创建是否成功
     */
    public function get_targetdir($post_id){
        global $user_ID;
        $this->dir=wp_upload_dir();
        $this->from_dir=$this->dir["basedir"]."/tmp/".$user_ID;
        $this->target_dir=$this->dir["basedir"]."/".$post_id;

        //创建目标文件夹
        if(!is_dir($this->target_dir)){
            if(!mkdir($this->target_dir)){
                return false;
            }

            chmod($this->target_dir,0777);
        }

        return true;
    }

    /**
     * 新建的时候保存媒体数据
     * @param int $post_id 文章或者幻灯片id
     * @param string $new_medias 媒体文件数据json字符串
     * @return bool true|false 保存是否成功
     */
    public function new_save_medias($post_id,$new_medias){
        global $user_ID;

        //获取和创建目录
        if(!$this->get_targetdir($post_id)){
            return false;
        }

        if($new_medias!=""){

            //使用这个删除字符串中前台添加的反斜杠，需要删除反斜杠json_decode才能解析
            $new_medias=stripslashes($new_medias);
            $new_medias_array=json_decode($new_medias,true);

            //循环处理每一个媒体文件
            foreach($new_medias_array as $key=>$value){

                //移动文件
                if(!$this->move_media_files($this->from_dir,$this->target_dir,$value)){
                    return false;
                }

                //替换路径中tmp,json_decode函数遇到中文的时候会转成unicode编码，在写入数据库之前要设置成utf8
                $json_string=Zy_Util::array_to_string($value);
                $json_string=str_replace("tmp/$user_ID",$post_id,$json_string);

                if(!update_post_meta($post_id,$key,$json_string)){
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 修改的时候保存媒体数据
     * @param int $post_id 文章或者幻灯片id
     * @param string $new_medias 所有上传的媒体文件的json字符串
     * @return bool true|false 保存是否成功
     */
    public function edit_save_medias($post_id,$new_medias){
        global $user_ID;

        //获取和创建目录
        if(!$this->get_targetdir($post_id)){
            return false;
        }

        //获取出所有的meta
        $old_meta=get_post_meta($post_id);
        $old_medias=array();

        //取出是代表媒体文件的数据
        foreach($old_meta as $key=>$value){
            if(strpos($key,"zy_image_")!==false||strpos($key,"zy_location_")!==false||strpos($key,"zy_3d_")!==false||strpos($key,"zy_ppt_")!==false||strpos($key,"zy_network_")!==false){
                $old_medias[$key]=json_decode($value[0],true);
            }
        }

        if($new_medias!=""){

            //本次提交的媒体文件不为空的情况下，需要判断是否更改了
            $new_medias=stripslashes($new_medias);
            $new_medias_array=json_decode($new_medias,true);

            $new_medias_diffrent=array_diff_key($new_medias_array,$old_medias);//新的和旧的不同，以新的为基准
            $old_medias_diffrent=array_diff_key($old_medias,$new_medias_array);//旧的和旧的不同，以旧的为基准

            /*
             * 如果旧的文件和新的文件键不一样，那就是增加或者删除了与此同时可能修改了，需要进一步判断删除了的，新增了的
             * 这里就有多种情况
             * 1、新增了并且改了原来的
             * 2、删除了并且改了原来的
             * 3、纯粹新增
             * 4、纯粹删除
             * */
            if(count($new_medias_diffrent)||count($old_medias_diffrent)){

                //处理1、3情况,处理2的改了原来部分，以新的为基准
                foreach($new_medias_array as $key=>$value){

                    //old和new不同的时候，这里需要先判断键在旧的中是否存在，来判断该媒体文件是否是新增的
                    if(array_key_exists($key,$old_medias)){

                        //如果键存在，这里也分两种情况，一种是value值改动过，一种是没有改动过
                        $diffrent=array_diff_assoc($value,$old_medias[$key]);//取数组差集,看这个值是否改动过,函数不能比较嵌套数组

                        //改动过值，需要取文件，并且更新数据库
                        if(count($diffrent)){
                            if(!$this->move_media_files($this->from_dir,$this->target_dir,$value)){
                                return false;
                            }

                            //更新数据库记录
                            $json_string=Zy_Util::array_to_string($value);
                            $json_string=str_replace("tmp/$user_ID",$post_id,$json_string);

                            if(!update_post_meta($post_id,$key,$json_string)){
                                return false;
                            }
                        }else{

                            //没有改动过值，不需要更新数据库，只需要取同名文件
                            if(!$this->move_media_files($this->from_dir,$this->target_dir,$value)){
                                return false;
                            }
                        }
                    }else{

                        //不存在的话，代表是新增的，需要插入数据库记录，并且移动文件
                        if(!$this->move_media_files($this->from_dir,$this->target_dir,$value)){
                            return false;
                        }

                        //更新数据库记录
                        $json_string=Zy_Util::array_to_string($value);
                        $json_string=str_replace("tmp/$user_ID",$post_id,$json_string);

                        if(!update_post_meta($post_id,$key,$json_string)){
                            return false;
                        }
                    }
                }


                //处理4情况，处理2的删除部分，旧的中存在，但是新的中不存在的，需要删除
                foreach($old_medias as $key=>$value){
                    if(!array_key_exists($key,$new_medias_array)){

                        //在新的数组中不存在的，要删除数据库记录，并且删除文件
                        if(!delete_post_meta($post_id,$key)){
                            return false;
                        }

                        //删除文件和文件夹，这里网络文件不需要删除文件
                        $type=$value["zy_media_type"];
                        $filename=$value["zy_media_filename"];
                        if($type!="zy_network_video"){
                            if($type=="zy_3d"||$type=="zy_ppt"){

                                //删除解压的文件夹
                                $zipdir=substr($filename, 0, strrpos($filename, "."));
                                if(!Zy_Util::deldir($this->target_dir."/".$zipdir)){
                                    return false;
                                }

                            }

                            //删除文件本身
                            if(is_file($this->target_dir."/".$filename)){
                                if(!unlink($this->target_dir."/".$filename)){
                                    return false;
                                }
                            }

                        }

                        //删除文件的缩略图
                        $thumb_name=$value["zy_media_thumb_filename"];

                        //图文混排的媒体文件缩略图为空
                        if($thumb_name!=""){
                            if(is_file($this->target_dir."/".$thumb_name)){
                                if(!unlink($this->target_dir."/".$thumb_name)){
                                    return false;
                                }
                            }

                            //删除系统自动压缩的媒体图片（图片本身、媒体文件缩略图)
                            $thumb_path=pathinfo($thumb_name);
                            $thumb_path["filename"]=substr($thumb_name, 0,strrpos($thumb_name, '.'));
                            $zy_compress=$thumb_path["filename"].self::COMPRESS_SUFFIX.".".$thumb_path["extension"];
                            if(is_file($this->target_dir."/".$zy_compress)){
                                if(!unlink($this->target_dir."/".$zy_compress)){
                                    return false;
                                }
                            }
                        }

                    }
                }

            }else{

                //如果旧的和新的键一样，也可能修改了键的值，要进行比较，即使没有修改也要取同名文件
                foreach($new_medias_array as $key=>$value){
                    $diffrent=array_diff_assoc($value,$old_medias[$key]);//取数组差集,看这个值是否改动过,函数不能比较嵌套数组

                    if(count($diffrent)){

                        //改动过值，需要取文件，并且更新数据库
                        if(!$this->move_media_files($this->from_dir,$this->target_dir,$value)){
                            return false;
                        }

                        //更新数据库记录
                        $json_string=Zy_Util::array_to_string($value);
                        $json_string=str_replace("tmp/$user_ID",$post_id,$json_string);

                        if(!update_post_meta($post_id,$key,$json_string)){
                            return false;
                        }
                    }else{

                        //没有改动过值，不需要更新数据库，只需要取同名文件
                        if(!$this->move_media_files($this->from_dir,$this->target_dir,$value)){
                            return false;
                        }
                    }
                }
            }
        }else{

            //本次提交的媒体文件为空，那说明所有的媒体文件都被删除，则要删除原来的文件和数据库数据
            foreach($old_medias as $key=>$value){

                //删除数据库记录，并且删除文件
                if(!delete_post_meta($post_id,$key)){
                    return false;
                }

                //删除文件和文件夹，这里网络文件不需要删除文件
                $type=$value["zy_media_type"];
                $filename=$value["zy_media_filename"];
                if($type!="zy_network_video"){
                    if($type=="zy_3d"||$type=="zy_ppt"){

                        //删除解压的文件夹
                        $zipdir=substr($filename, 0, strrpos($filename, "."));
                        if(!Zy_Util::deldir($this->target_dir."/".$zipdir)){
                            return false;
                        }
                    }

                    //删除文件本身
                    if(is_file($this->target_dir."/".$filename)){
                        if(!unlink($this->target_dir."/".$filename)){
                            return false;
                        }
                    }

                }

                //删除文件的缩略图
                $thumb_name=$value["zy_media_thumb_filename"];

                //图文混排的媒体文件缩略图默认为空
                if($thumb_name!=""){
                    if(is_file($this->target_dir."/".$thumb_name)){
                        if(!unlink($this->target_dir."/".$thumb_name)){
                            return false;
                        }
                    }

                    //删除系统自动压缩的媒体图片（图片本身、媒体文件缩略图)
                    $thumb_path=pathinfo($thumb_name);
                    $thumb_path["filename"]=substr($thumb_name, 0,strrpos($thumb_name, '.'));
                    $zy_compress=$thumb_path["filename"].self::COMPRESS_SUFFIX.".".$thumb_path["extension"];
                    if(is_file($this->target_dir."/".$zy_compress)){
                        if(!unlink($this->target_dir."/".$zy_compress)){
                            return false;
                        }
                    }
                }

            }
        }

        //返回值
        return true;
    }

    /**
     * 新建时保存缩略图文件
     * @param int $post_id 文章或者幻灯片id
     * @param string $filename 缩略图文件名
     * @return bool true|false 保存是否成功
     */
    public function new_save_thumb($post_id,$filename){

        //获取和创建目录
        if(!$this->get_targetdir($post_id)){
            return false;
        }

        //移动文件
        if(is_file($this->from_dir."/".$filename)){
            if(!rename($this->from_dir."/".$filename,$this->target_dir."/".$filename)){
                return false;
            }
        }


        //获取系统压缩的缩略图的压缩图路径
        $pathinfo=pathinfo($filename);
        $pathinfo["filename"]=substr($filename, 0,strrpos($filename, '.'));
        $zy_thumb=$pathinfo["filename"].self::COMPRESS_SUFFIX.".".$pathinfo["extension"];

        if(is_file($this->from_dir."/".$zy_thumb)){
            if(!rename($this->from_dir."/".$zy_thumb,$this->target_dir."/".$zy_thumb)){
                return false;
            }
        }

        $filepath=$this->dir["baseurl"]."/".$post_id."/".$filename;
        $json='{"filename":"'.$filename.'","filepath":"'.$filepath.'"}';
        if(!update_post_meta($post_id,"zy_thumb",$json)){
            return false;
        }

        return true;
    }

    /**
     * 修改时保存缩略图文件
     * @param int $post_id 文章或者幻灯片id
     * @param string $filename 新缩略图文件名
     * @param string $old_filename 旧缩略图文件名
     * @return bool true|false 保存是否成功
     */
    public function edit_save_thumb($post_id,$filename,$old_filename){

        //获取和创建目录
        if(!$this->get_targetdir($post_id)){
            return false;
        }

        //如果缩略图文件的文件名改变了
        if($old_filename!=$filename){

            //删除原有文件
            if(is_file($this->target_dir."/".$old_filename)){
                if(!unlink($this->target_dir."/".$old_filename)){
                    return false;
                }
            }

            //获取系统压缩的缩略图的压缩图路径
            $pathinfo_old=pathinfo($old_filename);
            $pathinfo_old["filename"]=substr($old_filename, 0,strrpos($old_filename, '.'));
            $zy_compress_old=$pathinfo_old["filename"].self::COMPRESS_SUFFIX.".".$pathinfo_old["extension"];

            if(is_file($this->target_dir."/".$zy_compress_old)){
                if(!unlink($this->target_dir."/".$zy_compress_old)){
                    return false;
                }
            }


            //移动新缩略图文件
            if(is_file($this->from_dir."/".$filename)){
                if(!rename($this->from_dir."/".$filename,$this->target_dir."/".$filename)){
                    return false;
                }
            }

            //获取系统压缩的缩略图的压缩图路径
            $pathinfo=pathinfo($filename);
            $pathinfo["filename"]=substr($filename, 0,strrpos($filename, '.'));
            $zy_compress=$pathinfo["filename"].self::COMPRESS_SUFFIX.".".$pathinfo["extension"];

            if(is_file($this->from_dir."/".$zy_compress)){
                if(!rename($this->from_dir."/".$zy_compress,$this->target_dir."/".$zy_compress)){
                    return false;
                }
            }

            $filepath=$this->dir["baseurl"]."/".$post_id."/".$filename;
            $json='{"filename":"'.$filename.'","filepath":"'.$filepath.'"}';
            if(!update_post_meta($post_id,"zy_thumb",$json)){
                return false;
            }
        }else{

            //如果旧的和新的相同，也要去取同名文件
            if(is_file($this->from_dir."/".$filename)){
                if(!rename($this->from_dir."/".$filename,$this->target_dir."/".$filename)){
                    return false;
                }
            }

            //获取系统压缩的缩略图的压缩图路径
            $pathinfo=pathinfo($filename);
            $pathinfo["filename"]=substr($filename, 0,strrpos($filename, '.'));
            $zy_compress=$pathinfo["filename"].self::COMPRESS_SUFFIX.".".$pathinfo["extension"];

            if(is_file($this->from_dir."/".$zy_compress)){
                if(!rename($this->from_dir."/".$zy_compress,$this->target_dir."/".$zy_compress)){
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * 新建时保存背景文件
     * @param int $post_id 文章或幻灯片id
     * @param string $filename 背景文件名
     * @return bool true|false 保存是否成功
     */
    public function new_save_background($post_id,$filename){
        if(!empty($filename)){
            $pathinfo=pathinfo($filename);
            $filetype =$pathinfo["extension"];//获取后缀

            //获取和创建目录
            if(!$this->get_targetdir($post_id)){
                return false;
            }

            if(is_file($this->from_dir."/".$filename)){
                if(!rename($this->from_dir."/".$filename,$this->target_dir."/".$filename)){
                    return false;
                }
            }

            $filepath=$this->dir["baseurl"]."/".$post_id."/".$filename;
            $json='{"filename":"'.$filename.'","filepath":"'.$filepath.'","type":"'.$filetype.'"}';
            if(!update_post_meta($post_id,"zy_background",$json)){
                return false;
            }
        }

        return true;
    }

    /**
     * 修改时保存缩略图文件
     * @param int $post_id 文章或者幻灯片id
     * @param string $filename 新背景文件名
     * @param string $old_filename 旧背景文件名
     * @return bool true|false 保存是否成功
     */
    public function edit_save_background($post_id,$filename,$old_filename){

        //如果新传的不为空则是修改，如果为空则是直接删除了背景图
        if(!empty($filename)){
            $pathinfo=pathinfo($filename);
            $filetype =$pathinfo["extension"];//获取后缀

            //获取和创建目录
            if(!$this->get_targetdir($post_id)){
                return false;
            }


            //如果新传过来和原来的不一样
            if($old_filename!=$filename){

                //删除原有文件
                if(is_file($this->target_dir."/".$old_filename)){
                    if(!unlink($this->target_dir."/".$old_filename)){
                        return false;
                    }
                }

                //移动新文件
                if(is_file($this->from_dir."/".$filename)){
                    if(!rename($this->from_dir."/".$filename,$this->target_dir."/".$filename)){
                        return false;
                    }
                }

                $filepath=$this->dir["baseurl"]."/".$post_id."/".$filename;
                $json='{"filename":"'.$filename.'","filepath":"'.$filepath.'","type":"'.$filetype.'"}';
                if(!update_post_meta($post_id,"zy_background",$json)){
                    return false;
                }
            }else{

                //如果相同，也要去同名文件
                if(is_file($this->from_dir."/".$filename)){
                    if(!rename($this->from_dir."/".$filename,$this->target_dir."/".$filename)){
                        return false;
                    }
                }
            }
        }else{

            /*
             * 如果新传过来的为空，则需要删除原来的背景
             * 这里需要判断原来的背景是否存在，因为在幻灯片那里可能传一个不存在的值过来$_POST["zy_backgroun"]为null
             * */
            if(isset($old_filename)){
                //删除原有文件
                if(is_file($this->target_dir."/".$old_filename)){
                    if(!unlink($this->target_dir."/".$old_filename)){
                        return false;
                    }
                }

                //如果值为空，则直接删除meta
                if(!delete_post_meta($post_id,"zy_background")){
                    return false;
                }
            }
        }

        return true;
    }

    public function save_medias($post_id){

        $new_medias=$_POST["zy_medias"];

        if(isset($_POST["zy_old_medias"])){
            //判断是否为修改
            if(!$this->edit_save_medias($post_id,$new_medias)){
                return false;
            }
        }else{
            //判断为新增
            if(!$this->new_save_medias($post_id,$new_medias)){
                return false;
            }
        }
        //返回值
        return true;
    }
    /*
     * 存储缩略图数据函数
     * */
    function save_thumb($post_id){
        $filename=$_POST["zy_thumb"];

        //分为新建和修改两种类型
        if(isset($_POST["zy_old_thumb"])){
            $old_filename=$_POST["zy_old_thumb"];
            //如果是修改了文件
            if(!$this->edit_save_thumb($post_id,$filename,$old_filename)){
                return false;
            }
        }else{
            if(!$this->new_save_thumb($post_id,$filename)){
                return false;
            }
        }

        //返回值,让
        return true;
    }
    /*
     * 存储背景图数据函数
     * */
    function save_background($post_id){
        $filename=$_POST["zy_background"];

        //分为新建和修改两种类型
        if(isset($_POST["zy_old_background"])){
            $old_filename=$_POST["zy_old_background"];
            //如果是修改了文件
            if(!$this->edit_save_background($post_id,$filename,$old_filename)){
                return false;
            }
        }else{
            if(!$this->new_save_background($post_id,$filename)){
                return false;
            }
        }

        //返回值,让
        return true;
    }

    /*
     * 保存自定义数据,所有的数据在一个函数保存
     * */
    function zy_data_save( $post_id ) {
        global $wpdb;
        $post=get_post($post_id);

        /*
         * 需要判断是图文混排还是幻灯片，因为幻灯片的wp_insert_post也会出发publish_post
         * */
        if(strpos($post->post_mime_type,"zyslide")===false&&isset($_POST["zy_thumb"])){

            //设置页面编码
            header("content-type:text/html;charset=utf-8");

            /*存储媒体文件数据*/
            if(!$this->save_medias($post_id)){
                //提示错误
                die("保存媒体数据出错，请联系开发人员");
            }

            /*存储缩略图数据*/
            if(!$this->save_thumb($post_id)){
                //提示错误
                die("保存缩略图数据出错，请联系开发人员");
            }
            /*存储背景数据*/
            if(!$this->save_background($post_id)){
                //提示错误
                die("保存背景数据出错，请联系开发人员");
            }

            //保存打包数据
            $tablename=$wpdb->prefix."pack_ids";
            if(count($wpdb->get_col("SELECT post_id FROM $tablename WHERE post_id=$post_id"))){
                //存在的情况下，修改
                if($wpdb->update($wpdb->prefix."pack_ids",array("pack_lock"=>0,"pack_time"=>NULL),array("post_id"=>$post_id),array("%d","%s"))===false){
                    die("保存打包数据出错，请联系开发人员");
                }
            }else{
                //不存在的情况下新增
                if(!$wpdb->insert($wpdb->prefix."pack_ids",array("post_id"=>$post_id),array("%d"))){
                    die("保存打包数据出错，请联系开发人员");
                }
            }

            //更新发布时间
            $post_date=current_time('mysql');
            $wpdb->update($wpdb->posts, array("post_date"=>$post_date,"post_date_gmt"=>date("Y-m-d H:i:s")), array("ID"=>$post_id));
        }
    }
}