<?php


namespace PsergiuDev\OmnipayMaib\Message;


use Omnipay\Common\Message\ResponseInterface;
use PsergiuDev\OmnipayMaib\Exceptions\EcommException;

class CompletePurchaseRequest extends AbstractRequest
{

    public function getData()
    {
        return [
            'client_ip'      => $this->getClientIp(),
            'transaction_id' => $this->getTransactionId()
        ];
    }

    /**
     * @param mixed $data
     *
     * @return ResponseInterface|Response
     * @throws EcommException
     */
    public function sendData($data)
    {
        $response = $this->maib->getTransactionResult($data['transaction_id'], $data['client_ip']);

        return $this->createResponse($response, null);
    }
}