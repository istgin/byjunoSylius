<?php


declare(strict_types=1);

namespace Ij\SyliusByjunoPlugin\Action;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Ij\SyliusByjunoPlugin\Action\Api\ObtainToken;
use Ij\SyliusByjunoPlugin\Api\DataHelper;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Stripe\Request\Api\CreateCharge;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Payum\Core\Request\Capture;
use Sylius\PayPalPlugin\Payum\Action\StatusAction;

class CaptureAction implements ActionInterface, GatewayAwareInterface
{
    /*
    private $client;

    private $api;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var SyliusPaymentInterface $payment * /
        $payment = $request->getModel();
        try {
            $xml = DataHelper::CreateSyliusShopRequestOrderQuote($payment, "", "", "", "", "");
            var_dump($xml);
            exit();
        } catch (RequestException $exception) {
            //$response = $exception->getResponse();
            //$payment->setDetails(['status' => $response->getStatusCode()]);
            $payment->setDetails(['status' => 400/*$response->getStatusCode()* /]);
        } finally {
            $payment->setDetails(['status' => 200/*$response->getStatusCode()* /]);
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
    */

    use GatewayAwareTrait;

    public function execute($request)
    {

        RequestNotSupportedException::assertSupports($this, $request);
        $model = ArrayObject::ensureArrayObject($request->getModel());
        //echo '<br>CaptureAction: '.$model['byjyno_status']."<br><br>";
        if (empty($model["byjyno_status"]) || $model['byjyno_status'] == 1) {
            //$obtainToken = new ObtainToken($request->getToken());
            //$obtainToken->setModel($model);
            //$this->gateway->execute($obtainToken);
            // go payment directly
            $model['byjyno_status'] = 2;
        } else if (!empty($model["byjyno_status"]) && $model['byjyno_status'] == 2) {
            $model['byjyno_status'] = 200;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request): bool
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }
}
