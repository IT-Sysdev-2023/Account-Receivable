<template>
    <div>
        <Head :title="` | ${$page.component}`" />
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <ViewCustomerLedger
                v-if="showViewModal"
                :show="showViewModal"
                :selected="selectedRow"
                :selectedcustname="selectedcusname"
                @close="closeViewModal"
            />
        </Transition>
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <PaymenHistory
                v-if="showViewPaymentHistoryModal"
                :customer_code="selectedPaymentRowCustomerCode"
                :document_no="selectedPaymentRowDocumentNO"
                :type="selectedPaymentRowType"
                @close="closeViewPaymentHistoryModal"
            />
        </Transition>

        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <CustomerListModal
                v-if="showCustomerModal"
                :show="showCustomerModal"
                @close="showCustomerModal = false"
                @submit="handleSelectedCustomer"
            />
        </Transition>

        <ToastAlert :show="showToast" :message="toastMessage" />
        <ToastAlertWarning :show="showWToast" :message="toastWMessage" />

        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <ConfirmationDialog
                :show="showDialog"
                message="Are you sure about deleting this packing type?"
                @close="handleConfirm"
            />
        </Transition>
        <div
            class="flex flex-col lg:flex-row justify-between items-start lg:items-center pb-3 pt-1 gap-4"
        >
            <div class="w-full lg:w-auto lg:flex-shrink-0">
                <div
                    class="text-[var(--color-text-primary)] flex justify-between font-medium w-1/2 px-3 py-2 rounded-lg border border-[var(--color-border)] min-w-[280px]"
                >
                    <span class="text-base font-semibold text-left"
                        >Amount Forwarded :
                    </span>
                    <span class="text-base font-semibold text-left">
                        {{ formatCurrency(paymentForwarded) }}</span
                    >
                </div>
            </div>
            <div
                class="flex flex-col sm:flex-row justify-end items-stretch sm:items-center gap-2 w-full lg:w-auto lg:flex-1 lg:max-w-2xl"
            >
                <div class="relative w-full sm:w-64 lg:w-72">
                    <input
                        type="search"
                        id="Search"
                        v-model="search"
                        placeholder=" "
                        class="peer w-full"
                        ref="searchInput"
                        autocomplete="off"
                    />
                    <button
                        v-if="search"
                        @click="clearSearch"
                        class="absolute top-1/2 right-2 transform -translate-y-1/2 text-[var(--color-text-primary)] hover:text-red-500"
                    >
                        <svg-icon
                            type="mdi"
                            :path="mdiClose"
                            class="w-5 h-5 hover:text-red-500"
                        />
                    </button>
                    <div
                        v-else
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-[var(--color-text-secondary)]"
                    >
                        <svg-icon type="mdi" :path="mdiMagnify" size="20" />
                    </div>
                    <label
                        for="Search"
                        class="absolute left-0 -top-2 rounded px-1 text-sm text-[var(--color-text-primary)] transition-all peer-placeholder-shown:top-2.5 peer-placeholder-shown:text-sm peer-placeholder-shown:text-[var(--color-text-primary)] peer-focus:-top-2 peer-focus:text-sm peer-focus:text-[var(--color-text-primary)] cursor-text"
                    >
                        Search Here ...
                    </label>
                </div>
                <!-- Export to Excel -->
                <div class="w-full sm:w-auto">
                    <button
                        :disabled="
                            (!isLoading &&
                                filteredLedgers.data.length === 0 &&
                                generateTableData) ||
                            !generateTableData
                        "
                        @click="exportToExcel"
                        class="w-full sm:w-auto px-4 py-2 rounded-md font-medium transition-all duration-300 flex items-center justify-center gap-2 relative overflow-hidden disabled:opacity-70 disabled:cursor-not-allowed group bg-[var(--color-primary)] text-white hover:bg-transparent hover:text-[var(--color-primary)] hover:ring-1 hover:ring-[var(--color-primary)]"
                    >
                        <div
                            class="relative flex items-center justify-center gap-2"
                        >
                            <span
                                class="transition-transform duration-300 group-hover:rotate-360"
                            >
                                <svg-icon
                                    type="mdi"
                                    :path="mdiMicrosoftExcel"
                                    class="w-5 h-5"
                                />
                            </span>
                            <span>Export To Excel</span>
                        </div>
                    </button>
                </div>
                <!-- Filter Dropdown -->
                <div class="relative w-full sm:w-auto" ref="dropdownContainer">
                    <button
                        @click="toggleFilters"
                        class="w-full sm:w-auto px-4 py-2 rounded-md font-medium transition-all duration-300 flex items-center justify-center gap-2 relative overflow-hidden disabled:opacity-70 disabled:cursor-not-allowed group"
                        :class="{
                            'bg-transparent text-[var(--color-primary)] ring-1 ring-[var(--color-primary)]':
                                showFilters,
                            'bg-[var(--color-primary)] text-white hover:bg-transparent hover:text-[var(--color-primary)] hover:ring-1 hover:ring-[var(--color-primary)]':
                                !showFilters,
                        }"
                    >
                        <div
                            class="relative flex items-center justify-center gap-2"
                        >
                            <span
                                class="transition-transform duration-300"
                                :class="{
                                    'rotate-360': showFilters,
                                    'group-hover:rotate-360': !showFilters,
                                }"
                            >
                                <FunnelIcon class="w-5 h-5" />
                            </span>
                            <span>Filters</span>
                            <span class="h-5 w-5">
                                <span
                                    v-if="activeFiltersCount > 0"
                                    class="bg-[var(--color-primary-hover)] text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"
                                >
                                    {{ activeFiltersCount }}
                                </span>
                            </span>
                        </div>
                    </button>

                    <!-- Filter Panel -->
                    <Transition
                        enter-active-class="transition ease-out duration-100"
                        enter-from-class="transform opacity-0 scale-95"
                        enter-to-class="transform opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-75"
                        leave-from-class="transform opacity-100 scale-100"
                        leave-to-class="transform opacity-0 scale-95"
                    >
                        <div
                            v-if="showFilters"
                            class="absolute right-0 z-20 mt-2 w-lg bg-[var(--color-bg-secondary)] text-[var(--color-text-primary)] rounded-md shadow-lg shadow-[#131313a2] border border-[var(--color-border)]"
                        >
                            <div class="p-4 space-y-4">
                                <!-- Customer Filter -->
                                <div>
                                    <h3 class="text-sm font-medium mb-2">
                                        Customer
                                    </h3>
                                    <div>
                                        <label class="block text-xs mb-1"
                                            >Customer Code</label
                                        >
                                        <TextInput
                                            type="text"
                                            v-model="cus_code"
                                            @click="onCustomerClick()"
                                            selectable="yes"
                                        />
                                    </div>
                                </div>

                                <!-- Date Filter -->
                                <div>
                                    <h3 class="text-sm font-medium mb-2">
                                        Date Range
                                    </h3>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-xs mb-1"
                                                >From</label
                                            >
                                            <div class="mb-2">
                                                <DatePicker
                                                    v-model="dateRange.start"
                                                    placeholder="Select Date"
                                                    format="MM/DD/YYYY"
                                                />
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs mb-1"
                                                >To</label
                                            >
                                            <div class="mb-2">
                                                <DatePicker
                                                    v-model="dateRange.end"
                                                    placeholder="Select Date"
                                                    format="MM/DD/YYYY"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- table filter  -->
                                <div>
                                    <h3 class="text-sm font-medium mb-2">
                                        Type
                                    </h3>
                                    <div class="grid grid-cols-1 gap-2">
                                        <label
                                            class="flex items-center space-x-2"
                                        >
                                            <label
                                                class="inline-flex items-center cursor-pointer group"
                                            >
                                                <input
                                                    type="radio"
                                                    v-model="typeFilters"
                                                    value="With Floating Deducted"
                                                    class="hidden peer"
                                                />
                                                <div
                                                    class="relative flex items-center justify-center"
                                                >
                                                    <!-- Radio button -->
                                                    <div
                                                        class="relative w-4 h-4 mr-2 rounded-full border-2 border-[var(--color-bg-avatar)] transition-colors z-10 group-hover:border-[var(--color-border)]"
                                                        :class="{
                                                            'border-[var(--color-border)]':
                                                                typeFilters ===
                                                                'With Floating Deducted',
                                                        }"
                                                    >
                                                        <div
                                                            class="absolute inset-0 m-auto w-1.5 h-1.5 rounded-full bg-[var(--color-border)] transition-opacity"
                                                            :class="{
                                                                'opacity-100':
                                                                    typeFilters ===
                                                                    'With Floating Deducted',
                                                                'opacity-0':
                                                                    typeFilters !==
                                                                    'With Floating Deducted',
                                                            }"
                                                        ></div>
                                                    </div>
                                                    <span class="text-sm z-10"
                                                        >With Floating
                                                        Deducted</span
                                                    >
                                                </div>
                                            </label>
                                        </label>

                                        <label
                                            class="flex items-center space-x-2"
                                        >
                                            <label
                                                class="inline-flex items-center cursor-pointer group"
                                            >
                                                <input
                                                    type="radio"
                                                    v-model="typeFilters"
                                                    value="Without Floating Deducted"
                                                    class="hidden peer"
                                                />
                                                <div
                                                    class="relative flex items-center justify-center"
                                                >
                                                    <!-- Radio button -->
                                                    <div
                                                        class="relative w-4 h-4 mr-2 rounded-full border-2 border-[var(--color-bg-avatar)] transition-colors z-10 group-hover:border-[var(--color-border)]"
                                                        :class="{
                                                            'border-[var(--color-border)]':
                                                                typeFilters ===
                                                                'Without Floating Deducted',
                                                        }"
                                                    >
                                                        <div
                                                            class="absolute inset-0 m-auto w-1.5 h-1.5 rounded-full bg-[var(--color-border)] transition-opacity"
                                                            :class="{
                                                                'opacity-100':
                                                                    typeFilters ===
                                                                    'Without Floating Deducted',
                                                                'opacity-0':
                                                                    typeFilters !==
                                                                    'Without Floating Deducted',
                                                            }"
                                                        ></div>
                                                    </div>
                                                    <span class="text-sm z-10"
                                                        >Without Floating
                                                        Deducted</span
                                                    >
                                                </div>
                                            </label>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="bg-[var(--color-bg-secondary)] border-t border-[var(--color-border)] px-4 py-3 flex justify-between"
                            >
                                <button
                                    @click="resetFilters"
                                    class="text-xs font-semibold px-3 py-1 rounded"
                                >
                                    Reset All
                                </button>
                                <button
                                    @click="applyFilters"
                                    :disabled="!hasActiveFilters"
                                    class="text-xs font-semibold text-white bg-[var(--color-primary)] hover:bg-[var(--color-primary-hover)] px-3 py-1 rounded"
                                >
                                    Apply Filters
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>

        <div
            class="bg-[var(--color-bg-secondary)]/20 p-4 rounded-md shadow-[0_0_20px_var(--color-shadow)]/20 mt-4"
        >
            <table
                class="w-full text-sm text-[var(--color-text-primary)] rounded-xl overflow-hidden mb-2"
            >
                <thead class="sticky top-0 z-10">
                    <tr>
                        <th
                            class="px-3 py-2 w-[12%] text-left font-semibold tracking-wider"
                        >
                            DOCUMENT NO
                        </th>
                        <th
                            class="px-3 py-2 w-[10%] text-left font-semibold tracking-wider"
                        >
                            DATE
                        </th>
                        <th
                            class="px-3 py-2 w-[28%] text-left font-semibold tracking-wider"
                        >
                            CUSTOMER NAME
                        </th>
                        <th
                            class="px-3 py-2 w-[10%] text-center font-semibold tracking-wider"
                        >
                            TYPE
                        </th>
                        <th
                            class="px-3 py-2 w-[10%] text-center font-semibold tracking-wider"
                        >
                            DEBIT
                        </th>
                        <th
                            class="px-3 py-2 w-[10%] text-center font-semibold tracking-wider"
                        >
                            CREDIT
                        </th>
                        <th
                            class="px-3 py-2 w-[10%] text-center font-semibold tracking-wider"
                        >
                            BALANCE
                        </th>
                        <th
                            class="px-3 py-2 w-[10%] text-center font-semibold tracking-wider"
                        >
                            ACTION
                        </th>
                    </tr>
                </thead>

                <!-- Modern Body -->
                <tbody>
                    <!-- Loading State -->
                    <tr
                        v-if="
                            isLoading &&
                            filteredLedgers.data.length !== 0 &&
                            generateTableData
                        "
                    >
                        <td colspan="8" class="text-center py-8">
                            <div class="flex justify-center items-center">
                                <svg
                                    width="30"
                                    height="30"
                                    viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="var(--color-icon)"
                                >
                                    <rect
                                        class="spinner_jCIR"
                                        x="1"
                                        y="6"
                                        width="2.8"
                                        height="12"
                                    />
                                    <rect
                                        class="spinner_jCIR spinner_upm8"
                                        x="5.8"
                                        y="6"
                                        width="2.8"
                                        height="12"
                                    />
                                    <rect
                                        class="spinner_jCIR spinner_2eL5"
                                        x="10.6"
                                        y="6"
                                        width="2.8"
                                        height="12"
                                    />
                                    <rect
                                        class="spinner_jCIR spinner_Rp9l"
                                        x="15.4"
                                        y="6"
                                        width="2.8"
                                        height="12"
                                    />
                                    <rect
                                        class="spinner_jCIR spinner_dy3W"
                                        x="20.2"
                                        y="6"
                                        width="2.8"
                                        height="12"
                                    />
                                </svg>
                            </div>
                        </td>
                    </tr>
                    <tr
                        v-else-if="
                            !isLoading &&
                            filteredLedgers.data.length !== 0 &&
                            generateTableData
                        "
                        v-for="customerledger in filteredLedgers.data"
                        :key="customerledger.id"
                        class="hover:bg-[var(--color-primary)]/20 transition-colors duration-150 group h-10"
                    >
                        <td class="px-3 py-1 font-medium">
                            {{ customerledger.invoice_number }}
                        </td>
                        <td class="px-3 py-1">
                            {{ formatDate(customerledger.date) }}
                        </td>
                        <td class="px-3 py-1 font-medium">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-full bg-[var(--color-bg-avatar)] flex items-center justify-center text-white"
                                >
                                    {{
                                        getFirstValidChar(
                                            customerledger.customer_name ||
                                                "Loading..."
                                        )
                                    }}
                                </div>
                                <div>
                                    <div class="font-medium">
                                        {{
                                            customerledger.customer_name ||
                                            "Loading..."
                                        }}
                                    </div>
                                    <div
                                        class="text-xs text-[var(--color-text-secondary)] mt-0.5"
                                    >
                                        {{
                                            customerledger.customer_code ||
                                            "No TIN"
                                        }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-1 text-center">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                :class="{
                                    'bg-emerald-700 text-emerald-300':
                                        customerledger.type === 'Sales Invoice',
                                    'bg-cyan-700 text-cyan-300':
                                        customerledger.type ===
                                        'Charge Invoice',
                                    'bg-pink-700 text-pink-300':
                                        customerledger.type === 'Payment',
                                    'bg-purple-700 text-purple-300':
                                        customerledger.type === 'BG',
                                }"
                            >
                                {{ customerledger.type }}
                            </span>
                        </td>
                        <td class="px-3 py-1 text-right">
                            {{
                                formatCurrency(
                                    calculateNetAmount(customerledger)
                                )
                            }}
                        </td>
                        <td class="px-3 py-1 text-right">
                            {{ formatCurrency(customerledger.amount_paid) }}
                        </td>
                        <td class="px-3 py-1 text-right">
                            {{ formatCurrency(customerledger.running_balance) }}
                        </td>
                        <td class="px-3 py-1">
                            <div class="flex justify-center gap-2">
                                <button
                                    @click="
                                        openViewModal(
                                            customerledger,
                                            customerledger.customer_name
                                        )
                                    "
                                    class="p-1.5 cursor-pointer rounded-lg transition-all duration-200 bg-[var(--color-primary)]/30 hover:bg-[var(--color-primary)]/50 hover:shadow-lg group-hover:opacity-100"
                                >
                                    <svg-icon
                                        type="mdi"
                                        :path="mdiEye"
                                        class="w-4 h-4 text-[var(--color-primary)]"
                                    />
                                </button>
                                <button
                                    @click="
                                        openViewPaymentHistoryModal(
                                            customerledger
                                        )
                                    "
                                    class="p-1.5 cursor-pointer rounded-lg transition-all duration-200 bg-sky-500/30 hover:bg-sky-500/50 hover:shadow-lg group-hover:opacity-100"
                                >
                                    <svg
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.5"
                                        stroke="currentColor"
                                        class="w-4 h-4 text-sky-600"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <!-- Empty State -->
                    <tr
                        v-if="
                            (!isLoading &&
                                filteredLedgers.data.length === 0 &&
                                generateTableData) ||
                            !generateTableData
                        "
                    >
                        <td colspan="8" class="px-5 py-6 text-center">
                            <div
                                class="flex flex-col items-center justify-center text-[var(--color-text-primary)]"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-10 w-10 mb-2"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                    />
                                </svg>
                                <p class="font-medium">No data found</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination Links -->
            <div
                v-if="
                    isLoading ||
                    filteredLedgers.data.length === 0 ||
                    !generateTableData
                "
            />
            <div v-else>
                <PaginationLinks :paginator="filteredLedgers" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, computed, onMounted, onUnmounted, nextTick } from "vue";
