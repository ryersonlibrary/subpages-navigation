<?php
/**
 * Plugin Name: Subpages Navigation 
 * Plugin URI: https://github.com/ryersonlibrary/subpages-navigation
 * Description: Create subpages navigation menu with sidebar widgets and shortcodes. Legacy plugin support for the Ryerson University Library & Archives website.
 * Author: Ryerson University Library & Archives
 * Author URI: https://github.com/ryersonlibrary/
 * GitHub Plugin URI: https://github.com/ryersonlibrary/subpages-navigation
 * Version: 1.1.5
 */

//if(!defined("SUBPAGE_NAVIGATION_STYLE"))
//  define("SUBPAGE_NAVIGATION_STYLE",true);
/**
 * Add function to widgets_init that'll load our widget.
 * @since 1.0
 */
add_action( 'widgets_init', 'olt_subpages_navigation_load_widgets' );
//if(SUBPAGE_NAVIGATION_STYLE)
add_action( 'init', 'init_subpages_navigation_plugin' );

/**
 * Register our widget.
 * 'olt_subpages_navigation_Widget' is the widget class used below.
 *
 * @since 1.0
 */
function olt_subpages_navigation_load_widgets() {
  register_widget( 'OLT_Subpages_Navigation_Widget' );
}

/**
 * OLT Subpages Navigation Widget class.
 * This class handles everything that needs to be handled with the widget:
 * the settings, form, display, and update.  Nice!
 *
 * @since 1.0
 */
class OLT_Subpages_Navigation_Widget extends WP_Widget {

  /**
   * Widget setup.
   */
  function __construct() {
    /* Widget settings. */
    $widget_ops = array( 'classname' => 'widget_subpages_navigation', 'description' => __('A widget that creates a subpages navigation menu.', 'olt_subpages_navigation') );

    /* Widget control settings. */
    $control_ops = array( 'width' => 400, 'height' => 350, 'id_base' => 'olt-subpages-navigation-widget' );

    /* Create the widget. */
    // $this->WP_Widget( 'olt-subpages-navigation-widget', __('Subpages Navigation', 'olt_subpages_navigation'), $widget_ops, $control_ops );
    // WP_Widget::__construct( string $id_base, string $name, array $widget_options = array(), array $control_options = array() )
    parent::__construct( 'olt-subpages-navigation-widget', __('Subpages Navigation', 'olt_subpages_navigation'), $widget_ops, $control_ops );
  }

  /**
   * How to display the widget on the screen.
   */
  function widget( $args, $instance ) {
    global $post;
    
    
    if(is_page()):
      /* Extract the arguments  */  
      extract( $args );
      extract( $instance);
      
      $root_id = '';
      /* Find the root post */
        if($root == 0): #all pages 
        $root_id = "0";
        $pages = get_pages("sort_column=menu_order");

        elseif($root == 1): # subpages of the top-level page
        $rootPost = $post;
        while ($rootPost->post_parent != 0):
          $rootPost = get_post($rootPost->post_parent);
        endwhile;

        $pages = get_pages("child_of=".$rootPost->ID."&sort_column=menu_order");

        if($top_title):
          $title = $rootPost->post_title;
        endif;
        if($title_link):
          $title_link = get_permalink($rootPost->ID);
        endif;

        else: # subpages of the current page


        if( !$siblings ) :
          $pages = get_pages("child_of=".$post->ID."&sort_column=menu_order");
          $title_link = get_permalink($post->ID);
        else:
          $pages = get_pages("child_of=".$post->post_parent."&sort_column=menu_order");
          $title_link = get_permalink($post->post_parent);
        endif;

        if($top_title):
          $title = $post->post_title;
          $title_link = get_permalink($post->ID);
        endif;


      endif;

      if($top_title):
        if($root_id):
          $root_page = get_page($root_id);
          $title = $root_page->post_title;
          $title_link = get_permalink($root_page->ID);
        endif;
      endif;
      
      /* Our variables from the widget settings. */
      $title = apply_filters('widget_title', $title );

      /* Prepare the walker */
      $walker = new SubpagesNavigationPageList;
      
      if(is_array($pages) && !empty($pages)):
        /* Before widget (defined by themes). */
      echo $before_widget;

      /* Display the widget title if one was specified (before and after defined by themes). */
      if ( $title ) {

        echo $before_title;

        if ( $title_link ) {

          echo "<a href='". $title_link ."'>". $title ."</a>";

        } else {

          echo $title;
          
        }

        echo $after_title;
      }

      $classes = 'subpages-navi subpages-navi-widget';

      if($exclusive)  
        $classes .= ' subpages-navi-exclusive';

      if($collapsible) 
        $classes .= ' subpages-navi-collapsible';

      if($expand) 
        $classes .= ' subpages-navi-auto-expand';

      $depth = ($nested)? '0' : '-1';

      ?>
      <ul class="<?php echo $classes; ?>">
        <?php echo $walker->walk($pages, $depth, array('current_level' => $post->ID)); ?>
      </ul>

      <?php
      
      /* After widget (defined by themes). */
      echo $after_widget;
    endif;

  endif;

}

