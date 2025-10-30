<template>
    <div
        v-if="show"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center px-4"
    >
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <AllTransactionListModal
                v-if="showAllTransactionListModal"
                :customer_code="selected_customer_code"
                :receipt_date="selected_receipt_date"
                :type="'Add'"
                @close="showAllTransactionListModal = false"
            />
        </Transition>
        <ToastAlertWarning :show="showToast" :message="toastMessage" />
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
                message="Are you sure you want to submit?"
                @close="handleConfirm"
            />
        </Transition>
        <transition
            @before-enter="beforeEnter"
            @enter="enter"
            @after-enter="afterEnter"
            @before-leave="beforeLeave"
            @leave="leave"
        >
            <form
                v-if="isExpanded"
                ref="formElement"
                @submit.prevent="submit"
                class="bg-[var(--color-bg-secondary)] text-[var(--color-text-primary)] w-full max-w-5xl rounded-2xl shadow-lg shadow-[#131313a2] border border-[var(--color-border)]"
            >
                <!-- Show spinner while loading -->
                <div
                    v-if="modalLoading"
                    class="flex justify-center items-center py-20"
                >
                    <svg
                        width="60"
                        height="60"
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
                <div v-else class="px-8 py-6">
                    <!-- Header -->
                    <div class="px-8 pb-4">
                        <h2 class="text-2xl font-bold text-center">
                            ADD NEW BEGINNING BALANCE
                        </h2>
                        <div
                            class="mt-2 h-0.5 bg-gradient-to-r from-transparent via-[var(--color-border)] to-transparent"
                        ></div>
                    </div>

                    <div class="flex flex-col md:flex-col gap-4 px-4">
                        <div
                            :class="
                                canInsert('0204-BGBLT')
                                    ? 'grid grid-cols-1 md:grid-cols-2 gap-4'
                                    : 'grid grid-cols-1 md:grid-cols-1 gap-4'
                            "
                        >
                            <div
                                v-if="canInsert('0204-BGBLT')"
                                class="flex flex-col items-center"
                            >
                                <label
                                    class="group transition-all duration-300 relative w-full h-20 rounded-2xl p-1 border-2 border-dashed backdrop-blur-sm flex flex-col items-center justify-center cursor-pointer overflow-hidden"
                                    :class="
                                        form.errors.importFile
                                            ? 'border-red-500  shadow-md'
                                            : 'border-[var(--color-border)]  shadow-md'
                                    "
                                >
                                    <div
                                        class="relative z-10 flex items-center justify-center p-4 text-center gap-2"
                                    >
                                        <span
                                            class="p-3 rounded-full transition-transform duration-300 group-hover:rotate-360"
                                            :class="
                                                form.errors.importFile
                                                    ? 'bg-red-500 border border-red-500 group-hover:bg-transparent'
                                                    : 'bg-[var(--color-border)] border border-[var(--color-border)] group-hover:bg-transparent'
                                            "
                                        >
                                            <svg-icon
                                                type="mdi"
                                                :path="mdiFileUploadOutline"
                                                class="h-8 w-8"
                                                :class="
                                                    form.errors.importFile
                                                        ? 'text-white group-hover:text-red-500 '
                                                        : 'text-white group-hover:text-[var(--color-border)] '
                                                "
                                            />
                                        </span>
                                        <span
                                            class="mt-1 text-sm font-medium text-[var(--color-text-primary)] truncate max-w-full"
                                            :class="
                                                form.errors.importFile
                                                    ? 'text-red-500 '
                                                    : ''
                                            "
                                        >
                                            {{
                                                form.importFile
                                                    ? form.importFile.name
                                                    : "Select an Excel file"
                                            }}
                                        </span>
                                        <span
                                            class="text-xs text-[var(--color-text-primary)] mt-1"
                                            :class="
                                                form.errors.importFile
                                                    ? 'text-red-500 '
                                                    : ''
                                            "
                                            >.xlsx, .xls</span
                                        >
                                    </div>
                                    <input
                                        type="file"
                                        @change="handleImportFileChange"
                                        accept=".xlsx, .xls, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                                        class="hidden"
                                        :disabled="!canInsert('0204-BGBLT')"
                                    />
                                    <div
                                        class="absolute inset-0 bg-[var(--color-border)]/20 opacity-0 group-hover:opacity-100 transition-opacity"
                                        :class="
                                            form.errors.importFile
                                                ? 'bg-gradient-to-br from-transparent to-red-500/20 '
                                                : ''
                                        "
                                    ></div>
                                </label>
                            </div>

                            <!-- Export File Section - Modern Redesign -->
                            <div class="flex flex-col items-center">
                                <button
                                    type="button"
                                    @click="exportTemplate"
                                    class="group relative w-full h-20 rounded-2xl p-1 border-2 border-dashed border-[var(--color-border)] transition-all duration-300 shadow-md backdrop-blur-sm flex flex-col items-center justify-center cursor-pointer overflow-hidden"
                                >
                                    <div
                                        class="relative z-10 flex items-center justify-center p-4 text-center gap-2"
                                    >
                                        <span
                                            class="p-3 rounded-full transition-transform duration-300 group-hover:rotate-360 bg-[var(--color-border)] border border-[var(--color-border)] group-hover:bg-transparent"
                                        >
                                            <svg-icon
                                                type="mdi"
                                                :path="mdiFileDownloadOutline"
                                                class="h-8 w-8 text-white group-hover:text-[var(--color-border)] transition-colors"
                                            />
                                        </span>
                                        <span
                                            class="mt-1 text-sm font-medium text-[var(--color-text-primary)] transition-colors"
                                        >
                                            Download Template
                                        </span>
                                        <span
                                            class="text-xs text-[var(--color-text-primary)] mt-1"
                                            >Pre-formatted Excel</span
                                        >
                                    </div>
                                    <div
                                        class="absolute inset-0 bg-[var(--color-border)]/20 opacity-0 group-hover:opacity-100 transition-opacity"
                                    ></div>
                                </button>
                            </div>
                        </div>
                        <div
                            class="w-full grid sm:grid-cols-1 md:grid-cols-2 gap-4"
                        >
                            <div class="mb-2">
                                <label
                                    class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2"
                                    >Receipt Date</label
                                >
                                <DatePicker
                                    v-model="form.receipt_date"
                                    placeholder="Select Date"
                                    format="MM/DD/YYYY"
                                    :message="form.errors.receipt_date"
                                />
                            </div>
                            <TextInput
                                label="Transaction Date"
                                v-model="form.transaction_date"
                                type="date"
                                readonly
                                :message="form.errors.transaction_date"
                            />
                            <!-- <h3
                            class="col-span-2 cursor-pointer"
                            @click="onAllTransactionsClick()"
                        >
                            <span
                                class="text-sm hover:underline hover:text-green-500"
                                >Click Here to View All Transactions</span
                            >
                        </h3> -->
                        </div>
                        <div
                            class="w-full grid sm:grid-cols-1 md:grid-cols-2 gap-4"
                        >
                            <div
                                class="w-full rounded-xl overflow-hidden border border-[var(--color-border)] backdrop-blur-sm pl-2 col-span-2"
                            >
                                <div class="sticky top-0 z-10 pr-2">
                                    <table
                                        class="w-full text-[var(--color-text-primary)]"
                                    >
                                        <thead
                                            class="border-b border-[var(--color-border)]/50"
                                        >
                                            <tr>
                                                <th
                                                    class="px-5 py-2 text-left w-[25%] tracking-wide uppercase"
                                                >
                                                    Customer Code
                                                </th>
                                                <th
                                                    class="px-5 py-2 text-left w-[35%] tracking-wide uppercase"
                                                >
                                                    Customer Name
                                                </th>

                                                <th
                                                    class="px-5 py-2 text-center w-[20%] tracking-wide uppercase"
                                                >
                                                    Amount
                                                </th>
                                                <th
                                                    class="px-5 py-2 text-center w-[20%] tracking-wide uppercase"
                                                >
                                                    Action
                                                </th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <div class="relative overflow-hidden">
                                    <div
                                        class="max-h-75 overflow-y-auto relative scrollbar-thin scrollbar-thumb-[var(--color-scrollbar-track)] scrollbar-track-[var(--color-primary)]/20 scrollbar-stable [scrollbar-gutter:stable] scrollbar-thumb-rounded-full"
                                    >
                                        <table
                                            class="w-full text-[var(--color-text-primary)] text-sm"
                                        >
                                            <!-- Loading State -->
                                            <tbody v-if="isLoading">
                                                <tr>
                                                    <td
                                                        colspan="4"
                                                        class="text-center py-8"
                                                    >
                                                        <div
                                                            class="flex justify-center items-center"
                                                        >
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
                                            </tbody>

                                            <!-- Data Rows -->
                                            <tbody
                                                v-else
                                                class="divide-y divide-[var(--color-border)]/50 rounded-xl"
                                            >
                                                <tr
                                                    v-for="(
                                                        customer, index
                                                    ) in importedData"
                                                    :key="index"
                                                    class="rounded-xl hover:bg-[var(--color-primary)]/20 transition-colors duration-150 group"
                                                >
                                                    <td
                                                        class="px-5 py-2 font-medium w-[25%]"
                                                    >
                                                        {{
                                                            customer[
                                                                "Customer Code"
                                                            ]
                                                        }}
                                                    </td>
                                                    <td
                                                        class="px-5 py-2 text-left font-medium w-[35%]"
                                                    >
                                                        {{
                                                            customer[
                                                                "Customer Name"
                                                            ]
                                                        }}
                                                    </td>
                                                    <td
                                                        class="px-5 py-2 text-right font-medium w-[20%]"
                                                    >
                                                        {{
                                                            formatCurrency(
                                                                customer[
                                                                    "Amount"
                                                                ]
                                                            )
                                                        }}
                                                    </td>
                                                    <td
                                                        class="px-5 py-2 w-[20%]"
                                                    >
                                                        <div
                                                            class="flex justify-center gap-2"
                                                        >
                                                            <button
                                                                type="button"
                                                                @click="
                                                                    openViewModal(
                                                                        customer
                                                                    )
                                                                "
                                                                class="p-1.5 cursor-pointer rounded-lg transition-all duration-200 bg-[var(--color-primary)]/30 hover:bg-[var(--color-primary)]/50 hover:shadow-lg group-hover:opacity-100"
                                                            >
                                                                <svg-icon
                                                                    type="mdi"
                                                                    :path="
                                                                        mdiEye
                                                                    "
                                                                    class="w-4 h-4 text-[var(--color-bg-avatar)]"
                                                                />
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>

                                                <!-- Empty State -->
                                                <tr
                                                    v-if="
                                                        importedData.length ===
                                                            0 && !isLoading
                                                    "
                                                >
                                                    <td
                                                        colspan="4"
                                                        class="px-5 py-6 text-center"
                                                    >
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
                                                            <p
                                                                class="font-medium"
                                                            >
                                                                No data found.
                                                                Please upload an
                                                                Excel file.
                                                            </p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div
                        class="flex justify-end gap-2 pt-2 border-t border-[var(--color-border)] mt-4"
                    >
                        <button
                            type="button"
                            @click="closeModal"
                            class="closeButton group"
                        >
                            <div class="flex justify-center items-center gap-2">
                                <span
                                    class="transition-transform duration-300 group-hover:rotate-180"
                                >
                                    <svg-icon
                                        type="mdi"
                                        :path="mdiClose"
                                        class="w-5 h-5"
                                    />
                                </span>
                                Close
                            </div>
                        </button>
                        <button
                            type="submit"
                            class="submitButton group"
                            :disabled="form.processing"
                        >
                            <div class="flex justify-center items-center gap-2">
                                <span
                                    class="transition-transform duration-300 group-hover:rotate-405"
                                >
                                    <svg-icon
                                        type="mdi"
                                        :path="mdiNavigationVariantOutline"
                                        class="w-5 h-5"
                                    />
                                </span>
                                <span v-if="form.processing"
                                    >Submitting...</span
                                >
                                <span v-else>Submit</span>
                            </div>
                        </button>
                    </div>
                </div>
            </form>
        </transition>
    </div>
