<?php
namespace Drupal\userdata\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
* Class UserdataForm.
*
* @package Drupal\userdata\Form
*/
class UserdataForm extends FormBase {
	/**
	* {@inheritdoc}
	*/
	public function getFormId() {
		return 'userdata_form';
	}
	
	/**
	* {@inheritdoc}
	*/
	public function buildForm(array $form, FormStateInterface $form_state) {	
		$conn = Database::getConnection();
		$record = array();
		if (isset($_GET['num'])) {
			$query = $conn->select('userdata', 'm')
			->condition('id', $_GET['num'])
			->fields('m');
			$record = $query->execute()->fetchAssoc();
		}
		$form['candidate_name'] = array(
			'#type' => 'textfield',
			'#title' => t('Candidate Name:'),
			'#required' => TRUE,
			'#default_value' => (isset($record['name']) && $_GET['num']) ? $record['name'] : '',
		);
		$form['mobile_number'] = array(
			'#type' => 'textfield',
			'#title' => t('Mobile Number:'),
			'#default_value' => (isset($record['mobilenumber']) && $_GET['num']) ? $record['mobilenumber'] : '',
		);
		$form['candidate_mail'] = array(
			'#type' => 'email',
			'#title' => t('Email ID:'),
			'#required' => TRUE,
			'#default_value' => (isset($record['email']) && $_GET['num']) ? $record['email'] : '',
		);
		$form['candidate_age'] = array (
			'#type' => 'textfield',
			'#title' => t('Age'),
			'#required' => TRUE,
			'#default_value' => (isset($record['age']) && $_GET['num']) ? $record['age'] : '',
		);
		$form['candidate_gender'] = array (
			'#type' => 'select',
			'#title' => ('Gender'),
			'#options' => array(
				'Female' => t('Female'),
				'male' => t('Male'),
				'#default_value' => (isset($record['gender']) && $_GET['num']) ? $record['gender'] : '',
			),
		);
		$form['web_site'] = array (
			'#type' => 'textfield',
			'#title' => t('Website'),
			'#default_value' => (isset($record['website']) && $_GET['num']) ? $record['website'] : '',
		);
		$form['submit'] = [
			'#type' => 'submit',
			'#value' => 'Save',
		];
		return $form;
	}
	/**
	* {@inheritdoc}
	*/
	public function validateForm(array &$form, FormStateInterface $form_state) {
		$name = $form_state->getValue('candidate_name');
		if(preg_match('/[^A-Za-z]/', $name)) {
			$form_state->setErrorByName('candidate_name', $this->t('your name must in characters without space'));
		}
		if (!intval($form_state->getValue('candidate_age'))) {
			$form_state->setErrorByName('candidate_age', $this->t('Age needs to be a number'));
		}
		if (strlen($form_state->getValue('mobile_number')) < 10 ) {
			$form_state->setErrorByName('mobile_number', $this->t('your mobile number must in 10 digits'));
		}
		parent::validateForm($form, $form_state);
	}
	/**
	* {@inheritdoc}
	*/
	public function submitForm(array &$form, FormStateInterface $form_state) {
		$field = $form_state->getValues();
		$name = $field['candidate_name'];
		$number = $field['mobile_number'];
		$email = $field['candidate_mail'];
		$age = $field['candidate_age'];
		$gender = $field['candidate_gender'];
		$website = $field['web_site'];
		if (isset($_GET['num'])) {
			$field  = array(
				'name' => $name,
				'mobilenumber' => $number,
				'email' => $email,
				'age' => $age,
				'gender' => $gender,
				'website' => $website,
			);
			$query = \Drupal::database();
			$query->update('userdata')
				->fields($field)
				->condition('id', $_GET['num'])
				->execute();
			drupal_set_message("User Data Update Succesful..");
			$form_state->setRedirect('userdata.display_table_controller_display');
		}
		else {
			$field  = array(
				'name' => $name,
				'mobilenumber' => $number,
				'email' => $email,
				'age' => $age,
				'gender' => $gender,
				'website' => $website,
			);
			$query = \Drupal::database();
			$query ->insert('userdata')
				->fields($field)
				->execute();
			drupal_set_message("User Data Save Succesful..");
			$response = new RedirectResponse("/userdata/hello/table");
			$response->send();
		}
	}
}	