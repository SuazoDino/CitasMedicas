<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SmsService
{
    /**
     * @var array<int, array<string, mixed>>
     */
    protected array $sentMessages = [];

    public function send(string $to, string $message, array $context = []): void
    {
        if (config('sms.pretend')) {
            $context['pretend'] = true;
        }

        $driver = config('sms.driver', 'log');

        match ($driver) {
            'log' => $this->sendViaLog($to, $message, $context),
            'array' => $this->storeInArray($to, $message, $context),
            'null', null => null,
            default => throw new RuntimeException("Unsupported SMS driver [{$driver}]."),
        };
    }

    public function sentMessages(): array
    {
        return $this->sentMessages;
    }

    public function reset(): void
    {
        $this->sentMessages = [];
    }

    protected function sendViaLog(string $to, string $message, array $context = []): void
    {
        $payload = array_merge($context, [
            'to' => $to,
            'from' => config('sms.from'),
        ]);

        Log::channel(config('sms.log_channel', config('logging.default')))
            ->info('[SMS] '.$message, $payload);

        $this->storeInArray($to, $message, $context);
    }

    protected function storeInArray(string $to, string $message, array $context = []): void
    {
        $this->sentMessages[] = [
            'to' => $to,
            'message' => $message,
            'context' => Arr::wrap($context),
        ];
    }
}