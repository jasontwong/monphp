<?php

/**
 * Helper class for the Swift MPMailer library.
 *
 * Configuration is set in system/config.mailer.php
 * If multiple transports need to be used, use the Swift MPMailer library directly
 */

class MPMailer
{
    // {{{ properties
    public $mailer;
    public $message;
    // }}}
    // {{{ function __call($name, $params)
    function __call($name, $params)
    {
        return call_user_func_array(array($this->message, $name), $params);
    }
    // }}}
    // {{{ function __construct($config = array())
    /**
     * set the $config parameter to override any EMAIL_* constants
     *
     * example: $config['transport'] will override EMAIL_TRANSPORT
     */
    function __construct($config = array())
    {
        include DIR_SYS . '/config.mailer.php';
        if (!defined('EMAIL_TRANSPORT') && !eka($config, 'transport'))
        {
            throw new Exception('Email configuration not set');
        }
        if (!class_exists('Swift_Mailer'))
        {
            include_once DIR_LIB . '/swift-mailer/swift_required.php';
        }
        $transport = deka(EMAIL_TRANSPORT, $config, 'transport');
        $hostname = deka(EMAIL_HOSTNAME, $config, 'hostname');

        switch ($transport)
        {
            case 'smtp':
                $port = deka(EMAIL_PORT, $config, 'port');
                $username = deka(EMAIL_USERNAME, $config, 'username');
                $password = deka(EMAIL_PASSWORD, $config, 'password');
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
    }
    // }}}
    // {{{ function send()
    function send()
    {
        $this->mailer->send($this->message);
    }
    // }}}
    // {{{ static function is_email($email)
    static function is_email($email)
    {
        if (!class_exists('Swift_Validate'))
        {
            include DIR_LIB.'/swift-mailer/swift_required.php';
        }
        return Swift_Validate::email($email);
    }
    // }}}
}
