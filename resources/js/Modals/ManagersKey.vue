<template>
    <div
        v-if="show"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center px-4"
    >
        <ToastAlertWarning :show="showToast" :message="toastMessage" />
        <div
            class="bg-[var(--color-bg-secondary)] rounded-2xl p-6 w-full max-w-md space-y-4 border border-[var(--color-border)]"
        >
            <h2
                class="text-xl font-bold text-center text-[var(--color-text-primary)]"
            >
                Manager Authorization
            </h2>
            <p class="text-center text-[var(--color-text-primary)]">
                Enter Manager's Key Code!
            </p>

            <div class="flex flex-col">
                <TextInput
                    @keydown.enter="confirm(true)"
                    type="text"
                    v-model="managersKeyCode"
                    default-placeholder="Enter Manager's Key Code"
                />
                <!-- <TextInput
                    @keydown.enter="confirm(true)"
                    type="password"
                    v-model="managerPasswordInput"
                    default-placeholder="Enter Password"
                /> -->
            </div>
            <!-- <input
                class="block w-full rounded border shadow-lg shadow-[#131313a2] border-amber-50 px-4 py-1.5 text-black placeholder-gray-800 sm:text-sm bg-gray-200 focus:outline-none focus:ring-2 focus:ring-green-700 focus:border-green-700"
            /> -->

            <div class="flex justify-end gap-4 pt-4">
                <button
                    type="button"
                    @click="confirm(false)"
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
                        Cancel
                    </div>
                </button>
                <button
                    type="button"
                    @click="confirm(true)"
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
                        <span v-if="form.processing">Confirming...</span>
                        <span v-else>Confirm</span>
                    </div>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from "vue";
import ToastAlertWarning from "../Pages/Components/ToastAlertWarning.vue";
import axios from "axios";
import TextInput from "../Pages/Components/TextInput.vue";
import { mdiClose, mdiNavigationVariantOutline } from "@mdi/js";

const props = defineProps({
    show: Boolean,
});

const emit = defineEmits(["success", "cancel"]);

const managerUsernameInput = ref("");
const managerPasswordInput = ref("");
const managersKeyCode = ref("");
const errorMessage = ref("");

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

const form = reactive({
    processing: false,
});

const confirm = async (choice) => {
    if (choice) {
        form.processing = true;
        errorMessage.value = "";

        try {
            if (managersKeyCode.value !== "") {
                const response = await axios.post(route("validateManagerKey"), {
                    managerskeycode: managersKeyCode.value,
                });

                if (response.data.authorized) {
                    emit("success", {
                        person_authored: response.data.user_name,
                    });
                } else {
                    showWarningToast(response.data.message);
                }
                managersKeyCode.value = "";
            } else {
                showWarningToast("Please Enter Manager's Key Code");
            }
        } catch (error) {
            //console.log(error);
            showWarningToast("Error Please Try Again");
        }
        form.processing = false;
    } else {
        managersKeyCode.value = "";
        errorMessage.value = "";
        form.processing = false;
        emit("cancel");
    }
};
</script>
