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
            border-top: 1px solid black;
            padding-top: 10px;
            margin-top: 10px;
            margin-right: 0;
            margin-bottom: 10px;
            margin-left: 0;
            display: flex;
            align-items: center;
            font-size: 12px;
            white-space: nowrap;
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
            border-bottom: 1px solid black;
        }

        th,
        td {

            padding: 5px 6px;
            text-align: right;
        }

        th {
            border-top: 1px solid black;
            border-bottom: 1px solid black;
        }

        td:first-child,
        th:first-child {
            text-align: left;
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
        <div><strong>Payment Number : </strong> {{ $data['payment_no'] }}</div>
        <div><strong>Receipt Date : </strong> {{ $data['receipt_date'] }}</div>
    </div>

    <div class="header">
        <h2>BRDR BIL-AG003</h2>
        <small>Accounts Receivable System - Local</small>
    </div>

    <div class="title">
        PAYMENT SLIP
    </div>

    <div class="section">
        This is to certify that a payment has been made and received to the account of
        <strong>{{ $data['name'] }}</strong> in the amount of
        <strong>{{ $data['amount_in_words'] }}</strong> (Php
        <strong>{{ number_format($data['amount_paid'], 2) }}</strong>).
    </div>

    <div class="info-row">
        <div><strong><span class="label">Payment Type : </span></strong>{{ $data['payment_type'] }}</div>
        <div><strong><span class="label">OR No. : </span></strong>{{ $data['ds_no'] ?? '_________' }}</div>
        {{-- <div><span class="label">Ref. No.:</span> {{ $data['ref_no'] ?? '_________' }}</div> --}}
    </div>

    <table class="document-table">
        <thead>
            <tr>
                <th>S.I / C.I Date</th>
                <th>S.I / C.I No.</th>
                <th>S.I / C.I Amount</th>
                <th>Balance</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['paidDocuments'] as $doc)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($doc->document_date)->format('m/d/Y') }}</td>
                    <td>{{ $doc->document_no }}</td>
                    <td>{{ number_format($doc->amount, 2) }}</td>
                    <td>{{ number_format($doc->balance, 2) }}</td>
                    <td>{{ number_format($doc->amount_paid, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="tfoot">
            <tr>
                <td colspan="4" style="text-align: right;">Total Amount >>></td>
                <td>{{ number_format($data['amount_paid'], 2) }}</td>
            </tr>
        </tfoot>
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
