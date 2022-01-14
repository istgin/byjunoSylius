<?php

declare(strict_types=1);

namespace Ij\SyliusByjunoPlugin\Resolver;

use Sylius\Component\Core\Model\OrderInterface as CoreOrderInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Payment\Model\PaymentInterface as BasePaymentInterface;
use Sylius\Component\Payment\Model\PaymentMethodInterface;
use Webmozart\Assert\Assert;

final class AfterCheckoutOrderPaymentProcessor implements OrderProcessorInterface
{
    private OrderProcessorInterface $baseAfterCheckoutOrderPaymentProcessor;

    public function __construct(OrderProcessorInterface $baseAfterCheckoutOrderPaymentProcessor)
    {
        $this->baseAfterCheckoutOrderPaymentProcessor = $baseAfterCheckoutOrderPaymentProcessor;
    }

    /** @var CoreOrderInterface */
    public function process(OrderInterface $order): void
    {
        debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        exit('rrrr');
        Assert::isInstanceOf($order, CoreOrderInterface::class);
        /** @var $o \App\Entity\Order\Order $o */
        $o = $order;
        if ($order->getState() === OrderInterface::STATE_CANCELLED) {
            $isByjunoUsed = false;
            $payment = $o->getPayments()->filter(function (BasePaymentInterface $payment) : void {
                /** @var \Sylius\Component\Core\Model\PaymentMethodInterface $method */
                $method = $payment->getMethod();
                //var_dump($method->getGatewayConfig()->getFactoryName());
                if ($method != null && $method->getGatewayConfig()->getFactoryName() == 'byjuno') {
                    $isByjunoUsed = true;
                }
            });
           // var_dump($isByjunoUsed);
            if ($isByjunoUsed) {
            }
        }
        $this->baseAfterCheckoutOrderPaymentProcessor->process($order);
    }
}
