<?php

/***
 * Plugin Name: Wordpress Vanity Links
 * Description: Create short/vanity marketing friendly urls that redirect to desired target destination.
 * Version: 1.0
 * Author: Russell Ramey
 * Author URI: https://russellramey.dev/
 ***/

class WPVLVanityLinks
{
  /**
     *
     * Configuration array.
     * @var Array
     *
    **/
  public $config = [
    'post_type' => 'wpvl_redirect',
    'metadata_target' => '_wpvl_vanity_redirect_target',
    'metadata_count' => '_wpvl_vanity_redirect_count'
  ];

  /**
     *
     * Class constructor
     *
    **/
  public function __construct()
  {
    add_action('init', [$this, 'wpvl_redirects_post_type']);
    add_action('init', [$this, 'wpvl_redirect_rewrite_rules']);
    add_action("add_meta_boxes", [$this, 'wpvl_redirect_metaboxes']);
    add_action('save_post', [$this, 'wpvl_update_meta_target'], 10, 2 );
    add_action('manage_'.$this->config['post_type'].'_posts_custom_column', [$this, 'wpvl_redirects_posts_column_data'], 10, 2);
    add_filter('manage_'.$this->config['post_type'].'_posts_columns', [$this, 'wpvl_redirects_posts_columns']);
    add_filter('single_template', [$this, 'wpvl_post_template']);
    add_filter('post_type_link', [$this, 'wpvl_set_redirect_permalink'], 10, 3);
  }

  /**
     *
     * Redirect Post Type
     * Regiter custom post type with wordpress
     * @return Function
     *
    **/
  public function wpvl_redirects_post_type()
  {
    $labels = array(
        'name'                => _x( 'Vanity Links', 'Post Type General Name', 'text_domain' ),
        'singular_name'       => _x( 'Vanity Link', 'Post Type Singular Name', 'text_domain' ),
        'menu_name'           => __( 'Vanity Links', 'text_domain' ),
        'parent_item_colon'   => __( 'Parent Item:', 'text_domain' ),
        'all_items'           => __( 'All Vanity Links', 'text_domain' ),
        'view_item'           => __( 'View Vanity Link', 'text_domain' ),
        'add_new_item'        => __( 'Add New Vanity Link', 'text_domain' ),
        'add_new'             => __( 'New Vanity Link', 'text_domain' ),
        'edit_item'           => __( 'Edit Vanity Link', 'text_domain' ),
        'update_item'         => __( 'Update Vanity Link', 'text_domain' ),
        'search_items'        => __( 'Search Vanity Links', 'text_domain' ),
        'not_found'           => __( 'Not found', 'text_domain' ),
        'not_found_in_trash'  => __( 'Not found in Trash', 'text_domain' ),
    );
    $args = array(
        'label'               => __( 'Vanity Links', 'text_domain' ),
        'description'         => __( 'Vanity Link post type', 'text_domain' ),
        'labels'              => $labels,
        'supports'            => array( 'title'),
        'taxonomies'          => array(),
        'hierarchical'        => false,
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => false,
        'show_in_admin_bar'   => true,
        'menu_position'       => 1000,
        'can_export'          => true,
        'has_archive'         => false,
        'exclude_from_search' => true,
        'publicly_queryable'  => true,
        'show_in_rest'        => true,
        'capability_type'     => 'post',
        'menu_icon'           => 'dashicons-admin-links',
        'rewrite'             => false
    );
    return register_post_type( $this->config['post_type'], $args );
  }

  /**
     *
     * Post list columns
     * Add/modify column names on post list screen
     * @param Array $columns
     * @return Array
     *
    **/
  public function wpvl_redirects_posts_columns($columns)
  {
    // Edit/update column names
    unset($columns['date']);
    $columns['url'] = __('Vanity URL', 'text_domain');
    $columns['target_url'] = __('Target URL', 'text_domain');
    $columns['requests'] = __('# Requests', 'text_domain');
    $columns['date_modified'] = __('Last Modified', 'text_domain');
    $columns['date'] = __('Created', 'text_domain');
    // Return columns
    return $columns;
  }

  /**
     *
     * Post list columns data
     * Display custom data for new post data columns
     * @param Object $column
     * @param Object $post
     * @return null
     *
    **/
  public function wpvl_redirects_posts_column_data($column, $post)
  {
    // Post data
    $data = $this->wpvl_redirects_post_data($post);
    // Update column values
    switch($column){
      case 'url':
        echo $data['link'];
        break;
      case 'target_url':
        echo $data['target'];
        break;
      case 'date_modified':
        echo 'Updated<br>';
        echo $data['updated'];
        break;
      case 'requests':
        echo '<b>' . $data['count'] . '</b>';
        break;
    }
    // Return 
    return;
  }

  /**
     *
     * Post Template
     * Custom single post template for custom post type
     * @param Object $page_template
     * @return Object
     *
    **/
  public function wpvl_post_template($page_template)
  {
    // If current page is target post type
    if (get_post_type() === $this->config['post_type']) {
      $page_template = dirname(__FILE__) . '/wpvl-template.php';
    }
    // Return template
    return $page_template;
  }

  /**
     *
     * Custom Post Type Permalint
     * Rewrite a unique permalink for each custom post
     * @param String $post_link
     * @param Object $post
     * @return String
     *
    **/
  public function wpvl_set_redirect_permalink($post_link, $post)
  {
    // If correct post type
    if (isset($post->post_type) && $post->post_type === $this->config['post_type']) {
      $post_link = home_url($post->post_name);
    }
    // Return link
    return $post_link;
  }

