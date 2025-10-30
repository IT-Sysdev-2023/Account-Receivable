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
                        EDIT ADJUSTMENT SETUP
                    </h2>
                    <div
                        class="mt-2 h-0.5 bg-gradient-to-r from-transparent via-[var(--color-border)] to-transparent"
                    ></div>
                </div>
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
                <div v-else class="px-8 space-y-6">
                    <div class="flex flex-col md:flex-row gap-6">
                        <!-- Right: Form Fields -->
                        <div
                            class="w-full grid grid-cols-1 md:grid-cols-2 gap-4"
                        >
                            <TextInput
                                label="Account Code"
                                v-model="form.acc_code"
                                type="text"
                                :message="form.errors.acc_code"
                            />
                            <TextInput
                                label=" Reason Name"
                                v-model="form.reason_name"
                                type="text"
                                :message="form.errors.reason_name"
                            />
                            <DropdownInput
                                label="Type"
                                v-model="form.type"
                                :options="[
                                    'Sales Invoice',
                                    'Other Income',
                                    'Payment',
                                ]"
                                placeholder="Click to Select"
                                :message="form.errors.type"
                            />
                            <DropdownInput
                                label="Status"
                                v-model="form.status"
                                :options="['Active', 'Inactive']"
                                placeholder="Click to Select"
                                :message="form.errors.status"
                            />
                        </div>
                    </div>
                </div>
                <!-- Footer -->
                <div
                    class="mt-4 mx-8 pb-6 pt-2 border-t gap-2 border-[var(--color-border)] flex justify-between"
                >
                    <div class="flex items-center">
                        <p class="text-xs">
                            Updated By : {{ props.adjustment.created_by }}
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
                                <span v-if="form.processing">Saving...</span>
                                <span v-else>Save Changes</span>
                            </div>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import { route } from "../../../../vendor/tightenco/ziggy/src/js";
import TextInput from "../../Pages/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import ToastAlertWarning from "../../Pages/Components/ToastAlertWarning.vue";
import DropdownInput from "../../Pages/Components/DropdownInput.vue";
import { mdiClose, mdiNavigationVariantOutline } from "@mdi/js";

const props = defineProps({
    show: Boolean,
    adjustment: Object,
});

const form = useForm({
    acc_code: null,
    reason_name: null,
    type: null,
    status: null,
});

const modalLoading = ref(false);

// Set initial value
watch(
    () => props.show,
    (newVal) => {
        modalLoading.value = true;
        if (newVal && props.adjustment) {
            form.acc_code = props.adjustment.acc_code;
            form.reason_name = props.adjustment.reason_name;
            form.type = props.adjustment.type;
            form.status = props.adjustment.status;
            modalLoading.value = false;
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

////////////  //////////////////////////////////////////////////////////////////////////////////////////////////////////

const submit = () => {
    form.put(route("editAdjustmentReasonSetup", props.adjustment.id), {
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