import PaginationLinks from "./Components/PaginationLinks.vue";
import { router } from "@inertiajs/vue3";
import { debounce } from "lodash";
import ToastAlert from "./Components/ToastAlert.vue";
import ConfirmationDialog from "./Components/ConfirmationDialog.vue";
import TextInput from "./Components/TextInput.vue";
import { route } from "../../../vendor/tightenco/ziggy/src/js";
import ViewCustomerLedger from "../Modals/ReportModals/ViewCustomerLedger.vue";
import PaymenHistory from "../Modals/ReportModals/PaymenHistory.vue";
import CustomerListModal from "../Modals/TransactionModals/CustomerListModal.vue";
import { mdiClose, mdiEye, mdiMagnify, mdiMicrosoftExcel } from "@mdi/js";
import { FunnelIcon } from "@heroicons/vue/24/solid";
import DatePicker from "./Components/DatePicker.vue";
import ToastAlertWarning from "./Components/ToastAlertWarning.vue";
import { saveAs } from "file-saver"; // Import file-saver
import ExcelJS from "exceljs";

const props = defineProps({
    customerledgers: Object,
    searchTerm: String,
    paymentForwarded: Number,
    can: Object,
    filters: Object,
    generateTableData: Boolean,
    broadcastChannel: String,
});

