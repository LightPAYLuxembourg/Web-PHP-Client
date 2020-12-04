# LightPAY Web API
> The first digital meal voucher

This is a client API that allow you to request LightPAY Web API in order to pay with ours the digital meal voucher through the E-commerce **without redirecting your customer from your shop**.
> This API allows you to: 
> - receive payment
> - get payment infos
> - total or partial refund
> - cancel a payment (total refund)

### Before start
In order to get your `Ceredentials` for `test` and `prod` mode you should have a merchant account.

Contact us by email : [info@lightpay.lu](mailto:info@lightpay.lu) or visit our website [www.lightpay.lu](www.lightpay.lu)


### About this API
<p>
We've thought a lot about the best way to implement this API, since LightPAY it is a smartphone application.

>Please note that the payment is ont executed from the merchant shop (like a visa card payment), it is executed rather from the customers App.
</p>

### Payment procedure
<ol>
    <li>let's say the customer has placed an order from your shop and clicked `Pay with LightPAY` button.</li>
    <li>the merchant shop will create a payment page to initiate the payment</li>
    <li>the API generate a payment token and return it with the response</li>
    <li>
    when you get the token, the merchant shop should:
        <ul>
            <li>generate an Qr-code with the payment token : this useful if your customer use computer to order, then he can scan the Qr-code.</li>
            <li>create a button just below to allow customers to copy the token : if they use a mobile phone, then he can copy the token and past it though the App.</li>
        </ul>
        please see the sample project, you have a full example designed for pc and it is mobile friendly
    </li>
    <li>the payment it is executed once your customer scan or copy/path the token</li>
    <li>
        ours server will notify the customer about the status of the payement and will send you the response through the `success_url` `error_url` that you have to define withthe request params.
    </li>
</ol>

### How init payment
It is very simple, all what you need it is to init the `LightPAY\Framework\Request` with your credentials
> pleas note by default the `mode` is set to `test`

```php
use LightPAY\Framework\Request;

$request = new Request(
    "__API_KEY__",
    "__CONSUMER_KEY__",
    "__SECRET_KEY__"
);

$amount = 1000; // 10,00€

// this will return a json token
$res = $request->post("/v1/web/api/payments/init", [
    'amount' => $amount,
    'ref' => "your order reference (should be unique)",
    "merchant_id" => $request->consumer_key,
    "custom_fields" => "foo=bar;barfoo=foot"
]);


$data = \GuzzleHttp\json_decode($res->getBody()->getContents());
// user this token to generate Qr-code
$data->token;
```

### Refund a payment
You can totally or partially refund a payment simply by providing tow parameters `trx_id` and `refund_amount`.

You can repeat the refund until the original trnsaction amount has been totally refunded.

> If you need to do a total refund you can send a `cancel` request (see Cancel payment below).

> Warning
> The only transaction that can be refunded, those who has status `REGISTERED` or `AWAITING_PARTIAL_REFUND` or `PARTIALLY_REFUNDED` <br>


> The status `AWAITING_TO_CAPTURED` `CAPTURED` `AWAITING_TOTAL_REFUND` `TOTALLY_REFUNDED` `PARTIALLY_REFUNDED` `CANCELLED` ***CANNOT BE REFUNDED***

```php
$res = $request->post("/v1/web/api/payments/cancels", [
        "trx_id" => "trx-5e15c21d4631f-1578484253", 
        "refund_amount" => "500"
]);
var_dump($res);
```

### Cancel a payment
You can cancel a payment by providing `trx_id`.

You can repeat the refund until the original trnsaction amount has been totally refunded.

> Warning
> The only transaction that can be canceled, those who has status `REGISTERED` or `AWAITING_PARTIAL_REFUND` or `PARTIALLY_REFUNDED` <br>


> The status `AWAITING_TO_CAPTURED` `CAPTURED` `AWAITING_TOTAL_REFUND` `TOTALLY_REFUNDED` `PARTIALLY_REFUNDED` `CANCELLED` ***CANNOT BE REFUNDED***

```php
$res = $request->post("/v1/web/api/payments/refunds", [
        "trx_id" => "trx-5e15c21d4631f-1578484253"
]);
var_dump($res);
```

### API
Here it is the params that you can send with the request

>All the data in the form must be encoded in UTF-8.

The following table lists the different formats that you can encounter when building your form.

