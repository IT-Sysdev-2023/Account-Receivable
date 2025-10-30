<template>
    <div>
        <Head :title="` | Cancel Payment`" />
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <AddCancelPayment
                v-if="showModal"
                :show="showModal"
                @closeSuccess="closeSuccessModal()"
                @close="closeModal()"
            />
        </Transition>
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <ViewCancelPayment
                v-if="showViewModal"
                :show="showViewModal"
                :selected="selectedRow"
                @close="closeViewModal()"
            />
        </Transition>

        <ToastAlert :show="showToast" :message="toastMessage" />

        <div class="flex justify-between pb-3 pt-1">
            <button
                :disabled="!canInsert('0403-CNCLPY')"
                @click="openModal()"
                class="px-4 py-2 rounded-md font-medium transition-all duration-300 flex items-center justify-center gap-2 relative overflow-hidden bg-[var(--color-primary)] text-white hover:bg-transparent hover:text-[var(--color-primary)] hover:ring-1 hover:ring-[var(--color-primary)] disabled:opacity-70 disabled:cursor-not-allowed group"
            >
                <div class="relative flex items-center justify-center gap-1">
                    <span
                        class="transition-transform duration-300 group-hover:rotate-180"
                    >
                        <svg-icon type="mdi" :path="mdiPlus" class="w-5 h-5" />
                    </span>

                    Add New
                </div>
            </button>
            <div class="flex items-center gap-2 w-1/3">
                <div class="relative w-full">
                    <input
                        type="search"
                        id="Search"
                        v-model="search"
                        placeholder=" "
                        class="peer"
                        ref="searchInput"
                        autocomplete="off"
                    />
                    <button
                        v-if="search"
                        @click="clearSearch"
                        class="absolute top-1/2 right-2 transform -translate-y-1/2 text-[var(--color-text-primary)] hover:text-red-500"
                    >
                        <svg-icon
                            type="mdi"
                            :path="mdiClose"
                            class="w-5 h-5 hover:text-red-500"
                        />
                    </button>
                    <div
                        v-else
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-[var(--color-text-secondary)]"
                    >
                        <svg-icon type="mdi" :path="mdiMagnify" size="20" />
                    </div>
                    <label
                        for="Search"
                        class="absolute left-0 -top-2 rounded px-1 text-sm text-[var(--color-text-primary)] transition-all peer-placeholder-shown:top-2.5 peer-placeholder-shown:text-sm peer-placeholder-shown:text-[var(--color-text-primary)] peer-focus:-top-2 peer-focus:text-sm peer-focus:text-[var(--color-text-primary)] cursor-text"
                    >
                        Search Here ...
                    </label>
                </div>
            </div>
        </div>

        <div
            class="bg-[var(--color-bg-secondary)]/20 p-4 rounded-md shadow-[0_0_20px_var(--color-shadow)]/20 mt-4"
        >
            <table
                class="w-full text-sm text-[var(--color-text-primary)] rounded-xl overflow-hidden mb-2"
            >
                <thead class="sticky top-0 z-10">
                    <tr>
                        <th
                            class="px-3 py-2 w-[20%] text-left font-semibold tracking-wider"
                        >
                            DOCUMENT/PAYMENT NO
                        </th>
                        <th
                            class="px-3 py-2 w-[20%] text-left font-semibold tracking-wider"
                        >
                            CANCELLED DATE
                        </th>
                        <th
                            class="px-3 py-2 w-[30%] text-left font-semibold tracking-wider"
                        >
                            CUSTOMER NAME
                        </th>
                        <!-- <th
                            class="px-3 py-2 w-[20%] text-left font-semibold tracking-wider"
                        >
                            AMOUNT
                        </th> -->
                        <th
                            class="px-3 py-2 w-[10%] text-center font-semibold tracking-wider"
                        >
                            ACTION
                        </th>
                    </tr>
                </thead>
                <!-- Loading State -->
                <tbody v-if="isLoading">
                    <tr>
                        <td colspan="6" class="text-center py-8">
                            <div class="flex justify-center items-center">
                                <svg
                                    width="30"
                                    height="30"
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
                        </td>
                    </tr>
                </tbody>

                <!-- Modern Body -->
                <tbody v-else>
                    <tr
                        v-for="cancel_payment in cancel_payments.data"
                        :key="cancel_payment.id"
                        class="hover:bg-[var(--color-primary)]/20 transition-colors duration-150 group h-10"
                    >
                        <td class="px-3 py-1 font-medium">
                            {{ cancel_payment.document_no ? cancel_payment.document_no : cancel_payment.payment_no }}
                        </td>
                        <td class="px-3 py-1">
                            {{ formatDate(cancel_payment.created_at) }}
                        </td>
                        <td class="px-3 py-1 font-medium">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-full bg-[var(--color-bg-avatar)] flex items-center justify-center text-white"
                                >
                                    {{
                                        getFirstValidChar(
                                            cancel_payment.customer_name
                                        )
                                    }}
                                </div>
                                <div>
                                    <div class="font-medium">
                                        {{ cancel_payment.customer_name }}
                                    </div>
                                    <div
                                        class="text-xs text-[var(--color-text-secondary)] mt-0.5"
                                    >
                                        {{
                                            cancel_payment.customer_code ||
                                            "No Code"
                                        }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <!-- <td class="px-3 py-1">
                            {{ formatCurrency(cancel_payment.amount_paid) }}
                        </td> -->
                        <td class="px-3 py-1">
                            <div class="flex justify-center gap-2">
                                <button
                                    @click="openViewModal(cancel_payment)"
                                    class="p-1.5 cursor-pointer rounded-lg transition-all duration-200 bg-[var(--color-primary)]/30 hover:bg-[var(--color-primary)]/50 hover:shadow-lg group-hover:opacity-100"
                                >
                                    <svg-icon
                                        type="mdi"
                                        :path="mdiEye"
                                        class="w-4 h-4 text-[var(--color-primary)]"
                                    />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <!-- Empty State -->
                    <tr v-if="!isLoading && cancel_payments.data.length === 0">
                        <td colspan="6" class="px-5 py-6 text-center">
                            <div
                                class="flex flex-col items-center justify-center text-[var(--color-text-primary)]"
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
                                <p class="font-medium">No data found</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination Links -->
            <div v-if="isLoading || cancel_payments.data.length === 0" />
            <div v-else>
                <PaginationLinks :paginator="cancel_payments" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, computed, onMounted, onUnmounted } from "vue";
