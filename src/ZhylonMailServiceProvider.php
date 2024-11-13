<?php

namespace Zhylon\Mailer;

use Illuminate\Mail\MailServiceProvider;

class ZhylonMailServiceProvider extends MailServiceProvider
{
    /**
     * Register the Illuminate mailer instance.
     */
    protected function registerIlluminateMailer(): void
    {
        $this->app->singleton('mail.manager', function($app) {
            return new ZhylonMailManager($app);
        });
    }
}
