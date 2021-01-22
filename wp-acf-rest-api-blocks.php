
<?php
	
/**
* Plugin Name: WP ACF Rest API Blocks
* Version: 1.0.0
* Plugin URI: https://github.com/wp-kit/wp-acf-rest-api-blocks
* Description: The plugin sends Gutneberg Blocks into REST API responses under 'gblocks' property and detects if ACF is installed, if so it runs the acf_setup_meta + get_fields hack to output acf fields within the response for acf blocks
* Author: WPKit
*/

function wp_acf_rest_api_blocks_init() {
	
	if ( ! function_exists( 'use_block_editor_for_post_type' ) ) {
		require ABSPATH . 'wp-admin/includes/post.php';
	}
	
	$post_types = get_post_types_by_support( [ 'editor' ] );
	foreach ( $post_types as $post_type ) {
		if ( use_block_editor_for_post_type( $post_type ) ) {
			register_rest_field(
				$post_type,
				'gblocks',
				['get_callback' => function ( array $post ) {
					return apply_filters( 'rest_response_parse_blocks', json_decode( json_encode( parse_blocks( $post['content']['raw'] ) ) ), $post, $post_type );
				}]
			);
		}
	}
	
}

add_action('rest_api_init', 'wp_acf_rest_api_blocks_init');

if( function_exists('acf_register_block_type') ) {
	
	function wp_acf_rest_api_blocks_filter_response( $blocks ) {

		foreach($blocks as &$block) {
			
			if(strpos($block->blockName, 'acf/') === 0) {
			
				acf_setup_meta( json_decode(json_encode($block->attrs->data), true), $block->attrs->id, true );
				
				$id = $block->attrs->id;
				
				unset($block->attrs->name);
				unset($block->attrs->id);
				unset($block->attrs->data);
				
				$block->attrs = (object) array_merge((array) $block->attrs, get_fields($id));
			}
			
			if(!empty($block->innerBlocks) && is_array($block->innerBlocks)) {
				$block->innerBlocks = wp_rest_api_blocks_transform($block->innerBlocks);
			}
		}
		
		return array_values(array_filter($blocks, fn($block) => $block->blockName));
		
	}
	
	add_filter('rest_response_parse_blocks', 'wp_acf_rest_api_blocks_filter_response');

}
