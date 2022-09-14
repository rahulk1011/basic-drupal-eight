<?php

namespace Drupal\drupal_chatbot\Plugin\Block;
use Drupal\Core\Block\BlockBase;

/**
* Provides a block for executing PHP code.
*
* @Block(
*   id = "chatbot_block",
*   admin_label = @Translation("Chat Bot")
* )
*/
class DrupalbotBlock extends BlockBase {
	/**
	* Builds and returns the renderable array for this block plugin.
	*
	* @return array
	*   A renderable array representing the content of the block.
	*
	* @see \Drupal\block\BlockViewBuilder
	*/
	public function build() {
		$config = \Drupal::config('drupal_chatbot.credentials');
		$botheaderlogo->img =  $config->get('bot_header_logo');
		$botheader_logo[] = $botheaderlogo;
		$params = array('botheaderlogo' => $botheader_logo);
		$chatbot_template = array('#theme' => 'chatbot_web', '#params' => $params);
		return $chatbot_template;
	}
}