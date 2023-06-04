<?php
/**
 * The template for Element Listing Map.
 * This is the template that elementor element map
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<?php
if(empty($lng) || empty($lat)) {
    ?>
    <div class="">
        <p class="wdk_alert wdk_alert-danger"><?php echo esc_html__('Missing address', 'wpdirectorykit');?></p>
    </div>
    <?php
    return false;
}
?>

<div class="wdk-element" id="wdk_el_<?php echo esc_html($id_element);?>">
    <div class="wdk-map infobox-basic">
        <div id="wdk_map_results_<?php echo esc_html($id_element);?>" style="height:<?php echo esc_attr($settings['conf_custom_map_height']['size']);?>px" ></div>
    </div>
</div>
<?php
    $zoom_index = $settings['conf_custom_map_zoom_index']['size'];
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

    <?php if(is_numeric($lng)):?>
        <?php 
        $font_class = "fa fa-home";
        $font_icon = $this->generate_icon($settings['conf_custom_map_pin_icon']);
        $pin_icon = $settings['conf_custom_map_pin']['url'];

        if(!empty(wdk_field_value('category_id', $wdk_listing_id))){
            $category = $WMVC->category_m->get_data(wdk_field_value('category_id', $wdk_listing_id));
            if(wmvc_show_data('marker_image_id', $category, false, TRUE, TRUE)){
                $pin_icon = wdk_image_src($category, 'full', NULL,'marker_image_id');
            } else if(!empty(wmvc_show_data('font_icon_code', $category))) {
                $font_class = wmvc_show_data('font_icon_code', $category);
            } 
        } else {
            $font_class = "";
        }

        $wdk_popup_content = '<div class="infobox map-box wdk-infobox-basic">'
                                .'<h3 class="title">'.esc_html(wdk_field_value('post_title', $wdk_listing_id)).'</h3>'
                                .'<p>'.esc_html(wdk_field_value('address', $wdk_listing_id)).'</p>'
                            .'</div>';

        if($settings['puopup_custom_content'] == 'yes') {
            $title_field_id = substr($settings['title_field_id'], strpos($settings['title_field_id'],'__')+2);
            $content_field_id = substr($settings['content_field_id'], strpos($settings['content_field_id'],'__')+2);

            $wdk_popup_content = '<div class="infobox map-box wdk-infobox-basic">'
                .'<h3 class="title">'.esc_html(wdk_field_value($title_field_id, $wdk_listing_id)).'</h3>'
                .'<p>'.esc_html(wdk_field_value($content_field_id, $wdk_listing_id)).'</p>'
            .'</div>';

        }


        $wdk_popup_content = str_replace("'", "\'", $wdk_popup_content);
        $wdk_popup_content = str_replace("\n", "", $wdk_popup_content);
        $wdk_popup_content = str_replace("\r", "", $wdk_popup_content);
        
        ?>
        <?php if($pin_icon):?>
            var image = '<?php echo esc_html($pin_icon);?>'; var innerMarker = '<div class="wdk_marker-container wdk_marker-container-image category_id_<?php echo esc_js(wdk_field_value('category_id', $wdk_listing_id));?>""><img src='+image+'></img></div>';
        <?php elseif($font_icon && empty($font_class)):?> 
            var innerMarker = '<div class="wdk_marker-container category_id_<?php echo esc_js(wdk_field_value('category_id', $wdk_listing_id));?>""><div class="front wdk_face"><?php echo wdk_viewe($font_icon);?></div><div class="wdk_marker-card"><div class="wdk_marker-arrow"></div></div></div>';
        <?php else:?> 
            var innerMarker = '<div class="wdk_marker-container category_id_<?php echo esc_js(wdk_field_value('category_id', $wdk_listing_id));?>""><div class="front wdk_face"><i class="<?php echo esc_html($font_class);?>"></i></div><div class="wdk_marker-card"><div class="wdk_marker-arrow"></div></div></div>';
        <?php endif;?>

        <?php if($settings['conf_custom_popup_enable'] == 'yes'):?>
            wdk_markers.push(wdk_generate_marker_basic_popup('<?php echo esc_html($lat);?>','<?php echo esc_html($lng);?>',innerMarker,'<?php echo $wdk_popup_content;?>', wdk_jpopup_customOptions));
        <?php else:?>
            wdk_markers.push(wdk_generate_marker_nopopup('<?php echo esc_html($lat);?>','<?php echo esc_html($lng);?>',innerMarker));
        <?php endif;?>
        wdk_map.addLayer(wdk_clusters);
    <?php endif;?>
    /* set center */
 })
</script>

<?php
if (!$is_edit_mode) {
    $js_content = ob_get_clean();
    $js_content = str_replace(array('</script>','<script>'),'',$js_content );
    wp_add_inline_script( 'wdk-elementor-main', $js_content );
}
?>

