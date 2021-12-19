<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace Ij\SyliusByjunoPlugin\Action;

use App\Entity\Payment\Payment;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Ij\SyliusByjunoPlugin\Api\Communicator\ByjunoCommunicator;
use Ij\SyliusByjunoPlugin\Api\Communicator\ByjunoResponse;
use Ij\SyliusByjunoPlugin\Api\DataHelper;
use Ij\SyliusByjunoPlugin\Entity\ByjunoLog;
use Ij\SyliusByjunoPlugin\Repository\ByjunoLogRepository;
use Ij\SyliusByjunoPlugin\Repository\ByjunoLogTrait;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Sylius\Bundle\PayumBundle\Request\GetStatus;

final class ConvertPaymentAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public $config;
    public $entityManager;

    public function __construct(EntityManagerInterface $em)
    {
        $this->entityManager = $em;
    }

    /**
     * {@inheritdoc}
     *
     * @param Convert $request
     */
    public function execute($request): void
    {
        if ($request instanceof GetStatus && $request->getModel() instanceof Payment) {
           // echo 'aaa';
            $payment = $request->getModel();
            $details = $payment->getDetails();
            /** @var $payment Payment*/
            if ($details['byjyno_status'] == 2) {
                $statusLog = "CDP request (S1)";
                $communicator = new ByjunoCommunicator();
                $responseS2 = new ByjunoResponse();
                $requestS2 = DataHelper::CreateSyliusShopRequestOrderQuote($this->config, $payment, "de");
                $xml = $requestS2->createRequest();
                if ($this->config["mode"] == 'live') {
                    $communicator->setServer('live');
                } else {
                    $communicator->setServer('test');
                }

                $response = $communicator->sendRequest($xml, (int)30);
                $statusS2 = 0;
                if ($response) {
                    $responseS2->setRawResponse($response);
                    $responseS2->processResponse();
                    $statusS2 = (int)$responseS2->getCustomerRequestStatus();
                    DataHelper::saveLog($this->entityManager, $requestS2, $xml, $response, $statusS2, $statusLog);
                    if (intval($statusS2) > 15) {
                        $statusS2 = 0;
                    }
                } else {
                    DataHelper::saveLog($this->entityManager, $requestS2, $xml, "empty response", "0", $statusLog);
                }
                if (DataHelper::byjunoIsStatusOk($statusS2, $this->config['accept_s2'])) {
                    $details['byjyno_status'] = 200;
                    $request->markCaptured();
                } else {
                    $details['byjyno_status'] = 400;
                    $request->markFailed();
                }
            }
        } else {
            //echo 'ConvertPaymentAction<br>';
            RequestNotSupportedException::assertSupports($this, $request);
            /** @var PaymentInterface $payment */
            $payment = $request->getSource();
            $details = ArrayObject::ensureArrayObject($payment->getDetails());
            if (!empty($details['byjyno_status']) && $details['byjyno_status'] == 2) {
                exit('aaa');
                // S1 & S2 goes here
                //  $details['byjyno_status'] = 200;
                //   $request->setResult((array) $details);
            } else {
                $details['totalAmount'] = $payment->getTotalAmount();
                $details['currencyCode'] = $payment->getCurrencyCode();
                $details['extOrderId'] = uniqid((string)$payment->getNumber(), true);
                $details['description'] = $payment->getDescription();
                $details['client_email'] = $payment->getClientEmail();
                $details['client_id'] = $payment->getClientId();
                $details['customerIp'] = $this->getClientIp();
                $details['byjyno_status'] = 1;
                $request->setResult((array)$details);
            }
        }
    }

    public function setApi($config): void
    {
        if (false === is_array($config)) {
            throw new UnsupportedApiException('Not supported. Expected to be set as array.');
        }
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        if ($request instanceof Convert &&
                $request->getSource() instanceof PaymentInterface &&
                $request->getTo() == 'array')
        {
            return true;
        }
        if (
                $request instanceof GetStatus && $request->getModel() instanceof Payment
            ) {
            $payment = $request->getModel();
            $details = $payment->getDetails();
            if (!empty($details['byjyno_status']) && $details['byjyno_status'] == 2) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    private function getClientIp(): ?string
    {
        return $_SERVER['REMOTE_ADDR'] ?? null;
    }
}
