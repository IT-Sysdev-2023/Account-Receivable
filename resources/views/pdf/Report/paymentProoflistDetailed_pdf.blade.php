<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Payment Prooflist Detailed</title>
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
            text-align: left;
            width: 11%;
        }

        .col-date {
            text-align: left;
            width: 11%;
        }

        .col-type {
            text-align: left;
            width: 11%;
        }

        .col-orno {
            text-align: left;
            width: 11%;
        }

        .col-docno {
            text-align: left;
            width: 11%;
        }

        .col-refno {
            text-align: left;
            width: 11%;
        }

        .col-amount-h {
            width: 11%;
            text-align: center;
        }

        .col-remarks-h {
            width: 12%;
            text-align: center;
        }

        .col-amount {
            width: 11%;
            text-align: right;
        }

        .col-remarks {
            width: 12%;
            text-align: center;
        }

        .totals {
            font-weight: normal;
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

        .divider {
            padding: 10px;
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
        <h3>Payment Prooflist Detailed</h3>
    </div>
    <p style="font-size: 10px;"><strong>{{ $date_type }} Date Range:</strong> {{ $dateRange }}</p>

    @foreach ($groupedData as $paymentTypeGroup)
        <div class="payment-type-section">
            <h3>Payment Type: {{ $paymentTypeGroup['payment_type'] }}</h3>
            <p><strong>Currency:</strong> {{ $currency }}</p>

            <table class="table">
                <thead>
                    <tr>
                        <th class="col-trans-no">TRANS. NO</th>
                        <th class="col-date">{{ $date_type === 'Transaction' ? 'TRANS.' : 'RECEIPT' }} DATE</th>
                        <th class="col-type">TYPE</th>
                        <th class="col-docno">DOCUMENT NO.</th>
                        <th class="col-orno">O.R. NO.</th>
                        <th class="col-refno">REF. NO.</th>
                        <th class="col-remarks-h">REMARKS</th>
                        <th class="col-amount-h">CANCELLED AMOUNT</th>
                        <th class="col-amount-h">AMOUNT</th>
                    </tr>
                </thead>
            </table>

            @foreach ($paymentTypeGroup['customers'] as $customer)
                <div class="customer-section">
                    <p style="padding-left: 4px;"><strong>{{ $customer['customer_code'] }}</strong>
                        {{ $customer['customer_name'] }}</p>

                    <table class="table">
                        <tbody>
                            @foreach ($customer['payments'] as $payment)
                                @foreach ($payment['payment_details'] as $detail)
                                    <tr>
                                        <td class="col-trans-no" style="vertical-align: top;">
                                            {{ $payment['payment_no'] }}
                                        </td>
                                        <td class="col-date" style="vertical-align: top;">
                                            {{ \Carbon\Carbon::parse($payment['date'])->format('m/d/Y') }}
                                        </td>
                                        <td class="col-type">{{ $detail['type'] }}</td>
                                        <td class="col-docno">{{ $detail['document_no'] }}</td>
                                        <td class="col-orno">{{ $detail['ds_no'] }}</td>
                                        <td class="col-refno">{{ $detail['reference_no'] }}</td>
                                        <td class="col-remarks">{{ $detail['remarks'] }}</td>
                                        <td class="col-amount">
                                            {{ $detail['cancelled_amount'] ? number_format($detail['cancelled_amount'], 2) : '' }}
                                        </td>
                                        <td class="col-amount">
                                            {{ $detail['amount_paid'] ? number_format($detail['amount_paid'], 2) : '' }}
                                        </td>
                                    </tr>
                                @endforeach

                                @if ($payment['cash_in_bank'])
                                    <tr>
                                        <td class="col-refno" colspan="4"></td>
                                        <td class="col-refno" colspan="1">Bank Name :</td>
                                        <td class="col-refno" colspan="2">{{ $payment['cash_in_bank'] }}</td>
                                    </tr>
                                @endif
                            @endforeach

                            <tr class="totals" style="border-bottom: 1px solid black;">
                                <td colspan="8"><strong>Customer Total : </strong></td>
                                <td class="col-amount">
                                    <strong>{{ number_format($customer['customer_total'], 2) }}</strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endforeach

            <div class="payment-type-total">
                <table class="overall-table">
                    <tbody>
                        <tr class="totals">
                            {{-- <td colspan="6"> Sub Total &gt;&gt;&gt;&gt;</td> --}}
                            <td colspan="7"> <strong> Sub Total : </strong></td>
                            <td class="col-amount">
                                <strong>{{ number_format($paymentTypeGroup['type_total'], 2) }}</strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="divider"></p>
        </div>
    @endforeach

    <div class="grand-total">
        <table class="overall-table">
            <tbody>
                <tr class="totals">
                    <td colspan="6"> <strong>Total : </strong></td>
                    <td class="col-amount"> <strong>{{ number_format($grandTotal, 2) }}</strong></td>
                </tr>
                <tr class="totals">
                    <td colspan="6"> <strong>Grand Total : </strong></td>
                    <td class="col-amount"> <strong>{{ number_format($grandTotal, 2) }}</strong></td>
                </tr>
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

</body>

</html>
