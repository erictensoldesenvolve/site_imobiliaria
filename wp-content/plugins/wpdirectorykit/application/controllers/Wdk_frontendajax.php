<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly;

class Wdk_frontendajax extends Winter_MVC_Controller {

	public function __construct(){
		if(defined( 'WP_DEBUG' ) && WP_DEBUG) {
			ini_set('display_errors',1);
			ini_set('display_startup_errors',1);
			error_reporting(-1);
		}
		
		parent::__construct();

        $this->data['is_ajax'] = true;
        
	}
    
	public function index(&$output=NULL, $atts=array())
	{

	}

	public function map_infowindow() {
		$this->load->load_helper('listing');
		$this->load->model('listing_m');
		$this->load->model('listingfield_m');
		
		$data = array();
        $data['message'] = '';
		$listing_post_id = NULL;
		if(isset($_POST['listing_post_id']))
        	$listing_post_id = sanitize_text_field($_POST['listing_post_id']);

		$listing = $this->load->listing_m->get($listing_post_id, TRUE);

		if(!empty($listing)) {
			$data['popup_content'] = wdk_listing_card($listing, array('infobox' => true), false, '<div class="infobox map-box">%1$s<div>');
		} else {
			$data['popup_content'] = __( 'Listing is missing', 'wpdirectorykit' );
		}

        $data['success'] = true;

        $this->output($data);
	}

	public function map_infowindow_dash() {
		$this->load->load_helper('listing');
		$this->load->model('listing_m');
		$this->load->model('listingfield_m');
		
		$data = array();
        $data['message'] = '';
		$listing_post_id = NULL;
		if(isset($_POST['listing_post_id']))
        	$listing_post_id = sanitize_text_field($_POST['listing_post_id']);

		$listing = $this->load->listing_m->get($listing_post_id, TRUE);

		if(!empty($listing)) {
			$data['popup_content'] = wdk_listing_card($listing, [], false, '<div class="infobox map-box">%1$s<div>', 'result_item_card_dash_edit');
		} else {
			$data['popup_content'] = __( 'Listing is missing', 'wpdirectorykit' );
		}

        $data['success'] = true;

        $this->output($data);
	}
	  
    public function treefieldid($output="", $atts=array(), $instance=NULL)
    {
		$this->load->load_helper('listing');
		$this->load->model('listing_m');
		$this->load->model('listingfield_m');

        $data = array();
        $data['message'] = __('No message returned!', 'wpdirectorykit');
        $results = array();
        
        $parameters = array();
        
		foreach ($_POST as $key => $value) {
			$parameters[$key] = sanitize_text_field($value);
		}

        $table_name = $table = $parameters['table'];
        
        if(empty($parameters['empty_value']))
            $parameters['empty_value'] = ' - ';
        
        if(empty($parameters['limit']))
            $parameters['limit'] = 10;
            
        if(empty($parameters['attribute_id']))
            $parameters['attribute_id'] = 'id';
            
        if(empty($parameters['attribute_value']))
            $parameters['attribute_value'] = 'address';
            
        if(empty($parameters['offset']))
            $parameters['offset'] = 0;
        
        $start_id = '';
        if(isset($parameters['start_id']))
            $start_id = $parameters['start_id'];

        if($parameters['offset'] == 0) // currently don't have load_more functionality'
            
        if(!empty($parameters['empty_value']))
        {
            $results[0]['key'] = '';
            $results[0]['value'] = sanitize_text_field($parameters['empty_value']);
        }
	
		// it's model

		if($table == 'calendar_listing_m')
			$table = 'listing_m';

		$table_name = substr($table,0, -2);
		$attr_id = sanitize_text_field($parameters['attribute_id']);
		$attr_val = sanitize_text_field($parameters['attribute_value']);
		$attr_search = sanitize_text_field($parameters['search_term']);
		$skip_id = intval($parameters['skip_id']);
		$language_id = intval($parameters['language_id']);

		if(empty($language_id))
			$language_id = NULL;

		$id_part="";
		if(is_numeric($attr_search))
			$id_part = "$attr_id=$attr_search OR ";
	
		if($table == 'icons_list') {
			$icons = $this->get_fa_icons();

			$tree_results = array();

			if(empty($attr_search)) {
				$tree_results = $icons;
			} else {
				foreach ($icons as $c){
					if (stripos($c, $attr_search) !== FALSE){
						//if $c starts with $input, add to matches list
						$tree_results[] = $c;
					} else if (strcmp($attr_search, $c) < 0){
						//$input comes after $c in alpha order
						//since $colors is sorted, we know that we won't find any more matches
						continue;
					}
				}
			}
			$tree_results = array_slice($tree_results,intval($parameters['offset']), intval($parameters['limit']));
			// limit

		} else {
			$this->load->model($table);
			
			$where = array();
			if(!empty($attr_search))
				$where["($id_part $attr_val LIKE '%$attr_search%')"] = NULL;
			
			if(!empty($parameters['attr_search']))
				$where[$parameters['attr_search']] = NULL;
			
			if(isset($parameters['user_check']) && ($parameters['user_check'] == 'true' || $parameters['user_check'] == '1')) {
				if($table == 'listing_m') {
					if($parameters['table'] == 'calendar_listing_m') {
						global $Winter_MVC_wdk_bookings;
						$Winter_MVC_wdk_bookings->model('calendar_m');
						$this->db->join($Winter_MVC_wdk_bookings->calendar_m->_table_name.' ON '.$this->$table->_table_name.'.post_id = '.$Winter_MVC_wdk_bookings->calendar_m->_table_name.'.post_id');
					} 

					$tree_results = $this->$table->get_pagination(intval($parameters['limit']),intval($parameters['offset']), $where, TRUE, get_current_user_id());
				} else {
					
					if(!empty($parameters['filter_ids'])){
						$this->db->where(array( esc_sql($this->$table->_primary_key).' IN ('.esc_sql($parameters['filter_ids']).')' => NULL));
					}

					$tree_results = $this->$table->get_pagination(intval($parameters['limit']),intval($parameters['offset']), $where );
				}
			} else {
				if($parameters['table'] == 'calendar_listing_m') {
					global $Winter_MVC_wdk_bookings;
					$Winter_MVC_wdk_bookings->model('calendar_m');
					$this->db->join($Winter_MVC_wdk_bookings->calendar_m->_table_name.' ON '.$this->$table->_table_name.'.post_id = '.$Winter_MVC_wdk_bookings->calendar_m->_table_name.'.post_id');
				}
				
				if(!empty($parameters['filter_ids'])){
					$this->db->where(array( esc_sql($this->$table->_primary_key).' IN ('.esc_sql($parameters['filter_ids']).')' => NULL));
				}
				
				if($table == 'user_m') {
					$like = "(meta_value LIKE '%wdk_%' )";
					$tree_results = $this->$table->get_pagination(intval($parameters['limit']),intval($parameters['offset']), $where, NULL, $like);

				} else {
					$tree_results = $this->$table->get_pagination(intval($parameters['limit']),intval($parameters['offset']), $where);
				}

			}
		}
	
		$ind_order=1;
		foreach ($tree_results as $key=>$row)
		{
				$level_gen='';
				if(empty($attr_search) && isset($row->level))
					$level_gen = str_pad('', $row->level*12, '&nbsp;').'';
				
				$results[$ind_order]['key'] = wmvc_show_data($attr_id, $row);
				if($table == 'listing_m') {
					$results[$ind_order]['value'] = $level_gen
												.'#'.wmvc_show_data($attr_id, $row).', '.wmvc_show_data($attr_val, $row);
				} elseif($table == 'user_m') {
					$results[$ind_order]['value'] = $level_gen
												.'#'.wmvc_show_data($attr_id, $row).', '.wmvc_show_data($attr_val, $row).' ('.wmvc_show_data('user_email', $row).')';
				} elseif($table == 'icons_list') {
					$results[$ind_order]['key'] = $row;
					if(defined('ELEMENTOR_ASSETS_URL')){
						$results[$ind_order]['value'] = '<i class="'. $row.'"></i>&nbsp;&nbsp;'.$row;
					} else {
						$results[$ind_order]['value'] = $row;
					}
				} else {
					$results[$ind_order]['value'] = $level_gen
													.esc_html__(wmvc_show_data($attr_val, $row), 'wpdirectorykit');
				}
			$ind_order++;
		}

	
		// get current value by ID
		$row=NULL;
		if($table == 'icons_list') {
			if(!empty($parameters['curr_id'])) {
				$row = '<i class="'. $parameters['curr_id'].'"></i>&nbsp;&nbsp;'.$parameters['curr_id'];
			}
		} else {
			if(!empty($parameters['curr_id']))
				$row = $this->$table->get(intval($parameters['curr_id']), TRUE);
		}

		if($table == 'icons_list') {
			$data['curr_val'] = $row;
		}elseif(is_object($row))
		{
            $level_gen='';
			if(isset($row->level))
			    $level_gen = str_pad('', $row->level*12, '&nbsp;').'';

			if($table == 'user_m') {
				$data['curr_val'] = $level_gen
											.wmvc_show_data(wmvc_show_data('attribute_value', $parameters), $row).' ('.wmvc_show_data('user_email', $row).')'.' #'.wmvc_show_data($attr_id, $row);
			} else {
				$data['curr_val'] = $level_gen
							.esc_html__(wmvc_show_data(wmvc_show_data('attribute_value', $parameters), $row), 'wpdirectorykit');
			}


			//if(!empty($start_id) && $start_id == $parameters['curr_id'] && isset($parameters['sub_empty_value']) && !empty($parameters['sub_empty_value'])) $this->data['curr_val'] = wmvc_show_data('sub_empty_value', $parameters);
			//elseif(!empty($start_id) && $start_id == $parameters['curr_id']) $this->data['curr_val'] = $parameters['empty_value'];
		}
		else
		{
			$data['curr_val'] = $parameters['empty_value'];
		}
	
		$this->data['success'] = true;
        
        $data['results'] = $results;
        //$data['sql'] = $this->db->last_query();
		$this->output($data);
    }
	  
