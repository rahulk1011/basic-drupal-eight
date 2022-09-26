<?php

namespace Drupal\userinfo\Service;

class Userinfo {
    public function userinfo_birthday_mails() {
		// this is used to send HTML emails
		$send_mail = new \Drupal\Core\Mail\Plugin\Mail\PhpMail();

		$current_m_d = date('m-d', \Drupal::time()->getRequestTime());
		$fetch_query = \Drupal::database()->query("SELECT `firstname`, `email`, `dob` FROM `a_user_info`");
		$query_result = $fetch_query->fetchAll();

		foreach($query_result as $result){
			$birthday = explode('-', $result->dob);
			$birth_m_d = $birthday[1].'-'.$birthday[2];
	
			$user_name = $result->firstname;
			$user_mail = $result->email;
	
			if($birth_m_d == $current_m_d){
				$from = 'info@basics.com';
				$to = $user_mail;
				$message['headers'] = array(
					'content-type' => 'text/html',
					'MIME-Version' => '1.0',
					'reply-to' => $from,
					'from' => 'Admin Eight <'.$from.'>'
				);
				$message['to'] = $to;
				$message['subject'] = "Happy Birthday ".$user_name."!!!";
			
				$message['body'] = 'Dear '.$user_name.',
				<br><br>
				We value your special day just as much as we value you. On your birthday, we send you our warmest and most heartfelt wishes..
				<br><br>
				Regards,<br>
				Admin Eight';
			
				$send_mail->mail($message);
				\Drupal::logger('userinfo')->notice('Birthday mail sent to '.$user_name);
				\Drupal::logger('userinfo')->info($message['body']);
			};
		}
	}
}