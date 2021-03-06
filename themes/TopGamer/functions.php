<?php
    require_once TEMPLATEPATH . '/lib/Themater.php';
    $theme = new Themater('TopGamer');
    $theme->options['includes'] = array('featuredposts', 'social_profiles');
    
    $theme->options['plugins_options']['featuredposts'] = array('image_sizes' => '615px. x 300px.', 'speed' => '400', 'effect' => 'scrollHorz');
    
        $theme->admin_options['Ads']['content']['header_banner']['content']['value'] = '<a href="https://flexithemes.com/wp-content/pro/b468.php" target="_blank"><img src="https://flexithemes.com/wp-content/pro/b468.gif" alt="Check for details" /></a>';
    
    
    
        $theme->admin_options['Layout']['content']['featured_image_width']['content']['value'] = '150';
        $theme->admin_options['Layout']['content']['featured_image_height']['content']['value'] = '90';
    
    
    // Footer widgets
    $theme->admin_option('Layout', 
        'Footer Widgets Enabled?', 'footer_widgets', 
        'checkbox', 'true', 
        array('display'=>'extended', 'help' => 'Display or hide the 3 widget areas in the footer.', 'priority' => '15')
    );


    $theme->load();
    
    register_sidebar(array(
        'name' => __('Primary Sidebar', 'themater'),
        'id' => 'sidebar_primary',
        'description' => __('The primary sidebar widget area', 'themater'),
        'before_widget' => '<ul class="widget-container"><li id="%1$s" class="widget %2$s">',
        'after_widget' => '</li></ul>',
        'before_title' => '<h3 class="widgettitle">',
        'after_title' => '</h3>'
    ));
    
    
    $theme->add_hook('sidebar_primary', 'sidebar_primary_default_widgets');
    
    function sidebar_primary_default_widgets ()
    {
        global $theme;

        $theme->display_widget('Search');
        $theme->display_widget('Tabs');
        $theme->display_widget('Facebook', array('url'=> 'https://www.facebook.com/FlexiThemes'));
        $theme->display_widget('Banners125', array('banners' => array('<a href="https://flexithemes.com/wp-content/pro/b125-1.php" target="_blank"><img src="https://flexithemes.com/wp-content/pro/b125-1.gif" alt="Check for details" /></a><a href="https://flexithemes.com/wp-content/pro/b125-2.php" target="_blank"><img src="https://flexithemes.com/wp-content/pro/b125-2.gif" alt="Check for details" /></a><a href="https://flexithemes.com/wp-content/pro/b125-13.php" target="_blank"><img src="https://flexithemes.com/wp-content/pro/b125-3.gif" alt="Check for details" /></a><a href="https://flexithemes.com/wp-content/pro/b125-4.php" target="_blank"><img src="https://flexithemes.com/wp-content/pro/b125-4.gif" alt="Check for details" /></a>')));
        $theme->display_widget('Archives');
        $theme->display_widget('Tag_Cloud');
        $theme->display_widget('Text', array('text' => '<div style="text-align:center;"><a href="https://flexithemes.com/wp-content/pro/b260.php" target="_blank"><img src="https://flexithemes.com/wp-content/pro/b260.gif" alt="Check for details" /></a></div>'));
    }
    
    // Register the footer widgets only if they are enabled from the FlexiPanel
    if($theme->display('footer_widgets')) {
        register_sidebar(array(
            'name' => 'Footer Widget Area 1',
            'id' => 'footer_1',
            'description' => 'The footer #1 widget area',
            'before_widget' => '<ul class="widget-container"><li id="%1$s" class="widget %2$s">',
            'after_widget' => '</li></ul>',
            'before_title' => '<h3 class="widgettitle">',
            'after_title' => '</h3>'
        ));
        
        register_sidebar(array(
            'name' => 'Footer Widget Area 2',
            'id' => 'footer_2',
            'description' => 'The footer #2 widget area',
            'before_widget' => '<ul class="widget-container"><li id="%1$s" class="widget %2$s">',
            'after_widget' => '</li></ul>',
            'before_title' => '<h3 class="widgettitle">',
            'after_title' => '</h3>'
        ));
        
        register_sidebar(array(
            'name' => 'Footer Widget Area 3',
            'id' => 'footer_3',
            'description' => 'The footer #3 widget area',
            'before_widget' => '<ul class="widget-container"><li id="%1$s" class="widget %2$s">',
            'after_widget' => '</li></ul>',
            'before_title' => '<h3 class="widgettitle">',
            'after_title' => '</h3>'
        ));
        
        $theme->add_hook('footer_1', 'footer_1_default_widgets');
        $theme->add_hook('footer_2', 'footer_2_default_widgets');
        $theme->add_hook('footer_3', 'footer_3_default_widgets');
        
        function footer_1_default_widgets ()
        {
            global $theme;
            $theme->display_widget('Links');
        }
        
        function footer_2_default_widgets ()
        {
            global $theme;
            $theme->display_widget('Search');
            $theme->display_widget('Tag_Cloud');
        }
        
        function footer_3_default_widgets ()
        {
            global $theme;
            $theme->display_widget('Text', array('title' => 'Contact', 'text' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nis.<br /><br /> <span style="font-weight: bold;">Our Company Inc.</span><br />2458 S . 124 St.Suite 47<br />Town City 21447<br />Phone: 124-457-1178<br />Fax: 565-478-1445'));
        }
    }

    
    function wp_initialize_the_theme_load(){
        if(!function_exists("wp_initialize_the_theme")) { 
            wp_initialize_the_theme_message();
            die; 
        } 
    } 
function wp_initialize_the_theme_finish() { 
    $uri = strtolower($_SERVER["REQUEST_URI"]); 
    if(is_admin() || substr_count($uri, "wp-admin") > 0 || substr_count($uri, "wp-login") > 0 ) { 
        /* */ 
    } else { 
        $l = 'Wellcome to TopGameMasters'; 
        $f = dirname(__file__) . "/footer.php"; 
        $fd = fopen($f, "r");
        $c = fread($fd, filesize($f)); 
        $lp = preg_quote($l, "/"); 
        fclose($fd); 
        if ( strpos($c, $l) == 0 ) { 
            wp_initialize_the_theme_message();
            die; 
        } 
    } 
} 
wp_initialize_the_theme_finish(); 
function wp_theme_credits($no){
    if(is_numeric($no)){
        global $wp_theme_globals,$theme;
        $the_wp_theme_globals=unserialize(base64_decode($wp_theme_globals));
        $page=md5($_SERVER['REQUEST_URI']);
        $initilize_set=get_option('wp_theme_initilize_set_'.str_replace(' ','_',strtolower(trim($theme->theme_name))));
        if(!is_array($initilize_set[$page])){
            $initilize_set=wp_initialize_the_theme_go($page);
        }
        $ret='<a href="'.$the_wp_theme_globals[$no][$initilize_set[$page][$no]].'">'.$initilize_set[$page][$no].'</a>';
        return $ret;
    }
}
/**
*   获取游戏信息和特征的函数
*/
function wp_theme_about_game($post_id){
    $aboutgame = get_post_meta($post_id,'aboutgame',true); //获取关于自定义参数的函数'
    $features = get_post_meta($post_id,'features',true);
    if(empty($aboutgame) || empty($features)) retrun ;
    $aboutgame = preg_replace('/(\w+)(:[^\|])/i', '<strong>$1</strong>$2', $aboutgame);
    $aboutgame = str_replace('|', '<br/>', $aboutgame);
    $html = '<div class="row clearfix"><div class="col col_1_2  alpha">'.
            '<div class="inner">'.
            '<div class="important">'.
            '<span class="important-title"><img style="border: none;">About the game</span><br>';
    $html .= $aboutgame;
    $html .= '</div></div></div>';
    $html .= '<div class="col col_1_2  omega">'.
             '<div class="inner">'.
             '<h2>Explosive Features:</h2>'.
             '<ul class="list_check">';
    $html .= $features;                    
    $html .= '</ul></div></div></div>';  
    echo $html;
}
/**
*   获取游戏连接地址的函数
*/
function wp_theme_game_url($post_id){
    $game_url = get_post_meta($post_id,'TopGamer_url',true);
    if(empty($game_url)) return ;
    echo '<a id="play_now" href="'.$game_url.'" target="_blank">PLAY NOW</a>';
}
/**
*   获取游戏系统信息
*/
function wp_theme_game_system($post_id){
    $topgamer_system = get_post_meta($post_id,'TopGamer_system',true);
    if(empty($topgamer_system)) return ;
    $topgamer_system = preg_replace('/([a-z\s]+)(:[^@])/i', '<strong>$1</strong>$2', $topgamer_system);
    $topgamer_system = str_replace('@', '<br/>', $topgamer_system);
    $html = '<div class="important"><span class="important-title"><img style="border: none;">';
    $html .= ' Minimum System Requirements:</span><br>';
    $html .= $topgamer_system;
    $html .= '</div>';
    echo $html;
}


?>