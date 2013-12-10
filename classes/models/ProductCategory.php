<?php

namespace modules\products\classes\models;

use core\classes\models\Category;
use core\classes\traits\Thumbnails;

class ProductCategory extends Category {
	use Thumbnails;

	protected $link_type = 'link-table';

	protected $table       = 'product_category';
	protected $primary_key = 'product_category_id';
	protected $columns     = [
		'product_category_id' => [
			'data_type'      => 'int',
			'auto_increment' => TRUE,
			'null_allowed'   => FALSE,
		],
		'site_id' => [
			'data_type'      => 'int',
			'null_allowed'   => FALSE,
		],
		'product_category_name' => [
			'data_type'      => 'text',
			'data_length'    => '128',
			'null_allowed'   => FALSE,
		],
		'product_category_parent_id' => [
			'data_type'      => 'int',
			'null_allowed'   => TRUE,
		],
		'product_category_image' => [
			'data_type'      => 'text',
			'data_length'    => '256',
			'null_allowed'   => TRUE,
		],
	];

	protected $indexes = [
		'site_id',
		'product_category_parent_id',
	];

	protected $foreign_keys = [
		'product_category_parent_id' => ['product_category', 'product_category_id'],
	];

	public function hasImage() {
		return TRUE;
	}

	public function getImageUrl() {
		if (is_null($this->image)) {
			return NULL;
		}

		$site = $this->config->siteConfig();
		$language = $site->language;
		$theme = $site->theme;
		$path = '/sites/'.$site->namespace.'/themes/product_category_images/';
		return $path.$this->image;
	}

	public function getImageThumbnailUrl() {
		if (is_null($this->image)) {
			return NULL;
		}

		$site = $this->config->siteConfig();
		$language = $site->language;
		$theme = $site->theme;
		$path = '/sites/'.$site->namespace.'/themes/product_category_images/';
		return $path.'tn-'.$this->image;
	}

	public function uploadImage($tmp_name, $filename) {
		$site = $this->config->siteConfig();
		$language = $site->language;
		$theme = $site->theme;
		$root = __DIR__.DS.'..'.DS.'..'.DS.'..'.DS.'..'.DS;
		$path = $root.'sites'.DS.$site->namespace.DS.'themes'.DS.'product_category_images'.DS;
		if (!file_exists($path)) {
			mkdir($path);
		}

		if ($this->image && file_exists($path.$this->image)) {
			unlink($path.$this->image);
		}
		if ($this->image && file_exists($path.'tn-'.$this->image)) {
			unlink($path.'tn-'.$this->image);
		}

		// copy the file
		copy($tmp_name, $path.$filename);
		$this->makeThumbnails($path, $filename);

		$this->image = $filename;
		$this->update();
	}
}
