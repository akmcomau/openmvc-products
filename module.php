<?php
$_MODULE = [
	"name" => "Products",
	"description" => "Support for products",
	"namespace" => "\\modules\\products",
	"config_controller" => "administrator\\Products",
	"controllers" => [
		"Products",
		"administrator\\Products",
		"administrator\\ProductCategoryManager"
	],
	"default_config" => [
	]
];
