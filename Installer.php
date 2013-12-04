<?php

namespace modules\products;

use ErrorException;
use core\classes\Config;
use core\classes\Database;
use core\classes\Language;
use core\classes\Model;
use core\classes\Menu;

class Installer {
	protected $config;
	protected $database;

	public function __construct(Config $config, Database $database) {
		$this->config = $config;
		$this->database = $database;
	}

	public function install() {
		$model = new Model($this->config, $this->database);

		$table = $model->getModel('\\modules\\products\\classes\\models\\ProductBrand');
		$table->createTable();
		$table->createIndexes();
		$table->createForeignKeys();

		$table = $model->getModel('\\modules\\products\\classes\\models\\Product');
		$table->createTable();
		$table->createIndexes();
		$table->createForeignKeys();

		$table = $model->getModel('\\modules\\products\\classes\\models\\CheckoutProduct');
		$table->createTable();
		$table->createIndexes();
		$table->createForeignKeys();

		$table = $model->getModel('\\modules\\products\\classes\\models\\ProductAttribute');
		$table->createTable();
		$table->createIndexes();
		$table->createForeignKeys();

		$table = $model->getModel('\\modules\\products\\classes\\models\\ProductAttributeOption');
		$table->createTable();
		$table->createIndexes();
		$table->createForeignKeys();

		$table = $model->getModel('\\modules\\products\\classes\\models\\ProductAttributeValue');
		$table->createTable();
		$table->createIndexes();
		$table->createForeignKeys();

		$table = $model->getModel('\\modules\\products\\classes\\models\\ProductImage');
		$table->createTable();
		$table->createIndexes();
		$table->createForeignKeys();

		$table = $model->getModel('\\modules\\products\\classes\\models\\ProductCategory');
		$table->createTable();
		$table->createIndexes();
		$table->createForeignKeys();

		$table = $model->getModel('\\modules\\products\\classes\\models\\ProductCategoryLink');
		$table->createTable();
		$table->createIndexes();
		$table->createForeignKeys();
	}

	public function uninstall() {
		$model = new Model($this->config, $this->database);

		$table = $model->getModel('\\modules\\products\\classes\\models\\ProductCategoryLink');
		$table->dropTable();
		$table = $model->getModel('\\modules\\products\\classes\\models\\ProductCategory');
		$table->dropTable();
		$table = $model->getModel('\\modules\\products\\classes\\models\\ProductImage');
		$table->dropTable();
		$table = $model->getModel('\\modules\\products\\classes\\models\\ProductAttributeValue');
		$table->dropTable();
		$table = $model->getModel('\\modules\\products\\classes\\models\\ProductAttributeOption');
		$table->dropTable();
		$table = $model->getModel('\\modules\\products\\classes\\models\\ProductAttribute');
		$table->dropTable();
		$table = $model->getModel('\\modules\\products\\classes\\models\\CheckoutProduct');
		$table->dropTable();
		$table = $model->getModel('\\modules\\products\\classes\\models\\Product');
		$table->dropTable();
		$table = $model->getModel('\\modules\\products\\classes\\models\\ProductBrand');
		$table->dropTable();
	}

	public function enable() {
		$language = new Language($this->config);
		$language->loadLanguageFile('administrator/products.php', DS.'modules'.DS.'products');

		$layout_strings = $language->getFile('administrator/layout.php');
		$layout_strings['checkout_module_products'] = $language->get('products');
		$language->updateFile('administrator/layout.php', $layout_strings);

		$main_menu = new Menu($this->config, $language);
		$main_menu->loadMenu('menu_admin_main.php');
		$main_menu->insert_menu(['checkout', 'checkout_orders'], 'checkout_products', [
			'controller' => 'administrator/Products',
			'method' => 'index',
			'text_tag' => 'checkout_module_products',
			'children' => [
				'checkout_products_list' => [
					'controller' => 'administrator/Products',
					'method' => 'index',
				],
				'checkout_products_add' => [
					'controller' => 'administrator/Products',
					'method' => 'addProduct',
				],
				'checkout_products_brands' => [
					'controller' => 'administrator/ProductCategoryManager',
					'method' => 'brands',
				],
				'checkout_products_categories' => [
					'controller' => 'administrator/ProductCategoryManager',
					'method' => 'categories',
				],
				'checkout_products_attributes' => [
					'controller' => 'administrator/Products',
					'method' => 'attributes',
				],
			],
		]);
		$main_menu->update();
	}

	public function disable() {
		$language = new Language($this->config);
		$language->loadLanguageFile('administrator/checkout.php', DS.'modules'.DS.'checkout');

		$layout_strings = $language->getFile('administrator/layout.php');
		unset($layout_strings['checkout_module_products']);
		$language->updateFile('administrator/layout.php', $layout_strings);

		// Remove some menu items to the admin menu
		$main_menu = new Menu($this->config, $language);
		$main_menu->loadMenu('menu_admin_main.php');
		$menu = $main_menu->getMenuData();
		unset($menu['checkout']['children']['checkout_products']);
		$main_menu->setMenuData($menu);
		$main_menu->update();
	}
}