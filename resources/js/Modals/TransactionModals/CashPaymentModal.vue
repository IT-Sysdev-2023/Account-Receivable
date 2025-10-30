<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-sm bg-black/60"
    >
        <ToastAlertWarning :show="showToast" :message="toastMessage" />

        <!-- <Transition
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
        </Transition> -->

        <!-- Modal Container -->
        <div
            class="bg-[var(--color-bg-secondary)] text-[var(--color-text-primary)] w-full max-w-3xl rounded-2xl border border-[var(--color-border)]"
        >
            <form @submit.prevent="submit">
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
                <div v-else class="p-6">
                    <!-- Header -->
                    <div class="px-8 pb-4">
                        <h2 class="text-2xl font-bold text-center">
                            CASH PAYMENT
                        </h2>
                        <div
                            class="mt-2 h-0.5 bg-gradient-to-r from-transparent via-[var(--color-border)] to-transparent"
                        ></div>
                    </div>

                    <div class="flex flex-col md:flex-row gap-6 px-4">
                        <!-- Right: Form Fields -->
                        <div class="w-full grid grid-cols-2 grid-rows-auto">
                            <div class="col-span-2 row-span-2">
                                <div
                                    class="w-full grid sm:grid-cols-1 md:grid-cols-2 gap-x-[16px]"
                                >
                                    <TextInput
                                        label="Payment Number"
                                        v-model="form.payment_no"
                                        type="text"
                                        :readonly="true"
                                        :message="form.errors.payment_no"
                                    />
                                    <TextInput
                                        label="Date"
                                        v-model="form.receipt_date"
                                        type="date"
                                        readonly
                                        :message="form.errors.receipt_date"
                                    />
                                    <TextInput
                                        label="Payment Type"
                                        v-model="form.payment_type"
                                        type="text"
                                        :readonly="true"
                                        :message="form.errors.payment_type"
                                    />
                                    <PrefixTextInput
                                        label="DS Number"
                                        v-model="dsNumberWithPrefix"
                                        type="text"
                                        :message="form.errors.ds_no"
                                        prefixType="DS"
                                        @keydown="handleKeyDownDS"
                                        @click="handleClickDS"
                                    />
                                    <TextInput
                                        label="CI Amount"
                                        v-model="form.total_amount"
                                        type="text"
                                        :message="form.errors.total_amount"
                                        readonly
                                    />
                                    <TextInput
                                        label="Cash in Bank"
                                        v-model="form.cash_in_bank"
                                        type="text"
                                        :readonly="true"
                                        :message="form.errors.cash_in_bank"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="mt-4 pt-2 border-t gap-2 border-[var(--color-border)] flex justify-end"
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
import { useForm } from "@inertiajs/vue3";
import ConfirmationDialog from "../../Pages/Components/ConfirmationDialog.vue";
import TextInput from "../../Pages/Components/TextInput.vue";
import ToastAlertWarning from "../../Pages/Components/ToastAlertWarning.vue";
import { computed, ref, watch } from "vue";
import { mdiClose, mdiNavigationVariantOutline } from "@mdi/js";
import PrefixTextInput from "../../Pages/Components/PrefixTextInput.vue";

const props = defineProps({
    show: Boolean,
    formData: Object,
});

const form = useForm({
    payment_no: null,
    receipt_date: null,
    transaction_date: null,
    customer_code: null,
    name: null,
    payment_type: null,
    type: null,
    ds_no: null,
    document_no: null,
    document_date: null,
    total_amount: null,
    amount_paid: null,
    cash_in_bank: null,
    due_date: null,
});

const modalLoading = ref(false);

const emit = defineEmits(["close", "closeSuccess"]);

const closeModal = () => {
    emit("close");
};

////////// SHOW TOAST ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
const showToast = ref(false);
const toastMessage = ref("");
let toastTimeout = null;

const showWarningToast = (message) => {
    toastMessage.value = message;
    showToast.value = false;
    if (toastTimeout) clearTimeout(toastTimeout);

    setTimeout(() => {
        showToast.value = true;
    }, 0);

    toastTimeout = setTimeout(() => {
        showToast.value = false;
        toastTimeout = null;
    }, 3000);
};

