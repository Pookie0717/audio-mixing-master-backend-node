<x-mail::message>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header img {
            max-width: 150px;
        }
        .content {
            font-size: 16px;
            color: #333;
        }
        .content h1 {
            font-size: 20px;
            color: #222;
        }
        .content p {
            line-height: 1.6;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button-container a {
            background-color: #1dbf73;
            color: #ffffff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #888;
            margin-top: 20px;
        }
        .footer a {
            color: #888;
            text-decoration: none;
        }
        .footer .social-icons {
            margin: 10px 0;
        }
        .footer .social-icons img {
            width: 24px;
            margin: 0 5px;
        }
    </style>
    <div class="email-container">
        <div class="header">
            <img src="{{ asset('img/logo.png') }}" alt="Company Logo">
        </div>
        <div class="content">
            <p>Hi {{ $data['name'] }},</p>
            <p>Your revision request has been successfully received, and our engineering team is actively working on it.</p>
            <p>Order ID: {{$data['order_id']}}</p>
            <p>We’ll keep you updated on the progress and notify you promptly once the revision is completed.</p>
        </div>
        <div class="button-container">
            <a href="{{ $data['url'] }}" target="_blank">View revision request</a>
        </div>
        <div class="footer">
            <p>Thank you for your patience and trust in our team!</p>
            <p>{{ env('APP_NAME') }}</p>
            
            <div class="social-icons">
                <a href="#"><img src="https://via.placeholder.com/24" alt="Facebook"></a>
                <a href="#"><img src="https://via.placeholder.com/24" alt="Twitter"></a>
                <a href="#"><img src="https://via.placeholder.com/24" alt="Instagram"></a>
            </div>
        </div>
    </div>
</x-mail::message>
