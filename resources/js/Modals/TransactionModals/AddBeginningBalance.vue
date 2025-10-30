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
                :type="'Add'"
                @close="showAllTransactionListModal = false"
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
        <form
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
                        ADD NEW BEGINNING BALANCE
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
                            :message="form.errors.beginningbalance_no"
                        />
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

                        <TextInput
                            label="Customer Code"
                            type="text"
                            v-model="form.customer_code"
                            @click="onCustomerClick()"
                            :message="form.errors.customer_code"
                            :readonly="
                                !(form.receipt_date && form.transaction_date)
                            "
                            :default-placeholder="'Click to Select'"
                            :modifiedPlaceholder="'As Of Dates Required'"
                            selectable="yes"
                        />

                        <TextInput
                            label="Customer Name"
                            v-model="form.name"
                            type="text"
                            readonly
                            :message="form.errors.name"
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
                            :message="form.errors.particular"
                            :readonly="!form.name"
                            class="col-span-2"
                        />
                        <TextInput
                            label="Balance Amount"
                            v-model="form.balance_amount"
                            type="decimal"
                            :message="form.errors.balance_amount"
                            :readonly="!form.name"
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
                            <span v-if="form.processing">Submitting...</span>
                            <span v-else>Submit</span>
                        </div>
                    </button>
                </div>
            </div>
        </form>
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
import { route } from "../../../../vendor/tightenco/ziggy/src/js";
import TextInput from "../../Pages/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import ToastAlertWarning from "../../Pages/Components/ToastAlertWarning.vue";
import axios from "axios";
import ConfirmationDialog from "../../Pages/Components/ConfirmationDialog.vue";
import AllTransactionListModal from "./AllTransactionListModal.vue";
import CustomerListModal from "./CustomerListModal.vue";
import { mdiClose, mdiNavigationVariantOutline } from "@mdi/js";
import DatePicker from "../../Pages/Components/DatePicker.vue";

const props = defineProps({
    show: Boolean,
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
const showCustomerModal = ref(false);
const showToast = ref(false);
const toastMessage = ref("");

let toastTimeout = null;

form.transaction_date = new Date().toISOString().split("T")[0];

async function nextBeginningBalanceNo() {
    try {
        const response = await axios.get(route("getlatestbeginningbalanceno"));
        return String(response.data.next_beginningbalance_no);
    } catch (err) {
        console.error(err);
        return "25000001";
    }
}

function onAllTransactionsClick() {
    showAllTransactionListModal.value = true;
}

const emit = defineEmits(["close", "closeSuccess"]);

const closeModal = () => {
    emit("close");
};

////////////////TOAST///////////////////////////////////////////////////////////////////////////////////////////////////////
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

/////// CUSTOMER CODE DROPDOWN /////////////////////////////////////////////////////////////////////////////////////////////
function onCustomerClick() {
    showCustomerModal.value = true;
}
const handleSelectedCustomer = (selectedData) => {
    form.customer_code = selectedData.cus_code;
    form.name = selectedData.cus_name;

    showCustomerModal.value = false;
};

////////SHOW DIALOG FOR PRINT///////////////////////////
const showDialog = ref(false);
const handleConfirm = async (confirmed) => {
    showDialog.value = false;
    if (confirmed) {
        Object.keys(form.errors).forEach((key) => {
            form.errors[key] = "";
        });
        form.post(route("addBeginningBalance"), {
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
                //console.log(errors); // helpful for debugging
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
            form.beginningbalance_no = await nextBeginningBalanceNo();
            modalLoading.value = false;
        }
    },
    { immediate: true }
);

watch(
    () => form.customer_code,
    async (newVal, oldVal) => {
        if (newVal === "" || newVal !== oldVal) {
            form.balance_amount = "";
            form.particular = "";
        }
    }
);
//#endregion

//////////////////////////SUBMIT////////////////////
const submit = () => {
    showDialog.value = true;
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
