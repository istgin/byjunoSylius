<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Ij\SyliusByjunoPlugin\Resolver;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use mysql_xdevapi\Exception;
use Sylius\Component\Core\Inventory\Operator\OrderInventoryOperatorInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Payment\Model\PaymentInterface as BasePaymentInterface;

final class OrderInventoryOperator implements OrderInventoryOperatorInterface
{
    private OrderInventoryOperatorInterface $decoratedOperator;

    public function __construct(
        OrderInventoryOperatorInterface $decoratedOperator
    ) {
        $this->decoratedOperator = $decoratedOperator;
    }

    /**
     * @throws OptimisticLockException
     */
    public function cancel(OrderInterface $order): void
    {

        var_dump(get_class($order));
        $o = $order;
        if ($o->getState() === \Sylius\Component\Order\Model\OrderInterface::STATE_CANCELLED) {
            $isByjunoUsed = false;
            $o->getPayments()->filter(function (BasePaymentInterface $payment) : void {
                /** @var \Sylius\Component\Core\Model\PaymentMethodInterface $method */
                $method = $payment->getMethod();
                //var_dump($method->getGatewayConfig()->getFactoryName());
                if ($method != null && $method->getGatewayConfig()->getFactoryName() == 'byjuno') {
                    $isByjunoUsed = true;
                }
            });
            if ($isByjunoUsed) {

            }
        }
      //  throw new Exception("RRRR");
      //  exit('aaarrrxxx');
        $this->decoratedOperator->cancel($order);
    }

    /**
     * @throws OptimisticLockException
     */
    public function hold(OrderInterface $order): void
    {
        $this->decoratedOperator->hold($order);
    }

    /**
     * @throws OptimisticLockException
     */
    public function sell(OrderInterface $order): void
    {
        $this->decoratedOperator->sell($order);
    }
}
