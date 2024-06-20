@component('mail::message')

Hi Farmsell Administrator, You received a message from a customer,


<p>
Name:{{$user_name}}
</p>

<p>
Email:{{$email}}
</p>

<p>
Message:{{$message}}
</p>


Thank you,<br>

Farmsell Support Team<br>

{{ config('app.name') }}
@endcomponent