<?php
/**
 * Main Header
 *
 * This template can be overridden by copying it to yourtheme/eac/header.php.
 *
 * @version 1.1.0
 * @package EverAccounting
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<link rel="profile" href="https://gmpg.org/xfn/11"/>
	<meta name="robots" content="noindex,nofollow">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo esc_html( apply_filters( 'eac_page_title', esc_html__( 'Ever Accounting', 'wp-ever-accounting' ) ) ); ?></title>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<?php wp_head(); ?>
</head>

<body class="wp-core-ui eac">
<div class="eac-page-content">
