<?php

namespace modules\products\classes\models;

use core\classes\URL;
use core\classes\Model;
use core\classes\Module;
use core\classes\Request;
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

	protected $partial_uniques = [
		["product_sku != ''", 'product_sku'],
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

	public function insert() {
		// insert the product
		parent::insert();
		$this->updateCategory();
		$this->updateAttributes();
	}

	public function update() {
		// update the product
		parent::update();
		$this->updateCategory();
		$this->updateAttributes();
	}

	public function getSellPrice() {
		$value = 0;
		if ($this->config->moduleConfig('\modules\checkout')->show_prices_inc_tax) {
			$value = $this->getSellIncTax();
		}
		else {
			$value = $this->sell;
		}

		return $this->callPriceHook('getSellPrice', $value);
	}

	public function getSellTotal() {
		$value = 0;
		if ($this->config->moduleConfig('\modules\checkout')->show_prices_inc_tax) {
			$value = $this->getTotalIncTax();
		}
		else {
			$value = $this->getTotal();
		}

		return $this->callPriceHook('getSellTotal', $value);
	}

	protected function callPriceHook($name, $price) {
		$modules = (new Module($this->config))->getEnabledModules();
		foreach ($modules as $module) {
			if (isset($module['hooks']['checkout'][$name])) {
				$class = $module['namespace'].'\\'.$module['hooks']['checkout'][$name];
				$this->logger->debug("Calling Hook: $class::$name");
				$class = new $class($this->config, $this->database, NULL);
				$price = call_user_func_array(array($class, $name), [$price]);
			}
		}
		return $price;
	}

	public function getSellIncTax() {
		$checkout_config = $this->config->moduleConfig('\modules\checkout');
		$tax_type = $checkout_config->tax_type;
		$tax_class = NULL;
		if ($tax_type) {
			$tax_class = $this->config->siteConfig()->checkout->tax_types->$tax_type->class;
			$tax_class = new $tax_class($this->config, $this->database);
			return $tax_class->calculateTax($this->sell) + $this->sell;
		}
		else {
			return $this->sell;
		}
	}

	public function getTotalIncTax() {
		$checkout_config = $this->config->moduleConfig('\modules\checkout');
		$tax_type = $checkout_config->tax_type;
		$tax_class = NULL;
		if ($tax_type) {
			$tax_class = $this->config->siteConfig()->checkout->tax_types->$tax_type->class;
			$tax_class = new $tax_class($this->config, $this->database);
			return $tax_class->calculateTax($this->getTotal()) + $this->getTotal();
		}
		else {
			return $this->getTotal();
		}
	}

	public function setAttributeValue(ProductAttribute $attribute, $value) {
		$this->getAttributes();
		if (isset($this->objects['attributes'][$attribute->id])) {
			$this->objects['attributes'][$attribute->id]->product_attribute_option_id   = $attribute->getValueOption($value);
			$this->objects['attributes'][$attribute->id]->product_attribute_category_id = $attribute->getValueCategory($value);
			$this->objects['attributes'][$attribute->id]->product_attribute_value_text  = $attribute->getValueText($value);
		}
		else {
			$attribute_value = $this->getModel('\modules\products\classes\models\ProductAttributeValue');
			$attribute_value->product_id                    = $this->id;
			$attribute_value->product_attribute_id          = $attribute->id;
			$attribute_value->product_attribute_option_id   = $attribute->getValueOption($value);
			$attribute_value->product_attribute_category_id = $attribute->getValueCategory($value);
			$attribute_value->product_attribute_value_text  = $attribute->getValueText($value);
			$this->objects['attributes'][$attribute->id] = $attribute_value;
		}
	}

	public function removeAttributeValue(ProductAttribute $attribute) {
		$this->getAttributes();
		if (isset($this->objects['attributes'][$attribute->id])) {
			unset($this->objects['attributes'][$attribute->id]);
		}
	}

	public function getAttributeValue($attribute) {
		$attributes = $this->getAttributes();

		if (is_a($attribute, '\modules\products\classes\models\ProductAttribute')) {
			if (isset($attributes[$attribute->id])) {
				return $attributes[$attribute->id]->getValue();
			}
		}
		elseif (is_int($attribute)) {
			if (isset($attributes[$attribute])) {
				return $attributes[$attribute]->getValue();
			}
		}
		else {
			foreach ($attributes as $attr) {
				if ($attr->getProductAttribute()->name == $attribute) {
					return $attr->getValue();
				}
			}
		}

		return NULL;
	}

	protected function updateAttributes() {
		if (!isset($this->objects['attributes'])) {
			return;
		}

		foreach ($this->objects['attributes'] as $attribute) {
			if ($attribute->id) {
				$attribute->update();
			}
			else {
				if (!$attribute->product_id) {
					$attribute->product_id = $this->id;
				}
				$attribute->insert();
			}
		}
	}

	protected function updateCategory() {
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

	public function delete() {
		// delete the images
		$this->removed_images = $this->getImages();
		$this->images = [];
		$this->updateImages();

		// delete the categories
		$categories = $this->getModel('\modules\products\classes\models\ProductCategoryLink')->getMulti([
			'product_id' => $this->id
		]);
		foreach ($categories as $category) {
			$category->delete();
		}

		// delete the attributes
		$attributes = $this->getModel('\modules\products\classes\models\ProductAttributeValue')->getMulti([
			'product_id' => $this->id
		]);
		foreach ($attributes as $attribute) {
			$attribute->delete();
		}

		// delete the product
		parent::delete();
	}

	public function getBrand() {
		if (isset($this->objects['brand'])) {
			return $this->objects['brand'];
		}

		$this->objects['brand'] = $this->getModel('\modules\products\classes\models\ProductBrand')->get([
			'id' => $this->brand_id
		]);

		return $this->objects['brand'];
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

	public function getAllAttributes() {
		if (isset($this->objects['all_attributes'])) {
			return $this->objects['all_attributes'];
		}

		$sql = "
			SELECT
				*
			FROM
				product_attribute
			WHERE
				product_attribute.site_id = ".(int)$this->config->siteConfig()->site_id."
			ORDER BY
				product_attribute_ordering
		";
		$records = $this->database->queryMulti($sql);

		$this->objects['all_attributes'] = [];
		foreach ($records as $record) {
			$this->objects['all_attributes'][] = $this->getModel('\modules\products\classes\models\ProductAttribute', $record);
		}

		return $this->objects['all_attributes'];
	}

	public function getAttributes() {
		if (isset($this->objects['attributes'])) {
			return $this->objects['attributes'];
		}

		$sql = "
			SELECT
				*,
				product_attribute.product_attribute_id
			FROM
				product_attribute_value
				JOIN product_attribute USING (product_attribute_id)
				LEFT JOIN product_attribute_option USING (product_attribute_option_id)
			WHERE
				product_id = ".(int)$this->id."
				AND product_attribute.site_id = ".(int)$this->config->siteConfig()->site_id."
			ORDER BY
				product_attribute_ordering
		";
		$records = $this->database->queryMulti($sql);

		$this->objects['attributes'] = [];
		foreach ($records as $record) {
			$attribute = $this->getModel('\modules\products\classes\models\ProductAttributeValue', $record);
			$attribute->setObjectCache('product_attribute', $this->getModel('\modules\products\classes\models\ProductAttribute', $record));
			if ($record['product_attribute_option_id']) {
				$attribute->setObjectCache('product_attribute_value', $this->getModel('\modules\products\classes\models\ProductAttributeValue', $record));
			}
			$this->objects['attributes'][$record['product_attribute_id']] = $attribute;
		}

		return $this->objects['attributes'];
	}

	public function getUrl(URL $url) {
		return $url->getUrl('Products', 'view', [$this->id, $url->canonical($this->name)]);
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

	public function getViewedIds(Request $request) {
		$module_config = $this->config->moduleConfig('\modules\products');
		if (!$module_config->track_viewed_products) return [];
		$type = $module_config->track_viewed_products;

		$viewed = [];
		switch($type) {
			case 'session':
				$viewed = $request->session->get('track_products_viewed');
				break;
		}

		if (!is_array($viewed)) $viewed = [];
		return $viewed;
	}

	public function trackViewed(Request $request) {
		$module_config = $this->config->moduleConfig('\modules\products');
		if (!$module_config->track_viewed_products) return;
		$type = $module_config->track_viewed_products;

		// add to the top of the array
		$viewed = array_merge([$this->id], $this->getViewedIds($request));

		// remove duplicates
		$max_length = 100;
		$duplicates = array_keys($viewed, $this->id);
		array_shift($duplicates);
		$new_viewed = [];
		$counter = 0;
		foreach ($viewed as $index => $id) {
			if ($counter < 100 && !in_array($index, $duplicates)) {
				$new_viewed[] = $id;
			}
		}

		switch($type) {
			case 'session':
				$request->session->set('track_products_viewed', $new_viewed);
				break;
		}
	}
}
