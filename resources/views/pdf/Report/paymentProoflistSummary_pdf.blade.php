<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Payment Prooflist Summary</title>
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
            text-align: center;
            font-weight: bold;
            font-size: 14px;
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

        .overall-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            table-layout: fixed;
        }

        .overall-table td {
            padding: 4px;
            word-wrap: break-word;
        }

        .col-date {
            text-align: left;
            width: 12%;
        }

        .col-trans-no {
            text-align: left;
            width: 10%;
        }

        .col-docno {
            text-align: left;
            width: 10%;
        }

        .col-customer {
            text-align: left;
            width: 20%;
        }

        .col-amount {
            text-align: right;
            width: 12%;
        }

        .col-amountpaid {
            width: 12%;
            text-align: right;
        }

        .col-pop {
            text-align: left;
            width: 12%;
        }

        .col-amount-h {
            width: 12%;
            text-align: center;
        }

        .col-amountpaid-h {
            width: 12%;
            text-align: center;
        }

        .totals {
            font-weight: bold;
        }

        .overall-amount {
            padding-right: 4px;
            text-align: right;
        }

        .customer-section {
            margin-top: 10px;
            margin-bottom: 2px;
        }

        .payment-type-section p {
            margin-top: 0;
            margin-bottom: 2px;
        }

        .payment-type-section h3 {
            margin-top: 0;
            margin-bottom: 2px;
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
            font-size: 9px;
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
        <h3>Payment Prooflist Summary</h3>
    </div>

    <p style="font-size: 10px;"><strong>{{ $date_type }} Date Range:</strong> {{ $dateRange }}</p>

    <div class="payment-type-section">

        <table class="table">
            <thead>
                <tr>
                    <th class="col-date">{{ $date_type === 'Transaction' ? 'TRANS.' : 'RECEIPT' }} DATE</th>
                    <th class="col-trans-no">TRANS. NO</th>
                    <th class="col-docno">DOCUMENT NO.</th>
                    <th class="col-customer">CUSTOMER NAME</th>
                    <th class="col-amount-h">AMOUNT</th>
                    <th class="col-amountpaid-h">AMOUNT PAID</th>
                    <th class="col-pop">PROOF OF PAYMENT</th>
                    <th class="col-pop">REMARKS</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($payments as $payment)
                    @foreach ($payment['payment_details'] as $detail)
                        <tr>
                            <td class="col-date">
                                {{ \Carbon\Carbon::parse($payment['date'])->format('m/d/Y') }}</td>
                            <td class="col-trans-no">{{ $payment['payment_no'] }}</td>
                            <td class="col-docno">{{ $detail['document_no'] }}</td>
                            <td class="col-customer">{{ $payment['customer'] }}</td>
                            <td class="col-amount">{{ number_format($detail['amount'], 2) }}</td>
                            <td class="col-amountpaid">{{ number_format($detail['amount_paid'], 2) }}</td>
                            <td class="col-pop">{{ $detail['reference_no'] ?? $detail['ds_no'] }}</td>
                            <td class="col-pop">{{ $detail['remarks'] }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
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

    <div class="footer">
        <p class="run-date">Run Date/Time: {{ \Carbon\Carbon::now()->format('m/d/Y h:i:s A') }}</p>
        <p class="note">Note: This document is not valid without complete signatory.</p>
    </div>

</body>

</html>
