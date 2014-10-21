/**
 * Created by JetBrains PhpStorm.
 * User: ty
 * Date: 13-6-13
 * Time: 上午8:58
 * 幻灯片设置网络视频文件属性页面js
 */
$(document).ready(function(){

   var zy_media_id=zy_set_common.zy_get_media_id();


    //设置原来的内容
    zy_set_common.zy_set_old_content(zy_media_id,"zy_network_video");

    //输入视频文件控制部分
    $("#zy_network_input_ok").click(function(){
        if($("#zy_network_input").val().trim().match(/^<iframe/)!=null){
            $("#zy_network_input").removeClass("zy_input_invalid");

            //防止后台json_decode出错，将双引号改成单引号
            var filename=$("#zy_network_input").val().replace(/["]/g,"'");

            //设置列表中的值
            $("#zy_uploaded_medias_ol a[data-zy-media-id='"+zy_media_id+"']",parent.document).find(".zy_media_filename").text(filename);

            //设置uploaded_medias的值
            zy_set_common.zy_get_private_uploaded()[zy_media_id]["zy_media_filename"]=filename;
            zy_set_common.zy_get_private_uploaded()[zy_media_id]["zy_media_filepath"]=filename;

            //设置file_info的值
            $("#zy_file_info").text(filename);
            $("#zy_file_info_div").removeClass("zy_hidden");
            $("#zy_change_div").addClass("zy_hidden");
        }else{
            $("#zy_network_input").removeClass("zy_input_invalid");
        }
    });

    //上传罗略图部分代码
    zy_set_common.zy_create_thumb_uploader(zy_media_id);

    //点击更换按钮
    $("#zy_network_change").click(function(){
        $("#zy_change_div").removeClass("zy_hidden");
        $("#zy_file_info_div").addClass("zy_hidden");
    });

    /*-----------------控制标题和描述的输入===============*/
    $("#zy_media_title").blur(function(){

        //防止后台json_decode出错，将双引号改成单引号
        zy_set_common.zy_get_private_uploaded()[zy_media_id]["zy_media_title"]=$(this).val().replace(/["]/g,"'");
    });
    $("#zy_media_memo").blur(function(){

        //防止后台json_decode出错，将双引号改成单引号
        zy_set_common.zy_get_private_uploaded()[zy_media_id]["zy_media_memo"]=$(this).val().replace(/["]/g,"'");
    });
});
