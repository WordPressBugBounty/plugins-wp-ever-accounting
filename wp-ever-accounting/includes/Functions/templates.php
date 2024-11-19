<?php

defined( 'ABSPATH' ) || exit();

/**
 * Get template part.
 *
 * @param mixed  $slug Template slug.
 * @param string $name Template name (default: '').
 *
 * @return void
 */
function eac_get_template_part( $slug, $name = null ) {
	$templates = array();
	if ( $name ) {
		$templates[] = "{$slug}-{$name}.php";
	}

	$templates[] = "{$slug}.php";

	/**
	 * Filters the  templates for a given $slug and/or $name combination.
	 *
	 * @param string $templates The list of possible template parts.
	 * @param string $slug The slug of the template part.
	 * @param string $name The name of the template part.
	 */
	$templates = apply_filters( 'eac_get_template_part', $templates, $slug, $name );

	foreach ( $templates as $template ) {
		$located = eac_locate_template( $template );

		if ( ! empty( $located ) ) {
			load_template( $located, false );
			break;
		}
	}
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 * yourtheme/eac/$template_path/$template_name
 * yourtheme/eac/$template_name
 * $default_path/$template_name
 *
 * @param string $template_name Template name.
 * @param string $template_path Template path. (default: '').
 * @param string $default_path Default path. (default: '').
 *
 * @return string
 */
function eac_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	if ( ! $template_path ) {
		$template_path = EAC()->get_template_path();
	}

	if ( ! $default_path ) {
		$default_path = EAC()->get_template_path();
	}

	// Look within passed path within the theme - this is priority.
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			'eac/' . $template_name,
		)
	);

	// Get default template/.
	if ( ! $template ) {
		$template = $default_path . $template_name;
	}

	// Return what we found.
	return apply_filters( 'eac_locate_template', $template, $template_name, $template_path );
}

/**
 * Get other templates passing attributes and including the file.
 *
 * @param string $template_name Template name.
 * @param array  $args Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path Default path. (default: '').
 */
function eac_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	$template = eac_locate_template( $template_name, $template_path, $default_path );

	// Allow 3rd party plugin filter template file from their plugin.
	$filter_template = apply_filters( 'eac_get_template', $template, $template_name, $args, $template_path, $default_path );

	if ( $filter_template !== $template ) {
		if ( ! file_exists( $filter_template ) ) {
			$filter_template = $template;
		}
	}

	$action_args = array(
		'template_name' => $template_name,
		'template_path' => $template_path,
		'located'       => $template,
		'args'          => $args,
	);

	if ( ! empty( $args ) && is_array( $args ) ) {
		extract( $args ); // @codingStandardsIgnoreLine
	}

	do_action( 'ever_accounting_before_template_part', $action_args['template_name'], $action_args['template_path'], $action_args['located'], $action_args['args'] );

	include $action_args['located'];

	do_action( 'ever_accounting_after_template_part', $action_args['template_name'], $action_args['template_path'], $action_args['located'], $action_args['args'] );
}

/**
 * Like eac_get_template, but returns the HTML instead of outputting.
 *
 * @param string $template_name Template name.
 * @param array  $args Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path Default path. (default: '').
 *
 * @return string
 * @since 1.0.2
 * @see   eaccounting_get_template
 */
function eac_get_template_html( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	ob_start();
	eac_get_template( $template_name, $args, $template_path, $default_path );

	return ob_get_clean();
}

/**
 * Get the header template.
 *
 * @since 1.0.0
 * @see   eac_get_template
 *
 * @return void
 */
function eac_header() {
	eac_get_template_part( 'header' );
}

/**
 * Get the footer template.
 *
 * @since 1.0.0
 * @see   eac_get_template
 *
 * @return void
 */
function eac_footer() {
	eac_get_template_part( 'footer' );
}
