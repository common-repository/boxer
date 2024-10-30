<?php

global $wpbp, $post;

$custom = get_post_custom( $post->ID );

?>
 
<div class="wpbloxx-options wrap">     

    <div id="cow-tabs" class="postbox">
        
        <ul>
            <?php foreach ( $wpbp->options as $group_name => $option ) : ?>
                <?php $header = explode( "_", $group_name ); ?>
                <li><a href="#<?php echo $group_name; ?>"><?php foreach( $header as $h ) { echo ucfirst( $h ). ' '; } ?></a></li>
            <?php endforeach; ?> 
        </ul>
         
        <?php foreach ( $wpbp->options as $group_name => $option ) : ?>
            <?php $header = explode( "_", $group_name ); ?>
            <div id="<?php echo $group_name; ?>">

                <?php if ( $group_name == "block_masks" ): ?>
                    <div id="apply_style_message"></div>
                    <table cellspacing="0" class="widefat form-table">
                        <tbody>
                            <tr valign="top">
                                <th scope="row">
                                    <label for="styles_combo"><?php _e('Choose a Mask:', WPBOXERPRO_ADMIN_TEXTDOMAIN); ?></label>
                                </th>
                                <td>
                                    <select name="styles_combo" id="styles_combo"></select>
                                    <a href="#" class="bttn" data-action="apply_mask"><?php _e('Apply', WPBOXERPRO_ADMIN_TEXTDOMAIN); ?> <i class="bs-icon icon-ok"></i></a>
                                    <?php if ( $wpbp->get_setting("general", "tooltips") == "on" ): ?>
                                    <span class="label label-help pull-right"><?php _e('Apply the selected block mask to the current content block', WPBOXERPRO_ADMIN_TEXTDOMAIN); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    <label for='save_style_text'><?php _e('Mask name:', WPBOXERPRO_ADMIN_TEXTDOMAIN); ?></label>
                                </th>
                                <td>
                                    <input type="text" name="save_style_text" id="save_style_text" value="">
                                    <a href="#" class="bttn" data-action="save_mask"><?php _e('Save', WPBOXERPRO_ADMIN_TEXTDOMAIN); ?> <i class="bs-icon icon-check"></i></a>
                                    <?php if ( $wpbp->get_setting("general", "tooltips") == "on" ): ?>
                                    <span class="label label-help pull-right"><?php _e('Store the settings of the current content block into a block mask', WPBOXERPRO_ADMIN_TEXTDOMAIN); ?></span>                     
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>               
                <?php endif; ?>
                
                <table cellspacing="0" class="widefat form-table">
                    <tbody>                
                        <?php foreach ( $option as $option_name => $value ): ?>
                        
                        <?php if ( $option_name == "impexp" ): ?>
                        <tr valign="top">
                            <th scope="row">
                                <label for="test">Import/Export</label>
                            </th>
                            <td>
                                <table>
                                <tr>
                                    <td width="50%">
                                        <textarea id="style_import" name="style_import" cols="50" rows="3" placeholder="Copy your JSON encoded string here"></textarea>
                                        <a id="style_import_button" href="#" class="bttn" data-id="<?php echo $post->ID; ?>" data-action="style_import_cb"><?php _e('Import', WPBOXERPRO_ADMIN_TEXTDOMAIN); ?> <i class="bs-icon icon-download"></i></a>                                    
                                    </td>
                                    <td width="50%">
                                        <textarea id="style_export" name="style_export" cols="50" rows="3"></textarea>
                                    </td>
                                </tr>
                                </table>                               
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php
                            // Determine the ID for the current field 
                            $id = $group_name. '['. $option_name. ']';

                            // Get custom field values
                            if ( isset( $custom[$group_name. '_'. $option_name] ) ) {
                                // If a value is found, use it !                                
                                $val = $custom[$group_name. '_'. $option_name][0];
                                if ( $group_name. '_'. $option_name == 'box_content_links' ) {
                                    $val = unserialize(get_post_meta($post->ID, 'box_content_links', true));    
                                }            
                            } else {
                                // Use the default value
                                $val = isset($value['std']) ? $value['std'] : '';           
                            }
           
                            switch ( isset($value['type']) ? $value['type'] : 'text' ) {                
                                case 'text': 
?>
                                    <tr valign="top">
                                        <th scope="row">
                                            <label for="<?php echo $id; ?>"><?php echo isset($value['name']) ? $value['name'] : ''; ?></label>
                                        </th>
                                        <td>
                                            <span><?php echo isset($value['before'])?$value['before']:''; ?></span>
                                            <input type="text" name="<?php echo $id; ?>" value='<?php echo stripslashes($val); ?>' size="<?php echo $value['size']; ?>" />
                                            <span><?php echo isset($value['after'])?$value['after']:''; ?></span>
                                            <?php if ( ! empty( $value['desc'] ) && $wpbp->get_setting("general", "tooltips") == "on" ): ?>
                                            <span class="label label-help pull-right"><?php echo $value['desc']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
<?php
                                    break;
                             
                                case 'range': 
?>
                                    <tr valign="top">
                                        <th scope="row">
                                            <label for="<?php echo $id; ?>"><?php echo $value['name']; ?></label>
                                        </th>
                                        <td>
                                            <div class="range-input-wrap">                                                
                                                <span><?php echo isset($value['before']) ? $value['before'] : ''; ?></span>
                                                <input class="range" type="range" min="<?php echo $value['min']; ?>" max="<?php echo $value['max']; ?>" step="<?php echo $value['step']; ?>" name="<?php echo $id; ?>" value="<?php echo stripslashes($val); ?>" />
                                                <span><?php echo isset($value['after']) ? $value['after'] : ''; ?></span>
                                            </div> 
                                            <?php if ( ! empty( $value['desc'] ) && $wpbp->get_setting("general", "tooltips") == "on" ): ?>
                                            <span class="label label-help pull-right"><?php echo $value['desc']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
<?php
                                    break;
                                    
                                case 'color':
?>
                                    <tr valign="top">
                                        <th scope="row">
                                            <label for="<?php echo $id; ?>"><?php echo $value['name']; ?></label>
                                        </th>
                                        <td>
                                            <div class="wpbp_colorSelector" id="_<?php echo $id; ?>_picker"><div></div></div>
                                            <input class="wpbp_color_picker" type="text" id="<?php echo $id; ?>_color" name="<?php echo $id; ?>_color" value="<?php echo $val; ?>" size="<?php echo $value['size']; ?>" maxlength="7" />
                                            <?php if ( ! empty( $value['desc'] ) && $wpbp->get_setting("general", "tooltips") == "on" ): ?>
                                            <span class="label label-help pull-right"><?php echo $value['desc']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
<?php
                                    break;
                                    
                                case 'textarea':
?>
                                    <tr valign="top">
                                        <th scope="row">
                                            <label for="<?php echo $id; ?>"><?php echo $value['name']; ?></label>
                                        </th>
                                        <td>
                                            <span><?php echo isset($value['before'])?$value['before']:''; ?></span>
                                            <textarea name="<?php echo $id; ?>"><?php echo $val; ?></textarea>
                                            <span><?php echo isset($value['after']) ? $value['after'] : ''; ?></span>
                                            <?php if ( ! empty( $value['desc'] ) && $wpbp->get_setting("general", "tooltips") == "on" ): ?>
                                            <span class="label label-help pull-right"><?php echo $value['desc']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>                    
<?php
                                    break;
                                    
                                case 'radio': 
?>
                                    <tr valign="top">
                                        <th scope="row">
                                            <label for="<?php echo $id; ?>"><?php echo $value['name']; ?></label>
                                        </th>
                                        <td class='radio_option_set'>
                                            <div id="templates">
                                                <?php foreach ( $value['options'] as $opt ) : ?>
                                                <input type="radio" name="<?php echo $id; ?>" value="<?php echo $opt; ?>" <?php if ( $val == $opt ) echo 'checked="checked"'; ?>> <?php if ( $value['images'] ): ?><img src='<?php echo $wpbp->img_uri; ?>box-type-<?php echo $opt ?>.png' alt='box-type-<?php echo $opt ?>' /><?php else: ?><?php echo $value['option_prefix']. $opt; ?><?php endif; ?>     
                                                <?php endforeach; ?>                                            
                                            </div>
                                            <?php if ( ! empty( $value['desc'] ) && $wpbp->get_setting("general", "tooltips") == "on" ): ?>
                                            <span class="label label-help pull-right"><?php echo $value['desc']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr> 
<?php
                                    break;
                                    
                                case 'select':
?>
                                    <tr valign="top">
                                        <th scope="row">
                                            <label for="<?php echo $id; ?>"><?php echo $value['name']; ?></label>
                                        </th>
                                        <td>
                                            <div style='width: 200px;' class="inline">
                                            <span><?php echo isset($value['before'])?$value['before']:''; ?></span>
                                            <select id="<?php echo $id; ?>" name="<?php echo $id; ?>">
                                                <?php foreach ( $value['options'] as $v ) : ?>                        
                                                    <option value='<?php echo $v; ?>'<?php if ( $val == $v ) echo ' selected="selected"'; ?>><?php echo $v; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <span><?php echo isset($value['after']) ? $value['after'] : ''; ?></span>
                                            </div>
                                            <?php if ( ! empty( $value['desc'] ) && $wpbp->get_setting("general", "tooltips") == "on" ): ?>
                                            <span class="label label-help pull-right"><?php echo $value['desc']; ?></span>
                                            <?php endif; ?>                                           
                                        </td>
                                    </tr>
<?php
                                    break;
                                
                                case 'multiselect':
?>
                                    <tr valign="top">
                                        <th scope="row">
                                            <label for="<?php echo $id; ?>"><?php echo $value['name']; ?></label>
                                        </th>
                                        <td>
                                            <select data-placeholder="<?php echo $value['placeholder']; ?>" class="chosen" name="<?php echo $id.'[]' ?>" multiple  style="height: auto;width:350px;" >
                                                <option value=""></option>
<?php                                       
                                                $val = explode( " ", $val );                                            
                                                foreach( $value['options'] as $key => $option ) {
                                                    if ( is_array( $option ) ) {
                                                        echo '<optgroup label="' . $key . '">';
                                                        foreach( $option as $k => $o ) {
                                                            echo '<option value="' . $o . '"';
                                                            if ( is_array( $val ) && in_array( $o, $val ) ) {
                                                                echo ' selected="selected"';
                                                            }
                                                            echo '>' . $o . '</option>';
                                                        }
                                                        echo "</optgroup>";
                                                    } else {
                                                        echo '<option value="' . $option . '"';
                                                        if ( is_array( $val ) && in_array( $option, $val ) ) {
                                                            echo ' selected="selected"';
                                                        }
                                                        echo '>' . $option . '</option>';
                                                    }
                                                }
?>                                          
                                            </select>
                                            <?php if ( ! empty( $value['desc'] ) && $wpbp->get_setting("general", "tooltips") == "on" ): ?>
                                            <span class="label label-help pull-right"><?php echo $value['desc']; ?></span>
                                            <?php endif; ?>                                  
                                        </td>
                                    </tr>
<?php                          
                                    break;
                                
                                case 'linktoggle':
?>                                
                                   <tr valign="top">
                                   <th scope="row">
                                        <?php echo $value['name']; ?>          
                                   </th> 
                                   <td>
<?php                               
                                    $init = false;
                                    
                                    if ( $val == '#' ){
                                        $init = true;
                                        $linktoggle_keys[0] = "#";    
                                    } 
                                    else {
                                        $linktoggle_keys = explode( ',', $val['linktoggle_keys'] );  
                                    }
                                    
                                    $key_count = count( $linktoggle_keys );    
                         
?>                                    
                                    <div class="links_option_set">
                                        <div class="add_menu">
                                            <a href="#" class="button bttn cow_add_menu" title=""><i class="bs-icon icon-plus"></i> <?php echo __( 'Add Link', WPBOXERPRO_ADMIN_TEXTDOMAIN ); ?></a>                     
                                        </div>
                                    
                                        <div class="clear menu_clear"<?php echo ( $init == true ? ' style="display:none;"' : '' ); ?>></div>
                                    
                                    <?php if( $init == true ): ?>
                                        <ul class="menu-to-edit menu" style="display:none;">
                                            <li></li>
                                        </ul>
                                    <?php endif; ?>
<?php                                    
                                    $i = 1;

                                    foreach( $linktoggle_keys as $key ) {
                                        if( ( $i == 1 ) && ( $init == false ) ): ?>
                                            <ul class="menu-to-edit menu">
                                        <?php endif; ?>
                                        
                                        <?php if ( $i == $key_count ): ?>
                                            <ul class="sample-to-edit menu" style="display:none;">
                                        <?php endif; ?>
<?php                                        
                                        $_id = $key;
                                        $link_data = ( ( $_id != '#' ) && ( isset( $val[$key] ) ) ) ? $val[$key] : '';
                                        
                                        $name = $group_name . '[links][' . $_id . ']';
                                        $link_url = ( !empty( $link_data['link_url'] ) ) ? esc_url(stripslashes( $link_data['link_url'] ) ) : '';
                                        $link_target = ( !empty( $link_data['link_target'] ) ) ? stripslashes( $link_data['link_target'] ) : '_blank'; 
                                        $title = ( !empty( $link_data['title'] ) ) ? stripslashes( $link_data['title'] ) : '';
                                        $link_style = ( !empty( $link_data['link_style'] ) ) ? stripslashes( $link_data['link_style'] ) : '';
                                        $link_icon = ( !empty( $link_data['link_icon'] ) ) ? stripslashes( $link_data['link_icon'] ) : '';
                                        $icon_color = ( !empty( $link_data['icon_color'] ) ) ? stripslashes( $link_data['icon_color'] ) : 'light';
                                        $lightbox = ( !empty( $link_data['lightbox'] ) ) ? stripslashes( $link_data['lightbox'] ) : 'off';
                                        $link_text = ( !empty( $link_data['link_text'] ) ) ? stripslashes( $link_data['link_text'] ) : 'Link '. $key; ?>
                                        
                                        
                                        <li id="links-menu-item-<?php echo $_id; ?>" class="menu-item menu-item-edit-inactive">
                                        
                                        <!-- menu handle -->
                                        <dl class="menu-item-bar">
                                            <dt class="menu-item-handle">
                                                <span class="item-title"><?php echo sprintf( __( 'Link %1$s', WPBOXERPRO_ADMIN_TEXTDOMAIN ), $i ); ?></span>
                                                <span class="item-controls">
                                                    <a href="links-menu-item-settings-<?php echo $_id; ?>" title="Edit Menu Item" id="edit-<?php echo $_id; ?>" class="item-edit"><?php echo __( 'Edit Menu Item', WPBOXERPRO_ADMIN_TEXTDOMAIN ); ?></a>
                                                </span>
                                            </dt>
                                        </dl>
                                        
                                        <!-- menu settings -->
                                        <div id="links-menu-item-settings-<?php echo $_id; ?>" class="menu-item-settings" style="display:none;">
                                        
                                        <!-- link url -->
                                        <p class="description wide floatleft">
                                            <label for="edit-menu-link-url-<?php echo $_id; ?>"><?php echo __( 'Link URL', WPBOXERPRO_ADMIN_TEXTDOMAIN ); ?><br />
                                                <input type="text" name="<?php echo $name; ?>[link_url]" value="<?php echo $link_url; ?>" id="edit-menu-link-url-<?php echo $_id; ?>" class="widefat" />
                                            </label>                                 
                                        </p>

                                        <!-- link target -->
                                        <p class="description floatleft">
                                            <label for="edit-menu-link-target-<?php echo $_id; ?>"><?php echo __( 'Link Target', WPBOXERPRO_ADMIN_TEXTDOMAIN ); ?><br />
                                                <select id="edit-menu-link-target-<?php echo $_id; ?>" name="<?php echo $name; ?>[link_target]">
    <?php                                           $link_targets = array( "_blank", "_self", "_parent", "_top" );
                                                    foreach( $link_targets as $target ):?>                                                                                                                                             
                                                        <option value="<?php echo $target; ?>"<?php if($target == $link_target) echo " selected='selected'"; ?>><?php echo $target; ?></option>
                                                    <?php endforeach; ?> 
                                                </select>
                                            </label>
                                       </p>                                        
                                       
                                       <p class="description thin floatleft" style='clear:left;'>
                                            <label for="edit-menu-link-text-<?php echo $_id; ?>"><?php echo __( 'Link Text', WPBOXERPRO_ADMIN_TEXTDOMAIN ); ?><br />
                                                <input type="text" name="<?php echo $name; ?>[link_text]" value="<?php echo $link_text; ?>" id="edit-menu-link-text-<?php echo $_id; ?>" class="widefat" />
                                            </label>
                                       </p>
                                       
                                        <!-- link title  -->
                                        <p class="description wide floatleft">
                                            <label for="edit-menu-title-<?php echo $_id; ?>"><?php echo __( 'Title', WPBOXERPRO_ADMIN_TEXTDOMAIN ); ?><br />
                                                <input type="text" name="<?php echo $name; ?>[title]" value="<?php echo $title; ?>" id="edit-menu-title-<?php echo $_id; ?>" class="widefat" />
                                            </label>
                                        </p>
                                       
                                       <!-- link style -->
                                        <p class="description floatleft" style='clear:left;'>
                                            <label for="edit-menu-link-style-<?php echo $_id; ?>"><?php echo __( 'Link Style', WPBOXERPRO_ADMIN_TEXTDOMAIN ); ?><br />
                                                <select id="edit-menu-link-style-<?php echo $_id; ?>" name="<?php echo $name; ?>[link_style]">
                                                    <option value=""></option>
    <?php                                       
                                                    $link_styles = array( "link", "danger", "disabled", "info", "primary", "success", "warning",
                                                                          "eyecandy", "apple", "black-pill", "pure-web", "origin", "metro" );
                                                    asort($link_styles);
                                                    foreach( $link_styles as $style ):                                                                                               
    ?>                                          
                                                        <option value="<?php echo $style; ?>"<?php if($style == $link_style) echo " selected='selected'"; ?>><?php echo $style; ?></option>
                                                    <?php endforeach; ?> 
                                                </select>
                                            </label>
                                       </p>
 
                                       <!-- link icon -->
                                        <p class="description floatleft">
                                            <label for="edit-menu-link-icon-<?php echo $_id; ?>"><?php echo __( 'Link Icon', WPBOXERPRO_ADMIN_TEXTDOMAIN ); ?><br />
                                                <select id="edit-menu-link-icon-<?php echo $_id; ?>" name="<?php echo $name; ?>[link_icon]">
                                                    <option value=""></option>
    <?php                                       
                                                    asort($wpbp->bootstrap_icons);
                                                    foreach( $wpbp->bootstrap_icons as $icon ):                                                                                              
    ?>                                          
                                                        <option value="<?php echo $icon; ?>"<?php if($icon == $link_icon) echo " selected='selected'"; ?>><?php echo $icon; ?></option>
                                                    <?php endforeach; ?> 
                                                </select>
                                            </label>
                                       </p>

                                       <!-- icon color -->
                                        <p class="description floatleft">
                                            <label for="edit-menu-icon-color-<?php echo $_id; ?>"><?php echo __( 'Icon Color', WPBOXERPRO_ADMIN_TEXTDOMAIN ); ?><br />
                                                <select id="edit-menu-icon-color-<?php echo $_id; ?>" name="<?php echo $name; ?>[icon_color]">
    <?php                                       
                                                    $icon_colors = array( "light", "dark" );
                                                    foreach( $icon_colors as $color ):                                                                                              
    ?>                                          
                                                        <option value="<?php echo $color; ?>"<?php if($color == $icon_color) echo " selected='selected'"; ?>><?php echo $color; ?></option>
                                                    <?php endforeach; ?> 
                                                </select>
                                            </label>
                                       </p>
                                      
                                        <p class="description floatleft">
                                            <label for="edit-menu-lightbox-<?php echo $_id; ?>"><?php echo __( 'Lightbox', WPBOXERPRO_ADMIN_TEXTDOMAIN ); ?><br />
                                                <select id="edit-menu-lightbox-<?php echo $_id; ?>" name="<?php echo $name; ?>[lightbox]">
    <?php                                       
                                                    $lb_options = array( __("False", WPBOXERPRO_ADMIN_TEXTDOMAIN),  __("True", WPBOXERPRO_ADMIN_TEXTDOMAIN) );
                                                    foreach( $lb_options as $lb_option ):                                                                                              
    ?>                                          
                                                        <option value="<?php echo $lb_option; ?>"<?php if($lb_option == $lightbox) echo " selected='selected'"; ?>><?php echo $lb_option; ?></option>
                                                    <?php endforeach; ?> 
                                                </select>
                                            </label>
                                       </p>
                                                                                                                    
                                        <!-- menu item actions -->
                                        <div class="menu-item-actions wide submitbox">
                                            <a id="delete-links-menu-item-<?php echo $_id; ?>" class="button bttn bttn-danger links_deletion" href="#" title=""><i class="bs-icon icon-white icon-minus"></i> <?php echo __( 'Remove', WPBOXERPRO_ADMIN_TEXTDOMAIN ); ?></a>                                          
                                        </div>
                                        
                                        </div><!-- #links-menu-item-settings-## -->
                                        </li>
                                        
                                        <?php if( $i == $key_count-1 ): ?>
                                            </ul><!-- .menu-to-edit -->
                                        <?php endif; ?>
                                        
                                        <?php if( $i == $key_count ): ?>
                                            </ul><!-- .sample-to-edit -->
                                        <?php endif; 
                                        
                                        $i++; 
                                    }   ?>
                                    
                                        <input type="hidden" name="<?php echo $group_name; ?>[links][linktoggle_keys]" value="<?php echo $val['linktoggle_keys']; ?>" class="menu-keys" />

                                    </div><!-- .links_option_set -->
                                                                      
                                    </td>
                                    </tr> 
<?php                                
                                    break;
                                                                      
                                case 'check': 
?>
                                    <tr valign="top">
                                        <th scope="row">
                                            <label for="<?php echo $id; ?>"><?php echo $value['name']; ?></label>
                                        </th>
                                        <td class="checkbox_option_set">
                                            <span><?php echo isset($value['before']) ? $value['before'] : ''; ?></span>
                                            <input type="checkbox" name="<?php echo $id; ?>"<?php if ( $val == 'on' ) echo 'checked="checked"'; ?> />
                                            <span><?php echo isset($value['after']) ? $value['after'] : ''; ?></span>
                                            <?php if ( ! empty( $value['desc'] ) && $wpbp->get_setting("general", "tooltips") == "on" ): ?>
                                            <span class="label label-help pull-right"><?php echo $value['desc']; ?></span>
                                            <?php endif; ?> 
                                        </td>
                                    </tr>        
                        <?php } ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>        
            </div> 
        
        <?php endforeach; ?> 
   
        <input type="hidden" name='updated' value='true' />
        <input type="hidden" name='postid' value='<?php echo $post->ID; ?>' />
    
    </div>
</div>