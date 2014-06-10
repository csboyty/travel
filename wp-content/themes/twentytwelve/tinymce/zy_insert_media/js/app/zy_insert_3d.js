/**
 * Created by JetBrains PhpStorm.
 * User: ty
 * Date: 13-6-13
 * Time: 下午4:41
 * tinymce插件上传3d文件js
 */

$(document).ready(function(){

    var zy_media_type="zy_3d"; //页面能绑定的媒体类型


    //上传视频文件部分代码
    zy_insert_common.zy_create_media_uploader("zip");

    //设置原来的内容
    zy_insert_common.zy_set_old_content(zy_media_type);

    //控制步骤是否可以点击代码
    $("#zy_header a").click(function(){
        zy_insert_common.zy_control_step($(this),zy_media_type);
        return false;
    });

    //取消代码
    $("#zy_resetBtn").click(function(){
        tinyMCEPopup.close();
    });

    //插入代码
    $("#zy_submitBtn").click(function(){
        zy_insert_common.zy_insert_media(zy_media_type);
    });
});

