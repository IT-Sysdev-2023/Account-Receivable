<template>
    <div
        v-if="show"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center px-4"
    >
        <ToastAlertWarning :show="showToast" :message="toastMessage" />

        <form
            @submit.prevent="submit"
            class="bg-[rgb(15,42,29)] text-gray-900 dark:text-white w-full max-w-xl rounded-xl shadow-lg shadow-[#131313a2] border border-[rgb(30,60,45)] overflow-hidden"
        >
            <!-- Show spinner while loading (unchanged as requested) -->
            <div
                v-if="modalLoading"
                class="flex justify-center items-center py-20"
            >
                <svg
                    width="60"
                    height="60"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="#008236"
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
                <!-- Header (unchanged as requested) -->
                <div class="px-8 pb-4">
                    <h2 class="text-2xl font-bold text-center">
                        IMPORT/EXPORT BEGINNING BALANCE
                    </h2>
                    <div
                        class="mt-2 h-0.5 bg-gradient-to-r from-transparent via-green-600/50 to-transparent"
                    ></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 px-4">
                    <!-- Import File Section - Modern Redesign -->
                    <div class="flex flex-col items-center">
                        <label
                            class="group relative w-full h-48 rounded-2xl p-1 border-2 border-dashed border-gray-400/80 hover:border-green-400 transition-all duration-300 shadow-md hover:shadow-green-500/20 bg-white/5 backdrop-blur-sm flex flex-col items-center justify-center cursor-pointer overflow-hidden"
                        >
                            <div
                                class="relative z-10 flex flex-col items-center justify-center p-4 text-center"
                            >
                                <div
                                    class="mb-3 p-3 rounded-full bg-green-500/10 group-hover:bg-green-500/20 transition-all"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-8 w-8 text-green-400 group-hover:text-green-300 transition-colors"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M9 8.25H7.5a2.25 2.25 0 00-2.25 2.25v9a2.25 2.25 0 002.25 2.25h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25H15m0-3l-3-3m0 0l-3 3m3-3V15"
                                        />
                                    </svg>
                                </div>
                                <span
                                    class="mt-1 text-sm font-medium text-gray-300 group-hover:text-green-200 transition-colors truncate max-w-full px-4"
                                >
                                    {{
                                        importFile
                                            ? importFile.name
                                            : "Select or drag Excel file"
                                    }}
                                </span>
                                <span class="text-xs text-gray-400/80 mt-1"
                                    >.xlsx, .xls</span
                                >
                            </div>
                            <input
                                type="file"
                                @change="handleImportFileChange"
                                accept=".xlsx, .xls, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                                class="hidden"
                            />
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-transparent to-green-900/10 opacity-0 group-hover:opacity-100 transition-opacity"
                            ></div>
                        </label>
                        <p class="mt-3 text-center font-medium text-white/90">
                            Import Excel File
                        </p>
                    </div>

                    <!-- Export File Section - Modern Redesign -->
                    <div class="flex flex-col items-center">
                        <button
                            type="button"
                            @click="exportTemplate"
                            class="group relative w-full h-48 rounded-2xl p-1 border-2 border-dashed border-gray-400/80 hover:border-green-400 transition-all duration-300 shadow-md hover:shadow-green-500/20 bg-white/5 backdrop-blur-sm flex flex-col items-center justify-center cursor-pointer overflow-hidden"
                        >
                            <div
                                class="relative z-10 flex flex-col items-center justify-center p-4 text-center"
                            >
                                <div
                                    class="mb-3 p-3 rounded-full bg-green-500/10 group-hover:bg-green-500/20 transition-all"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-8 w-8 text-green-400 group-hover:text-green-300 transition-colors"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"
                                        />
                                    </svg>
                                </div>
                                <span
                                    class="mt-1 text-sm font-medium text-gray-300 group-hover:text-green-200 transition-colors"
                                >
                                    Download Template
                                </span>
                                <span class="text-xs text-gray-400/80 mt-1"
                                    >Pre-formatted Excel</span
                                >
                            </div>
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-transparent to-green-900/10 opacity-0 group-hover:opacity-100 transition-opacity"
                            ></div>
                        </button>
                        <p class="mt-3 text-center font-medium text-white/90">
                            Export Excel Template
                        </p>
                    </div>
                </div>

                <!-- Footer (unchanged as requested) -->
                <div
                    class="flex justify-end gap-4 pt-2 border-t border-white/10 mt-4"
                >
                    <button
                        type="submit"
                        class="submitButton"
                        :disabled="form.processing || !importFile"
                    >
                        <span v-if="form.processing">Processing...</span>
                        <span v-else>Import Data</span>
                    </button>
                    <button
                        type="button"
                        @click="closeModal"
                        class="closeButton"
                    >
                        Close
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import ToastAlertWarning from "../../Pages/Components/ToastAlertWarning.vue";
// import { exportExcel } from "@/Utils/ExportExcel"; // You'll need to create this utility
// import { saveAs } from "file-saver";
// import * as XLSX from "xlsx";

const props = defineProps({
    show: Boolean,
});

const modalLoading = ref(false);
const importFile = ref(null);

const emit = defineEmits(["close", "closeSuccess"]);

const handleImportFileChange = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Validate file type
    const validTypes = [
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        "application/vnd.ms-excel",
        ".xlsx",
        ".xls",
    ];

    const fileExtension = file.name.split(".").pop().toLowerCase();
    const isValidType =
        validTypes.includes(file.type) ||
        [".xlsx", ".xls"].includes(`.${fileExtension}`);

    if (!isValidType) {
        showWarningToast("Please upload a valid Excel file (.xlsx or .xls)");
        event.target.value = ""; // Reset input
        importFile.value = null;
        return;
    }

    importFile.value = file;
};

const exportTemplate = () => {
    // Define the template data structure matching your example
    const templateData = [
        ["Customer Code", "As of Date", "Amount"],
        ["AGC-00066", "2025-05-26", "2400"],
    ];

    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(templateData);
    XLSX.utils.book_append_sheet(wb, ws, "Sheet1");

    // Generate Excel file
    const excelBuffer = XLSX.write(wb, { bookType: "xlsx", type: "array" });
    const data = new Blob([excelBuffer], {
        type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    });

    // Save the file using FileSaver.js
    saveAs(data, "BeginningBalanceTemplate.xlsx");
};

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

const closeModal = () => {
    importFile.value = null;
    emit("close");
};

const form = useForm({
    importFile: null,
});

const submit = async () => {
    if (!importFile.value) {
        showWarningToast("Please select an Excel file to import");
        return;
    }

    form.importFile = importFile.value;

    try {
        modalLoading.value = true;
        await form.post(route("beginning-balance.import"), {
            preserveScroll: true,
            onSuccess: () => {
                emit("closeSuccess");
                closeModal();
            },
            onError: (errors) => {
                showWarningToast(
                    errors.importFile || "An error occurred during import"
                );
            },
        });
    } finally {
        modalLoading.value = false;
    }
};
</script>
