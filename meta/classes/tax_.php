<?php
namespace bb_meta;
class taxClass {
  function __construct($singular, $plural, array $posttypes, array $args = array()) {
    $this->plural = $plural;
    $this->singular = $singular;
    $this->taxonomy = str_replace(' ','',strtolower($singular));
    $this->args = $args;
    foreach($posttypes as $key => & $posttype) {
      $posttype = strtolower($posttype);
      add_post_type_support($posttype, 'page-attributes');
    }

    $this->posttypes = $posttypes;
    add_action('init', array(
      $this,
      'tax_mytax'
    ) , 0);
  }

  function tax_mytax() {
    $labels = array(
      'name' => _x(ucfirst($this->singular) , 'taxonomy general name') ,
      'singular_name' => _x(ucfirst($this->singular) , 'taxonomy singular name') ,
      'search_items' => __('Search ' . ucfirst($this->plural)) ,
      'all_items' => __('All ' . ucfirst($this->plural)) ,
      'parent_item' => __('Parent ' . ucfirst($this->singular)) ,
      'parent_item_colon' => __('Parent ' . ucfirst($this->singular) . ':') ,
      'edit_item' => __('Edit ' . ucfirst($this->singular)) ,
      'update_item' => __('Update ' . ucfirst($this->singular)) ,
      'add_new_item' => __('Add New ' . ucfirst($this->singular)) ,
      'new_item_name' => __('New ' . ucfirst($this->singular)) ,
      'menu_name' => __(ucfirst($this->plural)) ,
    );
    $default_args = array(
      'labels' => $labels,
      'hierarchical' => true, // true = categories & false = tags
      'public' => true,
      'show_ui' => true,
      'show_tagcloud' => true,
      'show_in_nav_menus' => true,
      'show_admin_column' => true,
      'update_count_callback' => '_update_generic_term_count',
      'query_var' => $this->taxonomy,
      'rewrite' => array(
        'slug' => $this->taxonomy,
      ),
    );
    $args = array_merge($default_args, $this->args);
    register_taxonomy($this->taxonomy, $this->posttypes, $args);
  }
}
