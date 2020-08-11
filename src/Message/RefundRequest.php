<?php


namespace PsergiuDev\OmnipayMaib\Message;


use Omnipay\Common\Message\ResponseInterface;
use PsergiuDev\OmnipayMaib\Exceptions\EcommException;

class RefundRequest extends AbstractRequest
{

    public function getData()
    {
        return [
            'amount'         => $this->getAmountInteger(),
            'transaction_id' => $this->getTransactionId()
        ];
    }

    /**
     * @param array $data
     *
     * @return ResponseInterface|Response
     * @throws EcommException
     */
    public function sendData($data)
    {
        $response = $this->maib->revertTransaction($data['transaction_id'], $data['amount']);

        return $this->createResponse($response, null);
    }
}