@extends('layouts.backend')

@section('title', trans('cashier::messages.offline'))

@section('page_script')
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>

    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/tinymce/tinymce.min.js') }}"></script>
        
    <script type="text/javascript" src="{{ URL::asset('js/editor.js') }}"></script>
@endsection

@section('page_header')

    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
            <li><a href="{{ action("Admin\PaymentController@index") }}">{{ trans('messages.payment_gateways') }}</a></li>
            <li class="active">{{ trans('messages.update') }}</li>
        </ul>
        <h1>
            <span class="text-semibold"><i class="icon-credit-card2"></i> {{ trans('cashier::messages.offline') }}</span>
        </h1>
    </div>

@endsection

@section('content')
		<div class="row">
			<div class="col-md-6">
				<p>
					{!! $gateway->getDescription() !!}
				</p>
			</div>
		</div>
			
		<h3>{{ trans('messages.payment.options') }}</h3>

        <form enctype="multipart/form-data" action="{{ $gateway->getSettingsUrl() }}" method="POST" class="form-validate-jquery">
            {{ csrf_field() }}
            <div class="row">
                <div class="col-md-6">
                    @include('helpers.form_control', [
                        'type' => 'textarea',
                        'class' => 'setting-editor',
                        'name' => 'payment_instruction',
                        'value' => $gateway->getPaymentInstruction(),
                        'label' => trans('cashier::messages.offline.payment_instruction'),
                        'help_class' => 'payment',
                        'rules' => ['payment_instruction' => 'required'],
                    ])
                </div>
            </div>


            <hr>
            <div class="text-left">
                @if (!Acelle\Model\Setting::get('payment.' . $gateway->getName()))
                    <input type="submit" class="btn btn-mc_primary bg-teal  mr-5" name="save_and_enable" value="{{ trans('messages.payment.connect') }}" />
                @else
                    <button class="btn btn-mc_primary mr-5">{{ trans('messages.save') }}</button>
                @endif
                <a class="btn btn-mc_default" href="{{ action('Admin\PaymentController@index') }}">{{ trans('messages.cancel') }}</a>
            </div>

        </form>

@endsection