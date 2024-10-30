tinymce.create('tinymce.plugins.roundabout', {
    init : function(ed, url) {
        ed.addCommand('cmd_roundabout', function() {
            ed.windowManager.open({
                file : url + '/../roundabout.php',
                width : 600 + parseInt(ed.getLang('shortcode.delta_width', 0)),
                height : 600 + parseInt(ed.getLang('shortcode.delta_height', 0)),
                inline : 1
            }, {
                plugin_url : url
            });
        });
        ed.addButton('roundabout', {
            title : 'Roundabout Content Wizard', 
            cmd : 'cmd_roundabout', 
            image: url + '/../images/roundabout.png' 
        });
    }
});
tinymce.PluginManager.add('roundabout', tinymce.plugins.roundabout);