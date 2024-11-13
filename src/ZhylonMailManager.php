<?php

namespace Zhylon\Mailer;

use Illuminate\Mail\MailManager;
use Symfony\Component\Mailer\Transport\Dsn;

class ZhylonMailManager extends MailManager
{
    /**
     * Create an instance of the Zhylon Transport driver.
     */
    protected function createZhylonMailTransport(array $config): ZhylonApiTransport
    {
        $factory = new ZhylonTransportFactory(null, $this->getHttpClient($config));

        return $factory->create(new Dsn(
            scheme : 'zhylon-mail+api',
            host   : 'default',
            user   : $config['token'] ?? $this->app['config']->get('services.zhylon-mail.token'),
            options: isset($config['message_stream_id']) ? ['message_stream' => $config['message_stream_id']] : []
        ));
    }
}
