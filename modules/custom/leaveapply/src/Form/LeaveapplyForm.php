<?php

/**
 * @file
 * Contains \Drupal\leaveapply\Form\LeaveapplyForm.
*/

namespace Drupal\leaveapply\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

class LeaveapplyForm extends FormBase {
	/**
	* {@inheritdoc}
	*/
	public function getFormId() {
		return 'leaveapply_form';
	}

	/**
	* {@inheritdoc}
	*/
	public function buildForm(array $form, FormStateInterface $form_state) {
		$uid = \Drupal::currentUser()->id();
		$user = \Drupal\user\Entity\User::load($uid);
		$applicant_name = $user->field_first_name->value.' '.$user->field_last_name->value;

		if($user->roles->target_id != ''){
			$form['leave_type'] = array(
				'#type' => 'select',
				'#title' => 'Leave Type',
				'#required' => TRUE,
				'#options' => array(
					'casual_leave' => 'Casual Leave',
					'sick_leave' => 'Sick Leave',
					'paid_leave' => 'Paid Leave',
				),
			);
			$form['leave_from'] = array(
				'#type' => 'date',
				'#title' => 'From',
				'#required' => TRUE,
			);
			$form['leave_to'] = array(
				'#type' => 'date',
				'#title' => 'To',
				'#required' => TRUE,
			);
			$form['leave_reason'] = array(
				'#type' => 'textarea',
				'#title' => 'Reason',
				'#required' => TRUE,
			);
			$form['user_id'] = array(
				'#type' => 'hidden',
				'#value' => $uid,
			);
			$form['applicant_name'] = array(
				'#type' => 'hidden',
				'#value' => $applicant_name,
			);
			
			$form['actions']['#type'] = 'actions';
			$form['actions']['submit'] = array(
				'#type' => 'submit',
				'#value' => $this->t('Apply'),
				'#button_type' => 'primary',
			);
			return $form;
		}
		else{
			$form['form_info'] = array(
				'#markup' => '<strong>Please login to view this page</strong>',
			);
			return $form;
		}
	}

	/**
	* {@inheritdoc}
	*/
	public function validateForm(array &$form, FormStateInterface $form_state) {
		if ($form_state->getValue('leave_type') == '') {
			$form_state->setErrorByName('leave_type', $this->t('Please Enter Leave Type'));
		}
		if ($form_state->getValue('leave_from') == '') {
			$form_state->setErrorByName('leave_from', $this->t('Please Enter From Date'));
		}
		if ($form_state->getValue('leave_to') == '') {
			$form_state->setErrorByName('leave_to', $this->t('Please Enter To Date'));
		}
		if ($form_state->getValue('leave_reason') == '') {
			$form_state->setErrorByName('leave_reason', $this->t('Please Enter Reason'));
		}
	}

	/**
	* {@inheritdoc}
	*/
	public function submitForm(array &$form, FormStateInterface $form_state) {
		$apply_date = date('d-m-Y H-i-s', \Drupal::time()->getRequestTime());

		$node = Node::create(['type' => 'leave_application']);
		$node->langcode = "en";
		$node->promote = 0;
		$node->sticky = 0;
		$node->title= $form_state->getValue('applicant_name').' '.$apply_date;
		$node->field_user_id = $form_state->getValue('user_id');
		$node->field_leave_from = $form_state->getValue('leave_from');
		$node->field_leave_to = $form_state->getValue('leave_to');
		$node->field_reason = $form_state->getValue('leave_reason');
		$node->field_leave_type = $form_state->getValue('leave_type');
		$node->field_mail_status = 0;
		$node->field_leave_status = 'pending';
		$node->save();

		drupal_set_message('Leave Application Submitted..');
	}
}
