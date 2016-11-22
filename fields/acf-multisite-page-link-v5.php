<?php

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


// check if class already exists
if( !class_exists('acf_field_multisite_page_link') ) :


class acf_field_multisite_page_link extends acf_field {


	/*
	*  __construct
	*
	*  This function will setup the field type data
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/

	function __construct( $settings ) {

		/*
		*  name (string) Single word, no spaces. Underscores allowed
		*/

		$this->name = 'multisite-page-link';


		/*
		*  label (string) Multiple words, can include spaces, visible when selecting a field type
		*/

		$this->label = __('Multisite Page Link', 'acf-multisite-page-link');


		/*
		*  category (string) basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME
		*/

		$this->category = 'relational';


		/*
		*  defaults (array) Array of default settings which are merged into the field object. These are used later in settings
		*/

		$this->defaults = array(
      'post_type'     => array(),
      'sites'         => array(),
      'taxonomy'      => array(),
      'allow_null'    => 0,
      'multiple'      => 0,
      'allow_archives'  => 0
    );


		/*
		*  l10n (array) Array of strings that are used in JavaScript. This allows JS strings to be translated in PHP and loaded via:
		*  var message = acf._e('multisite-page-link', 'error');
		*/

		$this->l10n = array(
			'error'	=> __('Error! Please enter a higher value', 'acf-multisite-page-link'),
		);


    /*
    *   ajax actions to handle queries
    */
    add_action('wp_ajax_acf/fields/multisite-page-link/query',      array($this, 'ajax_query'));
    add_action('wp_ajax_nopriv_acf/fields/multisite-page-link/query',   array($this, 'ajax_query'));


		/*
		*  settings (array) Store plugin settings (url, path, version) as a reference for later use with assets
		*/

		$this->settings = $settings;


