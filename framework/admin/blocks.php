<?php

require_once('../../../../../wp-load.php');

global $wpbp, $wpdb;

//
$sc = 'wpbp_blocks';

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
    <title>Blocks Content Wizard</title>
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
                var count = $('#count').val();                
                var type = $('#type').val();
                var mask = $('#mask').val();
                var post_type = $('#post_type').val();
                var ids = $('#ids').val();
                var cat = $('#cat').val();
                var author = $('#author').val();
                var columns = $('#columns').val();               
                var nopaging = $('#nopaging').val();                
                var orderby = $('#orderby').val();
                var meta_key = $('#meta_key').val();
                var order = $('#order').val();

               
                var output = '[<?php echo $sc; ?>';
                
                if(set) {
                    output += ' set="' + set + '"';
                }
                if(count) {
                    output += ' count="' + count + '"';
                }                             
                if(type) {
                    output += ' type="' + type + '"';
                }                
                if(mask) {
                    output += ' mask="' + mask + '"';
                }
                if(post_type) {
                    output += ' post_type="' + post_type + '"';
                }
                if(ids) {
                    output += ' ids="' + ids + '"';
                }
                if(cat) {
                    output += ' cat="' + cat + '"';
                }
                if(author) {
                    output += ' author="' + author + '"';
                }
                if(columns) {
                    output += ' columns="' + columns + '"';
                }
                if(nopaging) {
                    output += ' nopaging="' + nopaging + '"';
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
            <label class="control-label" for="type">Template</label>
            <div class="controls">
                <select id="type">
                    <option value=""></option>
                    <?php foreach( $wpbp->templates as $name => $template ): ?>
                    <option value="<?php echo $name; ?>"><?php echo $name; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>                 
        
        <div class="control-group">
            <label class="control-label" for="mask">Box Mask</label>
            <div class="controls">
                <select id="mask">
                    <option value=""></option>
                    <?php asort( $wpbp->masks ); foreach( $wpbp->masks as $name => $mask ): ?>
                    <option value="<?php echo $name; ?>"><?php echo $name; ?></option>
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
            <label class="control-label" for="ids">IDs</label>
            <div class="controls">
                <input type="text" class="input-xlarge" id="ids" placeholder="Specify IDs of items to include...">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="cat">Categories</label>
            <div class="controls">
                <input type="text" class="input-xlarge" id="cat" placeholder="Specify category IDs of items to include...">
            </div>
        </div>
        
        <div class="control-group">
            <label class="control-label" for="tag">Tags</label>
            <div class="controls">
                <input type="text" class="input-xlarge" id="tag" placeholder="Specify tags of items to include...">
            </div>
        </div>
 
        <div class="control-group wpbp_blocks wpbp_featured_list">
            <label class="control-label" for="author">Authors</label>
            <div class="controls">
                <input type="text" class="input-xlarge" id="author" placeholder="Specify author IDs of items to include...">
            </div>
        </div>

        <div class="control-group">                
            <label class="control-label" for="columns">Columns</label>
            <div class="controls">
                <select id="columns">
                    <option value=""></option>
                    <?php for( $i = 1; $i <= 6; $i++ ): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
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
            <label class="control-label" for="nopaging">No Paging</label>
            <div class="controls">
                <select id="nopaging">
                    <option value="" selected="selected"></option>
                    <option value="false">False</option>
                </select>
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
        
        <a href="javascript:ShortcodeTiny.insert(ShortcodeTiny.e)" class="bttn">Insert Shortcode</a>
                                                      
    </form>

</body>
</html>
