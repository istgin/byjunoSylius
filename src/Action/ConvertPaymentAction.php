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
use mysql_xdevapi\Exception;
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
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Sylius\Component\Locale\Context\CompositeLocaleContext;

final class ConvertPaymentAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public $config;
    public $entityManager;
    private $s2Status = -1;
    private $s3Status = -1;
    /* @var CompositeLocaleContext */
    private $localeProvider;

    public function __construct(EntityManagerInterface $em,
                                CompositeLocaleContext $lp)
    {
        $this->entityManager = $em;
        $this->localeProvider = $lp;
    }

    /**
     * {@inheritdoc}
     *
     * @param Convert $request
     * @throws \Exception
     */
    public function execute($request): void
    {
        if ($request instanceof GetStatus && $request->getModel() instanceof Payment) {
            $payment = $request->getModel();
            $details = $payment->getDetails();
            $locale = $this->localeProvider->getLocaleCode();
            $localeEx = explode("_", $locale);
            if (!empty($localeEx[0])) {
                $locale = $localeEx[0];
            }
            /** @var $payment SyliusPaymentInterface */
            if ($details['byjyno_status'] == 2) {
                $b2b = false;
                $billingAddress = $payment->getOrder()->getBillingAddress();
                $company = $billingAddress->getCompany();
                if (!empty($company)) {
                    $b2b = true;
                }
                $_SESSION["BYJUNO_CDP_COMPLETED"] = -1;
                if ($this->s2Status >= 0) {
                    $riskOwner = "";
                    if (DataHelper::byjunoIsStatusOk($this->s2Status, $this->config['accept_s2_ij'])) {
                        $riskOwner = "IJ";
                    } else if (DataHelper::byjunoIsStatusOk($this->s2Status, $this->config['accept_s2_client'])) {
                        $riskOwner = "CLIENT";
                    }
                    if ($riskOwner == "" || $this->s3Status == -1) {
                        $details['byjyno_status'] = 400;
                        $request->markFailed();
                    } else {
                        if (DataHelper::byjunoIsStatusOk($this->s3Status, $this->config['accept_s3'])) {
                            $details['byjyno_status'] = 200;
                            $request->markCaptured();
                        } else {
                            $details['byjyno_status'] = 400;
                            $request->markFailed();
                        }
                    }
                } else {
                    $statusLogS1 = "S1 request";
                    if ($b2b) {
                        $statusLogS1 = "S1 request for company";
                    }
                    $communicator = new ByjunoCommunicator();
                    $responseS2 = new ByjunoResponse();
                    $localeCodeOrder = $payment->getOrder()->getLocaleCode();
                    $orderId = $payment->getOrder()->getNumber();
                    $localeCodeOrderEx = explode("_", $locale);
                    if (!empty($localeCodeOrderEx[0])) {
                        $localeCodeOrder = $localeEx[0];
                    }
                    if (!empty($localeCodeOrder)) {
                        $locale = $localeCodeOrder;
                    }
                    $requestS1 = DataHelper::CreateSyliusShopRequestOrderQuote($this->config, $payment, $locale, "", "", "", "", "","NO");
                    if ($b2b) {
                        $xml = $requestS1->createRequestCompany();
                    } else {
                        $xml = $requestS1->createRequest();
                    }
                    if ($this->config["mode"] == 'live') {
                        $communicator->setServer('live');
                    } else {
                        $communicator->setServer('test');
                    }

                    $responseOnS1 = $communicator->sendRequest($xml, (int)30);
                    $this->s2Status = 0;
                    if ($responseOnS1) {
                        $responseS2->setRawResponse($responseOnS1);
                        $responseS2->processResponse();
                        $this->s2Status = (int)$responseS2->getCustomerRequestStatus();
                        DataHelper::saveLog($this->entityManager, $requestS1, $xml, $responseOnS1, $this->s2Status, $statusLogS1);
                        if (intval($this->s2Status) > 15) {
                            $this->s2Status = 0;
                        }
                    } else {
                        DataHelper::saveLog($this->entityManager, $requestS1, $xml, "empty response", "0", $statusLogS1);
                    }
                    $riskOwner = "";
                    if (DataHelper::byjunoIsStatusOk($this->s2Status, $this->config['accept_s2_ij'])) {
                        $riskOwner = "IJ";
                    } else if (DataHelper::byjunoIsStatusOk($this->s2Status, $this->config['accept_s2_client'])) {
                        $riskOwner = "CLIENT";
                    }
                    if ($riskOwner == "") {
                        $details['byjyno_status'] = 400;
                        $request->markFailed();
                    } else {
                        //S3
                        $statusLogS3 = "S3 request";
                        if ($b2b) {
                            $statusLogS3 = "S3 request for company";
                        }
                        $responseS3 = new ByjunoResponse();
                        $requestS3 = DataHelper::CreateSyliusShopRequestOrderQuote($this->config, $payment, $locale, $riskOwner, $orderId, "", $responseS2->getTransactionNumber(), "","YES");
                        if ($b2b) {
                            $xmlS3 = $requestS3->createRequestCompany();
                        } else {
                            $xmlS3 = $requestS3->createRequest();
                        }
                        $responseOnS3 = $communicator->sendRequest($xmlS3, (int)30);
                        $this->s3Status = 0;
                        if ($responseOnS3) {
                            $responseS3->setRawResponse($responseOnS3);
                            $responseS3->processResponse();
                            $this->s3Status = (int)$responseS3->getCustomerRequestStatus();
                            DataHelper::saveLog($this->entityManager, $requestS3, $xmlS3, $responseOnS3, $this->s3Status, $statusLogS3);
                            if (intval($this->s3Status) > 15) {
                                $this->s3Status = 0;
                            }
                        } else {
                            DataHelper::saveLog($this->entityManager, $requestS3, $xmlS3, "empty response", "0", $statusLogS3);
                        }

                        if (DataHelper::byjunoIsStatusOk($this->s3Status, $this->config['accept_s3'])) {
                            $details['byjyno_status'] = 200;
                            $request->markCaptured();
                        } else {
                            $details['byjyno_status'] = 400;
                            $request->markFailed();
                        }
                    }
                }
            }
        } else {
            //echo 'ConvertPaymentAction<br>';
            RequestNotSupportedException::assertSupports($this, $request);
            /** @var PaymentInterface $payment */
            $payment = $request->getSource();
            $details = ArrayObject::ensureArrayObject($payment->getDetails());
            if (!empty($details['byjyno_status']) && $details['byjyno_status'] == 2) {
                throw new \Exception('Error: byjyno_status == 2');
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
