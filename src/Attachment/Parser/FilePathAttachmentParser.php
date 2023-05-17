<?php

declare(strict_types=1);

namespace AcMailer\Attachment\Parser;

use AcMailer\Attachment\Helper\AttachmentHelperTrait;
use AcMailer\Exception\InvalidAttachmentException;
use finfo;
use Laminas\Mime;
use Laminas\Mime\Exception\InvalidArgumentException;
use Laminas\Mime\Part;

use function basename;
use function fopen;
use function is_file;
use function is_string;

use const FILEINFO_MIME_TYPE;

class FilePathAttachmentParser implements AttachmentParserInterface
{
    use AttachmentHelperTrait;

    private finfo $finfo;

    public function __construct(?finfo $finfo = null)
    {
        $this->finfo = $finfo ?: new finfo(FILEINFO_MIME_TYPE);
    }

    /**
     * @param array|string|resource|Part $attachment
     * @throws InvalidArgumentException
     * @throws InvalidAttachmentException
     */
    public function parse($attachment, ?string $attachmentName = null): Mime\Part
    {
        if (! is_string($attachment) || ! is_file($attachment)) {
            throw InvalidAttachmentException::fromExpectedType('file path');
        }

        $part = new Mime\Part(fopen($attachment, 'rb'));
        /** @phpstan-ignore-next-line */
        $part->type = $this->finfo->file($attachment);

        // Make sure encoding and disposition have a default value
        $part->encoding    = Mime\Mime::ENCODING_BASE64;
        $part->disposition = Mime\Mime::DISPOSITION_ATTACHMENT;

        // If the attachment name is not defined, use the attachment's \basename
        $name = $attachmentName ?? basename($attachment);
        return $this->applyNameToPart($part, $name);
    }
}
