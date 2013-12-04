<?php

namespace modules\products\classes\models;

use core\classes\Model;

class CheckoutProduct extends Model {

	protected $table       = 'checkout_product';
	protected $primary_key = 'checkout_product_id';
	protected $columns     = [
		'checkout_product_id' => [
			'data_type'      => 'int',
			'auto_increment' => TRUE,
			'null_allowed'   => FALSE,
		],
		'product_id' => [
			'data_type'      => 'int',
			'null_allowed'   => FALSE,
		],
		'checkout_product_quantity' => [
			'data_type'      => 'int',
			'null_allowed'   => FALSE,
		],
		'checkout_product_cost' => [
			'data_type'      => 'numeric',
			'data_length'    => [6, 4],
			'null_allowed'   => FALSE,
		],
		'checkout_product_sell' => [
			'data_type'      => 'numeric',
			'data_length'    => [6, 4],
			'null_allowed'   => FALSE,
		],
		'checkout_product_tax' => [
			'data_type'      => 'numeric',
			'data_length'    => [6, 4],
			'null_allowed'   => FALSE,
		],
	];

	protected $indexes = [
		'product_id',
	];

	protected $foreign_keys = [
		'product_id' => ['product', 'product_id'],
	];
}
