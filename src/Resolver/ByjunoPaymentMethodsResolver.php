<?php

declare(strict_types=1);

namespace Ij\SyliusByjunoPlugin\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Ij\SyliusByjunoPlugin\Api\Communicator\ByjunoCommunicator;
use Ij\SyliusByjunoPlugin\Api\Communicator\ByjunoResponse;
use Ij\SyliusByjunoPlugin\Api\DataHelper;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\PaymentInterface as BasePaymentInterface;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;

final class ByjunoPaymentMethodsResolver implements PaymentMethodsResolverInterface
{
    private PaymentMethodsResolverInterface $decoratedPaymentMethodsResolver;

    private string $firstPaymentMethodFactoryName;
    private $entityManager;

    public function __construct(
        EntityManagerInterface $em,
        PaymentMethodsResolverInterface $decoratedPaymentMethodsResolver,
        string $firstPaymentMethodFactoryName)
    {
        $this->decoratedPaymentMethodsResolver = $decoratedPaymentMethodsResolver;
        $this->firstPaymentMethodFactoryName = $firstPaymentMethodFactoryName;
        $this->entityManager = $em;
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
                if ($paymentMethod->getGatewayConfig()->getConfig()["cdp_enabled"] != "yes") {
                    return true;
                }
                $statusCDP = 0;
                /* @var $payment \App\Entity\Payment\Payment */
                $payment = $subject;
                $orderId = $payment->getOrder()->getId();
                if (empty($_SESSION["BYJUNO_CDP_ORDER"]) || $_SESSION["BYJUNO_CDP_ORDER"] != $orderId) {
                    $_SESSION["BYJUNO_CDP_COMPLETED"] = null;
                }
                $_SESSION["BYJUNO_CDP_ORDER"] = $orderId;
                if (isset($_SESSION["BYJUNO_CDP_COMPLETED"]) && $_SESSION["BYJUNO_CDP_COMPLETED"] != -1) {
                    $statusCDP = $_SESSION["BYJUNO_CDP_COMPLETED"];
                } else {
                    $requestCDP = DataHelper::CreateSyliusShopRequestOrderQuote($paymentMethod->getGatewayConfig()->getConfig(), $payment, "de", "", "", "", "", "CDP", "NO");
                    $statusLogCDP = "CDP request";
                    $responseCDP = new ByjunoResponse();
                    $communicator = new ByjunoCommunicator();
                    if ($paymentMethod->getGatewayConfig()->getConfig()["mode"] == 'live') {
                        $communicator->setServer('live');
                    } else {
                        $communicator->setServer('test');
                    }
                    $xmlCDP = $requestCDP->createRequest();
                    $responseOnCDP = $communicator->sendRequest($xmlCDP, (int)30);
                    if ($responseOnCDP) {
                        $responseCDP->setRawResponse($responseOnCDP);
                        $responseCDP->processResponse();
                        $statusCDP = (int)$responseCDP->getCustomerRequestStatus();
                        if (intval($statusCDP) > 15) {
                            $statusCDP = 0;
                        }
                        DataHelper::saveLog($this->entityManager, $requestCDP, $xmlCDP, $responseOnCDP, $statusCDP, $statusLogCDP);
                    } else {
                        DataHelper::saveLog($this->entityManager, $requestCDP, $xmlCDP, "empty response", "0", $statusLogCDP);
                    }
                    $_SESSION["BYJUNO_CDP_COMPLETED"] = $statusCDP;
                }

                if (DataHelper::byjunoIsStatusOk($statusCDP, $paymentMethod->getGatewayConfig()->getConfig()['accept_cdp'])) {
                    return true;
                } else {
                    return false;
                }
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
