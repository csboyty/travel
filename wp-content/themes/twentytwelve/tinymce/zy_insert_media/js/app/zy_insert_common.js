/**
 * Created by JetBrains PhpStorm.
 * User: ty
 * Date: 13-6-13
 * Time: 下午4:50
 * tinymce插件上传媒体文件公共js
 * 数据字典
 * zy_get_random 产生随机数
 * zy_set_media_id 设置私有变量media_id
 * zy_set_old_content 设置原始值以供修改
 * zy_control_step 控制tab项点击
 * zy_create_media_uploader 上传媒体文件句柄
 */
var zy_insert_common =(function(){

    //已经上的媒体文件，申明成私有变量，zy_uploaded_medias为全局变量
    var parent=tinyMCEPopup.getWin();
    var uploaded_medias=parent.zy_uploaded_medias;
    var zy_media_id=tinyMCEPopup.getWindowArg("zy_media_id"); //声明称私有方法

    //获取选中的元素
    var editor=tinymce.EditorManager.activeEditor;
    var img=editor.selection.getNode();
    return {

        /*
         * 产生随机数函数，根据当前日期，并且加上前缀，尾部4位随机数
         * */
        "zy_get_random":function(){
            var date=new Date();
            var retValue="";
            var mo=(date.getMonth()+1)<10?('0'+''+(date.getMonth()+1)):date.getMonth()+1;
            var dd=date.getDate()<10?('0'+''+date.getDate()):date.getDate();
            var hh=date.getHours()<10?('0'+''+date.getHours()):date.getHours();
            var mi=date.getMinutes()<10?('0'+''+date.getMinutes()):date.getMinutes();
            var ss=date.getSeconds()<10?('0'+''+date.getSeconds()):date.getSeconds();
            retValue=date.getFullYear()+''+mo+''+dd+''+hh+''+mi+''+ss+'';
            for(var j=0;j<4;j++){
                retValue+=''+parseInt(10*Math.random())+'';
            }
            if(arguments.length==1){
                return arguments[0]+''+retValue;
            }
            else
                return retValue;
        },

        /*
        *设置原始内容函数
        * @params media_type 页面对应的上传媒体类型
        *
        * */
        "zy_set_old_content":function(media_type){

            /*
            * 如果传过来的已绑定文件的媒体类型和页面可以绑定的媒体类型相同
            * 则是修改那么需要设置原来的内容以供修改
            * */
            if(zy_media_id!==null){

                if(uploaded_medias[zy_media_id]["zy_media_type"]==media_type){

                    if(media_type=="zy_network_video"){
                        $("#zy_file_info").text(uploaded_medias[zy_media_id]["zy_media_filename"]);
                        $("#zy_network_input").val(uploaded_medias[zy_media_id]["zy_media_filename"]);

                        //隐藏输入框
                        $("#zy_file_info_div").removeClass("zy_hidden");
                        $("#zy_change_div").addClass("zy_hidden");
                    }else{
                        $("#zy_file_info").html(uploaded_medias[zy_media_id]["zy_media_filename"])
                            .attr("data-zy-media-url",uploaded_medias[zy_media_id]["zy_media_filepath"]).removeClass("zy_hidden");
                    }

                    $("#zy_media_title").val(uploaded_medias[zy_media_id]["zy_media_title"]);
                    $("#zy_media_memo").text(uploaded_medias[zy_media_id]["zy_media_memo"]);

                    return ; //将控制权交给页面，不再执行下面的代码。
                }
            }

            //如果不存在media_id,生成一个新的值
            if(media_type=="zy_3d"){
                zy_media_id=this.zy_get_random("zy_3d_");
            }else if(media_type=="zy_ppt"){
                zy_media_id=this.zy_get_random("zy_ppt_");
            }else if(media_type=="zy_location_video"){
                zy_media_id=this.zy_get_random("zy_location_");
            }else{
                zy_media_id=this.zy_get_random("zy_network_");
            }
        },

        /*
        * 控制步骤点击的公共模块，控制显示
        * params target a标签本身
        * */
        "zy_control_step":function(target,zy_media_type){

            var target_article=target.attr("href");
            if(target_article!="#zy_content"){

                //如果是预览面板，先要判断是否可以点击，
                if($("#zy_file_info").html()!=""&&$("#zy_file_info").html().indexOf("%")==-1){
                    var zy_media_url=$('#zy_file_info').data('zy-media-url');

                    //设置预览内容
                    if(zy_media_type=="zy_location_video"){
                        $("#zy_preview").html("<video class='zy_preview_video' controls><source src='"+zy_media_url+"' type='video/mp4' /></video>");
                    }else if(zy_media_type=="zy_network_video"){
                        zy_media_url=$("#zy_file_info").text(); //重新获取地址
                        $("#zy_preview").html(zy_media_url);
                    }else if(zy_media_type=="zy_3d"){
                        $("#zy_preview").html("预览内容");
                    }else{
                        $("#zy_preview").html("<iframe src='"+zy_media_url+"/index.html'></iframe>");
                    }


                    //显示插入按钮
                    $("#zy_submitBtn").removeClass("zy_hidden");
                }else{
                	
                    //如果没有填写，提示错误信息
                    alert("没有上传媒体文件，请上传后再预览!");
                    return false;
                }
            }

            //控制效果
            $("article").addClass("zy_hidden");
            $(target_article).removeClass("zy_hidden");
            if(target_article=="#zy_content"){
                //如果是第一个，则需要清空第二面板的所有内容
                $("#zy_preview").html("");
                $("#zy_submitBtn").addClass("zy_hidden");
            }
            $("#zy_header a").removeClass("zy_active");
            target.addClass("zy_active");
        },

        /*
         * 媒体文件插入函数
         * params zy_media_id 媒体文件的id  zy_media_type 页面能够绑定的媒体文件类型
         * */
        "zy_insert_media":function(zy_media_type){

            var filepath=$("#zy_file_info").data("zy-media-url");
            if(zy_media_type=="zy_network_video"){
                filepath=$("#zy_file_info").text().replace(/["]/g,"'");
            }

            //添加一个对象到已上传的媒体文件对象中
            uploaded_medias[zy_media_id]={
                "zy_media_type":zy_media_type,
                "zy_media_filepath":filepath,

                //防止后台json_decode出错，将双引号改成单引号
                "zy_media_title":$("#zy_media_title").val().replace(/["]/g,"'"),

                //防止后台json_decode出错，将双引号改成单引号
                "zy_media_memo":$("#zy_media_memo").val().replace(/["]/g,"'"),
                "zy_media_thumb_filename":"",
                "zy_media_thumb_filepath":"",

                //网络视频的名称和地址是一个值
                "zy_media_filename":zy_media_type=="zy_network_video"?filepath:$("#zy_file_info").data("zy-unique-filename")
            };

            //设置img的属性，此img为tinymce中选中的元素
            img.setAttribute("data-zy-media-id",zy_media_id);
            //img.setAttribute("data-zy-media-type",zy_media_type);

            if(zy_media_type=="zy_location_video"){
                img.parentNode.className="videoslide";
            }else if(zy_media_type=="zy_network_video"){
                img.parentNode.className="webslide";
            }else if(zy_media_type=="zy_3d"){
                img.parentNode.className="_3dslide";
            }else{
                img.parentNode.className="pptslide";
            }

            //关闭窗口
            tinyMCEPopup.close();
        },


        /*
         * 上传媒体文件函数
         * params filters 媒体文件格式筛选器
         * */
        "zy_create_media_uploader":function(filters){
            var uploader_video=new plupload.Uploader({
                runtimes:"html5",
                multi_selection:false,
                max_file_size:parent.zy_config.zy_media_upload_size,
                browse_button:"zy_upload_media_button",
                container:"zy_left_top",
                //flash_swf_url: 'js/lib/plupload.flash.swf',
                url: parent.ajaxurl,
                filters : [
                    {title : "Media files", extensions : filters}
                ],
                multipart_params: {
                    action: "uploadfile",
                    user_id:parent.zy_config.zy_user_id,
                    post_id:jQuery("#post_ID",parent.document).val()
                }
            });

            //初始化
            uploader_video.init();

            //文件添加事件
            uploader_video.bind("FilesAdded",function(up,files){
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
                    $("#zy_file_info").removeClass("zy_hidden");

                    //显示按钮
                    $("#zy_upload_media_button").addClass("zy_hidden");
                }
            });

            //文件上传进度条事件
            uploader_video.bind("UploadProgress",function(up,file){
                $("#zy_file_info").html(file.percent + '%'+file.name);
            });

            //出错事件
            uploader_video.bind("Error",function(up,err){
                up.refresh();

                //设置页面展示
                $("#zy_file_info").html("上传出错");
                $("#zy_upload_media_button").removeClass("zy_hidden");
            });

            //上传完毕事件
            uploader_video.bind("FileUploaded",function(up,file,res){
                var response=JSON.parse(res.response);
                if(response.success){
                    $("#zy_file_info").html(file.name).attr(
                        {
                            "data-zy-media-url":response.data.url,
                            "data-zy-unique-filename":response.data.filename,
                            "title":file.name
                         }
                    );
                    $("#zy_upload_media_button").removeClass("zy_hidden");
                }else{
                    alert(response.data.message);
                }

            });
        }
    }
})();
