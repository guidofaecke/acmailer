<?php

declare(strict_types=1);

namespace AcMailer\Service;

use AcMailer\Exception;
use AcMailer\Model\Email;
use AcMailer\Result\ResultInterface;

interface MailServiceInterface
{
    /** @deprecated Use Email::DEFAULT_CHARSET instead */
    public const DEFAULT_CHARSET = Email::DEFAULT_CHARSET;

    /**
     * Tries to send the message, returning a MailResult object
     *
     * @param string|array|Email $email
     * @param array $options
     * @throws Exception\InvalidArgumentException
     * @throws Exception\EmailNotFoundException
     * @throws Exception\MailException
     */
    public function send($email, array $options = []): ResultInterface;
}
