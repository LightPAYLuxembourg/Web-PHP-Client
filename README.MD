# LightPAY Web API


This is a client API that allow you to interact with LightPAY Web API server in order to receive payment with the digital meal voucher through your web site  **without redirecting your customer from your shop**.
> This API allows you to: 
> - receive payment
> - get payment infos
> - total or partial refund
> - cancel a payment (total refund)

### Before start
In order to get your `Ceredentials` for testing and production mode and get access to your back office; you should have a merchant account.

Please contact us by email : [info@lightpay.lu](mailto:info@lightpay.lu) or visit our website [www.lightpay.lu](www.lightpay.lu)

### About this API
Since LightPAY it is a smartphone application, and the payment will be executed from it, the way to implement this API is a bit unique.
- You don't have to handle the payment errors, they will be pop-up directly on the App side.
- You don't need to store the payment token (except for statistical reasons or if you have a reason to do so)
- The payment it's not executed from the merchant shop (like a visa card payment), it is executed rather from the customers App.
- You will get notified only for the success payment via the `success_url` (see [Back office](#Back office))


### Payment procedure
Let's say the customer has placed an order from your shop, and clicked `Pay with LightPAY` button.

1. the merchant shop will create a payment page to initiate the payment `/v1/web/api/payments/init` [see the sample here](public/index.php)
2. a payment token will be sent with the response
3. once the token is received, the merchant shop should:

    a) generate a Qr-code with the payment token : this will be used in case your customer order from a computer, then he can scan the Qr-code from LightPAY App.
    
    b) create a button just below the Qr-code to allow customers to copy the payment token : if he use a mobile phone, then he can copy the token and past it through LightPAY App.

> please see the [sample project here](public/index.php), you have a full example designed for PC and it is mobile friendly
    
