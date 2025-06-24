<x-mail::message>

<div style="margin: 0px auto; width: 100%; text-align: center;">
    <img src="{{asset('img/logo.png')}}" alt="" srcset="" width="100px">
</div>
<p>Dear {{ $data['name'] }},</p>
<p>To complete your registration, please click the button below to verify your email address:</p>

<a href="{{ $data['url'] }}"
    style="
        background-color: #0d6efd;
        color: #fff;
        font-size: 15px;
        font-weight: bold;
        padding: 5px 12px;
        text-decoration: none;
        border-radius: 5px;
        display: inline-block;
    ">
    Verify
</a>
<p>If you did not initiate this request, please disregard this email.</p>
<p>Thank you for joining us!</p>

{{ env('APP_NAME') }}
</x-mail::message>