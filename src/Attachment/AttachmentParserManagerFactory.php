<?php

declare(strict_types=1);

namespace AcMailer\Attachment;

use interop\container\containerinterface;
use Laminas\Stdlib\ArrayUtils;
use Psr\Container;

class AttachmentParserManagerFactory
{
    /**
     * @throws Container\ContainerExceptionInterface
     * @throws Container\NotFoundExceptionInterface
     */
    public function __invoke(containerinterface $container): AttachmentParserManager
    {
        /** @var array $config */
        $config               = $container->get('config');
        $oldAttachmentParsers = $config['attachment_parsers'] ?? [];
        $attachmentParsers    = $config['acmailer_options']['attachment_parsers'] ?? [];

        return new AttachmentParserManager($container, ArrayUtils::merge($oldAttachmentParsers, $attachmentParsers));
    }
}
