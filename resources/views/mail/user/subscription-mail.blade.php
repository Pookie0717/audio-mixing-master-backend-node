<x-mail::message>
    <div style="margin: 0px auto; width: 100%; text-align: center;">
        <img src="{{asset('img/logo.png')}}" alt="Company Logo" width="100px">
    </div>
    <p>Your 10% Off Coupon Code!</p>
    <p>Thank you for subscribing!</p>
    <br>
    <p><strong>Get 10% Off Your Next Purchase!</strong></p>
    <p>Here’s your exclusive coupon code: <strong>OFF10</strong></p>
    <p>To redeem, simply copy the code and paste it at checkout to receive your discount. Enjoy 10% off on any of our services and include as many songs as you’d like for this first order..</p>
    <br>
    <p>Note: This code is valid for a single use only. Don’t miss the chance to maximize your savings on your initial purchase.</p>
    <center>
        <a href="" style="background-color:#34eb40; padding:5px; border-radius:2px; color:black; margin:5px;">Use Coupon Now</a>
        <p>{{ env('APP_NAME') }}</p>
    </center>
</x-mail::message>
