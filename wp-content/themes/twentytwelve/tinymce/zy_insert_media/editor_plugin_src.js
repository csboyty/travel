/**
 * Created by JetBrains WebStorm.
 * User: ty
 * Date: 13-5-10
 * Time: 上午11:22
 * tinymce插件，主要是用来点击图片时邦定媒体文件
 */
(function() {

    tinymce.create('tinymce.plugins.zy_insert_media', {
        /**
         * 初始化插件, 插件创建后执行.
         * This call is done before the editor instance has finished it's initialization so use the onInit event
         * of the editor instance to intercept that event.
         *
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
        init : function(ed, url) {
            // 注册mceExample命令，使用tinyMCE.activeEditor.execCommand('mceExample')激活此命令;
            //这里面不能使用ed.dom在这个方法里面，ed.dom还没有初始化只能用tinymce.DOM
           /* tinymce.DOM.add(document.body, 'div', {
                id : 'zy_editbtns',
                style : 'display:none;'
            });*/

            tinymce.DOM.add("wp_editbtns","img",{
                id : 'zy_insert_network_video',
                src:url + '/img/zy_insert_network_video.png',
                width:'24',
                class:"zy_insert_img",
                height:'24',
                title:"插入网络视频"
            });
            tinymce.DOM.add("wp_editbtns","img",{
                id : 'zy_insert_location_video',
                src:url + '/img/zy_insert_location_video.png',
                width:'24',
                class:"zy_insert_img",
                height:'24',
                title:"插入本地视频"
            });
            tinymce.DOM.add("wp_editbtns","img",{
                id : 'zy_insert_3d',
                src:url + '/img/zy_insert_3d.png',
                width:'24',
                class:"zy_insert_img",
                height:'24',
                title:"插入3D文件"
            });
            tinymce.DOM.add("wp_editbtns","img",{
                id : 'zy_insert_ppt',
                src:url + '/img/zy_insert_ppt.png',
                width:'24',
                class:"zy_insert_img",
                height:'24',
                title:"插入ppt文件"
            });


            /*
            * 绑定事件
            * */
            //删除事件
            tinymce.dom.Event.remove("wp_delimgbtn", 'mousedown');
            tinymce.dom.Event.add("wp_delimgbtn", 'mousedown',function(){
                if(confirm("确定删除吗?")){
                    var ed = tinymce.activeEditor, el = ed.selection.getNode(), parent;

                    if ( el.nodeName == 'IMG' && ed.dom.getAttrib(el, 'class').indexOf('mceItem') == -1 ) {
                        if ( (parent = ed.dom.getParent(el, 'div')) && ed.dom.hasClass(parent, 'mceTemp') ) {
                            ed.dom.remove(parent);
                        } else {
                            if ( el.parentNode.nodeName == 'A' && el.parentNode.childNodes.length == 1 )
                                el = el.parentNode;

                            if ( el.parentNode.nodeName == 'P' && el.parentNode.childNodes.length == 1 )
                                el = el.parentNode;

                            ed.dom.remove(el);
                        }

                        ed.execCommand('mceRepaint');
                        return false;
                    }
                    ed.plugins.wordpress._hideButtons();
                }
            });
              //插入网络视频
             tinymce.dom.Event.add("zy_insert_network_video", 'click', function(e) {
                //alert(ed.selection.getNode().src);
                //ed.selection.getNode().setAttribute("data-zy-media-id","12222");

                ed.windowManager.open({
                    title:"插入网络视频",
                    file : url + '/zy_insert_network_video.html',
                    width : 1020,
                    height : 610,
                    inline : 1
                }, {
                    zy_media_id : ed.selection.getNode().getAttribute("data-zy-media-id") // 媒体id参数
                    //zy_media_type : ed.selection.getNode().getAttribute("data-zy-media-type") // 媒体类型参数
                });

                 //下面这句是wordpress自己编写的插件来隐藏按钮，这里可以直接使用
                ed.plugins.wordpress._hideButtons();
            });
            //插入本地视频
            tinymce.dom.Event.add("zy_insert_location_video", 'click', function(e) {
                //alert(ed.selection.getNode().src);
                //ed.selection.getNode().setAttribute("data-zy-media-id","12222");

                ed.windowManager.open({
                    title:"插入本地视频",
                    file : url + '/zy_insert_location_video.html',
                    width : 1020,
                    height : 610,
                    inline : 1
                }, {
                    zy_media_id : ed.selection.getNode().getAttribute("data-zy-media-id") // 媒体id参数
                    //zy_media_type : ed.selection.getNode().getAttribute("data-zy-media-type") // 媒体类型参数
                });

                //下面这句是wordpress自己编写的插件来隐藏按钮，这里可以直接使用
                ed.plugins.wordpress._hideButtons();
            });
            //插入3d文件
            tinymce.dom.Event.add("zy_insert_3d", 'click', function(e) {
                //alert(ed.selection.getNode().src);
                //ed.selection.getNode().setAttribute("data-zy-media-id","12222");

                ed.windowManager.open({
                    title:"插入3d文件",
                    file : url + '/zy_insert_3d.html',
                    width : 1020,
                    height : 610,
                    inline : 1
                }, {
                    zy_media_id : ed.selection.getNode().getAttribute("data-zy-media-id") // 媒体id参数
                    //zy_media_type : ed.selection.getNode().getAttribute("data-zy-media-type") // 媒体类型参数
                });

                //下面这句是wordpress自己编写的插件来隐藏按钮，这里可以直接使用
                ed.plugins.wordpress._hideButtons();
            });
            //插入ppt文件
            tinymce.dom.Event.add("zy_insert_ppt", 'click', function(e) {
                //alert(ed.selection.getNode().src);
                //ed.selection.getNode().setAttribute("data-zy-media-id","12222");

                ed.windowManager.open({
                    title:"插入ppt文件",
                    file : url + '/zy_insert_ppt.html',
                    width : 1020,
                    height : 610,
                    inline : 1
                }, {
                    zy_media_id : ed.selection.getNode().getAttribute("data-zy-media-id") // 媒体id参数
                    //zy_media_type : ed.selection.getNode().getAttribute("data-zy-media-type") // 媒体类型参数
                });

                //下面这句是wordpress自己编写的插件来隐藏按钮，这里可以直接使用
                ed.plugins.wordpress._hideButtons();
            });

            //替换wordpress原有的图标
            document.getElementById("wp_editimgbtn").src=url+"/img/zy_image_edit.png";
            document.getElementById("wp_delimgbtn").src=url+"/img/zy_image_delete.png";

        },


        /**
         * 以键/值数组格式返回插件信息
         * 下面有：longname, author, authorurl, infourl and version.
         *
         * @return {Object} Name/value array containing information about the plugin.
         */
        getInfo : function() {
            return {
                longname : 'zy_insert_media plugin',
                author : 'ty',
                authorurl : 'http://tinymce.moxiecode.com',
                infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/example',
                version : "1.0"
            };
        }
    });

    // 注册插件
    tinymce.PluginManager.add('zy_insert_media', tinymce.plugins.zy_insert_media);
})();