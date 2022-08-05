<?php

/**
 * @file
 * Contains \Drupal\user_create\Form\UsercreateForm.
*/

namespace Drupal\user_create\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
use Drupal\Core\Routing;

class UsercreateForm extends FormBase {
	/**
	* {@inheritdoc}
	*/
	public function getFormId() {
		return 'user_create_form';
	}

	/**
	* {@inheritdoc}
	*/
	public function buildForm(array $form, FormStateInterface $form_state) {
		$uid = \Drupal::currentUser()->id();
		$user = \Drupal\user\Entity\User::load($uid);

		if($user->roles->target_id == 'administrator'){
			$form['user_email'] = array(
				'#type' => 'textfield',
				'#title' => t('Email-ID'),
				'#required' => TRUE,
				'#maxlength' => 50,
				'#default_value' => '',
			);
			$form['user_name'] = array(
				'#type' => 'textfield',
				'#title' => t('Username'),
				'#required' => TRUE,
				'#maxlength' => 50,
				'#default_value' => '',
			);
			$form['password'] = array(
				'#type' => 'password',
				'#title' => t('Password'),
				'#required' => TRUE,
				'#maxlength' => 50,
				'#default_value' => '',
			);
			$form['c_password'] = array(
				'#type' => 'password',
				'#title' => t('Confirm Password'),
				'#required' => TRUE,
				'#maxlength' => 50,
				'#default_value' => '',
			);
			$form['user_role'] = [
				'#type' => 'select',
				'#title' => t('Role'),
				'#required' => TRUE,
				'#options' => [
					'' => t('-- Select --'),
					'general_user' => t('General User'),
					'moderator' => t('Moderator'),
					'manager' => t('Manager'),
				],
			];

			$form['actions']['#type'] = 'actions';
			$form['actions']['submit'] = array(
				'#type' => 'submit',
				'#value' => $this->t('Create User'),
				'#button_type' => 'primary',
			);
			return $form;
		}
		else{
			$form['form_info'] = array(
			'#markup' => '<strong>You are not authorized to view this page</strong>',
			);
			return $form;
		}
	}

	/**
	* {@inheritdoc}
	*/
	public function validateForm(array &$form, FormStateInterface $form_state) {
		if ($form_state->getValue('user_email') == '') {
			$form_state->setErrorByName('user_email', $this->t('Please Enter Email-ID'));
		}
		if ($form_state->getValue('user_name') == '') {
			$form_state->setErrorByName('user_name', $this->t('Please Enter Username'));
		}
		if ($form_state->getValue('password') == '') {
			$form_state->setErrorByName('password', $this->t('Please Enter Password'));
		}
		if ($form_state->getValue('c_password') == '') {
			$form_state->setErrorByName('c_password', $this->t('Please Enter Confirm Password'));
		}
		if ($form_state->getValue('c_password') != $form_state->getValue('password')) {
			$form_state->setErrorByName('c_password', $this->t('Password & Confirm Password does not match'));
		}
		if ($form_state->getValue('user_role') == '') {
			$form_state->setErrorByName('user_role', $this->t('Please Select User Role'));
		}
	}

	/**
	* {@inheritdoc}
	*/
	public function submitForm(array &$form, FormStateInterface $form_state) {
		try{
			$field = $form_state->getValues();
			
			\Drupal\user\Entity\User::create([
				'name' => $field['user_name'],
				'pass' => $field['password'],
				'mail' => $field['user_email'],
				'roles' => $field['user_role'],
				'status' => 0,
			])->save();
			drupal_set_message('New User Added Succesfully...');
		}
		catch(Exception $ex){
			drupal_set_message(t($ex->getMessage()), 'error');
		}
	}
}