<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

use Elementor\Plugin;
use Elementor\Controls_Manager;
use Elementor\Modules\DynamicTags\Module;

add_action( 'elementor/init', function() {

    $listing_page_id = get_option('wdk_listing_page');

    if(!empty($listing_page_id))
    {
        new WDK_Extension_Hider();

        /*
        if(isset($_GET['post']) && $_GET['post'] == $listing_page_id)
        {
            new WDK_Extension_Hider();
        }
        elseif(!Plugin::$instance->editor->is_edit_mode())
        {
            new WDK_Extension_Hider();
        }*/
    }
});

/**
 * WDK_Extension_Hider
 *
 * Class to extend Elementor controls functionality, adding hide feature based on specific wdk field
 *
 */

class WDK_Extension_Hider {

	public $name = 'WDK Hider';

	private $is_common = true;

	private $depended_scripts = [];

	private $depended_styles = [];

    private $has_controls = TRUE;

	public $common_sections_actions = array(
		array(
			'element' => 'common',
			'action' => '_section_style',
		),
        array(
			'element' => 'section',
			'action' => 'section_advanced',
		),
        array(
			'element' => 'column',
			'action' => 'section_advanced',
		),
	);

    private $supported_elements = array(
        'heading'
    );

	public function __construct() {

        /*
        Controls_Manager::add_tab(
			'wdk_hider',
			__( 'Hider', 'wpdirectorykit' )
		);*/

		$this->init();
	}

	public function init( $param = null ) {
		// Enqueue scripts
		add_action( 'elementor/frontend/after_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		// Enqueue styles
		add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'enqueue_styles' ] );

		// Elementor hooks

		if ( $this->is_common ) {
			// Add the advanced section required to display controls
			$this->add_common_sections_actions();
		}

