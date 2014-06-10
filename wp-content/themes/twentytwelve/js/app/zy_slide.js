/*
* 幻灯片编辑javascript

* 2013-06-17

* */

jQuery(document).ready(function($){



    /*

    * 编辑幻灯片页面的逻辑处理类

    * */

    var zy_slide_controller={

        /*

         * 获取当前日期

         * @return 年月日的日期字符串

         * */

        "zy_toDay":function(){

            var date=new Date();

            var year=date.getFullYear();

            var month=date.getMonth()+1;

            var day=date.getDate();

            return year+"-"+month+"-"+day;

        },

        /*
         * 获取所有已经上传的媒体文件
         * */
        "zy_get_xinjiang_slides":function(){


            var array_slides=[];

            $(".zy_media_list").each(function(index,m){

				var obj={};//幻灯片每一页的对象

				var media_id=$(this).data("zy-media-id");

				var title=zy_uploaded_medias[media_id]["zy_media_title"];

				var type=zy_uploaded_medias[media_id]["zy_media_type"];

				var memo=zy_uploaded_medias[media_id]["zy_media_memo"];

				var img_src=zy_uploaded_medias[media_id]["zy_media_thumb_filepath"];

				var img_ext=img_src.substring(img_src.lastIndexOf("."),img_src.length);

				var img_src_compress=img_src.substring(0,img_src.lastIndexOf("."))+zy_config.zy_compress_suffix+img_ext;



                obj.title=title;

                obj.memo=memo;



                if(type=="zy_image"){

                    obj.content='<a href="'+img_src+'"><img src="'+img_src_compress+'" data-zy-media-id="'+media_id+'" /></a>';

                }else if(type=="zy_ppt"){

                    obj.content='<a class="zy_preview_pptslide" href="'+img_src+'"><img src="'+img_src_compress+'" data-zy-media-id="'+media_id+'" /></a>';

                }else if(type=="zy_3d"){

                    obj.content='<a class="zy_preview_3dslide" href="'+img_src+'"><img src="'+img_src_compress+'" data-zy-media-id="'+media_id+'" /></a>';

                }else if(type=="zy_location_video"){

                    obj.content='<a class="zy_preview_videoslide" href="'+img_src+'"><img src="'+img_src_compress+'" data-zy-media-id="'+media_id+'" /></a>';

                }else if(type=="zy_network_video"){

                    obj.content='<a class="zy_preview_webslide" href="'+img_src+'"><img src="'+img_src_compress+'" data-zy-media-id="'+media_id+'" /></a>';

                }



                array_slides.push(obj);

            });



            //将所有的幻灯片页返回

            return array_slides;

        },



        /*

         * 新疆幻灯片展示模版

         * */

        "zy_show_xinjiang_slide":function(){

            var data={};

            data.title=$("#zy_title").val();

            data.author=zy_config.zy_current_author;

            data.category=$("#zy_category_list .children input:checked").parent().text();

            data.date=this.zy_toDay();

            data.excerpt=$("#zy_memo").val();



            data.slides=this.zy_get_xinjiang_slides();



            var tpl=$("#zy_xinjiang_preview_tpl").html();

            var html=juicer(tpl,data);



            $("#zy_preview_content").html(html).height(550).css("background","white");

        },



        /*

        * 获取幻灯片的内容

        * @return 内容字符串

        * */

        "zy_get_slide_content":function(){

            var contents="";

            $(".zy_media_list").each(function(index,l){

                var media_id=$(this).data("zy-media-id");

                var media_type=$(this).data("zy-media-type");

                var img_src_compress=$(this).find("img").attr("src");

                var img_src=img_src_compress.replace(zy_config.zy_compress_suffix,"");



                if(media_type!="zy_image"){

                    if(media_type=="zy_ppt"){

                        contents+='<a class="pptslide" href="'+img_src+'"><img src="'+img_src_compress+'" data-zy-media-id="'+media_id+'" /></a>';

                    }else if(media_type=="zy_3d"){

                        contents+='<a class="_3dslide" href="'+img_src+'"><img src="'+img_src_compress+'" data-zy-media-id="'+media_id+'" /></a>';

                    }else if(media_type=="zy_location_video"){

                        contents+='<a class="videoslide" href="'+img_src+'"><img src="'+img_src_compress+'" data-zy-media-id="'+media_id+'" /></a>';

                    }else if(media_type=="zy_network_video"){

                        contents+='<a class="webslide" href="'+img_src+'"><img src="'+img_src_compress+'" data-zy-media-id="'+media_id+'" /></a>';

                    }

                }else{

                    //如果是纯粹的图片，不需要保存media_id

                    contents+='<a href="'+img_src+'"><img data-zy-media-id="'+media_id+'" src="'+img_src_compress+'" /></a>';

                }
            });
            return contents;

        },

        /*
        * 显示标签，先要判断是否存在，如果存在则不显示
        * @param string tag_name 标签名
        * */
        "zy_tag_show":function(tag_name){
            var exist_flag=false;
            if($(".zy_tag_name").length!=0){
                $(".zy_tag_name").each(function(index,t){
                    if($(this).text().trim()==tag_name){

                        //如果存在，则直接返回
                        exist_flag=true;
                        return false;
                    }
                });
            }

            if(!exist_flag){
                var tpl=$("#zy_tag_tpl").html();
                var data={
                    tag_name:tag_name
                };
                var html=juicer(tpl,data);
                $("#zy_select_tags").append(html);
            }
        }

    };

    //控制分类的点击
    zy_common.zy_control_category_checkbox($("#zy_category_list"));

    //清除背景
    zy_common.zy_clear_background();

    //tab点击事件
    $("#zy_nav a").click(function(){

        var target=$(this).attr("href");

        //判断第二步是否可以点击
        if(target=="#zy_content"){

            //第一步中所有的内容都填写后才能点击第二步
            if($("#zy_title").val()==""||$("#zy_memo").val()==""||$("#zy_thumb").val()==""||$("#zy_category_list input:checked").length==0){

                alert("标题、描述、类别、缩略图等没有填写完整。");

                return false;

            }

            //进入让第一个选中(针对修改)
            if($(".zy_media_list_active").length==0){
                if($("#zy_uploaded_medias_ol li:eq(0)").length!=0){

                    $("#zy_uploaded_medias_ol li:eq(0)").addClass("zy_media_list_active");

                    $("#zy_media_iframe").attr("src",$("#zy_uploaded_medias_ol li:eq(0)").find("a").attr("href"));

                }
            }

        }



        //判断第三步是否可以点击

        if(target=="#zy_preview"){

            //判断第二中的内容是否都已经填写完整。

            if($(".zy_media_list").length!=0&&$(".zy_uncomplete_li").length==0){

                for(var obj in zy_uploaded_medias){

                    //如果有媒体文件没有传缩略图，则不能到第三步

                    if(!zy_uploaded_medias[obj]["zy_media_thumb_filename"]){

                    	alert("有媒体文件没有上传缩略图，请上传后再预览！");

                        return false;

                    }

                }

            }else{

         		alert("没有上传媒体文件或者有上传错误的媒体文件，请上传或者删除后再预览！");

                return false;

            }

            //显示新疆幻灯片

            zy_slide_controller.zy_show_xinjiang_slide();

            //显示插入按钮

            $("#zy_insert_btn").removeClass("zy_hidden");

        }else{

            $("#zy_insert_btn").addClass("zy_hidden");

        }

        //做显示控制

        $("article").addClass("zy_hidden");

        $(target).removeClass("zy_hidden");



        $("#zy_nav a").removeClass("zy_active");

        $(this).addClass("zy_active");



        //阻止浏览器默认事件，默认进行a的跳转

        return false;

    });

    //标签自定匹配
    $("#zy_tags").autocomplete({
        minLength:2,
        source:zy_tags,
        select:function(event,ui){
           zy_slide_controller.zy_tag_show(ui.item.label);
        }
    });

    //标签添加按钮事件
    $("#zy_add_tag").click(function(){
        var tag_name=$("#zy_tags").val();
        if(tag_name){
            zy_slide_controller.zy_tag_show(tag_name);
        }
    });

    //删除标签
    $(document).on("click",".zy_tag_delete",function(){
        $(this).parent().remove();
    });

    //上传缩略图

    zy_common.zy_create_thumb_uploader();

    zy_common.zy_create_background_uploader();



    //第二步代码
    //点击添加文件事件

    $("#zy_add_medias_button").hover(function(e){

       /*$("#zy_add_media_menu").css("zIndex",3);*/

	   $("#zy_add_media_menu").css("height","300px")

    },function(e){

        /*$("#zy_add_media_menu").css("zIndex",1);*/

		$("#zy_add_media_menu").css("height",0)

    });

    $("#zy_add_media_menu").hover(function(e){

        /*$("#zy_add_media_menu").css("zIndex",3);*/

		$("#zy_add_media_menu").css("height","300px")

    },function(e){

        /*$("#zy_add_media_menu").css("zIndex",1);*/

		$("#zy_add_media_menu").css("height",0)

    });



    //输入视频文件控制部分

    $("#zy_network_input_ok").click(function(){

        if($("#zy_network_input").val().trim().match(/^<iframe/)!=null){

            $("#zy_network_input").removeClass("zy_input_invalid");



            //防止后台json_decode出错，将双引号改成单引号

            var filename=$("#zy_network_input").val().replace(/["]/g,"'");



            //生成zy_media_id

            var zy_media_id=zy_common.zy_get_random("zy_network_");



            var classString="class='zy_media_list_error'"; //记录下是否有class



            //设置列表中的值

            if(jQuery("#zy_uploaded_medias_ol .zy_media_list_active").length==0){

                classString="class='zy_media_list_error zy_media_list_active'";



                $("#zy_media_iframe").attr("src",zy_config.zy_template_url+'/zy_pages/zy_set_network_video.html?'+zy_media_id);

                

            }



            //组装显示的数据

            var data={

                classString:classString,

                media_type:"zy_network_video",

                media_id:zy_media_id,

                iframe_src:zy_config.zy_template_url+'/zy_pages/zy_set_network_video.html?'+zy_media_id,

                thumb_src:zy_config.zy_template_url+'/images/app/zy_small_thumb.png',

                filename:filename

            };



            //显示列表项

            var tpl=$("#zy_complete_tpl").html();

            var html=juicer(tpl,data);

            $("#zy_uploaded_medias_ol").append(html);





            //设置zy_uploaded_medias

            zy_uploaded_medias[zy_media_id]={



                //声明一个空的对象，后续将内容全部加入

            };

            zy_uploaded_medias[zy_media_id]["zy_media_type"]="zy_network_video";

            zy_uploaded_medias[zy_media_id]["zy_media_filename"]=filename;

            zy_uploaded_medias[zy_media_id]["zy_media_filepath"]=filename;



            //关闭窗口

            tb_remove();           



            //重新绑定拖拽事件

            zy_common.zy_drag();

        }else{

            $("#zy_network_input").addClass("zy_input_invalid");

        }

    });



    //添加图片文件

    zy_common.zy_create_media_uploader("jpg,jpeg,png","zy_add_image","zy_image",zy_config.zy_img_upload_size);



    //添加本地视频文件

    zy_common.zy_create_media_uploader("mp4","zy_add_location_video","zy_location_video",zy_config.zy_media_upload_size);



    //添加3d文件

    zy_common.zy_create_media_uploader("zip","zy_add_3d","zy_3d",zy_config.zy_media_upload_size);



    //添加ppt文件

    zy_common.zy_create_media_uploader("zip","zy_add_ppt","zy_ppt",zy_config.zy_media_upload_size);



    //删除未上传的文件

    $(document).on("click","span.zy_uncomplete_delete",function(){

        //删除li

        if(confirm("确定删除吗？")){

                $(this).parents("li").remove();

        }

    });



    //删除已经上传的文件

    $(document).on("click","span.zy_media_delete",function(event){

        if(confirm("确定删除吗？")){

            var media_id=$(this).parent().data("zy-media-id");

            zy_uploaded_medias[media_id]=undefined;

            delete zy_uploaded_medias[media_id];

            $(this).parents("li").remove();



            //让第一个选中

            if($("#zy_uploaded_medias_ol li").not(".zy_uncomplete_li").length!=0){

                $("#zy_uploaded_medias_ol li").removeClass("zy_media_list_active");

                $("#zy_uploaded_medias_ol li:eq(0)").addClass("zy_media_list_active");

                $("#zy_media_iframe").attr("src",$("#zy_uploaded_medias_ol li:eq(0)").find("a").attr("href"));

               

                $("#zy_uploaded_medias_ol").scrollTop(0);

            }else{

                

                $("#zy_media_iframe").removeAttr("src");

            }

        }



        //阻止事件冒泡到a

        return false;

    });



    //列表中每一项的点击事件，如果选中的列表没有填写完整，则不能选择其他列表

    $(document).on("click","a.zy_media_list",function(){

        var active=$(".zy_media_list_active");



        if(active.length!=0){

			//如果可以显示其他列表项，要删除active类

			active.removeClass("zy_media_list_active");	

        }



        //设置媒体类型

        var type=$(this).data("zy-media-type");

        if(type=="zy_location_video"){

            $("#zy_media_type").text("本地视频");

        }else if(type=="zy_3d"){

            $("#zy_media_type").text("3d文件");

        }else if(type=="zy_ppt"){

            $("#zy_media_type").text("ppt文件");

        }else if(type=="zy_image"){

            $("#zy_media_type").text("图片");

        }else if(type=="zy_network_video"){

            $("#zy_media_type").text("网络视频");

        }



        //控制类

        $(this).parent("li").addClass("zy_media_list_active");
    });



    //执行一次拖拽方法，因为修改的时候会有刷出来的数据

    zy_common.zy_drag();



    //第三步代码

    //预览的时候要显示媒体文件

    $(document).on("click",".zy_preview_slide_xinjiang a",function(event){



        var media_id=$(this).find("img").data("zy-media-id");

        var media_type=zy_uploaded_medias[media_id]["zy_media_type"];

        var media_filepath=zy_uploaded_medias[media_id]["zy_media_filepath"];



        if(media_type=="zy_location_video"){

            $("#zy_show_div").html("<video class='zy_preview_video' "+

                " autoplay='autoplay' controls>"+

                "<source src='"+media_filepath+"' type='video/mp4' /></video>");

        }else if(media_type=="zy_3d"){



        }else if(media_type=="zy_ppt"){

            $("#zy_show_div").html("<iframe class='zy_preview_iframe' src='"+media_filepath+"/index.html'></iframe>");

        }else if(media_type=="zy_network_video"){

            $("#zy_show_div").html(media_filepath);

        }else{

            $("#zy_show_div").html($(this).html());

        }



        //显示thickbox

        tb_show("预览媒体文件","#TB_inline?width=800&height=500&inlineId=zy_show_div",false);



        //阻止默认的事件

        return false;

    });



    //提交事件

    $("#zy_insert_btn").click(function(){


        //设置传到后台的幻灯片内容

        $("#zy_slide_content").val(zy_slide_controller.zy_get_slide_content());



        var zy_medias_string=zy_common.zy_json_to_Str(zy_uploaded_medias);

        $("#zy_medias").val(zy_medias_string);



        //加上禁用的checkbox

        var disable_value=$("#zy_category_list .selectit").not(".children .selectit").find("input:checked").val();

        $("<input type='hidden' name='post_category[]' value='"+disable_value+"'>").appendTo($(".zy_form"));



    });

});