<?php

/* Register Metaboxes */
add_meta_box( 'create-menu', __('Create Menu'), 'wp_nav_menu_create_metabox', 'menus', 'side', 'core' );
add_meta_box( 'add-custom-links', __('Add Custom Links'), 'wp_nav_menu_item_link_metabox', 'menus', 'side', 'default' );
wp_nav_menu_post_type_metaboxes();
wp_nav_menu_taxonomy_metaboxes();

/**
 * Creates metaboxes for any post type menu item.
 *
 * @since 3.0.0
 */
function wp_nav_menu_post_type_metaboxes() {
	$post_types = get_post_types( array( 'public' => true ), 'object' );

	if ( !$post_types )
		return false;

	$allowed_types = apply_filters('post_types_allowed_in_menus', array('page'));
	foreach ( $post_types as $post_type ) {
		if ( !in_array($post_type->name, $allowed_types) )
			continue;
		$id = $post_type->name;

		add_meta_box( "add-{$id}", sprintf( __('Add an Existing %s'), $post_type->singular_label ), 'wp_nav_menu_item_post_type_metabox', 'menus', 'side', 'default', $post_type );
	}
}

/**
 * Creates metaboxes for any taxonomy menu item.
 *
 * @since 3.0.0
 */
function wp_nav_menu_taxonomy_metaboxes() {
	$taxonomies = get_taxonomies( array( 'show_ui' => true ), 'object' );

	if ( !$taxonomies )
		return false;

	$allowed_types = apply_filters('taxonomies_allowed_in_menus', array('category'));
	foreach ( $taxonomies as $tax ) {
		if ( !in_array($tax->name, $allowed_types) )
			continue;
		$id = $tax->name;

		add_meta_box( "add-{$id}", sprintf( __('Add an Existing %s'), $tax->singular_label ), 'wp_nav_menu_item_taxonomy_metabox', 'menus', 'side', 'default', $tax );
	}
}

/**
 * Displays a metabox for managing the active menu being edited.
 *
 * @since 3.0.0
 */
function wp_nav_menu_manage_menu_metabox( $object, $menu ) { ?>
	<div id="submitpost" class="submitbox">
		<div id="minor-publishing">
			<div class="misc-pub-section misc-pub-section-last">
				<label class="howto" for="menu-name">
					<span><?php _e('Name'); ?></span>
					<input id="menu-name" name="menu-name" type="text" class="regular-text menu-item-textbox" value="<?php esc_attr_e( $menu['args'][1] ); ?>" />
					<br class="clear" />
				</label>
			</div><!--END .misc-pub-section misc-pub-section-last-->
			<br class="clear" />
		</div><!--END #misc-publishing-actions-->
		<div id="major-publishing-actions">
			<div id="delete-action">
				<a class="submitdelete deletion" href="<?php echo wp_nonce_url( admin_url('nav-menus.php?action=delete&amp;menu=' . $menu['args'][0]), 'delete-nav_menu-' . $menu['args'][0] ); ?>"><?php _e('Delete Menu'); ?></a>
			</div><!--END #delete-action-->

			<div id="publishing-action">
				<input class="button-primary" name="save_menu" type="submit" value="<?php esc_attr_e('Save Menu'); ?>" />
			</div><!--END #publishing-action-->
			<br class="clear" />
		</div><!--END #major-publishing-actions-->
	</div><!--END #submitpost .submitbox-->
	<?php
}

/**
 * Displays a metabox for creating a new menu.
 *
 * @since 3.0.0
 */
function wp_nav_menu_create_metabox() { ?>
	<p>
		<input type="text" name="create-menu-name" id="create-menu-name" class="regular-text" value=""  />
		<input type="submit" name="create-menu" id="create-menu" class="button" value="<?php esc_attr_e('Create Menu'); ?>" />
	</p>
	<?php
}

/**
 * Displays a metabox for the custom links menu item.
 *
 * @since 3.0.0
 */
