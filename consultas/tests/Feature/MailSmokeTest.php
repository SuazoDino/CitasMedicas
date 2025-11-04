<?php

namespace Tests\Feature;

use Tests\TestCase;

class MailSmokeTest extends TestCase
{
    public function test_mail_command_renders_smoke_template(): void
    {
        $this->artisan('mail:send', [
            'view' => 'emails.system.smoke',
            '--to' => 'smoke@example.test',
            '--pretend' => true,
        ])->assertExitCode(0);
    }
}