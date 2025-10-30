<template>
    <div
        v-if="show"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center px-4"
    >
        <ToastAlertWarning :show="showToast" :message="toastMessage" />
        <div
            class="bg-[rgb(15,42,29)] text-white w-full max-w-5xl rounded-2xl shadow-2xl shadow-[#131313a2] border border-[rgb(30,60,45)] overflow-hidden"
        >
            <form @submit.prevent="submit">
                <!-- Header -->
                <div class="px-8 pt-6 pb-4">
                    <h2 class="text-2xl font-bold text-center">ADD NEW ITEM</h2>
                    <div
                        class="mt-2 h-0.5 bg-gradient-to-r from-transparent via-green-600/50 to-transparent"
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
                        <!-- Left: Image Upload -->
                        <div class="w-full md:w-1/3 flex flex-col items-center">
                            <label
                                class="group relative w-full aspect-square rounded-xl p-2 border-2 border-dashed border-gray-400 hover:border-green-500 shadow-lg shadow-[#131313a2] flex items-center justify-center overflow-hidden cursor-pointer"
                            >
                                <img
                                    :src="imageUrl"
                                    alt="Item Preview"
                                    class="object-cover w-full h-full transition group-hover:opacity-80 rounded-xl"
                                />
                                <input
                                    type="file"
                                    accept="image/*"
                                    @change="onImageChange"
                                    class="hidden"
                                />
                                <div
                                    class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-30 text-white opacity-0 group-hover:opacity-100 transition"
                                >
                                    <span class="text-sm font-medium"
                                        >Change Photo</span
                                    >
                                </div>
                            </label>
                            <p class="mt-3 text-center font-medium">
                                Item Photo
                            </p>
                        </div>

                        <!-- Right: Form Fields -->
                        <div
                            class="w-full md:w-2/3 grid grid-cols-1 md:grid-cols-2 gap-4"
                        >
                            <TextInput
                                label="Code"
                                v-model="form.code"
                                type="text"
                                :message="form.errors.code"
                            />
                            <TextInput
                                label="Setup Date"
                                v-model="form.setup_date"
                                type="date"
                                :readonly="true"
                                :message="form.errors.setup_date"
                            />
                            <TextInput
                                label="Name"
                                v-model="form.name"
                                type="text"
                                :message="form.errors.name"
                                class="md:col-span-2"
                            />
                            <TextInput
                                label="Description"
                                v-model="form.description"
                                type="textarea"
                                :message="form.errors.description"
                                class="md:col-span-2"
                            />
                            <TextInput
                                label="Type"
                                v-model="form.type"
                                type="select"
                                :options="typeOptions"
                                :message="form.errors.type"
                            />
                            <TextInput
                                label="Account Code"
                                v-model="form.acc_code"
                                type="text"
                                :message="form.errors.acc_code"
                            />
                        </div>
                    </div>
                </div>
                <!-- Footer -->
                <div
                    class="mt-4 mx-8 pb-6 pt-2 border-t gap-2 border-white/10 flex justify-end"
                >
                    <button
                        type="submit"
                        class="submitButton"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing">Submitting...</span>
                        <span v-else>Submit</span>
                    </button>
                    <button
                        type="button"
                        @click="closeModal"
                        class="closeButton"
                    >
                        Close
                    </button>
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
import axios from "axios";
import ToastAlertWarning from "../../Pages/Components/ToastAlertWarning.vue";

const props = defineProps({
    show: Boolean,
});

const form = useForm({
    code: null,
    setup_date: null,
    name: null,
    description: null,
    type: null,
    acc_code: null,
    item_photo: null,
});

form.setup_date = new Date().toISOString().split("T")[0];
const imageUrl = ref("/storage/images/addItem.png");
const imageFile = ref(null);
const typeOptions = ref([]);
const modalLoading = ref(false);

function onImageChange(event) {
    const file = event.target.files[0];
    if (file) {
        form.item_photo = imageFile.value = file;
        imageUrl.value = URL.createObjectURL(file);
        // Handle file upload logic here if needed
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
    form.post(route("addItem"), {
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

watch(
    () => props.show,
    async (newVal) => {
        modalLoading.value = true;
        if (newVal) {
            try {
                const response = await axios.get(route("ci-types"));
                typeOptions.value = response.data;
            } catch (error) {
                console.error("Failed to fetch ci_types:", error);
            } finally {
                // modalLoading.value = false;
            }
        }
    },
    { immediate: true }
);
</script>
