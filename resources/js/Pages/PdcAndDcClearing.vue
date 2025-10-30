<template>
    <div>
        <Head :title="` | Check Clearing`" />
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <CheckClearing
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
            <ViewCheckClearing
                v-if="showViewModal"
                :show="showViewModal"
                :selected="selectedRow"
                @close="closeViewModal()"
            />
        </Transition>

        <ToastAlert :show="showToast" :message="toastMessage" />

        <div class="flex justify-between pb-3 pt-1">
            <button
                :disabled="!canInsert('0401-CHKCLR')"
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
                            class="absolute right-0 z-20 mt-2 w-lg bg-[var(--color-bg-secondary)] text-[var(--color-text-primary)] rounded-md shadow-lg shadow-[#131313a2] border border-[var(--color-border)]"
                        >
                            <div class="p-4 space-y-4">
                                <!-- Code Sorting -->
                                <div>
                                    <h3 class="text-sm font-medium mb-2">
                                        Sort by Clearing No
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
                                            ASC
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
                                            DSC
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

                                <!-- Date Filter -->
                                <div>
                                    <h3 class="text-sm font-medium mb-2">
                                        Date Range
                                    </h3>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-xs mb-1"
                                                >From</label
                                            >
                                            <div class="mb-2">
                                                <DatePicker
                                                    v-model="dateRange.start"
                                                    placeholder="Select Date"
                                                    format="MM/DD/YYYY"
                                                    validation="no"
                                                />
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs mb-1"
                                                >To</label
                                            >
                                            <div class="mb-2">
                                                <DatePicker
                                                    v-model="dateRange.end"
                                                    placeholder="Select Date"
                                                    format="MM/DD/YYYY"
                                                    validation="no"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Check Type Filter -->
                                <div>
                                    <h3 class="text-sm font-medium mb-2">
                                        Check Type
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
                                                    v-model="
                                                        typeFiltersCheckType
                                                    "
                                                    value="Post Dated Check"
                                                    class="peer appearance-none w-4 h-4 border-2 rounded-sm border-[var(--color-border)] bg-transparent checked:bg-[var(--color-primary)] checked:!border-[var(--color-primary)] focus:outline-none transition-colors duration-200 cursor-pointer"
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
                                            <span class="text-sm cursor-pointer"
                                                >Post Dated Check</span
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
                                                    v-model="
                                                        typeFiltersCheckType
                                                    "
                                                    value="Dated Check"
                                                    class="peer appearance-none w-4 h-4 border-2 rounded-sm border-[var(--color-border)] bg-transparent checked:bg-[var(--color-primary)] checked:!border-[var(--color-primary)] focus:outline-none transition-colors duration-200 cursor-pointer"
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
                                            <span class="text-sm cursor-pointer"
                                                >Dated Check</span
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
                                    class="text-xs font-semibold px-3 py-1 rounded"
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

        <div
            class="bg-[var(--color-bg-secondary)]/20 p-4 rounded-md shadow-[0_0_20px_var(--color-shadow)]/20 mt-4"
        >
            <table
                class="w-full text-sm text-[var(--color-text-primary)] rounded-xl overflow-hidden mb-2"
            >
                <thead class="sticky top-0 z-10">
                    <tr>
                        <th
                            class="px-3 py-2 w-[15%] text-left font-semibold tracking-wider"
                        >
                            CLEARING NO
                        </th>
                        <th
                            class="px-3 py-2 w-[15%] text-left font-semibold tracking-wider"
                        >
                            TRANSACTION DATE
                        </th>
                        <th
                            class="px-3 py-2 w-[15%] text-left font-semibold tracking-wider"
                        >
                            CLEARING DATE
                        </th>
                        <th
                            class="px-3 py-2 w-[30%] text-left font-semibold tracking-wider"
                        >
                            CUSTOMER NAME
                        </th>
                        <th
                            class="px-3 py-2 w-[15%] text-left font-semibold tracking-wider"
                        >
                            CHECK TYPE
                        </th>
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
                        v-for="check_clearing in check_clearings.data"
                        :key="check_clearing.id"
                        class="hover:bg-[var(--color-primary)]/20 transition-colors duration-150 group h-10"
                    >
                        <td class="px-3 py-2 font-medium">
                            {{ check_clearing.clearing_no }}
                        </td>
                        <td class="px-3 py-2">
                            {{ formatDate(check_clearing.transaction_date) }}
                        </td>
                        <td class="px-3 py-2">
                            {{ formatDate(check_clearing.clearing_date) }}
                        </td>
                        <td class="px-3 py-2 font-medium">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-full bg-[var(--color-bg-avatar)] flex items-center justify-center text-white"
                                >
                                    {{
                                        getFirstValidChar(
                                            check_clearing.customer_name
                                        )
                                    }}
                                </div>
                                <div>
                                    <div class="font-medium">
                                        {{ check_clearing.customer_name }}
                                    </div>
                                    <div
                                        class="text-xs text-[var(--color-text-secondary)] mt-0.5"
                                    >
                                        {{
                                            check_clearing.customer_code ||
                                            "No Code"
                                        }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-2">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                :class="{
                                    'bg-amber-700 text-amber-300':
                                        check_clearing.check_type ===
                                        'Post Dated Check',
                                    'bg-lime-700 text-lime-300':
                                        check_clearing.check_type ===
                                        'Dated Check',
                                }"
                            >
                                {{ check_clearing.check_type }}
                            </span>
                        </td>
                        <td class="px-3 py-2">
                            <div class="flex justify-center gap-2">
                                <button
                                    @click="openViewModal(check_clearing)"
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
                    <tr v-if="!isLoading && check_clearings.data.length === 0">
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
            <div v-if="isLoading || check_clearings.data.length === 0" />
            <div v-else>
                <PaginationLinks :paginator="check_clearings" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, computed, onMounted, onUnmounted, nextTick } from "vue";
