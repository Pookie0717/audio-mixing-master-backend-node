<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap');
        @media print {
            @page {
                size: US-Letter;
                margin: 0;

                @top-left {
                    content: element(header);
                }

                @bottom-left {
                    content: element(footer);
                }
            }

            body {
                margin: 0 !important;
                padding: 0;
                color: black;
                font-family: 'Montserrat', sans-serif;
                font-size: 10pt;
            }

            a {
                color: inherit;
                text-decoration: none;
            }

            hr {
                margin: 1cm 0;
                height: 0;
                border: 0;
                border-top: 1mm solid #f38030;
            }

            header {
                padding: 0 2cm;
                position: running(header);
                background-color: #f3813033;
            }

            header .headerSection {
                display: flex;
                justify-content: space-between;
            }

            header .headerSection:first-child {
                padding-top: .5cm;
            }

            header .headerSection:last-child {
                padding-bottom: .5cm;
            }

            header .headerSection div:last-child {
                width: 35%;
            }

            header .logoAndName {
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            header .logoAndName svg {
                width: 1.5cm;
                height: 1.5cm;
                margin-right: .5cm;
            }

            header .headerSection .invoiceDetails {
                padding-top: .5cm;
            }

            header .headerSection h3 {
                margin: 0 .75cm 0 0;
                color: #f38030;
            }

            header .headerSection div:last-of-type h3:last-of-type {
                margin-top: .5cm;
            }

            header .headerSection div p {
                margin-top: 2px;
            }

            header h1,
            header h2,
            header h3,
            header p {
                margin: 0;
            }

            header .invoiceDetails,
            header .invoiceDetails h2 {
                text-align: right;
                font-size: 1em;
                text-transform: none;
            }

            header h2,
            header h3 {
                text-transform: uppercase;
            }

            header hr {
                margin: 1cm 0 .5cm 0;
            }

            main table {
                width: 100%;
                border-collapse: collapse;
            }

            main table thead th {
                height: 1cm;
                color: #f38030;
            }

            main table thead th:nth-of-type(2),
            main table thead th:nth-of-type(3),
            main table thead th:last-of-type {
                width: 2.5cm;
            }

            main table tbody td {
                padding: 2mm 0;
            }

            main table thead th:last-of-type,
            main table tbody td:last-of-type {
                text-align: right;
            }

            main table th {
                text-align: left;
            }

            main table.summary {
                width: calc(40% + 2cm);
                margin-left: 60%;
                margin-top: .5cm;
            }

            main table.summary tr.total {
                color: black;
                font-weight: bold;
                background-color: #f38030;
            }
            
            main table.summary tr.total td{
                color: black;
            }

            main table.summary th {
                padding: 4mm 0 4mm 1cm;
            }

            main table.summary td {
                padding: 4mm 2cm 4mm 0;
                border-bottom: 0;
            }

            aside {
                -prince-float: bottom;
                padding: 0 2cm .5cm 2cm;
            }

            aside>div {
                display: flex;
                justify-content: space-between;
            }

            aside>div>div {
                width: 45%;
            }

            aside>div>div ul {
                list-style-type: none;
                margin: 0;
            }

            footer {
                height: 3cm;
                line-height: 3cm;
                padding: 0 2cm;
                position: running(footer);
                background-color: #BFC0C3;
                font-size: 8pt;
                display: flex;
                align-items: baseline;
                justify-content: space-between;
            }

            footer a:first-child {
                font-weight: bold;
            }

            .logoAndName img {
                width: 120px;
                height: 60px;
                object-fit: contain;
            }

            .list-table td {
                border-bottom: 1px solid #f38030;
                padding: 20px 5px;
            }
        }
    </style>
</head>

<body>

    <header>
        <div class="headerSection">

            <div class="logoAndName">
                <img src="https://i.ibb.co/Q6ShwwV/image-267.png" alt="logo">
            </div>

            <div class="invoiceDetails">
                <h2>Report Generated on</h2>
                <p>
                    07 March 2021
                </p>
            </div>
        </div>

        <hr />

    </header>

    <main>
        <table class="list-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User Info</th>
                    <th style="text-align: center;">Check in</th>
                    <th style="text-align: center;">Check Out</th>
                    <th>Vehical Make</th>
                    <th>Vehical Model</th>
                    <th>Vehical color</th>
                    <th>Subtotal</th>
                    <th>Taxes</th>
                    <th>Discount</th>
                    <th>Total</th>
                    <th>Deposit</th>
                    <th>Due at lot</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>PND928</td>
                    <td>Hopper<br /> abc@gmail.com<br /> 0982717627717</td>
                    <td style="text-align: center;">5/6/2024</td>
                    <td style="text-align: center;">5/7/2024</td>
                    <td>2019</td>
                    <td>Toyota</td>
                    <td>Pink</td>
                    <td>$59</td>
                    <td>$59</td>
                    <td>$10</td>
                    <td>$10</td>
                    <td>$10</td>
                    <td>$100</td>
                </tr>
            </tbody>
        </table>

        <table class="summary">
            <tr class="total">
                <th>Total
                </th>
                <td>$12,000.00</td>
            </tr>
        </table>
    </main>

</body>

</html>
