<template>
    <div>
        <Head :title="` | ${$page.component}`" />
        <ToastAlert :show="showToast" :message="toastMessage" />
        <ToastAlertWarning :show="showWToast" :message="toastWMessage" />
        <div class="flex justify-between pb-3 pt-1">
            <button
                v-if="canUpdate('0101-CUST')"
                @click="syncCustomers"
                class="px-4 py-2 rounded-md font-medium transition-all duration-300 flex items-center justify-center gap-2 relative overflow-hidden bg-[var(--color-primary)] text-white hover:bg-transparent hover:text-[var(--color-primary)] hover:ring-1 hover:ring-[var(--color-primary)] disabled:opacity-70 disabled:cursor-not-allowed group"
            >
                <div class="relative flex items-center justify-center gap-1">
                    <span
                        class="transition-transform duration-300 group-hover:rotate-180"
                    >
                        <svg-icon
                            type="mdi"
                            :path="mdiCached"
                            class="w-5 h-5"
                        />
                    </span>

                    Get Updates
                </div>
            </button>
            <div v-else></div>
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
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            class="w-5 h-5"
                        >
                            <path
                                d="M6 18L18 6M6 6l12 12"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
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
                <!-- Filter Dropdown -->
                <div class="relative" ref="dropdownContainer">
                    <button
                        @click="toggleFilters"
                        class="px-4 py-2 rounded-md font-medium transition-all duration-300 flex items-center justify-center gap-2 relative overflow-hidden disabled:opacity-70 disabled:cursor-not-allowed group"
                        :class="{
                            'bg-transparent text-[var(--color-primary)] ring-1 ring-[var(--color-primary)]':
                                showFilters,
                            'bg-[var(--color-primary)] text-white hover:bg-transparent hover:text-[var(--color-primary)] hover:ring-1 hover:ring-[var(--color-primary)]':
                                !showFilters,
                        }"
                    >
                        <div
                            class="relative flex items-center justify-center gap-2"
                        >
                            <span
                                class="transition-transform duration-300"
                                :class="{
                                    'rotate-360': showFilters,
                                    'group-hover:rotate-360': !showFilters,
                                }"
                            >
                                <FunnelIcon class="w-5 h-5" />
                            </span>
                            <span>Filters</span>
                            <span class="h-5 w-5">
                                <span
                                    v-if="activeFiltersCount > 0"
                                    class="bg-[var(--color-primary-hover)] text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"
                                >
                                    {{ activeFiltersCount }}
                                </span>
                            </span>
                        </div>
                    </button>
                    <!-- Filter Panel -->
                    <Transition
                        enter-active-class="transition ease-out duration-100"
                        enter-from-class="transform opacity-0 scale-95"
                        enter-to-class="transform opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-75"
                        leave-from-class="transform opacity-100 scale-100"
                        leave-to-class="transform opacity-0 scale-95"
                    >
                        <div
                            v-if="showFilters"
                            class="absolute right-0 z-20 mt-2 w-64 bg-[var(--color-bg-secondary)] text-[var(--color-text-primary)] rounded-md shadow-lg shadow-[#131313a2] border border-[var(--color-border)] overflow-hidden"
                        >
                            <div class="p-4 space-y-4">
                                <!-- Code Sorting -->
                                <div>
                                    <h3 class="text-sm font-medium mb-2">
                                        Sort by Code
                                    </h3>
                                    <div class="flex gap-2">
                                        <button
                                            @click="setCodeSort('asc')"
                                            class="flex-1 py-1.5 px-3 text-xs rounded-md transition-colors text-white"
                                            :class="
                                                codeSort === 'asc'
                                                    ? 'bg-[var(--color-primary-hover)]'
                                                    : 'bg-[var(--color-primary)] hover:bg-[var(--color-primary-hover)]'
                                            "
                                        >
                                            A-Z
                                        </button>
                                        <button
                                            @click="setCodeSort('desc')"
                                            class="flex-1 py-1.5 px-3 text-xs rounded-md transition-colors text-white"
                                            :class="
                                                codeSort === 'desc'
                                                    ? 'bg-[var(--color-primary-hover)]'
                                                    : 'bg-[var(--color-primary)] hover:bg-[var(--color-primary-hover)]'
                                            "
                                        >
                                            Z-A
                                        </button>
                                        <button
                                            @click="setCodeSort(null)"
                                            class="p-1.5 px-2 text-xs rounded-md transition-colors font-semibold text-white"
                                            :class="
                                                !codeSort
                                                    ? 'bg-[var(--color-primary)] hover:bg-[var(--color-primary-hover)]'
                                                    : 'bg-[var(--color-primary-hover)]'
                                            "
                                            title="Clear"
                                        >
                                            <svg-icon
                                                type="mdi"
                                                :path="mdiClose"
                                                class="w-4 h-4"
                                            />
                                        </button>
                                    </div>
                                </div>

                                <!-- Type Filter -->
                                <div>
                                    <h3 class="text-sm font-medium mb-2">
                                        Customer Type
                                    </h3>
                                    <div class="space-y-2">
                                        <label
                                            class="flex items-center space-x-2"
                                        >
                                            <label
                                                class="relative inline-block w-4 h-4"
                                            >
                                                <input
                                                    type="checkbox"
                                                    v-model="typeFilters"
                                                    value="INTERNAL"
                                                    class="peer appearance-none w-4 h-4 border-2 rounded-sm border-[var(--color-border)] bg-transparent checked:bg-[var(--color-primary)] checked:!border-[var(--color-primary)] focus:outline-none transition-colors duration-200"
                                                />
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 24 24"
                                                    class="absolute p-0.5 top-0.5 left-0 right-0 bottom-0 w-4 h-4 text-[var(--color-bg-primary)] hidden peer-checked:block pointer-events-none"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        d="M9,20.42L2.79,14.21L5.62,11.38L9,14.77L18.88,4.88L21.71,7.71L9,20.42Z"
                                                    />
                                                </svg>
                                            </label>
                                            <span class="text-sm"
                                                >Internal</span
                                            >
                                        </label>
                                        <label
                                            class="flex items-center space-x-2"
                                        >
                                            <label
                                                class="relative inline-block w-4 h-4"
                                            >
                                                <input
                                                    type="checkbox"
                                                    v-model="typeFilters"
                                                    value="EXTERNAL"
                                                    class="peer appearance-none w-4 h-4 border-2 rounded-sm border-[var(--color-border)] bg-transparent checked:bg-[var(--color-primary)] checked:!border-[var(--color-primary)] focus:outline-none transition-colors duration-200"
                                                />
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 24 24"
                                                    class="absolute p-0.5 top-0.5 left-0 right-0 bottom-0 w-4 h-4 text-[var(--color-bg-primary)] hidden peer-checked:block pointer-events-none"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        d="M9,20.42L2.79,14.21L5.62,11.38L9,14.77L18.88,4.88L21.71,7.71L9,20.42Z"
                                                    />
                                                </svg>
                                            </label>
                                            <span class="text-sm"
                                                >External</span
                                            >
                                        </label>
                                    </div>
                                </div>

                                <!-- Status Filter -->
                                <div>
                                    <h3 class="text-sm font-medium mb-2">
                                        Status
                                    </h3>
                                    <div class="space-y-2">
                                        <label
                                            class="flex items-center space-x-2"
                                        >
                                            <label
                                                class="relative inline-block w-4 h-4"
                                            >
                                                <input
                                                    type="checkbox"
                                                    v-model="statusFilters"
                                                    value="Active"
                                                    class="peer appearance-none w-4 h-4 border-2 rounded-sm border-[var(--color-border)] bg-transparent checked:bg-[var(--color-primary)] checked:!border-[var(--color-primary)] focus:outline-none transition-colors duration-200"
                                                />
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 24 24"
                                                    class="absolute p-0.5 top-0.5 left-0 right-0 bottom-0 w-4 h-4 text-[var(--color-bg-primary)] hidden peer-checked:block pointer-events-none"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        d="M9,20.42L2.79,14.21L5.62,11.38L9,14.77L18.88,4.88L21.71,7.71L9,20.42Z"
                                                    />
                                                </svg>
                                            </label>
                                            <span class="text-sm">Active</span>
                                        </label>
                                        <label
                                            class="flex items-center space-x-2"
                                        >
                                            <label
                                                class="relative inline-block w-4 h-4"
                                            >
                                                <input
                                                    type="checkbox"
                                                    v-model="statusFilters"
                                                    value="Inactive"
                                                    class="peer appearance-none w-4 h-4 border-2 rounded-sm border-[var(--color-border)] bg-transparent checked:bg-[var(--color-primary)] checked:!border-[var(--color-primary)] focus:outline-none transition-colors duration-200"
                                                />
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 24 24"
                                                    class="absolute p-0.5 top-0.5 left-0 right-0 bottom-0 w-4 h-4 text-[var(--color-bg-primary)] hidden peer-checked:block pointer-events-none"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        d="M9,20.42L2.79,14.21L5.62,11.38L9,14.77L18.88,4.88L21.71,7.71L9,20.42Z"
                                                    />
                                                </svg>
                                            </label>
                                            <span class="text-sm"
                                                >Inactive</span
                                            >
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="bg-[var(--color-bg-secondary)] border-t border-[var(--color-border)] px-4 py-3 flex justify-between"
                            >
                                <button
                                    @click="resetFilters"
                                    class="text-xs font-semibold px-3 py-1 rounded hover:bg-green-600"
                                >
                                    Reset All
                                </button>
                                <button
                                    @click="applyFilters"
                                    class="text-xs font-semibold text-white bg-[var(--color-primary)] hover:bg-[var(--color-primary-hover)] px-3 py-1 rounded"
                                >
                                    Apply Filters
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>

        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <ViewCustomer
                v-if="showEditModal"
                :show="showEditModal"
                :selected="selectedCustomer"
                @close="closeEditModal"
            />
        </Transition>

        <div
            class="bg-[var(--color-bg-secondary)]/20 p-4 rounded-md shadow-[0_0_20px_var(--color-shadow)]/20 mt-4"
        >
            <table
                class="w-full text-sm text-[var(--color-text-primary)] rounded-xl overflow-hidden mb-2"
            >
                <!-- Modern Header -->
                <thead class="sticky top-0 z-10">
                    <tr>
                        <th
                            class="px-3 py-2 w-[10%] text-left font-semibold tracking-wider"
                        >
                            CODE
                        </th>
                        <th
                            class="px-3 py-2 w-[40%] text-left font-semibold tracking-wider"
                        >
                            CUSTOMER
                        </th>
                        <th
                            class="px-3 py-2 w-[20%] text-left font-semibold tracking-wider"
                        >
                            ADDRESS
                        </th>
                        <th
                            class="px-3 py-2 w-[10%] text-left font-semibold tracking-wider"
                        >
                            TYPE
                        </th>
                        <th
                            class="px-3 py-2 w-[10%] text-left font-semibold tracking-wider"
                        >
                            STATUS
                        </th>
                        <th
                            class="px-5 py-3.5 w-[10%] text-center font-semibold tracking-wider"
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
                        v-for="customer in customers.data"
                        :key="customer.id"
                        class="hover:bg-[var(--color-primary)]/20 transition-colors duration-150 group h-10"
                    >
                        <!-- Code -->
                        <td class="px-3 py-2">
                            <span class="font-medium">{{
                                customer.cus_code
                            }}</span>
                        </td>

                        <!-- Name -->
                        <td class="px-3 py-2">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-full bg-[var(--color-bg-avatar)] flex items-center justify-center text-white"
                                >
                                    {{ getFirstValidChar(customer.cus_name) }}
                                </div>
                                <div>
                                    <div class="font-medium">
                                        {{ customer.cus_name }}
                                    </div>
                                    <div
                                        class="text-xs text-[var(--color-text-secondary)] mt-0.5"
                                    >
                                        {{ customer.cus_tin || "No TIN" }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Address -->
                        <td class="px-3 py-2">
                            <div
                                class="line-clamp-2 text-[var(--color-text-primary)]"
                            >
                                {{ customer.cus_address }}
                            </div>
                        </td>

                        <!-- Type -->
                        <td class="px-3 py-2">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                :class="{
                                    'bg-red-700 text-red-300':
                                        customer.cus_type === 'EXTERNAL',
                                    'bg-cyan-700 text-cyan-300':
                                        customer.cus_type === 'INTERNAL',
                                }"
                            >
                                {{ customer.cus_type }}
                            </span>
                        </td>

                        <!-- Status -->
                        <td class="px-3 py-2">
                            <span
                                class="inline-flex items-center"
                                :title="customer.cus_status"
                            >
                                <span class="relative flex h-2.5 w-2.5 mr-2">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full"
                                        :class="
                                            customer.cus_status === 'Active'
                                                ? 'bg-green-400'
                                                : 'bg-red-400'
                                        "
                                    >
                                    </span>
                                    <span
                                        class="relative inline-flex rounded-full h-2.5 w-2.5"
                                        :class="
                                            customer.cus_status === 'Active'
                                                ? 'bg-green-500'
                                                : 'bg-red-500'
                                        "
                                    >
                                    </span>
                                </span>
                                <span class="capitalize">{{
                                    customer.cus_status
                                }}</span>
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="px-3 py-2 text-center">
                            <div class="flex justify-center gap-2">
                                <button
                                    @click="openEditModal(customer)"
                                    class="p-1.5 cursor-pointer rounded-lg transition-all duration-200 bg-[var(--color-primary)]/30 hover:bg-[var(--color-primary)]/50 hover:shadow-lg hover:shadow-[var(--color-primary)]/10 group-hover:opacity-100"
                                    title="View Details"
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
                    <tr v-if="!isLoading && customers.data.length === 0">
                        <td colspan="6" class="px-5 py-6 text-center">
                            <div
                                class="flex flex-col items-center justify-center text-[var(--color-text-primary)]"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-10 w-10 mb-2"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="var(--color-icon)"
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
            <div v-if="isLoading || customers.data.length === 0" />
            <div v-else>
                <PaginationLinks :paginator="customers" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from "vue";
