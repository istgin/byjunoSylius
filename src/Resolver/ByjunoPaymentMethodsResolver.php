<?php

declare(strict_types=1);

namespace Ij\SyliusByjunoPlugin\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Ij\SyliusByjunoPlugin\Api\Communicator\ByjunoCommunicator;
use Ij\SyliusByjunoPlugin\Api\Communicator\ByjunoResponse;
use Ij\SyliusByjunoPlugin\Api\DataHelper;
use Sylius\Behat\Context\Ui\Shop\LocaleContext;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Locale\Context\CompositeLocaleContext;
use Sylius\Component\Payment\Model\PaymentInterface as BasePaymentInterface;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;

final class ByjunoPaymentMethodsResolver implements PaymentMethodsResolverInterface
{
    private PaymentMethodsResolverInterface $decoratedPaymentMethodsResolver;

    private string $firstPaymentMethodFactoryName;
    private $entityManager;
    /* @var CompositeLocaleContext */
    private $localeProvider;

    public function __construct(
        CompositeLocaleContext $lp,
        EntityManagerInterface $em,
        PaymentMethodsResolverInterface $decoratedPaymentMethodsResolver,
        string $firstPaymentMethodFactoryName)
    {
        $this->localeProvider = $lp;
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
        return array_filter($this->decoratedPaymentMethodsResolver->getSupportedMethods($subject), function (PaymentMethodInterface $paymentMethod) use ($subject) {
            if ($paymentMethod->getGatewayConfig()->getFactoryName() == 'byjuno') {
                $locale = $this->localeProvider->getLocaleCode();
                $localeEx = explode("_", $locale);
                if (!empty($localeEx[0])) {
                    $locale = $localeEx[0];
                }
                $minAmount = (float)$paymentMethod->getGatewayConfig()->getConfig()["min_amount"] * 100;
                $maxAmount = (float)$paymentMethod->getGatewayConfig()->getConfig()["max_amount"] * 100;
                $allowB2C = $paymentMethod->getGatewayConfig()->getConfig()["b2c_allow"];
                $allowB2B = $paymentMethod->getGatewayConfig()->getConfig()["b2b_allow"];
                $orderAmount = $subject->getAmount();
                if ($minAmount > $orderAmount || $maxAmount < $orderAmount) {
                    return false;
                }
                /* @var $payment SyliusPaymentInterface */
                $payment = $subject;
                $orderId = $payment->getOrder()->getId();
                $localeCodeOrderEx = explode("_", $locale);
                if (!empty($localeCodeOrderEx[0])) {
                    $localeCodeOrder = $localeEx[0];
                }
                if (!empty($localeCodeOrder)) {
                    $locale = $localeCodeOrder;
                }
                $billingAddress = $payment->getOrder()->getBillingAddress();
                $company = $billingAddress->getCompany();
                $b2b = false;
                if (!empty($company)) {
                    $b2b = true;
                }
                if (!$b2b && $allowB2C == "no") {
                    return false;
                }
                if ($b2b && $allowB2B == "no") {
                    return false;
                }
                if ($paymentMethod->getGatewayConfig()->getConfig()["cdp_enabled"] != "yes") {
                    return true;
                }
                $statusCDP = 0;
                if (empty($_SESSION["BYJUNO_CDP_ORDER"]) || $_SESSION["BYJUNO_CDP_ORDER"] != $orderId) {
                    $_SESSION["BYJUNO_CDP_COMPLETED"] = null;
                }
                $_SESSION["BYJUNO_CDP_ORDER"] = $orderId;
                if (isset($_SESSION["BYJUNO_CDP_COMPLETED"]) && $_SESSION["BYJUNO_CDP_COMPLETED"] != -1) {
                    $statusCDP = $_SESSION["BYJUNO_CDP_COMPLETED"];
                } else {
                    $requestCDP = DataHelper::CreateSyliusShopRequestOrderQuote($paymentMethod->getGatewayConfig()->getConfig(), $payment, $locale, "", "", "", "", "CDP", "NO");
                    $statusLogCDP = "CDP request";
                    if ($b2b) {
                        $statusLogCDP = "CDP request for company";
                    }
                    $responseCDP = new ByjunoResponse();
                    $communicator = new ByjunoCommunicator();
                    if ($paymentMethod->getGatewayConfig()->getConfig()["mode"] == 'live') {
                        $communicator->setServer('live');
                    } else {
                        $communicator->setServer('test');
                    }
                    if ($b2b) {
                        $xmlCDP = $requestCDP->createRequestCompany();
                    } else {
                        $xmlCDP = $requestCDP->createRequest();
                    }
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