  /**
     *
     * Post Rewrite 
     * Specific rewrite rule for custom post type
     * @return Function
     *
    **/
  public function wpvl_redirect_rewrite_rules() {
    return add_rewrite_rule('(.+?)/?$', 'index.php?'.$this->config['post_type'].'=$matches[1]', 'bottom');   
  }

  /**
     *
     * Metabox Markup - Post Name
     * HTML markup for custom metabox
     * @param Object $post
     * @return null
     *
    **/
  public function wpvl_post_name_metabox_markup($post)
  {
    // HTML Render
    echo '<p></p>
          <p>
            <span><b> ' . get_bloginfo('url') . '/ </b></span>
            <span>
              <input name="post_name" type="text" size="13" id="post_name" value="' . $post->post_name . '" style="max-width:200px; width:100%">
            </span>
          </p>
          <p>Enter vainity url. This will be the published URL you can use for markting purposes.<br><b><em>NOTE: Must be a unique value, and is not case sensitive.</em></b></p>';
  }

  /**
     *
     * Metabox Markup - Post Target URL
     * HTML markup for custom metabox
     * @param Object $post
     * @return null
     *
    **/
  public function wpvl_redirect_target_metabox_markup($post)
  {
    // Post data
    $data = $this->wpvl_redirects_post_data($post->ID);
    // Nonce field for security
    wp_nonce_field(basename(__FILE__), $this->config['metadata_target'] . '_nonce');
    // HTML Render
    echo '<p></p>
          <p>
            <span>
              <input name="' . $this->config['metadata_target'] . '" type="text" size="13" style="max-width:480px; width:100%" placeholder="https://target.url" value="' . $data['target'] . '">
            </span>
          </p>
          <p>Enter the target/destination URL. This is the URL where you would like the vanity link to take the user.<br><b><em>NOTE: Must be valid a url.</em></b></p>';
  }

  /**
     *
     * Metabox Markup - Post Stats
     * HTML markup for custom metabox
     * @param Object $post
     * @return null
     *
    **/
  public function wpvl_redirect_information_metabox_markup($post)
  {
    // Post data
    $data = $this->wpvl_redirects_post_data($post->ID);
    // HTML Render
    echo '<table class="wp-list-table" style="width:100%">
            <tr>
              <td width="33%">
                <h3>' . __('Vanity', 'text_domain') . '</h3>
                <p><a href="' . $data['link'] . '" target="_blank">' . $data['link'] . '</a></p>
              </td>
              <td width="33%">
                <h3>'. __('Target', 'text_domain') .'</h3>
                <p><a href="' . $data['target'] . '" target="_blank">' .  $data['target'] . '</a></p>
              </td>
              <td width="33%">
                <h3>'. __('# Requests', 'text_domain') .'</h3>
                <p>' . $data['count'] . '</p>
              </td>
            </tr>
          </table>';
  }

  /**
     *
     * Add custom metaboxes
     * Display custom metaboxes on post edit screen
     * @return null
     *
    **/
  public function wpvl_redirect_metaboxes()
  {
    // Add custom vanity url (post slug) metabox
    add_meta_box("wpvu-vanity-metabox", __("Vanity URL", 'text_domain'), [$this, "wpvl_post_name_metabox_markup"], $this->config['post_type'], "normal", "default", null);
    // Add custom target url (post metadata) metabox
    add_meta_box("wpvu-target-metabox", __("Target URL", 'text_domain'), [$this, "wpvl_redirect_target_metabox_markup"], $this->config['post_type'], "normal", "default", null);
    // Add custom information metabox
    add_meta_box("wpvu-info-metabox", __("Redirect Information", 'text_domain'), [$this, "wpvl_redirect_information_metabox_markup"], $this->config['post_type'], "normal", "default", null);
    // Remove default post slug metabox
    remove_meta_box('slugdiv', $this->config['post_type'], 'normal');
  }

  /**
     *
     * Save Metadata
     * Validate and save metadata for custom post type
     * @param Object $post
     * @return null
     *
    **/
  public function wpvl_update_meta_target($post)
  {
    // If not correct post type
    if( get_post_type() !== $this->config['post_type'] ) return $post;
    // Make sure that it is set.
    if( !isset($_POST[$this->config['metadata_target']] ) || !wp_verify_nonce($_POST[$this->config['metadata_target'] . '_nonce'], basename(__FILE__))) return $post;
    // Check if autosaving.
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post;
    // Check if WP is doing AJAX
    if( defined('DOING_AJAX') && DOING_AJAX ) return $post;
    // Sanitize user input.
    $data = sanitize_text_field( $_POST[$this->config['metadata_target']] );
    // Update the meta field in the database.
    update_post_meta($post, $this->config['metadata_target'], $data );
  }

  /**
     *
     * Update metadata
     * Update metadata for custom post type
     * @param Object $post
     * @return null
     *
    **/
  public function wpvl_update_meta_count($post)
  {
    // Post data
    $data = $this->wpvl_redirects_post_data($post);
    // Update the meta field in the database.
    update_post_meta($post, $this->config['metadata_count'], sanitize_text_field(intval($data['count']) + 1) );
  }

  /**
     *
     * Post data
     * Fetch desired post data to be displayed on edit screens
     * @param Object $post
     * @return Array
     *
    **/
  private function wpvl_redirects_post_data($post){
    return [
      'count' => (get_post_meta($post, $this->config['metadata_count']) ? get_post_meta($post, $this->config['metadata_count'], true) : 0),
      'target' =>  (get_post_meta($post, $this->config['metadata_target']) ? get_post_meta($post, $this->config['metadata_target'], true) : '#'),
      'link'=>  get_the_permalink($post),
      'updated' => get_the_modified_date('Y/m/d \a\t h:i:s', $post)
    ];
  }

}

// Instanciate class
$WPVLVanityLinks = new WPVLVanityLinks;