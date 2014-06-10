/*
* 图文混排文章js
* */
jQuery(document).ready(function($){

    /*
    * 编写图文混排页面的逻辑处理函数
    * */
    var zy_post_controller={

        /*
        * 显示摘要输入框
        * */
        "zy_show_excerpt":function(){
            $("#postexcerpt-hide").attr("checked","checked");
            $("#postexcerpt").css("display","block");
        },

        /*
        * 获取已经上传的媒体文件
        * @return 以上上传的媒体文件json字符串
        * */
        "zy_get_medias":function(){

            //设置medias，要先把所有的img的获取出来，比较一下，因为有可能有的图片被删掉了。
            //获取富文本编辑器内容
            var content="";
            if(document.getElementById('content_ifr')){
                content=$(document.getElementById('content_ifr').contentWindow.document.body).html();
            }else{
                content=$("#content").text();
            }

            var patt=/zy_location_\d+|zy_network_\d+|zy_3d_\d+|zy_ppt_\d+/g;
            var medias_list=content.match(patt);
            var zy_medias_string="";

            //判断是否上传了媒体文件
            if(medias_list&&medias_list.length!=0){
                var medias_list_string=medias_list.join(",");

                //判断已经上传的对象数据中的键值，是否都在最后提交的数组中存在，如果不存在则需要删除
                for(var obj in zy_uploaded_medias){
                    if(medias_list_string.indexOf(obj)==-1){

                        //如果不存在，要删除该属性
                        zy_uploaded_medias[obj]=undefined;//先删除属性值
                        delete zy_uploaded_medias[obj];//删除属性键，这样才能彻底删除对象属性
                    }
                }
                zy_medias_string=zy_common.zy_json_to_Str(zy_uploaded_medias);
            }

            return zy_medias_string;
        }
    };

     //上传句柄
    zy_common.zy_create_thumb_uploader();
    zy_common.zy_create_background_uploader();

    //控制分类的点击
    zy_common.zy_control_category_checkbox($("#categorychecklist"));

    //显示摘要
    zy_post_controller.zy_show_excerpt();

    //清楚背景图图
    zy_common.zy_clear_background();

    //提交事件
    $("#publish").click(function(){
        if($("#title").val()==""||$("#zy_thumb").val()==""||$("#excerpt").val()==""||$("#categorychecklist input:checked").length==0){
            alert("标题、摘要、分类、缩略图没有填写完整。");
            return false;
        }


        $("#zy_medias").val(zy_post_controller.zy_get_medias());

        //加上禁用的checkbox
        var disable_value=$("#categorychecklist .selectit").not(".children .selectit").find("input:checked").val();
        $("<input type='hidden' name='post_category[]' value='"+disable_value+"'>").appendTo($("#post"));
    });
});
