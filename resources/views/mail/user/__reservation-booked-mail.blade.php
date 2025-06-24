<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Template</title>
</head>

<body>

    <style>
        * {
            margin: 0px;
            padding: 0px;
            box-sizing: border-box;
        }

        body {
            background: #eeeeee;
            font-family: "Open Sans", sans-serif;
        }

        main {
            max-width: 600px !important;
            margin: 0 auto;
            background: #fff;
            min-height: 100vh;
        }

        .top-bar {
            width: 100%;
            background: #fff;
            border-top: 1px solid #f38030;
            border-bottom: 1px solid #f38030;
            padding: 30px 10px;
        }

        .top-bar svg {
            width: 100%;
            display: block;
            object-fit: contain;
            max-width: 150px;
            margin: 0 auto;
        }

        .top-bar>img {
            width: 100%;
            display: block;
            object-fit: contain;
            max-width: 150px;
            margin: 0 auto;
        }

        .template-text {
            width: 100%;
            text-align: center;
            padding: 40px 20px 25px;
        }

        .ico {
            width: 100%;
            max-width: 50px;
            margin: 0 auto 20px;
        }

        .ico svg,
        .ico img {
            width: 100%;
            display: block;
            object-fit: contain;
        }

        .template-text h2 {
            color: #47484b;
            font-size: 24px;
            line-height: 32px;
        }

        .main-table {
            width: 100%;
            padding-bottom: 40px;
        }

        .main-table table {
            width: 100%;
            border-spacing: 0px;
            padding: 0px 15px;
        }

        .main-table table thead th {
            border-top: 1px solid #d8d8d8;
            border-bottom: 1px solid #d8d8d8;
            word-break: break-word;
            padding: 10px;
            text-align: right;
            font-size: 14px;
            line-height: 19.6px;
            color: #615e5e;
            font-weight: 700;
            font-family: "Open Sans", sans-serif;
        }

        .main-table table thead th:first-child,
        .main-table table tbody tr td:first-child,
        .main-table table tfoot td:first-child {
            text-align: left;
        }

        .main-table table tbody tr td {
            word-break: break-word;
            padding: 10px;
            text-align: right;
            font-size: 14px;
            line-height: 19.6px;
            color: #615e5e;
            font-weight: 400;
            font-family: "Open Sans", sans-serif;
        }

        .main-table table tfoot td {
            word-break: break-word;
            padding: 10px;
            text-align: right;
            font-size: 14px;
            line-height: 19.6px;
            color: #615e5e;
            font-weight: 700;
            font-family: "Open Sans", sans-serif;
        }

        .main-table table tfoot tr:first-child td {
            border-top: 1px solid #d8d8d8;
        }

        .main-table table tfoot tr:last-child td {
            border-bottom: 1px solid #d8d8d8;
        }

        .info-row {
            width: 100%;
            padding: 0px 15px 40px;
            /* display: grid;
            grid-template-columns: 1fr 1fr; */
        }

        .info-col {
            padding: 0px 10px;
            line-height: 17px;
            display: inline-block;
            width: 100%;
            max-width: calc(50% - 10px);
            vertical-align: top;
        }

        .info-col>span {
            display: block;
            font-size: 14px;
            line-height: 19.6px;
            color: #615e5e;
            font-weight: 700;
            font-family: "Open Sans", sans-serif;
        }

        .info-col :is(p, a, p a) {
            font-size: 14px;
            line-height: 10px;
            color: #615e5e;
            font-weight: 400;
            font-family: "Open Sans", sans-serif;
            text-decoration: none;
        }

        .inf-top {
            width: 100%;
            grid-column: span 2;
            padding: 0px 10px 20px;
        }

        .inf-top p {
            font-size: 14px;
            line-height: 19px;
            color: #615e5e;
            font-weight: 400;
            font-family: "Open Sans", sans-serif;
            text-decoration: none;
        }
    </style>

    <main>
        <div class="top-bar">
            <img src="https://i.ibb.co/X7SgzDv/logo.png" alt="">

        </div>
        <div class="template-text">
            <div class="ico">
                <img src="https://i.ibb.co/PN6nyD7/tickk.png" alt="">
            </div>
            <h2 style="text-align: center">Thanks for Reservation!</h2>
        </div>
        <div class="main-table">
            <table>
                <thead>
                    <tr>
                        <th>Reservation ID</th>
                        <th>PND{{ $data->id }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Name</td>
                        <td>{{ $data->user->first_name }}</td>
                    </tr>
                    <!--<tr>
                        <td>Last name</td>
                        <td>{{ $data->user->last_name }}</td>
                    </tr>-->
                    <tr>
                        <td>Email</td>
                        <td>{{ $data->user->email }}</td>
                    </tr>
                    <tr>
                        <td>Phone number</td>
                        <td>{{ $data->user->phone_number }}</td>
                    </tr>
                    <tr>
                        <td>Vehicle make</td>
                        <td>{{ $data->car_make }}</td>
                    </tr>
                    <tr>
                        <td>Vehicle model</td>
                        <td>{{ $data->car_model }}</td>
                    </tr>
                    <tr>
                        <td>Vehicle color</td>
                        <td>{{ $data->car_color }}</td>
                    </tr>
                    <tr>
                        <td>Package </td>
                        <td>{{ $data->package_name }}</td>
                    </tr>
                    @foreach ($data->taxes as $tax)
                        <tr>
                            <td>{{ $tax->tax_name }}</td>
                            <td>
                                @if ($tax->tax_percentage != null)
                                    ${{ ($data->total * $tax->tax_percentage) / 100 }}
                                @else
                                    ${{ $tax->tax_amount }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td>${{ $data->total }}</td>
                    </tr>
                    <tr>
                        <td>Amount paid</td>
                        <td>${{ $data->total - $data->due_now }}</td>
                    </tr>
                    <tr>
                        <td>Pay at lot </td>
                        <td>${{ $data->due_now }}</td>

                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="info-row">
            <div class="info-col">
                <span>Phone:</span>
                <a href="tel:{{ $business_info->phone_number }}">{{ $business_info->phone_number }}</a>
            </div>
            <div class="info-col">
                <span>Location:</span>
                <a href="#" target="_blank">{{ $business_info->location }}</a>
            </div>
        </div>
    </main>

</body>

</html>
