<?php

namespace Drupal\reviews\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
* Class ReviewsController.
*
* @package Drupal\reviews\Controller
*/
class ReviewsController extends ControllerBase
{
  /**
  * Display.
  *
  * @return string
  *   Return Hello string.
  */
  public function display()
  {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('This page contain all inforamtion about Movie Reviews ')
    ];
  }
}
