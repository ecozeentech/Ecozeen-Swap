<?php

namespace Tests\Feature;

use Tests\TestCase;

class MailTestCommandTest extends TestCase
{
    public function test_it_sends_a_test_email_and_warns_when_still_using_the_log_driver(): void
    {
        config(['mail.default' => 'log']);

        $this->artisan('mail:test', ['email' => 'someone@example.com'])
            ->expectsOutputToContain("MAIL_MAILER is still set to 'log'")
            ->assertSuccessful();
    }

    public function test_it_reports_success_without_the_log_warning_on_a_real_mailer(): void
    {
        // The 'array' transport is a real (non-faked) Symfony Mailer
        // transport that stores messages in memory instead of a fake
        // no-op — Mail::fake() can't be used here because MailFake::raw()
        // deliberately doesn't record anything.
        config(['mail.default' => 'array']);

        $this->artisan('mail:test', ['email' => 'someone@example.com'])
            ->doesntExpectOutputToContain("MAIL_MAILER is still set to 'log'")
            ->assertSuccessful();

        $sent = app('mail.manager')->mailer('array')->getSymfonyTransport()->messages();
        $this->assertCount(1, $sent);
    }
}
