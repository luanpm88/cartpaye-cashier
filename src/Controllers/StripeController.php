<?php

namespace Acelle\Cashier\Controllers;

use Acelle\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as LaravelLog;
use Acelle\Cashier\Cashier;
use Acelle\Cashier\Services\StripePaymentGateway;
use Acelle\Library\Facades\Billing;
use Acelle\Model\Setting;
use App\Models\Invoice;
use Acelle\Library\TransactionVerificationResult;
use Acelle\Model\Transaction;
use Acelle\Library\AutoBillingData;


class StripeController extends Controller
{
    public function settings(Request $request)
    {
        $gateway = Billing::getGateway('stripe');

        if ($request->isMethod('post')) {
            // make validator
            $validator = \Validator::make($request->all(), [
                'secret_key' => 'required',
                'publishable_key' => 'required',
            ]);

            // test service
            $validator->after(function ($validator) use ($gateway, $request) {
                try {
                    $stripe = new StripePaymentGateway($request->publishable_key, $request->secret_key);
                    $stripe->test();
                } catch(\Exception $e) {
                    $validator->errors()->add('field', 'Can not connect to ' . $gateway->getName() . '. Error: ' . $e->getMessage());
                }
            });

            // redirect if fails
            if ($validator->fails()) {
                return response()->view('cashier::stripe.settings', [
                    'gateway' => $gateway,
                    'errors' => $validator->errors(),
                ], 400);
            }

            // save settings
            Setting::set('cashier.stripe.secret_key', $request->secret_key);
            Setting::set('cashier.stripe.publishable_key', $request->publishable_key);

            // enable if not validate
            if ($request->enable_gateway) {
                Billing::enablePaymentGateway($gateway->getType());
            }

            $request->session()->flash('alert-success', trans('cashier::messages.gateway.updated'));
            return redirect()->action('App\Http\Controllers\Admin\PaymentController@index');
        }

        return view('cashier::stripe.settings', [
            'gateway' => $gateway,
        ]);
    }

    public function getCheckoutUrl($invoice)
    {
        return action("\Acelle\Cashier\Controllers\StripeController@checkout", [
            'invoice_uid' => $invoice->id,
        ]);
    }

    /**
     * Get current payment service.
     *
     * @return \Illuminate\Http\Response
     **/
    public function getPaymentService()
    {
        return Billing::getGateway('stripe');
    }
    
    /**
     * Subscription checkout page.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     **/
    public function checkout(Request $request, $invoice_uid)
    {
        $customer = $request->user()->account;
        $service = $this->getPaymentService();
        $invoice = Invoice::find($invoice_uid);

        // exceptions
        if (!$invoice->isNew()) {
            throw new \Exception('Invoice is not new');
        }

        // free plan. No charge
        if ($invoice->total() == 0) {
            $invoice->checkout($service, function($invoice) {
                return new TransactionVerificationResult(TransactionVerificationResult::RESULT_DONE);
            });

            return redirect()->action('App\Http\Controllers\User\SubscriptionController@index');
        }

        if ($request->isMethod('post')) {
            $invoice->fulfill();

            return redirect()->action('App\Http\Controllers\User\SubscriptionController@index');
        }

        return view('cashier::stripe.checkout', [
            'service' => $service,
            'invoice' => $invoice,
            'paymentMethod' => $service->getPaymentMethod($customer),
            'clientSecret' => $service->getClientSecret($customer, $invoice),
        ]);
    }

    public function paymentAuth(Request $request, $invoice_uid)
    {
        $invoice = Invoice::find($invoice_uid);
        $service = $this->getPaymentService();
        $intent = \Stripe\PaymentIntent::retrieve($request->payment_intent_id);

        return view('cashier::stripe.paymentAuth', [
            'invoice' => $invoice,
            'service' => $service,
            'intent' => $intent,
        ]);
    }

    public function autoBillingDataUpdate(Request $request)
    {
        return redirect()->action('App\Http\Controllers\User\SubscriptionController@index');
    }
}
