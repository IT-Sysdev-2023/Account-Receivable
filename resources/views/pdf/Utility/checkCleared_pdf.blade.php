<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $header['Check Type'] }} Clearing Slip</title>
    <style>
        @page {
            margin: 10mm !important;
            size: A4 portrait;
        }

        <style>* {
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            background-color: #ffffff;
            color: #000000;
            font-size: 10px;
        }

        .top-right {
            position: absolute;
            top: 0;
            right: 0;
            text-align: right;
        }

        .top-right div {
            margin-bottom: 2px;
        }

        .header {
            text-align: left;
            margin-top: 0;
        }

        .header h2 {
            margin: 0;
            font-size: 16px;
        }

        .header small {
            font-size: 12px;
        }

        .title {
            text-align: left;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 6px;
            margin-top: 14px;
            margin-right: 0;
            margin-left: 0;
        }

        .container {
            max-width: 100%;
            background: #ffffff;
            border-radius: 8px;
        }

        .reprint {
            margin-top: 0;
            text-align: right;
            margin-bottom: 0;
        }

        .reprint p {
            font-size: 12px;
            color: #000000;
            margin-top: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            margin-top: 0;
        }

        th,
        td {
            padding: 4px 12px;
            text-align: left;
            vertical-align: top;
        }

        th {
            border-top: 1px solid black;
            border-bottom: 1px solid black;
            font-weight: bold;
        }

        .center {
            text-align: center;
        }

        .no-border td {
            border: none;
        }


        .signatory-table {
            width: 100%;
            border-collapse: collapse;
        }

        .signatory-table td {
            text-align: justify;
            padding: 10px;
            font-size: 10px;
            color: #000000;
        }

        .signatory-table div {
            margin: 0;
        }

        .footer {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #000000;
            gap: 20px;
            text-align: center;
        }

        .footer p {
            margin: 0;
            display: inline-block;
            white-space: nowrap;
        }

        .note {
            font-size: 10px;
            color: #e74c3c;
        }

        .run-date {
            font-size: 10px;
            color: #000000;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .textRight {
            text-align: right;
        }

        /* .col-customer_name {
            text-align: left;
            width: 20%;
        } */

        .col-document_no {
            text-align: left;
            width: 11%;
        }

        .col-payment_no {
            text-align: left;
            width: 14%;
        }

        .col-check_no {
            text-align: left;
            width: 15%;
        }

        .col-due_date {
            text-align: left;
            width: 14%;
        }

        .col-amount {
            text-align: center;
            width: 14%;
        }

        .col-status {
            text-align: left;
            width: 12%;
        }

        .col-remarks {
            text-align: left;
            width: 20%;
        }

        .col-amount-td {
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="top-right">
            @if ($reprint_count > 0)
                <div><strong>Reprinted Copy No : </strong>{{ $reprint_count }}</div>
            @endif
            <div><strong>Document No : </strong> {{ $header['Document No'] }}</div>
            <div><strong>Transaction Date : </strong> {{ $header['Transaction Date'] }}</div>
            <div><strong>Clearing Date : </strong> {{ $header['Clearing Date'] }}</div>
        </div>

        <div class="header">
            <h2>BRDR BIL-AG003</h2>
            <small>Accounts Receivable System - Local</small>
        </div>

        <div class="title" style="border-bottom: 1px solid black; padding-bottom: 4px;">
            {{ $header['Check Type'] }} Clearing Slip
        </div>

        <p><strong>CUSTOMER NAME : </strong>{{ $header['Customer Name'] }}</p>

        <table>
            <thead>
                <tr>
                    <th class="col-document_no">DOC. NO.</th>
                    <th class="col-payment_no">PAYMENT NO.</th>
                    <th class="col-check_no">CHECK NO.</th>
                    <th class="col-due_date">DUE DATE</th>
                    <th class="col-amount">AMOUNT</th>
                    <th class="col-status">STATUS</th>
                    <th class="col-remarks">REMARKS</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $item['document_no'] }}</td>
                        <td>{{ $item['payment_no'] }}</td>
                        <td>{{ $item['check_no'] }}</td>
                        <td>{{ $item['due_date'] }}</td>
                        <td class="col-amount-td">{{ $item['amount'] }}</td>
                        <td>{{ $item['status'] }}</td>
                        <td>{{ $item['remarks'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
