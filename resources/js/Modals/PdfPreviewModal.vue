<template>
    <div class="fixed inset-0 z-60 flex items-center justify-center">
        <ToastAlert :show="showToast" :message="toastMessage" />
        <ToastAlertWarning :show="showWToast" :message="toastWMessage" />
        <!-- Modal container -->
        <div class="relative w-full h-full p-4">
            <!-- Loading state with progress -->
            <div v-if="loading"
                class="fixed inset-0 bg-opacity-50 backdrop-blur-sm flex items-center justify-center p-4 z-50">
                <div
                    class="w-full max-w-md bg-[var(--color-bg-secondary)] border border-[var(--color-border)] rounded-2xl shadow-xl overflow-hidden transition-all duration-300">
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-[var(--color-text-primary])]">
                                {{ progressMessage }}
                            </h3>
                            <span class="text-lg font-medium text-[var(--color-primary)]">
                                {{ progress }}%
                            </span>
                        </div>

                        <!-- Animated progress bar -->
                        <div class="relative h-2.5 bg-[var(--color-bg-primary)] rounded-full overflow-hidden">
                            <div class="absolute top-0 left-0 h-full bg-gradient-to-r from-[var(--color-primary)] to-[var(--color-primary-hover)] rounded-full transition-all duration-500 ease-out"
                                :style="{ width: progress + '%' }">
                                <div class="absolute inset-0 opacity-30 animate-pulse"></div>
                            </div>
                        </div>

                        <!-- Animated dots for visual interest -->
                        <div class="flex justify-center space-x-1.5 pt-1">
                            <div v-for="i in 3" :key="i"
                                class="w-2 h-2 bg-[var(--color-primary)] rounded-full animate-bounce"
                                :style="`animation-delay: ${i * 0.15}s`"></div>
                        </div>

                        <p class="text-center text-sm text-[var(--color-text-secondary)] font-medium">
                            Generating your PDF document...
                        </p>
                    </div>

                    <!-- Subtle animated border at bottom -->
                    <div
                        class="h-1 bg-gradient-to-r from-transparent via-[var(--color-primary)] to-transparent animate-[pulse_2s_infinite]">
                    </div>
                </div>
            </div>
            <!-- Modal content -->
            <div v-else
                class="relative flex flex-col h-full w-full bg-[var(--color-bg-secondary)] rounded-lg p-6 shadow-xl overflow-hidden">
                <!-- Modal header -->
                <div class="text-center">
                    <h2 class="text-2xl font-bold text-[var(--color-text-primary)] tracking-wide">
                        DOCUMENT PREVIEW
                    </h2>
                    <div class="mt-2 h-0.5 bg-gradient-to-r from-transparent via-[var(--color-border)] to-transparent">
                    </div>
                </div>

                <!-- Modal body -->
                <div class="flex-1 overflow-auto p-4">
                    <!-- PDF preview -->
                    <iframe v-if="pdfUrl && !loading" :src="pdfUrl"
                        class="w-full h-full min-h-[500px] border border-[var(--color-border)] rounded-md"
                        frameborder="0"></iframe>

                    <!-- Error state -->
                    <div v-if="error" class="flex flex-col justify-center items-center h-full text-red-500 py-8">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-red-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <p class="mt-2">Failed To Load Document Preview.</p>
                        <p class="text-sm text-[var(--color-text-primary)]">
                            {{ error }}
                        </p>
                        <button @click="retryFetch"
                            class="mt-4 px-4 py-2 bg-[var(--color-primary)] text-white rounded hover:bg-[var(--color-primary-dark)] transition">
                            Retry
                        </button>
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="mt-4 pt-2 border-t gap-2 border-[var(--color-border)] flex justify-end">
                    <button v-if="!loading" type="button" @click="closeModal" class="closeButton group">
                        <div class="flex justify-center items-center gap-2">
                            <span class="transition-transform duration-300 group-hover:rotate-180">
                                <svg-icon type="mdi" :path="mdiClose" class="w-5 h-5" />
                            </span>
                            Close
                        </div>
                    </button>
                    <button v-if="!loading" type="button" @click="printPdf()" class="submitButton group"
                        :disabled="!pdfUrl || loading">
                        <div class="flex justify-center items-center gap-2">
                            <span class="transition-transform duration-300 group-hover:rotate-360">
                                <svg-icon type="mdi" :path="mdiPrinterOutline" class="w-5 h-5" />
                            </span>
                            <span v-if="!pdfUrl">Printing...</span>
                            <span v-else>Print</span>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { mdiClose, mdiPrinterOutline } from "@mdi/js";
