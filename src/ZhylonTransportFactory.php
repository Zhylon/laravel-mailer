<?php

namespace Zhylon\Mailer;

use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mailer\Transport\AbstractTransportFactory;
use Symfony\Component\Mailer\Exception\UnsupportedSchemeException;

class ZhylonTransportFactory extends AbstractTransportFactory
{
    public function create(Dsn $dsn): TransportInterface
    {
        $transport = null;
        $scheme = $dsn->getScheme();
        $user = $this->getUser($dsn);

        if ('zhylon-mail' === $scheme || 'zhylon-mail+api' === $scheme) {
            $host = 'default' === $dsn->getHost() ? null : $dsn->getHost();
            $port = $dsn->getPort();

            $transport = (new ZhylonApiTransport($user, $this->client, $this->dispatcher, $this->logger))->setHost($host)->setPort($port);
        }

        if (null !== $transport) {
            $messageStream = $dsn->getOption('message_stream');

            if (null !== $messageStream) {
                $transport->setMessageStream($messageStream);
            }

            return $transport;
        }

        throw new UnsupportedSchemeException($dsn, 'zhylon-mail', $this->getSupportedSchemes());
    }

    protected function getSupportedSchemes(): array
    {
        return ['zhylon-mail', 'zhylon-mail+api',];
    }
}
