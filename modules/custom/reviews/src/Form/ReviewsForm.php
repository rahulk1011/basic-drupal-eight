<?php

namespace Drupal\reviews\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
* Class ReviewsForm.
*
* @package Drupal\reviews\Form
*/
class ReviewsForm extends FormBase
{
  /**
  * {@inheritdoc}
  */
  public function getFormId()
  {
    return 'reviews_form';
  }

  /**
  * {@inheritdoc}
  */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $conn = Database::getConnection();
    $record = array();
    if (isset($_GET['num'])) {
      $query = $conn->select('reviews', 'm')
      ->condition('id', $_GET['num'])
      ->fields('m');
      $record = $query->execute()->fetchAssoc();
    }
    $form['new'] = array(
        '#type' => 'markup',
        '#markup' => '<h2>'.t('Enter a New Movie Review:').'</h2>',
    );
    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => t('Name:'),
      '#required' => TRUE,
      '#default_value' => (isset($record['name']) && $_GET['num']) ? $record['name']:'',
    );
    $form['review'] = array(
      '#type' => 'textarea',
      '#title' => t('Review:'),
      '#required' => TRUE,
      '#default_value' => (isset($record['review']) && $_GET['num']) ? $record['review']:'',
    );
    $form['director'] = array (
      '#type' => 'textfield',
      '#title' => t('Director'),
      '#required' => TRUE,
      '#default_value' => (isset($record['director']) && $_GET['num']) ? $record['director']:'',
    );
    $form['producer'] = array (
      '#type' => 'textfield',
      '#title' => t('Producer'),
      '#required' => TRUE,
      '#default_value' => (isset($record['producer']) && $_GET['num']) ? $record['producer']:'',
    );
    $form['writer'] = array (
      '#type' => 'textfield',
      '#title' => t('Writer'),
      '#required' => TRUE,
      '#default_value' => (isset($record['writer']) && $_GET['num']) ? $record['writer']:'',
    );
    $form['certification'] = array (
      '#type' => 'radios',
      '#title' => t('Certification'),
      '#required' => TRUE,
      '#options' => array(
        'General' => t('General'),
        'Mature' => t('Mature'),
        'ParentalGuidance' => t('Parental Guidance'),
        'NotRated' => t('Not Rated'),
        '#default_value' => (isset($record['certification']) && $_GET['num']) ? $record['certification']:'',
      ),
    );
    $form['genre'] = array (
      '#type' => 'radios',
      '#title' => t('Genre'),
      '#required' => TRUE,
      '#options' => array(
        'Action' => t('Action'),
        'Adventure' => t('Adventure'),
        'Biography' => t('Biography'),
        'Documentary' => t('Documentary'),
        'Drama' => t('Drama'),
        'Horror' => t('Horror'),
        'Musical' => t('Musical'),
        'Romance' => t('Romance'),
        'ScienceFiction' => t('Science Fiction'),
        'Thriller' => t('Thriller'),
        '#default_value' => (isset($record['genre']) && $_GET['num']) ? $record['genre']:'',
      ),
    );
    $form['rating'] = array (
      '#type' => 'select',
      '#title' => ('Rating'),
      '#required' => TRUE,
      '#options' => array(
        '5-Star' => t('5-Star'),
        '4-Star' => t('4-Star'),
        '3-Star' => t('3-Star'),
        '2-Star' => t('2-Star'),
        '1-Star' => t('1-Star'),
        '#default_value' => (isset($record['rating']) && $_GET['num']) ? $record['rating']:'',
      ),
    );
    $form['runtime'] = array (
      '#type' => 'textfield',
      '#title' => t('Runtime'),
      '#required' => TRUE,
      '#default_value' => (isset($record['runtime']) && $_GET['num']) ? $record['runtime']:'',
    );
    $form['reviewer'] = array (
      '#type' => 'textfield',
      '#title' => t('Reviewer'),
      '#required' => TRUE,
      '#default_value' => (isset($record['reviewer']) && $_GET['num']) ? $record['reviewer']:'',
    );
    $form['reviewdate'] = array (
      '#type' => 'date',
      '#title' => t('Review Date'),
      '#required' => TRUE,
      '#default_value' => (isset($record['reviewdate']) && $_GET['num']) ? $record['reviewdate']:'',
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
    $name = $form_state->getValue('runtime');
    if(preg_match('/[^0-9]/', $name))
    {
      $form_state->setErrorByName('runtime', $this->t('Run-time must be a number!'));
    }
    if (strlen($form_state->getValue('review')) > 1000 )
    {
      $form_state->setErrorByName('review', $this->t('Review maximum limit is 1000 characters!'));
    }
    parent::validateForm($form, $form_state);
  }

  /**
  * {@inheritdoc}
  */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $field=$form_state->getValues();
    $name = $field['name'];
    $review = $field['review'];
    $director = $field['director'];
    $producer = $field['producer'];
    $writer = $field['writer'];
    $certification = $field['certification'];
    $genre = $field['genre'];
    $rating = $field['rating'];
    $runtime = $field['runtime'];
    $reviewer = $field['reviewer'];
    $reviewdate = $field['reviewdate'];

    if (isset($_GET['num']))
    {
      $field  = array(
        'name' => $name,
        'review' => $review,
        'director' => $director,
        'producer' => $producer,
        'writer' => $writer,
        'certification' => $certification,
        'genre' => $genre,
        'rating' => $rating,
        'runtime' => $runtime,
        'reviewer' => $reviewer,
        'reviewdate' => $reviewdate,
      );
      $query = \Drupal::database();
      $query->update('reviews')
      ->fields($field)
      ->condition('id', $_GET['num'])
      ->execute();
      drupal_set_message("Succesfully Updated");
      $form_state->setRedirect('reviews.display_table_controller_display');
    }
    else
    {
      $field  = array(
        'name' => $name,
        'review' => $review,
        'director' => $director,
        'producer' => $producer,
        'writer' => $writer,
        'certification' => $certification,
        'genre' => $genre,
        'rating' => $rating,
        'runtime' => $runtime,
        'reviewer' => $reviewer,
        'reviewdate' => $reviewdate,
      );
      $query = \Drupal::database();
      $query ->insert('reviews')->fields($field)->execute();
      drupal_set_message("Succesfully Saved");

      $response = new RedirectResponse("/crudop8/reviews/hello/table");
      $response->send();
    }
  }
}
