<?php
namespace Ij\SyliusByjunoPlugin\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\ObtainCreditCard;
use Payum\Core\Request\RenderTemplate;
use Payum\Stripe\Constants;
use Payum\Stripe\Request\Api\CreateCharge;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;

class ObtainTokenForCreditCardAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use ApiAwareTrait {
        setApi as _setApi;
    }
    use GatewayAwareTrait;

    protected $templateName;

    public function __construct($templateName)
    {
        $this->templateName = $templateName;
    }

    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
      //  var_dump($api);
      //  $this->_setApi($api);
    }
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
       // echo 'ObtainTokenForCreditCardAction<br>';
        /** @var $request ObtainToken */
        RequestNotSupportedException::assertSupports($this, $request);
        $payment = $request->getModel();

        $getHttpRequest = new GetHttpRequest();
        $this->gateway->execute($getHttpRequest);
        if ($getHttpRequest->method == 'POST') {
            $payment['dob'] = $getHttpRequest->request["dob"];
            $payment['byjyno_status'] = 2;
            return;
        }

        $this->gateway->execute($renderTemplate = new RenderTemplate($this->templateName, array(
         //   'model' => $model,
            'publishable_key' => "XXXX",
            'actionUrl' => $request->getToken() ? $request->getToken()->getTargetUrl() : null,
        )));

        throw new HttpResponse($renderTemplate->getResult());
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
      //  echo "XXX===  ".get_class($request)." ---XXX";
        //$model = $request->getModel();
       // echo get_class($model);
        return
            $request instanceof ObtainToken &&
            $request->getModel() instanceof ArrayObject
        ;
    }
}
