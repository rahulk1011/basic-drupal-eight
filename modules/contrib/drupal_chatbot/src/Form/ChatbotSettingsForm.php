<?php


namespace Drupal\drupal_chatbot\Form;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\MapArray;
use Drupal\file\Entity\File;


class ChatbotSettingsForm extends ConfigFormBase {
	public function getFormID() {
		return 'chatbot_settings_form';
	}

	/**
	* Form constructor.
	*
	* @param array $form
	*   An associative array containing the structure of the form.
	* @param array $form_state
	*   An associative array containing the current state of the form.
	*
	* @return array
	*   The form structure.
	*/

	protected function getEditableConfigNames() {
		return ['drupal_chatbot.credentials'];
	}
	public function buildForm(array $form, FormStateInterface $form_state) {
		$config = $this->config('drupal_chatbot.credentials');  //since we are extending ConfigFormBase instaad of FormBase, it gives use access to the config object

		$form['bot_logo'] = array(
			'#type' => 'managed_file',
			'#upload_location' => 'public://upload/bot_images',
			'#title' => t('Image'),
			'#upload_validators' => array(
				'file_validate_extensions' => array('jpg jpeg gif png')
			),
			'#default_value' => $config->get('bot_logo'),
			'#description' => t('The Bot Logo to display on Chat Messages'),
			'#required' => true
		);

		$form['bot_user_logo'] = array(
			'#type' => 'managed_file',
			'#upload_location' => 'public://upload/bot_images',
			'#title' => t('Image'),
			'#upload_validators' => array(
				'file_validate_extensions' => array('jpg jpeg gif png')
			),
			'#default_value' => $config->get('bot_user_logo'),
			'#description' => t('The Bot User Logo to display in Chat Messages'),
			'#required' => true
		);

		$form['bot_header_logo'] = array(
			'#type' => 'managed_file',
			'#upload_location' => 'public://upload/bot_images',
			'#title' => t('Image'),
			'#upload_validators' => array(
			'file_validate_extensions' => array('jpg jpeg gif png')
			),
			'#default_value' => $config->get('bot_header_logo'),
			'#description' => t('The Bot Header Logo to display in Chat Panel'),
			'#required' => true
		);

		$form['access_token'] = array(
			'#type' => 'textfield',
			'#description' => t('Dialogflow Client Access Token'),
			'#title' => t('Access Token'),
			'#default_value' => $config->get('access_token'),
			'#required' => true
		);
		return parent::buildForm($form,$form_state);
	}

	/**
	* Form submission handler.
	*
	* @param array $form
	*   An associative array containing the structure of the form.
	* @param array $form_state
	*   An associative array containing the current state of the form.
	*/
	public function submitForm(array &$form, FormStateInterface $form_state) {
		parent::submitForm($form, $form_state);

		$bot_img_loc = $form['bot_logo']['#upload_location'];
		$fid = $form_state->getValue('bot_logo');
		$db = \Drupal::database();
		$data = $db->select('file_managed', 'fe')
		->fields('fe')
		->orderBy('fe.fid', 'DESC')
		->range(0, 1)
		->condition('fe.fid', $fid, '=')
		->execute();
		$value = $data->fetchAssoc();
		$filename_bot = $value['filename'];
		$bot_url = file_create_url($bot_img_loc);
		$bot_url = parse_url($bot_url);
		//dpm($bot_url['path'].'/'.$filename_bot);

		$bot_user_img_loc = $form['bot_user_logo']['#upload_location'];
		$fid = $form_state->getValue('bot_user_logo');
		$db = \Drupal::database();
		$data = $db->select('file_managed', 'fe')
		->fields('fe')
		->orderBy('fe.fid', 'DESC')
		->range(0, 1)
		->condition('fe.fid', $fid, '=')
		->execute();
		$value = $data->fetchAssoc();
		$filename_bot_user = $value['filename'];
		$bot_user_url = file_create_url($bot_user_img_loc);
		$bot_user_url = parse_url($bot_user_url);
		//dpm($bot_user_url['path'].'/'.$filename_bot_user);

		$bot_header_img_loc = $form['bot_header_logo']['#upload_location'];
		$fid = $form_state->getValue('bot_header_logo');
		$db = \Drupal::database();
		$data = $db->select('file_managed', 'fe')
		->fields('fe')
		->orderBy('fe.fid', 'DESC')
		->range(0, 1)
		->condition('fe.fid', $fid, '=')
		->execute();
		$value = $data->fetchAssoc();
		$filename_bot_header = $value['filename'];
		$bot_header_url = file_create_url($bot_header_img_loc);
		$bot_header_url = parse_url($bot_header_url);
		//dpm($bot_user_url['path'].'/'.$filename_bot_header);

		//dpm($bot_img_loc);

		$image1 = $form_state->getValue('bot_logo');
		$image2 = $form_state->getValue('bot_user_logo');
		$image3 = $form_state->getValue('bot_header_logo');

		// Load the object of the file by its fid.
		$file1 = File::load($image1[0]);
		$file2 = File::load($image2[0]);
		$file3 = File::load($image3[0]);

		// Set the status flag permanent of the file object.
		if (!empty($file1) && !empty($file2) && !empty($file3)) {
			$file1->setPermanent();
			$file2->setPermanent();
			$file3->setPermanent();

			// Save the file in the database.
			$file1->save();
			$file2->save();
			$file3->save();

			$file_usage = \Drupal::service('file.usage');
			$file_usage->add($file1, 'welcome', 'welcome', \Drupal::currentUser()->id());
			$file_usage->add($file2, 'welcome', 'welcome', \Drupal::currentUser()->id());
			$file_usage->add($file3, 'welcome', 'welcome', \Drupal::currentUser()->id());
		}

		$this->config('drupal_chatbot.credentials')
		->set('bot_logo', $bot_url['path'].'/'.$filename_bot)
		->set('bot_user_logo', $bot_user_url['path'].'/'.$filename_bot_user)
		->set('bot_header_logo', $bot_header_url['path'].'/'.$filename_bot_header)
		->set('access_token', $form_state->getValue('access_token'))
		->save();
	}
}