const showModal = ref(false);
const showViewModal = ref(false);
const selectedRow = ref(null);
const selectedcusname = ref(null);
const showToast = ref(false);
const toastMessage = ref("");
const showDialog = ref(false);
const pendingDeleteID = ref(null);
const selectedPaymentRowCustomerCode = ref(null);
const selectedPaymentRowDocumentNO = ref(null);
const selectedPaymentRowType = ref(null);
const showViewPaymentHistoryModal = ref(false);

const showCustomerModal = ref(false);

// Filter functionality (new)
const showFilters = ref(false);
const cus_code = ref(props.filters?.customer_code || null);
const typeFilters = ref(props.filters?.type_filters || null);
const dateRange = ref({
    start: props.filters?.date_start || null,
    end: props.filters?.date_end || null,
});
const generateTableData = ref(props.generateTableData ?? false);

const search = ref(props.searchTerm);

const showSuccessToast = (message) => {
    toastMessage.value = message;
    showToast.value = true;

    setTimeout(() => {
        showToast.value = false;
    }, 3000); // Show toast for 3 seconds
};

/////////////////////////CUSTOMER CODE FETCH DATA FROM TABLE//////////////////////////////////////
function onCustomerClick() {
    showCustomerModal.value = true;
    showFilters.value = !showFilters.value;
}
const handleSelectedCustomer = (selectedData) => {
    cus_code.value = selectedData.cus_code;

    showCustomerModal.value = false;
    setTimeout(() => {
        showFilters.value = true;
    }, 0);
};

