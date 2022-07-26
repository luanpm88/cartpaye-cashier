<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

@include('user.subscription._tmp_nav')

<div class="container pt-5">    
    
    <div class="row">
			<div class="col-md-6">
				<p>
					{!! trans('cashier::messages.stripe.intro') !!}
				</p>

                @if (count($errors) > 0)
                    <!-- Form Error List -->
                    <div class="alert alert-danger alert-noborder alert-dismissible">        
                        <ul class="m-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
			</div>
		</div>
        

		<h3>{{ trans('cashier::messages.payment.options') }}</h3>

        <form enctype="multipart/form-data" action="{{ $gateway->getSettingsUrl() }}" method="POST" class="form-validate-jquery">
            {{ csrf_field() }}
            <div class="row">
                <div class="col-md-6">

                    <div class="mb-3">
                        <label class="form-label">Publishable Key</label>
                        <input type="text" name="publishable_key" class="form-control" value="{{ $gateway->getPublishableKey() }}" required="">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Secret Key</label>
                        <input type="text" name="secret_key" class="form-control" value="{{ $gateway->getSecretKey() }}" required="">
                    </div>
                </div>
            </div>


            <hr>
            <div class="text-left">
                @if ($gateway->isActive())
                    @if (!\Acelle\Library\Facades\Billing::isGatewayEnabled($gateway))
                        <input type="submit" name="enable_gateway" class="btn btn-primary me-1" value="{{ trans('cashier::messages.save_and_enable') }}" />
                        <button class="btn btn-default me-1">{{ trans('messages.save') }}</button>
                    @else
                        <button class="btn btn-primary me-1">{{ trans('messages.save') }}</button>
                    @endif
                @else
                    <input type="submit" name="enable_gateway" class="btn btn-primary me-1" value="{{ trans('cashier::messages.connect') }}" />
                @endif
                <a class="btn btn-default" href="{{ action('App\Http\Controllers\Admin\PaymentController@index') }}">{{ trans('messages.cancel') }}</a>
            </div>

        </form>
</div>