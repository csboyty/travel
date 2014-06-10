/**
 * User: ty
 * Date: 13-6-13
 * 幻灯片设置媒体文件页面公用js
 * 数据字典
 * zy_get_media_id 获取当前媒体的id
 * zy_get_private_uploaded 获取私有的uploaded_medias
 * zy_get_compress_img  获取压缩的图片
 * zy_set_old_content 进入页面后设置原来的值
 * zy_create_media_uploader  媒体文件上传句柄
 * zy_create_thumb_uploader  上传缩略图句柄
 */
var zy_set_common = (function(){

    var parent=window.parent;
    var uploaded_medias=parent.zy_uploaded_medias; //已经上传了的媒体文件

    return {
        /*
         * 获取当前媒体文件的id
         * return 媒体文件id
         * */
        "zy_get_media_id":function(){
            var url=location.href;
            var pos=url.indexOf("?"); //获取参数，因为media_id将通过参数传递过来
            var zy_media_id=url.substr(pos+1,url.length-1);

            return zy_media_id;
        },

        /*
         * 获取压缩后的图片
         * params imgurl 图片的路径
         * return  压缩图片路径
         * */
        "zy_get_compress_img":function(imgurl){
            var img_ext=imgurl.substring(imgurl.lastIndexOf("."),imgurl.length);
            var img_src_compress=imgurl.substring(0,imgurl.lastIndexOf("."))+parent.zy_config.zy_compress_suffix+img_ext;

            return img_src_compress;
        },

        /*
        * 获取私有变量uploaded_medias
        * return uploaded_medias
        * */
        "zy_get_private_uploaded":function(){

            return uploaded_medias;
        },

        /*
         * 打开设置页面时，设置原来的内容
         * params zy_media_id 媒体文件的id
         * */
        "zy_set_old_content":function(zy_media_id,zy_media_type){

            if(zy_media_type!="zy_image"){
                if(zy_media_type=="zy_network_video"){

                    //网络视频设置不一样
                    if(uploaded_medias[zy_media_id]["zy_media_filename"]){
                        $("#zy_network_input").val(uploaded_medias[zy_media_id]["zy_media_filename"]);
                        $("#zy_file_info").text($("#zy_network_input").val());
                        $("#zy_file_info_div").removeClass("zy_hidden");
                        $("#zy_change_div").addClass("zy_hidden");
                    }
                }else{
                    $("#zy_file_info").html(uploaded_medias[zy_media_id]["zy_media_filename"]);
                }
            }

            // 设置缩略图
            if(uploaded_medias[zy_media_id]["zy_media_thumb_filepath"]){

                //设置缩略图,显示压缩后的图片
                $("#zy_media_thumb").attr("src",this.zy_get_compress_img(uploaded_medias[zy_media_id]["zy_media_thumb_filepath"]));
            }

            // 设置标题和描述
            if(uploaded_medias[zy_media_id]["zy_media_title"]){
                $("#zy_media_title").val(uploaded_medias[zy_media_id]["zy_media_title"]);
            }
            if(uploaded_medias[zy_media_id]["zy_media_memo"]){
                $("#zy_media_memo").text(uploaded_medias[zy_media_id]["zy_media_memo"]);

            }
        },

        /*
         * 媒体设置时上传媒体
         * params filters 媒体文件格式筛选器，zy_media_id 媒体文件id
         * */
        "zy_create_media_uploader":function(filters,zy_media_id){
            var uploader_video=new plupload.Uploader({
                runtimes:"html5",
                multi_selection:false,
                max_file_size:parent.zy_config.zy_media_upload_size,
                browse_button:"zy_upload_media_button",
                container:"zy_left_top",
                //flash_swf_url: '../js/lib/plupload.flash.swf',
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
                }else{
                    up.start(); //开始上传

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
                    //设置uploaded_medias的值
                    uploaded_medias[zy_media_id]["zy_media_filename"]=response.data.filename;
                    uploaded_medias[zy_media_id]["zy_media_filepath"]=response.data.url;

                    //设置列表中的值
                    $("#zy_uploaded_medias_ol a[data-zy-media-id='"+zy_media_id+"']",parent.document).find(".zy_media_filename").text(file.name);

                    //$(".zy_file_info").html(file.name).attr("data-zy-media-url",response.data.url);
                    $("#zy_file_info").html(file.name);
                    $("#zy_upload_media_button").removeClass("zy_hidden");
                }else{
                    alert(response.data.message);
                }

            });
        },

        /*
         * 媒体设置时上传缩略图
         * params zy_media_id 媒体文件的id
         * */
        "zy_create_thumb_uploader":function(zy_media_id){
            var uploader_thumb=new plupload.Uploader({
                runtimes:"html5",
                multi_selection:false,
                max_file_size:parent.zy_config.zy_img_upload_size,
                browse_button:"zy_upload_thumb_button",
                container:"zy_left_bottom",
                //flash_swf_url: '../js/lib/plupload.flash.swf',
                url: parent.ajaxurl,//parent在每个js里面都有定义
                filters : [
                    {title : "Image files", extensions : "jpg,jpeg,png"}
                ],
                multipart_params: { action: "uploadfile",
                    user_id:parent.zy_config.zy_user_id,
                    file_type:"zy_content_img",//代表图片需要显示到内容中
                    post_id:jQuery("#post_ID",parent.document).val()
                }
            });

            //初始化
            uploader_thumb.init();

            //文件添加事件
            uploader_thumb.bind("FilesAdded",function(up,files){
                var filename=files[0].name;
                var lastIndex=filename.lastIndexOf(".");
                filename=filename.substring(0,lastIndex);
                //只含有汉字、数字、字母、下划线不能以下划线开头和结尾
                var reg=/^(?!_)(?!.*?_$)[a-zA-Z0-9_\u4e00-\u9fa5]+$/;
                //var reg=/^(\w+)ws([\u0391-\uFFE5]+)$/;
                if(!reg.test(filename)){
                    alert("文件名必须是数字下划线汉字字母,且不能以下划线开头。");
                    //删除文件
                    up.removeFile(files[0]);
                }else{
                    up.start();//开始上传
                }
            });

            //文件上传进度条事件
            uploader_thumb.bind("UploadProgress",function(up,file){
                //$("#"+file.id+" b").html(file.percent + "%");
            });

            //出错事件
            uploader_thumb.bind("Error",function(up,err){
                alert(err.message);
                up.refresh();
            });

            //上传完毕事件
            uploader_thumb.bind("FileUploaded",function(up,file,res){
                //console.log(response.success+"路径："+response.url);
                var response=JSON.parse(res.response);
                if(response.success){
                    //设置uploaded_medias的值
                    if(uploaded_medias[zy_media_id]["zy_media_type"]=="zy_image"){
                        //如果是图片媒体，需要同时设置四个信息
                        uploaded_medias[zy_media_id]["zy_media_thumb_filename"]=response.data.filename;
                        uploaded_medias[zy_media_id]["zy_media_thumb_filepath"]=response.data.url;
                        uploaded_medias[zy_media_id]["zy_media_filename"]=response.data.filename;
                        uploaded_medias[zy_media_id]["zy_media_filepath"]=response.data.url;
                    }else{
                        uploaded_medias[zy_media_id]["zy_media_thumb_filename"]=response.data.filename;
                        uploaded_medias[zy_media_id]["zy_media_thumb_filepath"]=response.data.url;
                    }

                    //设置缩略图，显示压缩后的图片
                    var img_src=response.data.url;
                    var img_ext=img_src.substring(img_src.lastIndexOf("."),img_src.length);
                    var img_src_compress=img_src.substring(0,img_src.lastIndexOf("."))+parent.zy_config.zy_compress_suffix+img_ext;
                    $("#zy_media_thumb").attr("src",img_src_compress);

                    var parentA=$("#zy_uploaded_medias_ol a[data-zy-media-id='"+zy_media_id+"']",parent.document);


                    //设置列表中的缩略图
                    parentA.find(".zy_small_thumb").attr("src",img_src_compress);
                    parentA.parent().removeClass("zy_media_list_error");


                    //如果是图片类型，还需要设置文件名
                    if(zy_media_id.match("zy_image")!=null){
                        parentA.find(".zy_media_filename").text(file.name);
                    }

                }else{
                    alert(response.data.message);
                }
            });
        }
    };


})();