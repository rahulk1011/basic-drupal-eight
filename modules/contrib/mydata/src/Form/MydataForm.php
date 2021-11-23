<?php
	
namespace Drupal\mydata\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
	* Class MydataForm.
	*
	* @package Drupal\mydata\Form
*/
class MydataForm extends FormBase
{
	/**
		* {@inheritdoc}
	*/
	public function getFormId()
	{
		return 'mydata_form';
	}
	
	/**
		* {@inheritdoc}
	*/
	public function buildForm(array $form, FormStateInterface $form_state)
	{
		$conn = Database::getConnection();
		$record = array();
		if (isset($_GET['num'])) {
			$query = $conn->select('mydata', 'm')
			->condition('id', $_GET['num'])
			->fields('m');
			$record = $query->execute()->fetchAssoc();
		}
		$form['new'] = array(
			'#type' => 'markup',
			'#markup' => '<h2>' . t('Enter New Candidate Data:') . '</h2>',
		);
		$form['candidate_name'] = array(
			'#type' => 'textfield',
			'#title' => t('Candidate Name:'),
			'#required' => TRUE,
			'#default_value' => (isset($record['name']) && $_GET['num']) ? $record['name'] : '',
		);
		$form['mobile_number'] = array(
			'#type' => 'textfield',
			'#title' => t('Mobile Number:'),
			'#maxlength' => 10,
			'#required' => TRUE,
			'#default_value' => (isset($record['mobilenumber']) && $_GET['num']) ? $record['mobilenumber'] : '',
		);
		$form['candidate_mail'] = array(
			'#type' => 'email',
			'#title' => t('Email ID:'),
			'#required' => TRUE,
			'#default_value' => (isset($record['email']) && $_GET['num']) ? $record['email'] : '',
		);
		$form['candidate_age'] = array(
			'#type' => 'textfield',
			'#title' => t('Age:'),
			'#maxlength' => 2,
			'#required' => TRUE,
			'#default_value' => (isset($record['age']) && $_GET['num']) ? $record['age'] : '',
		);
		$form['address'] = array(
			'#type' => 'textarea',
			'#title' => t('Address:'),
			'#required' => TRUE,
			'#default_value' => (isset($record['address']) && $_GET['num']) ? $record['address'] : '',
		);
		$form['candidate_gender'] = array(
			'#type' => 'radios',
			'#title' => ('Gender:'),
			'#required' => TRUE,
			'#options' => array(
			'Male' => t('Male'),
			'Female' => t('Female'),
			'#default_value' => (isset($record['gender']) && $_GET['num']) ? $record['gender'] : '',
		),
		);
		$form['entrydate'] = array(
			'#type' => 'date',
			'#title' => t('Entry Date:'),
			'#required' => TRUE,
			'#default_value' => (isset($record['entrydate']) && $_GET['num']) ? $record['entrydate'] : '',
		);
		$form['submit'] = [
			'#type' => 'submit',
			'#value' => 'save',
		];
		return $form;
	}
	
	/**
		* {@inheritdoc}
	*/
	public function validateForm(array &$form, FormStateInterface $form_state)
	{
		// $name = $form_state->getValue('candidate_name');
		// if(preg_match('/[^A-Za-z]/', $name))
		// {
		//   $form_state->setErrorByName('candidate_name', $this->t('your name must in characters without space'));
		// }
		// Confirm that age is numeric.
		if (!intval($form_state->getValue('candidate_age'))) {
			$form_state->setErrorByName('candidate_age', $this->t('Age needs to be a number'));
		}
		if (strlen($form_state->getValue('mobile_number')) != 10) {
			$form_state->setErrorByName('mobile_number', $this->t('Your mobile number must in 10 digits'));
		}
		parent::validateForm($form, $form_state);
	}
	
	/**
		* {@inheritdoc}
	*/
	public function submitForm(array &$form, FormStateInterface $form_state)
	{
		$field = $form_state->getValues();
		$name = $field['candidate_name'];
		$number = $field['mobile_number'];
		$email = $field['candidate_mail'];
		$age = $field['candidate_age'];
		$address = $field['address'];
		$gender = $field['candidate_gender'];
		$entrydate = $field['entrydate'];
		
		if (isset($_GET['num'])) {
			$field  = array(
			'name' => $name,
			'mobilenumber' => $number,
			'email' =>  $email,
			'age' => $age,
			'address' => $address,
			'gender' => $gender,
			'entrydate' => $entrydate,
			);
			$query = \Drupal::database();
			$query->update('mydata')->fields($field)->condition('id', $_GET['num'])->execute();
			drupal_set_message("Succesfully Updated..!!");
			$form_state->setRedirect('mydata.display_table_controller_display');
			} else {
			$field  = array(
			'name' => $name,
			'mobilenumber' => $number,
			'email' =>  $email,
			'age' => $age,
			'address' => $address,
			'gender' => $gender,
			'entrydate' => $entrydate,
			);
			$query = \Drupal::database();
			$query->insert('mydata')->fields($field)->execute();
			drupal_set_message("Succesfully Saved..!!");
			
			$response = new RedirectResponse("/mydata/hello/table");
			$response->send();
		}
	}
}
