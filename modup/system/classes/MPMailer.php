<?php

/**
 * Helper class for the Swift MPMailer library.
 *
 * Configuration is set in system/config.misc.php
 * If multiple transports need to be used, use the Swift MPMailer library directly
 */

class MPMailer
{
    public $transport;
    public $mailer;
    public $m;
    public $message;
    public $foo;

    /**
     * set the $config parameter to override any EMAIL_* constants
     *
     * example: $config['transport'] will override EMAIL_TRANSPORT
     */
    function __construct($config = array())
    {
        if (!defined('EMAIL_TRANSPORT') && !eka($config, 'transport'))
        {
            throw new Exception('Email configuration not set');
        }
        if (!class_exists('Swift_MPMailer'))
        {
            include_once DIR_LIB.'/swift-mailer/swift_required.php';
        }
        $transport = deka(EMAIL_TRANSPORT, $config, 'transport');
        $hostname = deka(EMAIL_HOSTNAME, $config, 'hostname');
        $port = deka(EMAIL_PORT, $config, 'port');
        $username = deka(EMAIL_USERNAME, $config, 'username');
        $password = deka(EMAIL_PASSWORD, $config, 'password');

        switch ($transport)
        {
            case 'smtp':
                $this->transport = Swift_SmtpTransport::newInstance($hostname, $port)
                    ->setMPUsername($username)
                    ->setPassword($password);
            break;
            case 'sendmail':
                $this->transport = Swift_SendmailTransport::newInstance($hostname);
            break;
            case 'mail':
                $this->transport = Swift_MailTransport::newInstance();
            break;
        }
        $this->mailer = Swift_MPMailer::newInstance($this->transport);
        $this->message = Swift_Message::newInstance();
        $this->m =& $this->message;
        $this->foo = 'asd';
    }

    function send()
    {
        $this->mailer->send($this->message);
    }

    function __call($name, $params)
    {
        return call_user_func_array(array($this->message, $name), $params);
    }

}
?>
