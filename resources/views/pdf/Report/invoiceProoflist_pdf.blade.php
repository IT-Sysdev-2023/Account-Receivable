<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Temporary Delivery Slip Prooflist</title>
    <style>
        @page {
            margin: 10mm !important;
            size: A4 portrait;
        }

        body {
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
            margin-top: 5px;
            margin-bottom: 10px;
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

        .col-trans-no {
            width: 13%;
            text-align: left;
        }

        .col-date {
            width: 15%;
            text-align: left;
        }

        .col-ref {
            width: 13%;
            text-align: left;
        }

        .col-item {
            width: 29%;
            text-align: left;
        }

        .col-cash {
            width: 15%;
            text-align: right;
        }

        .col-ar {
            width: 15%;
            text-align: right;
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
        <h3>Temporary Delivery Slip Prooflist</h3>
    </div>

    <p><strong>{{ $date_type }} Date Range:</strong> {{ $dateRange }}</p>

    @foreach ($groupedData as $group)
        <table style="width: 100%; margin-bottom: 4px;">
            <tr>
                <td>
                    <strong>{{ $group['customer_code'] }}</strong> {{ $group['customer_name'] }}
                </td>
                <td style="text-align: right;"><strong>Currency:</strong> {{ $currency }}</td>
            </tr>
        </table>

        <table class="table">
            <thead>
                <tr>
                    <th class="col-trans-no">TRANS. NO.</th>
                    <th class="col-date">{{ $date_type === 'Transaction' ? 'TRANS.' : 'RECEIPT' }} DATE</th>
                    <th class="col-ref">REF NO.</th>
                    <th class="col-item">ITEM CODE & NAME</th>
                    <th class="col-cash">CASH AMOUNT</th>
                    <th class="col-ar">AR AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($group['invoices'] as $invoice)
                    @foreach ($invoice['items'] as $index => $item)
                        <tr>
                            @if ($index === 0)
                                <td class="col-trans-no" style="vertical-align: top;"
                                    rowspan="{{ count($invoice['items']) }}">
                                    {{ $invoice['invoice_no'] }}</td>
                                <td class="col-date" style="vertical-align: top;"
                                    rowspan="{{ count($invoice['items']) }}">
                                    {{ \Carbon\Carbon::parse($invoice['transaction_date'])->format('m/d/Y') }}</td>
                                <td class="col-ref" style="vertical-align: top;"
                                    rowspan="{{ count($invoice['items']) }}">
                                    {{ $invoice['reference_no'] }}</td>
                            @endif
                            <td class="col-item">{{ $item['item_code'] }} - {{ $item['item_name'] }}</td>
                            @if ($index === 0)
                                <td class="col-cash" style="vertical-align: top;"
                                    rowspan="{{ count($invoice['items']) }}">
                                    {{ number_format($invoice['cash_amount'], 2) }}</td>
                                <td class="col-ar" style="vertical-align: top;"
                                    rowspan="{{ count($invoice['items']) }}">
                                    {{ number_format($invoice['ar_amount'], 2) }}</td>
                            @endif
                        </tr>
                    @endforeach

                    <tr class="particular">
                        <td colspan="3"></td>
                        <td>Particular: {{ $invoice['particular'] }}</td>
                        <td colspan="2"></td>
                    </tr>
                    <tr class="totals">
                        <td colspan="4">Document Total : </td>
                        <td class="col-cash">{{ number_format($invoice['cash_amount'], 2) }}</td>
                        <td class="col-ar">{{ number_format($invoice['ar_amount'], 2) }}</td>
                    </tr>
                @endforeach

                {{-- Subtotal row for this customer --}}
                <tr class="totals" style="border-top: 1px solid black;">
                    <td colspan="4">Sub Total : </td>
                    <td class="col-cash">{{ number_format($group['customer_cash_total'], 2) }}</td>
                    <td class="col-ar">{{ number_format($group['customer_ar_total'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endforeach
    <table class="grand-table">
        <tbody>
            <tr class="totals">
                <td colspan="4">Grand Total: </td>
                <td class="col-ar">{{ number_format($grandTotalCash, 2) }}</td>
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