import { ref, watch, onMounted, onUnmounted } from "vue";
import { route } from "../../../vendor/tightenco/ziggy/src/js";
import { usePage } from "@inertiajs/vue3";
import { saveAs } from "file-saver"; // Import file-saver
import ExcelJS from "exceljs";
import ToastAlert from "../Pages/Components/ToastAlert.vue";
import ToastAlertWarning from "../Pages/Components/ToastAlertWarning.vue";

const props = defineProps({
    show: Boolean,
    formData: {
        type: Object,
        required: true,
    },
    apiEndpoint: {
        type: String,
    },
});

const emit = defineEmits(["close", "closeSuccess"]);

const pdfUrl = ref(null);
const loading = ref(false);
const error = ref(null);
const progress = ref(0);
const progressMessage = ref("Starting PDF generation...");
const channel = ref(null);
let echo = null;
const userId = ref(null);
const page = usePage();
let channelInstance = null;
const pathDelete = ref(null);

//WARNING TOAST
const showToast = ref(false);
const toastMessage = ref("");
const showWToast = ref(false);
const toastWMessage = ref("");

let toastTimeout = null;
let toastWTimeout = null;
const showWarningToast = (message) => {
    toastWMessage.value = message;
    showWToast.value = false;
    if (toastWTimeout) clearTimeout(toastWTimeout);

    setTimeout(() => {
        showWToast.value = true;
    }, 0);

    toastWTimeout = setTimeout(() => {
        showWToast.value = false;
        toastWTimeout = null;
    }, 3000);
};

const showSuccessToast = (message) => {
    toastMessage.value = message;
    showToast.value = true;

    if (toastTimeout) clearTimeout(toastTimeout);

    setTimeout(() => {
        showToast.value = true;
    }, 0);

    toastTimeout = setTimeout(() => {
        showToast.value = false;
        toastTimeout = null;
    }, 3000);
};

const closeModal = async () => {
    if (channelInstance) {
        window.Echo.leave(channel.value);
        channelInstance = null;
    }

    await deletePdf();

    loading.value = false;
    error.value = null;
    progress.value = 0;
    progressMessage.value = "Starting PDF generation...";
    emit("close");
};

const retryFetch = () => {
    error.value = null;
    startPdfGeneration();
};

const startPdfGeneration = async () => {
    try {
        loading.value = true;
        error.value = null;
        progress.value = 0;
        progressMessage.value = "Starting PDF generation...";

        const response = await axios.post(route(props.apiEndpoint), {
            ...props.formData,
        });

        if (response.data.channel) {
            channel.value = response.data.channel;
            setupWebSocketListener();
        } else {
            throw new Error("Failed to start PDF generation");
        }
    } catch (err) {
        console.error("Error starting PDF generation:", err);
        error.value =
            err.response?.data?.message ||
            err.message ||
            "Failed to start PDF generation";
        loading.value = false;
    }
};