| Notation | Description                                |
|----------|--------------------------------------------|
| A        | Alphabetic characters (from ‘A’ to ‘Z’ and from ‘a’ to ‘z’) |
| N        | Numeric characters |
| S        | Special characters ('-', '_', '.', '$') |
| AN       | Alphanumeric characters |
| ANS      | Alphanumeric and special characters, use this regex to test  : `/^([\p{L}0-9\-\_\.$=@\/%;:?&]+-?)+$/` |
| MAP      | List of key / value pairs ​​separated by a “;”. <br><br>Each key/value pair contains the name of the key followed by “=”, followed by a value.<br>The value can be as string only.<br>The list of possible values ​​​​for each key/value pair is provided in the field definition.<br><br>Example: foo=bar,bar=cool |



#### Constructor
| Params         | Mandatory   | Description                                          |
|----------------|:-----------:|------------------------------------------------------|
| api_key        | YES         | the `api_key` (do not forged to use the correct one in accordance with `test` or `prod` mode) |
| consumer_key   | YES         | the `consumer_key` (do not forged to use the correct one in accordance with `test` or `prod` mode) |
| secret_key     | YES         | the `secret_key` (do not forged to use the correct one in accordance with `test` or `prod` mode) |
| success_url    | YES         | this url will be used to send the response on successful payment (communication server/server). note that you can set this params on the constructor. |
| error_url      | YES         | this url will be used to send the response on payment error (communication server/server). note that you can set this params on the constructor. |
| mode           | YES         | possible value `test` (default) `prod` for live payment |

#### Request params
| Params         | Mandatory   | Type | Length | Description                                          |
|----------------|:-----------:|:----:|:------:|------------------------------------------------------|
| amount         | YES         | N    | 1-5    |the amount should be a cent formatted ex: 100 = 1€.  |
| ref            | YES         | ANS  | 4-255  |your order reference                        |
| merchant_id    | YES         | AN   | 30-255 |use `$request->consumer_key`                         |
| custom_fields  | NO          | MAP  | 0-1024 |a key=value pair ";" separated, each key/value will be sent with `success_url` and `error_url` as a individual `POST` params |

### Responses
All response will be a `JSON` object, if you use the `Request` class you don't need to care about that since the `request` method handle this for you.

#### Payment response
The payment response will be sent by a server to server communication, you need to whitelist the IP address range `80.92.90.96/32`.
The only way to get the payment response it is via the `success_url` and `error_url`, since the payment it is executed from the LightPAY customer application, there is no notification sent to the buyer web page, because he will get notified about the payment directly on the application side.
 
> please note the response will be sent as a `POST` request

#### Response body
| Name           | Description                                          |
|----------------|------------------------------------------------------|
| status         | a status string represented `success` or `error`  |
| code           | status code `success`:`200` `error`:`300`                      |
| trx_id         | you should store this one, it is the only way to get more information's about the transaction                         |
| trx_date       | a datetime `YYYY-MM-DD HH:MM:SS`                        |
| shop_id        | Your shop ID                       |
| ref            | your order reference                         |
| msg            | a textual message about the payment status                         |
| custom_fields  | if set with the init request, a POST populated with each key/value set on the `custom_fields`<br><br> PHP example:  $_POST['foo'] will return bar|


### Payment status
There are 3 types of status :

| Status                    | Description                                          |
|---------------------------|------------------------------------------------------|
| REGISTERED                | the payment was successful and waiting for capturing  |
| CAPTURED                  | the payment has been captured by the bank   |
| AWAITING_TOTAL_REFUND     | waite to a total refunded               |
| AWAITING_PARTIAL_REFUND   | wait to a partial refunded               |
| TOTALLY_REFUNDED          | the payment has been totally refunded               |
| PARTIALLY_REFUNDED        | the payment has been partially refunded               |
| CANCELED                  | the payment has been canceled (total refund)                   |





### About Request
Each request has to implement the flowing parameters :

#### Important note
> all request parameters should be `sorted` regardless the request method (post, get, delete, put...)
> in php use `ksort` function with `SORT_NATURAL` flag


#### Headers
All the flowing headers is mandatory, if it is not set the server will not accept your request.

#### Time
set the timestamp in the header `X-LightPAY-Timestamp` header

```bash
X-LightPAY-Timestamp: time()
```

#### Sign parameters
All parameters of the request must be concatenated with `+` and signed and in a very precise order, please the order below :
> the result of this signature should be set to the `X-LightPAY-Signature` header

```bash
api_key+secret_key+consumer_key+method+url+body+now
...
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

        $key = pack('H*', substr($this->secret_key, 0, 32));
        $iv = hex2bin(substr($this->consumer_key, 0, 32));
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