<?php

namespace modules\products\classes\models;

use core\classes\Model;

class ProductImage extends Model {

	protected $table       = 'product_image';
	protected $primary_key = 'product_image_id';
	protected $columns     = [
		'product_image_id' => [
			'data_type'      => 'int',
			'auto_increment' => TRUE,
			'null_allowed'   => FALSE,
		],
		'product_id' => [
			'data_type'      => 'int',
			'null_allowed'   => FALSE,
		],
		'product_image_name' => [
			'data_type'      => 'text',
			'data_length'    => '256',
			'null_allowed'   => FALSE,
		],
		'product_image_filename' => [
			'data_type'      => 'text',
			'data_length'    => '256',
			'null_allowed'   => FALSE,
		],
		'product_width' => [
			'data_type'      => 'int',
			'null_allowed'   => FALSE,
		],
		'product_height' => [
			'data_type'      => 'int',
			'null_allowed'   => FALSE,
		],
		'product_ordering' => [
			'data_type'      => 'int',
			'null_allowed'   => FALSE,
		],
	];

	protected $indexes = [
		'site_id',
		'product_id',
	];

	protected $foreign_keys = [
		'product_id' => ['product', 'product_id'],
	];
}