function wp_nav_menu_item_link_metabox() {
	$args = array( 'post_status' => 'any', 'post_type' => 'nav_menu_item', 'meta_value' => 'custom', 'showposts' => -1 );

	// @todo transient caching of these results with proper invalidation on updating links
	$query = new WP_Query( $args );

	?>
	<p id="menu-item-url-wrap">
		<label class="howto" for="menu-item-url">
			<span><?php _e('URL'); ?></span>
			<input id="custom-menu-item-url" name="custom-menu-item-url" type="text" class="code menu-item-textbox" value="http://" />
		</label>
	</p>
	<br class="clear" />
	<p id="menu-item-name-wrap">
		<label class="howto" for="custom-menu-item-name">
			<span><?php _e('Text'); ?></span>
			<input id="custom-menu-item-name" name="custom-menu-item-name" type="text" class="regular-text menu-item-textbox" value="<?php echo esc_attr( __('Menu Item') ); ?>" />
		</label>
	</p>

	<p class="button-controls">
		<a class="show-all"><?php _e('View All'); ?></a>
		<a class="hide-all"><?php _e('Hide All'); ?></a>
	</p>
	<div id="available-links" class="list-wrap">
		<div class="list-container">
			<ul class="list">
				<?php echo wp_nav_menu_get_items( $query->posts, 'custom' ); ?>
			</ul>
		</div><!-- /.list-container-->
	</div><!-- /#available-links-->
	<p class="add-to-menu">
		<a class="button"><?php _e('Add to Menu'); ?></a>
	</p>
	<div class="clear"></div>
	<?php
}

/**
 * Displays a metabox for a post type menu item.
 *
 * @since 3.0.0
 *
 * @param string $object Not used.
 * @param string $post_type The post type object.
 */
function wp_nav_menu_item_post_type_metabox( $object, $post_type ) {
	$args = array( 'post_type' => $post_type['args']->name, 'post_status' => 'publish', 'showposts' => -1 );

	if ( 'attachment' == $post_type['args']->name )
		$args['post_status'] = 'any';

	// @todo transient caching of these results with proper invalidation on updating of a post of this type
	$query = new WP_Query( $args );

	if ( !$query->posts )
		$error = '<li id="error">'. sprintf( __( 'No %s exists' ), $post_type['args']->label ) .'</li>';

	$pt_names = '';
	if ( is_array($query->posts) ) {
		foreach ( $query->posts as $post ) {
			if ( $post->post_title ) {
				$pt_names .= htmlentities( $post->post_title ) .'|';
			} else {
				$pt_names = sprintf( __('No %s exists'), $post_type['args']->label );
			}
		}
	}

	$id = $post_type['args']->name;
	?>
	<p class="quick-search-wrap">
		<input type="text" class="quick-search regular-text" value="" />
		<a class="quick-search-submit button-secondary"><?php _e('Search'); ?></a>
	</p>

	<p class="button-controls">
		<a class="show-all"><?php _e('View All'); ?></a>
		<a class="hide-all"><?php _e('Hide All'); ?></a>
	</p>

	<div id="existing-<?php echo esc_attr( $id ); ?>" class="list-wrap">
		<div class="list-container">
			<ul class="list">
				<?php echo isset( $error ) ? $error : wp_nav_menu_get_items( $query->posts, 'post_type', $id ); ?>
			</ul>
		</div><!-- /.list-container-->
	</div><!-- /#existing-categories-->
	<p class="add-to-menu">
		<a class="button-secondary"><?php _e('Add to Menu'); ?></a>
	</p>
	<input type="hidden" class="autocomplete" name="autocomplete-<?php echo esc_attr( $id ); ?>-names" value="<?php echo esc_js( $pt_names ); ?>" />
	<br class="clear" />
	<script type="text/javascript" charset="utf-8">
		// <![CDATA[
		jQuery(document).ready(function(){
			wp_nav_menu_autocomplete('<?php echo esc_attr($id); ?>');
		});
		// ]]>
	</script>
	<?php
}

/**
 * Displays a metabox for a taxonomy menu item.
 *
 * @since 3.0.0
 *
 * @param string $object Not used.
 * @param string $taxonomy The taxonomy object.
 */
function wp_nav_menu_item_taxonomy_metabox( $object, $taxonomy ) {
	$args = array(
		'child_of' => 0, 'orderby' => 'name', 'order' => 'ASC',
		'hide_empty' => false, 'include_last_update_time' => false, 'hierarchical' => 1, 'exclude' => '',
		'include' => '', 'number' => '', 'pad_counts' => false
	);

	// @todo transient caching of these results with proper invalidation on updating of a tax of this type
	$terms = get_terms( $taxonomy['args']->name, $args );

	if ( !$terms )
		$error = '<li id="error">'. sprintf( __( 'No %s exists' ), $taxonomy['args']->label ) .'</li>';

	$term_names = '';
	if ( is_array($terms) ) {
		foreach ( $terms as $term ) {
			if ( $term->name ) {
				$term_names .= htmlentities( $term->name ) .'|';
			} else {
				$term_names = sprintf( __('No %s exists'), $taxonomy['args']->label );
			}
		}
	}

	$id = $taxonomy['args']->name;
	?>
	<p class="quick-search-wrap">
		<input type="text" class="quick-search regular-text" value="" />
		<a class="quick-search-submit button-secondary"><?php _e('Search'); ?></a>
	</p>

	<p class="button-controls">
		<a class="show-all"><?php _e('View All'); ?></a>
		<a class="hide-all"><?php _e('Hide All'); ?></a>
	</p>

	<div id="existing-<?php echo esc_attr( $id ); ?>" class="list-wrap">
		<div class="list-container">
			<ul class="list">
				<?php echo isset( $error ) ? $error : wp_nav_menu_get_items( $terms, 'taxonomy', $id ); ?>
			</ul>
		</div><!-- /.list-container-->
	</div><!-- /#existing-categories-->
	<p class="add-to-menu">
		<a class="button-secondary"><?php _e('Add to Menu'); ?></a>
	</p>
	<input type="hidden" class="autocomplete" name="autocomplete-<?php echo esc_attr($id); ?>-names" value="<?php echo esc_js( $term_names ); ?>" />
	<br class="clear" />
	<script type="text/javascript" charset="utf-8">
		// <![CDATA[
		jQuery(document).ready(function(){
			wp_nav_menu_autocomplete('<?php echo esc_attr($id); ?>');
		});
		// ]]>
	</script>
	<?php
}