async function openModal() {
    showModal.value = true;
}
const closeModal = () => {
    showModal.value = false; // Close the modal
};
const closeSuccessModal = () => {
    showModal.value = false; // Close the modal
    showSuccessToast("Payment Has Been Added Successfully");
};

const openViewModal = (selected, customername) => {
    selectedRow.value = selected;
    selectedcusname.value = customername;
    showViewModal.value = true;
};
const closeViewModal = () => {
    showViewModal.value = false; // Close the modal
};

const openViewPaymentHistoryModal = (selected) => {
    selectedPaymentRowCustomerCode.value = selected.customer_code;
    selectedPaymentRowDocumentNO.value = selected.invoice_number;
    selectedPaymentRowType.value = selected.type;
    showViewPaymentHistoryModal.value = true;
};
const closeViewPaymentHistoryModal = () => {
    showViewPaymentHistoryModal.value = false; // Close the modal
};

const closeEditSuccessModal = () => {
    showViewModal.value = false; // Close the modal
    showSuccessToast("Payment has Been Updated Successfully");
};

const deleteItem = async (adjust) => {
    pendingDeleteID.value = adjust;
    showDialog.value = true;
};

const formatDate = (dateString) => {
    const options = { year: "numeric", month: "short", day: "numeric" };
    return new Date(dateString).toLocaleDateString(undefined, options);
};

