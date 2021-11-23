<?php
	
	namespace Drupal\mydata\Controller;
	
	use Drupal\Core\Controller\ControllerBase;
	use Drupal\Core\Database\Database;
	use Drupal\Core\Url;
	
	/**
	* Class DisplayTableController.
	*
	* @package Drupal\mydata\Controller
	*/
	class DisplayTableController extends ControllerBase
	{
		public function getContent()
		{
			// First we'll tell the user what's going on. This content can be found
			// in the twig template file: templates/description.html.twig.
			// @todo: Set up links to create nodes and point to devel module.
			$build = [
			'description' => [
			'#theme' => 'mydata_description',
			'#description' => 'foo',
			'#attributes' => [],
			],
			];
			return $build;
		}
		
		/**
		* Display.
		*
		* @return string
		*   Return Hello string.
		*/
		public function display()
		{
			//create table header
			$header_table = array(
			'id' => t('ID'),
			'name' => t('Name'),
			'mobilenumber' => t('Mobile Number'),
			'email' => t('Email ID'),
			'age' => t('Age'),
			'address' => t('Address'),
			'gender' => t('Gender'),
			'entrydate' => t('Date'),
			'opt1' => t('Edit'),
			'opt' => t('Delete'),
			);
			
			//select records from table
			$query = \Drupal::database()->select('mydata', 'm');
			$query->fields('m', ['id', 'name', 'mobilenumber', 'email', 'age', 'address', 'gender', 'entrydate']);
			$results = $query->execute()->fetchAll();
			$rows=array();
			foreach($results as $data)
			{
				$delete = Url::fromUserInput('/mydata/form/delete/'.$data->id);
				$edit   = Url::fromUserInput('/mydata/form/mydata?num='.$data->id);
				
				//print the data from table
				$rows[] = array(
				'id' => $data->id,
				'name' => $data->name,
				'mobilenumber' => $data->mobilenumber,
				'email' => $data->email,
				'age' => $data->age,
				'address' => $data->address,
				'gender' => $data->gender,
				'entrydate' => date('d.m.Y', strtotime($data->entrydate)),
				\Drupal::l('Edit', $edit),
				\Drupal::l('Delete', $delete),
				);
			}
			//display data in site
			$form['table'] = [
			'#type' => 'table',
			'#header' => $header_table,
			'#rows' => $rows,
			'#empty' => t('No Data Found..'),
			];
			return $form;
		}
	}
