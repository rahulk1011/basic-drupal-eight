<?php

namespace Drupal\userdata\Controller;
use Drupal\Core\Controller\ControllerBase;

/**
* Class UserdataController.
*
* @package Drupal\userdata\Controller
*/
class UserdataController extends ControllerBase {
	/**
	* Display.
	*
	* @return string
	*   Return Hello string.
	*/
	public function display() {
		return [
			'#type' => 'markup',
			'#markup' => $this->t('This page contain all inforamtion about User Data')
		];
	}
}