/**
 * Abstract function for returning all menu items of a menu item type.
 *
 * @since 3.0.0
 *
 * @param string $menu_items Array of objects containing all menu items to be displayed.
 * @param string $object_type Menu item type.
 * @param string $object Optional. Menu item type name.
 * @param string $context Optional. The context for how the menu items should be formatted.
 * @return string $ouput Menu items.
 */
function wp_nav_menu_get_items( $menu_items, $object_type, $object = null, $context = 'frontend' ) {
	if ( !$menu_items )
		return __( 'Not Found' );

	$output = '';
	$i = 1;
	foreach ( $menu_items as $menu_item ) {
		// convert the 'parent' taxonomy property to 'post_parent'
		// so we don't have to duplicate this entire function.
		if ( !isset($menu_item->post_parent) )
			$menu_item->post_parent = $menu_item->parent;

		// Cleanest way to get all attachements
		if ( 'attachment' == $object )
			$menu_item->post_parent = 0;

		if ( 0 == $menu_item->post_parent ) {
			// Set up the menu item
			$menu_item = wp_setup_nav_menu_item( $menu_item, $object_type, $object );
			$attributes = ( 'backend' == $context ) ? ' id="menu-item-'. $i .'" value="'. $i .'"' : '';

			$output .= '<li'. $attributes .'>';
			$output .= wp_get_nav_menu_item( $menu_item, $object_type, $object );
			$output .= wp_get_nav_menu_sub_items( $menu_item->ID, $object_type, $object, $context );
			$output .= '</li>';

			++$i;
		}
	}

	return $output;
}

/**
 * Recursive function to retrieve sub menu items.
 *
 * @since 3.0.0
 *
 * @param string $childof The Parent ID.
 * @param string $object_type The object type.
 * @param string $object The object name.
 * @return string $output sub menu items.
 */
function wp_get_nav_menu_sub_items( $childof, $object_type, $object = null, $context = 'frontend' ) {
	$args = array( 'child_of' => $childof, 'parent' => $childof, 'hide_empty' => false, );

	switch ( $object_type ) {
		case 'post_type':
			$hierarchical_post_types = get_post_types( array( 'hierarchical' => true ) );
			if ( in_array( $object, $hierarchical_post_types ) ) {
				$args['post_type'] = $object;
				$sub_menu_items = get_pages( $args );
			} else {
				$sub_menu_items = array();
			}
			break;

		case 'taxonomy':
			if ( is_taxonomy_hierarchical( $object ) ) {
				$sub_menu_items = get_terms( $object, $args );
			} else {
				$sub_menu_items = array();
			}
			break;

		default:
			$sub_menu_items = array();
			break;
	}

	$output = '';
	$i = 1;
	if ( !empty($sub_menu_items) && !is_wp_error($sub_menu_items) ) {
		$output .= '<ul class="sub-menu menu-item-type-'. $object_type .'">';
		foreach ( $sub_menu_items as $menu_item ) {
			// Set up the menu item
			$menu_item = wp_setup_nav_menu_item( $menu_item, $object_type, $object );
			$attributes = ( 'backend' == $context ) ? ' id="menu-item-'. $i .'" value="'. $i .'"' : '';

			$output .= '<li'. $attributes .'>';
			$output .= wp_get_nav_menu_item( $menu_item, $object_type, $object );
			$output .= wp_get_nav_menu_sub_items( $menu_item->ID, $object_type, $object );
			$output .= '</li>';

			++$i;
		}
		$output .= '</ul>';
	}
	return $output;
}
?>