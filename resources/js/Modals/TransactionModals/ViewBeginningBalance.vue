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
                :customer_code="form.customer_code"
                :receipt_date="form.receipt_date"
                :type="'View'"
                @close="showAllTransactionListModal = false"
            />
        </Transition>
        <div
            @submit.prevent="submit"
            class="bg-[var(--color-bg-secondary)] text-[var(--color-text-primary)] w-full max-w-4xl rounded-2xl border border-[var(--color-border)]"
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
                        VIEW BEGINNING BALANCE
                    </h2>
                    <div
                        class="mt-2 h-0.5 bg-gradient-to-r from-transparent via-[var(--color-border)] to-transparent"
                    ></div>
                </div>

                <div class="flex flex-col md:flex-col gap-4 px-4">
                    <div
                        class="w-full grid sm:grid-cols-1 md:grid-cols-3 gap-4"
                    >
                        <TextInput
                            label="Beginning Balance Number"
                            v-model="form.beginningbalance_no"
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
                            label="Particular"
                            v-model="form.particular"
                            type="text"
                            readonly
                            class="col-span-2"
                        />
                        <TextInput
                            label="Balance Amount"
                            v-model="form.balance_amount"
                            type="text"
                            readonly
                            class="col-span-2"
                        />
                        <h3
                            class="col-span-2 cursor-pointer"
                            @click="onAllTransactionsClick()"
                        >
                            <span
                                class="text-sm hover:underline hover:text-[var(--color-bg-avatar)]"
                                >Click Here to View All Transactions</span
                            >
                        </h3>
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
    nextTick,
} from "vue";
import TextInput from "../../Pages/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import AllTransactionListModal from "./AllTransactionListModal.vue";
import { mdiClose } from "@mdi/js";

const props = defineProps({
    show: Boolean,
    selected: Object,
});

const form = useForm({
    beginningbalance_no: null,
    receipt_date: null,
    transaction_date: null,
    customer_code: null,
    name: null,
    particular: null,
    balance_amount: null,
});

const modalLoading = ref(false);
const showAllTransactionListModal = ref(false);

function onAllTransactionsClick() {
    showAllTransactionListModal.value = true;
}

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

/////// WATCH //////////////////////////////////////////////////////////////////////////////////////////////////////////////
watch(
    () => props.show,
    async (visible, oldVisible) => {
        if (visible && !oldVisible) {
            modalLoading.value = true;
            form.beginningbalance_no = props.selected.beginningbalance_no;
            form.receipt_date = props.selected.receipt_date;
            form.transaction_date = props.selected.transaction_date;
            form.customer_code = props.selected.customer_code;
            form.name = props.selected.name;
            form.particular = props.selected.particular;
            form.balance_amount = formatCurrency(props.selected.balance_amount);
            modalLoading.value = false;
        }
    },
    { immediate: true }
);
</script>
