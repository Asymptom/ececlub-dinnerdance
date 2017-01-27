<?php

interface iMailer
{
	public function bulkSendAccountCreationEmail($appHome, $emails, $names, $ticketNums, $passwords);
    public function sendAccountCreationEmail($appHome, $email, $name, $ticketNum, $password);
    public function sendPasswordResetRequestEmail($appHome, $email, $name, $resetLink);
    public function sendPasswordResetEmail($appHome, $email, $name, $ticketNum, $password);
}

?>