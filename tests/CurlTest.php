<?php
use PHPUnit\Framework\TestCase;
use Waponix\CurlOop\Curl;
use Waponix\CurlOop\Exception\CurlException;

class CurlTest extends TestCase
{
    public function testResponseCodeShouldBe200()
    {
        $curl = new Curl();
        $curl
            ->send(url: 'https://dummyjson.com/products/1')
            ->end();
        $this->assertSame(200, $curl->getResponseCode());
    }

    public function testResponseCodeShouldBe404()
    {
        $curl = new Curl();
        $curl
            ->send(url: 'https://dummyjson.com/producto')
            ->end();
        $this->assertSame(404, $curl->getResponseCode());
    }

    public function testResonseShouldBeText()
    {
        $curl = new Curl();
        $curl
            ->send(url: 'https://dummyjson.com/products/1')
            ->end();
        $this->assertIsString($curl->getResponse());
    }

    public function testConnectionShouldBeActiveOrNot()
    {
        $curl = new Curl();
        $curl->send(url: 'https://dummyjson.com/products/1');
        $this->assertTrue($curl->isActive());
        $curl->end();
        $this->assertNotTrue($curl->isActive());
    }

    public function testShouldExpectsCurlException()
    {
        $this->expectException(CurlException::class);
        
        $curl = new Curl();
        $curl
            ->setRequestTimeout(1)
            ->setConnectionTimeout(1)
            ->send(url: 'https://dummyjson.com/products', method: 'INVALID_METHOD')
            ->end();
    }

    public function testResponseHeaderValueShouldBeArray()
    {
        $curl = new Curl();
        $curl
            ->send(url: 'https://dummyjson.com/products')
            ->end();

        $this->assertIsArray($curl->getResponseHeader());
    }

    public function testResponseHeaderValueShouldBeString()
    {
        $curl = new Curl();
        $curl
            ->send(url: 'https://dummyjson.com/products')
            ->end();

        $this->assertIsString($curl->getResponseHeader('content-type'));
    }

    public function testResponseHeaderValueShouldBeNull()
    {
        $curl = new Curl();
        $curl
            ->send(url: 'https://dummyjson.com/products')
            ->end();

        $this->assertTrue($curl->getResponseHeader('none-existing-header-value') === null);
    }
}