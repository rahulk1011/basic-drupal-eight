<?php

namespace Drupal\drupal_chatbot\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Drupal\search_api\Query;
use Drupal\Core\Session\UserSession;
use Drupal\Core\Session\AccountInterface;
use Drupal\search_api\ParseMode\ParseModePluginManager;
use Drupal\search_api\Entity\Index;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Routing\TrustedRedirectResponse;

/**
	*  Class DefaultController.
*/

class DefaultController extends ControllerBase {
	/**
	* Symfony\Component\HttpFoundation\RequestStack definition.
	*
	* @var \Symfony\Component\HttpFoundation\RequestStack
	*/
	protected $requestStack;

	/**
	* The logger factory.
	*
	* @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
	*/
	protected $loggerFactory;

	/**
	* Constructs a new DefaultController object.
	*/
	public function __construct(RequestStack $request_stack, LoggerChannelFactoryInterface $loggerFactory) {
		$this->requestStack = $request_stack;
		$this->loggerFactory = $loggerFactory;
	}

	/**
	* {@inheritdoc}
	*/
	public static function create(ContainerInterface $container) {
		return new static(
		$container->get('request_stack'),
		$container->get('logger.factory')
		);
	}

	/**
	* Handlerequest.
	*
	* @return mixed
	*   Return Hello string.
	*/
	public function handleRequest(Request $http_request) {
		\Drupal::logger('chatbot')->error("Request Arrived");
		$content = $http_request->getContent();
		\Drupal::logger('chatbot')->error($content);
		$method = $_SERVER['REQUEST_METHOD'];

		// Process only when request method is POST

		if($method == 'POST'){
			$requestBody = file_get_contents('php://input');
			$json = json_decode($requestBody);

			$text = (!empty($json->result->resolvedQuery)) ? $json->result->resolvedQuery : '';

			$intent = (!empty($json->result->metadata->intentName)) ? $json->result->metadata->intentName : '';

			$param_email = (!empty($json->result->parameters->email)) ? $json->result->parameters->email : '';
			$param_name = (!empty($json->result->parameters->name)) ? $json->result->parameters->name : '';

			$responseText = $this->prepareResponse($intent, $text, $param_email, $param_name);

			$data = [
			'speech' => $responseText,
			'displayText' => $responseText,
			'data' => '',
			'contextOut' => [],
			'source' => 'LiquidHubDrupal',
			];

			return JsonResponse::create($data, 200);
		}
		else{
			echo "Method not allowed";
		}

		/**
		*  Dialogflow V2 API
		$data = [
		'fulfillmentText' => 'Cache Rebuild Completed for the Site, Cheers!',
		'fulfillmentMessages' => [],
		'source' => 'LiquidHubDrupal',
		];
		*/

		/**
		*  Dialogflow V1 API*
		$data = [
		'speech' => 'Cache Rebuild Completed for the Site',
		'displayText' => 'Cache Rebuild Completed',
		'data' => '',
		'contextOut' => [],
		'source' => 'LiquidHubDrupal',
		];
		*/
	}

	protected function prepareResponse($intent, $text, $param_email, $param_name){
		global $base_url;
		switch ($intent) {
			case 'WorldPublish':
			$latestArticles = $this->current_posts_contents();
			return $latestArticles;
			break;

			case 'Drupal-Pages':
			$latestPages = $this->current_pages_contents();
			return $latestPages;
			break;

			case 'Drupal-Search':
			$searchResult = $this->current_web_search($text);
			return $searchResult;
			break;

			default:
			return 'Sorry I Could not get you, Please repeat again or visit <a href="'.$base_url.'">Our Website</a>. Would You like to Search Our website Just Type: <b> Search [Your Term ex: Pricing] </b> ';
			break;
		}
	}

	protected function current_posts_contents() {
		$query = \Drupal::entityQuery('node');
		$newest_articles = $query->condition('type', 'article')->condition('status', 1)->sort('created', 'DESC')->pager(5);
		$nids = $query->execute();

		foreach ($nids as $nid) {
			$node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
			$name = $node->getTitle();
			$path = '/drupal8/node/' .(int)$node;
			$langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
			$path_alias = \Drupal::service('path.alias_manager')->getAliasByPath($path, $langcode);
			$articles = $articles . ' '.'<a href='.$path_alias.'>'. $name .'</a>'. ',';
		}

		return "Here are the five latest articles from DrupalBot, " . $articles . "You can read one of the articles by clicking title of the article";
	}

	protected function current_pages_contents() {
		$query = \Drupal::entityQuery('node');
		$newest_pages = $query->condition('type', 'page')->condition('status', 1)->sort('created', 'DESC')->pager(5);
		$nids = $query->execute();

		foreach ($nids as $nid) {
			$node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
			$name = $node->getTitle();
			$path = '/drupal8/node/' . (int)$nid;
			$langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
			$path_alias = \Drupal::service('path.alias_manager')->getAliasByPath($path, $langcode);
			$pages = $pages . ' '.'<a href='.$path_alias.'>'. $name .'</a>'. ',';
		}
		if($pages){
			return "Here are the five latest pages from DrupalBot, " . $pages . "You can read one of the Pages by clicking title of the page";
		}
		else{
			return "Sorry We Could not find any pages as of Now! Please try something else.";
		}
	}

	protected function current_web_search($text) {
		global $base_url;
		$org_text='';
		$pages = '';
		$text = strtolower($text);
		$pagehref='';
		if (strpos($text, 'search') !== false) {
			$org_text = trim(str_replace('search', '', $text));
			$queryPages = \Drupal::entityQuery('node');
			$newest_pages = $queryPages->condition('title', $org_text, 'CONTAINS')->condition('status', 1);
			$page_nids = $queryPages->execute();
			foreach ($page_nids as $pnid) {
				$node = \Drupal::entityTypeManager()->getStorage('node')->load($pnid);
				$name = $node->getTitle();
				$path = '/node/' . (int)$pnid;
				$langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
				$path_alias = \Drupal::service('path.alias_manager')->getAliasByPath($path, $langcode);
				$pages = $pages . ' '.'<a href="'.$base_url.$path.'">'. $name .'</a>'. ',';
				$pagehref = (int)$pnid;
			}
		}

		if($pages){
			if(count($pages) == 1){
				return 'Search Results for  <b>'.$org_text.'</b>  is:  '.$pages;
			}
			return 'Search Results for  <b>'.$org_text.'</b>  is:  '.$pages.'  Please click on the link to read ';
		}
		else{
			return 'Sorry! We could not process your query  <b>'.$text.'</b>,  Try with other keyword Like : <b> Search [Your Term ex: Pricing] </b>';
		}
	}
}