const calculateNetAmount = (ledger) => {
    if (ledger.type === "Sales Invoice") {
        const amount = parseFloat(ledger.amount) || 0;
        const returnamount = parseFloat(ledger.return) || 0;
        const overage = parseFloat(ledger.overage) || 0;
        const shrinkage = parseFloat(ledger.shrinkage) || 0;
        const adjustment = parseFloat(ledger.adjusted_amount) || 0;
        const temptot = amount - shrinkage + overage - returnamount;
        return parseFloat((temptot + adjustment).toFixed(2)); // Since adjustment is already negative
    } else {
        const amount = parseFloat(ledger.amount) || 0;
        const adjustment = parseFloat(ledger.adjusted_amount) || 0;
        return parseFloat((amount + adjustment).toFixed(2)); // Since adjustment is already negative
    }
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(amount);
};

const getFirstValidChar = (name) => {
    if (!name) return "";

    const trimmedName = name.trim();
    for (let i = 0; i < trimmedName.length; i++) {
        if (trimmedName[i] !== " ") {
            return trimmedName[i].toUpperCase();
        }
    }
    return "";
};

const handleConfirm = async (confirmed) => {
    showDialog.value = false;
    if (confirmed && pendingDeleteID.value) {
        try {
            router.delete(route("deleteAdjustment", pendingDeleteID.value), {
                onSuccess: () => {
                    showSuccessToast("Payment has been deleted successfully");
                },
                onError: (errors) => {
                    console.error("Failed to delete Payment:", errors);
                },
            });
        } catch (error) {
            console.error("Unexpected error deleting Payment:", error);
        }
    }
    pendingDeleteID.value = null;
};

