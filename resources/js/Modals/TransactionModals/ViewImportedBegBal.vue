<template>
    <Transition name="modal">
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-sm bg-black/60"
        >
            <!-- Modal Container -->
            <div
                class="w-full max-w-5xl overflow-hidden rounded-2xl bg-[rgb(15,42,29)] shadow-2xl shadow-[#131313a2] border border-[rgb(30,60,45)]"
            >
                <!-- Content -->
                <div class="p-6">
                    <!-- Header -->
                    <div class="mb-6 text-center">
                        <h2 class="text-2xl font-bold text-white tracking-wide">
                            ALL COMPUTED TRANSACTION
                        </h2>
                        <div
                            class="mt-2 h-0.5 bg-gradient-to-r from-transparent via-green-600/50 to-transparent"
                        ></div>
                    </div>

                    <!-- TABLE -->
                    <div
                        class="bg-[rgb(15,42,29)] rounded-xl shadow-lg shadow-[#131313a2] border border-[rgb(30,60,45)]"
                    >
                        <div
                            class="overflow-y-auto max-h-[410px] scrollbar-thin rounded-xl scrollbar-thumb-gray-500 scrollbar-track-[rgb(15, 42, 29)] scrollbar-stable [scrollbar-gutter:stable] scrollbar-thumb-rounded-full scrollbar-track-rounded-full"
                        >
                            <table
                                class="w-full bg-[rgb(15,42,29)] text-gray-100 text-sm rounded-xl"
                            >
                                <!-- Modern Header -->
                                <thead
                                    class="sticky top-0 z-10 rounded-t-xl bg-[rgb(15,42,29)]"
                                >
                                    <tr
                                        class="text-xs uppercase tracking-wider text-gray-300"
                                    >
                                        <th
                                            class="px-5 py-3.5 text-left w-[30%]"
                                        >
                                            Customer Code
                                        </th>
                                        <th
                                            class="px-5 py-3.5 text-left w-[40%]"
                                        >
                                            Customer Name
                                        </th>

                                        <th
                                            class="px-5 py-3.5 text-center w-[30%]"
                                        >
                                            Amount
                                        </th>
                                    </tr>
                                </thead>

                                <!-- Loading State -->
                                <tbody v-if="isLoading">
                                    <tr>
                                        <td
                                            colspan="5"
                                            class="text-center py-8"
                                        >
                                            <div
                                                class="flex justify-center items-center"
                                            >
                                                <svg
                                                    width="30"
                                                    height="30"
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
                                        </td>
                                    </tr>
                                </tbody>

                                <!-- Data Rows -->
                                <tbody
                                    v-else
                                    class="divide-y divide-[rgb(30,60,45)] rounded-xl"
                                >
                                    <tr
                                        v-for="(
                                            customer, index
                                        ) in allTransactionResults"
                                        :key="index"
                                        class="rounded-xl hover:bg-[rgb(30,60,45)]/60 transition-colors duration-150 group"
                                    >
                                        <td
                                            class="px-5 py-2 font-medium text-green-400/90"
                                        >
                                            {{ customer.customer_code }}
                                        </td>
                                        <td
                                            class="px-5 py-2 text-left font-medium"
                                        >
                                            {{ customer.customer_name }}
                                        </td>
                                        <td
                                            class="px-5 py-2 text-right font-medium"
                                        >
                                            {{
                                                formatCurrency(
                                                    customer.total_balance
                                                )
                                            }}
                                        </td>
                                    </tr>

                                    <!-- Empty State -->
                                    <tr
                                        v-if="
                                            allTransactionResults.length ===
                                                0 && !isLoading
                                        "
                                    >
                                        <td
                                            colspan="5"
                                            class="px-5 py-6 text-center"
                                        >
                                            <div
                                                class="flex flex-col items-center justify-center text-gray-400"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-10 w-10 mb-2"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                                    />
                                                </svg>
                                                <p class="font-medium">
                                                    No data found for this
                                                    customer
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="mt-4 pt-2 border-t border-white/10 flex justify-end gap-2"
                    >
                        <!-- <button
                            @click="submitSelected"
                            class="submitButton"
                        >
                            Select
                        </button> -->
                        <button
                            @click="closeModal"
                            class="closeButton"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref, watch } from "vue";
import { route } from "../../../../vendor/tightenco/ziggy/src/js";

const props = defineProps({
    show: Boolean,
    receipt_date: String,
});

const emit = defineEmits(["close"]);

const allTransactionResults = ref([]);
const isLoading = ref(false);

// Watch for changes in customer_code
watch(
    () => props.show,
    async (newVal) => {
        if (newVal) {
            try {
                isLoading.value = true;

                const response = await axios.get(
                    route("getCustomerBegBalList"),
                    {
                        params: {
                            date: props.receipt_date,
                        },
                    }
                );

                allTransactionResults.value = response.data.map((customer) => ({
                    customer_code: customer.customer_code,
                    customer_name: customer.customer_name,
                    total_balance: customer.total_balance,
                }));
            } catch (error) {
                console.error("Error fetching invoices:", error);
                allTransactionResults.value = [];
            } finally {
                isLoading.value = false;
            }
        } else {
            allTransactionResults.value = [];
        }
    },
    { immediate: true }
);

const formatDate = (dateString) => {
    const options = { year: "numeric", month: "short", day: "numeric" };
    return new Date(dateString).toLocaleDateString(undefined, options);
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(amount);
};

const closeModal = () => {
    emit("close");
};
</script>
<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
</style>
