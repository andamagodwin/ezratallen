@component('mail::message')
Hello {{$name}},

{{$body}}

<h1>{{$code}}</h1>

@include("layouts.app_store")
@include("layouts.footer_email")

@endcomponent