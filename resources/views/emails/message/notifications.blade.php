@component('mail::message')
# {{$user_name}} has sent you a message

{{$message_body}}


@include("layouts.app_store")
@include("layouts.footer_email")
@endcomponent