<?php

namespace Tests\Unit;

use GuzzleHttp\Client;
use Tests\TestCase;

class C2BTest extends TestCase
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
                'client_id' => '1',
                'client_secret' => 'DRTuuQcPc8sOxfNNJWBNO08S1UaNhDRB9WGXaMW1'
            ]
        ]);

        $access_token = json_decode($response->getBody())->{"access_token"};
        return $access_token;
    }

    public function testPost()
    {
        $body = [
            'Amount' => '200',
            'PhoneNumber' => '254708374149',
            'BillRefNumber' => 'test'
        ];

        $response = $this->http->request('POST', 'c2b/payment', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->setCredentials()
            ],
            'json' => $body
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);

        $ResponseDescription = json_decode($response->getBody())->{"ResponseDescription"};
        $this->assertRegexp('/Accept the service request successfully./', $ResponseDescription);

        $originatorConversationId = json_decode($response->getBody())->{"OriginatorCoversationID"};
        $this->assertNotEmpty($originatorConversationId);
    }

    public function testGet()
    {
        $response = $this->http->request('GET', 'c2b/payment', ['http_errors' => false]);
        $this->assertEquals($response->getStatusCode(), 405);
    }

    public function testPut()
    {
        $response = $this->http->request('PUT', 'c2b/payment', ['http_errors' => false]);
        $this->assertEquals($response->getStatusCode(), 405);
    }

    public function testDelete()
    {
        $response = $this->http->request('DELETE', 'c2b/payment', ['http_errors' => false]);
        $this->assertEquals($response->getStatusCode(), 405);
    }

    public function testAmountEmpty()
    {
        $body = [
            'Amount' => '',
            'PhoneNumber' => '254708374149',
            'BillRefNumber' => 'test'
        ];

        $response = $this->http->request('POST', 'c2b/payment', [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->setCredentials()
            ],
            'json' => $body
        ]);

        $amount = json_decode($response->getBody())->{"error"}->{"Amount"};
        $this->assertRegexp('/The amount must be an integer/', $amount[0]);
        $this->assertRegexp('/The amount is required/', $amount[1]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testAmountInvalid()
    {
        $body = [
            'Amount' => 'fasfaa',
            'PhoneNumber' => '254708374149',
            'BillRefNumber' => 'test'
        ];

        $response = $this->http->request('POST', 'c2b/payment', [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->setCredentials()
            ],
            'json' => $body
        ]);

        $amount = json_decode($response->getBody())->{"error"}->{"Amount"};
        $this->assertRegexp('/The amount must be an integer/', $amount[0]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testAmountExcessiveValue()
    {
        $body = [
            'Amount' => '5161698165',
            'PhoneNumber' => '254708374149',
            'BillRefNumber' => 'test'
        ];

        $response = $this->http->request('POST', 'c2b/payment', [
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

    public function testNumberEmpty()
    {
        $body = [
            'Amount' => '200',
            'PhoneNumber' => '',
            'BillRefNumber' => 'test'
        ];

        $response = $this->http->request('POST', 'c2b/payment', [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->setCredentials()
            ],
            'json' => $body
        ]);

        $number = json_decode($response->getBody())->{"error"}->{"PhoneNumber"};
        $this->assertRegexp('/A number is required/', $number[0]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testNumberLessThanRule()
    {
        $body = [
            'Amount' => '200',
            'PhoneNumber' => '25470837414',
            'BillRefNumber' => 'test'
        ];

        $response = $this->http->request('POST', 'c2b/payment', [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->setCredentials()
            ],
            'json' => $body
        ]);

        $number = json_decode($response->getBody())->{"error"}->{"PhoneNumber"};
        $this->assertRegexp('/The number must not be less than 12 characters/', $number[0]);
        $this->assertRegexp('/Numbers must be in the format 254xxxxxxxxx/', $number[1]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testNumberGreaterThanRule()
    {
        $body = [
            'Amount' => '200',
            'PhoneNumber' => '2547083741495',
            'BillRefNumber' => 'test'
        ];

        $response = $this->http->request('POST', 'c2b/payment', [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->setCredentials()
            ],
            'json' => $body
        ]);

        $number = json_decode($response->getBody())->{"error"}->{"PhoneNumber"};
        $this->assertRegexp('/The number must not exceed 12 characters/', $number[0]);
        $this->assertRegexp('/Numbers must be in the format 254xxxxxxxxx/', $number[1]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testBillReferenceEmpty()
    {
        $body = [
            'Amount' => '200',
            'PhoneNumber' => '254708374149',
            'BillRefNumber' => ''
        ];

        $response = $this->http->request('POST', 'c2b/payment', [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->setCredentials()
            ],
            'json' => $body
        ]);

        $billReference = json_decode($response->getBody())->{"error"}->{"BillRefNumber"};
        $this->assertRegexp('/The account reference field is required/', $billReference[0]);
    }

    public function testEmptyDataSet()
    {
        $body = [
            'Amount' => '',
            'PhoneNumber' => '',
            'BillRefNumber' => ''
        ];

        $response = $this->http->request('POST', 'c2b/payment', [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->setCredentials()
            ],
            'json' => $body
        ]);

        $amount = json_decode($response->getBody())->{"error"}->{"Amount"};
        $this->assertRegexp('/The amount must be an integer/', $amount[0]);
        $this->assertRegexp('/The amount is required/', $amount[1]);

        $number = json_decode($response->getBody())->{"error"}->{"PhoneNumber"};
        $this->assertRegexp('/A number is required/', $number[0]);

        $billReference = json_decode($response->getBody())->{"error"}->{"BillRefNumber"};
        $this->assertRegexp('/The account reference field is required/', $billReference[0]);
    }
}