    public function treefieldid_checkboxes($output="", $atts=array(), $instance=NULL)
    {
		$this->load->load_helper('listing');
		$this->load->model('listing_m');
		$this->load->model('listingfield_m');

        $data = array();
        $data['message'] = __('No message returned!', 'wpdirectorykit');
        $results = array();
        
        $parameters = array();
        
		foreach ($_POST as $key => $value) {
			$parameters[$key] = sanitize_text_field($value);
		}

        $table_name = $table = $parameters['table'];
        
        if(empty($parameters['empty_value']))
            $parameters['empty_value'] = ' - ';
        
        if(empty($parameters['limit']))
            $parameters['limit'] = 10;
            
        if(empty($parameters['attribute_id']))
            $parameters['attribute_id'] = 'id';
            
        if(empty($parameters['attribute_value']))
            $parameters['attribute_value'] = 'address';
            
        if(empty($parameters['offset']))
            $parameters['offset'] = 0;
        
        $start_id = '';
        if(isset($parameters['start_id']))
            $start_id = $parameters['start_id'];

        if($parameters['offset'] == 0) // currently don't have load_more functionality'
            
        if(!empty($parameters['empty_value']))
        {
            $results[0]['key'] = '';
            $results[0]['value'] = sanitize_text_field($parameters['empty_value']);
        }
	
		// it's model

		if($table == 'calendar_listing_m')
			$table = 'listing_m';

		$table_name = substr($table,0, -2);
		$attr_id = sanitize_text_field($parameters['attribute_id']);
		$attr_val = sanitize_text_field($parameters['attribute_value']);
		$attr_search = sanitize_text_field($parameters['search_term']);
		$skip_id = intval($parameters['skip_id']);
		$language_id = intval($parameters['language_id']);

		if(empty($language_id))
			$language_id = NULL;

		$id_part="";
		if(is_numeric($attr_search))
			$id_part = "$attr_id=$attr_search OR ";
	
		$this->load->model($table);
		
		$where = array();
		if(!empty($attr_search))
			$where["($id_part $attr_val LIKE '%$attr_search%')"] = NULL;
		
			if(isset($parameters['selected']) && !empty($parameters['selected'])) {
                   
				if(is_string($parameters['selected']) && strpos($parameters['selected'], ',') !== FALSE){
					$selected = explode(',', $parameters['selected']);
				} elseif(is_string($parameters['selected'])){
					$selected = array($parameters['selected']);
				}

				$ids = array();
				foreach($selected as $selected_item) {
					if(!empty($selected_item) && is_intval($selected_item)) {
						$ids [] = $selected_item;
					}
				}
				
				/* where in */
				if(!empty($ids)){
					$this->db->order_by('FIELD('.$this->$table->_table_name.'.'.$this->$table->_primary_key.', '. implode(',', array_reverse($ids)) . ') DESC');
					if(intval($parameters['limit']) < count($ids)) {
						$parameters['limit'] = count($ids);
					}
				}
            } 

			if(!empty($parameters['filter_ids'])){
				$this->db->where(array( esc_sql($this->$table->_primary_key).' IN ('.esc_sql($parameters['filter_ids']).')' => NULL));
			}
			
			$tree_results = $this->$table->get_pagination(intval($parameters['limit']),intval($parameters['offset']), $where);
	
		$ind_order=1;
		foreach ($tree_results as $key=>$row)
		{
				$level_gen='';
				if(empty($attr_search) && isset($row->level))
					$level_gen = str_pad('', $row->level*12, '&nbsp;').'';
				
				$results[$ind_order]['key'] = wmvc_show_data($attr_id, $row);
				$results[$ind_order]['value'] = $level_gen
												.esc_html__(wmvc_show_data($attr_val, $row), 'wpdirectorykit');
			$ind_order++;
		}
	
		// get current value by ID
		$row=NULL;
		if(!empty($parameters['curr_id']))
			$row = $this->$table->get(intval($parameters['curr_id']), TRUE);

		if(is_object($row))
		{
            $level_gen='';
			if(isset($row->level))
			    $level_gen = str_pad('', $row->level*12, '&nbsp;').'';

				$data['curr_val'] = $level_gen
							.esc_html__(wmvc_show_data(wmvc_show_data('attribute_value', $parameters), $row), 'wpdirectorykit');

		}
		else
		{
			$data['curr_val'] = $parameters['empty_value'];
		}
	
		$this->data['success'] = true;
        
        $data['results'] = $results;
        //$data['sql'] = $this->db->last_query();
		$this->output($data);
    }
	    
