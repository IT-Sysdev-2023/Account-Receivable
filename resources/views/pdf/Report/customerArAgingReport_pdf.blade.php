<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Customer AR Aging Report</title>
    <style>
        body {
            margin-bottom: 0 !important;
            padding: 0 !important;
            max-width: 100%;
            box-sizing: border-box;
            font-family: sans-serif;
            font-size: 10px;
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
            margin-bottom: 10px;
            table-layout: fixed;
            border-bottom: 1px solid black;
        }

        .table th {
            /* border: 1px solid black; */
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

        .customer-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            table-layout: fixed;
        }

        .customer-table td {
            padding: 4px;
            word-wrap: break-word;
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

        .col-paymentno {
            text-align: left;
            width: 10%;
        }

        .col-paymendate {
            text-align: left;
            width: 10%;
        }

        .col-checkno {
            text-align: left;
            width: 10%;
        }

        .col-checkstatus {
            text-align: left;
            width: 10%;
        }

        .col-duedate {
            text-align: left;
            width: 12%;
        }

        .col-clearingdate {
            text-align: left;
            width: 12%;
        }

        .col-docno {
            text-align: left;
            width: 12%;
        }

        .col-doctype {
            text-align: left;
            width: 12%;
        }

        .col-amount {
            text-align: right;
            width: 12%;
        }

        .col-amount-h {
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

        .text-right {
            text-align: right;
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
        <h3>Customer AR Aging Report</h3>
    </div>
    <p><strong>As Of Date :</strong> {{ $dateRange }}</p>

    @foreach ($groupedData as $group)
        <table style="width: 100%; margin-bottom: 2px;">
            <tr>
                <td>
                    <strong>{{ $group['customer_code'] }}</strong> {{ $group['customer_name'] }}
                </td>
                <td style="text-align: right;"><strong>Payment Terms :</strong> {{ $group['payment_terms'] }}</td>
            </tr>
        </table>

        <table class="table">
            <thead>
                <tr>
                    <th class="col-paymentno">DOCUMENT NO.</th>
                    <th class="col-paymendate">DOC. DATE</th>
                    <th class="col-checkno">DOC. TYPE</th>
                    <th class="col-checkstatus">DUE DATE</th>
                    <th class="col-amount-h">1-30 DAYS</th>
                    <th class="col-amount-h">31-60 DAYS</th>
                    <th class="col-amount-h">61-90 DAYS</th>
                    <th class="col-amount-h">91-360 DAYS</th>
                    <th class="col-amount-h">ABOVE 1 YEAR</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($group['invoices'] as $invoice)
                    @php
                        $docDate = \Carbon\Carbon::parse($invoice['document_date']);
                        $today = \Carbon\Carbon::now();
                        $daysDiff = $docDate->diffInDays($today, false); // false gives signed difference
                    @endphp
                    <tr>
                        <td>{{ $invoice['invoice_no'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($invoice['document_date'])->format('m/d/Y') }}</td>
                        <td>{{ $invoice['document_type'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($invoice['document_due_date'])->format('m/d/Y') }}</td>
                        <td class="col-amount">{{ number_format($invoice['amount_1_30'], 2) }}</td>
                        <td class="col-amount">{{ number_format($invoice['amount_31_60'], 2) }}</td>
                        <td class="col-amount">{{ number_format($invoice['amount_61_90'], 2) }}</td>
                        <td class="col-amount">{{ number_format($invoice['amount_91_360'], 2) }}</td>
                        <td class="col-amount">{{ number_format($invoice['amount_above_1_year'], 2) }}</td>
                    </tr>
                @endforeach
                {{-- Subtotal row for this customer --}}
                <tr class="totals">
                    <td colspan="4">Sub Total : </td>
                    <td class="col-amount">{{ number_format($group['totals']['1_30'], 2) }}</td>
                    <td class="col-amount">{{ number_format($group['totals']['31_60'], 2) }}</td>
                    <td class="col-amount">{{ number_format($group['totals']['61_90'], 2) }}</td>
                    <td class="col-amount">{{ number_format($group['totals']['91_360'], 2) }}</td>
                    <td class="col-amount">{{ number_format($group['totals']['above_1_year'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endforeach


    <table class="overall-table">
        <tbody>
            <tr class="totals">
                <td colspan="4">Grand Total:</td>
                <td class="col-amount">{{ number_format($grandTotals['1_30'], 2) }}</td>
                <td class="col-amount">{{ number_format($grandTotals['31_60'], 2) }}</td>
                <td class="col-amount">{{ number_format($grandTotals['61_90'], 2) }}</td>
                <td class="col-amount">{{ number_format($grandTotals['91_360'], 2) }}</td>
                <td class="col-amount">{{ number_format($grandTotals['above_1_year'], 2) }}</td>
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
