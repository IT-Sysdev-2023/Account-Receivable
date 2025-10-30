<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Customer AR PDC-DC Aging Report</title>
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
            font-size: 12px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
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
            margin-top: 5px;
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
            width: 8%;
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
            width: 8%;
        }

        .col-amount-h {
            width: 8%;
            text-align: center;
        }

        .totals {
            font-weight: bold;
        }

        .ttotals td {
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
        <h3>Customer AR PDC-DC Aging Report</h3>
    </div>
    <p style="font-size: 10px;"><strong>As Of Date :</strong> {{ $dateRange }}</p>

    @foreach ($groupedData as $group)
        <table style="width: 100%; margin-bottom: 2px;">
            <tr>
                <td>
                    <strong>{{ $group['customer_code'] }}</strong> {{ $group['customer_name'] }}
                </td>
                <td style="text-align: right;">
                    <strong>Legend:</strong>
                    <span
                        style="
                display: inline-block;
                width: 24px;
                height: 14px;
                background-color: #aaaa;
                border: 1px solid #aaaa;
                margin-left: 2px;
                vertical-align: middle;
            "></span>
                    <span style="vertical-align: middle;">Cancelled</span>
                </td>
            </tr>
        </table>
        <table class="table">
            <thead>
                <tr>
                    <th class="col-paymentno">DOC. NO.</th>
                    <th class="col-paymentno">PAYMENT DATE</th>
                    <th class="col-paymentno">DUE DATE</th>
                    <th class="col-checkno">DESCRIPTION</th>
                    <th class="col-checkno">CHECK NO.</th>
                    <th class="col-amount-h">1-15 DAYS</th>
                    <th class="col-amount-h">15-30 DAYS</th>
                    <th class="col-amount-h">31-45 DAYS</th>
                    <th class="col-amount-h">46-60 DAYS</th>
                    <th class="col-amount-h">61-75 DAYS</th>
                    <th class="col-amount-h">76-90 DAYS</th>
                    <th class="col-amount-h">ABOVE 90 DAYS</th>
                </tr>
            </thead>
        </table>

        @foreach ($group['check_types'] as $checkTypeGroup)
            <table class="table">
                <tbody>
                    <tr>
                        <td colspan="12" class="check-type-header">
                            <strong>Check Type : </strong>{{ $checkTypeGroup['check_type'] }}
                        </td>
                    </tr>
                </tbody>
                <tbody>
                    @foreach ($checkTypeGroup['invoices'] as $invoice)
                        <tr @if ($invoice['remarks'] === 'Cancelled') style="background-color: #aaaa;" @endif>
                            <td class="col-paymentno">{{ $invoice['document_no'] }}</td>
                            <td class="col-paymentno">
                                {{ \Carbon\Carbon::parse($invoice['payment_receipt_date'])->format('m/d/Y') }}</td>
                            <td class="col-paymentno">
                                {{ \Carbon\Carbon::parse($invoice['document_due_date'])->format('m/d/Y') }}</td>
                            <td class="col-checkno">{{ $invoice['document_type'] }}</td>
                            <td class="col-checkno">{{ $invoice['check_no'] }}</td>
                            <td class="col-amount">{{ number_format($invoice['amount_1_15'], 2) }}</td>
                            <td class="col-amount">{{ number_format($invoice['amount_16_30'], 2) }}</td>
                            <td class="col-amount">{{ number_format($invoice['amount_31_45'], 2) }}</td>
                            <td class="col-amount">{{ number_format($invoice['amount_46_60'], 2) }}</td>
                            <td class="col-amount">{{ number_format($invoice['amount_61_75'], 2) }}</td>
                            <td class="col-amount">{{ number_format($invoice['amount_76_90'], 2) }}</td>
                            <td class="col-amount">{{ number_format($invoice['amount_above_90_days'], 2) }}</td>
                        </tr>
                    @endforeach

                    {{-- Subtotal for this check type --}}
                    <tr class="ttotals" style="border-bottom: 1px solid black;">
                        <td colspan="5">Subtotal:</td>
                        <td class="col-amount">{{ number_format($checkTypeGroup['totals']['1_15'], 2) }}</td>
                        <td class="col-amount">{{ number_format($checkTypeGroup['totals']['16_30'], 2) }}</td>
                        <td class="col-amount">{{ number_format($checkTypeGroup['totals']['31_45'], 2) }}</td>
                        <td class="col-amount">{{ number_format($checkTypeGroup['totals']['46_60'], 2) }}</td>
                        <td class="col-amount">{{ number_format($checkTypeGroup['totals']['61_75'], 2) }}</td>
                        <td class="col-amount">{{ number_format($checkTypeGroup['totals']['76_90'], 2) }}</td>
                        <td class="col-amount">{{ number_format($checkTypeGroup['totals']['above_90_days'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        @endforeach

        {{-- Customer subtotal --}}
        <table class="table" style="margin-bottom: 10px;">
            <tr class="ttotals">
                <td colspan="5">Total :</td>
                <td class="col-amount">{{ number_format($group['totals']['1_15'], 2) }}</td>
                <td class="col-amount">{{ number_format($group['totals']['16_30'], 2) }}</td>
                <td class="col-amount">{{ number_format($group['totals']['31_45'], 2) }}</td>
                <td class="col-amount">{{ number_format($group['totals']['46_60'], 2) }}</td>
                <td class="col-amount">{{ number_format($group['totals']['61_75'], 2) }}</td>
                <td class="col-amount">{{ number_format($group['totals']['76_90'], 2) }}</td>
                <td class="col-amount">{{ number_format($group['totals']['above_90_days'], 2) }}</td>
            </tr>
        </table>
    @endforeach


    <table class="overall-table">
        <tbody>
            <tr class="totals">
                <td colspan="5">Grand Total:</td>
                <td class="col-amount">{{ number_format($grandTotals['1_15'], 2) }}</td>
                <td class="col-amount">{{ number_format($grandTotals['16_30'], 2) }}</td>
                <td class="col-amount">{{ number_format($grandTotals['31_45'], 2) }}</td>
                <td class="col-amount">{{ number_format($grandTotals['46_60'], 2) }}</td>
                <td class="col-amount">{{ number_format($grandTotals['61_75'], 2) }}</td>
                <td class="col-amount">{{ number_format($grandTotals['76_90'], 2) }}</td>
                <td class="col-amount">{{ number_format($grandTotals['above_90_days'], 2) }}</td>
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
