<?php

namespace PsergiuDev\OmnipayMaib;


use PsergiuDev\OmnipayMaib\Exceptions\EcommException;

class Ecomm
{

    private $merchantUrl;
    private $clientUrl;
    private $certificatePem;
    private $certificatePass;
    private $connectTimeout = 60;

    public function setMerchantCertificate($value): void
    {
        $this->certificatePem = $value;
    }

    public function setMerchantCertificatePassword($value): void
    {
        $this->certificatePass = $value;
    }

    /**
     * @param $transId
     * @return string
     */
    public function getRedirectUrl($transId): string
    {
        return $this->clientUrl . '?trans_id=' . urlencode($transId);
    }

    /**
     * @param $params
     *
     * @return array
     * @throws EcommException
     */
    private function sendRequest($params): array
    {
        $ch = curl_init();

        if ($this->certificatePem){

            $tempPemFile = tmpfile();
            fwrite($tempPemFile, $this->certificatePem);
            $tempPemPath = stream_get_meta_data($tempPemFile);
            $tempPemPath = $tempPemPath['uri'];

            curl_setopt($ch, CURLOPT_SSLCERT, $tempPemPath);
        }
        if ($this->certificatePass){
            curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $this->certificatePass);
        }

        curl_setopt($ch, CURLOPT_URL, $this->merchantUrl);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_NOPROGRESS, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);

        $result = trim(curl_exec($ch));

        if ($this->certificatePem && isset($tempPemFile)) {
            fclose($tempPemFile);
        }

        if ($error = curl_error($ch)) {
            curl_close($ch);
            throw new EcommException($error);
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (200 !== $http_code) {
            curl_close($ch);
            throw new EcommException('Error: ' . $http_code, $http_code);
        }

        curl_close($ch);

        $response = [];

        if (strpos($result, 'error') === 0) {
            $error = substr($result, 6);
            throw new EcommException($error);
        }

        foreach (explode("\n", $result) as $nvp) {
            [$key, $value] = explode(': ', $nvp);
            $response[$key] = $value;
        }

        return $response;
    }

    /**
     * Registering transactions
     *
     * @param float  $amount
     * @param        $currency
     * @param        $clientIpAddr
     * @param string $description
     * @param string $language
     * start SMS transaction. This is simplest form that charges amount to customer instantly.
     * TRANSACTION_ID - transaction identifier (28 characters in base64 encoding)
     * error          - in case of an error
     *
     * @return array
     * @throws EcommException
     */
    public function registerSmsTransaction($amount, $currency, $clientIpAddr, $description = '', $language = 'ru'): array
    {
        return $this->sendRequest([
            'command'        => 'v',
            'amount'         => (string)$amount,
            'msg_type'       => 'SMS',
            'currency'       => (string)$currency,
            'client_ip_addr' => $clientIpAddr,
            'description'    => $description,
            'language'       => $language
        ]);
    }

    /**
     * Registering DMS authorization
     *     * @param float $amount
     *     * @param int $currency
     *     * @param string $clientIpAddr
     *     * @param string $description
     *     * @param string $language
     *     DMS is different from SMS, dms_start_authorization blocks amount, and than we use dms_make_transaction to
     *     charge customer.
     * @return array  TRANSACTION_ID
     *     TRANSACTION_ID - transaction identifier (28 characters in base64 encoding)
     *     error          - in case of an error
     * @throws EcommException
     */
    public function registerDmsAuthorization($amount, int $currency, string $clientIpAddr, $description = '', $language = 'ru'): array
    {
        return $this->sendRequest([
            'command'        => 'a',
            'amount'         => (string)$amount,
            'currency'       => (string)$currency,
            'msg_type'       => 'DMS',
            'client_ip_addr' => $clientIpAddr,
            'description'    => $description,
            'language'       => $language
        ]);
    }

    /**
     * Executing a DMS transaction
     *
     * @param string $authId
     * @param float  $amount
     * @param int    $currency
     * @param string $clientIpAddr
     * @param string $description
     * @param string $language
     *
     * @return array  RESULT, RESULT_CODE, BRN, APPROVAL_CODE, CARD_NUMBER, error
     * RESULT         - transaction results: OK - successful transaction, FAILED - failed transaction
     * RESULT_CODE    - transaction result code returned from Card Suite Processing RTPS (3 digits)
     * BRN            - retrieval reference number returned from Card Suite Processing RTPS (12 characters)
     * APPROVAL_CODE  - approval code returned from Card Suite Processing RTPS (max 6 characters)
     * CARD_NUMBER    - masked card number
     * error          - in case of an error
     * @throws EcommException
     */
    public function makeDMSTrans($authId, $amount, $currency, $clientIpAddr, $description = '', $language = 'ru'): array
    {
        return $this->sendRequest([
            'command'        => 't',
            'trans_id'       => $authId,
            'amount'         => (string)$amount,
            'currency'       => $currency,
            'client_ip_addr' => $clientIpAddr,
            'msg_type'       => 'DMS',
            'description'    => $description,
            'language'       => $language
        ]);
    }

    /**
     * Transaction result
     *
     * @param string $transId
     * @param string $clientIpAddr
     *
     * @return array  RESULT, RESULT_PS, RESULT_CODE, 3DSECURE, RRN, APPROVAL_CODE, CARD_NUMBER, AAV, RECC_PMNT_ID,
     *                RECC_PMNT_EXPIRY, MRCH_TRANSACTION_ID RESULT               - OK              - successfully
     *                completed transaction,
     *                  FAILED          - transaction has failed,
     *                  CREATED         - transaction just registered in the system,
     *                  PENDING         - transaction is not accomplished yet,
     *                  DECLINED        - transaction declined by ECOMM,
     *                  REVERSED        - transaction is reversed,
     *                  AUTOREVERSED    - transaction is reversed by autoreversal,
     *                  TIMEOUT         - transaction was timed out
     *                    RESULT_PS           - transaction result, Payment Server interpretation (shown only if
     *                    configured to return ECOMM2 specific details FINISHED        - successfully completed
     *                    payment, CANCELLED       - cancelled payment, RETURNED        - returned payment, ACTIVE
     *                        - registered and not yet completed payment. RESULT_CODE       - transaction result code
     *                        returned from Card Suite Processing RTPS (3 digits)
     * 3DSECURE           - AUTHENTICATED   - successful 3D Secure authorization
     *                         DECLINED        - failed 3D Secure authorization
     *                         NOTPARTICIPATED - cardholder is not a member of 3D Secure scheme
     *                         NO_RANGE        - card is not in 3D secure card range defined by issuer
     *                         ATTEMPTED       - cardholder 3D secure authorization using attempts ACS server
     *                         UNAVAILABLE     - cardholder 3D secure authorization is unavailable
     *                         ERROR           - error message received from ACS server
     *                         SYSERROR        - 3D secure authorization ended with system error
     *                         UNKNOWNSCHEME   - 3D secure authorization was attempted by wrong card scheme (Dinners
     *                         club, American Express) RRN               - retrieval reference number returned from
     *                         Card Suite Processing RTPS APPROVAL_CODE       - approval code returned from Card Suite
     *                         Processing RTPS (max 6 characters) CARD_NUMBER       - Masked card number AAV
     *                            - FAILED the results of the verification of hash value in AAV merchant name (only if
     *                            failed) RECC_PMNT_ID            - Reoccurring payment (if available) identification
     *                            in Payment Server. RECC_PMNT_EXPIRY        - Reoccurring payment (if available)
     *                            expiry date in Payment Server in form of YYMM MRCH_TRANSACTION_ID     - Merchant
     *                            Transaction Identifier (if available) for Payment - shown if it was sent as
     *                            additional parameter  on Payment registration. The RESULT_CODE and 3DSECURE fields
     *                            are informative only and can be not shown. The fields RRN and APPROVAL_CODE appear for successful transactions only, for informative purposes, and they facilitate tracking the transactions in Card Suite Processing RTPS system. error                   - In case of an error warning                 - In case of warning (reserved for future use).
     * @throws EcommException
     */
    public function getTransactionResult($transId, $clientIpAddr): array
    {
        return $this->sendRequest([
            'command'        => 'c',
            'trans_id'       => $transId,
            'client_ip_addr' => $clientIpAddr,
        ]);
    }

    /**
     * Transaction reversal
     *
     * @param string $transId
     * @param string $amount reversal amount in fractional units (up to 12 characters). For DMS authorizations only
     *                       full amount can be reversed, i.e., the reversal and authorization amounts have to match.
     *                       In other cases partial reversal is also available.
     *
     * @return array  RESULT, RESULT_CODE
     * RESULT         - OK              - successful reversal transaction
     *                  REVERSED        - transaction has already been reversed
     *            FAILED          - failed to reverse transaction (transaction status remains as it was)
     * RESULT_CODE    - reversal result code returned from Card Suite Processing RTPS (3 digits)
     * error          - In case of an error
     * warning        - In case of warning (reserved for future use).
     * @throws EcommException
     */
    public function revertTransaction($transId, $amount): array
    {
        return $this->sendRequest([
            'command'  => 'r',
            'trans_id' => $transId,
            'amount'   => $amount,
        ]);
    }

    /**
     * needs to be run once every 24 hours.
     * this tells bank to process all transactions of that day SMS or DMS that were success
     * in case of DMS only confirmed and sucessful transactions will be processed
     * @return array RESULT, RESULT_CODE, FLD_075, FLD_076, FLD_087, FLD_088
     * RESULT        - OK     - successful end of business day
     *           FAILED - failed end of business day
     * RESULT_CODE   - end-of-business-day code returned from Card Suite Processing RTPS (3 digits)
     * FLD_075       - the number of credit reversals (up to 10 digits), shown only if result_code begins with 5
     * FLD_076       - the number of debit transactions (up to 10 digits), shown only if result_code begins with 5
     * FLD_087       - total amount of credit reversals (up to 16 digits), shown only if result_code begins with 5
     * FLD_088       - total amount of debit transactions (up to 16 digits), shown only if result_code begins with 5
     * @throws EcommException
     */
    public function closeDay(): array
    {
        return $this->sendRequest([
            'command' => 'b',
        ]);
    }

    /**
     * @return string
     */
    public function getMerchantUrl(): string
    {
        return $this->merchantUrl;
    }

    /**
     * @param string $merchantUrl
     */
    public function setMerchantUrl($merchantUrl): void
    {
        $this->merchantUrl = $merchantUrl;
    }

    /**
     * @return string
     */
    public function getClientUrl(): string
    {
        return $this->clientUrl;
    }

    /**
     * @param string $clientUrl
     */
    public function setClientUrl($clientUrl): void
    {
        $this->clientUrl = $clientUrl;
    }
}