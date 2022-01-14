<?php

declare(strict_types=1);

namespace Ij\SyliusByjunoPlugin\Resolver;

use Sylius\Bundle\ShopBundle\EmailManager\OrderEmailManagerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Webmozart\Assert\Assert;

final class OrderCompleteListener
{
    /** @var OrderEmailManagerInterface */
  //  private $orderEmailManager;

    public function __construct()
    {
     //   exit('bbb');
      //  $this->orderEmailManager = $orderEmailManager;
    }

    public function sendConfirmationEmail(GenericEvent $event): void
    {
        /** @var $order \App\Entity\Order\Order */
        $order = $event->getSubject();
        var_dump($order->getState());
        var_dump(get_class($order));
        exit('aaa');

      //  $this->orderEmailManager->sendConfirmationEmail($order);
    }
}
