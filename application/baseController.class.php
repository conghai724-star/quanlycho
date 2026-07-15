<?php
Abstract Class baseController {

/*
 * @registry object
 */
protected $registry;

function __construct($registry = null) {
	$this->registry = $registry;
}

/**
 * Lazy load singleton helpers dynamically when accessed by child classes
 */
public function __get($name) {
	switch ($name) {
		case 'func':
		case 'helper':
			return general::getInstance();
		case 'model':
			return baseModel::getInstance();
		case 'view':
			return baseView::getInstance();
		case 'home':
			return home::getInstance();
		case 'shop':
			return shop::getInstance();
		case 'book':
			return book::getInstance();
		case 'member':
			return member::getInstance();
		case 'pdf':
			return pdf::getInstance();
		case 'mail':
			return baseMailler::getInstance();
	}
	return null;
}

/**
 * @all controllers must contain an index method
 */
abstract function index();
}


?>