import PaginationLinks from "./Components/PaginationLinks.vue";
import { router } from "@inertiajs/vue3";
import { debounce } from "lodash";
import ViewCustomer from "../Modals/MasterfileModals/ViewCustomer.vue";
import { FunnelIcon } from "@heroicons/vue/24/solid";
import ToastAlert from "./Components/ToastAlert.vue";
import ToastAlertWarning from "./Components/ToastAlertWarning.vue";
import SvgIcon from "@jamescoyle/vue-icon";
import { mdiCached, mdiClose, mdiMagnify, mdiEye } from "@mdi/js";
import { route } from "../../../vendor/tightenco/ziggy/src/js";
import usePermissions from "./Composables/usePermissions";

const props = defineProps({
    customers: Object,
    searchTerm: String,
    can: Object,
    filters: Object,
});

const { canUpdate } = usePermissions();

const showEditModal = ref(false);
const selectedCustomer = ref(null);

const openEditModal = (customer) => {
    selectedCustomer.value = customer;
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false; // Close the modal
};

const searchInput = ref(null); // declare ref
const clearSearch = () => {
    search.value = ""; // Clear search input

    nextTick(() => {
        searchInput.value?.focus();
    });
};

//SUCCESSFULL TOAST
const showToast = ref(false);
const toastMessage = ref("");
const showSuccessToast = (message) => {
    toastMessage.value = message;
    showToast.value = true;

    setTimeout(() => {
        showToast.value = false;
    }, 3000); // Show toast for 3 seconds
};