const searchInput = ref(null); // declare ref
const clearSearch = () => {
    search.value = ""; // Clear search input

    nextTick(() => {
        searchInput.value?.focus();
    });
};

const isLoading = ref(false);
let debounceTimeout = null;
const filteredLedgers = ref({ ...props.customerledgers });
const performSearch = debounce((q) => {
    const filters = {
        search: q,
        customer_code: cus_code.value,
        type_filters: typeFilters.value,
        date_start: dateRange.value.start,
        date_end: dateRange.value.end,
        generateTableData: true,
    };

    router.get(route("customerledger"), filters, {
        preserveState: true,
        replace: true,
        onFinish: () => {
            isLoading.value = false;
        },
    });
    generateTableData.value = true;
}, 1000);
// watch(search, (q) => {
//     isLoading.value = true;
//     performSearch(q);
// });
watch(
    () => props.customerledgers,
    (newValue, oldValue) => {
        if (newValue !== oldValue) {
            filteredLedgers.value = { ...props.customerledgers };
        }
    }
);
watch(
    () => search.value,
    (query) => {
        isLoading.value = true;
        if (debounceTimeout) clearTimeout(debounceTimeout);

        debounceTimeout = setTimeout(() => {
            if (!query.trim()) {
                filteredLedgers.value = { ...props.customerledgers };
            } else {
                filteredLedgers.value.data = props.customerledgers.data.filter(
                    (customer) =>
                        (customer.invoice_number ?? "")
                            .toString()
                            .toLowerCase()
                            .includes(query.toLowerCase())
                );
                // Optional: reset pagination links, since it's now a filtered local list
                filteredLedgers.value.links = [];
                filteredLedgers.value.total = filteredLedgers.value.data.length;
                filteredLedgers.value.current_page = 1;
                filteredLedgers.value.last_page = 1;
            }
            isLoading.value = false;
        }, 400);
    }
);

