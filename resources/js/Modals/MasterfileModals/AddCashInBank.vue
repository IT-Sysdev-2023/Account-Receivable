<template>
    <div
        v-if="show"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center px-4"
    >
        <ToastAlertWarning :show="showToast" :message="toastMessage" />
        <div
            class="bg-[var(--color-bg-secondary)] text-[var(--color-text-primary)] w-full max-w-5xl rounded-2xl border border-[var(--color-border)]"
        >
            <form @submit.prevent="submit">
                <!-- Header -->
                <div class="px-8 pt-6 pb-4">
                    <h2 class="text-2xl font-bold text-center">
                        ADD NEW CASH IN BANK
                    </h2>
                    <div
                        class="mt-2 h-0.5 bg-gradient-to-r from-transparent via-[var(--color-border)] to-transparent"
                    ></div>
                </div>
                <div class="px-8 space-y-6">
                    <div class="flex flex-col md:flex-row gap-6">
                        <!-- Right: Form Fields -->
                        <div
                            class="w-full grid grid-cols-1 md:grid-cols-2 gap-4"
                        >
                            <TextInput
                                label="Bank Code"
                                v-model="form.bank_code"
                                type="text"
                                :readonly="true"
                                :message="form.errors.bank_code"
                            />
                            <TextInput
                                label="Bank Name"
                                v-model="form.bank_name"
                                type="text"
                                :message="form.errors.bank_name"
                            />
                            <TextInput
                                label="Address"
                                v-model="form.address"
                                type="textarea"
                                :message="form.errors.address"
                                class="md:col-span-2"
                            />
                            <TextInput
                                label="Account Code"
                                v-model="form.acc_code"
                                type="text"
                                :message="form.errors.acc_code"
                            />
                            <TextInput
                                label="Account Classification"
                                v-model="form.acc_classification"
                                type="text"
                                :message="form.errors.acc_classification"
                            />
                        </div>
                    </div>
                </div>
                <!-- Footer -->
                <div
                    class="mt-4 mx-8 pb-6 pt-2 border-t gap-2 border-[var(--color-border)] flex justify-end"
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
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from "vue";
import { route } from "../../../../vendor/tightenco/ziggy/src/js";
import TextInput from "../../Pages/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import ToastAlertWarning from "../../Pages/Components/ToastAlertWarning.vue";
import { mdiClose, mdiNavigationVariantOutline } from "@mdi/js";

const props = defineProps({
    show: Boolean,
});

const form = useForm({
    bank_code: null,
    bank_name: null,
    address: null,
    acc_code: null,
    acc_classification: null,
});

function pad(num, size = 3) {
    return String(num).padStart(size, "0");
}

async function nextBankCode() {
    try {
        const { data } = await axios.get(route("getlatestbankcode"));
        if (!data) return "B001";

        const prefix = data.slice(0, 1);
        const num = parseInt(data.slice(1), 10);
        return `${prefix}${pad(num + 1)}`;
    } catch (err) {
        console.error(err);
        return "B001";
    }
}

watch(
    () => props.show,
    async (visible) => {
        if (visible) {
            form.bank_code = await nextBankCode();
        }
    },
    { immediate: true }
);

const emit = defineEmits(["close", "closeSuccess"]);

const closeModal = () => {
    emit("close");
};

//////////////////////////////////////// SHOW TOAST /////////////////////////////////////////////////////////////////////////////////////////
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

////////////  //////////////////////////////////////////////////////////////////////////////////////////////////////////

const submit = () => {
    form.post(route("addCashInBank"), {
        onSuccess: () => {
            form.reset();
            emit("closeSuccess");
        },
        onError: (error) => {
            showToast.value = false;
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
