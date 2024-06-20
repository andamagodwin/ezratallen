@component('mail::message')
Hello {{$name}},

{!!$body!!}



@include("layouts.app_store")
@include("layouts.footer_email")


@endcomponent