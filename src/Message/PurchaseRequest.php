<?php


namespace PsergiuDev\OmnipayMaib\Message;


use PsergiuDev\OmnipayMaib\Exceptions\EcommException;

class PurchaseRequest extends AbstractRequest
{

    /**
     * @inheritDoc
     */
    public function getData()
    {
        return [
            'amount'      => $this->getAmountInteger(),
            'currency'    => $this->getCurrencyNumeric(),
            'client_ip'   => $this->getClientIp(),
            'description' => $this->getDescription(),
            'language'    => 'ru',
        ];
    }

    /**
     * @inheritDoc
     * @throws EcommException
     */
    public function sendData($data)
    {
        $response = $this->maib->registerSmsTransaction(
            $data['amount'],
            $data['currency'],
            $data['client_ip'],
            $data['description'],
            $data['language']
        );

        return $this->createResponse($response, null);
    }
}