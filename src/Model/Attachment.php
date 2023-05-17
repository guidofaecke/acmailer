<?php

declare(strict_types=1);

namespace AcMailer\Model;

final class Attachment
{
    private string $parserName;
    /** @var mixed */
    private $value;

    /**
     * @param mixed $value
     */
    public function __construct(string $parserName, $value)
    {
        $this->parserName = $parserName;
        $this->value      = $value;
    }

    public static function fromArray(array $data): self
    {
        return new self($data['parser_name'], $data['value']);
    }

    public function getParserName(): string
    {
        return $this->parserName;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