		$this->add_actions();
	}

	public static function is_enabled() {
		return true;
	}

	public function add_script_depends( $handler ) {
		$this->depended_scripts[] = $handler;
	}

	public function add_style_depends( $handler ) {
		$this->depended_styles[] = $handler;
	}

	public function get_script_depends() {
		return $this->depended_scripts;
	}

	public function enqueue_scripts() {
		foreach ( $this->get_script_depends() as $script ) {
			wp_enqueue_script( $script );
		}
	}

	public function get_style_depends() {
		return $this->depended_styles;
	}

	public static function get_description() {
		return '';
	}

	public function enqueue_styles() {
		foreach ( $this->get_style_depends() as $style ) {
			wp_enqueue_style( $style );
		}
	}

	public function _enqueue_scripts() {
		$scripts = $this->get_script_depends();
		if ( ! empty( $scripts ) ) {
			foreach ( $scripts as $script ) {
				wp_enqueue_script( $script );
			}
		}
	}

	public function _enqueue_styles() {
		$styles = $this->get_style_depends();
		if ( ! empty( $styles ) ) {
			foreach ( $styles as $style ) {
				wp_enqueue_style( $style );
			}
		}
	}

	public function enqueue_all() {
		$this->_enqueue_styles();
		$this->_enqueue_scripts();
	}

	public function get_low_name() {
		return 'hider';
	}

	final public function add_common_sections( $element, $args ) {
		$low_name = $this->get_low_name();
		$section_name = 'wdk_section_' . $low_name . '_advanced';

		if ( ! $this->has_controls ) {
			// no need settings
			return false;
		}

		// Check if this section exists
		$section_exists = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $element->get_unique_name(), $section_name );

		if ( ! is_wp_error( $section_exists ) ) {
			// We can't and should try to add this section to the stack
			return false;
		}

		$this->get_control_section( $section_name, $element );
	}

	public function add_common_sections_actions() {
		foreach ( $this->common_sections_actions as $action ) {
			// Activate action for elements
			add_action('elementor/element/' . $action['element'] . '/' . $action['action'] . '/after_section_end', function ( $element, $args ) {
				$this->add_common_sections( $element, $args );
			}, 10, 2);
		}
	}

	protected function add_actions() {

        // WIDGET
		add_action( 'elementor/frontend/widget/before_render', [ $this, '_start_element' ], 10, 1 );
		add_action( 'elementor/frontend/widget/after_render', [ $this, '_end_element' ], 10, 1 );
        
		// SECTION
		add_action( 'elementor/frontend/section/before_render', [ $this, '_start_element' ], 10, 1 );
		add_action( 'elementor/frontend/section/after_render', [ $this, '_end_element' ], 10, 1 );

		// COLUMN
		add_action( 'elementor/frontend/column/before_render', [ $this, '_start_element' ], 10, 1 );
		add_action( 'elementor/frontend/column/after_render', [ $this, '_end_element' ], 10, 1 );


	}

	protected function remove_controls( $element, $controls = null ) {
		if ( empty( $controls ) ) {
			return;
		}

		if ( is_array( $controls ) ) {
			$control_id = $controls;

			foreach ( $controls as $control_id ) {
				$element->remove_control( $control_id );
			}
		} else {
			$element->remove_control( $controls );
		}
	}

    public function get_control_section( $section_name, $element ) {
		$low_name = $this->get_low_name();

		$element->start_controls_section(
			$section_name,
			[
				'label' => '<span class="color-wdk icon icon-dyn-logo-wdk pull-right ml-1"></span> ' . $this->name,
                'tab' => 'advanced',
			]
		);

        $WMVC = &wdk_get_instance();
        $WMVC->model('field_m');
		$fields_data = $WMVC->field_m->get();
        $fields_list = array('' => esc_html__('Not Selected', 'wpdirectorykit'));
        $order_i = 0;

        $fields_list [(++$order_i).'__section'] = esc_html__('-- Section Custom fields --', 'wpdirectorykit');
        $fields_list [(++$order_i).'__have_sublistings'] = esc_html__('Listing have sublistings', 'wpdirectorykit');
        $fields_list [(++$order_i).'__have_calendar'] = esc_html__('Listing have calendar', 'wpdirectorykit');
        $fields_list [(++$order_i).'__havent_calendar'] = esc_html__('Listing not have calendar', 'wpdirectorykit');
        $fields_list [(++$order_i).'__idlisting'] = esc_html__('Id listing', 'wpdirectorykit');
        $fields_list [(++$order_i).'__post_id'] = esc_html__('Post Id', 'wpdirectorykit');
        $fields_list [(++$order_i).'__lat'] = esc_html__('Gps Lat', 'wpdirectorykit');
        $fields_list [(++$order_i).'__lng'] = esc_html__('Gps Lng', 'wpdirectorykit');
        $fields_list [(++$order_i).'__date'] = esc_html__('Date', 'wpdirectorykit');
        $fields_list [(++$order_i).'__date_modified'] = esc_html__('Date Modified', 'wpdirectorykit');
        $fields_list [(++$order_i).'__post_content'] = esc_html__('WP Content', 'wpdirectorykit');
        $fields_list [(++$order_i).'__address'] = esc_html__('Address', 'wpdirectorykit');
        $fields_list [(++$order_i).'__category_id'] = esc_html__('Category', 'wpdirectorykit');
        $fields_list [(++$order_i).'__location_id'] = esc_html__('Location', 'wpdirectorykit');

        foreach($fields_data as $field)
        {
            if(wmvc_show_data('field_type', $field) == 'SECTION') {
                $fields_list [(++$order_i).'section__'.wmvc_show_data('idfield', $field)] = '-- '.esc_html__('Section', 'wpdirectorykit').' '.wmvc_show_data('field_label', $field).' --';
            } else {
                $fields_list[(++$order_i).'__'.wmvc_show_data('idfield', $field)] = '#'.wmvc_show_data('idfield', $field).' '.wmvc_show_data('field_label', $field).'['.wmvc_show_data('field_type', $field).']';
            }
        }

        $element->add_control(
            'wdk_related_field',
            [
                'label' => esc_html__('WDK Related FIeld', 'wpdirectorykit'),
                'type' => Controls_Manager::SELECT,
                'options' => $fields_list,
                'default' => '',
                'separator' => 'after',
            ]
        );

		$element->end_controls_section();

	}

    public function _start_element( $element ) {
        global $wdk_listing_id;

        if(Plugin::$instance->editor->is_edit_mode() || empty($wdk_listing_id))return;
        //if(!in_array($element->get_name(), $this->supported_elements))return;

        $WMVC = &wdk_get_instance();
        $WMVC->model('field_m');

        $settings = $element->get_settings_for_display();

        if(isset($settings['wdk_related_field']) && !empty($settings['wdk_related_field']))
        {
            $field_id = NULL;
            $field_value = NULL;
            $field_empty = FALSE;
            if(strpos($settings['wdk_related_field'], '__') !== FALSE){
                $field_id = substr($settings['wdk_related_field'], strpos($settings['wdk_related_field'],'__')+2);
            }

            if(is_null($field_id))return;
         
            if($field_id == 'havent_calendar' || $field_id == 'have_calendar') {
                if(function_exists('run_wdk_bookings')) {
                    global $Winter_MVC_wdk_bookings;
                    $Winter_MVC_wdk_bookings->model('calendar_m');
                    $calendar = $Winter_MVC_wdk_bookings->calendar_m->get_by(array('post_id' => $wdk_listing_id));
                    if($field_id == 'havent_calendar') {
                        if($calendar){
                            $field_empty = true;
                        }
                    } else if($field_id == 'have_calendar') {
                        if(!$calendar){
                            $field_empty = true;
                        }
                    }
                }
            } elseif($field_id == 'have_sublistings') {

                $field_empty = true;
                if(!empty(wdk_field_value('listing_related_ids', $wdk_listing_id))){
                    foreach (explode(',',wdk_field_value('listing_related_ids', $wdk_listing_id)) as $key => $child_idlisting) {
                        if(!wdk_field_value('is_activated', $child_idlisting) || !wdk_field_value('is_approved', $child_idlisting)) continue;

                        $field_empty = false;
                        break;
                    }
                }
            } elseif(wdk_field_option($field_id, 'field_type') == "SECTION") {

                // get all section fields
                $sections_data =  $WMVC->field_m->get_fields_section();

                if(!isset($sections_data[$field_id]) || !isset($sections_data[$field_id]['fields']))return;

                // check if all subfields are empty
                $field_empty = true;
                foreach($sections_data[$field_id]['fields'] as $field) {
                    if(!empty(wdk_field_value(wmvc_show_data('idfield', $field), $wdk_listing_id))){
                        $field_empty = false;
                        break;
                    }
                }
            }
            else
            {
                $field_value = wdk_field_value($field_id, $wdk_listing_id);

                if(empty($field_value))$field_empty = TRUE;
            }

            if($field_empty)
            {
                /*
                echo '<pre>';
                var_dump($settings['wdk_related_field']);
                echo '</pre>';
                */

                ob_start();
            }

        }
	}

	public function _end_element( $element ) {
        global $wdk_listing_id;

        if(Plugin::$instance->editor->is_edit_mode() || empty($wdk_listing_id))return;
        //if(!in_array($element->get_name(), $this->supported_elements))return;

        $WMVC = &wdk_get_instance();
        $WMVC->model('field_m');

        $settings = $element->get_settings_for_display();

        if(isset($settings['wdk_related_field']) && !empty($settings['wdk_related_field']))
        {
            $field_id = NULL;
            $field_value = NULL;
            $field_empty = FALSE;
            if(strpos($settings['wdk_related_field'], '__') !== FALSE){
                $field_id = substr($settings['wdk_related_field'], strpos($settings['wdk_related_field'],'__')+2);
            }

            if(is_null($field_id))return;
            if($field_id == 'havent_calendar' || $field_id == 'have_calendar') {
                if(function_exists('run_wdk_bookings')) {
                    global $Winter_MVC_wdk_bookings;
                    $Winter_MVC_wdk_bookings->model('calendar_m');
                    $calendar = $Winter_MVC_wdk_bookings->calendar_m->get_by(array('post_id' => $wdk_listing_id));
                    if($field_id == 'havent_calendar') {
                        if($calendar){
                            $field_empty = true;
                        }
                    } else if($field_id == 'have_calendar') {
                        if(!$calendar){
                            $field_empty = true;
                        }
                    }
                }
            } elseif($field_id == 'have_sublistings') {

                $field_empty = true;
                if(!empty(wdk_field_value('listing_related_ids', $wdk_listing_id))){
                    foreach (explode(',',wdk_field_value('listing_related_ids', $wdk_listing_id)) as $key => $child_idlisting) {
                        if(!wdk_field_value('is_activated', $child_idlisting) || !wdk_field_value('is_approved', $child_idlisting)) continue;

                        $field_empty = false;
                        break;
                    }
                }
            } elseif(wdk_field_option($field_id, 'field_type') == "SECTION") {

                // get all section fields
                $sections_data =  $WMVC->field_m->get_fields_section();

                if(!isset($sections_data[$field_id]) || !isset($sections_data[$field_id]['fields']))return;

                // check if all subfields are empty
                $field_empty = true;
                foreach($sections_data[$field_id]['fields'] as $field) {
                    if(!empty(wdk_field_value(wmvc_show_data('idfield', $field), $wdk_listing_id))){
                        $field_empty = false;
                        break;
                    }
                }
            }
            else
            {
                $field_value = wdk_field_value($field_id, $wdk_listing_id);

                if(empty($field_value))$field_empty = TRUE;
            }

            if($field_empty)
            {
                /*
                echo '<pre>';
                var_dump($settings['wdk_related_field']);
                echo '</pre>';
                */

                $content = ob_get_clean();
            }

        }
	}
    
}






