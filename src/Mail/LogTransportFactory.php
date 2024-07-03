<?php declare(strict_types=1);

namespace App\Mail;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportFactoryInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;

final class LogTransportFactory implements TransportFactoryInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly LoggerInterface $logger
    ) {
    }

    public function create(Dsn $dsn): TransportInterface
    {
        return new LogTransport($this->dispatcher, $this->logger);
    }

    public function supports(Dsn $dsn): bool
    {
        return $dsn->getScheme() === 'log';
    }
}