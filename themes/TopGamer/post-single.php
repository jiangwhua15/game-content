<?php global $theme; ?>
	
	<style>
		.row .col{float:left;color:#ccc;}
		.row .col_1_2{width:48%;}
		.important {
			border: 1px solid #fff;
			padding: 15px;
			margin: 25px 0 10px 0;
			position: relative;
		}
		.important strong{color:#fff;line-height:20px;}
		span.important-title {
			background: #fff;
			color: #333;
			position: absolute;
			display: block;
			top: -0.8em;
			left: 10px;
			padding: 3px 8px;
			font-size: 100%;
		}
		.omega{
			margin: 20px 0 20px 0;
			padding-left: 20px;
		}
		ul.list_check li{
			margin-bottom:10px;
		}
		#play_now{
			display:block;
			padding: 10px;
			background: #f43302 none repeat scroll 0 0;
			width: 100%;
			height:20px;
			color:#fff;
			font-weight: 700;
			line-height: 20px;
			text-align: center;
		}	
	</style>
	
    <div <?php post_class('post post-single clearfix'); ?> id="post-<?php the_ID(); ?>">
    
        <h2 class="title"><?php the_title(); ?></h2>
        <?php
                if(has_post_thumbnail())  {
                    the_post_thumbnail(
                        array($theme->get_option('featured_image_width_single'), $theme->get_option('featured_image_height_single')),
                        array("class" => $theme->get_option('featured_image_position_single') . " featured_image")
                    );
                } 
            ?>
        <!-- 获取游戏连接地址的函数-->
        <?php wp_theme_game_url($post->ID); ?>
		<!-- 获取关于游戏和特征的信息 -->	
		<?php wp_theme_about_game($post->ID); ?>	
		
		<div class="entry clearfix">
            
            <?php
                the_content('');
                wp_link_pages( array( 'before' => '<p><strong>' . __( 'Pages:', 'themater' ) . '</strong>', 'after' => '</p>' ) );
            ?>
    
        </div>
        
        <?php if(get_the_tags()) {
                ?><div class="postmeta-secondary"><span class="meta_tags"><?php the_tags('', ', ', ''); ?></span></div><?php
            }
        ?> 
        <?php 
        	wp_theme_game_system($post->ID);
        ?>
    </div><!-- Post ID <?php the_ID(); ?> -->
    
    <?php 
        if(comments_open( get_the_ID() ))  {
            comments_template('', true); 
        }
    ?>