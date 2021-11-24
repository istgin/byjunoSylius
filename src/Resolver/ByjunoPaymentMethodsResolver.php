<?php

declare(strict_types=1);

namespace Ij\SyliusByjunoPlugin\Resolver;

use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\PaymentInterface as BasePaymentInterface;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;

final class ByjunoPaymentMethodsResolver implements PaymentMethodsResolverInterface
{
    private PaymentMethodsResolverInterface $decoratedPaymentMethodsResolver;

    private string $firstPaymentMethodFactoryName;

    public function __construct(PaymentMethodsResolverInterface $decoratedPaymentMethodsResolver, string $firstPaymentMethodFactoryName)
    {
        $this->decoratedPaymentMethodsResolver = $decoratedPaymentMethodsResolver;
        $this->firstPaymentMethodFactoryName = $firstPaymentMethodFactoryName;
    }

/*
    public function getSupportedMethods(BasePaymentInterface $payment): array
    {
        return $this->sortPayments(
            $this->decoratedPaymentMethodsResolver->getSupportedMethods($payment),
            $this->firstPaymentMethodFactoryName
        );
    }
*/
    public function getSupportedMethods(BasePaymentInterface $subject): array
    {
        //TODO CDP
        return array_filter($this->decoratedPaymentMethodsResolver->getSupportedMethods($subject), function (PaymentMethodInterface $paymentMethod) use ($subject) {
            if ($paymentMethod->getGatewayConfig()->getFactoryName() == 'byjuno') {
                $minAmount = (float)$paymentMethod->getGatewayConfig()->getConfig()["min_amount"] * 100;
                $maxAmount = (float)$paymentMethod->getGatewayConfig()->getConfig()["max_amount"] * 100;
                $orderAmount = $subject->getAmount();
                if ($minAmount > $orderAmount || $maxAmount < $orderAmount) {
                    return false;
                }
                return true;
            }
            return true;
        });
    }

    public function supports(BasePaymentInterface $payment): bool
    {
        return $this->decoratedPaymentMethodsResolver->supports($payment);
    }

    /**
     * @return PaymentMethodInterface[]
     */
    private function sortPayments(array $payments, string $firstPaymentFactoryName): array
    {
        /** @var PaymentMethodInterface[] $sortedPayments */
        $sortedPayments = [];

        /** @var PaymentMethodInterface $payment */
        foreach ($payments as $payment) {
            $gatewayConfig = $payment->getGatewayConfig();

            if ($gatewayConfig !== null && $gatewayConfig->getFactoryName() === $firstPaymentFactoryName) {
                array_unshift($sortedPayments, $payment);
            } else {
                $sortedPayments[] = $payment;
            }
        }

        return $sortedPayments;
    }
}
