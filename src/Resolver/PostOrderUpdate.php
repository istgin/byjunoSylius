<?php

declare(strict_types=1);

namespace Ij\SyliusByjunoPlugin\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Ij\SyliusByjunoPlugin\Api\Communicator\ByjunoCommunicator;
use Ij\SyliusByjunoPlugin\Api\Communicator\ByjunoS4Response;
use Ij\SyliusByjunoPlugin\Api\DataHelper;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Payment\Model\PaymentInterface as BasePaymentInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final class PostOrderUpdate
{
    private $isByjunoUsed;
    private PaymentMethodInterface $method;
    public EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $em)
    {
        $this->entityManager = $em;
    }

    public function onCancel(GenericEvent $event): void
    {
        /** @var $order \App\Entity\Order\Order */
        $order = $event->getSubject();
        if (PreOrderUpdate::$orderState != ""
            && $order->getState() === OrderInterface::STATE_CANCELLED
            && PreOrderUpdate::$orderState != $order->getState()) {
            $this->isByjunoUsed = false;
            $order->getPayments()->filter(function (BasePaymentInterface $payment): void {
                /** @var \Sylius\Component\Core\Model\PaymentMethodInterface $method */
                $method = $payment->getMethod();
                if ($method != null && $method->getGatewayConfig()->getFactoryName() === 'byjuno') {
                    $this->method = $method;
                    $this->isByjunoUsed = true;
                }
            });
            if ($this->isByjunoUsed) {
                $paymentMethod = $this->method;
                $request = DataHelper::CreateShopRequestS5Cancel($paymentMethod->getGatewayConfig()->getConfig(),
                    $order->getTotal(),
                    $order->getCurrencyCode(),
                    $order->getNumber(),
                    $order->getCustomer()->getId(),
                    date("Y-m-d"));
                $statusLog = "S5 Cancel request";
                $xml = $request->createRequest();
                $byjunoCommunicator = new ByjunoCommunicator();
                if ($paymentMethod->getGatewayConfig()->getConfig()["mode"] == 'live') {
                    $byjunoCommunicator->setServer('live');
                } else {
                    $byjunoCommunicator->setServer('test');
                }
                $response = $byjunoCommunicator->sendS4Request($xml, $paymentMethod->getGatewayConfig()->getConfig()["timeout"]);
                if (isset($response)) {
                    $byjunoResponse = new ByjunoS4Response();
                    $byjunoResponse->setRawResponse($response);
                    $byjunoResponse->processResponse();
                    $statusCDP = $byjunoResponse->getProcessingInfoClassification();
                    DataHelper::saveS5Log($this->entityManager, $request, $xml, $response, $statusCDP, $statusLog, "-", "-");
                } else {
                    DataHelper::saveS5Log($this->entityManager, $request, $xml, "Empty response", 0, $statusLog, "-", "-");
                }
            }
        }
    }
}
