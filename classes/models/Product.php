<?php

namespace modules\products\classes\models;

use core\classes\Model;
use modules\checkout\classes\models\ItemInterface;
use modules\checkout\classes\models\Checkout;
use modules\checkout\classes\models\CheckoutItem;

class Product extends Model implements ItemInterface {

	protected $quantity = 0;
	protected $total = 0;

	protected $images = NULL;
	protected $removed_images = [];

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

	protected $relationships = [
		'product_category' => [
			'where_fields'  => ['product_category_id'],
			'join_clause'   => 'LEFT JOIN product_category_link USING (product_id) LEFT JOIN product_category USING (product_category_id)',
		],
	];

	public function update() {
		// update the product
		parent::update();

		// get the link
		$link = $this->getModel('\modules\products\classes\models\ProductCategoryLink')->get([
			'product_id' => $this->id,
		]);

		// update the category
		$this->getCategory();
		$category = $this->objects['category'];
		if ($category && $link) {
			// update the category
			$link->product_category_id = $category->id;
			$link->update();
		}
		elseif ($category && !$link) {
			// insert the category
			$link = $this->getModel('\modules\products\classes\models\ProductCategoryLink');
			$link->product_id = $this->id;
			$link->product_category_id = $category->id;
			$link->insert();
		}
		elseif (!$category && $link) {
			// remove the link
			$link->delete();
		}
	}

	public function getBrand() {
		$this->getModel('\modules\products\classes\models\ProductBrand')->get([
			'id' => $this->brand_id
		]);
	}

	public function getBrandName() {
		$brand = $this->getBrand();
		return $brand ? $brand->name : NULL;
	}

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
				 $this->objects['category'] = $this->getModel('\modules\products\classes\models\ProductCategory', $record);
			}
			else {
				$this->objects['category'] =  NULL;
			}
		}
		return $this->objects['category'];
	}

	public function getImages() {
		if (is_null($this->images)) {
			$image = $this->getModel('\modules\products\classes\models\ProductImage');
			$this->images = $image->getMulti(['product_id' => $this->id], ['ordering' => 'asc']);
		}

		return $this->images;
	}

	public function addImage(ProductImage $image) {
		if (is_null($this->images)) {
			$this->getImages();
		}

		$this->images[] = $image;
	}

	public function removeImage($image_id) {
		if (is_null($this->images)) {
			$this->getImages();
		}

		$new_images = [];
		foreach ($this->images as $index => $image) {
			if ($image_id == $image->id) {
				$this->removed_images[] = $image;
			}
			else {
				$new_images[] = $image;
			}
		}

		$this->images = $new_images;
	}

	public function getNumImages() {
		return count($this->getImages());
	}

	public function updateImages() {
		// remove the images
		foreach ($this->removed_images as $image) {
			$image->delete();
		}

		// insert uploaded images
		foreach ($this->images as $image) {
			if ($image->getTmpName()) {
				$image->product_id = $this->id;
				$image->upload();
			}
		}
	}

	public function purchase(Checkout $checkout, CheckoutItem $checkout_item, ItemInterface $item) {
		$checkout_prod = $this->getModel('\modules\products\classes\models\CheckoutProduct');
		$checkout_prod->checkout_item_id = $checkout_item->id;
		$checkout_prod->product_id = $item->id;
		$checkout_prod->insert();
	}

	public function getUrl($url) {
		return $url->getUrl('Products', 'view', [$this->id, $this->name]);
	}

	public function allowMultiple() {
		return TRUE;
	}


	public function getMaxQuantity() {
		return 1000000;
	}

	public function getName() {
		return $this->name;
	}

	public function getPrice() {
		return $this->sell;
	}

	public function getCostPrice() {
		return $this->cost;
	}

	public function getSKU() {
		return $this->sku;
	}

	public function setQuantity($quantity) {
		$this->quantity = (int)$quantity;
		$this->total    = $this->sell * $this->quantity;
	}

	public function getQuantity() {
		return $this->quantity;
	}

	public function setTotal($total) {
		$this->total = (int)$total;
	}

	public function getTotal() {
		return $this->total;
	}

	public function getType() {
		return 'product';
	}

	public function isShippable() {
		return TRUE;
	}
}
