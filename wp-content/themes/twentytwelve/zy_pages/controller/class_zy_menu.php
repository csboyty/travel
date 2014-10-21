<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ty
 * Date: 14-10-5
 * Time: 上午11:52
 * To change this template use File | Settings | File Templates.
 */
//include ("class_zy_util.php");
class Zy_Menu {
    public function add_all_menu(){
        //添加文章子菜单“幻灯片”
        add_posts_page("幻灯片","幻灯片",'publish_posts','zy_slide_menu',array($this,'slide_menu_page'));
        //添加设置子菜单“打包数据”
        add_options_page("打包数据","打包数据","manage_options","zy_pack_menu",array($this,'pack_menu_page'));
    }
    public function slide_menu_page(){
        include(get_template_directory()."/zy_pages/view/slide.php");
    }
    public function pack_menu_page(){
        global $wpdb;

        echo "<br><br><br>正在打包中......<br><br><br>";

        $tablename=$wpdb->prefix."pack_ids";

        $zy_packing_ids=$wpdb->get_col("SELECT post_id FROM $tablename AS i,$wpdb->posts AS p
        WHERE i.pack_lock=0 AND p.ID=i.post_id AND p.post_status!='trash'");//需要发送的数组文章id

        if(count($zy_packing_ids)){

            $url=get_site_url()."/bundle-app/makeBundle";
            $zy_http_result=false;
            $zy_pack_time="";
            $ids=implode(",",$zy_packing_ids);//组成字符串

            //更改数据库后，发送到打包程序
            for($i=0;$i<3;$i++){
                if(Zy_Util::http_send($ids,$url)){
                    $zy_http_result=true;

                    $zy_pack_time=time();//记录时间，从1970到现在的秒数

                    break;//跳出循环
                }
            }

            //设置显示值和是否锁定id
            if($zy_http_result){

                //将时间写入到数据库中
                $wpdb->query("UPDATE $tablename SET pack_time=$zy_pack_time WHERE post_id IN ($ids)");



                //显示成功信息
                echo "文章".$ids."打包数据成功，请选择其他操作。";

            }else{
                //显示错误信息
                echo "打包数据出错，本次打包未成功！请稍后再打包。";
            }

        }else{
            echo "没有新数据可以打包，请选择其他操作!";
        }
    }
}