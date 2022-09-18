<?php
/**
* @file
* Contains \Drupal\leaveapply\Controller\LeaveapplyController.
*/
namespace Drupal\leaveapply\Controller;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\user\Entity\User;
use Drupal\user\Entity\Role;
use Drupal\node\Entity\Node;

//Controller routines for candidates routes

class LeaveapplyController extends ControllerBase {
    /**
	* Apply Leave API
	*/
    public function apply_leave(Request $request) {
        global $base_url;
        try{
			$content = $request->getContent();
			$params = json_decode($content, TRUE);

            $uid = \Drupal::currentUser()->id();
		    $user = \Drupal\user\Entity\User::load($uid);
		    $applicant_name = $user->field_first_name->value.' '.$user->field_last_name->value;

            $apply_date = date('d-m-Y H-i-s', \Drupal::time()->getRequestTime());

			$date_from = explode('/', $params['leave_from']);
            $leave_from = $date_from[2] . "-" . $date_from[1] . "-" . $date_from[0];
			$date_to = explode('/', $params['leave_to']);
			$leave_to = $date_to[2] . "-" . $date_to[1] . "-" . $date_to[0];

			$newLeaveApply = Node::create([
				'type' => 'leave_application',
				'title' => array('value' => $applicant_name.' '.$apply_date),
                'field_user_id' => array('value' => $uid),
				'field_leave_from' => array('value' => $leave_from),
				'field_leave_to' => array('value' => $leave_to),
				'field_reason' => array('value' => $params['leave_reason']),
				'field_leave_type' => array('value' => $params['leave_type']),
				'field_mail_status' => array('value' => 0),
				'field_leave_status' => array('value' => 'pending'),
			]);

			// Makes sure this creates a new node
			$newLeaveApply->enforceIsNew();
			$newLeaveApply->save();
			$nid = $newLeaveApply->id();
			$new_leave_details = $this->fetch_leave_detail($nid);
			$final_api_reponse = array(
				"status" => "OK",
				"message" => "Leave Applied Successfully",
				"result" => $new_leave_details,
			);
			return new JsonResponse($final_api_reponse);
		}
		catch(Exception $exception) {
			$this->exception_error_msg($exception->getMessage());
		}
    }

    public function fetch_leave_detail($nid){
		if(!empty($nid)){
			$node = \Drupal::entityManager()->getStorage('node')->load($nid);
            
            // Loading user details
            $user_id = $node->get('field_user_id')->value;
            $user = \Drupal\user\Entity\User::load($user_id);
		    $applicant_name = $user->field_first_name->value.' '.$user->field_last_name->value;

			$date_from = date_create($node->get('field_leave_from')->value);
            $leave_from = date_format($date_from, "d/m/Y");
			$date_to = date_create($node->get('field_leave_to')->value);
			$leave_to = date_format($date_to, "d/m/Y");

            $leave_details['user_id'] = $user_id;
			$leave_details['applicant_name'] = $applicant_name;
            $leave_details['leave_from'] = $leave_from;
            $leave_details['leave_to'] = $leave_to;
			$leave_details['leave_type'] = $node->get('field_leave_type')->value;
			$leave_details['leave_reason'] = $node->get('field_reason')->value;

			$final_api_reponse = array(
				'leave_detail' => $leave_details
			);
			return $final_api_reponse;
		}
		else{
			$this->exception_error_msg("Leave application details not found.");
		}
	}
}