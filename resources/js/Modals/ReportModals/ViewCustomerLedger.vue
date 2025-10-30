<template>
    <div
        v-if="show"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center px-4"
    >
        <div
            class="bg-[var(--color-bg-secondary)] text-[var(--color-text-primary)] w-full max-w-5xl rounded-2xl border border-[var(--color-border)]"
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
                        VIEW CUSTOMER LEDGER
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
                            label="Document Number"
                            v-model="form.invoice_number"
                            type="text"
                            readonly
                        />
                        <TextInput
                            label="Date"
                            v-model="form.date"
                            type="date"
                            readonly
                        />
                        <TextInput
                            label="Type"
                            v-model="form.type"
                            type="text"
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
                            label="Currency"
                            v-model="form.currency"
                            type="text"
                            readonly
                        />
                        <TextInput
                            label="Amount"
                            v-model="form.amount"
                            type="text"
                            readonly
                        />
                        <TextInput
                            label="Adjusted Amount"
                            v-model="form.adjusted_amount"
                            type="text"
                            readonly
                        />
                        <TextInput
                            label="Overage"
                            v-model="form.overage"
                            type="text"
                            readonly
                        />
                        <TextInput
                            label="Shrinkage"
                            v-model="form.shrinkage"
                            type="text"
                            readonly
                        />
                        <TextInput
                            label="Return"
                            v-model="form.return"
                            type="text"
                            readonly
                        />
                        <TextInput
                            label="Amount Paid"
                            v-model="form.amount_paid"
                            type="text"
                            readonly
                        />
                        <TextInput
                            label="Balance"
                            v-model="form.running_balance"
                            type="text"
                            readonly
                        />
                    </div>
                </div>

                <!-- Action Buttons -->
                <div
                    class="flex justify-end gap-2 pt-2 mt-4 border-t border-[var(--color-border)]"
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
import { mdiClose } from "@mdi/js";

const props = defineProps({
    show: Boolean,
    selected: Object,
    selectedcustname: String,
});

const form = useForm({
    invoice_number: null,
    date: null,
    type: null,
    customer_code: null,
    name: null,
    currency: null,
    amount: null,
    adjusted_amount: null,
    overage: null,
    shrinkage: null,
    return: null,
    amount_paid: null,
    running_balance: null,
});

const adjustmentreasonOptions = ref([]);

const emit = defineEmits(["close"]);

const closeModal = () => {
    emit("close");
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(amount);
};

///////////////////////////// WATCH //////////////////////////////////////////
const modalLoading = ref(false);
watch(
    () => props.show,
    async (visible) => {
        if (visible) {
            modalLoading.value = true;

            form.invoice_number = props.selected.invoice_number;
            form.date = props.selected.date;
            form.type = props.selected.type;
            form.customer_code = props.selected.customer_code;
            form.name = props.selectedcustname;
            form.currency = props.selected.currency;
            form.amount = formatCurrency(props.selected.amount);
            form.adjusted_amount = formatCurrency(
                props.selected.adjusted_amount
            );

            form.overage = formatCurrency(props.selected.overage);
            form.shrinkage = formatCurrency(props.selected.shrinkage);
            form.return = props.selected.return
                ? formatCurrency(props.selected.return)
                : formatCurrency("0.00");
            form.amount_paid = formatCurrency(props.selected.amount_paid);
            form.running_balance = formatCurrency(
                props.selected.document_balance
            );

            try {
                const response = await axios.get(
                    route("getAdjustmentReasonSetup"),
                    {
                        params: {
                            type: form.type,
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
