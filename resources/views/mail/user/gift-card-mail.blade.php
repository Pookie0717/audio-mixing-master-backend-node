<x-mail::message>

<div style="margin: 0px auto; width: 100%; text-align: center;">
    <img src="{{asset('img/logo.png')}}" alt="" srcset="" width="100px">
</div>
# Welcome to {{ env('APP_NAME') }}
Hi {{ $data['name'] }},<br>
<b>{{$data['message']}}</b>.
<h1>{{$data['code']}}</h1>


{{ env('APP_NAME') }}


</x-mail::message>