    private function output($data, $print = TRUE) {
        if($print) {
            header('Pragma: no-cache');
            header('Cache-Control: no-store, no-cache');
            header('Content-Type: application/json; charset=utf8');
            //header('Content-Length: '.$length); // special characters causing troubles
            echo (wp_json_encode($data));
            exit();
        } else {
            return $data;
        }
    }
	
	  
    public function select_2_ajax($output="", $atts=array(), $instance=NULL)
    {
		$this->load->load_helper('listing');
		$this->load->model('listing_m');
		$this->load->model('listingfield_m');

        $data = array();
        $data['message'] = __('No message returned!', 'wpdirectorykit');
        $results = array();
		$data['pagination'] = true;
        $parameters = array();
        
		foreach ($_POST as $key => $value) {
			$parameters[$key] = sanitize_text_field($value);
		}

		if(empty($parameters['table'])) return false;

		$model_name = $parameters['table'];

		$key_column = '';
		$print_column = '';
		
		$search_column = '';
		if(empty($parameters['columns_search'])) {
			switch ($model_name) {
				case 'category_m':
					$search_column = 'idcategory,category_title';
					break;
				case 'location_m':
					$search_column = 'idlocation,location_title';
					break;
				
				default:
					# code...
					break;
			}
		} else {
			$search_column = $parameters['columns_search'];
		}

		if(empty($parameters['key_column'])) {
			switch ($model_name) {
				case 'category_m':
					$key_column = 'idcategory';
					break;
				case 'location_m':
					$key_column = 'idlocation';
					break;
				
				default:
					# code...
					break;
			}
		} else {
			$key_column = $parameters['key_column'];
		}

		if(empty($parameters['print_column'])) {
			switch ($model_name) {
				case 'category_m':
					$print_column = 'category_title';
					break;
				case 'location_m':
					$print_column = 'location_title';
					break;
				
				default:
					# code...
					break;
			}
		} else {
			$print_column = $parameters['print_column'];
		}

		$limit = 20;
		if(!empty($parameters['limit'])) {
			$limit = intval($parameters['limit']);
		}

		$offset = NULL;
		if(!empty($parameters['page_result']) && $parameters['page_result'] > 1) {
			$offset = (intval($parameters['page_result']) - 1) * $limit;
		}

		$this->load->model($model_name);

		$where = array();
		if(!empty($_POST['q']['term']) && !empty($search_column)) {
			$sql_search = '';
			foreach (explode(',', $search_column) as $column) {
				if(empty($column)) continue;

				if(!empty($sql_search))
					$sql_search .= " OR ";

				$sql_search .= " ".esc_sql($column)." LIKE '%".esc_sql($_POST['q']['term'])."%'";
			}

			$where ["($sql_search)"] = NULL;
		}

		$db_results_total = $this->$model_name->total($where);
		$db_results = $this->$model_name->get_pagination($limit,$offset, $where);
		$data['output'] =  $db_results;
       
		if (!$db_results) {
            $data['errors'] = '';
        } else {
            foreach($db_results as $row) {

				$level_gen='';
				if(empty($attr_search) && isset($row->level))
					$level_gen = str_pad('', $row->level*12, '&nbsp;').'';

                $results[] = [
                        'id'=> wmvc_show_data($key_column, $row),
                        'text'=> $level_gen.esc_html__(trim(wmvc_show_data($print_column, $row)), 'wpdirectorykit'),
                ];
            }

        }

		$data['success'] = true;
        
        $data['results'] = $results;


		if($db_results_total >= $limit + $offset) {
			$data['pagination'] =[
				"more"=> true
			];
		}

        //$data['sql'] = $this->db->last_query();
		$this->output($data);
    }
	  
