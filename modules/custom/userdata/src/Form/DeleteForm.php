<?php
namespace Drupal\userdata\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;
use Drupal\Core\Render\Element;

/**
* Class DeleteForm.
*
* @package Drupal\userdata\Form
*/

class DeleteForm extends ConfirmFormBase {
	public function getFormId() {
		return 'delete_form';
	}
	public $cid;

	public function getQuestion() {
		return t('Do you want to delete %cid?', array('%cid' => $this->cid));
	}

	public function getCancelUrl() {
		return new Url('userdata.display_table_controller_display');
	}

	public function getDescription() {
		return t('Only do this if you are sure!');
	}

	public function getConfirmText() {
		return t('Delete it!');
	}

	public function getCancelText() {
		return t('Cancel');
	}

	public function buildForm(array $form, FormStateInterface $form_state, $cid = NULL) {

		$this->id = $cid;
		return parent::buildForm($form, $form_state);
	}

	public function validateForm(array &$form, FormStateInterface $form_state) {
		parent::validateForm($form, $form_state);
	}

	public function submitForm(array &$form, FormStateInterface $form_state) {
		$query = \Drupal::database();
		$query->delete('userdata')
			->condition('id', $this->id)
			->execute();
		drupal_set_message("User Data Delete Succesful..");
		$form_state->setRedirect('userdata.display_table_controller_display');
	}
}