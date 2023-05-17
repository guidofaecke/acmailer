<?php

declare(strict_types=1);

namespace AcMailer\Attachment\Parser;

use AcMailer\Attachment\Helper\AttachmentHelperTrait;
use AcMailer\Exception\InvalidAttachmentException;
use Laminas\Mime\Part;

class MimePartAttachmentParser implements AttachmentParserInterface
{
    use AttachmentHelperTrait;

    /**
     * @param array|string|resource|Part $attachment
     * @throws InvalidAttachmentException
     */
    public function parse($attachment, ?string $attachmentName = null): Part
    {
        if (! $attachment instanceof Part) {
            throw InvalidAttachmentException::fromExpectedType(Part::class);
        }

        return $this->applyNameToPart($attachment, $attachmentName);
    }
}
