<x-mail::message>

<div style="margin: 0px auto; width: 100%; text-align: center;">
    <img src="{{asset('img/logo.png')}}" alt="" srcset="" width="100px">
</div>
# Welcome to {{ env('APP_NAME') }}
Hi {{'engName'}},<br>
<p>New Revision request is posted you can check .</p>
<p>Order ID # {{$data['order_id']}}</p>
<p>Service ID # {{$data['service_id']}}</p>



{{ env('APP_NAME') }}


</x-mail::message>