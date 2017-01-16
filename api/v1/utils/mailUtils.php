<?php

class mailUtils {

	public $app;

	public function __construct(\Slim\Container $app) {
		$this->app = $app; 
	}

    public function sendAccountCreationEmail($appHome, $email, $name, $ticketNum, $password){
	    try {
	        $year = date("Y");
	        $template_name = 'account-creation';
	        $template_content = array(
	            array(
	                'name' => $name,
	                'year' => $year,
	                'ticketNum' => $ticketNum,
	                'password' => $password,
	                'url' => $appHome . "#/login"
	            )
	        );
	        $message = array(
	            'to' => array(
	                array(
	                    'email' => $email,
	                    'name' => $name,
	                    'type' => 'to'
	                )
	            ),
	            'important' => false,
	            'track_opens' => null,
	            'track_clicks' => null,
	            'auto_text' => null,
	            'auto_html' => null,
	            'inline_css' => null,
	            'url_strip_qs' => null,
	            'preserve_recipients' => null,
	            'view_content_link' => null,
	            'tracking_domain' => null,
	            'signing_domain' => null,
	            'return_path_domain' => null,
	            'merge' => true,
	            'merge_language' => 'mailchimp',
	            'tags' => array('account-creation'),
	        );
	        $async = false;
	        $ip_pool = '';
	        $send_at = '';
	        $result = $this->app->mandrill->messages->sendTemplate($template_name, $template_content, $message, $async, $ip_pool, $send_at);
	        return $result;
	    } catch(Mandrill_Error $e) {
	        // Mandrill errors are thrown as exceptions
	        $this->app->logger->error('A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage());
	        throw $e;
	    }
	}

	public function checkEmailResults($action, $results){
    	$ret = true;
	    foreach ($results as $result) {
	        if ($result['status'] == 'rejected'){
	            $ret = false;
	        }
	        $this->app->logger->addInfo("Email results for $action: ", $result);
	    }
	    return $ret;
	}
}

?>
