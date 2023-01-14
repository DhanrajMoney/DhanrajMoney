@extends($activeTemplate.'layouts.master')
@section('content')
<div class="container pt-100 pb-100">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card custom--card">
                <div class="card-header">
                    <h5 class="card-title text-center">@lang('Stripe Storefront')</h5>
                </div>
                <div class="card-body p-5">
                    <form action="{{$data->url}}" method="{{$data->method}}">
                        <ul class="list-group list-group-flush text-center">
                            <li class="list-group-item d-flex flex-wrap justify-content-between">
                                @lang('You have to pay')
                                <strong>{{showAmount($deposit->final_amo)}} {{__($deposit->method_currency)}}</strong>
                            </li>
                            <li class="list-group-item d-flex flex-wrap justify-content-between">
                                @lang('You will get')
                                <strong>{{showAmount($deposit->amount)}}  {{__($general->cur_text)}}</strong>
                            </li>
                        </ul>
                         <script src="{{$data->src}}"
                            class="stripe-button"
                            @foreach($data->val as $key=> $value)
                            data-{{$key}}="{{$value}}"
                            @endforeach
                        >
                        </script>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        (function ($) {
            "use strict";
            $('.stripe-button-el').addClass("btn btn--base w-100 mt-4").removeClass('stripe-button-el');
            $('button[type="submit"]').text("Pay Now");
        })(jQuery);
    </script>
@endpush
