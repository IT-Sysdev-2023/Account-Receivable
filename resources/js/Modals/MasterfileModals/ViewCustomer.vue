<template>
    <Transition name="modal">
        <div
            v-if="show"
            class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
        >
            <!-- Modal Container -->
            <div
                class="w-full max-w-5xl overflow-hidden text-[var(--color-text-primary)] rounded-2xl bg-[var(--color-bg-secondary)] border border-[var(--color-border)]"
            >
                <!-- Loading State -->
                <div
                    v-if="modalLoading"
                    class="flex items-center justify-center py-20"
                >
                    <svg
                        width="50"
                        height="50"
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

                <!-- Content -->
                <div v-else class="p-6">
                    <!-- Header -->
                    <div class="px-8 pb-4">
                        <h2 class="text-2xl font-bold text-center">
                            CUSTOMER DETAILS
                        </h2>
                        <div
                            class="mt-2 h-0.5 bg-gradient-to-r from-transparent via-[var(--color-border)] to-transparent"
                        ></div>
                    </div>

                    <!-- Form Grid -->
                    <div
                        class="grid grid-cols-1 md:grid-cols-3 grid-rows-auto gap-2 md:gap-2"
                    >
                        <TextInput
                            label="Code"
                            v-model="form.cus_code"
                            icon="tag"
                            readonly
                        />
                        <TextInput
                            label="Name"
                            v-model="form.cus_name"
                            icon="user"
                            class="col-span-2"
                            readonly
                        />
                        <TextInput
                            label="Customer Type"
                            v-model="form.cus_type"
                            icon="price-tag"
                            readonly
                        />
                        <TextInput
                            label="Price Group"
                            v-model="form.cus_price_group"
                            icon="price-tag"
                            readonly
                        />
                        <TextInput
                            label="Address"
                            v-model="form.cus_address"
                            icon="location"
                            readonly
                        />
                        <TextInput
                            label="TIN Number"
                            v-model="form.cus_tin"
                            icon="id-card"
                            readonly
                        />

                        <TextInput
                            label="Currency"
                            v-model="form.cus_currency"
                            icon="currency-dollar"
                            readonly
                        />
                        <TextInput
                            label="NAV Code"
                            v-model="form.nav_code"
                            icon="code"
                            readonly
                        />
                        <TextInput
                            label="Credit Limit"
                            v-model="form.credit_limit"
                            icon="credit-card"
                            readonly
                        />
                        <TextInput
                            label="Payment Terms"
                            v-model="form.payment_terms"
                            icon="clock"
                            readonly
                        />
                        <TextInput
                            label="Status"
                            v-model="form.cus_status"
                            icon="status-online"
                            readonly
                        />
                    </div>

                    <!-- Checkbox Toggles -->
                    <div
                        class="mt-6 p-4 bg-[var(--color-bg-secondary)] rounded-lg border border-[var(--color-border)]"
                    >
                        <div class="grid grid-cols-4 gap-2">
                            <div
                                v-for="(item, index) in toggleItems"
                                :key="index"
                                class="flex items-center space-x-2"
                            >
                                <div class="relative inline-flex items-center">
                                    <!-- Hidden checkbox that controls the state -->
                                    <input
                                        type="checkbox"
                                        :checked="form[item.key]"
                                        class="sr-only"
                                        disabled
                                    />
                                    <!-- Toggle track -->
                                    <div
                                        :class="[
                                            'w-11 h-6 rounded-full transition-colors',
                                            form[item.key]
                                                ? 'bg-[var(--color-bg-avatar)]'
                                                : 'bg-[var(--color-bg-avatar)]/50',
                                        ]"
                                    >
                                        <!-- Toggle circle -->
                                        <div
                                            :class="[
                                                'absolute top-0.5 w-5 h-5 bg-white rounded-full transition-transform',
                                                form[item.key]
                                                    ? 'translate-x-5 left-[2px]'
                                                    : 'left-0.5',
                                            ]"
                                        ></div>
                                    </div>
                                    <span
                                        class="text-sm font-semibold text-[var(--color-text-primary)] ml-2"
                                    >
                                        {{ item.label }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-2">
                        <TextInput
                            label="General Posting"
                            v-model="form.gen_posting"
                            icon="document-text"
                            readonly
                        />
                        <TextInput
                            label="Customer Posting"
                            v-model="form.cus_posting"
                            icon="user-circle"
                            readonly
                        />
                        <TextInput
                            label="VAT Posting"
                            v-model="form.vat_posting"
                            icon="receipt-tax"
                            readonly
                        />
                        <TextInput
                            label="Advanced Payment Balance"
                            v-model="form.advance_payment_balance"
                            icon="receipt-tax"
                            readonly
                        />
                    </div>

                    <!-- Footer -->
                    <div
                        class="mt-4 pt-2 border-t border-[var(--color-border)] flex justify-end"
                    >
                        <button
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
    </Transition>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import TextInput from "../../Pages/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import SvgIcon from "@jamescoyle/vue-icon";
import { mdiClose } from "@mdi/js";

const props = defineProps({
    show: Boolean,
    selected: Object,
});

const form = useForm({
    cus_id: null,
    cus_code: null,
    cus_name: null,
    cus_type: null,
    cus_price_group: null,
    cus_address: null,
    cus_tin: null,
    cus_currency: null,
    cus_bu: null,
    nav_code: null,
    credit_limit: null,
    payment_terms: null,
    non_trade: null,
    applies_shrinkage: null,
    editable_wht: null,
    journal_voucher: null,
    gen_posting: null,
    cus_posting: null,
    vat_posting: null,
    cus_status: null,
    advance_payment_balance: null,
    setup_by: null,
});

const toggleItems = computed(() => [
    { key: "non_trade", label: "Non Trade" },
    { key: "applies_shrinkage", label: "Applies Shrinkage" },
    { key: "editable_wht", label: "Editable WHT" },
    { key: "journal_voucher", label: "Journal Voucher" },
]);

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
        modalLoading.value = true;
        if (visible) {
            const safeAssign = (value, type = "string") => {
                if (value !== null && value !== undefined && value !== "") {
                    if (type === "boolean") {
                        return (
                            value === true ||
                            value === 1 ||
                            value === "1" ||
                            value === "true"
                        );
                    }
                    return value;
                }

                switch (type) {
                    case "number":
                        return 0;
                    case "decimal":
                        return 0.0;
                    case "boolean":
                        return false;
                    case "string":
                    default:
                        return "N/A";
                }
            };
            form.cus_id = safeAssign(props.selected.cus_id, "number");
            form.cus_code = safeAssign(props.selected.cus_code, "string");
            form.cus_name = safeAssign(props.selected.cus_name, "string");
            form.cus_type = safeAssign(props.selected.cus_type, "string");
            form.cus_price_group = safeAssign(
                props.selected.cus_price_group,
                "string"
            );
            form.cus_address = safeAssign(props.selected.cus_address, "string");
            form.cus_tin = safeAssign(props.selected.cus_tin, "string");
            form.cus_currency = safeAssign(
                props.selected.cus_currency,
                "string"
            );
            form.cus_bu = safeAssign(props.selected.cus_bu, "string");
            form.nav_code = safeAssign(props.selected.nav_code, "string");
            form.credit_limit = safeAssign(
                props.selected.credit_limit,
                "decimal"
            );
            form.payment_terms = safeAssign(
                props.selected.payment_terms,
                "string"
            );
            form.non_trade = safeAssign(props.selected.non_trade, "boolean");
            form.applies_shrinkage = safeAssign(
                props.selected.applies_shrinkage,
                "boolean"
            );
            form.editable_wht = safeAssign(
                props.selected.editable_wht,
                "boolean"
            );
            form.journal_voucher = safeAssign(
                props.selected.journal_voucher,
                "boolean"
            );
            form.gen_posting = safeAssign(props.selected.gen_posting, "string");
            form.cus_posting = safeAssign(props.selected.cus_posting, "string");
            form.vat_posting = safeAssign(props.selected.vat_posting, "string");
            form.cus_status = safeAssign(props.selected.cus_status, "string");
            form.setup_by = safeAssign(props.selected.setup_by, "string");

            form.advance_payment_balance = safeAssign(
                formatCurrency(props.selected.advanced_payment_balance),
                "string"
            );

            modalLoading.value = false;
        }
    },
    { immediate: true }
);
</script>
<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.spinner {
    position: relative;
    width: 60px;
    height: 60px;
}

.spinner-blade {
    position: absolute;
    left: 50%;
    top: 50%;
    width: 3px;
    height: 15px;
    margin-left: -1.5px;
    background-color: #008236;
    border-radius: 2px;
    transform-origin: center -12px;
    animation: spinner-fade 1s infinite linear;
}

.spinner-blade:nth-child(1) {
    transform: rotate(0deg) translateY(-12px);
    animation-delay: 0s;
}
.spinner-blade:nth-child(2) {
    transform: rotate(45deg) translateY(-12px);
    animation-delay: 0.1s;
}
.spinner-blade:nth-child(3) {
    transform: rotate(90deg) translateY(-12px);
    animation-delay: 0.2s;
}
.spinner-blade:nth-child(4) {
    transform: rotate(135deg) translateY(-12px);
    animation-delay: 0.3s;
}
.spinner-blade:nth-child(5) {
    transform: rotate(180deg) translateY(-12px);
    animation-delay: 0.4s;
}

@keyframes spinner-fade {
    0% {
        opacity: 0.2;
    }
    50% {
        opacity: 1;
    }
    100% {
        opacity: 0.2;
    }
}
</style>
