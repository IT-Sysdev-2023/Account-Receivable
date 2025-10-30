<template>
    <div class="relative w-full h-full pt-5">
        <!-- PDF Display with subtle border and shadow -->
        <div
            class="h-[calc(100vh-8rem)] w-full bg-white rounded-lg overflow-hidden shadow-sm"
        >
            <iframe
                v-if="!pdfFailed"
                :src="pdfUrl"
                class="w-full h-full border border-gray-100"
                title="AR System User Guide"
                frameborder="0"
                allowfullscreen
                @error="handlePdfError"
            ></iframe>
            <!-- Fallback for unsupported PDF -->
            <div
                v-else
                class="h-full w-full flex flex-col items-center justify-center p-6 bg-gray-50 text-center"
            >
                <svg
                    class="h-14 w-14 text-gray-400 mb-4"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                    />
                </svg>
                <p class="text-gray-600 mb-6 text-lg">
                    Your browser doesn't support embedded PDFs.
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const page = usePage();

const userRole = page.props.auth.user.role || null;

const pdfUrl = computed(() => {
    if (userRole === "Admin") {
        return "/storage/userguide/AR SYSTEM USER GUIDE.pdf";
    } else if (userRole === "Invoicing") {
        return "/storage/userguide/AR SYSTEM USER GUIDE (INVOICING CLERK).pdf";
    } else if (userRole === "Accounting") {
        return "/storage/userguide/AR SYSTEM USER GUIDE(ACCOUNTING CLERK).pdf";
    } else if (userRole === "Bookkeeper") {
        return "/storage/userguide/AR SYSTEM USER GUIDE (BOOKKEEPER).pdf";
    } else if (userRole === "IAD") {
        return "/storage/userguide/AR SYSTEM USER GUIDE(AUDIT).pdf";
    }
});

const pdfFailed = ref(false);

function handlePdfError() {
    pdfFailed.value = true;
}
</script>

<style scoped>
.pdf-iframe {
    transition: all 0.2s ease;
}
</style>
