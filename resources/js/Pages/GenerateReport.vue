<template>
    <div>

        <Head :title="` | ${$page.component}`" />
        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <InvoiceProoflist v-if="showInvoiceProoflist" :show="showInvoiceProoflist"
                @close="showInvoiceProoflist = false" />
        </Transition>
        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <InvoiceSummary v-if="showInvoiceSummarylist" :show="showInvoiceSummarylist"
                @close="showInvoiceSummarylist = false" />
        </Transition>
        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <AdjustmentProoflist v-if="showAdjustmentProoflist" :show="showAdjustmentProoflist"
                @close="showAdjustmentProoflist = false" />
        </Transition>
        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <PaymentProoflist v-if="showPaymentProoflistDetailed" :show="showPaymentProoflistDetailed"
                :type="'DETAILED'" @close="showPaymentProoflistDetailed = false" />
        </Transition>
        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <PaymentProoflist v-if="showPaymentProoflistSummary" :show="showPaymentProoflistSummary" :type="'SUMMARY'"
                @close="showPaymentProoflistSummary = false" />
        </Transition>
        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <CustomerPDCReport v-if="showCustomerPDCReport" :show="showCustomerPDCReport"
                @close="showCustomerPDCReport = false" />
        </Transition>

        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <CustomerARAging v-if="showCustomerARAging" :show="showCustomerARAging" :type="customerAgingType"
                @close="showCustomerARAging = false" />
        </Transition>

        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <BegBalProoflist v-if="showBegBalProoflist" :show="showBegBalProoflist"
                @close="showBegBalProoflist = false" />
        </Transition>

        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <AROutstandingBalanceAO v-if="showAROutstandingBalanceAO" :show="showAROutstandingBalanceAO"
                @close="showAROutstandingBalanceAO = false" />
        </Transition>

        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <AROutstandingBalanceDR v-if="showAROutstandingBalanceDR" :show="showAROutstandingBalanceDR"
                @close="showAROutstandingBalanceDR = false" />
        </Transition>

        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <SalesPerItem v-if="showSalesPerItem" :show="showSalesPerItem" @close="showSalesPerItem = false" />
        </Transition>

        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <OverageShortage v-if="showOverageShortage" :show="showOverageShortage"
                @close="showOverageShortage = false" />
        </Transition>

        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <StatementOfAccount v-if="showStatementOfAccount" :show="showStatementOfAccount" :type="SOAType"
                @close="showStatementOfAccount = false" />
        </Transition>
        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <StatementOfAccountSummary v-if="showStatementOfAccountSummary" :show="showStatementOfAccountSummary"
                @close="showStatementOfAccountSummary = false" />
        </Transition>
        <div
            class="bg-[var(--color-bg-secondary)]/20 p-6 rounded-xl backdrop-blur-sm border border-[var(--color-border)]/50 shadow-[0_4px_30px_var(--color-shadow)]/20 mt-6">
            <div class="flex flex-col">
                <!-- Grid Layout -->
                <div class="w-full grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Invoice Report Card -->
                    <div
                        class="bg-[var(--color-bg-secondary)]/30 p-5 rounded-lg border border-[var(--color-border)]/30 hover:border-[var(--color-primary)]/50 transition-all">
                        <h3
                            class="font-semibold text-lg pb-3 mb-3 border-b border-[var(--color-border)]/30 flex items-center gap-2">
                            <svg-icon type="mdi" :path="mdiFileChartOutline"
                                class="w-5 h-5 text-[var(--color-primary)]" />
                            Invoice Report
                        </h3>
                        <div class="grid grid-cols-1 gap-3">
                            <ReportButton icon="document-text" title="Invoice Prooflist"
                                @click="showInvoiceProoflist = true" />
                            <ReportButton icon="chart-bar" title="Invoice Summary"
                                @click="showInvoiceSummarylist = true" />
                        </div>
                    </div>

                    <!-- Adjustment Report Card -->
                    <div
                        class="bg-[var(--color-bg-secondary)]/30 p-5 rounded-lg border border-[var(--color-border)]/30 hover:border-[var(--color-primary)]/50 transition-all">
                        <h3
                            class="font-semibold text-lg pb-3 mb-3 border-b border-[var(--color-border)]/30 flex items-center gap-2">
                            <svg-icon type="mdi" :path="mdiTuneVerticalVariant"
                                class="w-5 h-5 text-[var(--color-primary)]" />
                            Adjustment Report
                        </h3>
                        <div class="grid grid-cols-1 gap-3">
                            <ReportButton icon="adjustments" title="Adjustment Prooflist"
                                @click="showAdjustmentProoflist = true" />
                        </div>
                    </div>

                    <!-- Payment Report Card -->
                    <div
                        class="bg-[var(--color-bg-secondary)]/30 p-5 rounded-lg border border-[var(--color-border)]/30 hover:border-[var(--color-primary)]/50 transition-all">
                        <h3
                            class="font-semibold text-lg pb-3 mb-3 border-b border-[var(--color-border)]/30 flex items-center gap-2">
                            <svg-icon type="mdi" :path="mdiCreditCardOutline"
                                class="w-5 h-5 text-[var(--color-primary)]" />
                            Payment Report
                        </h3>
                        <div class="grid grid-cols-1 gap-3">
                            <ReportButton icon="credit-card" title="Payment Prooflist (Detailed)"
                                @click="showPaymentProoflistDetailed = true" />
                            <ReportButton icon="credit-card" title="Payment Prooflist (Summary)"
                                @click="showPaymentProoflistSummary = true" />
                        </div>
                    </div>

                    <!-- PDC DC Report Card -->
                    <div
                        class="bg-[var(--color-bg-secondary)]/30 p-5 rounded-lg border border-[var(--color-border)]/30 hover:border-[var(--color-primary)]/50 transition-all">
                        <h3
                            class="font-semibold text-lg pb-3 mb-3 border-b border-[var(--color-border)]/30 flex items-center gap-2">
                            <svg-icon type="mdi" :path="mdiCheckbook" class="w-5 h-5 text-[var(--color-primary)]" />
                            PDC DC Report
                        </h3>
                        <div class="grid grid-cols-1 gap-3">
                            <ReportButton icon="document-check" title="Customer PDC DC Report"
                                @click="showCustomerPDCReport = true" />
                        </div>
                    </div>

                    <!-- Customer Aging Report Card -->
                    <div
                        class="bg-[var(--color-bg-secondary)]/30 p-5 rounded-lg border border-[var(--color-border)]/30 hover:border-[var(--color-primary)]/50 transition-all">
                        <h3
                            class="font-semibold text-lg pb-3 mb-3 border-b border-[var(--color-border)]/30 flex items-center gap-2">
                            <svg-icon type="mdi" :path="mdiBookClockOutline"
                                class="w-5 h-5 text-[var(--color-primary)]" />
                            Customer Aging Report
                        </h3>
                        <div class="grid grid-cols-1 gap-3">
                            <ReportButton icon="clock" title="AR Aging" @click="handleCustomerAgingReport('AR')" />
                            <ReportButton icon="banknotes" title="PDC - DC" @click="handleCustomerAgingReport('PDC')" />
                        </div>
                    </div>

                    <!-- Beginning Balance Report Card -->
                    <div
                        class="bg-[var(--color-bg-secondary)]/30 p-5 rounded-lg border border-[var(--color-border)]/30 hover:border-[var(--color-primary)]/50 transition-all">
                        <h3
                            class="font-semibold text-lg pb-3 mb-3 border-b border-[var(--color-border)]/30 flex items-center gap-2">
                            <svg-icon type="mdi" :path="mdiWalletOutline" class="w-5 h-5 text-[var(--color-primary)]" />
                            Beginning Balance Report
                        </h3>
                        <div class="grid grid-cols-1 gap-3">
                            <ReportButton icon="scale" title="Beginning Balance Prooflist"
                                @click="showBegBalProoflist = true" />
                        </div>
                    </div>

                    <!-- AR Outstanding Balance Card -->
                    <div
                        class="bg-[var(--color-bg-secondary)]/30 p-5 rounded-lg border border-[var(--color-border)]/30 hover:border-[var(--color-primary)]/50 transition-all">
                        <h3
                            class="font-semibold text-lg pb-3 mb-3 border-b border-[var(--color-border)]/30 flex items-center gap-2">
                            <svg-icon type="mdi" :path="mdiWalletBifoldOutline"
                                class="w-5 h-5 text-[var(--color-primary)]" />
                            AR Outstanding Balance
                        </h3>
                        <div class="grid grid-cols-1 gap-3">
                            <ReportButton icon="calendar" title="As of Date"
                                @click="showAROutstandingBalanceAO = true" />
                            <ReportButton icon="calendar-days" title="Date Range"
                                @click="showAROutstandingBalanceDR = true" />
                        </div>
                    </div>

                    <!-- Sales Per Item Report Card -->
                    <div
                        class="bg-[var(--color-bg-secondary)]/30 p-5 rounded-lg border border-[var(--color-border)]/30 hover:border-[var(--color-primary)]/50 transition-all">
                        <h3
                            class="font-semibold text-lg pb-3 mb-3 border-b border-[var(--color-border)]/30 flex items-center gap-2">
                            <svg-icon type="mdi" :path="mdiShoppingOutline"
                                class="w-5 h-5 text-[var(--color-primary)]" />
                            Sales Per Item Report
                        </h3>
                        <div class="grid grid-cols-1 gap-3">
                            <ReportButton icon="shopping-bag" title="Sales Per Item" @click="showSalesPerItem = true" />
                        </div>
                    </div>

                    <!-- Overage/Shortage Report Card -->
                    <div
                        class="bg-[var(--color-bg-secondary)]/30 p-5 rounded-lg border border-[var(--color-border)]/30 hover:border-[var(--color-primary)]/50 transition-all">
                        <h3
                            class="font-semibold text-lg pb-3 mb-3 border-b border-[var(--color-border)]/30 flex items-center gap-2">
                            <svg-icon type="mdi" :path="mdiAlertOutline" class="w-5 h-5 text-[var(--color-primary)]" />
                            Overage/Shortage Report
                        </h3>
                        <div class="grid grid-cols-1 gap-3">
                            <ReportButton icon="exclamation-triangle" title="Overage Shortage"
                                @click="showOverageShortage = true" />
                        </div>
                    </div>

                    <!-- Statement of Account Report Card -->
                    <div
                        class="bg-[var(--color-bg-secondary)]/30 p-5 rounded-lg border border-[var(--color-border)]/30 hover:border-[var(--color-primary)]/50 transition-all col-span-1 md:col-span-3">
                        <h3
                            class="font-semibold text-lg pb-3 mb-3 border-b border-[var(--color-border)]/30 flex items-center gap-2">
                            <svg-icon type="mdi" :path="mdiFileDocumentOutline"
                                class="w-5 h-5 text-[var(--color-primary)]" />
                            Statement of Account Report
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <ReportButton icon="document-duplicate" title="SOA" @click="handleSOA('SOA')" />
                            <ReportButton icon="document-minus" title="Summary"
                                @click="showStatementOfAccountSummary = true" />
                            <ReportButton icon="document-minus" title="SOA (DFC)" @click="handleSOA('DFC')" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import InvoiceProoflist from "../Modals/ReportModals/InvoiceProoflist.vue";