const setupWebSocketListener = () => {
    if (!channel.value) {
        error.value = "No channel provided by server";
        return;
    }

    if (channelInstance) {
        echo.leave(channel.value);
        channelInstance = null;
    }

    if (channel.value.startsWith(`transaction-pdf-generation.${userId.value}`)) {
        channelInstance = window.Echo.private(channel.value)
            .listen(".TransactionPdfGenerationProgress", (data) => {
                progress.value = data.progress;
                progressMessage.value = data.message;
                error.value = null;
            })
            .listen(".TransactionPdfGenerated", (data) => {
                loading.value = false;
                if (!data.path) {
                    error.value = "No PDF path provided.";
                    return;
                }
                pathDelete.value = data.path;
                fetch(data.path)
                    .then((res) => {
                        if (!res.ok) throw new Error("PDF fetch failed");
                        return res.blob();
                    })
                    .then((blob) => {
                        pdfUrl.value = URL.createObjectURL(blob);
                    })
                    .catch((err) => {
                        console.error("PDF fetch error:", err);
                        error.value = "Failed to load PDF.";
                    });
            })
            .listen("TransactionPdfGenerationError", (errorData) => {
                console.error("Generation error:", errorData);
                error.value = errorData.message || "PDF generation failed";
                loading.value = false;
            })
            .error((err) => {
                console.error("WebSocket error:", err);
                error.value = "Connection lost. Please retry.";
                loading.value = false;
            });
    } else {
        channelInstance = window.Echo.private(channel.value)
            .listen("PdfGenerationProgress", (data) => {
                progress.value = data.progress;
                progressMessage.value = data.message;
                error.value = null;
            })
            .listen("PdfGenerated", async (data) => {
                loading.value = false;
                try {
                    if (data.excelData) {
                        await generateAROutstandingExcelFile(data.excelData);
                        emit("closeSuccess");
                    } else if (data.path) {
                        if (!data.path) {
                            error.value = "No PDF path provided.";
                            return;
                        }
                        pathDelete.value = data.path;
                        fetch(data.path)
                            .then((res) => {
                                if (!res.ok)
                                    throw new Error("PDF fetch failed");
                                return res.blob();
                            })
                            .then((blob) => {
                                pdfUrl.value = URL.createObjectURL(blob);
                            })
                            .catch((err) => {
                                console.error("PDF fetch error:", err);
                                error.value = "Failed to load PDF.";
                            });
                    } else {
                        error.value = "No data or path provided.";
                        return;
                    }
                } catch (err) {
                    console.error("Excel generation error:", err);
                    error.value = "Failed to generate Excel file.";
                }
            })
            .listen("PdfGenerationError", (errorData) => {
                console.error("Generation error:", errorData);
                error.value = errorData.message || "PDF generation failed";
                loading.value = false;
            })
            .error((err) => {
                console.error("WebSocket error:", err);
                error.value = "Connection lost. Please retry.";
                loading.value = false;
            });
    }
};

