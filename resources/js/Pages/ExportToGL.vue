<template>
    <div>
        <ToastAlertWarning :show="showToast" :message="toastMessage" />
        <ToastAlert :show="showSToast" :message="toastSMessage" />
        <!-- Loading state with progress -->
        <div
            v-if="loading"
            class="fixed inset-0 bg-opacity-50 backdrop-blur-sm flex items-center justify-center p-4 z-50"
        >
            <div
                class="w-full max-w-md bg-[var(--color-bg-secondary)] border border-[var(--color-border)] rounded-2xl shadow-xl overflow-hidden transition-all duration-300"
            >
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3
                            class="text-lg font-semibold text-[var(--color-text-primary])]"
                        >
                            {{ progressMessage }}
                        </h3>
                        <span
                            class="text-lg font-medium text-[var(--color-primary)]"
                        >
                            {{ progress }}%
                        </span>
                    </div>

                    <!-- Animated progress bar -->
                    <div
                        class="relative h-2.5 bg-[var(--color-bg-primary)] rounded-full overflow-hidden"
                    >
                        <div
                            class="absolute top-0 left-0 h-full bg-gradient-to-r from-[var(--color-primary)] to-[var(--color-primary-hover)] rounded-full transition-all duration-500 ease-out"
                            :style="{ width: progress + '%' }"
                        >
                            <div
                                class="absolute inset-0 opacity-30 animate-pulse"
                            ></div>
                        </div>
                    </div>

                    <!-- Animated dots for visual interest -->
                    <div class="flex justify-center space-x-1.5 pt-1">
                        <div
                            v-for="i in 3"
                            :key="i"
                            class="w-2 h-2 bg-[var(--color-primary)] rounded-full animate-bounce"
                            :style="`animation-delay: ${i * 0.15}s`"
                        ></div>
                    </div>

                    <p
                        class="text-center text-sm text-[var(--color-text-secondary)] font-medium"
                    >
                        Generating Text File...
                    </p>
                </div>

                <!-- Subtle animated border at bottom -->
                <div
                    class="h-1 bg-gradient-to-r from-transparent via-[var(--color-primary)] to-transparent animate-[pulse_2s_infinite]"
                ></div>
            </div>
        </div>
        <div
            class="min-h-[82vh] p-6 rounded-xl mt-6 flex items-center justify-center w-full"
        >
            <div
                class="p-5 rounded-lg bg-[var(--color-bg-secondary)]/20 backdrop-blur-sm border border-[var(--color-border)]/30 hover:border-[var(--color-primary)]/50 shadow-[0_4px_30px_var(--color-shadow)]/20 transition-all w-full"
            >
                <h3
                    class="font-semibold text-lg pb-3 mb-3 border-b border-[var(--color-border)]/30 flex items-center gap-2"
                >
                    <svg-icon
                        type="mdi"
                        :path="mdiInvoiceTextSendOutline"
                        class="w-8 h-8 text-[var(--color-primary)]"
                    />
                    Export To GL (Nav RBreeder)
                </h3>
                <form @submit.prevent="submit">
                    <div class="p-5 rounded-lg transition-all">
                        <div class="flex flex-col gap-10">
                            <div>
                                <label class="block text-md font-bold"
                                    >Please Select Export Type Below</label
                                >
                                <div class="w-full flex gap-4 mt-4">
                                    <!-- Other Income Option -->
                                    <label
                                        class="w-full inline-flex items-center cursor-pointer group"
                                    >
                                        <input
                                            type="radio"
                                            v-model="form.export_type"
                                            value="Other Income"
                                            class="hidden peer"
                                        />
                                        <div
                                            class="w-full relative flex items-center justify-center p-2"
                                        >
                                            <!-- Hover circle -->
                                            <div
                                                class="absolute -inset-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-[var(--color-border)]/40"
                                                :class="{
                                                    'opacity-100':
                                                        form.export_type ===
                                                        'Other Income',
                                                }"
                                            ></div>
                                            <!-- Radio button -->
                                            <div
                                                class="relative w-5 h-5 mr-2 rounded-full border-2 border-[var(--color-bg-avatar)] transition-colors z-10 group-hover:border-[var(--color-border)]"
                                                :class="{
                                                    'border-[var(--color-border)]':
                                                        form.export_type ===
                                                        'Other Income',
                                                }"
                                            >
                                                <div
                                                    class="absolute inset-0 m-auto w-2.5 h-2.5 rounded-full bg-[var(--color-border)] transition-opacity"
                                                    :class="{
                                                        'opacity-100':
                                                            form.export_type ===
                                                            'Other Income',
                                                        'opacity-0':
                                                            form.export_type !==
                                                            'Other Income',
                                                    }"
                                                ></div>
                                            </div>
                                            <span
                                                class="text-sm font-medium z-10"
                                                >Other Income/Charge
                                                Invoice</span
                                            >
                                        </div>
                                    </label>

                                    <!-- Adjustment Option -->
                                    <label
                                        class="w-full inline-flex items-center cursor-pointer group"
                                    >
                                        <input
                                            type="radio"
                                            v-model="form.export_type"
                                            value="Adjustment"
                                            class="hidden peer"
                                        />
                                        <div
                                            class="w-full relative flex items-center justify-center p-2"
                                        >
                                            <!-- Hover circle -->
                                            <div
                                                class="absolute -inset-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-[var(--color-border)]/40"
                                                :class="{
                                                    'opacity-100':
                                                        form.export_type ===
                                                        'Adjustment',
                                                }"
                                            ></div>
                                            <!-- Radio button -->
                                            <div
                                                class="relative w-5 h-5 mr-2 rounded-full border-2 border-[var(--color-bg-avatar)] transition-colors z-10 group-hover:border-[var(--color-border)]"
                                                :class="{
                                                    'border-[var(--color-border)]':
                                                        form.export_type ===
                                                        'Adjustment',
                                                }"
                                            >
                                                <div
                                                    class="absolute inset-0 m-auto w-2.5 h-2.5 rounded-full bg-[var(--color-border)] transition-opacity"
                                                    :class="{
                                                        'opacity-100':
                                                            form.export_type ===
                                                            'Adjustment',
                                                        'opacity-0':
                                                            form.export_type !==
                                                            'Adjustment',
                                                    }"
                                                ></div>
                                            </div>
                                            <span
                                                class="text-sm font-medium z-10"
                                                >Adjustment</span
                                            >
                                        </div>
                                    </label>

                                    <!-- Payment Option -->
                                    <label
                                        class="w-full inline-flex items-center cursor-pointer group"
                                    >
                                        <input
                                            type="radio"
                                            v-model="form.export_type"
                                            value="Payment"
                                            class="hidden peer"
                                        />
                                        <div
                                            class="w-full relative flex items-center justify-center p-2"
                                        >
                                            <!-- Hover circle -->
                                            <div
                                                class="absolute -inset-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-[var(--color-border)]/40"
                                                :class="{
                                                    'opacity-100':
                                                        form.export_type ===
                                                        'Payment',
                                                }"
                                            ></div>
                                            <!-- Radio button -->
                                            <div
                                                class="relative w-5 h-5 mr-2 rounded-full border-2 border-[var(--color-bg-avatar)] transition-colors z-10 group-hover:border-[var(--color-border)]"
                                                :class="{
                                                    'border-[var(--color-border)]':
                                                        form.export_type ===
                                                        'Payment',
                                                }"
                                            >
                                                <div
                                                    class="absolute inset-0 m-auto w-2.5 h-2.5 rounded-full bg-[var(--color-border)] transition-opacity"
                                                    :class="{
                                                        'opacity-100':
                                                            form.export_type ===
                                                            'Payment',
                                                        'opacity-0':
                                                            form.export_type !==
                                                            'Payment',
                                                    }"
                                                ></div>
                                            </div>
                                            <span
                                                class="text-sm font-medium z-10"
                                                >Payment</span
                                            >
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <label
                                        class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2"
                                        >Start Date</label
                                    >
                                    <DatePicker
                                        v-model="form.start_date"
                                        placeholder="Select Date"
                                        format="MM-DD-YYYY"
                                        :message="form.errors.start_date"
                                    />
                                </div>
                                <div class="space-y-1">
                                    <label
                                        class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2"
                                        >End Date</label
                                    >
                                    <DatePicker
                                        v-model="form.end_date"
                                        placeholder="Select Date"
                                        format="MM-DD-YYYY"
                                        :message="form.errors.end_date"
                                    />
                                </div>
                            </div>

                            <div
                                class="w-full flex justify-center items-center gap-4"
                            >
                                <button
                                    v-if="canUpdate('0404-EXPRTGL')"
                                    type="submit"
                                    @click="submitType = 'untag'"
                                    class="submitButton w-full !flex !justify-center !items-center"
                                    :disabled="form.processing"
                                >
                                    <span>{{
                                        form.processing
                                            ? "Untagging Text File..."
                                            : "Untag Selected Export Type"
                                    }}</span>
                                </button>
                                <button
                                    type="submit"
                                    @click="submitType = 'generate'"
                                    class="submitButton w-full !flex !justify-center !items-center"
                                    :disabled="form.processing"
                                >
                                    <span>{{
                                        form.processing
                                            ? "Generating Text File..."
                                            : "Generate Selected Export Type"
                                    }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import { mdiInvoiceTextSendOutline } from "@mdi/js";
import { route } from "../../../vendor/tightenco/ziggy/src/js";
import ToastAlertWarning from "./Components/ToastAlertWarning.vue";
import { onMounted, onUnmounted, ref } from "vue";
import DatePicker from "./Components/DatePicker.vue";
import ToastAlert from "./Components/ToastAlert.vue";
import usePermissions from "./Composables/usePermissions";

const { canUpdate } = usePermissions();

const form = useForm({
    export_type: "Other Income",
    start_date: null,
    end_date: null,
});

const submitType = ref(null);

const loading = ref(false);
const progress = ref(0);
const progressMessage = ref("Starting text file generation...");
const error = ref(null);
const channel = ref(null);
let echo = null;
const userId = ref(null);
const page = usePage();
let channelInstance = null;
const pathDelete = ref(null);

const showToast = ref(false);
const toastMessage = ref("");
let toastTimeout = null;

const showSToast = ref(false);
const toastSMessage = ref("");
let toastSTimeout = null;

const showSuccessToast = (message) => {
    toastSMessage.value = message;
    showSToast.value = false;
    if (toastSTimeout) clearTimeout(toastSTimeout);

    setTimeout(() => {
        showSToast.value = true;
    }, 0);

    toastSTimeout = setTimeout(() => {
        showSToast.value = false;
        toastSTimeout = null;
    }, 3000);
};

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

const setupWebSocketListener = () => {
    if (!channel.value) {
        error.value = "No channel provided by server";
        loading.value = false;
        return;
    }

    if (channelInstance) {
        echo.leave(channel.value);
        channelInstance = null;
    }

    const cleanup = async () => {
        loading.value = false;
        progress.value = 0;
        progressMessage.value = "Ready for new export";
        if (channelInstance) {
            window.Echo.leave(channel.value);
            channelInstance = null;
        }
        form.reset();
    };

    // console.log("Connecting to channel:", channel.value);

    channelInstance = window.Echo.private(channel.value)
        .listen("ExportTextFileGenerationProgress", (data) => {
            progress.value = data.progress;
            progressMessage.value = data.message;
            error.value = null;
        })
        .listen("ExportTextFileGenerated", (data) => {
            const link = document.createElement("a");
            link.href = data.path;
            link.download = data.filename;

            document.body.appendChild(link);

            link.click();

            document.body.removeChild(link);

            showSuccessToast("Text File Export Successful");

            cleanup();
        })
        .error((err) => {
            console.error("WebSocket error:", err);
            error.value = "Connection lost. Please retry.";
            loading.value = false;
        });
};

const submit = async () => {
    if (submitType.value === "untag") {
        untagExport();
    } else {
        generateExport();
    }
};

const generateExport = async () => {
    if (!form.export_type || !form.start_date || !form.end_date) {
        showWarningToast("Please Fill In All Required Fields");
        return;
    }

    if (new Date(form.start_date) > new Date(form.end_date)) {
        showWarningToast("End Date Must Be After Start Date");
        return;
    }

    try {
        progressMessage.value = "Starting Text File Generation...";

        const response = await axios.post(
            route("generateTextFile"),
            form.data(),
            {}
        );

        loading.value = true;
        error.value = null;
        progress.value = 0;

        if (response.data.success === false) {
            showWarningToast(
                response.data.message ||
                    "No data found for the selected date range"
            );
            loading.value = false;
            return; // stop here
        }

        if (response.data.channel) {
            channel.value = response.data.channel;
            setupWebSocketListener();
        } else {
            throw new Error("Failed to start TextFile generation");
        }
    } catch (err) {
        // console.error("Error starting TextFile generation:", err);
        if (err.response?.status === 422 && err.response?.data?.errors) {
            const validationErrors = err.response.data.errors;

            if (Object.keys(validationErrors).length === 1) {
                const firstError = Object.values(validationErrors)[0][0]; // Get first error message
                showWarningToast(firstError);
            } else {
                showWarningToast(
                    "Please fill in all required fields correctly"
                );
            }
        } else {
            error.value =
                err.response?.data?.message ||
                err.message ||
                "Failed to start TextFile generation";
            showWarningToast(error.value);
        }

        loading.value = false;
    }
};

const untagExport = () => {
    form.post(route("untagExport"), {
        onSuccess: () => {
            showSuccessToast("Untagged Successfully");
            form.reset();
        },
        onError: (error) => {
            showToast.value = false;
            if (Object.keys(error).length === 1) {
                const firstError = Object.values(error)[0];
                showWarningToast(firstError);
            } else if (Object.keys(error).length !== 1) {
                showWarningToast("Please Fill In All Required Fields");
            }
        },
    });
};

const deletePdf = async () => {
    if (!pathDelete.value) return;

    try {
        await axios.delete(route("pdf.delete"), {
            data: { path: pathDelete.value.split("/storage/")[1] },
        });
    } catch (err) {
        console.warn("Failed to delete TextFile:", err);
    }
};

onMounted(() => {
    userId.value = page.props.auth.user.id || null;
});

onUnmounted(() => {
    if (echo) {
        echo.leave(channel.value);
    }
});
</script>