</template>

<script setup>
import { computed, ref, watch, onMounted, onUnmounted, nextTick } from "vue";
import { route } from "../../../../vendor/tightenco/ziggy/src/js";
import TextInput from "../../Pages/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import ToastAlertWarning from "../../Pages/Components/ToastAlertWarning.vue";
import axios from "axios";
import ConfirmationDialog from "../../Pages/Components/ConfirmationDialog.vue";
import AllTransactionListModal from "./AllTransactionListModal.vue";
import { saveAs } from "file-saver"; // Import file-saver
import ExcelJS from "exceljs";
import {
    mdiClose,
    mdiEye,
    mdiFileDownloadOutline,
    mdiFileUploadOutline,
    mdiNavigationVariantOutline,
    mdiUploadOutline,
} from "@mdi/js";
import DatePicker from "../../Pages/Components/DatePicker.vue";
import usePermissions from "../../Pages/Composables/usePermissions";

const props = defineProps({
    show: Boolean,
});

const { canInsert } = usePermissions();
const { canView } = usePermissions();

const form = useForm({
    receipt_date: null,
    transaction_date: null,
    importFile: null,
});

const modalLoading = ref(false);
const showAllTransactionListModal = ref(false);

form.transaction_date = new Date().toISOString().split("T")[0];