//#region ////////////////DS NO PREFIX ///////////////////////////////////////////////////////////////////////////////////////////////////////
const dsNumberWithPrefix = computed({
    get() {
        return form.ds_no?.startsWith("DS#")
            ? form.ds_no
            : `DS#${form.ds_no || ""}`;
    },
    set(value) {
        form.ds_no = value.startsWith("DS#") ? value : `DS#${value}`;
    },
});

const handleKeyDownDS = (e) => {
    if (e.ctrlKey || e.altKey || e.metaKey) {
        return;
    }

    const input = e.target;
    const currentPrefix = "DS#";
    const prefixLength = currentPrefix.length;
    const isAllSelected =
        input.selectionStart === 0 && input.selectionEnd === input.value.length;

    if (
        (e.key === "Backspace" || e.key === "Delete") &&
        input.selectionStart <= prefixLength &&
        input.selectionEnd <= prefixLength
    ) {
        e.preventDefault();
        return;
    }

    const allowedKeys = [
        "Backspace",
        "Delete",
        "ArrowLeft",
        "ArrowRight",
        "Tab",
        "Home",
        "End",
    ];

    if (allowedKeys.includes(e.key)) {
        return;
    }

    if (isAllSelected) {
        e.preventDefault();
        const newValue =
            currentPrefix +
            (e.key.match(/[0-9]/) ? e.key : form.ds_no.slice(3));
        input.value = newValue;
        form.ds_no = newValue;
        input.setSelectionRange(prefixLength + 1, prefixLength + 1);
        return;
    }

    if (input.selectionStart < prefixLength) {
        e.preventDefault();
        const currentValue = input.value;
        const newValue =
            currentValue.slice(0, prefixLength) +
            (e.key.match(/[0-9]/) ? e.key : "") +
            currentValue.slice(prefixLength);
        input.value = newValue;
        form.ds_no = newValue;
        const newCursorPos = prefixLength + 1;
        input.setSelectionRange(newCursorPos, newCursorPos);
        return;
    }

    if (!/[0-9]/.test(e.key)) {
        e.preventDefault();
    }
};

const handleClickDS = (e) => {
    const input = e.target;
    const prefix = "DS#";
    const prefixLength = prefix.length;

    if (input.selectionStart < prefixLength) {
        setTimeout(() => {
            if (input.selectionStart === input.selectionEnd) {
                input.setSelectionRange(prefixLength, prefixLength);
            }
        }, 0);
    }
};
//#endregion

/////////////////////// WATCH ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
watch(
    () => props.show,
    async (visible, oldVisible) => {
        if (visible && !oldVisible) {
            modalLoading.value = true;
            form.payment_no = "********";
            form.receipt_date = props.formData.receipt_date;
            form.transaction_date = props.formData.transaction_date;
            form.customer_code = props.formData.customer_code;
            form.name = props.formData.name;
            form.payment_type = "5A - Cash";
            form.type = "Charge Invoice";
            form.document_no = props.formData.invoice_no;
            form.document_date = props.formData.receipt_date;
            form.total_amount = props.formData.total_amount;
            form.amount_paid = parseFloat(
                props.formData.total_amount
                    .replace(/[^\d.-]/g, "")
                    .replace(/,/g, "")
            );
            form.cash_in_bank = "Cash on Hand";
            modalLoading.value = false;
        }
    },
    { immediate: true }
);

//////////////////////////SUBMIT//////////////////////////////////////////////////////////////////////////////////////////////////////
const submit = () => {
    const submissionData = {
        ...form.data(),
        _od_confirmation: false,
        _check_confirmation: false,
        _cash_confirmation: true,
    };

    form.transform((data) => submissionData).post(route("addPayment"), {
        onSuccess: () => {
            axios.get(route("payment.latest.paymentNumber")).then((res) => {
                form.payment_no = res.data.payment_number;
                emit("closeSuccess", {
                    payment_no: form.payment_no,
                });
                form.reset();
            });
        },
        onError: (errors) => {
            if (Object.keys(errors).length === 1) {
                const firstError = Object.values(errors)[0]; 
                showWarningToast(firstError);
            } else if (Object.keys(errors).length !== 1) {
                if (
                    errors.amount_paid ===
                    "Amount Should Not Be Greater Than Available Balance"
                ) {
                    showWarningToast(
                        "Amount Should Not Be Greater Than Available Balance"
                    );
                } else {
                    showWarningToast("Please Fill Up Necessary Fields");
                }
            }
            console.log(errors);
        },
    });
};
</script>
