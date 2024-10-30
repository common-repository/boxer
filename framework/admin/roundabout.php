<?php

require_once('../../../../../wp-load.php');

global $wpbp, $wpdb;

//
$sc = 'wpbp_roundabout';

// Get all box sets
$block_groups = $wpdb->get_results( "SELECT $wpdb->terms.slug FROM $wpdb->term_taxonomy INNER JOIN $wpdb->terms ON $wpdb->term_taxonomy.term_id = $wpdb->terms.term_id WHERE $wpdb->term_taxonomy.taxonomy = 'box_sets' ORDER BY $wpdb->terms.slug", ARRAY_A );

// Get all post types
$post_types = get_post_types();
unset( $post_types["page"] );
unset( $post_types["revision"] );
unset( $post_types["template"] );
                                                         
?>

<!DOCTYPE html>
<head>
    <title>Roundabout Content Wizard</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
        input[type="text"],
        textarea,
        select {
            border: 1px solid #ccc;
            background: url("images/input_bg.png") no-repeat scroll 0 0 #fff;
            font-size: 12px;
            -moz-border-radius: 3px;
            -webkit-border-radius: 3px;
            border-radius: 3px;
            padding: 5px 10px 5px 5px;
            }
        input[type="text"]:focus,
        textarea:focus {
            border: 1px solid #999;    
        } 
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
    <script src="../../../../../wp-includes/js/tinymce/tiny_mce_popup.js"></script>
    <script>
        var ShortcodeTiny = {
            e: '',
            init: function(e) {
                ShortcodeTiny.e = e;
                tinyMCEPopup.resizeToInnerSize();
            },
            insert: function createShortcode(e) {
                
                var set = $('#set').val();
                var post_type = $('#post_type').val();
                var count = $('#count').val(); 
                var width = $('#width').val();
                var height = $('#height').val();
                var duration = $('#duration').val();               
                var autoplay = $('#autoplay').val();
                var opacity = $('#opacity').val();              
                var orderby = $('#orderby').val();
                var meta_key = $('#meta_key').val();
                var order = $('#order').val();
                //var use_tt = $('#use_tt').val();
               
                var output = '[<?php echo $sc; ?>';
                
                if(set) {
                    output += ' set="' + set + '"';
                }
                if(post_type) {
                    output += ' post_type="' + post_type + '"';
                }
                if(count) {
                    output += ' count="' + count + '"';
                }                             
                if(width) {
                    output += ' width="' + width + '"';
                }                
                if(height) {
                    output += ' height="' + height + '"';
                }                
                if(duration) {
                    output += ' duration="' + duration + '"';
                }                                           
                if(orderby) {
                    output += ' orderby="' + orderby + '"';    
                }
                if(meta_key) {
                    output += ' meta_key="' + meta_key + '"';    
                }
                if(order) {
                    output += ' order="' + order + '"';    
                }  
//                if(use_tt == 'false') {
//                    output += ' use_tt="' + use_tt + '"';    
//                }
                
                output += ']';

                tinyMCEPopup.execCommand('mceReplaceContent', false, output);
                tinyMCEPopup.close();

            }
        }
        tinyMCEPopup.onInit.add(ShortcodeTiny.init, ShortcodeTiny);

    </script>
</head>
<body>
    <form class="form-horizontal">
        <legend>Select Criteria</legend>
      
        <div class="control-group">
            <label class="control-label" for="set">Block Group</label>
            <div class="controls">
                <select id="set">
                    <option value=""></option>
                    <?php foreach( $block_groups as $group ): ?>
                    <option value="<?php echo $group['slug']; ?>"><?php echo $group['slug']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="post_type">Post Type</label>
            <div class="controls">
                <select id="post_type">
                    <option value=""></option>
                    <?php asort( $post_types ); foreach( $post_types as $name => $type ): ?>
                    <option value="<?php echo $name; ?>"><?php echo $name; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
       
        <div class="control-group">                
            <label class="control-label" for="count">Count</label>
            <div class="controls">
                <select id="count">
                    <option value=""></option>
                    <?php for( $i = 1; $i < 36; $i++ ): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>
        
        <div class="control-group">
            <label class="control-label" for="width">Width</label>
            <div class="controls">
                <input type="text" class="input-small" id="width" placeholder="630"><span> px</span>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="height">Height</label>
            <div class="controls">
                <input type="text" class="input-small" id="height" placeholder="258"><span> px</span>
            </div>
        </div>
               
        <div class="control-group">                
            <label class="control-label" for="autoplay">Autoplay</label>
            <div class="controls">
                <select id="autoplay">
                    <option value="true">True</option>
                    <option value="false">False</option>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="duration">Duration</label>
            <div class="controls">
                <input type="text" class="input-small" id="duration" placeholder="7000"><span> ms</span>
            </div>
        </div>
        
        <div class="control-group">
            <label class="control-label" for="opacity">Opacity</label>
            <div class="controls">
                <input type="text" class="input-small" id="opacity" placeholder="1" min="0.1" max="1"><span> 0.1 - 1</span>
            </div>
        </div>
        
        <div class="control-group">
            <label class="control-label" for="orderby">Order By</label>
            <div class="controls">
                <select id="orderby">
                    <option value=""></option>
                    <option value="none">None</option>
                    <option value="ID">ID</option>
                    <option value="author">Author</option>
                    <option value="title">Title</option>
                    <option value="date">Date Created</option>
                    <option value="modified">Date Modified</option>
                    <option value="rand">Random</option>
                    <option value="comment_count">Comment Count</option>
                    <option value="meta_value">Meta Value</option>
                    <option value="meta_value_num">Numeric Meta Value</option>
                </select>
            </div>
        </div>
        
        <div class="control-group">
            <label class="control-label" for="meta_key">Meta Key</label>
            <div class="controls">
                <input type="text" class="input-xlarge" id="meta_key" placeholder="Specify a meta key to sort by">
            </div>
        </div>
        
        <div class="control-group">                
            <label class="control-label" for="order">Order</label>
            <div class="controls">
                <select id="order">
                    <option value="" selected="selected"></option>
                    <option value="asc">ASC</option>
                    <option value="desc">DESC</option>
                </select>
            </div>
        </div>
       
        <div class="control-group">                
            <label class="control-label" for="autoplay">Resize Images</label>
            <div class="controls">
                <select id="use_tt">
                    <option value="true">True</option>
                    <option value="false">False</option>
                </select>
            </div>
        </div>
         
        <a href="javascript:ShortcodeTiny.insert(ShortcodeTiny.e)" class="bttn">Insert Shortcode</a>
                                                      
    </form>

</body>
</html>
