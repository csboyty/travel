/**
 * Created by JetBrains PhpStorm.
 * User: ty
 * Date: 13-6-13
 * Time: 上午8:58
 * tinymce插件上传网络视频文件js
 */
$(document).ready(function(){

    var zy_media_type="zy_network_video";

    //输入视频文件控制部分
    $("#zy_network_input_ok").click(function(){
        if($("#zy_network_input").val().trim().match(/^<iframe/)!=null){
            $("#zy_network_input").removeClass("zy_input_invalid");
            $("#zy_file_info").text($("#zy_network_input").val());
            $("#zy_change_div").addClass("zy_hidden");
            $("#zy_file_info_div").removeClass("zy_hidden");
        }else{
            //alert("请粘贴含有iframe元素的通用代码!");
            $("#zy_network_input").addClass("zy_input_invalid");
        }

    });
    $("#zy_network_change").click(function(){
        $("#zy_change_div").removeClass("zy_hidden");
        $("#zy_file_info_div").addClass("zy_hidden");
    });

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
