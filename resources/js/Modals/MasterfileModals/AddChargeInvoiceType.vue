<template>
    <div
        v-if="show"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center px-4"
    >
        <ToastAlertWarning :show="showToast" :message="toastMessage" />
        <div
            class="bg-[var(--color-bg-secondary)] text-[var(--color-text-primary)] w-full max-w-xl rounded-2xl border border-[var(--color-border)]"
        >
            <form @submit.prevent="submit">
                <!-- Header -->
                <div class="px-8 pt-6 pb-4">
                    <h2 class="text-2xl font-bold text-center">
                        ADD NEW CHARGE INVOICE TYPE
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
                                label="Sequence Number"
                                v-model="form.sequence_no"
                                type="text"
                                :readonly="true"
                                :message="form.errors.sequence_no"
                            />
                            <TextInput
                                label="Nav Code"
                                v-model="form.nav_code"
                                type="text"
                                :message="form.errors.nav_code"
                            />
                            <TextInput
                                label="Charge Invoice Type"
                                v-model="form.ci_type"
                                type="text"
                                :message="form.errors.ci_type"
                                class="md:col-span-2"
                            />
                        </div>
                    </div>
                </div>
                <!-- Footer -->
                <div
                    class="mt-4 mx-8 pb-6 pt-2 gap-2 border-t border-[var(--color-border)] flex justify-end"
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
    sequence_no: null,
    ci_type: null,
    nav_code: null,
});

async function nextSequenceNo() {
    try {
        const { data } = await axios.get(route("getlatestChargeInvoiceType")); // "1" or null
        const next = (data ?? 0) + 1;
        return `${next}`;
    } catch (err) {
        console.error(err);
        return "1"; // safe fallback
    }
}

watch(
    () => props.show,
    async (visible) => {
        if (visible) {
            form.sequence_no = await nextSequenceNo();
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
    form.post(route("addChargeInvoiceType"), {
        onSuccess: () => {
            form.reset(); // clear on success
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
