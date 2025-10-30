<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Temporary Delivery Slip Summary</title>
    <style>
        body {
            margin-bottom: 0 !important;
            padding: 0 !important;
            max-width: 100%;
            box-sizing: border-box;
            font-family: sans-serif;
            font-size: 8px;
        }

        .top-right {
            position: absolute;
            top: 0;
            right: 0;
            text-align: right;
            font-size: 9px;
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

        .header h3 {
            margin: 0;
            font-weight: normal;
            font-size: 12px;
        }

        .header small {
            font-size: 12px;
        }

        .title {
            text-align: left;
            font-weight: bold;
            font-size: 12px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            table-layout: fixed;
        }

        .table th {
            padding: 4px;
            word-wrap: break-word;
        }

        .table td {
            padding: 4px;
            word-wrap: break-word;

        }

        .table th {
            border-top: 1px solid black;
            border-bottom: 1px solid black;
        }

        .col-trans-no {
            width: 8%;
            text-align: left;
        }

        .col-date {
            width: 8%;
            text-align: left;
        }

        .col-name {
            width: 25%;
            text-align: left;
        }

        .col-ref {
            width: 10%;
            text-align: left;
        }

        .col-item {
            width: 15%;
            text-align: left;
        }

        .col-cash {
            width: 12%;
            text-align: right;
        }

        .col-ar {
            width: 12%;
            text-align: right;
        }

        .col-cash-h {
            width: 12%;
            text-align: center;
        }

        .col-ar-h {
            width: 12%;
            text-align: center;
        }

        .totals {
            font-weight: bold;
        }

        .grand-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            table-layout: fixed;
        }

        .grand-table td {
            padding: 4px;
            word-wrap: break-word;
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
            border-top: 1px solid black;
        }

        .signatory-table div {
            margin: 0;
        }

        .footer {
            display: flex;
            align-items: center;
            justify-content: center;
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
            color: #e74c3c;
        }

        .run-date {
            font-size: 9px;
            color: #000000;
        }
    </style>
</head>

<body>
    <div class="top-right">
        <div>Run Date/Time: {{ \Carbon\Carbon::now()->format('m/d/Y h:i:s A') }}</div>
        <div class="note">Note: This document is not valid without complete signatory.</div>
    </div>

    <div class="header">
        <h2>BRDR BIL-AG003</h2>
        <small>Accounts Receivable System - Local</small>
        <h3>Temporary Delivery Slip Summary</h3>
    </div>

    <p style="font-size: 10px;"><strong>{{ $date_type }} Date Range:</strong> {{ $dateRange }}</p>

    <table class="table">
        <thead>
            <tr>
                <th class="col-trans-no">TRANS. NO.</th>
                <th class="col-date">{{ $date_type === 'Transaction' ? 'TRANS.' : 'RECEIPT' }} DATE</th>
                <th class="col-name">CUSTOMER NAME</th>
                <th class="col-ref">REF NO.</th>
                <th class="col-item">ITEM CODE & NAME</th>
                <th class="col-cash-h">CASH AMOUNT</th>
                <th class="col-ar-h">AR AMOUNT</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoices as $invoice)
                @foreach ($invoice['items'] as $index => $item)
                    <tr>
                        @if ($index === 0)
                            <td class="col-trans-no" style="vertical-align: top;"
                                rowspan="{{ count($invoice['items']) }}">
                                {{ $invoice['invoice_no'] }}</td>
                            <td class="col-date" style="vertical-align: top;" rowspan="{{ count($invoice['items']) }}">
                                {{ \Carbon\Carbon::parse($invoice['transaction_date'])->format('m/d/Y') }}</td>
                            <td class="col-name" style="vertical-align: top;" rowspan="{{ count($invoice['items']) }}">
                                {{ $invoice['customer_name'] }}
                            </td>
                            <td class="col-ref" style="vertical-align: top;" rowspan="{{ count($invoice['items']) }}">
                                {{ $invoice['reference_no'] }}
                            </td>
                        @endif
                        <td class="col-item" style="vertical-align: top;">{{ $item['item_code'] }} -
                            {{ $item['item_name'] }}</td>
                        @if ($index === 0)
                            <td class="col-cash" style="vertical-align: bottom;"
                                rowspan="{{ count($invoice['items']) }}">
                                {{ number_format($invoice['cash_amount'], 2) }}</td>
                            <td class="col-ar" style="vertical-align: bottom;"
                                rowspan="{{ count($invoice['items']) }}">
                                {{ number_format($invoice['ar_amount'], 2) }}</td>
                        @endif
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
    <table class="grand-table" style=" border-top: 1px solid black;">
        <tbody>
            <tr class="totals">
                <td class="col-trans-no">Grand Total: </td>
                <td class="col-date"></td>
                <td class="col-name"></td>
                <td class="col-ref"></td>
                <td class="col-item"></td>
                <td class="col-cash">{{ number_format($grandTotalCash, 2) }}</td>
                <td class="col-ar">{{ number_format($grandTotalAR, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="signatory-table">
        <tr>
            <td>
                <div>Prepared By:</div>
                <div style="border-bottom: 1px solid black; margin-top: 10px; text-align: center;">{{ $preparedBy }}
                </div>
                <div style="text-align: center;">(Signature Over Printed Name)</div>
                <div>Date:</div>
                <div style="border-bottom: 1px solid black; text-align: center; margin-bottom: 2px;">
                    {{ \Carbon\Carbon::now()->format('m/d/Y') }}</div>
                <div>Time:</div>
                <div style="border-bottom: 1px solid black; text-align: center; margin-bottom: 2px;">
                    {{ \Carbon\Carbon::now()->format(' h:i:s A') }}</div>
                <div>Designation:</div>
                <div style="border-bottom: 1px solid black; margin-top: 10px; text-align: center;"></div>
            </td>
            <td>
                <div>Checked By:</div>
                <div style="border-bottom: 1px solid black; margin-top: 22px; text-align: center;"></div>
                <div style="text-align: center;">(Signature Over Printed Name)</div>
                <div>Date:</div>
                <div style="border-bottom: 1px solid black; margin-top: 10px; text-align: center; margin-bottom: 2px;">
                </div>
                <div>Time:</div>
                <div style="border-bottom: 1px solid black; margin-top: 10px; text-align: center; margin-bottom: 2px;">
                </div>
                <div>Designation:</div>
                <div style="border-bottom: 1px solid black; margin-top: 10px; text-align: center;"></div>
            </td>
            <td>
                <div>Note By:</div>
                <div style="border-bottom: 1px solid black; margin-top: 22px; text-align: center;"></div>
                <div style="text-align: center;">(Signature Over Printed Name)</div>
                <div>Date:</div>
                <div style="border-bottom: 1px solid black; margin-top: 10px; text-align: center; margin-bottom: 2px;">
                </div>
                <div>Time:</div>
                <div style="border-bottom: 1px solid black; margin-top: 10px; text-align: center; margin-bottom: 2px;">
                </div>
                <div>Designation:</div>
                <div style="border-bottom: 1px solid black; margin-top: 10px; text-align: center;"></div>
            </td>
        </tr>
    </table>




</body>

</html>