  /**
   * Update the widget settings.
   */
  function update( $new_instance, $old_instance ) {
    $instance = $old_instance;

    /* Strip tags for title to remove HTML . */
    $instance['title'] = strip_tags( $new_instance['title'] );
    
    /* No need to strip tags  */
    $instance['top_title'] = $new_instance['top_title'];
    $instance['root'] = $new_instance['root'];
    $instance['title_link'] = $new_instance['title_link'];
    $instance['siblings'] = $new_instance['siblings'];
    $instance['nested'] = $new_instance['nested'];
    
    $instance['collapsible'] = $new_instance['collapsible'];
    $instance['exclusive'] = $new_instance['exclusive'];
    $instance['expand'] = $new_instance['expand'];
    
    
    return $instance;

  }

  /**
   * Displays the widget settings controls on the widget panel.
   * Make use of the get_field_id() and get_field_name() function
   * when creating your form elements. This handles the confusing stuff.
   */
  function form( $instance ) {

    /* Our Variables set by the form */
    $defaults = array(
      'title' => 'Navigation',
      'title_link' =>false,
      'top_title'=>true,
      'root' => -1,
      'siblings' => false,
      'nested' => true,
      'collapsible' => true,
      'exclusive' =>true,
      'expand' =>true,
    );
    $instance = wp_parse_args( (array) $instance, $defaults );
    
    
    
    $dt = ' disabled="disabled" style="background-color: #ccc"';
    $dm = ' disabled="disabled" style="color: #999"';
    ?>
    <!-- Widget Title: Text Input -->
    <p>
      <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Widget title:', 'hybrid'); ?></label>
      <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" <?php if($instance['top_title']) echo $dt; ?> class="olt-subpages-title" />
      
      <input type="checkbox" name="<?php echo $this->get_field_name( 'top_title' ); ?>" id="<?php echo $this->get_field_id( 'top_title' ); ?>" value="true" <?php checked($instance['top_title'], "true"); ?> class="olt-subpages-top-title" />
      <label for="<?php echo $this->get_field_id( 'top_title' ); ?>"><?php _e('Use title of root page', 'olt_subpages_navigation'); ?></label>

      <input class="olt-subpages-title-link" type="checkbox" name="<?php echo $this->get_field_name( 'title_link'); ?>" id="<?php echo $this->get_field_id('title_link'); ?>" <?php if(!$instance['top_title']) echo $dm; ?>   value="true" <?php checked($instance['title_link'], "true"); ?> />
      <label class="olt-subpages-title-link" for="<?php echo $this->get_field_id('title_link'); ?>" <?php if(!$instance['top_title']) echo $dm; ?>  ><?php _e('Link the title page?', 'olt_subpages_navigation'); ?></label>

    </p>

    
    <!-- Root: Select Box -->
    <p>
      <label for="<?php echo $this->get_field_id( 'root' ); ?>"><?php _e('Show:', 'olt_subpages_navigation'); ?></label> 
      <select id="<?php echo $this->get_field_id( 'root' ); ?>" name="<?php echo $this->get_field_name( 'root' ); ?>" class="olt-subpages-root">
        <option value="-1" <?php selected($instance['root'], -1); ?>><?php _e('subpages of the current page', 'olt_subpages_navigation'); ?></option>
        <option value="0"  <?php selected($instance['root'], 0); ?>><?php _e('all pages', 'olt_subpages_navigation'); ?></option>
        <option value="1" <?php selected($instance['root'], 1); ?>><?php _e('subpages of the top-level page', 'olt_subpages_navigation'); ?></option>
      </select>
      <input type="checkbox" name="<?php echo $this->get_field_name( 'siblings' ); ?>" id="<?php echo $this->get_field_id( 'siblings' ); ?>" value="true" <?php checked($instance['siblings'], "true"); ?> <?php if($instance['root'] != -1) echo $dm; ?> class="olt-subpages-siblings"/>
      <label for="<?php echo $this->get_field_id( 'siblings' ); ?>" <?php if($instance['root'] != -1) echo $dm; ?> class="olt-subpages-siblings"><?php _e('and its siblings', 'olt_subpages_navigation'); ?></label>
    </p>
    

    <!-- Children nested? Checkbox -->
    <p>
      <input class="checkbox olt-subpages-nested" type="checkbox" <?php checked( $instance['nested'], "on" ); ?> id="<?php echo $this->get_field_id( 'nested' ); ?>" name="<?php echo $this->get_field_name( 'nested' ); ?>" /> 
      <label for="<?php echo $this->get_field_id( 'nested' ); ?>"><?php _e('Nest children pages under their parents?', 'olt_subpages_navigation'); ?></label>
    </p>
    
    <!-- List Collapsible? Checkbox -->
    <p>
      &nbsp;&nbsp;&nbsp;&nbsp;
      <input class="checkbox olt-subpages-nested-options" type="checkbox" <?php checked( $instance['collapsible'], "on" ); ?> <?php if(!$instance['nested']) echo $dm; ?> id="<?php echo $this->get_field_id( 'collapsible' ); ?>" name="<?php echo $this->get_field_name( 'collapsible' ); ?>" /> 
      <label for="<?php echo $this->get_field_id( 'collapsible' ); ?>" <?php if(!$instance['nested']) echo $dm; ?> class="olt-subpages-nested-options"><?php _e('Make the list collapsible?', 'olt_subpages_navigation'); ?></label>
    </p>
    
    
    <!-- Exclusive selection? Checkbox -->
    <p>
      &nbsp;&nbsp;&nbsp;&nbsp;
      <input class="checkbox olt-subpages-nested-options" type="checkbox" <?php checked( $instance['exclusive'], "on" ); ?> id="<?php echo $this->get_field_id( 'exclusive' ); ?>" name="<?php echo $this->get_field_name( 'exclusive' ); ?>" class="olt-subpages-nested-options" <?php if(!$instance['nested']) echo $dm; ?> /> 
      <label for="<?php echo $this->get_field_id( 'exclusive' ); ?>" <?php if(!$instance['nested']) echo $dm; ?> class="olt-subpages-nested-options"><?php _e('Exclusive selection (Accordion style)', 'olt_subpages_navigation'); ?></label>
    </p>
    
    <!-- Auto expand? Checkbox -->
    <p>
      &nbsp;&nbsp;&nbsp;&nbsp;
      <input class="checkbox olt-subpages-nested-options" type="checkbox" <?php if(!$instance['nested']) echo $dm; ?> <?php checked( $instance['expand'], "on" ); ?> id="<?php echo $this->get_field_id( 'expand' ); ?>" name="<?php echo $this->get_field_name( 'expand' ); ?>" class="olt-subpages-nested-options" /> 
      <label for="<?php echo $this->get_field_id( 'expand' ); ?>" <?php if(!$instance['nested']) echo $dm; ?> class="olt-subpages-nested-options"><?php _e('Automatically expand the current level', 'olt_subpages_navigation'); ?></label>
    </p>
    <?php





  }
}


