<template>
    <div
        v-if="show"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center px-4"
    >
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <ConfirmationDialog
                :show="showDialog"
                message="Do you want to print the Report?"
                @close="handleConfirm"
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
            <PdfPreviewModal
                v-if="showPdfModal"
                :show="showPdfModal"
                :apiEndpoint="apiRoute"
                :formData="pdfFormData"
                @closeSuccess="pdfPrintSuccess"
                @close="pdfPrintSuccess"
            />
        </Transition>

        <ToastAlertWarning :show="showToast" :message="toastMessage" />

        <div
            class="bg-[var(--color-bg-secondary)] text-[var(--color-text-primary)] w-full max-w-3xl rounded-2xl border border-[var(--color-border)]"
        >
            <form @submit.prevent="submit">
                <div class="px-8 py-6">
                    <!-- Header -->
                    <div class="px-8 pb-4">
                        <h2 class="text-2xl font-bold text-center">
                            SALES PER ITEM
                        </h2>
                        <div
                            class="mt-2 h-0.5 bg-gradient-to-r from-transparent via-[var(--color-border)] to-transparent"
                        ></div>
                    </div>

                    <!-- Content - Date Range Selector -->
                    <div class="flex flex-col gap-2">
                        <!-- Item Selection -->
                        <div class="flex flex-col gap-2">
                            <label class="block text-md font-bold"
                                >SELECT ITEM</label
                            >
                            <div class="flex gap-4">
                                <!-- All Item Option -->
                                <label
                                    class="inline-flex items-center cursor-pointer group"
                                >
                                    <input
                                        type="radio"
                                        v-model="form.item_type"
                                        value="All Items"
                                        class="hidden peer"
                                    />
                                    <div
                                        class="relative flex items-center justify-center p-2"
                                    >
                                        <!-- Hover circle -->
                                        <div
                                            class="absolute -inset-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-[var(--color-border)]/40"
                                            :class="{
                                                'opacity-100':
                                                    form.item_type ===
                                                    'All Items',
                                            }"
                                        ></div>
                                        <!-- Radio button -->
                                        <div
                                            class="relative w-5 h-5 mr-2 rounded-full border-2 border-[var(--color-bg-avatar)] transition-colors z-10 group-hover:border-[var(--color-border)]"
                                            :class="{
                                                'border-[var(--color-border)]':
                                                    form.item_type ===
                                                    'All Items',
                                            }"
                                        >
                                            <div
                                                class="absolute inset-0 m-auto w-2.5 h-2.5 rounded-full bg-[var(--color-border)] transition-opacity"
                                                :class="{
                                                    'opacity-100':
                                                        form.item_type ===
                                                        'All Items',
                                                    'opacity-0':
                                                        form.item_type !==
                                                        'All Items',
                                                }"
                                            ></div>
                                        </div>
                                        <span class="text-sm font-medium z-10"
                                            >All Items</span
                                        >
                                    </div>
                                </label>

                                <!-- By Item Option -->
                                <label
                                    class="inline-flex items-center cursor-pointer group"
                                >
                                    <input
                                        type="radio"
                                        v-model="form.item_type"
                                        value="By Items"
                                        class="hidden peer"
                                    />
                                    <div
                                        class="relative flex items-center justify-center p-2"
                                    >
                                        <!-- Hover circle -->
                                        <div
                                            class="absolute -inset-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-[var(--color-border)]/40"
                                            :class="{
                                                'opacity-100':
                                                    form.item_type ===
                                                    'By Items',
                                            }"
                                        ></div>
                                        <!-- Radio button -->
                                        <div
                                            class="relative w-5 h-5 mr-2 rounded-full border-2 border-[var(--color-bg-avatar)] transition-colors z-10 group-hover:border-[var(--color-border)]"
                                            :class="{
                                                'border-[var(--color-border)]':
                                                    form.item_type ===
                                                    'By Items',
                                            }"
                                        >
                                            <div
                                                class="absolute inset-0 m-auto w-2.5 h-2.5 rounded-full bg-[var(--color-border)] transition-opacity"
                                                :class="{
                                                    'opacity-100':
                                                        form.item_type ===
                                                        'By Items',
                                                    'opacity-0':
                                                        form.item_type !==
                                                        'By Items',
                                                }"
                                            ></div>
                                        </div>
                                        <span class="text-sm font-medium z-10"
                                            >By Items</span
                                        >
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Multi-Select Dropdown for Items -->
                        <div class="flex flex-col gap-1">
                            <label class="block text-md font-bold"
                                >SELECT ITEMS</label
                            >
                            <div class="relative">
                                <!-- Selected Tags Display -->
                                <div
                                    class="flex flex-wrap gap-2 p-2 min-h-12 border border-[var(--color-border)] rounded-lg"
                                >
                                    <span
                                        v-if="form.item_type === 'By Items'"
                                        v-for="(
                                            item, index
                                        ) in form.selectedItems"
                                        :key="item"
                                        class="inline-flex items-center px-3 py-1 rounded-full bg-[var(--color-primary)]/40 font-medium text-sm"
                                    >
                                        {{ item }}
                                        <button
                                            type="button"
                                            @click.stop="removeItem(index)"
                                            class="ml-2"
                                        >
                                            <svg-icon
                                                type="mdi"
                                                :path="mdiClose"
                                                class="w-3 h-3"
                                            />
                                        </button>
                                    </span>
                                </div>
                                <TextInput
                                    type="text"
                                    v-model="itemSearch"
                                    @input="searchItems"
                                    @focus="openDropdown"
                                    @blur="closeDropdown"
                                    @keydown.enter.prevent="
                                        addFirstMatchingItem
                                    "
                                    @keydown.down="highlightNext"
                                    @keydown.up="highlightPrev"
                                    :modifiedPlaceholder="'By Items Only'"
                                    :defaultPlaceholder="'Click To Select Items'"
                                    :readonly="form.item_type === 'All Items'"
                                    ref="itemInput"
                                    validation="no"
                                    selectable="yes"
                                />
                                <!-- Dropdown with all items -->
                                <div
                                    v-show="showItemDropdown"
                                    class="absolute z-10 w-full pl-2 backdrop-blur-xl border border-[var(--color-border)] rounded-lg shadow-lg shadow-[#131313a2] overflow-hidden"
                                >
                                    <div
                                        class="max-h-60 overflow-auto p-1 scrollbar-thin scrollbar-thumb-[var(--color-scrollbar-track)] scrollbar-track-[var(--color-scrollbar-track)] scrollbar-stable [scrollbar-gutter:stable] scrollbar-thumb-rounded-full"
                                    >
                                        <ul>
                                            <li
                                                v-for="(
                                                    item, index
                                                ) in filteredItems"
                                                :key="item"
                                                @click="selectItem(item)"
                                                @mouseover="
                                                    highlightedIndex = index
                                                "
                                                class="px-2 py-1 cursor-pointer flex items-center gap-2 whitespace-nowrap text-sm border-b border-[var(--color-border)]/30 last:border-b-0 transition duration-150"
                                                :class="{
                                                    'bg-[var(--color-primary)]/40':
                                                        isSelected(item),
                                                }"
                                            >
                                                {{ item.name }}
                                                <span
                                                    v-if="isSelected(item)"
                                                    class="ml-auto"
                                                >
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        viewBox="0 0 24 24"
                                                        class="w-4 h-4 text-white"
                                                        fill="white"
                                                    >
                                                        <path
                                                            d="M9,20.42L2.79,14.21L5.62,11.38L9,14.77L18.88,4.88L21.71,7.71L9,20.42Z"
                                                        />
                                                    </svg>
                                                </span>
                                            </li>
                                            <li
                                                v-if="
                                                    filteredItems.length === 0
                                                "
                                                class="px-4 py-2 text-[var(--color-text-primary)] text-center"
                                            >
                                                No items found
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Date Type Selection -->
                        <div class="flex flex-col gap-2">
                            <label class="block text-md font-lg font-bold"
                                >SELECT DATE</label
                            >
                        </div>

                        <!-- Date Range -->
                        <div class="grid grid-cols-2 gap-2">
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
                    </div>

                    <!-- Footer -->
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
            </form>
        </div>
    </div>