const generateAROutstandingExcelFile = async (excelData) => {
    try {
        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet("AR Outstanding Balance");

        let currentRow = 1;

        // Top-right information
        worksheet.getCell(
            "K1"
        ).value = `Run Date/Time: ${excelData.runDateTime}`;
        worksheet.getCell("K2").value =
            "Note: This document is not valid without complete signatory.";
        worksheet.getCell("K1").font = { size: 9 };
        worksheet.getCell("K2").font = { size: 9, color: { argb: "e74c3c" } };
        worksheet.getCell("K1").alignment = { horizontal: "right" };
        worksheet.getCell("K2").alignment = { horizontal: "right" };

        // Header section
        currentRow = 4;
        worksheet.getCell(`A${currentRow}`).value = "BRDR BIL-AG003";
        worksheet.getCell(`A${currentRow}`).font = { bold: true, size: 16 };

        currentRow++;
        worksheet.getCell(`A${currentRow}`).value =
            "Accounts Receivable System - Local";
        worksheet.getCell(`A${currentRow}`).font = { size: 12 };

        currentRow++;
        const label1 = excelData.dateRange.includes("to") ? "DR" : "AO";
        worksheet.getCell(
            `A${currentRow}`
        ).value = `AR Outstanding Balances (${label1})`;
        worksheet.getCell(`A${currentRow}`).font = { size: 12 };

        currentRow += 2;
        const label2 = excelData.dateRange.includes("to")
            ? "Date Range:"
            : "As of Date:";
        worksheet.getCell(
            `A${currentRow}`
        ).value = `${label2} ${excelData.dateRange}`;
        worksheet.getCell(`A${currentRow}`).font = { bold: true };

        currentRow += 2;

        // Process grouped data
        excelData.groupedData.forEach((group, groupIndex) => {
            // Customer header
            worksheet.getCell(
                `A${currentRow}`
            ).value = `${group.customer_code} ${group.customer_name}`;
            worksheet.getCell(`A${currentRow}`).font = { bold: true };
            worksheet.mergeCells(`A${currentRow}:K${currentRow}`);

            currentRow++;

            // Table headers
            const headers = [
                "DOC. NO",
                "DOC. TYPE",
                "RECEIPT DATE",
                "GROSS AMOUNT",
                "S/O",
                "RETURN",
                "ADJUSTMENT",
                "PARTIAL PAYMENT",
                "FLOATING PDC/DC",
                "FLOATING WHT",
                "AR NET AMOUNT",
            ];

            const headerRow = worksheet.getRow(currentRow);
            headers.forEach((header, index) => {
                const cell = headerRow.getCell(index + 1);
                cell.value = header;

                // Apply styling to each cell individually
                cell.font = { bold: true, size: 10 };
                cell.alignment = { horizontal: "center", vertical: "middle" };
                cell.border = {
                    top: { style: "thin" },
                    left: { style: "thin" },
                    bottom: { style: "thin" },
                    right: { style: "thin" },
                };
                cell.fill = {
                    type: "pattern",
                    pattern: "solid",
                    fgColor: { argb: "f0f0f0" },
                };
            });
            // headers.forEach((header, index) => {
            //     headerRow.getCell(index + 1).value = header;
            // });

            // Style header row
            // headerRow.eachCell((cell) => {
            //     cell.font = { bold: true, size: 10 };
            //     cell.alignment = { horizontal: "center", vertical: "middle" };
            //     cell.border = {
            //         top: { style: "thin" },
            //         left: { style: "thin" },
            //         bottom: { style: "thin" },
            //         right: { style: "thin" },
            //     };
            //     cell.fill = {
            //         type: "pattern",
            //         pattern: "solid",
            //         fgColor: { argb: "f0f0f0" },
            //     };
            // });

            currentRow++;

            // Data rows for this customer
            group.outstandingBalances.forEach((balance) => {
                const dataRow = worksheet.getRow(currentRow);

                dataRow.getCell(1).value = balance.document_no;
                dataRow.getCell(2).value = balance.type;
                dataRow.getCell(3).value = new Date(balance.receipt_date);
                dataRow.getCell(4).value =
                    parseFloat(balance.gross_amount) || 0;
                dataRow.getCell(5).value =
                    parseFloat(balance.shrinkage_overage) || 0;
                dataRow.getCell(6).value = parseFloat(balance.return) || 0;
                dataRow.getCell(7).value = parseFloat(balance.adjustment) || 0;
                dataRow.getCell(8).value =
                    parseFloat(balance.partial_payment) || 0;
                dataRow.getCell(9).value =
                    parseFloat(balance.floating_pdc_dc) || 0;
                dataRow.getCell(10).value =
                    parseFloat(balance.floating_wht) || 0;
                dataRow.getCell(11).value =
                    parseFloat(balance.ar_net_amount) || 0;

                // Format cells
                dataRow.getCell(1).alignment = { horizontal: "center" };
                dataRow.getCell(2).alignment = { horizontal: "center" };
                dataRow.getCell(3).alignment = { horizontal: "center" };
                dataRow.getCell(3).numFmt = "mm/dd/yyyy";

                // Format currency columns (4-11)
                for (let col = 4; col <= 11; col++) {
                    dataRow.getCell(col).numFmt = "#,##0.00";
                    dataRow.getCell(col).alignment = { horizontal: "right" };
                }

                // Add thin borders
                dataRow.eachCell((cell) => {
                    cell.border = {
                        top: { style: "thin" },
                        left: { style: "thin" },
                        bottom: { style: "thin" },
                        right: { style: "thin" },
                    };
                });

                currentRow++;
            });

            // Subtotal row
            const subtotalRow = worksheet.getRow(currentRow);
            subtotalRow.getCell(10).value = "Sub Total :";
            subtotalRow.getCell(11).value =
                parseFloat(group.customerAmountTotal) || 0;

            // Style subtotal row
            subtotalRow.getCell(10).font = { bold: true };
            subtotalRow.getCell(10).alignment = { horizontal: "right" };
            subtotalRow.getCell(11).font = { bold: true };
            subtotalRow.getCell(11).numFmt = "#,##0.00";
            subtotalRow.getCell(11).alignment = { horizontal: "right" };

            // Add top border to subtotal
            subtotalRow.eachCell((cell, colNumber) => {
                if (colNumber >= 10) {
                    cell.border = {
                        top: { style: "thin" },
                        left: { style: "thin" },
                        bottom: { style: "thin" },
                        right: { style: "thin" },
                    };
                }
            });

            currentRow += 2; // Add space between customer groups
        });

        // Overall total
        const totalRow = worksheet.getRow(currentRow);
        totalRow.getCell(10).value = "Total Amount:";
        totalRow.getCell(11).value =
            parseFloat(excelData.customerOverallAmountTotal) || 0;

        totalRow.getCell(10).font = { bold: true, size: 12 };
        totalRow.getCell(10).alignment = { horizontal: "right" };
        totalRow.getCell(11).font = { bold: true, size: 12 };
        totalRow.getCell(11).numFmt = "#,##0.00";
        totalRow.getCell(11).alignment = { horizontal: "right" };

        // Add borders to total row
        totalRow.eachCell((cell, colNumber) => {
            if (colNumber >= 10) {
                cell.border = {
                    top: { style: "thick" },
                    left: { style: "thin" },
                    bottom: { style: "thick" },
                    right: { style: "thin" },
                };
            }
        });

        currentRow += 3;

        // Signatory section
        const signatoryStartRow = currentRow;

        // Headers for signatory section
        worksheet.getCell(`A${currentRow}`).value = "Prepared By:";
        worksheet.getCell(`E${currentRow}`).value = "Checked By:";
        worksheet.getCell(`I${currentRow}`).value = "Note By:";

        ["A", "E", "I"].forEach((col) => {
            worksheet.getCell(`${col}${currentRow}`).font = { bold: true };
        });

        currentRow += 2;

        // Prepared By section
        worksheet.getCell(`A${currentRow}`).value = excelData.preparedBy;
        worksheet.getCell(`A${currentRow}`).border = {
            bottom: { style: "thin" },
        };
        worksheet.mergeCells(`A${currentRow}:D${currentRow}`);
        worksheet.getCell(`A${currentRow}`).alignment = {
            horizontal: "center",
        };

        currentRow++;
        worksheet.getCell(`A${currentRow}`).value =
            "(Signature Over Printed Name)";
        worksheet.getCell(`A${currentRow}`).alignment = {
            horizontal: "center",
        };
        worksheet.getCell(`A${currentRow}`).font = { size: 9 };
        worksheet.mergeCells(`A${currentRow}:D${currentRow}`);

        currentRow++;
        worksheet.getCell(`A${currentRow}`).value = "Date:";
        worksheet.getCell(`B${currentRow}`).value =
            new Date().toLocaleDateString();
        worksheet.getCell(`B${currentRow}`).border = {
            bottom: { style: "thin" },
        };
        worksheet.getCell(`B${currentRow}`).alignment = {
            horizontal: "center",
        };

        currentRow++;
        worksheet.getCell(`A${currentRow}`).value = "Time:";
        worksheet.getCell(`B${currentRow}`).value =
            new Date().toLocaleTimeString();
        worksheet.getCell(`B${currentRow}`).border = {
            bottom: { style: "thin" },
        };
        worksheet.getCell(`B${currentRow}`).alignment = {
            horizontal: "center",
        };

        currentRow++;
        worksheet.getCell(`A${currentRow}`).value = "Designation:";
        worksheet.getCell(`B${currentRow}`).border = {
            bottom: { style: "thin" },
        };
        worksheet.mergeCells(`B${currentRow}:D${currentRow}`);

        // Add similar structure for "Checked By" and "Note By" columns
        const checkByRow = signatoryStartRow + 2;

        // Checked By and Note By sections (simplified)
        ["E", "I"].forEach((col) => {
            worksheet.getCell(`${col}${checkByRow}`).border = {
                bottom: { style: "thin" },
            };
            worksheet.mergeCells(
                `${col}${checkByRow}:${String.fromCharCode(
                    col.charCodeAt(0) + 3
                )}${checkByRow}`
            );

            worksheet.getCell(`${col}${checkByRow + 1}`).value =
                "(Signature Over Printed Name)";
            worksheet.getCell(`${col}${checkByRow + 1}`).alignment = {
                horizontal: "center",
            };
            worksheet.getCell(`${col}${checkByRow + 1}`).font = { size: 9 };
            worksheet.mergeCells(
                `${col}${checkByRow + 1}:${String.fromCharCode(
                    col.charCodeAt(0) + 3
                )}${checkByRow + 1}`
            );

            worksheet.getCell(`${col}${checkByRow + 2}`).value = "Date:";
            worksheet.getCell(
                `${String.fromCharCode(col.charCodeAt(0) + 1)}${checkByRow + 2}`
            ).border = { bottom: { style: "thin" } };

            worksheet.getCell(`${col}${checkByRow + 3}`).value = "Time:";
            worksheet.getCell(
                `${String.fromCharCode(col.charCodeAt(0) + 1)}${checkByRow + 3}`
            ).border = { bottom: { style: "thin" } };

            worksheet.getCell(`${col}${checkByRow + 4}`).value = "Designation:";
            worksheet.getCell(
                `${String.fromCharCode(col.charCodeAt(0) + 1)}${checkByRow + 4}`
            ).border = { bottom: { style: "thin" } };
            worksheet.mergeCells(
                `${String.fromCharCode(col.charCodeAt(0) + 1)}${checkByRow + 4
                }:${String.fromCharCode(col.charCodeAt(0) + 3)}${checkByRow + 4
                }`
            );
        });

        // Set column widths
        worksheet.columns = [
            { width: 12 }, // DOC. NO
            { width: 12 }, // DOC. TYPE
            { width: 12 }, // RECEIPT DATE
            { width: 15 }, // GROSS AMOUNT
            { width: 10 }, // S/O
            { width: 10 }, // RETURN
            { width: 12 }, // ADJUSTMENT
            { width: 15 }, // PARTIAL PAYMENT
            { width: 15 }, // FLOATING PDC/DC
            { width: 12 }, // FLOATING WHT
            { width: 15 }, // AR NET AMOUNT
        ];

        // Generate filename
        const currentDate = new Date().toISOString().split("T")[0];
        const filename = `AR_Outstanding_Balance_${currentDate}.xlsx`;

        // Generate blob and download
        const buffer = await workbook.xlsx.writeBuffer();
        const blob = new Blob([buffer], {
            type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        });

        saveAs(blob, filename);

        showSuccessToast(
            `AR Outstanding Balance report exported successfully!`
        );
    } catch (error) {
        console.error("Error generating AR Outstanding Excel file:", error);
        showWarningToast(
            "Failed to generate AR Outstanding Excel file. Please try again."
        );
        throw error;
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

watch(
    () => props.show,
    (isShowing) => {
        if (isShowing) {
            startPdfGeneration();
        } else {
            if (echo) {
                echo.leave(channel.value);
            }
        }
    },
    { immediate: true }
);

const printPdf = async () => {
    if (!pdfUrl.value) return;

    const iframe = document.createElement("iframe");
    iframe.style.display = "none";
    document.body.appendChild(iframe);

    try {
        iframe.src = pdfUrl.value;

        await new Promise((resolve) => {
            iframe.onload = resolve;
        });

        let dialogClosed = false;
        const dialogCheckInterval = 500; // ms
        let printAttempted = false;

        const printPromise = new Promise((resolve) => {
            const handleAfterPrint = () => {
                if (printAttempted) {
                    dialogClosed = true;
                    window.removeEventListener("afterprint", handleAfterPrint);
                    resolve();
                }
            };

            const handleFocus = () => {
                if (printAttempted && !dialogClosed) {
                    dialogClosed = true;
                    window.removeEventListener("focus", handleFocus);
                    resolve();
                }
            };

            const fallbackTimeout = setTimeout(() => {
                if (!dialogClosed) {
                    dialogClosed = true;
                    window.removeEventListener("afterprint", handleAfterPrint);
                    window.removeEventListener("focus", handleFocus);
                    resolve();
                }
            }, 10000);

            window.addEventListener("afterprint", handleAfterPrint);
            window.addEventListener("focus", handleFocus);

            const originalPrint = iframe.contentWindow.print;
            iframe.contentWindow.print = function () {
                printAttempted = true;
                originalPrint.apply(this, arguments);
            };

            setTimeout(() => {
                iframe.contentWindow?.print();
            }, 100);

            Promise.resolve().then(() => {
                clearTimeout(fallbackTimeout);
            });
        });

        await printPromise;
        emit("closeSuccess");
    } catch (error) {
        console.error("Print error:", error);
        emit("printError", error.message);
    } finally {
        iframe.remove();
        await deletePdf();
    }
};

const deletePdf = async () => {
    if (!pathDelete.value) return;

    try {
        await axios.delete(route("pdf.delete"), {
            data: { path: pathDelete.value.split("/storage/")[1] },
        });
    } catch (err) {
        console.warn("Failed to delete PDF:", err);
    } finally {
        URL.revokeObjectURL(pdfUrl.value);
        pdfUrl.value = null;
    }
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
    transition: all 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
    transform: translateY(-20px);
    opacity: 0;
}
</style>
