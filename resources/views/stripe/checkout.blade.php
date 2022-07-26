<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

@include('admin.plans._tmp_nav')

<script type="text/javascript" src="https://code.jquery.com/jquery-3.4.1.min.js"></script> 
<script src="https://js.stripe.com/v3/"></script>

<div class="container pt-5">
    
            <div class="col-md-4 mt-40 pd-60">

                @if ($paymentMethod != null)
                    <div class="sub-section">

                        <h4 class="fw-600 mb-3 mt-0">{!! trans('cashier::messages.stripe.current_card') !!}</h4>
                        
                        <ul class="dotted-list topborder section">
                            <li>
                                <div class="unit size1of2">
                                    <strong>{{ trans('cashier::messages.card.brand') }}</strong>
                                </div>
                                <div class="lastUnit size1of2">
                                    <mc:flag><strong>{{ $paymentMethod->card->brand }}</strong></mc:flag>
                                </div>
                            </li>
                            <li class="selfclear">
                                <div class="unit size1of2">
                                    <strong>{{ trans('cashier::messages.card.last4') }}</strong>
                                </div>
                                <div class="lastUnit size1of2">
                                    <mc:flag><strong>{{ $paymentMethod->card->last4 }}</strong></mc:flag>
                                </div>
                            </li>
                        </ul>
                        
                        <form method="POST" action="{{ action("\Acelle\Cashier\Controllers\StripeController@checkout", [
                            'invoice_uid' => $invoice->id,
                        ]) }}">
                            {{ csrf_field() }}
                            <input type="hidden" name="current_card" value="yes" />
                            <button  id="payWithCurrentCard" type="submit" class="mt-2 btn btn-secondary">{{ trans('cashier::messages.stripe.pay_with_this_card') }}</button>
                        </form>
                    </div>
                @endif
                
                <div class="sub-section">

                    <h4 class="fw-600 mb-3 mt-0">{!! trans('cashier::messages.stripe.new_card') !!}</h4>
                    <p>{!! trans('cashier::messages.stripe.new_card.intro') !!}</p>
                    
                    <div id="card-element" class="border p-3 rounded">
                        <!-- Elements will create input elements here -->
                    </div>
                        
                    <!-- We'll put the error messages in this element -->
                    <div id="card-errors" role="alert" class="text-danger small"></div>
                    
                    <button id="submit" class="mt-4 btn btn-secondary">{{ trans('cashier::messages.stripe.pay') }}</button>

                </div>

                <a
                    href="{{ \Acelle\Cashier\Cashier::lr_action('App\Http\Controllers\User\SubscriptionController@index') }}"
                    class="text-muted mt-4" style="text-decoration: underline; display: block"
                >{{ trans('cashier::messages.stripe.return_back') }}</a>
                
            </div>
            <div class="col-md-2"></div>
        </div>
        <br />
        <br />
        <br />
        <script>
            // Set your publishable key: remember to change this to your live publishable key in production
            // See your keys here: https://dashboard.stripe.com/apikeys
            var stripe = Stripe('{{ $service->getPublishableKey() }}');
            var elements = stripe.elements();

            // Set up Stripe.js and Elements to use in checkout form
            var style = {
                base: {
                    color: "#32325d",
                }
            };

            var card = elements.create("card", { style: style });
            card.mount("#card-element");

            card.on('change', function(event) {
                var displayError = document.getElementById('card-errors');
                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    displayError.textContent = '';
                }
            });

            $('#submit').on('click', function() {
                stripe.confirmCardPayment('{{ $clientSecret }}', {
                    payment_method: {
                        card: card,
                        billing_details: {
                            name: 'StripeCus',
                            "address": {
                            "city": null,
                                "line2": null,
                                "postal_code": null,
                                "state": null
                            },
                        }
                    },
                    setup_future_usage: 'off_session'
                }).then(function(result) {
                    if (result.error) {
                        // Show error to your customer
                        alert(result.error.message);
                    } else {
                        if (result.paymentIntent.status === 'succeeded') {
                            // Show a success message to your customer
                            // There's a risk of the customer closing the window before callback execution
                            // Set up a webhook or plugin to listen for the payment_intent.succeeded event
                            // to save the card to a Customer

                            // The PaymentMethod ID can be found on result.paymentIntent.payment_method
                            // console.log(result.paymentIntent);

                            // copy
                            $.ajax({
                                url: '{{ action("\Acelle\Cashier\Controllers\StripeController@checkout", [
                                    'invoice_uid' => $invoice->id,
                                ]) }}',
                                type: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    payment_method_id: result.paymentIntent.payment_method,
                                }
                            }).always(function(response) {
                                window.location = '{{ action('App\Http\Controllers\User\SubscriptionController@index') }}';
                            });
            
                        }
                    }
                });
            });
            
            $('#payWithCurrentCard').on('click', function() {
                
            });
        </script>