</template>

<script setup>
import {
    computed,
    nextTick,
    onMounted,
    onUnmounted,
    readonly,
    ref,
    watch,
} from "vue";
import { route } from "../../../../vendor/tightenco/ziggy/src/js";
import TextInput from "../../Pages/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import ConfirmationDialog from "../../Pages/Components/ConfirmationDialog.vue";
import ToastAlertWarning from "../../Pages/Components/ToastAlertWarning.vue";
import ToastAlertYellowWarning from "../../Pages/Components/ToastAlertYellowWarning.vue";
import { mdiClose, mdiNavigationVariantOutline } from "@mdi/js";
import DatePicker from "../../Pages/Components/DatePicker.vue";
import PdfPreviewModal from "../PdfPreviewModal.vue";

const props = defineProps({
    show: Boolean,
});

const form = useForm({
    item_type: "All Items",
    selectedItems: [],
    start_date: null,
    end_date: null,
    processtype: null,
});

const emit = defineEmits(["close", "closeSuccess"]);

const closeModal = () => {
    emit("close");
};

//////////////////////////////////////// MULTI SELECT ITEM /////////////////////////////////////////////////////////////////////////////////////
const itemSearch = ref("");
const showItemDropdown = ref(false);
const highlightedIndex = ref(-1);
const allItems = ref([]);
const itemInput = ref(null);

