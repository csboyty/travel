/**
 * Created by JetBrains PhpStorm.
 * User: ty
 * Date: 13-6-13
 * Time: 上午11:10
 * 图文混排和幻灯片公用js,包含了一些常用函数
 * 数据字典
 * zy_json_to_Str json对象转化为字符串
 * zy_get_random 获取随机函数
 * zy_drag 拖拽函数
 * zy_create_thumb_uploader 上传缩略图句柄
 * zy_create_background_uploader 上传背景句柄
 * zy_create__media_uploader    上传媒体文件句柄
 */
var zy_common = {

    /*
     * json对象转字符串函数,主要用于提交最终的media数据
     * params o 需要转化的json对象
     * */
    "zy_json_to_Str":function (o) {

        var me = this;
        var arr = [];
        var fmt = function (s) {
            if (typeof s == 'object' && s != null) return me.zy_json_to_Str(s);
            return /^(string|number)$/.test(typeof s) ? '"' + s + '"' : s;
        };
        for (var i in o) arr.push('"' + i + '":' + fmt(o[i]));
        return '{' + arr.join(',') + '}';
    },

    /*
     * 产生随机数函数，根据当前日期，并且加上前缀，尾部4位随机数
     * */
    "zy_get_random":function () {

        var date = new Date();
        var retValue = "";
        var mo = (date.getMonth() + 1) < 10 ? ('0' + '' + (date.getMonth() + 1)) : date.getMonth() + 1;
        var dd = date.getDate() < 10 ? ('0' + '' + date.getDate()) : date.getDate();
        var hh = date.getHours() < 10 ? ('0' + '' + date.getHours()) : date.getHours();
        var mi = date.getMinutes() < 10 ? ('0' + '' + date.getMinutes()) : date.getMinutes();
        var ss = date.getSeconds() < 10 ? ('0' + '' + date.getSeconds()) : date.getSeconds();
        retValue = date.getFullYear() + '' + mo + '' + dd + '' + hh + '' + mi + '' + ss + '';
        for (var j = 0; j < 4; j++) {
            retValue += '' + parseInt(10 * Math.random()) + '';
        }
        if (arguments.length == 1) {
            return arguments[0] + '' + retValue;
        }
        else
            return retValue;
    },

    /*
     * 控制分类是否可以点击
     * @param container checkbox的外围容器  jquery对象
     * */
    "zy_control_category_checkbox":function (container) {

        //设置分类的点击checkbox
        container.find(".selectit").not(".children .selectit").find("input").attr("disabled", "disabled");
        container.find(".children input").click(function () {
            if (jQuery(this).attr("checked") == "checked") {
                container.find("input:checkbox").not(jQuery(this)).attr("checked", false);
                jQuery(this).parents("li:eq(1)").find("input:eq(0)").attr("checked", "checked");
            } else {
                if (jQuery(this).parents(".children").find("input:checked").length == 0) {
                    jQuery(this).parents("li:eq(1)").find("input:eq(0)").attr("checked", false);
                }
            }
        });
    },

    /*
     * 清除背景
     * */
    "zy_clear_background":function () {
        jQuery("#zy_upload_background_clear").click(function () {
            jQuery("#zy_background_content").remove();
            jQuery("#zy_background").val("");
            jQuery("#zy_background_percent").text("");
            jQuery("<img id='zy_background_content'  class='zy_background' src='" + zy_config.zy_template_url + "/images/app/zy_default_background.png'>").
                appendTo(jQuery("#zy_background_container"));
        });
    },

    /*
     * 拖拽函数
     * */
    "zy_drag":function () {

        var targetOl = jQuery("#zy_uploaded_medias_ol")[0];//容器元素
        var eleDrag = null;//被拖动的元素

        jQuery("#zy_uploaded_medias_ol a").each(function (index, l) {

            var target = jQuery(this)[0];

            //开始选择
            target.onselectstart = function () {

                //阻止默认的事件
                return false;
            };

            //拖拽开始
            target.ondragstart = function (ev) {
                //拖拽效果
                ev.dataTransfer.effectAllowed = "move";
                eleDrag = ev.target;
                return true;
            };

            //拖拽结束
            target.ondragend = function (ev) {
                eleDrag = null;
                return false;
            };
        });

        //在元素中滑过
        //ol作为最大的容器也要处理拖拽事件，当其中有li的时候放到li的前面，但没有的时候放到ol的最后面
        targetOl.ondragover = function (ev) {
            ev.preventDefault();//阻止浏览器的默认事件
            return false;
        };

        //进入元素
        targetOl.ondragenter = function (ev) {

            if (ev.toElement == targetOl) {
                targetOl.appendChild(jQuery(eleDrag).parents("li")[0]);
            } else {
                targetOl.insertBefore(jQuery(eleDrag).parents("li")[0], jQuery(ev.toElement).parents("li")[0]);
            }
            return false;
        };
    },

    /*
     *上传缩略图模块
     * */
    "zy_create_thumb_uploader":function () {

        var uploader_thumb = new plupload.Uploader({
            runtimes:"html5",
            multi_selection:false,
            max_file_size:zy_config.zy_img_upload_size,
            browse_button:"zy_upload_thumb_button",
            container:"zy_thumb_container",
            //flash_swf_url:'../wp-includes/js/plupload/plupload.flash.swf',
            url:ajaxurl,
            filters:[
                {title:"Image files", extensions:"jpg,gif,png,jpeg"}
            ],
            multipart_params:{
                action:"uploadfile",
                user_id:zy_config.zy_user_id,
                file_type:"zy_thumb",
                post_id:jQuery("#post_ID").val()
            }
        });

        //初始化
        uploader_thumb.init();

        //文件添加事件
        uploader_thumb.bind("FilesAdded", function (up, files) {
            var filename = files[0].name;
            var lastIndex = filename.lastIndexOf(".");
            filename = filename.substring(0, lastIndex);

            //只含有汉字、数字、字母、下划线不能以下划线开头和结尾
            var reg = /^(?!_)(?!.*?_$)[a-zA-Z0-9_\u4e00-\u9fa5]+$/;
            //var reg=/^(\w+)ws([\u0391-\uFFE5]+)$/;
            if (!reg.test(filename)) {
                alert("文件名必须是数字下划线汉字字母,且不能以下划线开头。");

                //删除文件
                up.removeFile(files[0]);
                return false;
            } else {
                up.start();//开始上传
            }
        });

        //文件上传进度条事件
        uploader_thumb.bind("UploadProgress", function (up, file) {
            //$("#"+file.id+" b").html(file.percent + "%");
        });

        //出错事件
        uploader_thumb.bind("Error", function (up, err) {
            alert(err.message);
            up.refresh();
        });

        //上传完毕事件
        uploader_thumb.bind("FileUploaded", function (up, file, res) {
            //console.log(response.success+"路径："+response.url);
            var response = JSON.parse(res.response);
            //console.log(response);
            if (response.success) {

                //显示压缩后的图片
                var img_src = response.data.url;
                var img_ext = img_src.substring(img_src.lastIndexOf("."), img_src.length);
                var img_src_compress = img_src.substring(0, img_src.lastIndexOf(".")) + zy_config.zy_compress_suffix + img_ext;
                jQuery("#zy_uploaded_thumb").attr("src", img_src_compress);
                jQuery("#zy_thumb").val(response.data.filename);
            } else {
                alert(response.data.message);
            }
        });
    },

    /*
     *上传背景模块
     * */
    "zy_create_background_uploader":function () {

        var uploader_background = new plupload.Uploader({
            runtimes:"html5",
            multi_selection:false,
            max_file_size:"20mb",
            browse_button:"zy_upload_background_button",
            container:"zy_background_container",
            //flash_swf_url:'../wp-includes/js/plupload/plupload.flash.swf',
            url:ajaxurl,
            filters:[
                {title:"Background files", extensions:"jpg,gif,png,jpeg,mp4"}
            ],
            multipart_params:{
                action:"uploadfile",
                user_id:zy_config.zy_user_id,
                file_type:"zy_background",
                post_id:jQuery("#post_ID").val()
            }
        });

        //初始化
        uploader_background.init();

        //文件添加事件
        uploader_background.bind("FilesAdded", function (up, files) {
            var filename = files[0].name;
            var lastIndex = filename.lastIndexOf(".");
            filename = filename.substring(0, lastIndex);

            //只含有汉字、数字、字母、下划线不能以下划线开头和结尾
            var reg = /^(?!_)(?!.*?_$)[a-zA-Z0-9_\u4e00-\u9fa5]+$/;
            if (!reg.test(filename)) {
                alert("文件名必须是数字下划线汉字字母,且不能以下划线开头。");

                //删除文件
                up.removeFile(files[0]);
                return false;
            } else {
                up.start();//开始上传
            }
        });

        //文件上传进度条事件
        uploader_background.bind("UploadProgress", function (up, file) {
            //$("#"+file.id+" b").html(file.percent + "%");
            jQuery("#zy_background_percent").html(file.percent + "%");
        });

        //出错事件
        uploader_background.bind("Error", function (up, err) {
            alert(err.message);
            up.refresh();
        });

        //上传完毕事件
        uploader_background.bind("FileUploaded", function (up, file, res) {
            //console.log(response.success+"路径："+response.url);
            var response = JSON.parse(res.response);
            if (response.success) {
                var filename = response.data.filename;
                var extension = filename.substr(filename.indexOf(".") + 1, filename.length - 1);
                jQuery("#zy_background_content").remove();
                jQuery("#zy_background_percent").text("");
                var string = "";
                if (extension == "mp4") {
                    string = "<video id='zy_background_content' class='zy_background' controls><source src='" + response.data.url + "' type='video/mp4' /></video>";
                    jQuery("#zy_background_container").append(string);
                } else {
                    string = "<img id='zy_background_content' class='zy_background' src='" + response.data.url + "'>";
                    jQuery("#zy_background_container").append(string);
                }
                jQuery("#zy_background").val(filename);
            } else {
                alert(response.data.message);
            }
        });
    },

    /*
     * 上传媒体文件模块
     * params filtesrs 文件的格式筛选，upload_btn 绑定上传按钮的元素id，type 媒体文件的类型
     * filesize 文件大小
     * */
    "zy_create_media_uploader":function (filters, upload_btn, type, filesize) {

        var me = this;//保存下this变量，以防止事件过程中改变

        //注意，在上传过程中container是不能隐藏的，在声明的时候containner也应该有内容，宽高度都有，不然无法申明
        //上传媒体模块
        var uploader_media = new plupload.Uploader({
            runtimes:"html5",
            multi_selection:true,
            //multipart:true,//默认为true
            max_file_size:filesize,
            browse_button:upload_btn,
            container:"zy_add_media_menu",
            //flash_swf_url:'../wp-includes/js/plupload/plupload.flash.swf',
            url:ajaxurl,
            filters:[
                {title:"Media files", extensions:filters}
            ],
            multipart_params:{
                action:"uploadfile",
                user_id:zy_config.zy_user_id,
                file_type:filters.indexOf("jpg") != -1 ? "zy_content_img" : "",
                post_id:jQuery("#post_ID").val()
            }
        });


        //初始化
        uploader_media.init();

        //根据type生成zy_media_id,和iframe的页面
        var zy_media_ids = {};//一个file.id和媒体media_id的关联hash，因为要传多个文件，需要记录下每个media_id
        var zy_iframe_page_names = {};

        //文件添加事件
        uploader_media.bind("FilesAdded", function (up, files) {
            var zy_media_id = "";
            var zy_iframe_page_name = "";
            var fileLength=files.length;

            for (var i = 0; i < fileLength; i++) {
                var filename = files[i].name;
                var lastIndex = filename.lastIndexOf(".");
                var filename_noext = filename.substring(0, lastIndex);

                //只含有汉字、数字、字母、下划线不能以下划线开头和结尾
                var reg = /^(?!_)(?!.*?_$)[a-zA-Z0-9_\u4e00-\u9fa5]+$/;
                if (!reg.test(filename_noext)) {
                    alert("文件" + filename + "命名有误（只能数字汉字字母下划线，且不能以下划线开头）,将从上传列表中删除。");

                    up.removeFile(files[i]);
                } else {

                    //给zy_media_id和iframe页面名称赋值
                    if (type == "zy_location_video") {
                        zy_media_id = me.zy_get_random("zy_location_");
                        zy_iframe_page_name = "zy_set_location_video.html";
                        zy_media_ids[files[i]["id"]] = zy_media_id;
                        zy_iframe_page_names[files[i]["id"]] = zy_iframe_page_name;
                    } else if (type == "zy_3d") {
                        zy_media_id = me.zy_get_random("zy_3d_");
                        zy_iframe_page_name = "zy_set_3d.html";
                        zy_media_ids[files[i]["id"]] = zy_media_id;
                        zy_iframe_page_names[files[i]["id"]] = zy_iframe_page_name;
                    } else if (type == "zy_ppt") {
                        zy_media_id = me.zy_get_random("zy_ppt_");
                        zy_iframe_page_name = "zy_set_ppt.html";
                        zy_media_ids[files[i]["id"]] = zy_media_id;
                        zy_iframe_page_names[files[i]["id"]] = zy_iframe_page_name;
                    } else if (type == "zy_image") {
                        zy_media_id = me.zy_get_random("zy_image_");
                        zy_iframe_page_name = "zy_set_image.html";
                        zy_media_ids[files[i]["id"]] = zy_media_id;
                        zy_iframe_page_names[files[i]["id"]] = zy_iframe_page_name;
                    }

                    //组装显示的数据
                    var data = {
                        media_id:zy_media_id,
                        thumb_src:zy_config.zy_template_url + '/images/app/zy_small_thumb.png',
                        filename:filename
                    };

                    //显示列表项
                    var tpl = jQuery("#zy_uncomplete_tpl").html();
                    var html = juicer(tpl, data);
                    jQuery("#zy_uploaded_medias_ol").append(html);

                    //隐藏菜单栏
                    jQuery("#zy_add_media_menu").css("height", 0);
                    //jQuery("#zy_add_media_menu").css("zIndex",1);
                }
            }

            //开始上传
            up.start();

        });

        //文件上传进度条事件
        uploader_media.bind("UploadProgress", function (up, file) {
            jQuery(".zy_uncomplete_li[data-zy-media-id='" + zy_media_ids[file.id] + "']").find(".zy_media_percent").html(file.percent + "%");

        });

        //出错事件
        uploader_media.bind("Error", function (up, err) {
            //由于这里4个上传按钮放到一个面板中，会出现init错误，但是不影响使用，
            if (err.message.match("Init") == null) {
                alert(err.message);
            }
            up.refresh();
        });

        //上传完毕事件
        uploader_media.bind("FileUploaded", function (up, file, res) {
            var response = JSON.parse(res.response);
            if (response.success) {

                //如果存在未完成的li，那说明在上传的过程中没有被删除，应该处理
                var uncomplete_li=jQuery(".zy_uncomplete_li[data-zy-media-id='" + zy_media_ids[file.id] + "']");
                if(uncomplete_li.length){

                    //移除上传时候的li
                    uncomplete_li.remove();


                    var classString = "class='zy_media_list_error'";
                    var thumb_src = zy_config.zy_template_url + "/images/app/zy_small_thumb.png";


                    if (type == "zy_image") {

                        //显示压缩后的图片
                        var img_src = response.data.url;
                        var img_ext = img_src.substring(img_src.lastIndexOf("."), img_src.length);
                        var img_src_compress = img_src.substring(0, img_src.lastIndexOf(".")) + zy_config.zy_compress_suffix + img_ext;
                        thumb_src = img_src_compress;
                        classString = "";
                    }

                    if (jQuery("#zy_uploaded_medias_ol .zy_media_list_active").length == 0) {

                        classString = classString == "" ? "class='zy_media_list_active'" : "class='zy_media_list_active zy_media_list_error'";

                        jQuery("#zy_media_iframe").attr("src", zy_config.zy_template_url + '/zy_pages/' + zy_iframe_page_names[file.id] + '?' + zy_media_ids[file.id]);

                    }


                    //组装显示的数据
                    var data = {
                        classString:classString,
                        media_type:type,
                        media_id:zy_media_ids[file.id],
                        iframe_src:zy_config.zy_template_url + '/zy_pages/' + zy_iframe_page_names[file.id] + '?' + zy_media_ids[file.id],
                        thumb_src:thumb_src,
                        filename:file.name
                    };

                    //显示列表项
                    var tpl = jQuery("#zy_complete_tpl").html();
                    var html = juicer(tpl, data);
                    jQuery("#zy_uploaded_medias_ol").append(html);

                    //设置zy_uploaded_medias
                    zy_uploaded_medias[zy_media_ids[file.id]] = {

                        //声明一个空的对象，后续将内容全部加入
                    };

                    if (type == "zy_image") {

                        //如果是图片媒体，需要同时设置四个信息
                        zy_uploaded_medias[zy_media_ids[file.id]]["zy_media_thumb_filename"] = response.data.filename;
                        zy_uploaded_medias[zy_media_ids[file.id]]["zy_media_thumb_filepath"] = response.data.url;
                        zy_uploaded_medias[zy_media_ids[file.id]]["zy_media_filename"] = response.data.filename;
                        zy_uploaded_medias[zy_media_ids[file.id]]["zy_media_filepath"] = response.data.url;
                    } else {
                        zy_uploaded_medias[zy_media_ids[file.id]]["zy_media_filename"] = response.data.filename;
                        zy_uploaded_medias[zy_media_ids[file.id]]["zy_media_filepath"] = response.data.url;
                    }
                    zy_uploaded_medias[zy_media_ids[file.id]]["zy_media_type"] = type;

                    //执行一次拖拽,因为元素是动态添加的，应该在添加后添加拖拽事件
                    me.zy_drag();
                }
            } else {
                alert(response.data.message);
            }
        });

    }
};
