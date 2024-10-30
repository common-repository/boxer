<?php

global $wpbp;

if ( isset( $_POST['updated'] ) ) { 

    // Determine which option group has been updated
    $option_group = $_POST['updated'];
    
    // Get all posted values from option group
    $new_options = $_POST[$option_group];
    
    // Add a value for boolean options (which do NOT get posted)
    foreach ( $wpbp->settings[$option_group] as $opt => $prop ) {
        if ( strtolower( $prop['type'] ) == 'check' )
            $new_options[$opt] = $new_options[$opt] ? 'on' : 'off';           
    }

    // Loop through posted values and add them to our options array
    foreach ( $new_options as $name => $option )
        $wpbp->settings[$option_group][$name]['value'] = esc_attr( $option );        
    
    update_option( $wpbp->slug. '_settings', $wpbp->settings );

    echo '<div id="message" class="updated fade"><p><strong>' . __('Settings saved.', WPBOXER_ADMIN_TEXTDOMAIN) . '</strong></p></div>';        
}

?>

<div class="wpbloxx-options wrap">

    <div class="icon32" id="icon-options-general"><br></div>
    <h2><?php _e('WP Boxer Settings', WPBOXER_ADMIN_TEXTDOMAIN); ?></h2>

    <?php foreach ( $wpbp->settings as $group_name => $option ) : ?>
        
    <?php if ( $group_name[0] == '_' ) continue; ?>
    
    <!--<div class="theme-options-group">-->
        <table cellspacing="0" class="widefat form-table">
            <thead>
                <tr>
                    <th scope="row" colspan="2"><strong><?php $header = explode( "_", $group_name ); foreach( $header as $h ) { echo ucfirst( $h ). ' '; } ?></strong></th>
                </tr>
            </thead>
            <tbody>                 
                <form method="post">    
            
                <!-- Loop through all options in the current option group -->
                <?php foreach ( $option as $option_name => $value ): 

                    // Determine the ID for the current field 
                    $id = $group_name. '['. $option_name. ']';

                    if ( isset( $value['value'] ) ) {
                        $val = $value['value'];    
                    } 
                    else {
                        $val = $value['std'];    
                    }
                               
                    switch ( $value['type'] ) { 
                        case 'text': 
?>
                            <tr valign="top">
                                <th scope="row"><label for="<?php echo $id; ?>"><?php echo $value['name']; ?></label></th>
                                <td>
                                    <?php echo $value['before']; ?><input type="text" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value='<?php echo stripslashes($val); ?>' size="<?php echo $value['size']; ?>" /><?php echo $value['after']; ?>                                    
                                    <span class="label label-help pull-right"><?php echo $value['desc']; ?></span>
                                </td>
                            </tr>                    
<?php
                            break;
                            
                        case 'color':
?>
                            <tr valign="top">
                                <th scope="row"><label for="<?php echo $id; ?>"><?php echo $value['name']; ?></label></th>
                                <td>
                                    <?php echo $value['before']; ?><input class="color_picker" type="text" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $val; ?>" size="<?php echo $value['size']; ?>" maxlength="6" /><?php echo $value['after']; ?>
                                    <span class="label label-help pull-right"><?php echo $value['desc']; ?></span>
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
                                        <span><?php echo $value['before']; ?></span><input class="range" type="range" min="<?php echo $value['min']; ?>" max="<?php echo $value['max']; ?>" step="<?php echo $value['step']; ?>" name="<?php echo $id; ?>" value="<?php echo stripslashes($val); ?>" /><span><?php echo $value['after']; ?></span>
                                    </div> 
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
                                <th scope="row"><label for="<?php echo $id; ?>"><?php echo $value['name']; ?></label></th>
                                <td>
                                    <?php echo $value['before']; ?><textarea id="<?php echo $id; ?>" name="<?php echo $id; ?>"><?php echo $val; ?></textarea><?php echo $value['after']; ?>
                                    <span class="label label-help pull-right"><?php echo $value['desc']; ?></span>
                                </td>
                            </tr>                    
<?php
                            break;
                            
                        case 'radio': 
?>
                            <tr valign="top">
                                <th scope="row"><label for="<?php echo $id; ?>"><?php echo $value['name']; ?></label></th>
                                <td class='radio_option_set'>
                                    <?php foreach ( $value['options'] as $opt ) : ?>
                                    <input type="radio" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $opt; ?>" <?php if ( $val == $opt ) echo 'checked="checked"'; ?>> <?php if ( $value['images'] ): ?><img src='<?php echo $wpbp->img_uri; ?>box-type-<?php echo $opt ?>.png' alt='box-type-<?php echo $opt ?>' /><?php else: ?><?php echo $value['option_prefix']. $opt; ?><?php endif; ?>     
                                    <?php endforeach; ?> 
                                    <span class="label label-help pull-right"><?php echo $value['desc']; ?></span>                               
                                </td>
                            </tr> 
<?php
                            break;
                            
                        case 'select':
?>
                            <tr valign="top">
                                <th scope="row"><label for="<?php echo $id; ?>"><?php echo $value['name']; ?></label></th>
                                <td>
                                    <?php echo $value['before']; ?><select id="<?php echo $id; ?>" name="<?php echo $id; ?>">
                                        <?php foreach ( $value['options'] as $k => $v ) : ?>                        
                                        <option value='<?php echo $k; ?>'<?php if ( $val == $k ) echo ' selected="selected"'; ?>><?php echo $v; ?></option>
                                        <?php endforeach; ?>
                                    </select><?php echo $value['after']; ?>
                                    <span class="label label-help pull-right"><?php echo $value['desc']; ?></span>
                                </td>
                            </tr>
<?php
                            break;
                            
                        case 'multiselect':
?>
                            <tr valign="top">
                                <th scope="row"><label for="<?php echo $id; ?>"><?php echo $value['name']; ?></label></th>
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
                                    <span class="label label-help pull-right"><?php echo $value['desc']; ?></span>                                  
                                </td>
                            </tr>
<?php                          
                            break;  
                                      
                        case 'check': 
?>
                            <tr valign="top">
                                <th scope="row"><label for="<?php echo $id; ?>"><?php echo $value['name']; ?></label></th>
                                <td class="checkbox_option_set">
                                    <?php echo $value['before']; ?><input type="checkbox" id="<?php echo $id; ?>" name="<?php echo $id; ?>"<?php if ( $val == 'on' ) echo 'checked="checked"'; ?> /><?php echo $value['after']; ?>
                                    <span class="label label-help pull-right"><?php echo $value['desc']; ?></span> 
                                </td>
                            </tr>       
                    <?php } ?>
                    <?php endforeach; ?>                           
                </tbody>
            </table>
            
            <table class="widefat form-table">
                <thead>
                    <tr>
                        <th scope="row" colspan="2"><strong><?php _e('Plugin Management', "wpboxerpro"); ?></strong></th>
                    </tr>
                </thead>
                <tbody>           

                    <tr valign="top">
                        <th scope="row"><label><?php _e('Upgrade From Older Version', "wpboxerpro"); ?></label></th>
                        <td>
                            <a href="#" data-action="upgrade" class="bttn bttn-danger"><?php _e('Upgrade', "wpboxerpro"); ?> <i class="bs-icon icon-repeat icon-white"></i></a>
                            <span data-message="upgrade"></span>
                            <span class="label label-important pull-right"><?php _e('Upgrade boxes from the free version of Boxer to the Pro version. Remember, this action cannot be undone!', "wpboxerpro"); ?></span>
                        </td>
                    </tr>

                </tbody>
            </table>
            
            <input type="hidden" name='updated' value='<?php echo $group_name; ?>' />
            <p class='submit'><input type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" /></p>                     
        </form>             
<!--    </div>-->
    <?php endforeach; ?>   
</div>