add_shortcode('subpages', 'subpages_navigation_shortcode');
/*
 * Subpage naviagtion shortcode
 *********************************************/
function subpages_navigation_shortcode($atts) {
  global $post;
  extract(shortcode_atts(array(
    'depth' => '0',
    'siblings' => 'false',
    'collapsible' => 'true',
    'exclusive' => 'false',
  ), $atts));

      // Get all subpages of the current page
  $root = ($siblings == 'true')? $post->post_parent : $post->ID;
  $pages = get_pages("child_of={$root}&sort_column=menu_order");

      // Prepare the walker
  $walker = new SubpagesNavigationPageList;

  $output  = '<ul class="subpages-navi';
  if($collapsible == 'true')
    $output .= ' subpages-navi-collapsible';
  if($exclusive == 'true')
    $output .= ' subpages-navi-exclusive';
  $output .= "\">\n";
  $output .= $walker->walk($pages, (int) $depth, array('current_level' => $post->ID));
  $output .= "</ul>\n";

  return $output;
}
/**
 * init_subpages_navigation_plugin function.
 * 
 * @access public
 * @return void
 */ 
function init_subpages_navigation_plugin()
{
  if(!defined("SUBPAGE_NAVIGATION_STYLE"))
    define("SUBPAGE_NAVIGATION_STYLE",true);

  if(!defined("SUBPAGE_NAVIGATION_SCRIPT"))
    define("SUBPAGE_NAVIGATION_SCRIPT",true);
  
  if (!is_admin()) {
    if(SUBPAGE_NAVIGATION_SCRIPT)
      wp_enqueue_script('subpages-navigation', plugins_url('/subpages-navigation/subpages-navigation.js'), array('jquery'));
    
    if(SUBPAGE_NAVIGATION_STYLE){
      if (file_exists(STYLESHEETPATH."/subpages-navigation.css") )
      {
        wp_enqueue_style('subpages-navigation', get_bloginfo('stylesheet_directory').'/subpages-navigation.css');

      }else{
        wp_enqueue_style('subpages-navigation', plugins_url('/subpages-navigation/subpages-navigation.css'));
      }
    }
  }
  
  
  load_plugin_textdomain( 'olt_subpages_navigation', false , basename( dirname( __FILE__ ) ) . '/languages' );
}

