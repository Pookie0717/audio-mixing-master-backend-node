<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap');

        body {
            font-family: 'Montserrat', sans-serif;
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
        <img style="display: block; margin-left: auto; width: 180px;" src="https://i.ibb.co/X7SgzDv/logo.png"
            alt="logo">
        <div class="show-desk" style="@media only screen and (max-width: 600px){ display: none; }">
            <table style="width: 100%;">
                <tr>
                    <td>
                        <p style="color:#ff9900; font-size: 25px; font-weight: 600; margin: 0;">
                            @if (isset($data->reservation_id))
                                Reservation Cancellation Request
                            @else
                                New Notification
                            @endif
                        </p>
                    </td>
                </tr>
            </table>

            <table style="width: 100%; padding: 0;">
                <tr>
                    <td>
                        <table class="inner-table " style="margin-top: 10px;">
                            <tr>
                                <td>
                                    Name:
                                </td>
                                <td>
                                    {{ $data->name }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Email
                                </td>
                                <td>
                                    {{ $data->email }}
                                </td>
                            </tr>
                            @if (isset($data->reservation_id))
                                <tr>
                                    <td>
                                        Reservation ID:
                                    </td>
                                    <td>
                                        PND{{ $data->reservation_id }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Reason
                                    </td>
                                    <td>
                                        {{ $data->reason }}
                                    </td>
                                </tr>
                            @else
                                <tr>
                                    <td>
                                        Message
                                    </td>
                                    <td>
                                        {{ $data->reason }}
                                    </td>
                                </tr>
                            @endif
                        </table>
                    </td>
                </tr>
            </table>




        </div>


        <!-- mobile design -->


        <div class="show-mobile" style="@media only screen and (max-width: 600px){ display: block; }">
            <table style="width: 100%;">
                <tr>
                    <td>
                        <p style="color:#ff9900; font-size: 20px; font-weight: 600; margin: 0; margin-top: 40px;">
                            @if (isset($data->reservation_id))
                                Reservation Cancellation Request
                            @else
                                New Notification
                            @endif
                        </p>
                    </td>
                </tr>
            </table>

            <table style="width: 100%; padding: 0;">
                <tr>
                    <td>
                        <p style="margin: 0; color: white; font-size: 12px;">
                            <strong>Name : </strong>
                            {{ $data->name }}
                        </p>
                        <p style="margin: 0; color: white; font-size: 12px;">
                            <strong>Email : </strong>
                            {{ $data->email }}
                        </p>
                        @if (isset($data->reservation_id))
                            <p style="margin: 0; color: white; font-size: 12px;">
                                <strong>Reservation ID: </strong>
                                PND{{ $data->reservation_id }}
                            </p>
                            <p style="margin: 0; color: white; font-size: 12px;">
                                <strong>Reason : </strong>
                                {{ $data->reason }}
                            </p>
                        @else
                            <p style="margin: 0; color: white; font-size: 12px;">
                                <strong>Message : </strong>
                                {{ $data->reason }}
                            </p>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>



</body>

</html>
