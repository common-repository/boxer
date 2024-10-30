tinymce.create('tinymce.plugins.slider', {
    init : function(ed, url) {
        ed.addCommand('cmd_slider', function() {
            ed.windowManager.open({
                file : url + '/../slider.php',
                width : 600 + parseInt(ed.getLang('shortcode.delta_width', 0)),
                height : 600 + parseInt(ed.getLang('shortcode.delta_height', 0)),
                inline : 1
            }, {
                plugin_url : url
            });
        });
        ed.addButton('slider', {
            title : 'Slider Content Wizard', 
            cmd : 'cmd_slider', 
            image: url + '/../images/slider.png' 
        });
    }
});
tinymce.PluginManager.add('slider', tinymce.plugins.slider);