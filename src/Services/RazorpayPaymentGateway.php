<?php

namespace Acelle\Cashier\Services;

use Illuminate\Support\Facades\Log;
use Acelle\Library\Contracts\PaymentGatewayInterface;
use Carbon\Carbon;
use Acelle\Cashier\Cashier;

class RazorpayPaymentGateway implements PaymentGatewayInterface
{
    public $keyId;
    public $keySecret;
    public $active=false;

    /**
     * Construction
     */
    public function __construct($keyId, $keySecret)
    {
        $this->keyId = $keyId;
        $this->keySecret = $keySecret;
        $this->baseUri = 'https://api.razorpay.com/v1/';

        $this->validate();
    }

    public function getName() : string
    {
        return 'Razorpay';
    }

    public function getType() : string
    {
        return 'razorpay';
    }

    public function getDescription() : string
    {
        return 'Start Accepting Payments Instantly with Razorpay\'s Free Payment Gateway. Supports Netbanking, Credit, Debit Cards etc';
    }

    public function validate()
    {
        if (!$this->keyId || !$this->keySecret) {
            $this->active = false;
        } else {
            $this->active = true;
        }
        
    }

    public function isActive() : bool
    {
        return $this->active;
    }

    public function getSettingsUrl() : string
    {
        return action("\Acelle\Cashier\Controllers\RazorpayController@settings");
    }

    public function getCheckoutUrl($invoice) : string
    {
        return action("\Acelle\Cashier\Controllers\RazorpayController@checkout", [
            'invoice_uid' => $invoice->uid,
        ]);
    }

    public function autoCharge($invoice)
    {
        throw new \Exception('Razorpay payment gateway does not support auto charge!');
    }

    public function getAutoBillingDataUpdateUrl($returnUrl='/') : string
    {
        throw new \Exception('
            Razorpay gateway does not support auto charge.
            Therefor method getAutoBillingDataUpdateUrl is not supported.
            Something wrong in your design flow!
            Check if a gateway supports auto billing by calling $gateway->supportsAutoBilling().
        ');
    }

    public function supportsAutoBilling() : bool
    {
        return false;
    }

    public function getData($invoice) {
        return $invoice->pendingTransaction()->getMetadata();
    }

    public function updateData($invoice, $data) {
        return $invoice->pendingTransaction()->updateMetadata($data);
    }

    /**
     * Request PayPal service.
     *
     * @return void
     */
    private function request($type = 'GET', $uri, $headers = [], $body = '')
    {
        $client = new \GuzzleHttp\Client();
        $uri = $this->baseUri . $uri;
        $response = $client->request($type, $uri, [
            'headers' => $headers,
            'body' => is_array($body) ? json_encode($body) : $body,
            'auth' => [$this->keyId, $this->keySecret],
        ]);
        return json_decode($response->getBody(), true);
    }

    /**
     * Check if service is valid.
     *
     * @return void
     */
    public function test()
    {
        $this->request('GET', 'customers');
    }

    /**
     * Current rate for convert/revert Stripe price.
     *
     * @param  mixed    $price
     * @param  string    $currency
     * @return integer
     */
    public function currencyRates()
    {
        return [
            'CLP' => 1,
            'DJF' => 1,
            'JPY' => 1,
            'KMF' => 1,
            'RWF' => 1,
            'VUV' => 1,
            'XAF' => 1,
            'XOF' => 1,
            'BIF' => 1,
            'GNF' => 1,
            'KRW' => 1,
            'MGA' => 1,
            'PYG' => 1,
            'VND' => 1,
            'XPF' => 1,
        ];
    }

    /**
     * Convert price to Stripe price.
     *
     * @param  mixed    $price
     * @param  string    $currency
     * @return integer
     */
    public function convertPrice($price, $currency)
    {
        $currencyRates = $this->currencyRates();

        $rate = isset($currencyRates[$currency]) ? $currencyRates[$currency] : 100;

        return round($price * $rate);
    }

    /**
     * Get Razorpay Order.
     *
     * @param  mixed    $price
     * @param  string    $currency
     * @return integer
     */
    public function createRazorpayOrder($invoice, $plan=null)
    {
        return $this->request('POST', 'orders', [
            "content-type" => "application/json"
        ], [
            "amount" => $this->convertPrice($invoice->total(), $invoice->currency->code),
            "currency" => $invoice->currency->code,
            "receipt" => "rcptid_" . $invoice->uid,
            "payment_capture" => 1,
        ]);
    }
    
    /**
     * Get Razorpay Order.
     *
     * @param  mixed    $price
     * @param  string    $currency
     * @return integer
     */
    public function getRazorpayCustomer($invoice)
    {
        $data = $this->getData($invoice);

        // get customer
        if (isset($data["customer"])) {
            $customer = $this->request('GET', 'customers/' . $data["customer"]["id"]);
        } else {
            $customer = $this->request('POST', 'customers', [
                "Content-Type" => "application/json"
            ], [
                "name" => $invoice->customer->displayName(),
                "email" => $invoice->customer->getBillableEmail(),
                "contact" => "",
                "fail_existing" => "0",
                "notes" => [
                    "uid" => $invoice->customer->getBillableId()
                ]
            ]);
        }
        
        // save customer
        $data['customer'] = $customer;
        $this->updateData($invoice, $data);

        return $customer;
    }

    /**
     * Get access token.
     *
     * @return void
     */
    public function getAccessToken()
    {
        if (!isset($this->accessToken)) {
            // Get new one if not exist
            $uri = 'https://secure.payu.com/pl/standard/user/oauth/authorize';
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', $uri, [
                'headers' =>
                    [
                        'Content-Type' => 'application/x-www-form-urlencoded',
                    ],
                'body' => 'grant_type=client_credentials&client_id=' . $this->client_id . '&client_secret=' . $this->client_secret,
            ]);
            $data = json_decode($response->getBody(), true);
            $this->accessToken = $data['access_token'];
        }

        return $this->accessToken;
    }

    /**
     * Revert price from Stripe price.
     *
     * @param  mixed    $price
     * @param  string    $currency
     * @return integer
     */
    public function revertPrice($price, $currency)
    {
        $currencyRates = $this->currencyRates();

        $rate = isset($currencyRates[$currency]) ? $currencyRates[$currency] : 100;

        return $price / $rate;
    }

    /**
     * Check invoice for paying.
     *
     * @return void
    */
    public function charge($invoice, $request)
    {
        try {
            // charge invoice
            $this->verifyCharge($request);

            // pay invoice
            $invoice->fulfill();
        } catch (\Exception $e) {
            // transaction
            $invoice->payFailed($e->getMessage());
        }
    }

    public function verifyCharge($request)
    {
        $sig = hash_hmac('sha256', $request->razorpay_order_id . "|" . $request->razorpay_payment_id, $this->keySecret);
        if ($sig != $request->razorpay_signature) {
            throw new \Exception('Can not verify remote order: ' . $request->razorpay_order_id);
        }
    }
}