import PaginationLinks from "./Components/PaginationLinks.vue";
import { router } from "@inertiajs/vue3";
import { debounce } from "lodash";
import ToastAlert from "./Components/ToastAlert.vue";
import CheckClearing from "../Modals/UtilityModals/CheckClearing.vue";
import ViewCheckClearing from "../Modals/UtilityModals/ViewCheckClearing.vue";
import { mdiClose, mdiEye, mdiMagnify, mdiPlus } from "@mdi/js";
import { FunnelIcon } from "@heroicons/vue/24/solid";
import DatePicker from "./Components/DatePicker.vue";
import usePermissions from "./Composables/usePermissions";

const props = defineProps({
    check_clearings: Object,
    searchTerm: String,
    can: Object,
    filters: Object,
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
    showSuccessToast("Clearing has Been Updated Successfully");
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
        code_sort: codeSort.value,
        type_filtersCheckType:
            typeFiltersCheckType.value.length > 0
                ? typeFiltersCheckType.value
                : undefined,
        date_start: dateRange.value.start,
        date_end: dateRange.value.end,
    };

    router.get(route("clearing"), filters, {
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

// Filter functionality (new)
const showFilters = ref(false);
const codeSort = ref(props.filters?.code_sort || null);
const typeFiltersCheckType = ref(props.filters?.type_filtersCheckType || []);
const dateRange = ref({
    start: props.filters?.date_start || null,
    end: props.filters?.date_end || null,
});

const toggleFilters = () => {
    showFilters.value = !showFilters.value;
};

const setCodeSort = (sort) => {
    codeSort.value = sort === codeSort.value ? null : sort;
};

const resetFilters = () => {
    codeSort.value = null;
    typeFiltersCheckType.value = [];
    dateRange.value = { start: null, end: null };
};

const clearDates = () => {
    dateRange.value = {
        start: null,
        end: null,
    };
};

const applyFilters = () => {
    const filters = {
        search: search.value,
        code_sort: codeSort.value,
        type_filtersCheckType:
            typeFiltersCheckType.value.length > 0
                ? typeFiltersCheckType.value
                : undefined,
        date_start: dateRange.value.start,
        date_end: dateRange.value.end,
    };

    router.get(route("clearing"), filters, {
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
    if (typeFiltersCheckType.value.length > 0) count++;
    if (dateRange.value.start || dateRange.value.end) count++;
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

    try {
        window.Echo.channel(props.broadcastChannel)
            .listen(".check_clearing.created", () => {
                if (!showModal.value) {
                    router.reload({
                        preserveState: true,
                        only: ["check_clearings"],
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
    document.removeEventListener("click", handleClickOutside);

    window.Echo.leaveChannel(props.broadcastChannel);
});
</script>
