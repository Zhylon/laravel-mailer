<?php

namespace Zhylon\Mailer;

use Symfony\Component\Mime\Header\UnstructuredHeader;

class MessageStreamHeader extends UnstructuredHeader
{
    public function __construct(string $value)
    {
        parent::__construct('X-PM-Message-Stream', $value);
    }
}
