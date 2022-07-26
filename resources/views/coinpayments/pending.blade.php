

    @include("account._menu", ['tab' => 'subscription'])

    <div class="row">
        <div class="col-md-8">
            <label></label>  
            <h2 class="mt-0">
                {{ $invoice->title }}
            </h2>

            <p>{!! trans('cashier::messages.coinpayments.pending.intro') !!}
                
            <ul class="dotted-list topborder section mb-4">
                <li>
                    <div class="unit size1of3 font-weight-bold">
                        {{ trans('cashier::messages.coinpayments.status_code') }}
                    </div>
                    <div class="lastUnit size2of3">
                        <mc:flag>
                            {{ $service->getData($invoice)['status'] }}
                        </mc:flag>
                    </div>
                </li>
                <li>
                    <div class="unit size1of3 font-weight-bold">
                        {{ trans('cashier::messages.coinpayments.status') }}
                    </div>
                    <div class="lastUnit size2of3">
                        <mc:flag>
                            {{ $service->getData($invoice)['status_text'] }}
                        </mc:flag>
                    </div>
                </li>
                <li>
                    <div class="unit size1of3 font-weight-bold">
                        {{ trans('cashier::messages.coinpayments.plan') }}
                    </div>
                    <div class="lastUnit size2of3">
                        <mc:flag>{{ $invoice->title }}</mc:flag>
                    </div>
                </li>
                <li>
                    <div class="unit size1of3 font-weight-bold">
                        {{ trans('cashier::messages.coinpayments.amount') }}
                    </div>
                    <div class="lastUnit size2of3">
                        <mc:flag>{{ $invoice->total() }} {{ $service->receiveCurrency }}</mc:flag>
                    </div>
                </li>
                <li>
                    <div class="unit size1of3 font-weight-bold">
                        {{ trans('cashier::messages.coinpayments.checkout_url') }}
                    </div>
                    <div class="lastUnit size2of3">
                        <mc:flag>
                            <a target="_blank" style="white-space: nowrap;
overflow: hidden;
text-overflow: ellipsis;
display: block;" href="{{ $service->getData($invoice)['checkout_url'] }}">
                                {{ $service->getData($invoice)['checkout_url'] }}
                            </a>
                        </mc:flag>
                    </div>
                </li>
                <li>
                    <div class="unit size1of3 font-weight-bold">
                        {{ trans('cashier::messages.coinpayments.status_url') }}
                    </div>
                    <div class="lastUnit size2of3">
                        <mc:flag>
                            <a target="_blank" style="white-space: nowrap;
overflow: hidden;
text-overflow: ellipsis;
display: block;" href="{{ $service->getData($invoice)['status_url'] }}">
                                {{ $service->getData($invoice)['status_url'] }}
                            </a>
                        </mc:flag>
                    </div>
                </li>
            </ul> 

            <div class="my-4">
                <hr>
                <a class="" link-method="POST" link-confirm="{{ trans('messages.invoice.cancel.confirm') }}"
                    href="{{ action('SubscriptionController@cancelInvoice', [
                        'invoice_uid' => $invoice->id,
                    ]) }}">
                    {{ trans('messages.subscription.cancel_now_change_other_plan') }}
                </a>
            </div>
        </div>
        <div class="col-md-2"></div>
        <div class="col-md-4">
            @include('invoices.bill', [
                'bill' => $invoice->getBillingInfo(),
            ])
        </div>
    </div>