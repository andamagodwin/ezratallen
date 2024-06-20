@component('mail::message')
# Introduction

Your order was succesfully shipped 

@component('mail::button', ['url' => ''])
Button Text
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
