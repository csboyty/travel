(function(){tinymce.create('tinymce.plugins.zy_insert_media',{init:function(ed,url){tinymce.DOM.add("wp_editbtns","img",{id:'zy_insert_network_video',src:url+'/img/zy_insert_network_video.png',width:'24',class:"zy_insert_img",height:'24',title:"插入网络视频"});tinymce.DOM.add("wp_editbtns","img",{id:'zy_insert_location_video',src:url+'/img/zy_insert_location_video.png',width:'24',class:"zy_insert_img",height:'24',title:"插入本地视频"});tinymce.DOM.add("wp_editbtns","img",{id:'zy_insert_3d',src:url+'/img/zy_insert_3d.png',width:'24',class:"zy_insert_img",height:'24',title:"插入3D文件"});tinymce.DOM.add("wp_editbtns","img",{id:'zy_insert_ppt',src:url+'/img/zy_insert_ppt.png',width:'24',class:"zy_insert_img",height:'24',title:"插入ppt文件"});tinymce.dom.Event.remove("wp_delimgbtn",'mousedown');tinymce.dom.Event.add("wp_delimgbtn",'mousedown',function(){if(confirm("确定删除吗?")){var ed=tinymce.activeEditor,el=ed.selection.getNode(),parent;if(el.nodeName=='IMG'&&ed.dom.getAttrib(el,'class').indexOf('mceItem')==-1){if((parent=ed.dom.getParent(el,'div'))&&ed.dom.hasClass(parent,'mceTemp')){ed.dom.remove(parent)}else{if(el.parentNode.nodeName=='A'&&el.parentNode.childNodes.length==1)el=el.parentNode;if(el.parentNode.nodeName=='P'&&el.parentNode.childNodes.length==1)el=el.parentNode;ed.dom.remove(el)}ed.execCommand('mceRepaint');return false}ed.plugins.wordpress._hideButtons()}});tinymce.dom.Event.add("zy_insert_network_video",'click',function(e){ed.windowManager.open({title:"插入网络视频",file:url+'/zy_insert_network_video.html',width:1020,height:610,inline:1},{zy_media_id:ed.selection.getNode().getAttribute("data-zy-media-id")});ed.plugins.wordpress._hideButtons()});tinymce.dom.Event.add("zy_insert_location_video",'click',function(e){ed.windowManager.open({title:"插入本地视频",file:url+'/zy_insert_location_video.html',width:1020,height:610,inline:1},{zy_media_id:ed.selection.getNode().getAttribute("data-zy-media-id")});ed.plugins.wordpress._hideButtons()});tinymce.dom.Event.add("zy_insert_3d",'click',function(e){ed.windowManager.open({title:"插入3d文件",file:url+'/zy_insert_3d.html',width:1020,height:610,inline:1},{zy_media_id:ed.selection.getNode().getAttribute("data-zy-media-id")});ed.plugins.wordpress._hideButtons()});tinymce.dom.Event.add("zy_insert_ppt",'click',function(e){ed.windowManager.open({title:"插入ppt文件",file:url+'/zy_insert_ppt.html',width:1020,height:610,inline:1},{zy_media_id:ed.selection.getNode().getAttribute("data-zy-media-id")});ed.plugins.wordpress._hideButtons()});document.getElementById("wp_editimgbtn").src=url+"/img/zy_image_edit.png";document.getElementById("wp_delimgbtn").src=url+"/img/zy_image_delete.png"},getInfo:function(){return{longname:'zy_insert_media plugin',author:'ty',authorurl:'http://tinymce.moxiecode.com',infourl:'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/example',version:"1.0"}}});tinymce.PluginManager.add('zy_insert_media',tinymce.plugins.zy_insert_media)})();