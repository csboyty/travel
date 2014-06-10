/**
 * Created by JetBrains PhpStorm.
 * User: ty
 * Date: 13-6-13
 * Time: 上午8:58
 * 幻灯片设置本地视频文件属性页面js
 */
$(document).ready(function(){

    var zy_media_id=zy_set_common.zy_get_media_id();

    //设置原来的内容
    zy_set_common.zy_set_old_content(zy_media_id,"zy_location_video");

    //上传缩略图部分代码
    zy_set_common.zy_create_thumb_uploader(zy_media_id);

    //上传缩略图部分
    zy_set_common.zy_create_media_uploader("mp4",zy_media_id);

    //记录标题和描述的输入
    $("#zy_media_title").blur(function(){

        //防止后台json_decode出错，将双引号改成单引号
        zy_set_common.zy_get_private_uploaded()[zy_media_id]["zy_media_title"]=$(this).val().replace(/["]/g,"'");
    });
    $("#zy_media_memo").blur(function(){

        //防止后台json_decode出错，将双引号改成单引号
        zy_set_common.zy_get_private_uploaded()[zy_media_id]["zy_media_memo"]=$(this).val().replace(/["]/g,"'");
    });
});