    public function select_2_ajax_user($output="", $atts=array(), $instance=NULL)
    {
		$this->load->load_helper('listing');

        $data = array();
        $data['message'] = __('No message returned!', 'wpdirectorykit');
        $results = array();
		$data['pagination'] = true;
        $parameters = array();
        
		foreach ($_POST as $key => $value) {
			$parameters[$key] = sanitize_text_field($value);
		}

		$model_name = 'user_m';

		$key_column = '';
		$print_column = '';
		
		$limit = 20;
		if(!empty($parameters['limit'])) {
			$limit = intval($parameters['limit']);
		}

		$offset = NULL;
		if(!empty($parameters['page_result']) && $parameters['page_result'] > 1) {
			$offset = (intval($parameters['page_result']) - 1) * $limit;
		}

		$this->load->model($model_name);
		$search_column = 'display_name,user_email,user_login,ID';
		$where = array();
		if(!empty($_POST['q']['term']) && !empty($search_column)) {
			$sql_search = '';
			foreach (explode(',', $search_column) as $column) {
				if(empty($column)) continue;

				if(!empty($sql_search))
					$sql_search .= " OR ";

				$sql_search .= " ".esc_sql($column)." LIKE '%".esc_sql($_POST['q']['term'])."%'";
			}

			$where ["($sql_search)"] = NULL;
		}

		$like = "(meta_value LIKE '%wdk_%' )";
		$db_results_total = $this->$model_name->total($where, NULL, $like);

		$db_results = $this->$model_name->get_pagination($limit,$offset, $where, NULL, $like);
		$data['output'] =  $db_results;
       
		if (!$db_results) {
            $data['errors'] = '';
        } else {
            foreach($db_results as $row) {

				$level_gen='';
				//if(empty($attr_search) && isset($row->level))
				//	$level_gen = str_pad('', $row->level*12, '&nbsp;').'';

                $results[] = [
                        'id'=> wmvc_show_data('ID', $row),
                        'text'=> $level_gen.trim(wmvc_show_data('display_name', $row)),
                ];
            }

        }

		$data['success'] = true;
        
        $data['results'] = $results;
		if($db_results_total >= $limit + $offset) {
			$data['pagination'] =[
				"more"=> true
			];
		}

        //$data['sql'] = $this->db->last_query();
		$this->output($data);
    }

		  
    public function wdk_tree_dropdowns($output="", $atts=array(), $instance=NULL)
    {
		$this->load->load_helper('listing');

        $data = array();
        $data['message'] = __('No message returned!', 'wpdirectorykit');
        $results = array();
        $parameters = array();
        
		foreach ($_POST as $key => $value) {
			$parameters[$key] = sanitize_text_field($value);
		}

		if(empty($parameters['table'])) return false;

		switch ($parameters['table']) {
			case 'category_id':
			case 'search_category':
				$model_name = 'category_m';
				break;
			case 'location_id':
			case 'search_location':
				$model_name = 'location_m';
				break;
			default:
				# code...
				break;
		}

		$key_column = '';
		$print_column = '';
		$current_id = '0';

		if(!empty($parameters['id'])) {
			$current_id = intval($parameters['id']);
		}

		if(empty($parameters['key_column'])) {
			switch ($model_name) {
				case 'category_m':
					$key_column = 'idcategory';
					break;
				case 'location_m':
					$key_column = 'idlocation';
					break;
				
				default:
					# code...
					break;
			}
		} else {
			$key_column = $parameters['key_column'];
		}

		if(empty($parameters['print_column'])) {
			switch ($model_name) {
				case 'category_m':
					$print_column = 'category_title';
					break;
				case 'location_m':
					$print_column = 'location_title';
					break;
				
				default:
					# code...
					break;
			}
		} else {
			$print_column = $parameters['print_column'];
		}

		$this->load->model($model_name);

		$db_results = $this->$model_name->get_by(array('parent_id = '.$current_id => NULL)); 
		$level = 5;

		if(isset($db_results[0]->level)) {
			$level = $db_results[0]->level;
		}

		$data['output'] =  $db_results;
       
		if (!$db_results) {
            $data['errors'] = '';
        } else {

			if($model_name == 'category_m') {
				$placeholder_texts = [
					0 => esc_html__('Select Categories','wpdirectorykit'),
					1 => esc_html__('Select Sub Categories','wpdirectorykit'),
					2 => esc_html__('Select Sub Categories','wpdirectorykit'),
					3 => esc_html__('Select Sub Categories','wpdirectorykit'),
					4 => esc_html__('Select Sub Categories','wpdirectorykit'),
					5 => esc_html__('Select Sub Categories','wpdirectorykit'),
				];
			} else {
				$placeholder_texts = [
					0 => esc_html__('Select Country','wpdirectorykit'),
					1 => esc_html__('Select City','wpdirectorykit'),
					2 => esc_html__('Select Neighborhood','wpdirectorykit'),
					3 => esc_html__('Select Sub Area','wpdirectorykit'),
					4 => esc_html__('Select Sub Area','wpdirectorykit'),
					5 => esc_html__('Select Sub Area','wpdirectorykit'),
				];
			}
			
            if(isset($placeholder_texts[$level])) {
                $placeholder = $placeholder_texts[$level];
            } else {
                $placeholder = esc_html__('Select Sub Categories','wpdirectorykit');
            }

			$results[] = [
				'id'=> '',
				'text'=> $placeholder
			];

            foreach($db_results as $row) {
                $results[] = [
                        'id'=> wmvc_show_data($key_column, $row),
                        'text'=> esc_html__(trim(wmvc_show_data($print_column, $row)),'wpdirectorykit'),
                ];
            }

        }

		$data['success'] = true;
        $data['results'] = $results;

        //$data['sql'] = $this->db->last_query();
		$this->output($data);
    }

		  
    public function search_suggestion($output="", $atts=array(), $instance=NULL)
    {
		$this->load->load_helper('listing');
		$this->load->model('category_m');
		$this->load->model('location_m');
		$this->load->model('listing_m');

        $data = array();
		$categories_limit = 10;
		$categories_search_column = array('idcategory','category_title');
		$locations_limit = 10;
		$locations_search_column = array('idlocation','location_title');

		$results = array();
		/*
		[
			'field_key' => 'string',
			'value' => 'string',
			'print' => [
				'html' => 'string',
				'parsed_html' => [
					'left_column' => 'string',
					'middle_column' => 'string',
					'right_column' => 'string',
				],
				'parsed_content' => [
					'icon_class' => 'string',
					'title' => 'string',
					'sub_title' => 'string',
					'right_text' => 'string',
				]
			]
		]

		*/

        $data['message'] = __('No message returned!', 'wpdirectorykit');

        $parameters = array();
		foreach ($_POST as $key => $value) {
			$parameters[$key] = sanitize_text_field($value);
		}

		$search_text = '';
		if(!empty($parameters['search']))
			$search_text = trim($parameters['search']);

		/* Categories */
		$where = array();
		if($search_text) {
			$sql_search = '';
			foreach ($categories_search_column as $column) {
				if(!empty($sql_search))
					$sql_search .= " OR ";

				$sql_search .= " ".esc_sql($column)." LIKE '%".esc_sql($search_text)."%'";
			}
			$where ["($sql_search)"] = NULL;
			$db_results = $this->category_m->get_pagination($categories_limit, NULL, $where);
		} else {
			$db_results = $this->category_m->get_pagination($categories_limit, NULL, array('parent_id = 0' => NULL));
		}

		if($db_results) foreach($db_results as $row) {
			$results[] = [
				'field_key' => 'search_category',
				'value' => $row->category_title,
				'print' => [
					'parsed_content' => [
						'icon_class' => (!empty(wmvc_show_data('font_icon_code', $row, false))) ? wmvc_show_data('font_icon_code', $row) : 'fa fa-tag',
						'title' => esc_html__($row->category_title, 'wpdirectorykit'),
						'sub_title' => '',
						'right_text' => __('Category', 'wpdirectorykit')
					]
				]
			];
		}

		/* END Categories */

		/* Locations */
		$where = array();
		if($search_text) {
			$sql_search = '';
			foreach ($locations_search_column as $column) {
				if(!empty($sql_search))
					$sql_search .= " OR ";

				$sql_search .= " ".$this->db->prefix."wdk_locations.".esc_sql($column)." LIKE '%".esc_sql($search_text)."%'";
			}
			$where ["($sql_search)"] = NULL;
			
			$select = $this->db->prefix.'wdk_locations.idlocation, '.$this->db->prefix.'wdk_locations.location_title';
			$select .= ',location_1.location_title AS location_title_1';
	
			$this->db->join($this->db->prefix.'wdk_locations AS location_1 ON '.$this->db->prefix.'wdk_locations.parent_id = location_1.idlocation', NULL, 'LEFT');
			for($i = 2 ; $i <= 2; $i++) {
				$select .= ',location_'.$i.'.location_title AS location_title_'.$i.'';
				$this->db->join($this->db->prefix.'wdk_locations AS location_'.$i.' ON location_'.($i-1).'.parent_id = location_'.$i.'.idlocation', NULL, 'LEFT');
			}
			$this->db->select($select);
			$db_results = $this->location_m->get_pagination($locations_limit, NULL, $where);
		} else {
			$db_results = $this->location_m->get_pagination($locations_limit, NULL, array('parent_id = 0' => NULL));
		}

		if($db_results) foreach($db_results as $row) {
			$subtitle = '';

			for($i = 1 ; $i <= 2; $i++) {
				if(wmvc_show_data('location_title_'.$i, $row, false)){
					$subtitle .= wmvc_show_data('location_title_'.$i, $row, false).', ';
				}
			}

			$subtitle = substr($subtitle,0,-2);

			$results[] = [
				'field_key' => 'search_location',
				'value' => $row->location_title,
				'print' => [
					'parsed_content' => [
						'icon_class' => (!empty(wmvc_show_data('font_icon_code', $row, false))) ? wmvc_show_data('font_icon_code', $row) : 'fa fa-map-marker',
						'title' => esc_html__($row->location_title, 'wpdirectorykit'),
						'sub_title' => esc_html__($subtitle, 'wpdirectorykit'),
						'right_text' => __('Location', 'wpdirectorykit')
					]
				]
			];
		}
		
		/* END Locations */

		/* address suggestion if empty other */
		if(empty($results)) {
			$this->db->select('address');
			$db_results = $this->db->where(array("address LIKE '%".esc_sql($search_text)."%'" => NULL));
			$this->db->from($this->listing_m->_table_name);
			$this->db->group_by('address');
		
			$query = $this->db->get();
			if ($this->db->num_rows() > 0) {
				$db_results = $this->db->results();
			} else {
				$db_results = array();
			}

			if($db_results) foreach($db_results as $row) {
				$results[] = [
					'field_key' => 'address',
					'value' => $row->address,
					'print' => [
						'parsed_content' => [
							'icon_class' => 'fa fa-map-marker',
							'title' => $row->address,
							'sub_title' => '',
							'right_text' => __('Address', 'wpdirectorykit')
						]
					]
				];
			}
		}

		if(empty($results)) {

			$name_part = str_replace(' ','+',$search_text);
        
			//$url = 'http://photon.komoot.de/api/?q='.$name_part;
			$url = 'https://api.teleport.org/api/cities/?limit=1&search='.$name_part;

			$request    = wp_remote_get( $url );
			$response = '';

			// request failed
			if ( is_wp_error( $request ) ) {
				$response = $request;
			}
			$code = (int) wp_remote_retrieve_response_code( $request );
	
			// make sure the fetch was successful
			if (empty($response) && $code == 200 ) {
				$response = wp_remote_retrieve_body( $request );
	
				// Decode the json
				$resp = json_decode( $response,true ); 
				if(!empty($resp) && isset($resp['_embedded']) && isset($resp["_embedded"]["city:search-results"]) && !empty($resp["_embedded"]["city:search-results"])) {
					foreach($resp["_embedded"]["city:search-results"] as $prediction)
					{
						if(isset($prediction["matching_full_name"])){
							$results[] = [
								'field_key' => 'search_location',
								'value' => $prediction["matching_full_name"],
								'print' => [
									'parsed_content' => [
										'icon_class' => 'fa fa-map-marker',
										'title' => $prediction["matching_full_name"],
										'sub_title' => '',
										'right_text' => ''
									]
								]
							];

						}
						break;
					}
				}
			} 
		}

		$data['success'] = true;
        $data['results'] = $results;

        //$data['sql'] = $this->db->last_query();
		$this->output($data);
    }
	