const selected_customer_code = ref(null);
const selected_receipt_date = ref(null);
const openViewModal = (customer) => {
    selected_customer_code.value = customer["Customer Code"];
    selected_receipt_date.value = form.receipt_date;
    showAllTransactionListModal.value = true;
};

const emit = defineEmits(["close", "closeSuccess"]);

const closeModal = () => {
    emit("close");
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(amount);
};

///////////////////////////////////////////////////////////////////////////
const isLoading = ref(false);
const importedData = ref([]);

const handleImportFileChange = async (event) => {
    if (!form.receipt_date) {
        showWarningToast("Select Receipt Date First To Upload File");
        event.target.value = "";
        return;
    }
    const file = event.target.files[0];
    if (!file) return;
    form.importFile = file;
    isLoading.value = true;

    try {
        const buffer = await file.arrayBuffer();
        const workbook = new ExcelJS.Workbook();
        await workbook.xlsx.load(buffer);

        const worksheet = workbook.worksheets[0]; // First sheet
        const headerRow = worksheet.getRow(1);

        const headers = headerRow.values.slice(1); // remove empty 0 index
        const requiredHeaders = ["Customer Code", "Customer Name", "Amount"];

        // Validate headers
        for (const required of requiredHeaders) {
            if (!headers.includes(required)) {
                throw new Error(
                    `Invalid Excel format. Missing "${required}" column.`
                );
            }
        }

        // Get indexes of the required columns
        const codeIdx = headers.indexOf("Customer Code") + 1;
        const nameIdx = headers.indexOf("Customer Name") + 1;
        const amountIdx = headers.indexOf("Amount") + 1;

        const data = [];

        worksheet.eachRow((row, rowNumber) => {
            if (rowNumber === 1) return; // Skip header row

            const customerCode = row.getCell(codeIdx).text?.trim();
            const customerName = row.getCell(nameIdx).text?.trim();
            const amount = parseFloat(row.getCell(amountIdx).value) || 0.0;

            data.push({
                "Customer Code": customerCode,
                "Customer Name": customerName,
                Amount: amount.toFixed(2),
            });
        });

        importedData.value = data;
    } catch (error) {
        console.error("Error reading Excel file:", error);
        form.importFile = null;
        importedData.value = [];
        showWarningToast(error.message || "Failed to read Excel file.");
    } finally {
        isLoading.value = false;
    }
};

