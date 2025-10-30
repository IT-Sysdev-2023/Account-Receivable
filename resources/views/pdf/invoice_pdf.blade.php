<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Payment Slip</title>
    <style>
        @page {
            margin: 10mm !important;
            size: A4 portrait;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
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

        .section {
            border-top: 1px solid black;
            padding-top: 10px;
            margin-top: 10px;
            margin-right: 0;
            margin-bottom: 10px;
            margin-left: 0;
            line-height: 1.6;
        }

        .info-row {
            display: flex;
            align-items: center;
            font-size: 12px;
        }

        .info-row div {
            display: inline-block;
            padding: 0px 6px;
        }

        .document-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 11px;
        }

        .document-table th,
        .document-table td {

            padding: 5px 6px;
            text-align: right;
        }

        .document-table th {
            border-top: 1px solid black;
            border-bottom: 1px solid black;
        }

        .document-table th:nth-child(5),
        .document-table th:nth-child(6) {
            text-align: center;
        }

        .document-table td:nth-child(1),
        .document-table th:nth-child(1),
        .document-table td:nth-child(2),
        .document-table th:nth-child(2),
        .document-table td:nth-child(3),
        .document-table th:nth-child(3) {
            text-align: left;
        }

        .document-table-two {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 11px;
            border-bottom: 1px solid black;
        }

        .document-table-two th,
        .document-table-two td {

            padding: 5px 6px;
            text-align: right;
        }

        .document-table-two th {
            border-top: 1px solid black;
            border-bottom: 1px solid black;
        }

        .document-table-two td:first-child,
        .document-table-two th:first-child {
            text-align: left;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 11px;
            border-top: 1px solid black;
            table-layout: fixed;
        }

        .details-table th,
        .details-table td {
            padding: 3px 3px;
            text-align: left;
            width: 50%;
            word-wrap: break-word;
        }

        .tfoot td {
            font-weight: bold;
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
    </style>
</head>

<body>

    <div class="top-right">
        @if ($data['reprint_count'] > 0)
            <div><strong>Reprinted Copy No : </strong>{{ $data['reprint_count'] }}</div>
        @endif
        <div><strong>Transaction Date : </strong> {{ $data['transaction_date'] }}</div>
        <div><strong>Document Number : </strong> {{ $data['invoice_no'] }}</div>
        <div><strong>Receipt Date : </strong> {{ $data['receipt_date'] }}</div>
    </div>

    <div class="header">
        <h2>BRDR BIL-AG003</h2>
        <small>Accounts Receivable System - Local</small>
    </div>

    <div class="title">
        CHARGE SLIP
    </div>

    {{-- <div class="section">
        This is to certify that a payment has been made and received to the account of
        <strong>{{ $data['name'] }}</strong> in the amount of
        <strong>{{ $data['amount_in_words'] }}</strong> (Php
        <strong>{{ number_format($data['amount_paid'], 2) }}</strong>).
    </div> --}}

    <table class="details-table">
        <tbody>
            <tr>
                <td><strong><span class="label">Customer : </span></strong>{{ $data['name'] ?? '_________' }}
                </td>
                <td><strong><span class="label">Payment Type :
                        </span></strong>{{ $data['payment_mode'] ?? '_________' }}</td>
            </tr>
            <tr>
                <td><strong><span class="label">Reference No. :
                        </span></strong>{{ $data['reference_no'] ?? '_________' }}</td>
                <td><strong><span class="label">Particular :
                        </span></strong>{{ $data['particular'] ?? '_________' }}</td>
            </tr>
        </tbody>
    </table>

    <table class="document-table">
        <thead>
            <tr>
                <th>Item Code</th>
                <th>Description</th>
                <th>Unit</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['invoices'] as $doc)
                <tr>
                    <td>{{ $doc['item_code'] }}</td>
                    <td>{{ $doc['item_name'] }}</td>
                    <td>{{ $doc['packing'] }}</td>
                    <td>{{ number_format($doc['quantity']) }}</td>
                    <td>{{ number_format($doc['price'], 2) }}</td>
                    <td>{{ number_format($doc['amount'], 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="6" style="text-align: center"><strong>*** Nothing Follows ***</strong></td>
            </tr>
        </tbody>
    </table>

    <table class="document-table-two">
        <tbody>
            <tr>
                <td>Gross Total</td>
                <td>Php {{ number_format($data['total_amount'], 2) }}</td>
            </tr>
            <tr>
                <td>Net Total</td>
                <td>Php {{ number_format($data['total_amount'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="signatory-table">
        <tr>
            <td>
                <div>Prepared By:</div>
                <div style="border-bottom: 1px solid black; margin-top: 10px; text-align: center;">
                    {{ $data['preparedBy'] }}
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