// Filter functionality (new)
const toggleFilters = () => {
    showFilters.value = !showFilters.value;
};

const resetFilters = () => {
    cus_code.value = null;
    typeFilters.value = null;
    dateRange.value = { start: null, end: null };
    generateTableData.value = false;

    router.get(
        route("customerledger"),
        {
            generateTableData: false,
        },
        {
            preserveState: true,
            replace: true,
            onStart: () => (isLoading.value = true),
            onFinish: () => {
                isLoading.value = false;
            },
        }
    );
};

const clearDates = () => {
    dateRange.value = {
        start: null,
        end: null,
    };
};

const applyFilters = () => {
    const filters = {
        customer_code: cus_code.value,
        type_filters: typeFilters.value,
        date_start: dateRange.value.start,
        date_end: dateRange.value.end,
        generateTableData: true,
    };

    router.get(route("customerledger"), filters, {
        preserveState: true,
        replace: true,
        onStart: () => (isLoading.value = true),
        onFinish: () => {
            isLoading.value = false;
            showFilters.value = false;
        },
    });
    generateTableData.value = true;
};

// Compute active filter count for badge
const activeFiltersCount = computed(() => {
    let count = 0;
    if (cus_code.value) count++;
    if (typeFilters.value) count++;
    if (dateRange.value.start || dateRange.value.end) count++;
    return count;
});

const dropdownContainer = ref(null);

const handleClickOutside = (event) => {
    if (
        dropdownContainer.value &&
        !dropdownContainer.value.contains(event.target)
    ) {
        showFilters.value = false;
    }
};

onMounted(() => {
    // document.addEventListener("click", handleClickOutside);

    try {
        window.Echo.channel(props.broadcastChannel)
            .listen(".customerledger.created", () => {
                if (!showModal.value) {
                    router.reload({
                        preserveState: true,
                        only: ["customerledgers", "paymentForwarded"],
                        onFinish: () => {},
                    });
                }
            })
            .error((error) => {
                console.error("Echo error:", error);
            });
    } catch (error) {
        console.error("Error initializing Echo:", error);
    }
});

onUnmounted(() => {
    // document.removeEventListener("click", handleClickOutside);

    window.Echo.leaveChannel(props.broadcastChannel);
});

const hasActiveFilters = computed(() => {
    return (
        cus_code.value &&
        typeFilters.value &&
        dateRange.value.start &&
        dateRange.value.end
    );
});

const showWToast = ref(false);
const toastWMessage = ref("");
let toastTimeout = null; // to keep track of the timeout

const showWarningToast = (message) => {
    toastWMessage.value = message;
    showWToast.value = false;
    if (toastTimeout) clearTimeout(toastTimeout);

    setTimeout(() => {
        showWToast.value = true;
    }, 0);

    toastTimeout = setTimeout(() => {
        showWToast.value = false;
        toastTimeout = null;
    }, 3000);
};

