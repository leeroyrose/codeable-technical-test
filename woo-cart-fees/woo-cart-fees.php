<?php
/**
 * Plugin Name:     WooCommerce Cart Fees
 * Plugin URI:      https://www.leeroyrose.co.uk
 * Description:     Updates the total cart price by a set percentage if certain products are in the cart.
 * Author:          Leeroy Rose
 * Author URI:      https://www.leeroyrose.co.uk
 * Text Domain:     woo-cart-fees
 * Domain Path:     /languages
 * Version:         0.1.1
 *
 * @package         WooCommerceCartFees
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require 'vendor/autoload.php';

use CartFees\Setting;

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	if ( ! defined('WCI_TEXT_DOMAIN') ) {
		define('WCI_TEXT_DOMAIN', 'woo-cart-fees');
	}
	
	if ( ! defined('WCI_PERCENTAGE_DEFAULT') ) {
		define('WCI_PERCENTAGE_DEFAULT', 10);
	}

	//Create settings
	$setting = new Setting('products', array(
		'id' => 'cart-increase',
		'name' => 'Product Cart Fees',
	));
		
	$setting->addTitleAndDescription('Cart Percentage Increaser', 'The following options allow you to choose products that will increase the total value of the shopping cart by a specified percentage.')
			->addField(array(
				'id'       => 'percentage',
				'type'     => 'number',
				'default'  => WCI_PERCENTAGE_DEFAULT,
				'custom_attributes' => array('min' => 0, 'max' => 100),
				'name'     => Setting::wrapTextDomain('Percentage Value'),
				'desc'     => Setting::wrapTextDomain('The percentage amoutn to increase the total cart value by when applicable.'),	
				'desc_tip' => Setting::wrapTextDomain('Defaults to 10%'),
			))
			->addField(array(
				'type'     => 'multiselect',
				'id'       => 'products',
				'options'  => Setting::OPTIONS_ALL_PRODUCTS,
				'name'     => Setting::wrapTextDomain('Products'),
				'desc'     => Setting::wrapTextDomain('The Products that will trigger the price increase.'),	
				'desc_tip' => Setting::wrapTextDomain('Hold cmd (mac) or control (windows) to select multiple values.'),
	
			))
			->addField(array(
				'type'     => 'checkbox',
				'id'       => 'increment_per_product',
				'name'     => Setting::wrapTextDomain('Inrement discount per product?'),
				'desc'     => Setting::wrapTextDomain('When ticked, 2 products selected with a discount rate of 10% will add a 20% (10% * 2) fee to the cart. If unselected, the fee will be 10% regardless of the amount of products.'),
			))
			->create();
	
	//Adjust cart price.
	add_action('woocommerce_cart_calculate_fees', function($cart) use($setting) { 
		//Get Products
		$products = $setting->get('products');
	
		if ( ! $products ) {
			return;
		}
	
		//Get fee percentage
		$feePercentage = $setting->get('percentage');
	
		//Get increment option
		$incrementPerProduct = $setting->get('increment_per_product');
	
		//Check that we have applicable products in the cart.
		$applicableProducts = array_filter($cart->cart_contents, function($product) use($products) {
			return in_array($product['product_id'], $products);
		});
	
		if ( ! $applicableProducts ) {
			return;
		}
	
		//Increase fee percentage if increment setting is applied.
		if ( $incrementPerProduct == 'yes' ) {
			$feePercentage = count($applicableProducts) * $feePercentage;
		}
	
		//get_cart_contents_total honours existing discounts.
		$total = $cart->get_cart_contents_total();
	
		// Add the fee
		$cart->add_fee(Setting::wrapTextDomain("{$feePercentage}% Fee"), $total * ($feePercentage / 100));
	});
} 