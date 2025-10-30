<template>
    <div
        v-if="show"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm flex justify-center items-center z-50"
    >
        <ToastAlertWarning :show="showToast" :message="toastMessage" />

        <form @submit.prevent="submit">
            <div
                class="bg-[var(--color-bg-secondary)] text-[var(--color-text-primary)] p-6 rounded-2xl w-[700px] border border-[var(--color-border)] relative"
            >
                <div class="flex justify-center -mt-16">
                    <label class="relative cursor-pointer w-32 h-32">
                        <!-- Image Preview -->
                        <img
                            :src="imageUrl"
                            alt="Employee Photo"
                            class="w-32 h-32 rounded-full border-4 border-[var(--color-border)] object-cover shadow-lg shadow-[#131313a2]"
                        />

                        <!-- Hidden File Input -->
                        <input
                            type="file"
                            accept="image/*"
                            @change="onImageChange"
                            class="hidden"
                        />

                        <!-- Optional Hover Overlay -->
                        <div
                            class="absolute inset-0 bg-[var(--color-primary)]/80 rounded-full flex items-center justify-center opacity-0 hover:opacity-100 transition"
                        >
                            <span class="text-white text-sm font-medium"
                                >Change</span
                            >
                        </div>
                    </label>
                </div>

                <!-- Right Column: Inputs -->
                <div class="flex-1">
                    <h3 class="text-2xl text-center font-bold my-4">
                        ADD NEW CHECKER
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-2">
                        <TextInput
                            label="First Name"
                            v-model="form.first_name"
                            type="text"
                            :message="form.errors.first_name"
                        />
                        <TextInput
                            label="Last Name"
                            v-model="form.last_name"
                            type="text"
                            :message="form.errors.last_name"
                        />
                    </div>
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
                                <span v-if="form.processing"
                                    >Submitting...</span
                                >
                                <span v-else>Submit</span>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import { route } from "../../../../vendor/tightenco/ziggy/src/js";
import TextInput from "../../Pages/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import ToastAlertWarning from "../../Pages/Components/ToastAlertWarning.vue";
import { mdiClose, mdiNavigationVariantOutline } from "@mdi/js";

const props = defineProps({
    show: Boolean,
});

const form = useForm({
    first_name: null,
    last_name: null,
    photo: null,
});

const imageUrl = ref("/storage/images/default_profile_pic.png");
const imageFile = ref(null);

function onImageChange(event) {
    const file = event.target.files[0];
    if (file) {
        form.photo = imageFile.value = file;
        imageUrl.value = URL.createObjectURL(file);
    }
}

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
    form.post(route("addChecker"), {
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