4. the payment it is executed once your customer scan or copy/past the token on LightPAY App.
5. our server will notify the customer about the payment status (success/error) and will send you the response only in case of success through the `success_url` that you have to define on your [Back office](#Back office).
    


### Local testing
If you need to test the API in tour locale machine, you can use a service such `ngrok.com` to redirect the response from our servers to you local machine.
You need to specify the redirect url prompted by ngrok in your back office.

### Notation
The following table lists the different formats that you can encounter when building your form.

| Notation | Description                                |
|----------|--------------------------------------------|
| A        | Alphabetic characters (from ‘A’ to ‘Z’ and from ‘a’ to ‘z’) |
| N        | Numeric characters |
| S        | Special characters ('-', '_', '.', '$') |
| AN       | Alphanumeric characters |
| ANS      | Alphanumeric and special characters, use this regex to test  : `/^([\p{L}0-9\-_\.,\&#\$\*=@\/%:;\?\+\s]+-?)+$/` |
| MAP      | List of key / value pairs ​​separated by a “;”. <br><br>Each key/value pair contains the name of the key followed by “=”, followed by a value.<br>The value can be as string only.<br>The list of possible values ​​​​for each key/value pair is provided in the field definition.<br><br>Example: foo=bar,bar=cool |



### Initiate a payment

> pleas note by default the `mode` is set to `test`

##### ENDPOINTS
| Method    | Path                                                                      |
|:---------:|---------------------------------------------------------------------------|
| POST      | /v1/web/api/payments/init                                                 |

##### REQUEST PARAMS
| Params         | Mandatory   | Type | Length | Description                                          |
|----------------|:-----------:|:----:|:------:|------------------------------------------------------|
| amount         | YES         | N    | 1-5    |the amount should be a cent formatted ex: 100 = 1€.  |
| ref            | YES         | ANS  | 4-255  |your order reference                        |
| merchant_id    | YES         | AN   | 30-255 |use `consumer_key`                         |
| custom_fields  | NO          | MAP  | 0-1024 |a key=value pair ";" separated, each key/value will be sent with `success_url` as a individual `POST` params |

> Be aware, if you intent to set a url or yu have a special character such (éàè) in a `custom_field` you should UTF-8 encode your string (use Utf8_encode() in php)

##### RESPONSE
| Params    | type    |
|-----------|:-------:|
| token      | string |


```php
use LightPAY\Framework\Request;

$request = new Request(
    "__CONSUMER_KEY__",
    "__API_KEY__",
    "__SECRET_KEY__",
    "test" // prod
);

$amount = 1000; // 10,00€

// this will return a json token
$res = $request->post("/v1/web/api/payments/init", [
    'amount' => $amount,
    'ref' => "your order reference (should be unique)",
    "merchant_id" => $request->consumer_key,
    "custom_fields" => "foo=bar;barfoo=foot"
]);

// user this token to generate Qr-code
$res->token;
```

### Payment response
A payment response can be sent **only** as server/server communication, you should set the `success_url` through you back office. [see Back office](#Back office).

##### ENDPOINTS
| Method    | Path                                                                      |
|:---------:|---------------------------------------------------------------------------|
| POST      | the `succes_url` that you have previously defined in the back office      |

##### RESPONSE
The response it's an object that contain :

| Name                      | Type      | Description                                    |
|---------------------------|-----------|------------------------------------------------|
| status                    | string    | a string status `success` |
| code                      | int       | `200` for success `301` otherwise |
| amount                    | int       | the transaction amount |
| currency                  | string    | `EUR` |
| object                    | string    | the object of the request `payment` |
| trx_id                    | string    | **you should store this in you database, you need it to get payment info, refund or cancel...**|
| ref                       | string    | your payment reference that you've defined on the payment init |
| Your `custom_fields`      | mixed     | each field will be sent as a POST params |
| date                      | timestamp | a timestamp represent the transaction date |


### Refund a payment
Refund a payment partially or totally.


> Warning
> The only transaction that can be refunded, those who has the flowing status `REGISTERED` `PARTIALLY_REFUNDED` `AWAITING_PARTIAL_REFUND` <br>

> All the other status  ***CANNOT BE REFUNDED***

##### ENDPOINTS
| Method    | Path                                                                      |
|:---------:|---------------------------------------------------------------------------|
| POST      | /v1/web/api/payments/refunds                                                 |

##### REQUEST PARAMS
| Params         | Mandatory   | Type | Length | Description                                          |
|----------------|:-----------:|:----:|:------:|------------------------------------------------------|
| amount         | YES         | N    | 1-5    |the amount should be a cent formatted ex: 100 = 1€.  |
| trx_id         | YES         | ANS  | 4-255  |the `ORIGINAL` transaction Id that you receive with the payment response |

##### RESPONSE
| Name                      | Type      | Description                                    |
|---------------------------|-----------|------------------------------------------------|
| status                    | string    | a string status `success` or `error` |
| code                      | int       | `200` for success `301` otherwise |
| amount                    | int       | the transaction amount |
| currency                  | string    | `EUR` |
| object                    | string    | the object of the request `refund` |
| trx_id                    | string    | **be aware you can not use this transaction id for a future refund/cancel, use always the original trx_id for those purposes**|
| date                      | timestamp | a timestamp represent the transaction date |


```php
// Original payment amount it's 10€

// A partial refund
$res = $request->post("/v1/web/api/payments/refunds", [
        "trx_id" => "trx-5e15c21d4691f-1578484250", 
        "refund_amount" => "500"
]);
var_dump($res);

// A total refund
$res = $request->post("/v1/web/api/payments/refunds", [
        "trx_id" => "trx-5e15c21d4691f-1578484250", 
        "refund_amount" => "1000"
]);
var_dump($res);
```

### Cancel a payment
You can cancel a payment by providing a `trx_id`.

> Warning
> The only transaction that can be canceled, those who has status `REGISTERED` or `AWAITING_PARTIAL_REFUND` or `PARTIALLY_REFUNDED` <br>


> The status `AWAITING_TO_CAPTURED` `CAPTURED` `AWAITING_TOTAL_REFUND` `TOTALLY_REFUNDED` `PARTIALLY_REFUNDED` `CANCELLED` ***CANNOT BE REFUNDED***


##### ENDPOINTS
| Method    | Path                                                                      |
|-----------|---------------------------------------------------------------------------|
| POST      | /v1/web/api/payments/cancels                                              |

##### REQUEST PARAMS
| Params         | Mandatory   | Type | Length | Description                                          |
|----------------|:-----------:|:----:|:------:|------------------------------------------------------|
| trx_id         | YES         | ANS  | 4-255  |the `ORIGINAL` transaction Id that you receive with the payment response |

##### RESPONSE
| Name                      | Type      | Description                                    |
|---------------------------|-----------|------------------------------------------------|
| status                    | string    | a string status `success` or `error` |
| code                      | int       | `200` for success `301` otherwise |
| amount                    | int       | the transaction amount |
| currency                  | string    | `EUR` |
| object                    | string    | the object of the request `refund` |
| trx_id                    | string    | **be aware you can not use this transaction id for a future refund/cancel, use always the original trx_id for those purposes**|
| date                      | timestamp | a timestamp represent the transaction date |


```php
$res = $request->post("/v1/web/api/payments/cancels", [
        "trx_id" => "trx-5e15c21d4691f-1578484253"
]);
var_dump($res);
```

### Get payment status
##### ENDPOINTS
| Method    | Path                                                                      |
|-----------|---------------------------------------------------------------------------|
| GET       | /v1/web/api/payments/status                                              |

##### REQUEST PARAMS
| Params         | Mandatory   | Type | Length | Description                                          |
|----------------|:-----------:|:----:|:------:|------------------------------------------------------|
| trx_id         | YES         | ANS  | 4-255  |the `ORIGINAL` transaction Id that you receive with the payment response |

##### RESPONSE
| Name                      | Type      | Description                                    |
|---------------------------|-----------|------------------------------------------------|
| payment_status            | string    | a string status `REGISTERED` `AWAITING_PARTIAL_REFUND` `PARTIALLY_REFUNDED` `AWAITING_TO_CAPTURED` `CAPTURED` `AWAITING_TOTAL_REFUND` `TOTALLY_REFUNDED` `PARTIALLY_REFUNDED` `CANCELLED` |


```php
$res = $request->post("/v1/web/api/payments/status", [
        "trx_id" => "trx-5e15c21d4691f-1578484253"
]);
var_dump($res);
```


### Get payment infos
##### ENDPOINTS
| Method    | Path                                                                      |
|-----------|---------------------------------------------------------------------------|
| GET       | /v1/web/api/payments/details                                              |

##### REQUEST PARAMS
| Params         | Mandatory   | Type | Length | Description                                          |
|----------------|:-----------:|:----:|:------:|------------------------------------------------------|
| trx_id         | YES         | ANS  | 4-255  |the `ORIGINAL` transaction Id that you receive with the payment response |

##### RESPONSE
| Name                      | Type      | Description                                    |
|---------------------------|-----------|------------------------------------------------|
| status                    | string    | a string status `REGISTERED` `AWAITING_PARTIAL_REFUND` `PARTIALLY_REFUNDED` `AWAITING_TO_CAPTURED` `CAPTURED` `AWAITING_TOTAL_REFUND` `TOTALLY_REFUNDED` `PARTIALLY_REFUNDED` `CANCELLED` |
| amount                    | int       | the transaction amount |
| formatted_amount          | int       | a human represented amount |
| transaction_id            | string    | transaction Id that you receive with the payment response |
| refunds                   | object    | the different request(s) for refunds, each has the flowing parameters `trx_id` `amount` `date` |
| payment_mode              | string    | "Web API" |
| created_at                | timestamp | a timestamp represent the transaction date |


```php
$res = $request->post("/v1/web/api/payments/details", [
        "trx_id" => "trx-5e15c21d4691f-1578484253"
]);
var_dump($res);
[
    "amount" => 1000,
    "formatted_amount" => "10,00€",
    "transaction_id" => "trx-5e15c21d4691f-1578484253",
    "status" => "REGISTERED",
    "payment_mode" => "Web API",
    "created_at" => "2020-12-12 12:00:00",
    "refunds" => [
        [
            "trx_id" => "trx-4e14c41d469f4-1578484768",
            "amount" => "200",
            "date" => "2020-12-13 11:00:00",
        ],
        [
            "trx_id" => "trx-f514c4gd56965-1578480747",
            "amount" => "300",
            "date" => "2020-12-13 11:00:00",
        ]
    ]
];
```



### API
In this project you have a PHP client class that implement all you need to concentrate onto implementing the payment process. 
But if you need to implement your own class you must follow the flowing steps in order to make your requests accepted by our server, especially the signing step.

> All the data in the form must be encoded in UTF-8.

#### Getaways
| Production                      | Demo |
|---------------------------------|------------------------------------|
| https://web.lunch-digi-pay.lu   | https://webdemo.lunch-digi-pay.lu  |


#### Paths
| Path                            | Request method | Description                |
|---------------------------------|----------------|----------------------------|
| /v1/web/api/payments/init       | POST           | Initiate a payment         |
| /v1/web/api/payments/refunds    | POST           | Do a refund request        |
| /v1/web/api/payments/cancels    | POST           | Cancel a payment           |
| /v1/web/api/payments/status     | GET            | Get payment status         |
| /v1/web/api/payments/details    | GET            | Get payment details        |


#### Credentials

| Params         | Mandatory   | Description                                          |
|----------------|:-----------:|------------------------------------------------------|
| api_key        | YES         | the `api_key` (do not forged to use the correct one in accordance with `test` or `prod` mode) |
| consumer_key   | YES         | the `consumer_key` (do not forged to use the correct one in accordance with `test` or `prod` mode) |
| secret_key     | YES         | the `secret_key` (do not forged to use the correct one in accordance with `test` or `prod` mode) |

### Responses
All response will be a `JSON` object, if you use the `Request` class you don't need to care about that since the `request` method handle this for you.

#### Payment response
The payment response will be sent by a server to server communication, you need to whitelist the IP address range `80.92.90.96/32`.
The only way to get the payment response it is via the `success_url` and `error_url`, since the payment it is executed from the LightPAY customer application, there is no notification sent to the buyer web page, because he will get notified about the payment directly on the application side.
 
> please note the response will be sent as a `POST` request

#### Response body
The response parameters could change depending on the request, see the different examples above.

| Name           | Description                                          |
|----------------|------------------------------------------------------|
| status         | a status string represented `success` or `error`  |
| code           | status code `success`:`200` `error`:`300`                      |
| token          | a payment token |
| payment_status | a payment status [see Status chapter](#Payment status) |
| trx_id         | you should store this one, it is the only way to get more information's about the transaction  |
| trx_date       | a datetime `YYYY-MM-DD HH:MM:SS`                        |
| shop_id        | Your shop ID                       |
| ref            | your order reference                         |
| msg            | a textual message about the payment status                         |
| custom_fields  | if set with the init request, a POST populated with each key/value set on the `custom_fields`<br><br> PHP example:  $_POST['foo'] will return bar|


### Payment status
There are 7 types of status :

| Status                    | Description                                          |
|---------------------------|------------------------------------------------------|
| REGISTERED                | the payment was successful and waiting for capturing  |
| CAPTURED                  | the payment has been captured by the bank   |
| AWAITING_TOTAL_REFUND     | waite to a total refunded               |
| AWAITING_PARTIAL_REFUND   | wait to a partial refunded               |
| TOTALLY_REFUNDED          | the payment has been totally refunded               |
| PARTIALLY_REFUNDED        | the payment has been partially refunded               |
| CANCELED                  | the payment has been canceled (total refund)                   |





### Building a Request
Each request has to implement the flowing parameters :

#### Important note
> all request parameters should be `sorted` regardless the request method (post, get, delete, put...)
> in php use `ksort` function with `SORT_NATURAL` flag


#### Headers
All the flowing headers are mandatory, if it is not set the server will not accept your request.

#### Time
set the timestamp in the header `X-LightPAY-Timestamp` header

```bash
X-LightPAY-Timestamp: time()
```

#### Sign parameters
only `AES-128-CBC` algorithm should be used with **no padding option**.

All parameters of the request must be concatenated with `+` and signed and in a very precise order, please the order below :
> the result of this signature should be set to the `X-LightPAY-Signature` header

##### <u>Signing order:</u>
1. api_key
2. secret_key
3. consumer_key
4. method
5. url
6. body
7. now

- `key` should be the first 32 characters of the `secret_key`
- `iv` should be the first 32 characters of the `consumer_key`

to verify that the signature is correct, a `hello world` with the following key and iv should return:
```
$str = "hello world";
$key = "d9d359cce9371de02914c42d2786c0d9"; // length 32
$iv  = "bb78c8065a90e05f1520b8ca17de1295"; // length 32
// Result
CMIck+g5ZORUBnPbMWEFog==
```

```bash
// set the signature header
// here SIGN is a dommy function, use your own fuction or see the Signing chapter
X-LightPAY-Signature: SIGN(api_key+secret_key+consumer_key+method+url+body+now)
```

#### Authorization
In the `Authorization` header use the `consumer_key` as login and `api_key` as password `base64` encoded

```bash
Authorization: Basic base64_encode(consumer_key:api_key)
```

#### Content-Type
only `application/x-www-form-urlencoded` will be accepted
```bash
Content-Type: application/x-www-form-urlencoded
```

#### X-LightPAY-Credentials
you must concatenate the `api_key` and `secret_key` with the `+`, then you should sign the result with `AES-128-CBC`
> please see the [Signing](#Signing) chapter

```bash
// here SIGN is a dommy function, use your own fuction or see the Signing chapter
X-LightPAY-Credentials: SIGN($this->api_key . "+" . $this->secret_key)
```

### Signing
You should use the example below in `PHP` or something similar in other language.

- `key` should be the first 32 characters of the `secret_key`
- `iv` should be the first 32 characters of the `consumer_key`

only `AES-128-CBC` algorithm should be used



> use a function that sign the data for you, you will need it for each request

```php
public function sign($data): ?string
{
    if (!$data)
        return "";

    try {
        $toSign = null;

        if (gettype($data) === "array")
            $toSign = '$1$' . implode("+", $data);
        else
            $toSign = '$1$' . $data;

        $key = pack('H*', substr("__SECRET_KEY__", 0, 32));
        $iv = hex2bin(substr("__CONSUMER_KEY__", 0, 32));
        $encrypted = openssl_encrypt(
            $toSign,
            "AES-128-CBC",
            $key,
            0,
            $iv
        );

        return (string)$encrypted;
    } catch (Exception $err) {
        error_log("[ERROR] : encryption error");
        error_log($err);
        return null;
    }
}
```


### Back office
Once you have your access to the back office, go to the WEB API section, you'll find there your credentials for test and prod modes.

#### IMPORTANT
In order to get the payment response you should set the `success_url` in the dedicated field.
> For local testing see [Local testing chapter](#Local testing)
