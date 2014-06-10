<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ty
 * Date: 13-6-24
 * Time: 下午12:15
 * 音乐类，主要包括添加菜单函数、保存、删除、菜单页面等
 */
include("zy_common_class.php");
/**
 *
 */
class zy_music_class
{
    /**
     * 初始化自定义类型
     * */
    public function zy_music_init() {
        $labels = array(
            'name' => '音乐',
            'singular_name' => '音乐',
            'add_new' => '增加',
            'add_new_item' => '增加音乐',
            'edit_item' => '修改音乐',
            'new_item' => '增加音乐',
            'all_items' => '所有音乐',
            'view_item' => '查看音乐',
            'search_items' => '搜索音乐',
            'not_found' =>  '没有音乐',
            'not_found_in_trash' => '回收站没有音乐',
            'parent_item_colon' => '',
            'menu_name' => '音乐'
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array( 'slug' => 'zy_music' ),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => null,
            'taxonomies'=>array("category"),
            'supports' => array( 'title',"author"),
            'register_meta_box_cb'=>array("zy_music_class",'zy_add_music_upload')
        );

        register_post_type( 'zy_music', $args );
    }

    /**
     * 自定义的字段的box展示代码,将音乐文件名保存在post_content字段中
     * @param int $post_id 音乐id
     * */
    public function zy_add_music_html($post_id){
        echo "<div id='zy_upload_music'>";
        echo "<input id='zy_uploa_music_btn' class='zy_upload_btn' type='button' value='上传'><span style='font-style: italic;color:#CECFCF'>只可上传mp3文件</span>";
        $old_music=get_post($post_id);
        if($name=$old_music->post_content){
            echo "<input type='hidden' value='$name' name='post_old_content'>";
            echo "<input type='hidden' value='$name' id='zy_music_name' name='post_content'>";
            echo "<div id='zy_music_list'>$name</div></div>";
        }else{
            echo "<input type='hidden' id='zy_music_name' name='post_content'>";
            echo "<div id='zy_music_list'></div></div>";
        }
    }

    /**
     * 添加自定义的box
     * */
    public function zy_add_music_upload(){
        add_meta_box( "zy_music_upload", "上传音乐",array("zy_music_class","zy_add_music_html"), "zy_music", "normal");
    }

    /**
     * 更新消息提示函数
     * @param array $messages 消息数组
     * @return array $messages 消息数组
     * */
    public function zy_music_updated_messages( $messages ) {
        global $post, $post_ID;

        $messages['zy_music'] = array(
            0 => '', // Unused. Messages start at index 1.
            1 => sprintf( __('音乐保存成功. <a href="%s">查看所有音乐</a>', 'your_text_domain'), esc_url( get_permalink($post_ID) ) ),
            2 => __('Custom field updated.', 'your_text_domain'),
            3 => __('Custom field deleted.', 'your_text_domain'),
            4 => __('音乐保存成功.', 'your_text_domain'),
            /* translators: %s: date and time of the revision */
            5 => isset($_GET['revision']) ? sprintf( __('音乐 restored to revision from %s', 'your_text_domain'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
            6 => sprintf( __('音乐 published. <a href="%s">View book</a>', 'your_text_domain'), esc_url( get_permalink($post_ID) ) ),
            7 => __('音乐保存成功.', 'your_text_domain'),
            8 => sprintf( __('音乐 submitted. <a target="_blank" href="%s">Preview book</a>', 'your_text_domain'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
            9 => sprintf( __('音乐 scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview book</a>', 'your_text_domain'),
                // translators: Publish box date format, see http://php.net/date
                date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
            10 => sprintf( __('音乐 draft updated. <a target="_blank" href="%s">Preview book</a>', 'your_text_domain'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
        );

        return $messages;
    }

    /**
     * 删除时要删除掉音乐文件
     * @param  int $post_id 音乐id
     * @return bool true|false 删除是否成功
     */
    public function zy_music_delete($post_id){
        $targetDir=wp_upload_dir();
        if(!zy_common_class::zy_deldir($targetDir["basedir"]."/".$post_id)){
            header("content-type:text/html; charset=utf-8");
            die("删除音乐文件失败，请将音乐id".$post_id."告诉开发人员！");
        }


        return true;
    }

    /**
     * 保存音乐文件
     * @param int $post_id 音乐id
     * @return bool true|false 保存是否成功
     */
    public function zy_music_save($post_id){
        $filename=$_POST["post_content"];//获取文件名
        $dir=wp_upload_dir();
        global $user_ID;
        $from_dir=$dir["basedir"]."/tmp/".$user_ID;
        $target_dir=$dir["basedir"]."/".$post_id;

        //创建目标文件夹
        if(!is_dir($target_dir)){
            if(!mkdir($target_dir)){
                return false;
            }
        }

        if(isset($_POST["post_old_content"])){
            $old_filename=$_POST["post_old_content"];
            if($old_filename==$filename){

                //如果相同，也要移动同名文件
                if(is_file($from_dir."/".$filename)){
                    if(!rename($from_dir."/".$filename,$target_dir."/".$filename)){
                        header("content-type:text/html; charset=utf-8");
                        die("保存音乐文件失败，请将音乐id".$post_id."告诉开发人员！");
                    }
                    //更新数据库内容,可以不更新，后面ajax输出的时候加路径前缀
                }

                /*update_post_meta如果设置相同的值会返回false，但是可以直接使用，
                因为filesize读出来的是数字，而数据库中是字符串，两者永远不相同*/
                if(update_post_meta($post_id,"zy_music_size",filesize($target_dir."/".$filename))===false){
                    header("content-type:text/html; charset=utf-8");
                    die("读取音乐文件大小出错，请将音乐id".$post_id."告诉开发人员！");
                }
            }else{

                //删除文件
                if(is_file($target_dir."/".$old_filename)){
                    if(!unlink($target_dir."/".$old_filename)){
                        header("content-type:text/html; charset=utf-8");
                        die("保存音乐文件失败，请将音乐id".$post_id."告诉开发人员！");
                    }
                }

                //移动文件
                if(is_file($from_dir."/".$filename)){
                    if(!rename($from_dir."/".$filename,$target_dir."/".$filename)){
                        header("content-type:text/html; charset=utf-8");
                        die("保存音乐文件失败，请将音乐id".$post_id."告诉开发人员！");
                    }
                    //更新数据库内容,可以不更新，后面输出的时候加路径前缀
                }

                if(update_post_meta($post_id,"zy_music_size",filesize($target_dir."/".$filename))===false){
                    header("content-type:text/html; charset=utf-8");
                    die("读取音乐文件大小出错，请将音乐id".$post_id."告诉开发人员！");
                }
            }
        }else{

            //移动文件,需要判断文件是否存在
            if(is_file($from_dir."/".$filename)){
                if(!rename($from_dir."/".$filename,$target_dir."/".$filename)){
                    header("content-type:text/html; charset=utf-8");
                    die("保存音乐文件失败，请将音乐id".$post_id."告诉开发人员！");
                }
                //更新数据库内容,可以不更新，后面ajax输出的时候加路径前缀
            }

            if(update_post_meta($post_id,"zy_music_size",filesize($target_dir."/".$filename))===false){
                header("content-type:text/html; charset=utf-8");
                die("读取音乐文件大小出错，请将音乐id".$post_id."告诉开发人员！");
            }
        }
        return true;
    }
}