		// do not delete!
    	parent::__construct();

	}


	/*
	*  render_field_settings()
	*
	*  Create extra settings for your field. These are visible when editing a field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/

	function render_field_settings( $field ) {

		/*
		*  acf_render_field_setting
		*
		*  This function will create a setting for your field. Simply pass the $field parameter and an array of field settings.
		*  The array of settings does not require a `value` or `prefix`; These settings are found from the $field array.
		*
		*  More than one setting can be added by copy/paste the above code.
		*  Please note that you must also have a matching $defaults value for the field name (font_size)
		*/

    // post_type
    acf_render_field_setting( $field, array(
      'label'     => __('Filter by Post Type','acf'),
      'instructions'  => '',
      'type'      => 'select',
      'name'      => 'post_type',
      'choices'   => acf_get_pretty_post_types(),
      'multiple'    => 1,
      'ui'      => 1,
      'allow_null'  => 1,
      'placeholder' => __("All post types",'acf'),
    ));

    // site
    $sites = [];
    foreach ( get_sites() as $site ) {
      $id = $site->blog_id;
      $details = get_blog_details( $id );
      $sites[$id] = $details->blogname;
    }
    acf_render_field_setting( $field, array(
      'label'     => __('Select from these sites','acf'),
      'instructions'  => '',
      'type'      => 'select',
      'name'      => 'sites',
      'choices'   => $sites,
      'multiple'    => 1,
      'ui'      => 1,
      'allow_null'  => 1,
      'placeholder' => __("All sites",'acf'),
    ));

    // allow_null
    acf_render_field_setting( $field, array(
      'label'     => __('Allow Null?','acf'),
      'instructions'  => '',
      'type'      => 'radio',
      'name'      => 'allow_null',
      'choices'   => array(
        1       => __("Yes",'acf'),
        0       => __("No",'acf'),
      ),
      'layout'  =>  'horizontal',
    ));

	}



	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field (array) the $field being rendered
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/

	function render_field( $field ) {


		/*
		*  Review the data of $field.
		*  This will show what data is available
		*/


    // Change Field into a select
    $field['type'] = 'select';
    $field['ui'] = 1;
    $field['ajax'] = 1;
    $field['choices'] = array();


    // set choices
    if( !empty($field['value']) ) {

      $value = $field['value'];

      // Handle regular link fields
      if ( is_numeric( $value ) ) {
        $value = $this->get_value_from_id( $value );
      } else {
        $value = $this->decode_value( $value );
      }

      // append to choices
      $field['choices'][ $field['value'] ] = $this->get_post_title( get_post( $value['id'] ), $field );

    }


    // render
    acf_render_field( $field );

	}


	/*
	*  input_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
	*  Use this action to add CSS + JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/



	function input_admin_enqueue_scripts() {

		// vars
		$url = $this->settings['url'];
		$version = $this->settings['version'];


		// register & include JS
		wp_register_script( 'acf-input-multisite-page-link', "{$url}assets/js/input.js", array('acf-input'), $version );
		wp_enqueue_script('acf-input-multisite-page-link');


		// register & include CSS
		wp_register_style( 'acf-input-multisite-page-link', "{$url}assets/css/input.css", array('acf-input'), $version );
		wp_enqueue_style('acf-input-multisite-page-link');

	}



	/*
  *  update_value()
  *
  *  This filter is appied to the $value before it is updated in the db
  *
  *  @type  filter
  *  @since 3.6
  *  @date  23/01/13
  *
  *  @param $value - the value which will be saved in the database
  *  @param $post_id - the $post_id of which the value will be saved
  *  @param $field - the field array holding all the field options
  *
  *  @return  $value - the modified value
  */

  function update_value( $value, $post_id, $field ) {

    // validate
    if( empty($value) ) {

      return $value;

    }

    // return
    return $value;

  }


	/*
  *  format_value()
  *
  *  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
  *
  *  @type  filter
  *  @since 3.6
  *  @date  23/01/13
  *
  *  @param $value (mixed) the value which was loaded from the database
  *  @param $post_id (mixed) the $post_id from which the value was loaded
  *  @param $field (array) the field array holding all the field options
  *
  *  @return  $value (mixed) the modified value
  */

  function format_value( $value, $post_id, $field ) {

    // ACF4 null
    if( $value === 'null' ) {

      return false;

    }


    // bail early if no value
    if( empty($value) ) {

      return $value;

    }

    if ( is_numeric($value) ) {
      return get_permalink( $value );
    }


    // decode data
    $value = $this->decode_value( $value );

    // store current blog
    $current_blog = get_current_blog_id();

    // get post from target blog
    switch_to_blog( $value['site'] );
    $value = get_permalink( $value['id'] );

    // restore current blog
    switch_to_blog( $current_blog );


    // return value
    return $value;

  }


  /*===============================
  =            Helpers            =
  ===============================*/

  /*
  *  ajax_query
  *
  *  description
  *
  *  @type  function
  *  @date  24/10/13
  *  @since 5.0.0
  *
  *  @param $post_id (int)
  *  @return  $post_id (int)
  */

  function ajax_query() {

    // validate
    if( !acf_verify_ajax() ) die();


    // defaults
      $options = acf_parse_args($_POST, array(
      'post_id'   => 0,
      's'       => '',
      'field_key'   => '',
      'paged'     => 1
    ));


      // vars
      $results = array();
      $args = array();
      $s = false;
      $is_search = false;


    // paged
      $args['posts_per_page'] = -1;
      $args['paged'] = $options['paged'];

      if( $args['paged'] > 1 ) {
        return acf_send_ajax_results(array(
          'results' => $results,
          'limit'   => $args['posts_per_page']
        ));
      }


      // search
    if( $options['s'] !== '' ) {

      // strip slashes (search may be integer)
      $s = wp_unslash( strval($options['s']) );


      // update vars
      $args['s'] = $s;
      $is_search = true;

    }


    // load field
    $field = acf_get_field( $options['field_key'] );
    if( !$field ) die();


    // update $args
    if( !empty($field['post_type']) ) {

      $args['post_type'] = acf_get_array( $field['post_type'] );

    } else {

      $args['post_type'] = acf_get_post_types();

    }

    // filters
    $args = apply_filters('acf/fields/multisite_page_link/query', $args, $field, $options['post_id']);
    $args = apply_filters('acf/fields/multisite_page_link/query/name=' . $field['name'], $args, $field, $options['post_id'] );
    $args = apply_filters('acf/fields/multisite_page_link/query/key=' . $field['key'], $args, $field, $options['post_id'] );



    // get posts grouped by site
    $groups = [];
    $sites = $field['sites'];

    if ( empty( $sites ) ) {
      foreach ( get_sites() as $site ) {
        $sites[] = $site->blog_id;
      }
    }

    foreach ( $sites as $site ) {
      switch_to_blog( $site );
      $posts = get_posts( $args );
      $groups[$site] = $posts;
    }

    restore_current_blog();

    // loop
    if( !empty($groups) ) {

      foreach( $groups as $site_id => $posts ) {

        // blog details
        $details = get_blog_details( $site_id );

        // data
        $data = array(
          'text'    => $details->blogname,
          'children'  => array()
        );

        // Switch to target blog
        switch_to_blog( $site_id );


        // append to $data
        foreach( $posts as $post ) {

          $data['children'][] = $this->get_post_result( $post->ID, $post) ;

        }

        restore_current_blog();

        // append to $results
        $results[] = $data;

      }

    }


    // return
    acf_send_ajax_results(array(
      'results' => $results,
      'limit'   => $args['posts_per_page']
    ));

  }


  /*
  *  get_post_result
  *
  *  This function will return an array containing id, text and maybe description data
  *
  *  @type  function
  *  @date  7/07/2016
  *  @since 5.4.0
  *
  *  @param $id (mixed)
  *  @param $text (string)
  *  @return  (array)
  */

  function get_post_result( $id, $post ) {


    // vars
    $text = $post->post_title;

    $result = array(
      'id'  => $this->encode_value( $this->get_value_from_id( $id ) ),
      'text'  => $text
    );

    // look for parent
    $search = '| ' . __('Parent', 'acf') . ':';
    $pos = strpos($text, $search);

    if( $pos !== false ) {

      $result['description'] = substr($text, $pos+2);
      $result['text'] = substr($text, 0, $pos);

    }

    $post_type = get_post_type_object( $post->post_type );

    $result['text'] .= ' <span class="acf-multisite-page-link-post-type">' . __( $post_type->labels->singular_name ) . '</span>';


    // return
    return $result;

  }

  /**
   *
   * Encodes the value to a string
   *
   */

  function encode_value( $value ) {
    return htmlentities( json_encode( $value ) );
  }

  /**
   *
   * Decodes the value from a string
   *
   */

  function decode_value( $value ) {
    return json_decode( html_entity_decode( $value ), true );
  }

  /**
   *
   * Generates value from post id and current blog
   *
   */

  function get_value_from_id( $id ) {
    return array(
      'site'  => get_current_blog_id(),
      'id'    => $id
    );
  }


  /*
  *  get_post_title
  *
  *  This function returns the HTML for a result
  *
  *  @type  function
  *  @date  1/11/2013
  *  @since 5.0.0
  *
  *  @param $post (object)
  *  @param $field (array)
  *  @param $post_id (int) the post_id to which this value is saved to
  *  @return  (string)
  */

  function get_post_title( $post, $field, $post_id = 0, $is_search = 0 ) {

    // get post_id
    if( !$post_id ) $post_id = acf_get_form_data('post_id');


    // vars
    $title = acf_get_post_title( $post, $is_search );


    // filters
    $title = apply_filters('acf/fields/multisite_page_link/result', $title, $post, $field, $post_id);
    $title = apply_filters('acf/fields/multisite_page_link/result/name=' . $field['_name'], $title, $post, $field, $post_id);
    $title = apply_filters('acf/fields/multisite_page_link/result/key=' . $field['key'], $title, $post, $field, $post_id);


    // return
    return $title;

  }

}


// initialize
new acf_field_multisite_page_link( $this->settings );


// class_exists check
endif;

?>