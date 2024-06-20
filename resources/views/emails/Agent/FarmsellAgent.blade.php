@component('mail::message')
Hi {{$data->name}},


{!!$data->body!!}

Kindest Regards,

Farmsell Team.

@include("layouts.app_store")
@include("layouts.footer_email")

@endcomponent