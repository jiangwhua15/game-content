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
        <a id="play_now" href="<?php echo get_post_meta($post->ID,'TopGame_url',true);?>" target="_blank">PLAY NOW</a>
		<div class="row clearfix">
			<div class="col col_1_2  alpha">
				<div class="inner">
					<div class="important">
					
						<span class="important-title"><img style="border: none;">About the game</span><br>
						<?php
								global $gameTitle,$gameStatus,$gameGrapics,$gameGenre,$gameDeveloper,$gamePublisher;
								
								function the_about_game($postid){
									global $gameTitle,$gameStatus,$gameGrapics,$gameGenre,$gameDeveloper,$gamePublisher;
									$info = get_post_meta($postid,'aboutgame',true);
									preg_match('/Title:(.*)\|/Ui',$info,$matchs);
									$gameTitle = empty($matchs[1]) ? '--' : $matchs[1];
									preg_match('/Status:(.*)\|/Ui',$info,$matchs);
									$gameStatus = empty($matchs[1]) ? '--' : $matchs[1];
									preg_match('/Graphics:(.*)\|/Ui',$info,$matchs);
									$gameGrapics = empty($matchs[1]) ? '--' : $matchs[1];
									preg_match('/Genre:(.*)\|/Ui',$info,$matchs);
									$gameGenre = empty($matchs[1]) ? '--' : $matchs[1];
									preg_match('/Developer:(.*)\|/Ui',$info,$matchs);
									$gameDeveloper = empty($matchs[1]) ? '--' : $matchs[1];
									preg_match('/Publisher:(.*)/i',$info,$matchs);
									$gamePublisher = empty($matchs[1]) ? '--' : $matchs[1];
								}		
								the_about_game($post->ID);


						?>
						<strong>Title:</strong><?php echo $gameTitle; ?><br>
						<strong>Status:</strong> <?php echo $gameStatus; ?>  <br>
						<strong>Graphics:</strong> <?php echo $gameGrapics; ?><br>
						<strong>Genre:</strong> <a title="<?php echo $gameGenre; ?>" href="" hidefocus="true" style="outline: none;"><?php echo $gameGenre; ?></a><br>
						<strong>Developer:</strong> <?php echo $gameDeveloper; ?><br>
						<strong>Publisher:</strong> <?php echo $gamePublisher; ?>
					</div>
				</div>
			</div>
			<div class="col col_1_2  omega">
				<div class="inner">
					<h2>Explosive Features:</h2>
					<ul class="list_check">
						<?php
							$features = get_post_meta($post->ID,'features',true);
							$features = explode('@',$features);
							foreach($features as $val){
								echo '<li>'.$val.'</li>';
							}
						?>
					</ul>
				</div>
			</div>
		</div>
		
		
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
        	$System = get_post_meta($post->ID,"TopGamer_system",true);

				if(!empty($System)){	

				preg_match("/OS:(.*)@/Ui", $System,$matchs);
				$_os = empty($matchs[1]) ? '--' : $matchs[1];
				preg_match("/Processor:(.*)@/Ui", $System,$matchs);
				$_processor = empty($matchs[1]) ? '--' : $matchs[1];
				preg_match("/Ram:(.*)@/Ui", $System,$matchs);
				$_ram = empty($matchs[1]) ? '--' : $matchs[1];
				preg_match("/Space:(.*)@/Ui", $System,$matchs);
				$_space = empty($matchs[1]) ? '--' : $matchs[1];
				preg_match("/Card:(.*)/i", $System,$matchs);
				$_card = empty($matchs[1]) ? '--' : $matchs[1];
        ?>
        <div class="important">
			<span class="important-title"><img style="border: none;"><?php the_title();?> Minimum System Requirements:</span><br>
			<strong>OS:</strong> <?php echo $_os;?><br>
			<strong>Processor:</strong> <?php echo $_processor;?><br>
			<strong>Memory Ram:</strong> <?php echo $_ram;?><br>
			<strong>Hard Disk Space:</strong> <?php echo $_space;?><br>
			<strong>Video Card:</strong> <?php echo $_card;?>
			
		</div>
		<?php } ?>
    </div><!-- Post ID <?php the_ID(); ?> -->
    
    <?php 
        if(comments_open( get_the_ID() ))  {
            comments_template('', true); 
        }
    ?>