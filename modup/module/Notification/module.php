<?php

class Notification
{
    //{{{ properties
    private $mailer_type = NULL;
    private $using_auth = FALSE;
    private $smtp_info = array();

    //}}}
    //{{{ constants 
    const MODULE_AUTHOR = 'Jason T. Wong';
    const MODULE_DESCRIPTION = 'Notification Module';
    const MODULE_WEBSITE = '';
    const MODULE_DEPENDENCY = 'User';

    const TYPE_NOTICE = 1;
    const TYPE_SUCCESS = 2;
    const TYPE_ERROR = 3;
    const TYPE_IMPORTANT = 4;

    //}}}
    //{{{ constructor
    /**
     * @param int $state current state of module manager
     */
    public function __construct()
    {
    }

    //}}}
    //{{{ public function hook_active()
    public function hook_active()
    {
        // self::notify(self::SUCCESS, array('test 1', 'test 2'));
    }

    //}}}
    //{{{ public function hook_admin_js()
    public function hook_admin_js()
    {
        $js = array();
        $js[] = '/admin/static/Notification/notify.js/';
        return $js;
    }

    //}}}
    //{{{ public function hook_admin_css()
    public function hook_admin_css()
    {
        return array(
            'screen' => array(
                '/admin/static/Notification/notify.css/',
            )
        );
    }

    //}}}
    //{{{ public function hook_admin_nav()
    public function hook_admin_nav()
    {
        $links = array();
        if (User::perm('admin') && CMS_DEVELOPER)
        {
            $links['Tools'] = array(
                '<a href="/admin/module/Notification/test/">Test Notification</a>',
            );
        }

        return $links;
    }

    //}}}
    //{{{ public function hook_admin_module_page($page)
    public function hook_admin_module_page($page)
    {
    }
    
    //}}}
    //{{{ public function hook_rpc($action, $params = NULL)
    /**
     * Implementation of hook_rpc
     *
     * This looks at the action and checks for the method _rpc_<action> and
     * passes the parameters to that. There is no limit on parameters.
     *
     * @param string $action action name
     * @return string
     */
    public function hook_rpc($action)
    {
        $method = '_rpc_'.$action;
        $caller = array($this, $method);
        $args = array_slice(func_get_args(), 1);
        return method_exists($this, $method) 
            ? call_user_func_array($caller, $args)
            : '';
    }

    //}}}
    //{{{ public function _rpc_notify()
    public function _rpc_notify()
    {
        $notifications = array();
        
        $data = User::setting('notifications');
        if (is_array($data))
        {
            foreach ($data as $type => $messages)
            {
                $notifications[] = array(
                    'type' => $type,
                    'messages' => $messages
                );
                $data[$type] = array();
            }
        }
        
        echo json_encode($notifications);
        User::update('setting', 'notifications', $data);
    }

    //}}}
    //{{{ public static function notify($type, $messages, $user = NULL)
    public static function notify($type, $messages, $user = NULL)
    {
        if (is_string($messages))
        {
            $messages = array($messages);
        }
        $data = !is_null(User::setting('notifications'))
            ? User::setting('notifications')
            : array();
        switch ($type)
        {
            case self::TYPE_NOTICE:
                $data['notice'] = ake('notice', $data)
                    ? array_merge($data['notice'], $messages)
                    : $messages;
            break;
            case self::TYPE_SUCCESS:
                $data['success'] = ake('success', $data)
                    ? array_merge($data['success'], $messages)
                    : $messages;
            break;
            case self::TYPE_ERROR:
                $data['error'] = ake('error', $data)
                    ? array_merge($data['error'], $messages)
                    : $messages;
            break;
            case self::TYPE_IMPORTANT:
                $data['important'] = ake('important', $data)
                    ? array_merge($data['important'], $messages)
                    : $messages;
            break;
        }
        User::update('setting', 'notifications', $data);
    }
    
    //}}}
    // {{{ public static function email($from, $addresses, $message, $attachments = array(), $reply_to = array(), $headers = array(), $template = NULL)
    public static function email($from, $addresses, $message, $attachments = array(), $reply_to = array(), $headers = array(), $template = NULL)
    {
        // TODO templates
        // TODO other things like priority, charset, encoding?
        if (!ake('name', $from))
        {
            $from['name'] = '';
        }
        if (!empty($reply_to) && !ake('name', $reply_to))
        {
            $reply_to['name'] = '';
        }
        require_once dirname(__FILE__).'/includes/phpmailer/class.phpmailer.php';
        $mailer = new PHPMailer(TRUE);
        // {{{ switch mailer type
        switch ($this->mailer_type)
        {
            case 'smtp':
                $smtp = $this->smtp_info;
                if (!empty($smtp))
                {
                    $mailer->IsSMTP();
                    $mailer->Host = $smtp['host'];
                    $mailer->Port = $smtp['port'];
                    if ($this->using_auth)
                    {
                        $mailer->SMTPAuth = TRUE;
                        $mailer->Username = $smtp['user'];
                        $mailer->Password = $smtp['password'];
                    }
                }
            break;
            case 'sendmail':
                $mailer->IsSendmail();
            break;
            case 'qmail':
                $mailer->IsQmail();
            break;
            default:
                $mailer->IsMail();
        }

        // }}}
        // {{{ try to send email
        try
        {
            $mailer->SetFrom($from['email'], $from['name']);
            if (!empty($reply_to))
            {
                $mailer->AddReplyTo($reply_to['email'], $reply_to['name']);
            }
            $to = FALSE;
            if (ake('to', $addresses))
            {
                foreach ($addresses['to'] as $email => $name)
                {
                    $mailer->AddAddress($email, $name);
                    $to = TRUE;
                }
            }
            $cc = FALSE;
            if (ake('cc', $addresses))
            {
                foreach ($addresses['cc'] as $email => $name)
                {
                    $mailer->AddCC($email, $name);
                    $cc = TRUE;
                }
            }
            $bcc = FALSE;
            if (ake('bcc', $addresses))
            {
                foreach ($addresses['bcc'] as $email => $name)
                {
                    $mailer->AddBCC($email, $name);
                    $bcc = TRUE;
                }
            }
            if (!($to || $cc || $bcc))
            {
                throw new Exception('No email address to send to');
            }
            if (ake('subject', $message))
            {
                $mailer->Subject = $message['subject'];
            }
            if (ake('alt', $message))
            {
                $mailer->AltBody = $message['alt'];
            }
            if (ake('body', $message))
            {
                $mailer->MsgHTML($message['body']);
            }
            foreach ($attachments as $file)
            {
                if (!ake('name', $file))
                {
                    $file['name'] = '';
                }
                if (!ake('encoding', $file))
                {
                    $file['encoding'] = 'base64';
                }
                if (!ake('type', $file))
                {
                    $file['type'] = 'application/octet-stream';
                }
                $mailer->AddAttachment($file['path'], $file['name'], $file['encoding'], $file['type']);
            }
            foreach ($headers as $header)
            {
                $mailer->addCustomHeader($header);
            }
            $mailer->Send();
            $success = TRUE;
        }
        catch (phpmailerException $e)
        {
            $succes = FALSE;
        }
        catch(Exception $e)
        {
            $succes = FALSE;
        }
        return $success;

        // }}}
    }
    
    //}}}
}

?>