// Fetch items on component mount
onMounted(async () => {
    try {
        const response = await axios.get(route("getAllItemList"));
        allItems.value = response.data;
    } catch (error) {
        console.error("Error fetching items:", error);
    }
});

// Blur handler
const handleBlur = () => {
    setTimeout(() => {
        showItemDropdown.value = false;
    }, 200);
};

// Computed property for filtered items
const filteredItems = computed(() => {
    if (!itemSearch.value) return allItems.value;
    const searchTerm = itemSearch.value.toLowerCase();
    return allItems.value.filter((item) =>
        item.name.toLowerCase().includes(searchTerm)
    );
});

// Check if item is already selected
const isSelected = (item) => {
    return form.selectedItems.includes(item.name);
};

// Open dropdown
const openDropdown = () => {
    showItemDropdown.value = true;
    itemSearch.value = ""; // Clear search when opening
};

// Close dropdown with slight delay to allow click events
const closeDropdown = () => {
    setTimeout(() => {
        showItemDropdown.value = false;
    }, 200);
};

// Select item (toggle selection)
const selectItem = (item) => {
    if (isSelected(item)) {
        const index = form.selectedItems.indexOf(item.name);
        removeItem(index);
    } else {
        addItem(item);
    }
};

// Methods
const searchItems = () => {
    highlightedIndex.value = -1;
};

const addItem = (item) => {
    if (!form.selectedItems.includes(item.name)) {
        form.selectedItems.push(item.name);
    }
    itemSearch.value = "";
    showItemDropdown.value = false;
};

const removeItem = (index) => {
    form.selectedItems.splice(index, 1);
};

const addFirstMatchingItem = () => {
    if (filteredItems.value.length > 0 && highlightedIndex.value === -1) {
        addItem(filteredItems.value[0]);
    } else if (highlightedIndex.value >= 0) {
        addItem(filteredItems.value[highlightedIndex.value]);
    }
};

const highlightNext = () => {
    if (highlightedIndex.value < filteredItems.value.length - 1) {
        highlightedIndex.value++;
    }
};

const highlightPrev = () => {
    if (highlightedIndex.value > 0) {
        highlightedIndex.value--;
    }
};

const focusMultiSelect = () => {
    itemInput.value.focus();
};

watch(
    () => form.item_type,
    (newType) => {
        if (newType === "All Items") {
            form.selectedItems = [];
        }
    }
);

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
const showPdfModal = ref(false);
const pdfFormData = ref(null);
const apiRoute = ref(null);
const previewInvoice = async () => {
    try {
        form.processtype = "axios";

        apiRoute.value = "salesPerItem";
        pdfFormData.value = form;
        showPdfModal.value = true;
    } catch (error) {
        console.error("Error previewing invoice:", error);
    }
};

const pdfPrintSuccess = () => {
    showPdfModal.value = false;
    emit("close");
};

const showDialog = ref(false);
const handleConfirm = async (confirmed) => {
    showDialog.value = false;
    if (confirmed) {
        previewInvoice();
    }
};
/////////// SUBMIT ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
const submit = () => {
    if (form.item_type === "By Items" && form.selectedItems.length === 0) {
        showWarningToast("Please select at least one item.");
        return;
    }
    if (form.item_type === "All Items") {
        form.selectedItems = allItems.value.map((item) => item.name);
    }
    Object.keys(form.errors).forEach((key) => {
        form.errors[key] = "";
    });
    form.post(route("salesPerItem"), {
        onSuccess: () => {
            showDialog.value = true;
        },
        onError: (error) => {
            if (form.item_type === "All Items") {
                form.selectedItems = [];
            }
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
