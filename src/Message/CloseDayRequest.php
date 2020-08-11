<?php


namespace PsergiuDev\OmnipayMaib\Message;


use Omnipay\Common\Message\ResponseInterface;
use PsergiuDev\OmnipayMaib\Exceptions\EcommException;

class CloseDayRequest extends AbstractRequest
{

    public function getData()
    {
        return [];
    }

    /**
     * @param mixed $data
     *
     * @return ResponseInterface|Response
     * @throws EcommException
     */
    public function sendData($data)
    {
        $response = $this->maib->closeDay();

        return $this->createResponse($response);
    }
}