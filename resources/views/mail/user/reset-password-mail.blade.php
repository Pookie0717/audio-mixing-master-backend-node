<x-mail::message>
    <div style="margin: 0px auto; width: 100%; text-align: center;">
        <img src="{{asset('img/logo.png')}}" alt="" srcset="" width="100px">
    </div>
# Hi {{ $data['name'] }}

<p>It looks like you've requested a password reset. If you did not make this request, please disregard this email.</p>
<p>To reset your password, simply click the link below:</p>
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
    Reset Password
</a>

Thanks,<br>
{{ env('APP_NAME') }}
</x-mail::message>