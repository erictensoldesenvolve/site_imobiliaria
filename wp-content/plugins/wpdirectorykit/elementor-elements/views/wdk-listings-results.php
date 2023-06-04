<?php
/**
 * The template for Element Listings Results.
 * This is the template that elementor element listings, list, grid
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


?>
<div class="wdk-element" id="wdk_el_<?php echo esc_html($id_element);?>">
    <div class="wdk-listings-results <?php echo wmvc_show_data('styles_thmbn_des_type',$settings, '');?> view-<?php echo wmvc_show_data('layout_type',$settings, '');?>" <?php if($settings['get_filters_enable'] == 'yes'):?> id="results" <?php endif;?>>
        <?php if($settings['get_filters_enable'] == 'yes'
        && !(
            $settings['sorting_enable'] != 'yes' &&
            $settings['view_type_enable'] != 'yes' &&
            $settings['show_numbers_results_enable'] != 'yes'
        )):?>
        <div class="wdk-filter-head">
            <?php if($settings['sorting_enable'] == 'yes'):?>
                <div class="filter-group order">
                    <?php \Elementor\Icons_Manager::render_icon( $settings['filter_group_box_icon'], [ 'aria-hidden' => 'true' ] );?>
                    <select name="order_by" class='wdk-order'>
                        <?php if(!empty($custom_order)):?>
                            <?php foreach ($custom_order as $item):?>
                                <option value="<?php echo wmvc_show_data('key', $item);?>" <?php echo (wmvc_show_data('order_by', $_GET, '') == wmvc_show_data('key', $item))?'selected="selected"':''; ?>><?php echo esc_html__(wmvc_show_data('title', $item), 'wpdirectorykit');?></option>
                            <?php endforeach;?>
                        <?php else:?>
                            <option value="post_id ASC" <?php echo (wmvc_show_data('order_by', $_GET, '') =='post_id ASC')?'selected="selected"':''; ?>><?php echo esc_html__('Sort by: Newest', 'wpdirectorykit');?></option>
                            <option value="post_id DESC" <?php echo !empty(wmvc_show_data('order_by', $_GET, '') =='post_id DESC')?'selected="selected"':''; ?>><?php echo esc_html__('Sort by: Latest', 'wpdirectorykit');?></option>
                            <option value="post_title ASC" <?php echo !empty(wmvc_show_data('order_by', $_GET, '') =='post_title ASC')?'selected="selected"':''; ?>><?php echo esc_html__('Sort by: Title Asc', 'wpdirectorykit');?></option>
                            <option value="post_title DESC" <?php echo !empty(wmvc_show_data('order_by', $_GET, '') =='post_title DESC')?'selected="selected"':''; ?>><?php echo esc_html__('Sort by: Title Desc', 'wpdirectorykit');?></option>
                        <?php endif;?>
                    </select>
                </div>
            <?php endif;?>
            <?php if($settings['view_type_enable'] == 'yes'):?>
                <div class="filter-group wmvc-view-type">
                    <a class="nav-link <?php echo (wmvc_show_data('layout_type', $settings, '') =='list')?'active':''; ?>" data-id="list" href="#list-view"><i class="fa fa-list-ul"></i></a>
                    <a class="nav-link <?php echo (wmvc_show_data('layout_type', $settings, '') =='grid')?'active':''; ?>" data-id="grid" href="#grid-view"><i class="fa fa-th"></i></a>
                </div>
            <?php endif;?>
            <?php if($settings['show_numbers_results_enable'] == 'yes'):?>
                <div class="filter-group filter-status">
                    <span><?php echo esc_html($listings_count);?> <?php echo esc_html__('Listings', 'wpdirectorykit');?></span>
                </div>
            <?php endif;?>
        </div>
        <?php endif;?>
        <?php if(!empty($results)):?>
            <?php if($settings['layout_type'] == 'carousel'):?>
                <div class="wdk_results_listings_slider_box <?php echo esc_attr($settings['layout_carousel_animation_style']).'_animation';?> <?php echo join(' ', [$settings['styles_carousel_dots_position_style'], $settings['styles_carousel_arrows_position']]);?>">
                <div class="wdk_results_listings_slider_body">
                <div class="wdk_results_listings_slider_ini">
            <?php else:?>
                <div class="wdk-row <?php if(isset($settings['is_mobile_view_enable']) && $settings['is_mobile_view_enable'] == 'yes'):?> WdkScrollMobileSwipe_enable <?php endif;?>">
            <?php endif;?>
            <?php foreach($results as $listing):?>
                <div class="wdk-col">
                    <?php echo wdk_listing_card($listing, $settings);?>
                </div>
            <?php endforeach;?> 
            <?php if($settings['layout_type'] == 'carousel'):?>
                </div>
                    <div class="wdk_slider_arrows">
                        <a class="wdk-slider-prev wdk_lr_slider_arrow">
                            <?php \Elementor\Icons_Manager::render_icon( $settings['styles_carousel_arrows_icon_left'], [ 'aria-hidden' => 'true' ] ); ?>
                        </a>
                        <a class="wdk-slider-next wdk_lr_slider_arrow">
                            <?php \Elementor\Icons_Manager::render_icon( $settings['styles_carousel_arrows_icon_right'], [ 'aria-hidden' => 'true' ] ); ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php else:?>
                </div>
            <?php endif;?>
        <?php else:?>
            <p class="wdk_alert wdk_alert-danger"><?php echo esc_html__('Results not found', 'wpdirectorykit');?></p>
        <?php endif;?>
        <?php if($settings['layout_type'] != 'carousel'):?>
            <?php echo wmvc_xss_clean($pagination_output); ?>
        <?php endif;?>
    </div>
    <?php if($settings['layout_type'] == 'carousel'):?>
    <script>
        jQuery(document).ready(function($){
            var el = $('#wdk_el_<?php echo esc_html($id_element);?> .wdk_results_listings_slider_ini').slick({
                dots: true,
                arrows: true,
                slidesToShow: <?php echo (!empty(trim(wmvc_show_data('layout_carousel_columns', $settings, '3')))) ? wmvc_show_data('layout_carousel_columns', $settings, '3') : 3;?>,
                slidesToScroll: <?php echo (!empty(trim(wmvc_show_data('layout_carousel_columns', $settings, '3')))) ? wmvc_show_data('layout_carousel_columns', $settings, '3') : 3;?>,
                <?php if(!empty(wmvc_show_data('layout_carousel_is_infinite', $settings))):?>
                infinite: <?php echo wmvc_show_data('layout_carousel_is_infinite', $settings, 'true');?>,
                <?php endif;?>
                <?php if(!empty(wmvc_show_data('layout_carousel_is_autoplay', $settings))):?>
                autoplay: <?php echo wmvc_show_data('layout_carousel_is_autoplay', $settings, 'false');?>,
                <?php endif;?>
                nextArrow: $('#wdk_el_<?php echo esc_html($id_element);?> .wdk_slider_arrows .wdk-slider-next'),
                prevArrow: $('#wdk_el_<?php echo esc_html($id_element);?> .wdk_slider_arrows .wdk-slider-prev'),
                customPaging: function(slider, i) {
                    // this example would render "tabs" with titles
                    return '<span class="wdk_lr_dot"><?php \Elementor\Icons_Manager::render_icon( $settings['styles_carousel_dots_icon'], [ 'aria-hidden' => 'true' ] ); ?></span>';
                },
                responsive: [
                    {
                        breakpoint: 991,
                        settings: {
                            slidesToShow: <?php echo (!empty(trim(wmvc_show_data('layout_carousel_columns_tablet', $settings, '2')))) ? wmvc_show_data('layout_carousel_columns_tablet', $settings, '2') : 2;?>,
                            slidesToScroll: <?php echo (!empty(trim(wmvc_show_data('layout_carousel_columns_tablet', $settings, '2')))) ? wmvc_show_data('layout_carousel_columns_tablet', $settings, '2') : 2;?>,
                        }
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: <?php echo (!empty(trim(wmvc_show_data('layout_carousel_columns_mobile', $settings, '1')))) ? wmvc_show_data('layout_carousel_columns_mobile', $settings, '1') : 1;?>,
                            slidesToScroll: <?php echo (!empty(trim(wmvc_show_data('layout_carousel_columns_mobile', $settings, '1')))) ? wmvc_show_data('layout_carousel_columns_mobile', $settings, '1') : 1;?>,
                        }
                    },
                ]
            }).on('breakpoint', function(event, slick, breakpoint){
                wdk_result_listings_thumbnail_slider(el);
                
                if (typeof wdk_favorite == 'function') {
                    wdk_favorite('.wdk_results_listings_slider_ini');
                }
                
                if (typeof wdk_init_compare_elem == 'function') {
                    wdk_init_compare_elem();
                }

            });

            wdk_slick_slider_init(el, ()=>{
                wdk_result_listings_thumbnail_slider(el);
                                
                if (typeof wdk_favorite == 'function') {
                    wdk_favorite('.wdk_results_listings_slider_ini');
                }
                
                if (typeof wdk_init_compare_elem == 'function') {
                    wdk_init_compare_elem();
                }
            });
        })
    </script>
    <?php endif;?>
    <?php if($is_edit_mode):?>
    <script>
        jQuery(document).ready(function($){
            if(typeof wdk_result_listings_thumbnail_slider == 'function') {
                wdk_result_listings_thumbnail_slider();
            }
        })
    </script>
    <?php endif;?>
</div>

