<?php


use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use PsergiuDev\OmnipayMaib\Ecomm;
use PsergiuDev\OmnipayMaib\MaibGateway;
use PsergiuDev\OmnipayMaib\MaibConfiguration;

class OmnipayMaibTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_make_a_purchase(): void
    {
        $ecomm = Mockery::mock(Ecomm::class, static function (MockInterface $mock) {
            $mock->shouldReceive('registerSmsTransaction')
                ->with(1000, 498, '10.10.10.10', 'Purchase #01234', 'ru')
                ->once()
                ->andReturn([
                    'TRANSACTION_ID' => 'trfG/yvwuFsYXRY5uLgKWBLQvxM=',
                    'RESULT'         => 'OK',
                ]);
        })->makePartial();

        $gateway = new MaibGateway(null, null, $ecomm);
        $gateway->setMerchantUrl('https://ecomm.maib.md:4499/ecomm2/MerchantHandler');
        $gateway->setClientUrl('https://ecomm.maib.md:7443/ecomm2/ClientHandler');
        $gateway->setMerchantCertificate('CERT');
        $gateway->setMerchantCertificatePassword('PWD');
        $response = $gateway->purchase([
            'amount'      => '10',
            'currency'    => 'MDL',
            'client_ip'   => '10.10.10.10',
            'description' => 'Purchase #01234',
            'language'    => 'ru'
        ])->send();

        self::assertTrue($response->isSuccessful());
        self::assertEquals('trfG/yvwuFsYXRY5uLgKWBLQvxM=', $response->getTransactionReference());
    }

    /**
     * @test
     */
    public function it_uses_configuration(): void
    {
        MaibConfiguration::merchantUrl('https://ecomm.maib.md:4499/ecomm2/MerchantHandler');
        MaibConfiguration::clientUrl('https://ecomm.maib.md:7443/ecomm2/ClientHandler');
        MaibConfiguration::currency('MDL');
        MaibConfiguration::merchantCertificate('CERT');
        MaibConfiguration::merchantCertificatePassword('PASS');
        $actual = MaibConfiguration::ecomm();

        $expected = new Ecomm();
        $expected->setMerchantUrl('https://ecomm.maib.md:4499/ecomm2/MerchantHandler');
        $expected->setClientUrl('https://ecomm.maib.md:7443/ecomm2/ClientHandler');
        $expected->setCurrencyCode(498);
        $expected->setMerchantCertificate('CERT');
        $expected->setMerchantCertificatePassword('PASS');

        self::assertEquals($expected, $actual);
    }

    public function tearDown(): void
    {
        Mockery::close();
    }
}
