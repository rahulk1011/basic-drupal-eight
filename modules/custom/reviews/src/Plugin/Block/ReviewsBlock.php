<?php

namespace Drupal\reviews\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
* Provides a 'ReviewsBlock' block.
*
* @Block(
*  id = "reviews_block",
*  admin_label = @Translation("Reviews block"),
* )
*/
class ReviewsBlock extends BlockBase
{
  /**
  * {@inheritdoc}
  */
  public function build()
  {
    $form = \Drupal::formBuilder()->getForm('Drupal\reviews\Form\ReviewsForm');
    return $form;
  }
}
