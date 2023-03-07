<?php
use PHPUnit\Framework\TestCase;
use Curly\UrlRequest;

class UrlRequestTest extends TestCase
{
    public function testResponseCodeShouldBe200()
    {
        $UrlRequest = new UrlRequest();
        $UrlRequest
            ->send(url: 'https://dummyjson.com/products/1')
            ->end();
        $this->assertSame(200, $UrlRequest->getResponseCode());
    }

    public function testResponseCodeShouldBe404()
    {
        $UrlRequest = new UrlRequest();
        $UrlRequest
            ->send(url: 'https://dummyjson.com/producto')
            ->end();
        $this->assertSame(404, $UrlRequest->getResponseCode());
    }

    public function testResonseShouldBeText()
    {
        $UrlRequest = new UrlRequest();
        $UrlRequest
            ->send(url: 'https://dummyjson.com/products/1')
            ->end();
        $this->assertIsString($UrlRequest->getResponse());
    }

    public function testConnectionShouldBeActiveOrNot()
    {
        $UrlRequest = new UrlRequest();
        $UrlRequest->send(url: 'https://dummyjson.com/products/1');
        $this->assertTrue($UrlRequest->isActive());
        $UrlRequest->end();
        $this->assertNotTrue($UrlRequest->isActive());
    }

    public function testShouldExpectsCurlException()
    {
        $this->expectException(Curly\Exceptions\CurlException::class);
        
        $UrlRequest = new UrlRequest();
        $UrlRequest
            ->setRequestTimeout(1)
            ->setConnectionTimeout(1)
            ->send(url: 'https://dummyjson.com/products', method: 'INVALID_METHOD')
            ->end();
    }

    public function testResponseHeaderValueShouldBeArray()
    {
        $UrlRequest = new UrlRequest();
        $UrlRequest
            ->send(url: 'https://dummyjson.com/products')
            ->end();

        $this->assertIsArray($UrlRequest->getResponseHeader());
    }

    public function testResponseHeaderValueShouldBeString()
    {
        $UrlRequest = new UrlRequest();
        $UrlRequest
            ->send(url: 'https://dummyjson.com/products')
            ->end();

        $this->assertIsString($UrlRequest->getResponseHeader('content-type'));
    }

    public function testResponseHeaderValueShouldBeNull()
    {
        $UrlRequest = new UrlRequest();
        $UrlRequest
            ->send(url: 'https://dummyjson.com/products')
            ->end();

        $this->assertTrue($UrlRequest->getResponseHeader('none-existing-header-value') === null);
    }
}