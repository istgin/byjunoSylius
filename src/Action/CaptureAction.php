<?php


declare(strict_types=1);

namespace Ij\SyliusByjunoPlugin\Action;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Ij\SyliusByjunoPlugin\Api\DataHelper;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Payum\Core\Request\Capture;
use Sylius\PayPalPlugin\Payum\Action\StatusAction;

final class CaptureAction implements ActionInterface, ApiAwareInterface
{
    /** @var Client */
    private $client;

    private $api;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var SyliusPaymentInterface $payment */
        $payment = $request->getModel();
        try {
            $xml = DataHelper::CreateSyliusShopRequestOrderQuote($payment, "", "", "", "", "");
            var_dump($xml);
            exit();
        } catch (RequestException $exception) {
            //$response = $exception->getResponse();
            //$payment->setDetails(['status' => $response->getStatusCode()]);
            $payment->setDetails(['status' => 400/*$response->getStatusCode()*/]);
        } finally {
            $payment->setDetails(['status' => 200/*$response->getStatusCode()*/]);
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof SyliusPaymentInterface
            ;
    }

    public function setApi($api): void
    {
        if (false === is_array($api)) {
            throw new UnsupportedApiException('Not supported. Expected to be set as array.');
        }
        $this->api = $api;
    }
}
