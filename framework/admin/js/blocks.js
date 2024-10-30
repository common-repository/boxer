tinymce.create('tinymce.plugins.blocks', {
    init : function(ed, url) {
        ed.addCommand('cmd_blocks', function() {
            ed.windowManager.open({
                file : url + '/../blocks.php',
                width : 600 + parseInt(ed.getLang('shortcode.delta_width', 0)),
                height : 600 + parseInt(ed.getLang('shortcode.delta_height', 0)),
                inline : 1
            }, {
                plugin_url : url
            });
        });
        ed.addButton('blocks', {
            title : 'Blocks Content Wizard', 
            cmd : 'cmd_blocks', 
            image: url + '/../images/blocks.png' 
        });
    }
});
tinymce.PluginManager.add('blocks', tinymce.plugins.blocks);


