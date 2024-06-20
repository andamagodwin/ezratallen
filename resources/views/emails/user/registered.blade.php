@component('mail::message')

Hello Admin, 

A new user registered on Farmsell. Please view the registration details to approve their usage of the platform. The below are the users detail,

Name:{{$name}}

Email:{{$email}}

Phone:{{$phone}}


Regards.  

Farmsell.  

@include("layouts.footer_email")

@endcomponent