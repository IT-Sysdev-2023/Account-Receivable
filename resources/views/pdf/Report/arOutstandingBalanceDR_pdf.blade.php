<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>AR Outstanding Balance (Date Range)</title>
    <style>
        /* @page {
            margin: 10mm !important;
            size: A4 landscape;
        } */

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

        .col-trans-no {
            text-align: center;
            width: 6%;
        }

        .col-date {
            text-align: center;
            width: 7%;
        }

        .col-adjtype {
            text-align: left;
            width: 12%;
        }

        .col-adjapply {
            text-align: left;
            width: 14%;
        }

        .col-docno {
            text-align: left;
            width: 12%;
            /* text-align: right; */
        }

        .col-reason {
            text-align: left;
            width: 21%;
            /* text-align: right; */
        }

        .col-amount-h {
            width: 10%;
            text-align: center;
        }

        .col-amount {
            width: 10%;
            text-align: right;
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
        <h3>AR Outstanding Balances (Date Range)</h3>
    </div>

    <p><strong>Date Range:</strong> {{ $dateRange }}</p>



    @foreach ($groupedData as $group)
        <table style="width: 100%; margin-bottom: 2px;">
            <tr>
                <td>
                    <strong>{{ $group['customer_code'] }}</strong> {{ $group['customer_name'] }}
                </td>
            </tr>
        </table>



        <table class="table">
            <thead>
                <tr>
                    <th class="col-trans-no">DOC. NO</th>
                    <th class="col-date">DOC. TYPE</th>
                    <th class="col-date">RECEIPT DATE</th>
                    <th class="col-amount-h">GROSS AMOUNT</th>
                    <th class="col-amount-h">S/O</th>
                    <th class="col-amount-h">RETURN</th>
                    <th class="col-amount-h">ADJUSTMENT</th>
                    <th class="col-amount-h">PARTIAL PAYMENT</th>
                    <th class="col-amount-h">FLOATING PDC/DC</th>
                    <th class="col-amount-h">FLOATING WHT</th>
                    <th class="col-amount-h">AR NET AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($group['outstandingBalances'] as $outstandingBalance)
                    <tr>
                        <td class="col-trans-no">{{ $outstandingBalance['document_no'] }}</td>
                        <td class="col-date">{{ $outstandingBalance['type'] }}</td>
                        <td class="col-date">
                            {{ \Carbon\Carbon::parse($outstandingBalance['receipt_date'])->format('m/d/Y') }}</td>
                        <td class="col-amount">{{ number_format($outstandingBalance['gross_amount'], 2) }}</td>
                        <td class="col-amount">{{ number_format($outstandingBalance['shrinkage_overage'], 2) }}</td>
                        <td class="col-amount">{{ number_format($outstandingBalance['return'], 2) }}</td>
                        <td class="col-amount">{{ number_format($outstandingBalance['adjustment'], 2) }}</td>
                        <td class="col-amount">{{ number_format($outstandingBalance['partial_payment'], 2) }}</td>
                        <td class="col-amount">{{ number_format($outstandingBalance['floating_pdc_dc'], 2) }}</td>
                        <td class="col-amount">{{ number_format($outstandingBalance['floating_wht'], 2) }}</td>
                        <td class="col-amount">{{ number_format($outstandingBalance['ar_net_amount'], 2) }}</td>
                    </tr>
                @endforeach

                {{-- Subtotal row for this customer --}}
                <tr class="totals" style="border-top: 1px solid black;">
                    <td colspan="10">Sub Total : </td>
                    <td class="col-amount">{{ number_format($group['customerAmountTotal'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endforeach

    <table class="overall-table">
        <tbody>

            {{-- Subtotal row for this customer --}}
            <tr class="totals">
                <td colspan="10">Total Amount: </td>
                <td class="col-amount">{{ number_format($customerOverallAmountTotal, 2) }}</td>
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
