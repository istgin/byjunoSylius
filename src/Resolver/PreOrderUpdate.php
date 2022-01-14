<?php

declare(strict_types=1);

namespace Ij\SyliusByjunoPlugin\Resolver;

use Symfony\Component\EventDispatcher\GenericEvent;

final class PreOrderUpdate
{
    public static string $orderState = "";

    public function onCancel(GenericEvent $event): void
    {
        /** @var $order \App\Entity\Order\Order */
        $order = $event->getSubject();
        self::$orderState = $order->getState();
    }
}
