<?php

namespace modules\products\classes\models;

use core\classes\Model;

class Product extends Model {

	protected $table       = 'product';
	protected $primary_key = 'product_id';
	protected $columns     = [
		'product_id' => [
			'data_type'      => 'int',
			'auto_increment' => TRUE,
			'null_allowed'   => FALSE,
		],
		'site_id' => [
			'data_type'      => 'int',
			'null_allowed'   => FALSE,
		],
		'product_active' => [
			'data_type'      => 'bool',
			'null_allowed'   => FALSE,
			'default_value'  => 'TRUE',
		],
		'product_featured' => [
			'data_type'      => 'bool',
			'null_allowed'   => FALSE,
			'default_value'  => 'FALSE',
		],
		'product_sku' => [
			'data_type'      => 'text',
			'data_length'    => 64,
			'null_allowed'   => FALSE,
		],
		'product_model' => [
			'data_type'      => 'text',
			'data_length'    => 64,
			'null_allowed'   => FALSE,
		],
		'product_name' => [
			'data_type'      => 'text',
			'data_length'    => 128,
			'null_allowed'   => FALSE,
		],
		'product_brand_id' => [
			'data_type'      => 'int',
			'null_allowed'   => TRUE,
		],
		'product_description' => [
			'data_type'      => 'text',
			'data_length'    => 65535,
			'null_allowed'   => FALSE,
		],
		'product_cost' => [
			'data_type'      => 'numeric',
			'data_length'    => [6, 4],
			'null_allowed'   => TRUE,
		],
		'product_sell' => [
			'data_type'      => 'numeric',
			'data_length'    => [6, 4],
			'null_allowed'   => FALSE,
		],
	];

	protected $indexes = [
		'site_id',
		'product_active',
		'product_featured',
		'product_sku',
		'product_model',
	];

	protected $uniques = [
		'product_sku',
	];

	protected $foreign_keys = [
		'product_brand_id'  => ['product_brand', 'product_brand_id'],
	];

	public function setCategory(ProductCategory $category = NULL) {
		$this->objects['category'] = $category;
	}

	public function getCategoryName() {
		$category = $this->getCategory();
		return $category ? $category->name : NULL;
	}

	public function getCategory() {
		// object is not in the database
		if (!$this->id) {
			return NULL;
		}

		if (!isset($this->objects['category'])) {
			$sql = "
				SELECT product_category.*
				FROM
					product_category_link
					JOIN product_category USING (product_category_id)
				WHERE
					product_id=".$this->database->quote($this->id)."
			";
			$record = $this->database->querySingle($sql);
			if ($record) {
				 $this->objects['category'] = $this->getModel('\\modules\\products\\classes\\models\\ProductCategory', $record);
			}
			else {
				$this->objects['category'] =  NULL;
			}
		}
		return $this->objects['category'];
	}
}