add_action("admin_print_scripts-widgets.php","subpages_navigation_plugin_admin");
function subpages_navigation_plugin_admin(){
  wp_enqueue_script('subpages-navigation-admin', plugins_url('/subpages-navigation/subpages-navigation-admin.js'),array('jquery'));

}

class SubpagesNavigationPageList extends Walker {
  var $tree_type = 'page';
  var $db_fields = array ('parent' => 'post_parent', 'id' => 'ID');

  function start_lvl(&$output, $depth = 0, $args = array()) {
    $indent  = str_repeat("    ", $depth+1);
    $output .= $indent."<ul class='children'>\n";
  }

  function end_lvl(&$output, $depth = 0, $args = array()) {
    $indent  = str_repeat("    ", $depth+1);
    $output .= $indent."</ul>\n";
  }

  function start_el(&$output, $object, $depth = 0, $args = array(), $current_object_id = 0) {
    extract($args);
    $lightbox = false;
    $title = esc_html($object->post_title);
    $link  = get_permalink($object->ID);

    $indent  = str_repeat("    ", $depth)."  ";
    $output .= $indent."<li class=\"subpages-navi-node subpages-navi-level-$depth";
    if ($current_level == $object->ID)
      $output .= ' subpages-navi-current-level';
    $output .= "\">\n";
    $output .= $indent."  <a href=\"$link";
    if ($lightbox == true)
      $output .= "?iframe=true&amp;width=600&amp;height=400\" rel=\"prettyPhoto[iframes]";
    $output .= "\">$title</a>\n";
  }
  
  function end_el(&$output, $object, $depth = 0, $args = array()) {
    $indent  = str_repeat("    ", $depth)."  ";
    $output .= $indent."</li>\n";
  }
}
