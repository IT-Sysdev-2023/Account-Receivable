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
                message="Do you want to print the Document?"
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
                        VIEW TEMPORARY CHARGE INVOICE
                    </h2>
                    <div
                        class="mt-2 h-0.5 bg-gradient-to-r from-transparent via-[var(--color-border)] to-transparent"
                    ></div>
                </div>

                <div class="flex flex-col md:flex-row gap-6 px-4">
                    <!-- Right: Form Fields -->
                    <div class="w-full grid grid-cols-1 md:grid-cols-4 gap-4">
                        <TextInput
                            label="Invoice Number"
                            v-model="form.invoice_no"
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
                        <TextInput
                            label="Price Group"
                            v-model="form.price_group"
                            type="text"
                            readonly
                        />
                        <TextInput
                            label="Payment Mode"
                            v-model="form.payment_mode"
                            type="text"
                            readonly
                        />
                        <TextInput
                            label="Charge Invoice Type"
                            v-model="form.chargeinvoice_type"
                            type="text"
                            readonly
                        />
                        <TextInput
                            label="Particular"
                            v-model="form.particular"
                            type="text"
                            readonly
                        />
                        <TextInput
                            label="Reference Number"
                            v-model="form.reference_no"
                            type="text"
                            readonly
                        />
                        <TextInput
                            label="Total Amount"
                            v-model="form.total_amount"
                            type="text"
                            readonly
                        />
                    </div>
                </div>

                <div
                    class="rounded-xl bg-[var(--color-primary)]/20 backdrop-blur-sm mt-2"
                >
                    <div class="sticky top-0 z-10 px-2">
                        <!-- ref="tableWrapper" -->
                        <table
                            class="w-full text-[var(--color-text-primary)] text-sm"
                        >
                            <thead>
                                <tr
                                    class="text-sm uppercase tracking-wider text-[var(--color-text-primary)]"
                                >
                                    <th class="px-2 py-3 text-left w-[15%]">
                                        Item Code
                                    </th>
                                    <th class="px-2 py-3 text-left w-[26%]">
                                        Item Name
                                    </th>
                                    <th class="px-2 py-3 text-left w-[16%]">
                                        Packing
                                    </th>
                                    <th class="px-2 py-3 text-center w-[13%]">
                                        Quantity
                                    </th>
                                    <th class="px-2 py-3 text-center w-[14%]">
                                        Price
                                    </th>
                                    <th class="px-2 py-3 text-center w-[16%]">
                                        Amount
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="relative">
                        <div
                            class="overflow-y-auto max-h-[200px] scrollbar-thin scrollbar-stable [scrollbar-gutter:stable] pl-2 pb-2"
                        >
                            <table
                                class="w-full text-[var(--color-text-primary)] text-sm"
                            >
                                <tbody class="rounded-xl">
                                    <tr v-if="tableLoading">
                                        <td
                                            colspan="7"
                                            class="text-center py-4"
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
                                    <tr
                                        v-else
                                        v-for="(row, index) in rows"
                                        :key="index"
                                        class="transition-all duration-300 ease-in-out rounded-md text-sm"
                                    >
                                        <!-- Item Code -->
                                        <td class="px-2 py-1 w-[15%]">
                                            {{ row.item_code }}
                                        </td>
                                        <!-- Item Name -->
                                        <td class="px-2 py-1 w-[26%]">
                                            {{ row.item_name }}
                                        </td>
                                        <!-- Packing -->
                                        <td class="px-2 py-1 w-[16%]">
                                            {{ row.packing }}
                                        </td>

                                        <!-- Quantity -->
                                        <td
                                            class="px-2 py-1 text-center w-[13%]"
                                        >
                                            {{ row.quantity }}
                                        </td>

                                        <!-- Price -->
                                        <td
                                            class="px-2 py-1 text-center w-[14%]"
                                        >
                                            {{ row.price }}
                                        </td>

                                        <!-- Amount -->
                                        <td
                                            class="px-2 py-1 text-center w-[16%]"
                                        >
                                            {{ formatCurrency(row.amount) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
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
                            v-if="canReprint('0201-CIT')"
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
import { ref, watch } from "vue";
import TextInput from "../../Pages/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import axios from "axios";
import ConfirmationDialog from "../../Pages/Components/ConfirmationDialog.vue";
import ManagersKey from "../ManagersKey.vue";
import {
    mdiClose,
    mdiNavigationVariantOutline,
    mdiPrinterOutline,
} from "@mdi/js";
import PdfPreviewModal from "../PdfPreviewModal.vue";
import usePermissions from "../../Pages/Composables/usePermissions";

const props = defineProps({
    show: Boolean,
    selected: Object,
});

const form = useForm({
    invoice_no: null,
    receipt_date: null,
    transaction_date: null,
    customer_code: null,
    name: null,
    price_group: null,
    payment_mode: null,
    chargeinvoice_type: null,
    particular: null,
    reference_no: null,
    total_amount: null,
    payment_no: null,
    invoices: [],
});

const { canReprint } = usePermissions();

const typeOptions = ref([]);

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(amount);
};

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

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

const rows = ref([
    {
        item_code: "",
        item_name: "",
        packing: "",
        quantity: "",
        price: "",
        amount: "",
    },
]);

/////////////////// WATCH \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
const modalLoading = ref(false);
const tableLoading = ref(false);
watch(
    () => props.show,
    async (visible) => {
        if (visible) {
            modalLoading.value = true;
            form.invoice_no = props.selected.invoice_no;
            form.receipt_date = props.selected.receipt_date;
            form.transaction_date = props.selected.transaction_date;
            form.customer_code = props.selected.customer_code;
            form.name = props.selected.name;
            form.price_group = props.selected.price_group;
            form.payment_mode = props.selected.payment_mode;
            form.chargeinvoice_type = props.selected.chargeinvoice_type;
            form.particular = props.selected.particular;
            form.reference_no = props.selected.reference_no;
            form.payment_no = props.selected.payment_no;
            form.total_amount = formatCurrency(props.selected.total_amount);
            rows.value = [];
            try {
                const response = await axios.get(
                    route("getInvoiceItems", props.selected.invoice_no)
                );
                const items = response.data;

                rows.value = items.map((item) => ({
                    item_code: item.item_code,
                    item_name: item.item_name,
                    packing: item.packing,
                    quantity: item.quantity,
                    price: item.price,
                    amount: item.amount,
                }));

                try {
                    const response = await axios.get(route("ci-types"));
                    typeOptions.value = response.data;
                    modalLoading.value = false;
                } catch (error) {
                    console.error("Failed to fetch ci_types:", error);
                }
            } catch (error) {
                console.error("Failed to fetch invoice items:", error);
            }
        }
    },
    { immediate: true }
);
////////PREVIEW PDF
// Function to prepare invoices before preview
const prepareInvoices = () => {
    form.invoices = rows.value.filter(
        (row) =>
            row.item_code.trim() !== "" &&
            row.item_name.trim() !== "" &&
            row.packing.trim() !== "" &&
            row.quantity &&
            row.price &&
            row.amount
    );
};

//////// Preview Invoice PDF
const showPdfModal = ref(false);
const pdfFormData = ref(null);
const apiRoute = ref(null);
const previewInvoice = async () => {
    try {
        prepareInvoices();

        const submissionData = {
            ...form.data(),
            _reprint_confirmation: true,
            _person_authored: person_authored.value,
        };

        if (form.payment_mode === "Cash") {
            apiRoute.value = "preview-cash-invoice";
        } else {
            apiRoute.value = "preview-invoice";
        }

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
