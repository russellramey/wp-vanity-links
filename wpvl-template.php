<?php 
// If WPVU class exists
if (class_exists('WPVLVanityLinks')) {

    // Set target 
    $target = get_post_meta(get_the_ID(), $WPVLVanityLinks->config['metadata_target'], true );
    $target = ((strpos($target, '://') !== false) ? $target : 'http://' . $target);

    // If target exists, and is not empty
    if($target && $target != ''){
        
        // Set robots header
        header("robots: noindex, nofollow", true);
        // Set redirect/refresh header
        header( "refresh:1;url=" . $target);

        // If user is not logged in
        if(!is_user_logged_in()){
            // Increase view count for link
            $WPVLVanityLinks->wpvl_update_meta_count(get_the_ID());
        }
    }
}

// Get WP Header
get_header();

// Render redirect messaging
echo '<div style="padding: 3rem 1rem; text-align:center;">';
echo '<h1>' . __( 'You are being redirected...', 'text_domain' ) . '</h1>';
echo '<p>' . __( 'If you are not redirected automatically please', 'text_domain' ) . ' <a href="#">' . __('click here', 'text_domain') . '</a></p>';
echo '</div>';

// Get WP Footer
get_footer();
