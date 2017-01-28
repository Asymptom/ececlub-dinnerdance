<?php

class MandrillMailer implements iMailer{

    public $app;
    public $mandrill;

    public function __construct(\Slim\Container $app, Mandrill $mandrill) {
        $this->app = $app; 
        $this->mandrill = $mandrill;
    }

    private function checkEmailResults($action, $results){
        $ret = true;
        foreach ($results as $result) {
            if ($result['status'] != 'sent'){
                $ret = false;
            }
            $this->app->logger->addInfo("Email results for $action: ", $result);
        }
        return $ret;
    }

    public function bulkSendAccountCreationEmail($appHome, $emails, $names, $ticketNums, $passwords){
        //TODO
        return false;
    }

    public function sendAccountCreationEmail($appHome, $email, $name, $ticketNum, $password){
        $year = date("Y");
        $template_name = 'account-creation';
        $template_content = array(

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
            'merge_language' => 'handlebars',
            'global_merge_vars' => array(
                array(
                    'name' => 'year',
                    'content' => $year
                ),
                array(
                    'name' => 'login_url',
                    'content' => $appHome . '#/login'
                )
            ),
            'merge_vars' => array(
                array(
                    'rcpt' => $email,
                    'vars' => array(
                        array(
                            'name' => 'first_name',
                            'content' => $name 
                        ),
                        array(
                            'name' => 'ticket_num',
                            'content' => $ticketNum 
                        ),
                        array(
                            'name' => 'password',
                            'content' => $password 
                        )
                    )
                )
            ),
            'tags' => array($template_name),
        );
        $async = false;
        $ip_pool = '';
        $send_at = '';

        try {
            $results = $this->mandrill->messages->sendTemplate($template_name, $template_content, $message, $async, $ip_pool, $send_at);
            $this->app->logger->addInfo("Mandrill Email Results for $template_name $email $ticketNum", $results);
            return $this->checkEmailResults($template_name, $results);
        } catch(Mandrill_Error $e) {
            // Mandrill errors are thrown as exceptions
            $this->app->logger->error('A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage());
            return false; //fail silently
        }
    }

    public function sendPasswordResetRequestEmail($appHome, $email, $name, $resetLink){
        $year = date("Y");
        $template_name = 'password-reset-request';
        $template_content = array(
                array(
                'name' => 'header',
                'content' => '<h2>Password Reset Request</h2>'
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
            'merge_language' => 'handlebars',
            'global_merge_vars' => array(
                array(
                    'name' => 'reset_url',
                    'content' => $appHome . "#/passwordReset/" . $resetLink
                )
            ),
            'merge_vars' => array(
                array(
                    'rcpt' => $email,
                    'vars' => array(
                        array(
                            'name' => 'first_name',
                            'content' => $name 
                        ),
                    )
                )
            ),
            'tags' => array($template_name),
        );
        $async = false;
        $ip_pool = '';
        $send_at = '';
            
        try {
            $results = $this->mandrill->messages->sendTemplate($template_name, $template_content, $message, $async, $ip_pool, $send_at);
            $this->app->logger->addInfo("Mandrill Email Results for $template_name $email", $results);
            return $this->checkEmailResults($template_name, $results);
        } catch(Mandrill_Error $e) {
            // Mandrill errors are thrown as exceptions
            $this->app->logger->error('A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage());
            return false; //fail silently
        }
    }

    public function sendPasswordResetEmail($appHome, $email, $name, $ticketNum, $password){
        $year = date("Y");
        $template_name = 'password-reset';
        $template_content = array(
            array(
                'name' => 'header',
                'content' => '<h2>Password Reset</h2>'
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
            'merge_language' => 'handlebars',
            'merge_language' => 'handlebars',
            'global_merge_vars' => array(
                array(
                    'name' => 'year',
                    'content' => $year
                ),
                array(
                    'name' => 'login_url',
                    'content' => $appHome . '#/login'
                )
            ),
            'merge_vars' => array(
                array(
                    'rcpt' => $email,
                    'vars' => array(
                        array(
                            'name' => 'first_name',
                            'content' => $name 
                        ),
                        array(
                            'name' => 'ticket_num',
                            'content' => $ticketNum 
                        ),
                        array(
                            'name' => 'password',
                            'content' => $password 
                        )
                    )
                )
            ),
            'tags' => array($template_name),
        );
        $async = false;
        $ip_pool = '';
        $send_at = '';
            
        try {
            $results = $this->mandrill->messages->sendTemplate($template_name, $template_content, $message, $async, $ip_pool, $send_at);
            $this->app->logger->addInfo("Mandrill Email Results for $template_name $email $ticketNum", $results);
            return $this->checkEmailResults($template_name, $results);
        } catch(Mandrill_Error $e) {
            // Mandrill errors are thrown as exceptions
            $this->app->logger->error('A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage());
            return false; //fail silently
        }
        return false;
    }
}

?>
