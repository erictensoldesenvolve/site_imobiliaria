<?php
/**
 * The template for Element Categories List.
 * This is the template that elementor element list, categories results
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<?php
$results_page = wmvc_show_data('conf_link', $settings);
if(!is_array($results_page) && !empty($results_page)) {
    $results_page = get_permalink($results_page);
} else {
    $results_page = get_permalink(wdk_get_option('wdk_results_page'));
}
?>

<div class="wdk-element" id="wdk_el_<?php echo esc_html($id_element);?>">
    <div class="wdk-categories-list">
        <ul class="wdk-categories">
            <?php if(count($results) > 0):?>
                <?php foreach ($results as $key => $value):?>
                <li class="wdk-item">
                    <a href="<?php echo esc_url(wdk_url_suffix($results_page,'search_category='.wmvc_show_data('idcategory', $value)));?>#results"  class="wdk-link">
                        <?php if(wmvc_show_data('show_icon', $settings) == 'true'):?>
                            <?php if(wmvc_show_data('icon_id', $value, false)):?>
                                <img src="<?php echo esc_url(wdk_image_src($value, 'full',NULL,'icon_id','icon_path'));?>" alt="<?php echo wmvc_show_data('category_title', $value);?>" class="wdk-icon">
                            <?php endif;?>
                        <?php else:?>
                            <?php \Elementor\Icons_Manager::render_icon( $settings['item_icon_i'], [ 'aria-hidden' => 'true' ] );?>
                        <?php endif;?>
                        <span class="wdk-title"><?php echo wmvc_show_data('category_title', $value);?></span>
                        <span class="wdk-count">(<?php echo wmvc_show_data('listings_counter', $value);?>)</span>
                    </a>
                </li>
                <?php endforeach;?>
            <?php else:?>
                <div class="wdk-col wdk-col-full wdk-col-full-always">
                    <p class="wdk_alert wdk_alert-danger"><?php echo esc_html__('Categories not found', 'wpdirectorykit');?></p>
                </div>
            <?php endif;?>
        </ul> 
    </div>
</div>

