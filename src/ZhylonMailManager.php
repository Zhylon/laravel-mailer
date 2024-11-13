<?php

namespace Zhylon\Mailer;

use Illuminate\Mail\MailManager;
use Symfony\Component\Mailer\Transport\Dsn;

class ZhylonMailManager extends MailManager
{
    /**
     * Create an instance of the Zhylon Transport driver.
     */
    protected function createZhylonTransport(array $config): ZhylonApiTransport
    {
        $factory = new ZhylonTransportFactory(null, $this->getHttpClient($config));

        return $factory->create(new Dsn(
            scheme : 'zhylonmail+api',
            host   : 'default',
            user   : $config['token'] ?? $this->app['config']->get('services.zhylonmail.token'),
            options: isset($config['message_stream_id']) ? ['message_stream' => $config['message_stream_id']] : []
        ));
    }
}
