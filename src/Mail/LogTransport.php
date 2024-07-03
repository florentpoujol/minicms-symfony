<?php declare(strict_types=1);

namespace App\Mail;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

final class LogTransport extends AbstractTransport
{
    protected function doSend(SentMessage $message): void
    {
        $this->getLogger()->info('Sending mail ' . $message->toString());
    }

    public function __toString(): string
    {
        return 'log://';
    }
}