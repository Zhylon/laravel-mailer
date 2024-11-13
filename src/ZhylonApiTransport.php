<?php

namespace Zhylon\Mailer;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Header\TagHeader;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\Mailer\Header\MetadataHeader;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Transport\AbstractApiTransport;
use Symfony\Component\Mailer\Exception\HttpTransportException;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ZhylonApiTransport extends AbstractApiTransport
{
    private const HOST    = 'zhylonid.test';
    private const PACKAGE = 'zhylon/laravel-mailer';

    private string $key;

    private ?string $messageStream = null;


    public function __construct(
        string $key,
        ?HttpClientInterface $client = null,
        ?EventDispatcherInterface $dispatcher = null,
        ?LoggerInterface $logger = null
    ) {
        $this->key = $key;

        parent::__construct($client, $dispatcher, $logger);
    }

    protected function doSendApi(SentMessage $sentMessage, Email $email, Envelope $envelope): ResponseInterface
    {
        $response = $this->client->request('POST', 'https://'.$this->getEndpoint().'/email', [
            'headers' => [
                'Accept'                => 'application/json',
                'X-Zhylon-Package'      => self::PACKAGE,
                'X-Php-Version'         => PHP_VERSION,
                'X-Zhylon-Server-Token' => $this->key,
            ],
            'json'    => $this->getPayload($email, $envelope),
        ]);

        try {
            $statusCode = $response->getStatusCode();
            $result = $response->toArray(false);
        } catch (DecodingExceptionInterface) {
            throw new HttpTransportException('Unable to send an email: '.$response->getContent(false).sprintf(' (code %d).',
                    $statusCode), $response);
        } catch (TransportExceptionInterface $e) {
            throw new HttpTransportException('Could not reach the remote Zhylon server.', $response, 0, $e);
        }

        if (200 !== $statusCode) {
            throw new HttpTransportException('Unable to send an email: '.$result['Message'].sprintf(' (code %d).',
                    $result['ErrorCode']), $response);
        }

        $sentMessage->setMessageId($result['MessageID']);

        return $response;
    }

    private function getEndpoint(): null|string
    {
        return ($this->host ?: self::HOST);
    }

    private function getPayload(Email $email, Envelope $envelope): array
    {
        $payload = [
            'From'        => $envelope->getSender()->toString(),
            'To'          => implode(',', $this->stringifyAddresses($this->getRecipients($email, $envelope))),
            'Cc'          => implode(',', $this->stringifyAddresses($email->getCc())),
            'Bcc'         => implode(',', $this->stringifyAddresses($email->getBcc())),
            'ReplyTo'     => implode(',', $this->stringifyAddresses($email->getReplyTo())),
            'Subject'     => $email->getSubject(),
            'TextBody'    => $email->getTextBody(),
            'HtmlBody'    => $email->getHtmlBody(),
            'Attachments' => $this->getAttachments($email),
        ];

        $headersToBypass = ['from', 'to', 'cc', 'bcc', 'subject', 'content-type', 'sender', 'reply-to'];
        foreach ($email->getHeaders()->all() as $name => $header) {
            if (\in_array($name, $headersToBypass, true)) {
                continue;
            }

            if ($header instanceof TagHeader) {
                if (isset($payload['Tag'])) {
                    throw new TransportException('Zhylon only allows a single tag per email.');
                }

                $payload['Tag'] = $header->getValue();

                continue;
            }

            if ($header instanceof MetadataHeader) {
                $payload['Metadata'][$header->getKey()] = $header->getValue();

                continue;
            }

            if ($header instanceof MessageStreamHeader) {
                $payload['MessageStream'] = $header->getValue();

                continue;
            }

            $payload['Headers'][] = [
                'Name'  => $header->getName(),
                'Value' => $header->getBodyAsString(),
            ];
        }

        if (null !== $this->messageStream && !isset($payload['MessageStream'])) {
            $payload['MessageStream'] = $this->messageStream;
        }

        return $payload;
    }

    private function getAttachments(Email $email): array
    {
        $attachments = [];
        foreach ($email->getAttachments() as $attachment) {
            $headers = $attachment->getPreparedHeaders();
            $filename = $headers->getHeaderParameter('Content-Disposition', 'filename');
            $disposition = $headers->getHeaderBody('Content-Disposition');

            $att = [
                'Name'        => $filename,
                'Content'     => $attachment->bodyToString(),
                'ContentType' => $headers->get('Content-Type')->getBody(),
            ];

            if ('inline' === $disposition) {
                $att['ContentID'] = 'cid:'.$filename;
            }

            $attachments[] = $att;
        }

        return $attachments;
    }

    public function setMessageStream(string $messageStream): static
    {
        $this->messageStream = $messageStream;

        return $this;
    }

    public function __toString(): string
    {
        return sprintf('zhylonmail+api://%s', $this->getEndpoint()).($this->messageStream ? '?message_stream='.$this->messageStream : '');
    }
}