import PaginationLinks from "./Components/PaginationLinks.vue";
import { router } from "@inertiajs/vue3";
import { debounce } from "lodash";
import ToastAlert from "./Components/ToastAlert.vue";
import { mdiClose, mdiEye, mdiMagnify, mdiPlus } from "@mdi/js";
import AddCancelPayment from "../Modals/UtilityModals/AddCancelPayment.vue";
import ViewCancelPayment from "../Modals/UtilityModals/ViewCancelPayment.vue";
import { route } from "../../../vendor/tightenco/ziggy/src/js";
import usePermissions from "./Composables/usePermissions";

const props = defineProps({
    cancel_payments: Object,
    searchTerm: String,
    can: Object,
    broadcastChannel: String,
});

const { canInsert } = usePermissions();

const showModal = ref(false);
const showViewModal = ref(false);
const selectedRow = ref(null);
const showToast = ref(false);
const toastMessage = ref("");
const showDialog = ref(false);
const pendingDeleteID = ref(null);

const search = ref(props.searchTerm);

const showSuccessToast = (message) => {
    toastMessage.value = message;
    showToast.value = true;

    setTimeout(() => {
        showToast.value = false;
    }, 3000); // Show toast for 3 seconds
};

async function openModal() {
    showModal.value = true;
}
const closeModal = () => {
    showModal.value = false;
};
const closeSuccessModal = () => {
    showModal.value = false;
    showSuccessToast("Clearing Successful");
};

const openViewModal = (selected) => {
    selectedRow.value = selected;
    showViewModal.value = true;
};
const closeViewModal = () => {
    showViewModal.value = false; // Close the modal
};
const closeEditSuccessModal = () => {
    showViewModal.value = false; // Close the modal
    showSuccessToast("Cancelled Payment Successfully");
};

const deleteItem = async (adjust) => {
    pendingDeleteID.value = adjust;
    showDialog.value = true;
};

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

const getFirstValidChar = (name) => {
    if (!name) return "";

    const trimmedName = name.trim();
    for (let i = 0; i < trimmedName.length; i++) {
        if (trimmedName[i] !== " ") {
            return trimmedName[i].toUpperCase();
        }
    }
    return "";
};

const handleConfirm = async (confirmed) => {
    showDialog.value = false;
    if (confirmed && pendingDeleteID.value) {
        try {
            router.delete(route("deletePayment", pendingDeleteID.value), {
                onSuccess: () => {
                    showSuccessToast("Clearing has been deleted successfully");
                },
                onError: (errors) => {
                    console.error("Failed to delete Clearing:", errors);
                },
            });
        } catch (error) {
            console.error("Unexpected error deleting Clearing:", error);
        }
    }
    pendingDeleteID.value = null;
};

const searchInput = ref(null); // declare ref
const clearSearch = () => {
    search.value = ""; // Clear search input

    nextTick(() => {
        searchInput.value?.focus();
    });
};

const isLoading = ref(false);
const performSearch = debounce((q) => {
    const filters = {
        search: q,
    };

    router.get(route("cancelpayment"), filters, {
        preserveState: true,
        replace: true,
        onFinish: () => {
            isLoading.value = false;
        },
    });
}, 1000);
watch(search, (q) => {
    isLoading.value = true;
    performSearch(q);
});

onMounted(() => {
    try {
        window.Echo.channel(props.broadcastChannel)
            .listen(".cancel_payment.created", () => {
                if (!showModal.value) {
                    router.reload({
                        preserveState: true,
                        only: ["cancel_payments"],
                        onFinish: () => {},
                    });
                }
            })
            .error((error) => {
                console.error("Echo error:", error);
            });
    } catch (error) {
        console.error("Error initializing Echo:", error);
    }
});

onUnmounted(() => {
    window.Echo.leaveChannel(props.broadcastChannel);
});
</script>
