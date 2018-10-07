<?php

namespace Tests\Unit;

use GuzzleHttp\Client;
use Tests\TestCase;

class LipaNaMpesaTest extends TestCase
{
    private $http;

    public function setUp()
    {
        $this->http = new Client(['base_uri' => 'http://kapuwebapi.me/']);
    }

    public function setCredentials()
    {
        $response = $this->http->request('POST', 'oauth/token', [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => '3',
                'client_secret' => 'CghBxZuwkryvsIw6HNuzfZQF3iNPK00a3n4nsOay'
            ]
        ]);

        $access_token = json_decode($response->getBody())->{"access_token"};
        return $access_token;
    }

    public function testPost()
    {
        $body = [
            'amount' => '1',
            'number' => '254717556847',
            'account_reference' => 'test'
        ];

        $response = $this->http->request('POST', 'lipanampesa/paybill', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->setCredentials()
            ],
            'json' => $body
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];

        $this->assertEquals("application/json", $contentType);
        $ResponseCode = json_decode($response->getBody())->{"ResponseCode"};

        $this->assertRegexp('/0/', $ResponseCode);
        $ResponseDescription = json_decode($response->getBody())->{"ResponseDescription"};

        $this->assertRegexp('/Success. Request accepted for processing/', $ResponseDescription);
        $checkoutRequestId = json_decode($response->getBody())->{"CheckoutRequestID"};

        $this->assertNotEmpty($checkoutRequestId);

    }

    public function testGet()
    {
        $response = $this->http->request('GET', 'lipanampesa/paybill', ['http_errors' => false]);
        $this->assertEquals($response->getStatusCode(), 405);
    }

    public function testPut()
    {
        $response = $this->http->request('PUT', 'lipanampesa/paybill', ['http_errors' => false]);
        $this->assertEquals($response->getStatusCode(), 405);
    }

    public function testDelete()
    {
        $response = $this->http->request('DELETE', 'lipanampesa/paybill', ['http_errors' => false]);
        $this->assertEquals($response->getStatusCode(), 405);
    }

    public function testAmountEmpty()
    {
        $body = [
            'amount' => '',
            'number' => '254717556847',
            'account_reference' => 'test'
        ];

        $response = $this->http->request('POST', 'lipanampesa/paybill', [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->setCredentials()
            ],
            'json' => $body
        ]);

        $amount = json_decode($response->getBody())->{"error"}->{"amount"};
        $this->assertRegexp('/The amount must be an integer/', $amount[0]);
        $this->assertRegexp('/The amount is required/', $amount[1]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testInvalidAmount()
    {
        $body = [
            'amount' => 'a',
            'number' => '254717556847',
            'account_reference' => 'test'
        ];

        $response = $this->http->request('POST', 'lipanampesa/paybill', [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->setCredentials()
            ],
            'json' => $body
        ]);

        $amount = json_decode($response->getBody())->{"error"}->{"amount"};
        $this->assertRegexp('/The amount must be an integer/', $amount[0]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testAmountExcessiveValue()
    {
        $body = [
            'amount' => '56516189915961',
            'number' => '254717556847',
            'account_reference' => 'test'
        ];

        $response = $this->http->request('POST', 'lipanampesa/paybill', [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->setCredentials()
            ],
            'json' => $body
        ]);

        $amount = json_decode($response->getBody())->{"error"};
        $this->assertRegexp('/Request not acceptable/', $amount);

        $this->assertEquals(406, $response->getStatusCode());
    }

    public function testNumberLessThanRule()
    {
        $body = [
            'amount' => '1',
            'number' => '25471755684',
            'account_reference' => 'test'
        ];

        $response = $this->http->request('POST', 'lipanampesa/paybill', [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->setCredentials()
            ],
            'json' => $body
        ]);

        $number = json_decode($response->getBody())->{"error"}->{"number"};
        $this->assertRegexp('/The number must not be less than 12 characters/', $number[0]);
        $this->assertRegexp('/Numbers must be in the format 254xxxxxxxxx/', $number[1]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testNumberGreaterThanRule()
    {
        $body = [
            'amount' => '1',
            'number' => '2547175568472',
            'account_reference' => 'test'
        ];

        $response = $this->http->request('POST', 'lipanampesa/paybill', [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->setCredentials()
            ],
            'json' => $body
        ]);

        $number = json_decode($response->getBody())->{"error"}->{"number"};
        $this->assertRegexp('/The number must not exceed 12 characters/', $number[0]);
        $this->assertRegexp('/Numbers must be in the format 254xxxxxxxxx/', $number[1]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testAccountReferenceEmpty()
    {
        $body = [
            'amount' => '1',
            'number' => '254717556847',
            'account_reference' => ''
        ];

        $response = $this->http->request('POST', 'lipanampesa/paybill', [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->setCredentials()
            ],
            'json' => $body
        ]);

        $account_reference = json_decode($response->getBody())->{"error"}->{"account_reference"};
        $this->assertRegexp('/The account reference field is required/', $account_reference[0]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testEmptyDataSet()
    {
        $body = [
            'amount' => '',
            'number' => '',
            'account_reference' => ''
        ];

        $response = $this->http->request('POST', 'lipanampesa/paybill', [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->setCredentials()
            ],
            'json' => $body
        ]);

        $amount = json_decode($response->getBody())->{"error"}->{"amount"};
        $this->assertRegexp('/The amount must be an integer/', $amount[0]);
        $this->assertRegexp('/The amount is required/', $amount[1]);

        $number = json_decode($response->getBody())->{"error"}->{"number"};
        $this->assertRegexp('/A number is required/', $number[0]);

        $account_reference = json_decode($response->getBody())->{"error"}->{"account_reference"};
        $this->assertRegexp('/The account reference field is required/', $account_reference[0]);

        $this->assertEquals(422, $response->getStatusCode());
    }
}