//WARNING TOAST
const showWToast = ref(false);
const toastWMessage = ref("");
let toastTimeout = null; // to keep track of the timeout

const showWarningToast = (message) => {
    toastWMessage.value = message;
    showWToast.value = false; // Hide first to trigger reactivity if the same toast shows again
    if (toastTimeout) clearTimeout(toastTimeout); // Clear any previous timeout

    // Trigger reactivity again on next tick
    setTimeout(() => {
        showWToast.value = true;
    }, 0);

    toastTimeout = setTimeout(() => {
        showWToast.value = false;
        toastTimeout = null;
    }, 3000);
};

const search = ref(props.searchTerm);
const isLoading = ref(false);
const performSearch = debounce((q) => {
    const filters = {
        search: q,
        code_sort: codeSort.value,
        type_filters:
            typeFilters.value.length > 0 ? typeFilters.value : undefined,
        status_filters:
            statusFilters.value.length > 0 ? statusFilters.value : undefined,
    };

    router.get(route("customer"), filters, {
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

const syncCustomers = async () => {
// alert(1);
    try {
        isLoading.value = true;
        const response = await axios.post(route("syncCustomers"));

        if (response.data.success) {
            router.reload();
            showSuccessToast("Customer Update Successful");
        } else {
            console.error("Sync failed:", response.data.error);
            showWarningToast("Customer Update Failed");
            // Show error toast
        }
    } catch (error) {
        console.error("Error during sync:", error);
        showWarningToast("Customer Update Failed");
        // Show error toast
    } finally {
        isLoading.value = false;
    }
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

// Filter functionality (new)
const showFilters = ref(false);
const codeSort = ref(props.filters?.code_sort || null);
const typeFilters = ref(props.filters?.type_filters || []);
const statusFilters = ref(props.filters?.status_filters || []);

const toggleFilters = () => {
    showFilters.value = !showFilters.value;
};

const setCodeSort = (sort) => {
    codeSort.value = sort === codeSort.value ? null : sort;
};

const resetFilters = () => {
    codeSort.value = null;
    typeFilters.value = [];
    statusFilters.value = [];
};

const applyFilters = () => {
    const filters = {
        search: search.value,
        code_sort: codeSort.value,
        type_filters:
            typeFilters.value.length > 0 ? typeFilters.value : undefined,
        status_filters:
            statusFilters.value.length > 0 ? statusFilters.value : undefined,
    };

    router.get(route("customer"), filters, {
        preserveState: true,
        replace: true,
        onStart: () => (isLoading.value = true),
        onFinish: () => {
            isLoading.value = false;
            showFilters.value = false;
        },
    });
};

// Compute active filter count for badge
const activeFiltersCount = computed(() => {
    let count = 0;
    if (codeSort.value) count++;
    if (typeFilters.value.length > 0) count++;
    if (statusFilters.value.length > 0) count++;
    return count;
});

const dropdownContainer = ref(null);

const handleClickOutside = (event) => {
    if (
        dropdownContainer.value &&
        !dropdownContainer.value.contains(event.target)
    ) {
        showFilters.value = false;
    }
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>
