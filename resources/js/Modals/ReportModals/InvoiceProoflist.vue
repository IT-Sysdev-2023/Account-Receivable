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
            <ConfirmationDialog
                :show="showDialog"
                message="Do you want to print the Report?"
                @close="handleConfirm"
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
            <PdfPreviewModal
                v-if="showPdfModal"
                :show="showPdfModal"
                :apiEndpoint="apiRoute"
                :formData="pdfFormData"
                @closeSuccess="pdfPrintSuccess"
                @close="pdfPrintSuccess"
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

        <ToastAlertWarning :show="showToast" :message="toastMessage" />

        <div
            class="bg-[var(--color-bg-secondary)] text-[var(--color-text-primary)] w-full max-w-3xl rounded-2xl border border-[var(--color-border)]"
        >
            <form @submit.prevent="submit">
                <div class="px-8 py-6">
                    <!-- Header -->
                    <div class="px-8 pb-4">
                        <h2 class="text-2xl font-bold text-center">
                            INVOICE PROOFLIST
                        </h2>
                        <div
                            class="mt-2 h-0.5 bg-gradient-to-r from-transparent via-[var(--color-border)] to-transparent"
                        ></div>
                    </div>

                    <!-- Content - Date Range Selector -->
                    <div class="flex flex-col gap-2">
                        <!-- Customer Selection -->
                        <div class="flex flex-col gap-2">
                            <label class="block text-md font-bold"
                                >CUSTOMER</label
                            >
                            <div class="flex gap-4">
                                <!-- All Customer Option -->
                                <label
                                    class="inline-flex items-center cursor-pointer group"
                                >
                                    <input
                                        type="radio"
                                        v-model="form.customer_type"
                                        value="All Customer"
                                        class="hidden peer"
                                    />
                                    <div
                                        class="relative flex items-center justify-center p-2"
                                    >
                                        <!-- Hover circle -->
                                        <div
                                            class="absolute -inset-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-[var(--color-border)]/40"
                                            :class="{
                                                'opacity-100':
                                                    form.customer_type ===
                                                    'All Customer',
                                            }"
                                        ></div>
                                        <!-- Radio button -->
                                        <div
                                            class="relative w-5 h-5 mr-2 rounded-full border-2 border-[var(--color-bg-avatar)] transition-colors z-10 group-hover:border-[var(--color-border)]"
                                            :class="{
                                                'border-[var(--color-border)]':
                                                    form.customer_type ===
                                                    'All Customer',
                                            }"
                                        >
                                            <div
                                                class="absolute inset-0 m-auto w-2.5 h-2.5 rounded-full bg-[var(--color-border)] transition-opacity"
                                                :class="{
                                                    'opacity-100':
                                                        form.customer_type ===
                                                        'All Customer',
                                                    'opacity-0':
                                                        form.customer_type !==
                                                        'All Customer',
                                                }"
                                            ></div>
                                        </div>
                                        <span class="text-sm font-medium z-10"
                                            >All Customer</span
                                        >
                                    </div>
                                </label>

                                <!-- By Customer Option -->
                                <label
                                    class="inline-flex items-center cursor-pointer group"
                                >
                                    <input
                                        type="radio"
                                        v-model="form.customer_type"
                                        value="By Customer"
                                        class="hidden peer"
                                    />
                                    <div
                                        class="relative flex items-center justify-center p-2"
                                    >
                                        <!-- Hover circle -->
                                        <div
                                            class="absolute -inset-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-[var(--color-border)]/40"
                                            :class="{
                                                'opacity-100':
                                                    form.customer_type ===
                                                    'By Customer',
                                            }"
                                        ></div>
                                        <!-- Radio button -->
                                        <div
                                            class="relative w-5 h-5 mr-2 rounded-full border-2 border-[var(--color-bg-avatar)] transition-colors z-10 group-hover:border-[var(--color-border)]"
                                            :class="{
                                                'border-[var(--color-border)]':
                                                    form.customer_type ===
                                                    'By Customer',
                                            }"
                                        >
                                            <div
                                                class="absolute inset-0 m-auto w-2.5 h-2.5 rounded-full bg-[var(--color-border)] transition-opacity"
                                                :class="{
                                                    'opacity-100':
                                                        form.customer_type ===
                                                        'By Customer',
                                                    'opacity-0':
                                                        form.customer_type !==
                                                        'By Customer',
                                                }"
                                            ></div>
                                        </div>
                                        <span class="text-sm font-medium z-10"
                                            >By Customer</span
                                        >
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-2">
                            <TextInput
                                label="Customer Code"
                                type="text"
                                v-model="form.customer_code"
                                @click="onCustomerClick()"
                                :message="form.errors.customer_code"
                                :readonly="
                                    form.customer_type === 'All Customer'
                                "
                                :default-placeholder="'Click to Select'"
                                :modified-placeholder="'By Customer Only'"
                                selectable="yes"
                                :validation="
                                    form.customer_type === 'All Customer'
                                        ? 'no'
                                        : 'yes'
                                "
                            />
                            <div class="space-y-1 col-span-2">
                                <TextInput
                                    label="Customer Name"
                                    type="text"
                                    v-model="form.customer_name"
                                    readonly
                                    :message="form.errors.customer_name"
                                    :validation="
                                        form.customer_type === 'All Customer'
                                            ? 'no'
                                            : 'yes'
                                    "
                                />
                            </div>
                        </div>

                        <!-- Date Type Selection -->
                        <!-- <div class="flex flex-col gap-2">
                            <label class="block text-md font-lg font-bold"
                                >DATE TYPE</label
                            >
                            <div class="flex gap-4">
                                <label
                                    class="inline-flex items-center cursor-pointer group"
                                >
                                    <input
                                        type="radio"
                                        v-model="form.date_type"
                                        value="Transaction Date"
                                        class="hidden peer"
                                    />
                                    <div
                                        class="relative flex items-center justify-center p-2"
                                    >
                                        <div
                                            class="absolute -inset-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-[var(--color-border)]/40"
                                            :class="{
                                                'opacity-100':
                                                    form.date_type ===
                                                    'Transaction Date',
                                            }"
                                        ></div>
                                        <div
                                            class="relative w-5 h-5 mr-2 rounded-full border-2 border-[var(--color-bg-avatar)] transition-colors z-10 group-hover:border-[var(--color-border)]"
                                            :class="{
                                                'border-[var(--color-border)]':
                                                    form.date_type ===
                                                    'Transaction Date',
                                            }"
                                        >
                                            <div
                                                class="absolute inset-0 m-auto w-2.5 h-2.5 rounded-full bg-[var(--color-border)] transition-opacity"
                                                :class="{
                                                    'opacity-100':
                                                        form.date_type ===
                                                        'Transaction Date',
                                                    'opacity-0':
                                                        form.date_type !==
                                                        'Transaction Date',
                                                }"
                                            ></div>
                                        </div>
                                        <span class="text-sm font-medium z-10"
                                            >Transaction Date</span
                                        >
                                    </div>
                                </label>

                                <label
                                    class="inline-flex items-center cursor-pointer group"
                                >
                                    <input
                                        type="radio"
                                        v-model="form.date_type"
                                        value="Receipt Date"
                                        class="hidden peer"
                                    />
                                    <div
                                        class="relative flex items-center justify-center p-2"
                                    >
                                        <div
                                            class="absolute -inset-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-[var(--color-border)]/40"
                                            :class="{
                                                'opacity-100':
                                                    form.date_type ===
                                                    'Receipt Date',
                                            }"
                                        ></div>

                                        <div
                                            class="relative w-5 h-5 mr-2 rounded-full border-2 border-[var(--color-bg-avatar)] transition-colors z-10 group-hover:border-[var(--color-border)]"
                                            :class="{
                                                'border-[var(--color-border)]':
                                                    form.date_type ===
                                                    'Receipt Date',
                                            }"
                                        >
                                            <div
                                                class="absolute inset-0 m-auto w-2.5 h-2.5 rounded-full bg-[var(--color-border)] transition-opacity"
                                                :class="{
                                                    'opacity-100':
                                                        form.date_type ===
                                                        'Receipt Date',
                                                    'opacity-0':
                                                        form.date_type !==
                                                        'Receipt Date',
                                                }"
                                            ></div>
                                        </div>
                                        <span class="text-sm font-medium z-10"
                                            >Receipt Date</span
                                        >
                                    </div>
                                </label>
                            </div>
                        </div> -->

                        <div class="flex flex-col gap-2">
                            <label class="block text-md font-lg font-bold"
                                >RECEIPT DATE</label
                            >
                        </div>

                        <!-- Date Range -->
                        <div class="grid grid-cols-2 gap-2">
                            <div class="space-y-1">
                                <label
                                    class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2"
                                    >Start Date</label
                                >
                                <DatePicker
                                    v-model="form.start_date"
                                    placeholder="Select Date"
                                    format="MM-DD-YYYY"
                                    :message="form.errors.start_date"
                                />
                            </div>
                            <div class="space-y-1">
                                <label
                                    class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2"
                                    >End Date</label
                                >
                                <DatePicker
                                    v-model="form.end_date"
                                    placeholder="Select Date"
                                    format="MM-DD-YYYY"
                                    :message="form.errors.end_date"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
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
        </div>
    </div>
