<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

@include('user.subscription._tmp_nav')

<div class="container pt-5">    
    
    
    <div class="row">
        <div class="col-md-6">
            <h2>{!! trans('cashier::messages.pay_invoice') !!}</h2>  

            <div class="alert alert-info bg-grey-light">
                {!! $service->getPaymentInstruction() !!}
            </div>
            <hr>
                
            <div class="d-flex align-items-center">
                <form method="POST"
                    action="{{ \Acelle\Cashier\Cashier::lr_action('\Acelle\Cashier\Controllers\OfflineController@claim', [
                        'invoice_uid' => $invoice->id
                    ]) }}"
                >
                    {{ csrf_field() }}
                    <button
                        class="btn btn-primary mr-10 mr-4"
                    >{{ trans('cashier::messages.offline.claim_payment') }}</button>
                </form>

                <form id="cancelForm" method="POST" action="{{ action('SubscriptionController@cancelInvoice', [
                            'invoice_uid' => $invoice->id,
                ]) }}">
                    {{ csrf_field() }}
                    <a href="{{ action('App\Http\Controllers\User\SubscriptionController@index') }}">
                        {{ trans('cashier::messages.go_back') }}
                    </a>
                </form>
            </div>
            
        </div>
        <div class="col-md-2"></div>
        <div class="col-md-4">
            @include('invoices.bill', [
                'bill' => $invoice->getBillingInfo(),
            ])
        </div>
    </div>

</div>