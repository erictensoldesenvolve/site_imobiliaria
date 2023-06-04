<?php
/**
 * The template for Element Listings Results Map.
 * This is the template that elementor element map with markers of listings, show results
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="wdk-element" id="wdk_el_<?php echo esc_html($id_element);?>">
    <div class="wdk-map" class="wdk_map_results">
        <div id="wdk_map_results_<?php echo esc_html($id_element);?>" style="height:<?php echo esc_attr($settings['conf_custom_map_height']['size']);?>px" class="wdk_map_results <?php echo wmvc_show_data('styles_thmbn_des_type',$settings, '');?> " ></div>
    </div>
</div>
<?php
    $zoom_index = $settings['conf_custom_map_zoom_index']['size'];
?>
<?php
    if($lat == 0)
    {
        $lat = wmvc_show_data('conf_custom_map_center_gps_lat', $settings);
        $lng = wmvc_show_data('conf_custom_map_center_gps_lng', $settings);
    }
?>
<?php
$WMVC = &wdk_get_instance();
$WMVC->model('category_m');
if (!$is_edit_mode)
    ob_start();
?>
 <script>
    var wdk_map ='';
    var wdk_markers = [];
    var wdk_clusters ='';
    var wdk_jpopup_customOptions =
    {
        'maxWidth': 'initial',
        'width': 'initial',
        'className' : 'popupCustom'
    };
    jQuery(document).ready(function($) {
        if(wdk_clusters=='')
            wdk_clusters = L.markerClusterGroup({spiderfyOnMaxZoom: true, showCoverageOnHover: false, zoomToBoundsOnClick: true});
            wdk_map = L.map('wdk_map_results_<?php echo esc_html($id_element);?>', {
            center: ["<?php echo esc_js($lat);?>","<?php echo esc_js($lng);?>"],
            zoom: "<?php echo esc_js($zoom_index);?>",
            scrollWheelZoom: false,
            <?php if($settings['conf_custom_dragging'] == 'yes'):?>
                dragging: true,
            <?php else:?>
                dragging: false,
            <?php endif;?>
            tap: !L.Browser.mobile,
            fullscreenControl: true,
            fullscreenControlOptions: {
                position: 'topleft'
            }
        });     

        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(wdk_map);

        <?php if(!empty($settings['conf_custom_map_style']) && $settings['conf_custom_map_style'] =='custom' && !empty($settings['conf_custom_map_style_self'])):?>
            var positron = L.tileLayer('<?php echo esc_js($settings['conf_custom_map_style_self']);?>').addTo(wdk_map);
        <?php elseif(!empty($settings['conf_custom_map_style']) && $settings['conf_custom_map_style'] !='custom'):?>
            var positron = L.tileLayer('<?php echo esc_js($settings['conf_custom_map_style']);?>').addTo(wdk_map);
        <?php endif;?>
        var auto_marker_size = false;
        <?php foreach($results as $key=>$listing): ?>
        <?php  if(!is_numeric(wmvc_show_data('lng', $listing)))continue;?>
        <?php 
        $pin_icon = "";
        $font_class = "";
        $font_icon = $this->generate_icon($settings['conf_custom_map_pin_icon']);
        $pin_icon = $settings['conf_custom_map_pin']['url'];

        if(!empty(wmvc_show_data('category_id', $listing))){
            $category = $WMVC->category_m->get_data(wmvc_show_data('category_id', $listing));
            if(wmvc_show_data('marker_image_id', $category, false, TRUE, TRUE)){
                $pin_icon = wdk_image_src($category, 'full', NULL,'marker_image_id');
            } else if(!empty(wmvc_show_data('font_icon_code', $category))) {
                $font_class = wmvc_show_data('font_icon_code', $category);
            } 
        } else {
            $font_class = "";
        }

        ?>
        <?php if(!empty($settings['custom_marker_fields']) &&  substr($this->data['settings']['custom_marker_fields'], strpos($this->data['settings']['custom_marker_fields'],'__')+2) == 'first_image'):?>
            auto_marker_size = true;
            var innerMarker = '<div class="wdk_marker-container wdk_marker_label wdk_marker_clear category_id_<?php echo esc_js(wmvc_show_data('category_id', $listing));?>"><img src="<?php echo esc_js(wdk_image_src($listing));?>"></img></div>';

        <?php elseif(!empty($settings['custom_marker_fields']) &&  wdk_field_value (substr($this->data['settings']['custom_marker_fields'], strpos($this->data['settings']['custom_marker_fields'],'__')+2), $listing)):?>
            <?php
                $field_id = substr($this->data['settings']['custom_marker_fields'], strpos($this->data['settings']['custom_marker_fields'],'__')+2); 

                $field_value = '';
                $field_value .= apply_filters( 'wpdirectorykit/listing/field/prefix', wdk_field_option ($field_id, 'prefix'), $field_id);

                /* if price field use like 1l */
                if(wdk_field_option($field_id, 'is_price_format')) {
                    
                    $value = wdk_field_value($field_id, $listing);
                    if($value>=1000) {
                        $value = apply_filters( 'wpdirectorykit/listing/field/value', number_format_i18n(wdk_filter_decimal($value/1000)), $field_id).'k';
                    } else {
                        $value = apply_filters( 'wpdirectorykit/listing/field/value', wdk_field_value_on_type($field_id, $listing), $field_id);
                    }

                    $field_value = $value;

                } else {
                    $field_value .= apply_filters( 'wpdirectorykit/listing/field/value', wdk_field_value_on_type($field_id, $listing), $field_id);
                }

                $field_value .= apply_filters( 'wpdirectorykit/listing/field/suffix',wdk_field_option ($field_id, 'suffix'), $field_id);
            ?>
            auto_marker_size = true;
            var innerMarker = '<div class="wdk_marker-container wdk_marker_label category_id_<?php echo esc_js(wmvc_show_data('category_id', $listing));?>"><?php echo esc_js(strip_tags($field_value));?></div>';
        <?php elseif($pin_icon):?>
            var image = '<?php echo esc_html($pin_icon);?>'; var innerMarker = '<div class="wdk_marker-container wdk_marker-container-image"><img src='+image+'></img></div>';
        <?php elseif($font_icon && empty($font_class)):?> 
            var innerMarker = '<div class="wdk_marker-container category_id_<?php echo esc_js(wmvc_show_data('category_id', $listing));?>"><div class="front wdk_face"><?php echo wdk_viewe($font_icon);?></div><div class="wdk_marker-card"><div class="wdk_marker-arrow"></div></div></div>';
        <?php else:?> 
            var innerMarker = '<div class="wdk_marker-container category_id_<?php echo esc_js(wmvc_show_data('category_id', $listing));?>"><div class="front wdk_face"><i class="<?php echo esc_html($font_class);?>"></i></div><div class="wdk_marker-card"><div class="wdk_marker-arrow"></div></div></div>';
        <?php endif;?>
        wdk_markers.push(wdk_generate_marker_ajax_popup('<?php echo esc_url(admin_url('admin-ajax.php'));?>','<?php echo esc_html(wmvc_show_data('post_id', $listing));?>','<?php echo esc_html(wmvc_show_data('lat', $listing));?>','<?php echo esc_html(wmvc_show_data('lng', $listing));?>',innerMarker, wdk_jpopup_customOptions, auto_marker_size
                    , <?php if(wmvc_show_data('disable_cluster', $settings) == 'yes'):?> false <?php endif;?>));
    <?php endforeach; ?> 
    wdk_map.addLayer(wdk_clusters);
    /* set center */
    if(wdk_markers.length){
        var limits_center = [];
        for (var i in wdk_markers) {
            var latLngs = [ wdk_markers[i].getLatLng() ];
            limits_center.push(latLngs)
        };
        var bounds = L.latLngBounds(limits_center);
        <?php if(wdk_get_option('wdk_fixed_map_results_position') && wdk_get_option('wdk_default_lat') && wdk_get_option('wdk_default_lng')): ?>
            wdk_map.setView(["<?php echo esc_js(wdk_get_option('wdk_default_lat'));?>","<?php echo esc_js(wdk_get_option('wdk_default_lng'));?>"]);
        <?php elseif($settings['enable_custom_gps_center'] == 'yes'): ?>
            wdk_map.setView(["<?php echo esc_js($settings['conf_custom_map_center_gps_lat']);?>","<?php echo esc_js($settings['conf_custom_map_center_gps_lng']);?>"]);
        <?php else: ?>
            wdk_map.fitBounds(bounds);
        <?php endif; ?>
    }
 })
</script>
<?php

if (!$is_edit_mode) {
    $js_content = ob_get_clean();
    $js_content = str_replace(array('</script>','<script>'),'',$js_content );
    wp_add_inline_script( 'wdk-elementor-main', $js_content );
}
?>