	public function booking_price_calculate() {
		$data = array();
		$data['success'] = false;
		
		$this->load->load_helper('listing');
		global $Winter_MVC_wdk_bookings;

		$Winter_MVC_wdk_bookings->model('reservation_m');
		$Winter_MVC_wdk_bookings->model('calendar_m');

		$results = array();
        $parameters = array();
		foreach ($_POST as $key => $value) {
			$parameters[sanitize_text_field($key)] = sanitize_text_field($value);
		}

		$post_id = NULL;
		$date_from = NULL;
		$date_to = NULL;

		if(isset($parameters['post_id'])) {
			$post_id = $parameters['post_id'];
		}

		if(isset($parameters['date_from'])) {
			$date_from = wdk_normalize_date_db($parameters['date_from']);
		}

		if(isset($parameters['date_to'])) {
			$date_to = wdk_normalize_date_db($parameters['date_to']);
		}

		if($post_id && $date_from && $date_to) {
			$price = $Winter_MVC_wdk_bookings->reservation_m->calculate_price($post_id, $date_from, $date_to);

			$calendar = $Winter_MVC_wdk_bookings->calendar_m->get_by(array('post_id'=>$post_id), TRUE); // date_package_expire package_id
			$calendar_fees = array();
			if($calendar && !empty($calendar->json_data_fees))
				$calendar_fees = json_decode($calendar->json_data_fees );

			$results['symbol'] = '';
			if(function_exists('wdk_booking_currency_symbol'))
				$results['symbol'] = wdk_booking_currency_symbol();
			
			if($price) {
				$results['price'] = $price['price'];
				$results['total'] = $price['price'];
				$results['fees'] = array();
				foreach ($calendar_fees as $fee) {
					if(!wmvc_show_data('is_activated', $fee, false,TRUE,TRUE)) continue;
					if(is_intval(wmvc_show_data('value', $fee,'',TRUE,TRUE))) {
						$field = str_replace(' ','_',strtolower(esc_html(wmvc_show_data('title', $fee,'-',TRUE,TRUE)))); 
						if(!wmvc_show_data('is_required', $fee, false,TRUE,TRUE) && isset($parameters['fee_'.$field]) && $parameters['fee_'.$field] == 0) {
							
						} else {
							$price = 0;
							if(wmvc_show_data('calculation_base', $fee,'',TRUE,TRUE) == 'per_night') {
								$nights = (int)abs(strtotime($date_from) - strtotime($date_to))/(60*60*24);
								$price += intval(wmvc_show_data('value', $fee,'',TRUE,TRUE)) * $nights;
							} else if(wmvc_show_data('calculation_base', $fee,'',TRUE,TRUE) == 'per_person') {
								$price += intval(wmvc_show_data('value', $fee,'',TRUE,TRUE)) * intval(wmvc_show_data('guests', $_POST, 0,TRUE,TRUE));
							} else {
								$price += intval(wmvc_show_data('value', $fee,'',TRUE,TRUE));
							}
							$results['fees'][wmvc_show_data('title', $fee,'',TRUE,TRUE)] = $price;
							$results['total'] += $price;
						}
					}
				}
				$data['success'] = true;
			} else {
				$data['popup_text_error'] = __('Those dates are not available', 'wpdirectorykit');
			}
		}

		
        $data['results'] = $results;

        //$data['sql'] = $this->db->last_query();
		$this->output($data);
	}
	    
