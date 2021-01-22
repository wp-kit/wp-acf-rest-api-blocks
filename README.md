# WP ACF Rest API Blocks

This plugin sends Gutneberg Blocks into REST API responses under 'gblocks' property and detects if ACF is installed, if so it runs the acf_setup_meta + get_fields hack to output acf fields within the response for acf blocks.

Though this functionality is now default behavour when using wp-kit/rest-kit along with wp-kit/acf-integration, we were aware that this functionality is desirable outside of using wp-kit/rest-kit.
