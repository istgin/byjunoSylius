<?php

declare(strict_types=1);

namespace Ij\SyliusByjunoPlugin\Resolver;

use Sylius\Component\Core\Model\OrderInterface as CoreOrderInterface;
use Sylius\Component\Core\Updater\UnpaidOrdersStateUpdater;
use Sylius\Component\Core\Updater\UnpaidOrdersStateUpdaterInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Payment\Model\PaymentInterface as BasePaymentInterface;
use Sylius\Component\Payment\Model\PaymentMethodInterface;
use Webmozart\Assert\Assert;

final class AfterCheckoutOrderPaymentProcessor2 implements UnpaidOrdersStateUpdaterInterface
{
    private UnpaidOrdersStateUpdater $baseAfterCheckoutOrderPaymentProcessor;

    public function __construct(UnpaidOrdersStateUpdater $baseAfterCheckoutOrderPaymentProcessor)
    {
        $this->baseAfterCheckoutOrderPaymentProcessor = $baseAfterCheckoutOrderPaymentProcessor;
    }

    /** @var CoreOrderInterface */
    public function cancel(): void
    {
        exit('bbb');
        $this->baseAfterCheckoutOrderPaymentProcessor->process($order);
    }
}
