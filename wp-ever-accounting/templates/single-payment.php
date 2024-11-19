<?php
/**
 * The Template for displaying payment.
 *
 * This template can be overridden by copying it to yourtheme/eac/single-payment.php
 *
 * HOWEVER, on occasion EverAccounting will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://wpeveraccounting.com/docs/
 * @package EverAccounting\Templates
 * @version 1.0.0
 *
 * @var \EverAccounting\Models\Payment $payment Payment object.
 */

defined( 'ABSPATH' ) || exit;

do_action( 'eac_page_header' );

eac_get_template( 'content-payment.php', array( 'payment' => $payment ) );

do_action( 'eac_page_footer' );
