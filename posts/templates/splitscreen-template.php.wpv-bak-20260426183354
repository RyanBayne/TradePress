<?php
/**
 * Template Name: Clean Page
 * This template will only display the content you entered in the page editor
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body class="cleanpage">
<?php get_header(); ?>
<?php
    while ( have_posts() ) : the_post();  
        the_content();
    endwhile;              
    
    $page_id = get_queried_object_id();
    $meta_array = get_metadata( 'post', $page_id );
    
    $channel_name_one = $meta_array['TradePress_channel_one']['0'];
    $channel_name_two = $meta_array['TradePress_channel_two']['0'];
   
    $header_one = __( 'Broadcaster One', 'tradepress' );
    $header_two = __( 'Broadcaster Two', 'tradepress' );
    if( isset( $meta_array['TradePress_channel_one'] ) ) {
        $header_one = $meta_array['TradePress_channel_one']['0'];
    }
    if( isset( $meta_array['TradePress_channel_two'] ) ) {
        $header_two = $meta_array['TradePress_channel_two']['0'];
    } 
?>
            
<div class="TradePress_splitscreen_row">
  <div class="TradePress_splitscreen_column">
    <h2><?php echo $header_one; ?></h2>
    <p>    <?php
    $parameters =  array(          
        'channel'         => $channel_name_one, 
        'chat'            => 'default', // default|mobile
        'collection'      => '', // Example: https://embed.twitch.tv/?video=v124085610&collection=GMEgKwTQpRQwyA
        'height'          => 600, // 50%|Minimum: 400|Default: 480
        'theme'           => 'light', // light|dark
        'width'           => '100%'  // 80%|100%|Minimum: 340|Default: 940               
    );
    
    $parameters['channel'] = str_replace( '�', '', $parameters['channel'] );
    
    $parameters = json_encode( $parameters );
              
    echo '
    <!-- Add a placeholder for the Twitch embed -->
    <div id="TradePress-embed-everything1"></div>
    
    <!-- Load the Twitch embed script -->
    <script src="https://embed.twitch.tv/embed/v1.js"></script>
                    
    <script type="text/javascript">
      new Twitch.Embed("TradePress-embed-everything1", ' . $parameters . ');
    </script>';
    ?></p>
  </div>
  <div class="TradePress_splitscreen_column">
    <h2><?php echo $header_two; ?></h2>
    <p><?php
    $parameters =  array(          
        'channel'         => $channel_name_two, 
        'chat'            => 'default', // default|mobile
        'collection'      => '', // Example: https://embed.twitch.tv/?video=v124085610&collection=GMEgKwTQpRQwyA
        'height'          => 600, // 50%|Minimum: 400|Default: 480
        'theme'           => 'light', // light|dark
        'width'           => '100%'  // 80%|100%|Minimum: 340|Default: 940               
    );
    
    $parameters['channel'] = str_replace( '�', '', $parameters['channel'] );
    
    $parameters = json_encode( $parameters );
              
    echo '
    <!-- Add a placeholder for the Twitch embed -->
    <div id="TradePress-embed-everything2"></div>
    
    <!-- Load the Twitch embed script -->
    <script src="https://embed.twitch.tv/embed/v1.js"></script>
                    
    <script type="text/javascript">
      new Twitch.Embed("TradePress-embed-everything2", ' . $parameters . ');
    </script>';
    ?></p>
  </div>
</div>



<?php wp_footer(); ?>
</body>
</html>
