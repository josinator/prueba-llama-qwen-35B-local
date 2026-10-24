<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

trait KernelBootTrait
{
    private static ?KernelInterface $kernel = null;

    private ?KernelBrowser $client = null;

    private function bootClient(): KernelBrowser
    {
        if (null === $this->client) {
            if (null === self::$kernel) {
                self::$kernel = new \App\Kernel('test', true);
                self::$kernel->boot();
            }
            $this->client = new KernelBrowser(self::$kernel);
        }
        return $this->client;
    }

    private function getContainer(): ContainerInterface
    {
        return self::$kernel->getContainer();
    }
}
