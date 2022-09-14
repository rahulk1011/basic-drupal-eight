<?php

namespace Drupal\reviews\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;

/**
* Class DisplayTableController.
*
* @package Drupal\reviews\Controller
*/
class DisplayTableController extends ControllerBase {
  public function getContent() {
    // First we'll tell the user what's going on. This content can be found
    // in the twig template file: templates/description.html.twig.
    // @todo: Set up links to create nodes and point to devel module.
    $build = [
      'description' => [
        '#theme' => 'reviews_description',
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
  public function display() {
      /**return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: display with parameter(s): $name'),
    ];*/

    $header_table = array(
      // 'id'=> t('ID'),
      'rimage' => t('Image'),
      'name' => t('Name'),
      'review' => t('Review'),
      'director' => t('Director'),
      // 'producer' => t('Producer'),
      // 'writer' => t('Writer'),
      'certification' => t('Certification'),
      'genre' => t('Genre'),
      'rating' => t('Rating'),
      'runtime' => t('Runtime'),
      'reviewer' => t('Reviewer'),
      'reviewdate' => t('Review Date'),
      'opt1' => t('Edit'),
      'opt' => t('Delete'),
    );

    //select records from table
    $query = \Drupal::database()->select('reviews', 'm');
    $query->fields('m', ['id', 'rimage', 'name', 'review', 'director','producer', 'writer', 'certification', 'genre', 'rating', 'runtime', 'reviewer', 'reviewdate']);
    $results = $query->execute()->fetchAll();
    $rows=array();
    foreach($results as $data) {
      $delete = Url::fromUserInput('/reviews/form/delete/'.$data->id);
      $edit   = Url::fromUserInput('/reviews/form/reviews?num='.$data->id);

      //print the data from table
      $rows[] = array(
        // 'id' =>$data->id,
        'rimage' => $data->rimage,
        'name' => $data->name,
        'review' => $data->review,
        'director' => $data->director,
        // 'producer' => $data->producer,
        // 'writer' => $data->writer,
        'certification' => $data->certification,
        'genre' => $data->genre,
        'rating' => $data->rating,
        'runtime' => $data->runtime,
        'reviewer' => $data->reviewer,
        'reviewdate' => $data->reviewdate,
        \Drupal::l('Edit', $edit),
        \Drupal::l('Delete', $delete),
      );
    }
    //display data in site
    $form['table'] = [
      '#type' => 'table',
      '#header' => $header_table,
      '#rows' => $rows,
      '#empty' => t('No Data Found'),
    ];
    return $form;
  }
}