</template>

<script setup>
import {
    computed,
    nextTick,
    onMounted,
    onUnmounted,
    readonly,
    ref,
    watch,
} from "vue";
import { route } from "../../../../vendor/tightenco/ziggy/src/js";
import TextInput from "../../Pages/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import ConfirmationDialog from "../../Pages/Components/ConfirmationDialog.vue";
import ToastAlertWarning from "../../Pages/Components/ToastAlertWarning.vue";
import CustomerListModal from "../TransactionModals/CustomerListModal.vue";
import { mdiClose, mdiNavigationVariantOutline } from "@mdi/js";
import DatePicker from "../../Pages/Components/DatePicker.vue";
import PdfPreviewModal from "../PdfPreviewModal.vue";

const props = defineProps({
    show: Boolean,
});

const form = useForm({
    customer_type: "All Customer",
    customer_code: null,
    customer_name: null,
    date_type: "Receipt Date",
    start_date: null,
    end_date: null,
    processtype: null,
});

const emit = defineEmits(["close", "closeSuccess"]);

const closeModal = () => {
    emit("close");
};

//////// CUSTOMER CODE DROPDOWN ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
const showCustomerModal = ref(false);
function onCustomerClick() {
    showCustomerModal.value = true;
}
const handleSelectedCustomer = (selectedData) => {
    form.customer_code = selectedData.cus_code;
    form.customer_name = selectedData.cus_name;
    form.price_group = selectedData.price_group;

    showCustomerModal.value = false;
};

