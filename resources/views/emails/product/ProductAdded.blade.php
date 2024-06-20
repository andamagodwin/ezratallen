@component('mail::message')
Hi {{$user_name}},

Congratulations!   😇 😇 😇! 

Excellent job well done. We are pleased to inform you that your product {{$product}} has been successfully uploaded onto <a href="farmsell.org">Farmsell</a> marketplace platform. You can now click here to see the product under your profile. Our team is now reviewing your product and you will receive a notification upon approval. Please keep checking your email and phone for a notification regarding approval of your product. If you don’t get a notification within 60 minutes, please reach us through <a href="https://api.whatsapp.com/send/?phone=256742500300&text&app_absent=0https://farmsell.org/selfhelp">WhatsApp</a>,<a href="http://www.instagram.com/farmsell33"> Messenger </a> or <a href="#">Skype </a>]for quick response.   

Make sure that you have provided the most up-to-date phone and email address. If not update your contact information under your profile.  You might want to watch these videos on <a href="https://www.youtube.com/watch?v=FHIZHUFg7_U&t=7s">How to edit Your Profile</a> and <a href="https://www.youtube.com/watch?v=6EX-09vMbJQ">How to Edit Your Product</a> at <a href="farmsell.org">Farmsell</a>.  

Here is a SPECIAL OFFER exclusively for you. Just Refer and Win.  We are currently rewarding people who refer their friends or relatives to download and rate Farmsell App at <a href="https://play.google.com/store/apps/details?id=com.farmsell"> Google Play</a>. Please act NOW to grab your fortune! You can also join the campaign to WIN great prizes by adding a product to sell at  <a href="farmsell.org"> Farmsell</a> or buying a product at <a href="farmsell.org">Farmsell</a>.  

You can also subscribe to our  <a href="{{env('youtube')}}"> YouTube </a>  channel to watch our latest events. Would you please follow us <a href=" {{env('twitter')}}">Twitter</a>, <a href=" {{env('fb')}}"> Facebook</a>, <a href=" {{env('instagram')}}">Instagram</a>, <a href=" {{env('linkedin')}}"> LinkedIn </a>  to see interesting latest developments at Farmsell including promotions or prizes. Don’t Miss the opportunity.  

 
Cheers,  

Farmsell Team. 

@include("layouts.app_store")
@include("layouts.footer_email")

@endcomponent