/**
 * Created by JetBrains PhpStorm.
 * User: ty
 * Date: 13-6-24
 * Time: 下午1:21
 *上传音乐页面js
 */
var zy_music=(function($){
    return {
        create_uploader:function(){
            //上传句柄
            var uploader_music=new plupload.Uploader({
                runtimes:"html5",
                multi_selection:false,
                max_file_size:zy_config.zy_mp3_upload_size,
                browse_button:"zy_upload_music_btn",
                container:"zy_upload_music",
                //flash_swf_url: '../wp-includes/js/plupload/plupload.flash.swf',
                url: ajaxurl,
                filters : [
                    {title : "Audio files", extensions : "mp3"}
                ],
                multipart_params: {
                    action: "uploadfile",
                    user_id:zy_config.zy_user_id,
                    file_type:"zy_music"
                }
            });

            //初始化
            uploader_music.init();

            //文件添加事件
            uploader_music.bind("FilesAdded",function(up,files){
                var filename=files[0].name;
                var lastIndex=filename.lastIndexOf(".");
                filename=filename.substring(0,lastIndex);

                //只含有汉字、数字、字母、下划线不能以下划线开头和结尾
                var reg=/^(?!_)(?!.*?_$)[a-zA-Z0-9_\u4e00-\u9fa5]+$/;
                if(!reg.test(filename)){
                    alert("文件名必须是数字下划线汉字字母,且不能以下划线开头。");

                    //删除文件
                    up.removeFile(files[0]);
                    return false;
                }else{
                    up.start(); //开始上传
                }
            });

            //文件上传进度条事件
            uploader_music.bind("UploadProgress",function(up,file){
                $("#zy_music_list").html(file.name+"----"+file.percent + "%");
            });

            //出错事件
            uploader_music.bind("Error",function(up,err){
                alert(err.message);
                up.refresh();
            });

            //上传完毕事件
            uploader_music.bind("FileUploaded",function(up,file,res){
                var response=JSON.parse(res.response);
                if(response.success){
                    $("#zy_music_list").html(file.name+"----上传成功");
                    $("#zy_music_name").val(file.name);
                }else{
                    alert(response.data.message);
                }
            });
        }
    };
})(jQuery);
jQuery(document).ready(function($){

    zy_music.create_uploader();

    //禁用下面的点击
    $(".children input").attr("disabled","disabled");

    //提交
    $("#publish").click(function(){
        var music=$("#zy_music_list").html();
        if(((music.indexOf("%")!=-1&&music.indexOf("100%")==-1)||music=="")||
            $("#title").val()==""||$("#categorychecklist input:checked").length==0){
            alert("请填写标题、上传文件、选择类别");
            return false;
        }
    });
});
