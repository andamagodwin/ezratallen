@component('mail::message')

Dear {{$user_name}},

Here is great news for you. Your product {{$product_name}} which you recently added to <a href="https://farmsell.org/">Farmsell </a> marketplace has been approved by Farmsell Team. You can now check under “recent products” at Farmsell home page to see your product.

Your product has become visible to thousands of our happy buyers who will be contacting you directly for purchase. Make sure that you have provided the most up-to-date phone and email address. If not update your contact information under your profile. You might want to watch these videos on “How to edit Your Profile” and “How to Edit Your Product” at <a href="https://farmsell.org/">Farmsell.</a>

For best selling, please ensure your product and profile have the latest most accurate information. Remember that you can upload more than one picture for your product to attract buyers.

For quick selling, invite {{$product_url}} your friends or relatives to view, rate and comment on your product. Usually products that have more views, best rating and positive comments are 10 times more attractive to buyers. You can also click here {{$product_url}} to share the product on all your social media platforms to increase buying rates. Regularly visit your product page at <a href="https://farmsell.org/">Farmsell</a> to actively engage with your visitors and potential buyers who are visiting/viewing your product.


You can also subscribe to our <a href="{{env('youtube')}}"> YouTube </a> channel to watch our latest events. Would you please follow us <a href=" {{env('twitter')}}">Twitter</a>, <a href=" {{env('fb')}}"> Facebook</a>, <a href=" {{env('instagram')}}">Instagram</a>, <a href=" {{env('linkedin')}}"> LinkedIn </a> to see interesting latest developments at Farmsell including promotions or prizes. Don’t Miss the opportunity.


Cheers,


Farmsell Team.

@include("layouts.app_store")
@include("layouts.footer_email")

@endcomponent