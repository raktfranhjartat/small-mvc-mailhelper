<?php

namespace Raktfranhjartat\MailHelper;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class MailHelper 
{
    private array $smtpConfig = [];

    public function __construct(array $smtpConfig) 
    {
        $this->smtpConfig = $smtpConfig;
    }

    /**
     * Den enda metoden du behöver. Tar emot mottagare, ämne och färdig HTML-body.
     */
    public function send(string $to, string $subject, string $htmlBody, ?string $replyTo = null): bool 
    {
        $mail = new PHPMailer(true);

        try {
            // Serverinställningar
            $mail->isSMTP();
            $mail->Host       = $this->smtpConfig['host'] ?? '';
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->smtpConfig['username'] ?? '';
            $mail->Password   = $this->smtpConfig['password'] ?? '';
            $mail->SMTPSecure = $this->smtpConfig['encryption'] ?? 'tls';
            $mail->Port       = $this->smtpConfig['port'] ?? 587;

            // Mottagare
            $mail->setFrom($this->smtpConfig['from_email'] ?? '', $this->smtpConfig['from_name'] ?? '');
            $mail->addAddress($to);
            
            if ($replyTo) {
                $mail->addReplyTo($replyTo);
            }

            // Innehåll
            $mail->isHTML(true);
            $mail->Subject  = $subject;
            $mail->Body     = $htmlBody;
            $mail->CharSet  = 'UTF-8';
            $mail->Encoding = 'base64';

            $mail->send();
            return true;
        } catch (PHPMailerException $e) {
            error_log("Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
}