<x-mail::message>
# Welcome to {{ env('APP_NAME') }}
Hi {{ $data['name'] }},
Your account has been created.

## Login Cradentials
-------------
#### Your One Time Password : {{ $data['password'] }}

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
    Login
</a>

Thanks,

{{ env('APP_NAME') }}
</x-mail::message>