const exportToExcel = async () => {
    try {
        if (
            !filteredLedgers.value ||
            !filteredLedgers.value.data ||
            filteredLedgers.value.data.length === 0
        ) {
            showWarningToast("No data available to export.");
            return;
        }

        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet("Customer Ledgers");

        const headerRow = worksheet.addRow([
            "Invoice Number",
            "Date",
            "Customer Name",
            "Customer Code",
            "Type",
            "Net Amount",
            "Amount Paid",
            "Running Balance",
        ]);

        headerRow.eachCell((cell) => {
            cell.font = { bold: true, color: { argb: "FFFFFF" } };
            cell.fill = {
                type: "pattern",
                pattern: "solid",
                fgColor: { argb: "027937" },
            };
            cell.border = {
                top: { style: "thin" },
                left: { style: "thin" },
                bottom: { style: "thin" },
                right: { style: "thin" },
            };
            cell.alignment = { horizontal: "center", vertical: "middle" };
        });

        filteredLedgers.value.data.forEach((customerledger) => {
            const row = worksheet.addRow([
                customerledger.invoice_number || "",
                customerledger.date ? formatDate(customerledger.date) : "",
                customerledger.customer_name || "Loading...",
                customerledger.customer_code || "No Code",
                customerledger.type || "",
                calculateNetAmount(customerledger) || 0,
                customerledger.amount_paid || 0,
                customerledger.running_balance || 0,
            ]);

            row.eachCell((cell) => {
                cell.border = {
                    top: { style: "thin" },
                    left: { style: "thin" },
                    bottom: { style: "thin" },
                    right: { style: "thin" },
                };
            });
        });

        worksheet.columns = [
            { key: "invoice_number", width: 20 },
            { key: "date", width: 15 },
            { key: "customer_name", width: 35 },
            { key: "customer_code", width: 15 },
            { key: "type", width: 18 },
            { key: "net_amount", width: 15 },
            { key: "amount_paid", width: 15 },
            { key: "running_balance", width: 18 },
        ];

        const currencyColumns = [6, 7, 8]; // Columns F, G, H
        currencyColumns.forEach((colIndex) => {
            worksheet.getColumn(colIndex).numFmt = "#,##0.00";
        });

        worksheet.getColumn(2).numFmt = "mm/dd/yyyy";

        worksheet.getColumn(2).alignment = { horizontal: "center" }; // Date
        worksheet.getColumn(4).alignment = { horizontal: "center" }; // Customer Code
        worksheet.getColumn(5).alignment = { horizontal: "center" }; // Type

        currencyColumns.forEach((colIndex) => {
            worksheet.getColumn(colIndex).alignment = { horizontal: "right" };
        });

        worksheet.insertRow(1, ["Customer Ledgers Report"]);
        worksheet.insertRow(2, ["Generated on:", new Date().toLocaleString()]);
        worksheet.insertRow(3, [
            "Total Records:",
            filteredLedgers.value.data.length,
        ]);
        worksheet.insertRow(4, ["Payment Forwarded:", props.paymentForwarded]);
        worksheet.insertRow(5, []);

        worksheet.getCell("A1").font = {
            bold: true,
            size: 14,
            color: { argb: "FFFFFF" },
        };
        worksheet.getCell("A2").font = { bold: true };
        worksheet.getCell("A3").font = { bold: true };
        worksheet.getCell("A4").font = { bold: true };

        worksheet.getCell("B3").numFmt = "0";
        worksheet.getCell("B3").value = filteredLedgers.value.data.length;

        worksheet.getCell("B4").numFmt = "#,##0.00";
        worksheet.getCell("B4").value = props.paymentForwarded;

        worksheet.mergeCells("A1:H1");
        worksheet.getCell("A1").alignment = {
            horizontal: "center",
            vertical: "middle",
        };
        worksheet.getCell("A1").fill = {
            type: "pattern",
            pattern: "solid",
            fgColor: { argb: "027937" },
        };

        const dataRange = `A6:H${6 + filteredLedgers.value.data.length}`;
        worksheet.autoFilter = dataRange;

        worksheet.views = [{ state: "frozen", xSplit: 0, ySplit: 6 }];

        const currentDate = new Date().toISOString().split("T")[0];
        let filename = `Customer_Ledgers_${currentDate}`;

        if (cus_code.value) {
            filename += `_${cus_code.value}`;
        }
        if (typeFilters.value) {
            filename += `_${typeFilters.value.replace(/\s+/g, "_")}`;
        }

        filename += ".xlsx";

        const buffer = await workbook.xlsx.writeBuffer();
        const blob = new Blob([buffer], {
            type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        });

        saveAs(blob, filename);

        showSuccessToast(
            `Excel File Exported Successfully With ${filteredLedgers.value.data.length} Records`
        );
    } catch (error) {
        console.error("Error generating Excel file:", error);
        showWarningToast("Failed to generate Excel file. Please try again.");
    }
};
</script>
