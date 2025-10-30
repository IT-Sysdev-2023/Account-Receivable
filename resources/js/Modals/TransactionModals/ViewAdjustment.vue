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
                message="DO YOU WANT TO PRINT THE DOCUMENT?"
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
            <ManagersKey
                :show="showManagerModal"
                @success="onManagerSuccess"
                @cancel="onManagerCancel"
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
                @close="showPdfModal = false"
            />
        </Transition>

        <div
            class="bg-[var(--color-bg-secondary)] text-[var(--color-text-primary)] w-full max-w-7xl rounded-2xl border border-[var(--color-border)]"
        >
            <!-- Show spinner while loading -->
            <div
                v-if="modalLoading"
                class="flex justify-center items-center py-20"
            >
                <svg
                    width="40"
                    height="40"
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
                        VIEW ADJUSTMENT
                    </h2>
                    <div
                        class="mt-2 h-0.5 bg-gradient-to-r from-transparent via-[var(--color-border)] to-transparent"
                    ></div>
                </div>

                <div class="flex flex-col md:flex-col gap-4 px-4">
                    <!-- Right: Form Fields -->
                    <div
                        class="w-full grid sm:grid-cols-1 md:grid-cols-3 gap-4"
                    >
                        <TextInput
                            label="Adjustment Number"
                            v-model="form.adjustment_no"
                            type="text"
                            readonly
                        />
                        <TextInput
                            label="Receipt Date"
                            v-model="form.receipt_date"
                            type="date"
                            readonly
                        />
                        <TextInput
                            label="Transaction Date"
                            v-model="form.transaction_date"
                            type="date"
                            readonly
                        />
                        <TextInput
                            label="Customer Code"
                            type="text"
                            v-model="form.customer_code"
                            readonly
                        />
                        <TextInput
                            label="Customer Name"
                            v-model="form.name"
                            type="text"
                            readonly
                            class="col-span-2"
                        />
                    </div>
                    <div
                        class="w-full grid sm:grid-cols-1 md:grid-cols-4 gap-4"
                    >
                        <TextInput
                            label="Type"
                            v-model="form.type"
                            type="text"
                            readonly
                        />
                        <TextInput
                            label="Apply To"
                            v-model="form.apply_to"
                            type="text"
                            readonly
                        />
                        <TextInput
                            label="Document Number"
                            type="text"
                            v-model="form.invoice_no"
                            readonly
                        />
                        <TextInput
                            label="Balance"
                            v-model="form.balance"
                            type="decimal"
                            readonly
                        />
                        <TextInput
                            label="Adjustment Reason"
                            v-model="form.adjustment_reason"
                            type="text"
                            readonly
                        />
                        <TextInput
                            label="Particular"
                            v-model="form.particulars"
                            type="text"
                            readonly
                            class="col-span-2"
                        />
                        <TextInput
                            label="Amount"
                            v-model="form.amount"
                            type="decimal"
                            readonly
                        />
                    </div>
                </div>

                <!-- Action Buttons -->
                <div
                    class="flex justify-between gap-4 pt-2 border-t border-[var(--color-border)] mt-4"
                >
                    <div class="flex items-center">
                        <p class="text-xs">
                            Transacted By : {{ props.selected.created_by }}
                        </p>
                    </div>
                    <div class="flex gap-2">
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
                            v-if="canReprint('0202-ADT')"
                            type="submit"
                            @click="openManagerModal()"
                            class="submitButton group"
                        >
                            <div class="flex justify-center items-center gap-2">
                                <span
                                    class="transition-transform duration-300 group-hover:rotate-360"
                                >
                                    <svg-icon
                                        type="mdi"
                                        :path="mdiPrinterOutline"
                                        class="w-5 h-5"
                                    />
                                </span>
                                <span>Reprint</span>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import {
    computed,
    ref,
    watch,
    onMounted,
    onUnmounted,
    onBeforeUnmount,
} from "vue";
import { route } from "../../../../vendor/tightenco/ziggy/src/js";
import TextInput from "../../Pages/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import axios from "axios";
import ConfirmationDialog from "../../Pages/Components/ConfirmationDialog.vue";
import ManagersKey from "../ManagersKey.vue";
import { mdiClose, mdiPrinterOutline } from "@mdi/js";
import PdfPreviewModal from "../PdfPreviewModal.vue";
import usePermissions from "../../Pages/Composables/usePermissions";

const props = defineProps({
    show: Boolean,
    selected: Object,
});

const form = useForm({
    adjustment_no: null,
    receipt_date: null,
    transaction_date: null,
    customer_code: null,
    name: null,
    type: null,
    apply_to: null,
    invoice_no: null,
    balance: null,
    adjustment_reason: null,
    particulars: null,
    amount: null,
});

const { canReprint } = usePermissions();

const adjustmentreasonOptions = ref([]);

const emit = defineEmits(["close"]);

const closeModal = () => {
    emit("close");
};

const showManagerModal = ref(false);
const person_authored = ref(null);
function openManagerModal() {
    showDialog.value = true;
}

function onManagerSuccess(selectedData) {
    showManagerModal.value = false;
    person_authored.value = selectedData.person_authored;
    showDialog.value = true;
}

function onManagerCancel() {
    showManagerModal.value = false;
}

///////////////////////////// WATCH //////////////////////////////////////////
const modalLoading = ref(false);
watch(
    () => props.show,
    async (visible) => {
        if (visible) {
            modalLoading.value = true;
            form.adjustment_no = props.selected.adjustment_no;
            form.receipt_date = props.selected.receipt_date;
            form.transaction_date = props.selected.transaction_date;
            form.customer_code = props.selected.customer_code;
            form.name = props.selected.name;
            form.type = props.selected.type;
            form.apply_to = props.selected.apply_to;
            form.invoice_no = props.selected.invoice_no;
            form.balance = props.selected.balance;
            form.adjustment_reason = props.selected.adjustment_reason;
            form.particulars = props.selected.particulars;
            form.amount = props.selected.amount;
            try {
                const response = await axios.get(
                    route("getAdjustmentReasonSetup"),
                    {
                        params: {
                            apply_to: props.selected.apply_to,
                        },
                    }
                );
                adjustmentreasonOptions.value = response.data;
                modalLoading.value = false;
            } catch (error) {
                console.error("Failed to fetch adjustmen reason setup:", error);
            }
        }
    },
    { immediate: true }
);

////////PREVIEW PDF//////////////////////////////////////////////////////////////////////////////////////////////
// Preview Invoice PDF
const showPdfModal = ref(false);
const pdfFormData = ref(null);
const apiRoute = ref(null);
const previewInvoice = async () => {
    try {
        const submissionData = {
            ...form.data(),
            _reprint_confirmation: true,
            _person_authored: person_authored.value,
        };

        apiRoute.value = "preview-adjustment";
        pdfFormData.value = submissionData;
        showPdfModal.value = true;
    } catch (error) {
        console.error("Error previewing invoice:", error);
    }
};

const pdfPrintSuccess = () => {
    showPdfModal.value = false;
    emit("closeSuccess");
};

////////SHOW DIALOG FOR PRINT///////////////////////////
const showDialog = ref(false);
const handleConfirm = async (confirmed) => {
    showDialog.value = false;
    if (confirmed) {
        previewInvoice();
    }
};
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
</style>
