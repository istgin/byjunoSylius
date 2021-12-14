<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace Ij\SyliusByjunoPlugin\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetStatusInterface;
use Payum\Stripe\Constants;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;

final class StatusAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        //echo 'StatusAction<br>';
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        if (false == $model['byjyno_status'] || $model['byjyno_status'] == 1) {
         //   echo '1';
            $request->markNew();
            return;
        }
        if ($model['byjyno_status'] == 2) {
            $request->markNew();
            return;
        }

        if ($model['byjyno_status'] == 400) {
            //echo '3';
            $request->markFailed();
            return;
        }

        if ($model['byjyno_status'] == 200) {
          //  echo '4';
            $request->markAuthorized();
            return;
        }
      //  exit('aaa');

      //  echo '5';
        $request->markUnknown();
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof GetStatusInterface &&
            $request->getModel() instanceof ArrayAccess
            ;
    }
}
