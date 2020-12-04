<?php

use LightPAY\Framework\Request;

require dirname(__DIR__) . "/vendor/autoload.php";

try {
    $request = new Request(
        "abcbcbc333f7b31ca751d88db3e760874d0cf7803cf5cd8b24d6a6d5051b4efab4eecab",
        "a8dc8a5665d3ac143e6fff431a8dc8a5665d3ac143e6fff431a8dc8a5665d3ac143e6fff431",
        "838a74e940e5cac62b90214829e42e9a0e6ea0338d91eb8a9ae29b788d4bb",
        "http://localhost:9001/response.php",
        "http://localhost:9001/response.php"
    );
    
    $amount = 40;

    $res = $request->post("/v1/web/api/payments/cancels", [
        'trx_id' => "trx-5e15c21d4631f-1578484253"
    ]);
    var_dump($res);
    die();
    $nb = new NumberFormatter("fr_FR", NumberFormatter::CURRENCY);
    $amountView = $nb->format($amount / 100);
    $data = $request->post("/v1/web/api/payments/init", [
        'amount' => $amount,
        'ref' => "5lzRlKO2Rp3AJtSsByUe6JyfnYOPJ84TA7LO9mXT0hknhVqQDATuxgY7baWD3SoF4Iw6r4IPEXpliUTZYnXqqKKL3rNh8PWgdKqA6b9YPLbDEsUvN9zqiF6BjjrztnWN7xqpDxk5nsAeMTJbAQYZPlmrZEVsjxnYzMixNzRmWjzkOlYdmch0AJBmleaFQVBL5xKT4iGKO06AI3cfwm6hmfMiWZk8OVUMDoqEByJXaHQXYa1KzZg0sAUTEF8tJ1F",
        "merchant_id" => "a8dc8a5665d3ac143e6fff431a8dc8a5665d3ac143e6fff431a8dc8a5665d3ac143e6fff431",
        "custom_fields" => "foo=bar;barfoo=foot"
    ]);
    /*
    $res = $request->get("/v1/web/api/payments/response", [
        'foo' => 'bar',
        'baz' => ['hi', 'there!']
    ]);
    */
} catch (Exception $e) {
    error_log($e->getMessage());
}
//var_dump($data->token);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LightPAY</title>

    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="/css/main.css">

</head>
<body>

<div class="container">

    <div class="row">
        <div class="logo">
            <img src="/img/logo.png" alt="LightPAY logo" class="lp-logo">
            <span class="lp">LightPAY</span>
        </div>
    </div>

    <div class="spacer"></div>

    <div class="row">
        <div class="amount-container">
            <span class="amout-text">Montant à payer</span> : <span class="amount"><?= $amountView; ?></span>
        </div>
    </div>

    <div class="spacer"></div>

    <div class="row">
        <div class="qr-container">
            <canvas id="canvas" class="canvas"></canvas>
        </div>
    </div>

    <div class="spacer"></div>


    <div class="row">
        <div class="desc-container">
            <span class="text">Scanner ou copier le code depuis votre application LightPAY.</span>
        </div>
    </div>

    <div class="spacer"></div>

    <div class="row">
        <button class="payment-btn" data-copy="<?= $data->token ?>" id="copy" data-toggle="popover"
                data-content="Copié">Copier le code de paiement
        </button>
        <div class="alert-info">
            <p class="token-hidden" style="display: none;">Votre navigateur ne support pas le "Copy"! Veuillez copier le
                token ci-dessous:</p>
            <p class="token-hidden" style="display: none;"><?= $data->token ?></p>
        </div>
    </div>

</div>

<script src="/js/main.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"
        integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN"
        crossorigin="anonymous"></script>
<script src="https://unpkg.com/popper.js/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"
        integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV"
        crossorigin="anonymous"></script>
<script>
    let qr;
    (function () {
        qr = new QRious({
            element: document.getElementById('canvas'),
            size: 200,
            value: "<?= $data->token; ?>"
        });
    })();

    function generateQRCode() {
        let qrtext = document.getElementById("qr-text").value;
        document.getElementById("qr-result").innerHTML = "QR code for " + qrtext + ":";
        qr.set({
            foreground: 'black',
            size: 200,
            value: "qrtext"
        });
    }

    document.querySelector("#copy").addEventListener("click", async (e) => {

        try {
            if (!navigator.clipboard) {
                console.log("Navigator dose not support copy")
                document.querySelectorAll(".token-hidden").forEach(e => {
                    e.style.display = "block";
                });
                return;
            }

            const text = $(e)[0].path[0].dataset.copy;
            setTimeout(async () => {
                $("#copy").popover('show');
                await navigator.clipboard.writeText(text);
                setTimeout(() => {
                    window.location.replace("https://apps.apple.com/app/apple-store/id1463962129")
                }, 1000)
            }, 500)

            setTimeout(() => {
                $("#copy").popover('hide');
            }, 3000)
        } catch (error) {
            document.querySelectorAll(".token-hidden").forEach(e => {
                e.style.display = "block";
            });
            console.error("Copy failed", error);
        }
    });

    const device = {
        ua: navigator.userAgent,
        type: () => {
            console.log(this.ua)
            if (/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i.test(device.ua)) {
                return "tablet";
            }
            if (
                /Mobile|iP(hone|od|ad)|Android|BlackBerry|IEMobile|Kindle|Silk-Accelerated|(hpw|web)OS|Opera M(obi|ini)/.test(
                    this.ua
                )
            ) {
                return "mobile";
            }
            return "desktop";
        },
        isAndroid: () => {
            if (/Android/.test(device.ua)) {
                return true;
            }
            return false
        },
        isIOS: () => {
            console.log(navigator.userAgent)
            if (/iPhone/.test(device.ua)) {
                return true;
            }
            return false
        }
    };
    console.log(device.isIOS())
</script>
</body>
</html>
