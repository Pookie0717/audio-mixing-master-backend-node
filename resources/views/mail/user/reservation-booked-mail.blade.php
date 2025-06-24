<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap');
        body {
            font-family: 'Montserrat', sans-serif ;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        table {
            vertical-align: top;
        }
        
        td {
            vertical-align: top;
        }
        
        .inner-table {
            width: 100%;
            box-sizing: border-box;
        }
        
        .inner-table tr td {
            padding: 5px 10px;
            color: white;
            border-bottom: 1px solid white;
            font-size: 12px;
        }
        
        .inner-table tr:first-child td {
            border-top: 1px solid #00ffff;
        }
        
        .inner-table tr td:first-child {
            color: #ff9900;
            border-left: 1px solid white;
            width: 40%;
        }
        
        .inner-table tr td:last-child {
            border-left: 1px solid white;
            border-right: 1px solid white;
        }
        
        .show-desk {
            width: 100%;
            display: block;
        }
        
        .show-mobile {
            width: 100%;
            display: none;
        }

        
        @media only screen and (max-width: 600px) {
            .show-desk {
                display: none;
            }
            .show-mobile {
                display: block;
            }
        }
    </style>

</head>

<body>
    <div style="background-color: black; max-width: 800px; width: 100%; margin: 0 auto; padding: 40px  20px;">
        <img style="display: block; margin-left: auto; width: 180px;" src="https://i.ibb.co/X7SgzDv/logo.png" alt="logo">
        <div class="show-desk" style="@media only screen and (max-width: 600px){ display: none; }">
            <table style="width: 100%;">
                <tr>
                    <td>
                        <p style="color:#ff9900; font-size: 25px; font-weight: 600; margin: 0;">Order Confirmation #: PND{{ $data->id }}
                        </p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p style="color: white; margin: 0; margin-top: 10px; padding-top: 10px; font-size: 14px; border-top: 1px solid white;">
                            Congratulations! Your parking reservation at Park N Depart (LGA) has been confirmed. Please see the attached reservation details below and present them to the parking lot attendants upon check-in at the facility.
                        </p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p style="color:#ff9900; font-size: 20px; font-weight: 600; margin: 0; margin-top: 20px; margin-bottom: 10px;">
                            <u> Reservation Summary:</u>
                        </p>
                    </td>
                </tr>
            </table>
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%;">
                        <table class="inner-table">
                            <tr>
                                <td>
                                    Name:
                                </td>
                                <td>
                                    {{ $data->user_name }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Phone Number:
                                </td>
                                <td>
                                    {{ $data->user->phone_number }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Email:
                                </td>
                                <td>
                                   
                                  {{ $data->user->email }}
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 50%;">
                        <table class="inner-table" style="margin-left: 10px;">
                            <tr>
                                <td>
                                    Subtotal
                                </td>
                                <td>
                                    ${{ number_format($data->total,2) }}
                                    
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Paid:
                                </td>
                                <td>
                                    ${{ number_format($data->total - $data->due_now,2) }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Pay at Lot:
                                </td>
                                <td>
                                    ${{ number_format($data->due_now,2) }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="width: 50%;">
                        <table class="inner-table" style="margin-top: 10px;">
                            <tr>
                                <td>
                                    Vehicle
                                </td>
                                <td>
                                    {{ $data->car_make }} - {{ $data->car_model }} - {{ $data->car_color }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Package:
                                </td>
                                <td>
                                    {{ $data->package_name }}
                                </td>
                            </tr>

                        </table>
                    </td>
                    <td style="width: 50%; ">
                        <table class="inner-table" style="margin-top: 10px; margin-left: 10px;">
                            <tr>
                                <td style="border: none !important; padding: 0;">
                                    <h5 style="color: #ff9900; margin: 0;padding: 0; font-size: 16px; border-top:1px solid white ; padding-top: 10px;">
                                        Park ‘N Depart - LGA</h5>
                                    <p style="color: white;  font-size: 14px; max-width: 200px; margin: 0; margin-top: 5px;">
                                        {{ $business_info->location }} {{ $business_info->phone_number }}
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="width: 50%;">
                        <table class="inner-table" style="margin-top: 10px;">
                            <tr>
                                <td>
                                    Check-in:
                                </td>
                                <td>
                                    {{ date('m/d/Y - h:i A',strtotime($data->check_in)) }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Check-out:
                                </td>
                                <td>
                                    {{ date('m/d/Y - h:i A',strtotime($data->check_out)) }}
                                </td>
                            </tr>

                        </table>
                    </td>
                    <td style="width: 50%; ">
                        <table class="inner-table" style="margin-top: 10px; margin-left: 10px;">
                            <tr>
                                <td style="border: none !important; padding: 0;">
                                    <p style="color: #999999; margin: 0; font-size: 14px; padding-top: 5px; border-top: 1px solid #999999;">
                                        PLEASE EMAIL <a style="color:#ff9900; text-decoration: none;" href="mailto:SUPPORT@PARKNDEPART.COM" >SUPPORT@PARKNDEPART.COM</a> FOR ANY QUESTIONS OR CONCERNS OR CALL {{ $business_info->phone_number }}.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <div style="margin: 30px 0; border: 1px solid #999999;"></div>

            <table style="width: 100%; padding: 0;">
                <tr>
                    <td>
                        <h5 style="margin: 0; color:#ff9900; font-size: 14px;">Shuttle Information: </h5>
                        <p style="margin: 0; color: white; font-size: 12px;">
                            We provide a complimentary shuttle that runs every 30 minutes. Maximum of 4 passengers and 2 standard suitcases per reservation. No pets are allowed unless in a travel carrier.
                        </p>
                        <h5 style="margin: 0; color:#ff9900; font-size: 14px; margin-top: 20px;">Directions: </h5>
                        <p style="margin: 0; color: white; font-size: 12px;">
                            To get directions, please use Google Maps and enter the address: <span style="color: #ff9900;">{{ $business_info->location }}</span>. Our location is adjacent to the BQE Fitness parking area.
                        </p>
                        <h5 style="margin: 0; color:#ff9900; font-size: 14px; margin-top: 20px;">Contact Us: </h5>
                        <p style="margin: 0; color: white; font-size: 12px;">
                            To contact us please click this
                            <a style="color:#ff9900; text-decoration: none;" href="https://parkndepart.com/contact" target="_blank">link</a> and send an email through our <a style="color:#ff9900; text-decoration: none;" href="https://parkndepart.com/contact"
                                target="_blank"> Contact Us page</a>. Please follow the same link for <a style="color:#ff9900; text-decoration: none;" href="https://parkndepart.com/contact" target="_blank"> cancellation</a> requests.
                        </p>
                    </td>
                </tr>
            </table>
        </div>


        <!-- mobile design -->


        <div class="show-mobile" style="@media only screen and (max-width: 600px){ display: block; }">
            <table style="width: 100%;">
                <tr>
                    <td>
                        <p style="color:#ff9900; font-size: 20px; font-weight: 600; margin: 0; margin-top: 40px;">Order Confirmation #: PND{{ $data->id }}
                        </p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p style="color: white; margin: 0; margin-top: 10px; padding-top: 10px; font-size: 12px; border-top: 1px solid white;">
                            Congratulations! Your parking reservation at Park N Depart (LGA) has been confirmed. Please see the attached reservation details below and present them to the parking lot attendants upon check-in at the facility.
                        </p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p style="color:#ff9900; font-size: 18px; font-weight: 600; margin: 0; margin-top: 20px; margin-bottom: 10px;">
                            <u> Reservation Summary:</u>
                        </p>
                    </td>
                </tr>
            </table>
            <table style="width: 100%;">
                <tr>
                    <td style="width: 100%;">
                        <table class="inner-table">
                            <tr>
                                <td>
                                    Name:
                                </td>
                                <td>
                                    {{ $data->user_name }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Phone Number:
                                </td>
                                <td>
                                    {{ $data->user->phone_number }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Email:
                                </td>
                                <td>
                                    {{ $data->user->email }}
                                </td>
                            </tr>
                        </table>
                    </td>

                </tr>
                <tr>
                    <td style="width: 100%;">
                        <table class="inner-table " style="margin-top: 10px;">
                            <tr>
                                <td>
                                    Subtotal
                                </td>
                                <td>
                                    ${{ number_format($data->total,2) }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Paid:
                                </td>
                                <td>
                                    ${{ number_format($data->total - $data->due_now,2) }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Pay at Lot:
                                </td>
                                <td>
                                    ${{ number_format($data->due_now,2) }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="width: 100%;">
                        <table class="inner-table" style="margin-top: 10px;">
                            <tr>
                                <td>
                                    Vehicle
                                </td>
                                <td>
                                    {{ $data->car_make }} - {{ $data->car_model }} - {{ $data->car_color }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Package:
                                </td>
                                <td>
                                    {{ $data->package_name }}
                                </td>
                            </tr>

                        </table>
                    </td>

                </tr>

                <tr>
                    <td style="width: 100%;">
                        <table class="inner-table" style="margin-top: 10px;">
                            <tr>
                                <td>
                                    Check-in:
                                </td>
                                <td>
                                    {{ date('m/d/Y - h:i A',strtotime($data->check_in)) }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Check-out:
                                </td>
                                <td>
                                    {{ date('m/d/Y - h:i A',strtotime($data->check_out)) }}
                                </td>
                            </tr>

                        </table>
                    </td>

                </tr>
                <tr>
                    <td style="width: 100%; ">
                        <table class="inner-table" style="margin-top: 10px;">
                            <tr>
                                <td style="border: none !important; padding: 0;">
                                    <h5 style="color: #ff9900; margin: 0;padding: 0; font-size: 16px; border-top:1px solid white ; padding-top: 10px;">
                                        Park ‘N Depart - LGA</h5>
                                    <p style="color: white;  font-size: 14px; max-width: 200px; margin: 0; margin-top: 5px;">
                                        {{ $business_info->location }} {{ $business_info->phone_number }}
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="width: 100%; ">
                        <table class="inner-table" style="margin-top: 10px; ">
                            <tr>
                                <td style="border: none !important; padding: 0;">
                                    <p style="color: #999999; margin: 0; font-size: 14px; padding-top: 5px; border-top: 1px solid #999999;">
                                        PLEASE EMAIL SUPPORT@PARKNDEPART.COM FOR ANY QUESTIONS OR CONCERNS OR CALL {{ $business_info->phone_number }}.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <div style="margin: 10px 0; border: 1px solid #999999;"></div>

            <table style="width: 100%; padding: 0;">
                <tr>
                    <td>
                        <h5 style="margin: 0; color:#ff9900; font-size: 14px;">Shuttle Information: </h5>
                        <p style="margin: 0; color: white; font-size: 12px;">
                            We provide a complimentary shuttle that runs every 30 minutes. Maximum of 4 passengers and 2 standard suitcases per reservation. No pets are allowed unless in a travel carrier.
                        </p>
                        <h5 style="margin: 0; color:#ff9900; font-size: 14px; margin-top: 20px;">Directions: </h5>
                        <p style="margin: 0; color: white; font-size: 12px;">
                            To get directions, please use Google Maps and enter the address: <span style="color: #ff9900;">{{ $business_info->location }}</span>. Our location is adjacent to the BQE Fitness parking area.
                        </p>
                        <h5 style="margin: 0; color:#ff9900; font-size: 14px; margin-top: 20px;">Contact Us: </h5>
                        <p style="margin: 0; color: white; font-size: 12px;">
                            To contact us please click this
                            <a style="color:#ff9900; text-decoration: none;" href="https://parkndepart.com/contact" target="_blank">link</a> and send an email through our <a style="color:#ff9900; text-decoration: none;" href="https://parkndepart.com/contact"
                                target="_blank"> Contact Us page</a>. Please follow the same link for <a style="color:#ff9900; text-decoration: none;" href="https://parkndepart.com/contact" target="_blank"> cancellation</a> requests.
                        </p>
                    </td>
                </tr>
            </table>
        </div>
    </div>



</body>

</html>