const exportTemplate = async () => {
    if (!form.receipt_date) {
        showWarningToast("Select Receipt Date First To Download template");
        return;
    }

    try {
        const response = await axios.get(route("getCustomerList"));
        const customers = response.data.map((customer) => ({
            code: customer.cus_code,
            name: customer.cus_name,
        }));

        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet("Customers");

        // Header row
        worksheet.addRow(["Customer Code", "Customer Name", "Amount"]);

        // Data rows
        customers.forEach((customer) => {
            worksheet.addRow([customer.code, customer.name, 0.0]);
        });

        // Set column widths
        worksheet.columns = [
            { key: "code", width: 20 },
            { key: "name", width: 50 },
            { key: "amount", width: 20 },
        ];

        // Lock sheet, allow selection of unlocked cells
        await worksheet.protect("FarMsTeaM", {
            selectLockedCells: true,
            selectUnlockedCells: true,
        });

        // Set protection per cell: lock A & B, unlock C
        worksheet.eachRow((row, rowNumber) => {
            row.getCell(1).protection = { locked: true }; // Column A
            row.getCell(2).protection = { locked: true }; // Column B
            row.getCell(3).protection = { locked: false }; // Column C
        });

        // Format amount column
        worksheet.getColumn(3).numFmt = "#,##0.00";

        // Generate blob
        const buffer = await workbook.xlsx.writeBuffer();
        const blob = new Blob([buffer], {
            type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        });

        saveAs(blob, `BegBal_Template_${form.receipt_date}.xlsx`);
    } catch (error) {
        console.error("Error generating Excel template:", error);
        showWarningToast("Failed to generate template. Please try again.");
    }
};