	private function get_fa_icons() {
		return array("fa fa-500px","fa fa-address-book","fa fa-address-book-o","fa fa-address-card","fa fa-address-card-o","fa fa-adjust","fa fa-adn","fa fa-align-center","fa fa-align-justify","fa fa-align-left","fa fa-align-right",
		"fa fa-amazon","fa fa-ambulance","fa fa-american-sign-language-interpreting","fa fa-anchor","fa fa-android","fa fa-angellist","fa fa-angle-double-down","fa fa-angle-double-left","fa fa-angle-double-right","fa fa-angle-double-up",
		"fa fa-angle-down","fa fa-angle-left","fa fa-angle-right","fa fa-angle-up","fa fa-apple","fa fa-archive","fa fa-area-chart","fa fa-arrow-circle-down","fa fa-arrow-circle-left","fa fa-arrow-circle-o-down","fa fa-arrow-circle-o-left",
		"fa fa-arrow-circle-o-right","fa fa-arrow-circle-o-up","fa fa-arrow-circle-right","fa fa-arrow-circle-up","fa fa-arrow-down","fa fa-arrow-left","fa fa-arrow-right","fa fa-arrow-up","fa fa-arrows","fa fa-arrows-alt","fa fa-arrows-h",
		"fa fa-arrows-v","fa fa-assistive-listening-systems","fa fa-asterisk","fa fa-at","fa fa-audio-description","fa fa-backward","fa fa-balance-scale","fa fa-ban","fa fa-bandcamp","fa fa-bar-chart","fa fa-barcode","fa fa-bars","fa fa-bath",
		"fa fa-battery-empty","fa fa-battery-full","fa fa-battery-half","fa fa-battery-quarter","fa fa-battery-three-quarters","fa fa-bed","fa fa-beer","fa fa-behance","fa fa-behance-square","fa fa-bell","fa fa-bell-o","fa fa-bell-slash",
		"fa fa-bell-slash-o","fa fa-bicycle","fa fa-binoculars","fa fa-birthday-cake","fa fa-bitbucket","fa fa-bitbucket-square","fa fa-black-tie","fa fa-blind","fa fa-bluetooth","fa fa-bluetooth-b","fa fa-bold","fa fa-bolt","fa fa-bomb","fa fa-book",
		"fa fa-bookmark","fa fa-bookmark-o","fa fa-braille","fa fa-briefcase","fa fa-btc","fa fa-bug","fa fa-building","fa fa-building-o","fa fa-bullhorn","fa fa-bullseye","fa fa-bus","fa fa-buysellads","fa fa-calculator","fa fa-calendar",
		"fa fa-calendar-check-o","fa fa-calendar-minus-o","fa fa-calendar-o","fa fa-calendar-plus-o","fa fa-calendar-times-o","fa fa-camera","fa fa-camera-retro","fa fa-car","fa fa-caret-down","fa fa-caret-left","fa fa-caret-right",
		"fa fa-caret-square-o-down","fa fa-caret-square-o-left","fa fa-caret-square-o-right","fa fa-caret-square-o-up","fa fa-caret-up","fa fa-cart-arrow-down","fa fa-cart-plus","fa fa-cc","fa fa-cc-amex","fa fa-cc-diners-club",
		"fa fa-cc-discover","fa fa-cc-jcb","fa fa-cc-mastercard","fa fa-cc-paypal","fa fa-cc-stripe","fa fa-cc-visa","fa fa-certificate","fa fa-chain-broken","fa fa-check","fa fa-check-circle","fa fa-check-circle-o","fa fa-check-square",
		"fa fa-check-square-o","fa fa-chevron-circle-down","fa fa-chevron-circle-left","fa fa-chevron-circle-right","fa fa-chevron-circle-up","fa fa-chevron-down","fa fa-chevron-left","fa fa-chevron-right","fa fa-chevron-up","fa fa-child",
		"fa fa-chrome","fa fa-circle","fa fa-circle-o","fa fa-circle-o-notch","fa fa-circle-thin","fa fa-clipboard","fa fa-clock-o","fa fa-clone","fa fa-cloud","fa fa-cloud-download","fa fa-cloud-upload","fa fa-code","fa fa-code-fork","fa fa-codepen",
		"fa fa-codiepie","fa fa-coffee","fa fa-cog","fa fa-cogs","fa fa-columns","fa fa-comment","fa fa-comment-o","fa fa-commenting","fa fa-commenting-o","fa fa-comments","fa fa-comments-o","fa fa-compass","fa fa-compress","fa fa-connectdevelop",
		"fa fa-contao","fa fa-copyright","fa fa-creative-commons","fa fa-credit-card","fa fa-credit-card-alt","fa fa-crop","fa fa-crosshairs","fa fa-css3","fa fa-cube","fa fa-cubes","fa fa-cutlery","fa fa-dashcube","fa fa-database","fa fa-deaf",
		"fa fa-delicious","fa fa-desktop","fa fa-deviantart","fa fa-diamond","fa fa-digg","fa fa-dot-circle-o","fa fa-download","fa fa-dribbble","fa fa-dropbox","fa fa-drupal","fa fa-edge","fa fa-eercast","fa fa-eject","fa fa-ellipsis-h","fa fa-ellipsis-v",
		"fa fa-empire","fa fa-envelope","fa fa-envelope-o","fa fa-envelope-open","fa fa-envelope-open-o","fa fa-envelope-square","fa fa-envira","fa fa-eraser","fa fa-etsy","fa fa-eur","fa fa-exchange","fa fa-exclamation","fa fa-exclamation-circle",
		"fa fa-exclamation-triangle","fa fa-expand","fa fa-expeditedssl","fa fa-external-link","fa fa-external-link-square","fa fa-eye","fa fa-eye-slash","fa fa-eyedropper","fa fa-facebook","fa fa-facebook-official","fa fa-facebook-square",
		"fa fa-fast-backward","fa fa-fast-forward","fa fa-fax","fa fa-female","fa fa-fighter-jet","fa fa-file","fa fa-file-archive-o","fa fa-file-audio-o","fa fa-file-code-o","fa fa-file-excel-o","fa fa-file-image-o","fa fa-file-o","fa fa-file-pdf-o",
		"fa fa-file-powerpoint-o","fa fa-file-text","fa fa-file-text-o","fa fa-file-video-o","fa fa-file-word-o","fa fa-files-o","fa fa-film","fa fa-filter","fa fa-fire","fa fa-fire-extinguisher","fa fa-firefox","fa fa-first-order","fa fa-flag",
		"fa fa-flag-checkered","fa fa-flag-o","fa fa-flask","fa fa-flickr","fa fa-floppy-o","fa fa-folder","fa fa-folder-o","fa fa-folder-open","fa fa-folder-open-o","fa fa-font","fa fa-font-awesome","fa fa-fonticons","fa fa-fort-awesome","fa fa-forumbee",
		"fa fa-forward","fa fa-foursquare","fa fa-free-code-camp","fa fa-frown-o","fa fa-futbol-o","fa fa-gamepad","fa fa-gavel","fa fa-gbp","fa fa-genderless","fa fa-get-pocket","fa fa-gg","fa fa-gg-circle","fa fa-gift","fa fa-git","fa fa-git-square",
		"fa fa-github","fa fa-github-alt","fa fa-github-square","fa fa-gitlab","fa fa-glass","fa fa-glide","fa fa-glide-g","fa fa-globe","fa fa-google","fa fa-google-plus","fa fa-google-plus-official","fa fa-google-plus-square","fa fa-google-wallet",
		"fa fa-graduation-cap","fa fa-gratipay","fa fa-grav","fa fa-h-square","fa fa-hacker-news","fa fa-hand-lizard-o","fa fa-hand-o-down","fa fa-hand-o-left","fa fa-hand-o-right","fa fa-hand-o-up","fa fa-hand-paper-o","fa fa-hand-peace-o",
		"fa fa-hand-pointer-o","fa fa-hand-rock-o","fa fa-hand-scissors-o","fa fa-hand-spock-o","fa fa-handshake-o","fa fa-hashtag","fa fa-hdd-o","fa fa-header","fa fa-headphones","fa fa-heart","fa fa-heart-o","fa fa-heartbeat","fa fa-history",
		"fa fa-home","fa fa-hospital-o","fa fa-hourglass","fa fa-hourglass-end","fa fa-hourglass-half","fa fa-hourglass-o","fa fa-hourglass-start","fa fa-houzz","fa fa-html5","fa fa-i-cursor","fa fa-id-badge","fa fa-id-card","fa fa-id-card-o",
		"fa fa-ils","fa fa-imdb","fa fa-inbox","fa fa-indent","fa fa-industry","fa fa-info","fa fa-info-circle","fa fa-inr","fa fa-instagram","fa fa-internet-explorer","fa fa-ioxhost","fa fa-italic","fa fa-joomla","fa fa-jpy","fa fa-jsfiddle","fa fa-key",
		"fa fa-keyboard-o","fa fa-krw","fa fa-language","fa fa-laptop","fa fa-lastfm","fa fa-lastfm-square","fa fa-leaf","fa fa-leanpub","fa fa-lemon-o","fa fa-level-down","fa fa-level-up","fa fa-life-ring","fa fa-lightbulb-o","fa fa-line-chart",
		"fa fa-link","fa fa-linkedin","fa fa-linkedin-square","fa fa-linode","fa fa-linux","fa fa-list","fa fa-list-alt","fa fa-list-ol","fa fa-list-ul","fa fa-location-arrow","fa fa-lock","fa fa-long-arrow-down","fa fa-long-arrow-left",
		"fa fa-long-arrow-right","fa fa-long-arrow-up","fa fa-low-vision","fa fa-magic","fa fa-magnet","fa fa-male","fa fa-map","fa fa-map-marker","fa fa-map-o","fa fa-map-pin","fa fa-map-signs","fa fa-mars","fa fa-mars-double","fa fa-mars-stroke",
		"fa fa-mars-stroke-h","fa fa-mars-stroke-v","fa fa-maxcdn","fa fa-meanpath","fa fa-medium","fa fa-medkit","fa fa-meetup","fa fa-meh-o","fa fa-mercury","fa fa-microchip","fa fa-microphone","fa fa-microphone-slash","fa fa-minus",
		"fa fa-minus-circle","fa fa-minus-square","fa fa-minus-square-o","fa fa-mixcloud","fa fa-mobile","fa fa-modx","fa fa-money","fa fa-moon-o","fa fa-motorcycle","fa fa-mouse-pointer","fa fa-music","fa fa-neuter","fa fa-newspaper-o",
		"fa fa-object-group","fa fa-object-ungroup","fa fa-odnoklassniki","fa fa-odnoklassniki-square","fa fa-opencart","fa fa-openid","fa fa-opera","fa fa-optin-monster","fa fa-outdent","fa fa-pagelines","fa fa-paint-brush","fa fa-paper-plane",
		"fa fa-paper-plane-o","fa fa-paperclip","fa fa-paragraph","fa fa-pause","fa fa-pause-circle","fa fa-pause-circle-o","fa fa-paw","fa fa-paypal","fa fa-pencil","fa fa-pencil-square","fa fa-pencil-square-o","fa fa-percent","fa fa-phone",
		"fa fa-phone-square","fa fa-picture-o","fa fa-pie-chart","fa fa-pied-piper","fa fa-pied-piper-alt","fa fa-pied-piper-pp","fa fa-pinterest","fa fa-pinterest-p","fa fa-pinterest-square","fa fa-plane","fa fa-play","fa fa-play-circle",
		"fa fa-play-circle-o","fa fa-plug","fa fa-plus","fa fa-plus-circle","fa fa-plus-square","fa fa-plus-square-o","fa fa-podcast","fa fa-power-off","fa fa-print","fa fa-product-hunt","fa fa-puzzle-piece","fa fa-qq","fa fa-qrcode","fa fa-question",
		"fa fa-question-circle","fa fa-question-circle-o","fa fa-quora","fa fa-quote-left","fa fa-quote-right","fa fa-random","fa fa-ravelry","fa fa-rebel","fa fa-recycle","fa fa-reddit","fa fa-reddit-alien","fa fa-reddit-square","fa fa-refresh",
		"fa fa-registered","fa fa-renren","fa fa-repeat","fa fa-reply","fa fa-reply-all","fa fa-retweet","fa fa-road","fa fa-rocket","fa fa-rss","fa fa-rss-square","fa fa-rub","fa fa-safari","fa fa-scissors","fa fa-scribd","fa fa-search","fa fa-search-minus",
		"fa fa-search-plus","fa fa-sellsy","fa fa-server","fa fa-share","fa fa-share-alt","fa fa-share-alt-square","fa fa-share-square","fa fa-share-square-o","fa fa-shield","fa fa-ship","fa fa-shirtsinbulk","fa fa-shopping-bag","fa fa-shopping-basket",
		"fa fa-shopping-cart","fa fa-shower","fa fa-sign-in","fa fa-sign-language","fa fa-sign-out","fa fa-signal","fa fa-simplybuilt","fa fa-sitemap","fa fa-skyatlas","fa fa-skype","fa fa-slack","fa fa-sliders","fa fa-slideshare","fa fa-smile-o",
		"fa fa-snapchat","fa fa-snapchat-ghost","fa fa-snapchat-square","fa fa-snowflake-o","fa fa-sort","fa fa-sort-alpha-asc","fa fa-sort-alpha-desc","fa fa-sort-amount-asc","fa fa-sort-amount-desc","fa fa-sort-asc","fa fa-sort-desc",
		"fa fa-sort-numeric-asc","fa fa-sort-numeric-desc","fa fa-soundcloud","fa fa-space-shuttle","fa fa-spinner","fa fa-spoon","fa fa-spotify","fa fa-square","fa fa-square-o","fa fa-stack-exchange","fa fa-stack-overflow","fa fa-star",
		"fa fa-star-half","fa fa-star-half-o","fa fa-star-o","fa fa-steam","fa fa-steam-square","fa fa-step-backward","fa fa-step-forward","fa fa-stethoscope","fa fa-sticky-note","fa fa-sticky-note-o","fa fa-stop","fa fa-stop-circle",
		"fa fa-stop-circle-o","fa fa-street-view","fa fa-strikethrough","fa fa-stumbleupon","fa fa-stumbleupon-circle","fa fa-subscript","fa fa-subway","fa fa-suitcase","fa fa-sun-o","fa fa-superpowers","fa fa-superscript","fa fa-table",
		"fa fa-tablet","fa fa-tachometer","fa fa-tag","fa fa-tags","fa fa-tasks","fa fa-taxi","fa fa-telegram","fa fa-television","fa fa-tencent-weibo","fa fa-terminal","fa fa-text-height","fa fa-text-width","fa fa-th","fa fa-th-large","fa fa-th-list",
		"fa fa-themeisle","fa fa-thermometer-empty","fa fa-thermometer-full","fa fa-thermometer-half","fa fa-thermometer-quarter","fa fa-thermometer-three-quarters","fa fa-thumb-tack","fa fa-thumbs-down","fa fa-thumbs-o-down",
		"fa fa-thumbs-o-up","fa fa-thumbs-up","fa fa-ticket","fa fa-times","fa fa-times-circle","fa fa-times-circle-o","fa fa-tint","fa fa-toggle-off","fa fa-toggle-on","fa fa-trademark","fa fa-train","fa fa-transgender","fa fa-transgender-alt",
		"fa fa-trash","fa fa-trash-o","fa fa-tree","fa fa-trello","fa fa-tripadvisor","fa fa-trophy","fa fa-truck","fa fa-try","fa fa-tty","fa fa-tumblr","fa fa-tumblr-square","fa fa-twitch","fa fa-twitter","fa fa-twitter-square","fa fa-umbrella",
		"fa fa-underline","fa fa-undo","fa fa-universal-access","fa fa-university","fa fa-unlock","fa fa-unlock-alt","fa fa-upload","fa fa-usb","fa fa-usd","fa fa-user","fa fa-user-circle","fa fa-user-circle-o","fa fa-user-md","fa fa-user-o",
		"fa fa-user-plus","fa fa-user-secret","fa fa-user-times","fa fa-users","fa fa-venus","fa fa-venus-double","fa fa-venus-mars","fa fa-viacoin","fa fa-viadeo","fa fa-viadeo-square","fa fa-video-camera","fa fa-vimeo","fa fa-vimeo-square","fa fa-vine",
		"fa fa-vk","fa fa-volume-control-phone","fa fa-volume-down","fa fa-volume-off","fa fa-volume-up","fa fa-weibo","fa fa-weixin","fa fa-whatsapp","fa fa-wheelchair","fa fa-wheelchair-alt","fa fa-wifi","fa fa-wikipedia-w","fa fa-window-close",
		"fa fa-window-close-o","fa fa-window-maximize","fa fa-window-minimize","fa fa-window-restore","fa fa-windows","fa fa-wordpress","fa fa-wpbeginner","fa fa-wpexplorer","fa fa-wpforms","fa fa-wrench","fa fa-xing","fa fa-xing-square",
		"fa fa-y-combinator","fa fa-yahoo","fa fa-yelp","fa fa-yoast","fa fa-youtube","fa fa-youtube-play","fa fa-youtube-square");
	}
    
}