///////////// WATCH //////////////////////////////////////////////////////////////////////////////////////////////////////////
watch(
    () => form.customer_type,
    async (newVal, oldVal) => {
        if (newVal !== oldVal) {
            form.customer_code = "";
            form.customer_name = "";
        }
    }
);

//////////////////////////////////////// SHOW TOAST /////////////////////////////////////////////////////////////////////////////////////////
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

//////////// PRINT PDF //////////////////////////////////////////////////////////////////////////////////////////////////////////
const showPdfModal = ref(false);
const pdfFormData = ref(null);
const apiRoute = ref(null);
const previewInvoice = async () => {
    try {
        form.processtype = "axios";

        apiRoute.value = "invoiceReport";
        pdfFormData.value = form;
        showPdfModal.value = true;
    } catch (error) {
        console.error("Error previewing invoice:", error);
    }
};

const pdfPrintSuccess = () => {
    showPdfModal.value = false;
    emit("close");
};

const showDialog = ref(false);
const handleConfirm = async (confirmed) => {
    showDialog.value = false;
    if (confirmed) {
        previewInvoice();
    }
};
/////////// SUBMIT ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
const submit = () => {
    Object.keys(form.errors).forEach((key) => {
        form.errors[key] = "";
    });
    form.post(route("invoiceReport"), {
        onSuccess: () => {
            showDialog.value = true;
        },
        onError: (error) => {
            // handleFormErrors(errors);
            // console.log(error);
            if (Object.keys(error).length === 1) {
                const firstError = Object.values(error)[0];
                showWarningToast(firstError);
            } else if (Object.keys(error).length !== 1) {
                showWarningToast("Please Fill Up Necessary Fields");
            }
        },
    });
};
</script>