import ReportButton from "./Components/ReportButton.vue";
import AdjustmentProoflist from "../Modals/ReportModals/AdjustmentProoflist.vue";
import PaymentProoflist from "../Modals/ReportModals/PaymentProoflist.vue";
import CustomerPDCReport from "../Modals/ReportModals/CustomerPDCReport.vue";
import CustomerARAging from "../Modals/ReportModals/CustomerARAging.vue";
import BegBalProoflist from "../Modals/ReportModals/BegBalProoflist.vue";
import AROutstandingBalanceAO from "../Modals/ReportModals/AROutstandingBalanceAO.vue";
import AROutstandingBalanceDR from "../Modals/ReportModals/AROutstandingBalanceDR.vue";
import SalesPerItem from "../Modals/ReportModals/SalesPerItem.vue";
import StatementOfAccount from "../Modals/ReportModals/statementOfAccount.vue";
import StatementOfAccountSummary from "../Modals/ReportModals/StatementOfAccountSummary.vue";
import InvoiceSummary from "../Modals/ReportModals/InvoiceSummary.vue";
import OverageShortage from "../Modals/ReportModals/OverageShortage.vue";
import {
    mdiAlertOutline,
    mdiBookClockOutline,
    mdiCheckbook,
    mdiCreditCardOutline,
    mdiFileChartOutline,
    mdiFileDocumentOutline,
    mdiShoppingOutline,
    mdiTuneVerticalVariant,
    mdiWalletBifoldOutline,
    mdiWalletOutline,
} from "@mdi/js";

const showInvoiceProoflist = ref(false);
const showInvoiceSummarylist = ref(false);
const showAdjustmentProoflist = ref(false);
const showPaymentProoflistSummary = ref(false);
const showPaymentProoflistDetailed = ref(false);
const showCustomerPDCReport = ref(false);
const showCustomerARAging = ref(false);
const showBegBalProoflist = ref(false);
const showAROutstandingBalanceAO = ref(false);
const showAROutstandingBalanceDR = ref(false);
const showSalesPerItem = ref(false);
const showStatementOfAccount = ref(false);
const showStatementOfAccountSummary = ref(false);
const showOverageShortage = ref(false);


const customerAgingType = ref(null);
const SOAType = ref(null);

const handleCustomerAgingReport = (type) => {
    showCustomerARAging.value = true;
    customerAgingType.value = type;
};
const handleSOA = (type) => {
    showStatementOfAccount.value = true;
    SOAType.value = type;
};
</script>
