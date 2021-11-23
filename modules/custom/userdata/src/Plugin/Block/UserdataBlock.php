<?php
namespace Drupal\userdata\Plugin\Block;
use Drupal\Core\Block\BlockBase;
/**
* Provides a 'MydataBlock' block.
*
* @Block(
*  id = "userdata_block",
*  admin_label = @Translation("Userdata block"),
* )
*/
class UserdataBlock extends BlockBase {
	/**
	* {@inheritdoc}
	*/
	public function build() {
		////$build = [];
		//$build['mydata_block']['#markup'] = 'Implement MydataBlock.';
		$form = \Drupal::formBuilder()->getForm('Drupal\userdata\Form\UserdataForm');
		return $form;
	}
}