////////////////TOAST///////////////////////////////////////////////////////////////////////////////////////////////////////
const showToast = ref(false);
const toastMessage = ref("");
let toastTimeout = null; // to keep track of the timeout

const showWarningToast = (message) => {
    toastMessage.value = message;
    showToast.value = false; // Hide first to trigger reactivity if the same toast shows again
    if (toastTimeout) clearTimeout(toastTimeout); // Clear any previous timeout

    // Trigger reactivity again on next tick
    setTimeout(() => {
        showToast.value = true;
    }, 0);

    toastTimeout = setTimeout(() => {
        showToast.value = false;
        toastTimeout = null;
    }, 3000);
};

////////SHOW DIALOG FOR SUBMIT///////////////////////////
const showDialog = ref(false);
const handleConfirm = async (confirmed) => {
    showDialog.value = false;
    if (confirmed) {
        Object.keys(form.errors).forEach((key) => {
            form.errors[key] = "";
        });
        form.post(route("addMultipleBeginningBalance"), {
            onSuccess: () => {
                form.reset(); // clear on success
                emit("closeSuccess");
            },
            onError: (errors) => {
                if (Object.keys(errors).length === 1) {
                    const firstError = Object.values(errors)[0]; // get the first error message
                    showWarningToast(firstError);
                } else if (Object.keys(errors).length !== 1) {
                    showWarningToast("Please Fill Up Necessary Fields");
                }
                console.log(errors); // helpful for debugging
            },
        });
    }
};
//#endregion

//#region /////// WATCH //////////////////////////////////////////////////////////////////////////////////////////////////////////////
watch(
    () => props.show,
    async (visible, oldVisible) => {
        if (visible && !oldVisible) {
            modalLoading.value = true;
            modalLoading.value = false;
        }
    },
    { immediate: true }
);

//#endregion

//////////////////////////SUBMIT////////////////////
const submit = () => {
    showDialog.value = true;
};

//#region ///////////////////////////////////ANIMATION////////////////////////////////////////
///////////////////////////////////////////////////FORM ANIMATION////////////////////////////
const formElement = ref(null);
const isExpanded = ref(true); // Control this with your v-if condition

// Handle dynamic content changes
watch(
    () => importedData.value.length, // Watch whatever causes your form to expand
    async () => {
        if (!formElement.value || !isExpanded.value) return;

        // Start transition
        formElement.value.style.transition = "height 300ms ease-in-out";
        formElement.value.style.overflow = "hidden";

        // Set current height
        const startHeight = formElement.value.scrollHeight;
        formElement.value.style.height = `${startHeight}px`;

        await nextTick();

        // Get new height after content change
        const endHeight = formElement.value.scrollHeight;

        // Only animate if height actually changed
        if (startHeight !== endHeight) {
            formElement.value.style.height = `${endHeight}px`;

            // Clean up after animation completes
            const onTransitionEnd = () => {
                formElement.value.style.height = "";
                formElement.value.style.overflow = "";
                formElement.value.style.transition = "";
                formElement.value.removeEventListener(
                    "transitionend",
                    onTransitionEnd
                );
            };

            formElement.value.addEventListener(
                "transitionend",
                onTransitionEnd
            );
        } else {
            // No height change needed
            formElement.value.style.height = "";
            formElement.value.style.overflow = "";
            formElement.value.style.transition = "";
        }
    },
    { deep: true }
);

// Initial expand animation
const beforeEnter = (el) => {
    el.style.height = "0";
    el.style.overflow = "hidden";
};

const enter = (el) => {
    el.style.height = `${el.scrollHeight}px`;
};

const afterEnter = (el) => {
    el.style.height = "";
    el.style.overflow = "";
};

// Collapse animation
const beforeLeave = (el) => {
    el.style.height = `${el.scrollHeight}px`;
    el.style.overflow = "hidden";
};

const leave = (el) => {
    requestAnimationFrame(() => {
        el.style.height = "0";
    });
};
/////////////////////////TABLE ANIMATION/////////////////////////////////////
//#endregion
</script>

<style scoped>
@keyframes fadeIn {
    0% {
        opacity: 0;
        transform: translateY(10px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeIn 0.4s ease-out;
}

form {
    transition: box-shadow 300ms ease, border-radius 300ms ease;
}

/* Fallback for height transitions */
.v-enter-active,
.v-leave-active {
    transition: height 300ms ease-in-out;
}
</style>
