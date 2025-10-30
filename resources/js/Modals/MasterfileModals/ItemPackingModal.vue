<template>
    <div
        class="fixed inset-0 bg-black/60 backdrop-blur-sm flex justify-center items-center z-50"
    >
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <SelectionTable
                v-if="showSelectionTable"
                :show="showSelectionTable"
                :data="
                    priceGroupResults.map((code) => ({
                        label: code,
                        value: code,
                    }))
                "
                title="Select Price Group"
                labelKey="label"
                valueKey="value"
                @submit="onSelectGroupcode"
                @close="showSelectionTable = false"
            />
        </Transition>

        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <SelectionTable
                v-if="showSelectionTablePacking"
                :show="showSelectionTablePacking"
                :data="packingOptions"
                title="Select Packing Type"
                @submit="onSelectPacking"
                @close="showSelectionTablePacking = false"
            />
        </Transition>

        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <SelectionTable
                v-if="showSelectionTableStatus"
                :show="showSelectionTableStatus"
                :data="statusOptions"
                title="Select Status"
                @submit="onSelectStatus"
                @close="showSelectionTableStatus = false"
            />
        </Transition>

        <ToastAlertWarning :show="showToast" :message="toastMessage" />
        <!-- Modal Container -->
        <div
            class="w-full max-w-6xl rounded-2xl bg-[var(--color-bg-secondary)] border border-[var(--color-border)]"
        >
            <!-- Content -->
            <div class="p-6">
                <!-- Header -->
                <div class="mb-6 text-center">
                    <h2
                        class="text-2xl font-bold text-[var(--color-text-primary)] tracking-wide"
                    >
                        ITEM PRICE AND PACKING
                    </h2>
                    <div
                        class="mt-2 h-0.5 bg-gradient-to-r from-transparent via-[var(--color-border)] to-transparent"
                    ></div>
                </div>
                <!-- Form & Table -->
                <form @submit.prevent="submit">
                    <!-- Show spinner while loading -->
                    <div
                        v-if="isLoading"
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

                    <!-- Show table after data is loaded -->
                    <div
                        v-else
                        class="rounded-xl bg-[var(--color-primary)]/20 backdrop-blur-sm"
                    >
                        <div class="sticky top-0 z-10 px-2">
                            <table
                                class="w-full text-[var(--color-text-primary)] text-sm"
                            >
                                <thead>
                                    <tr
                                        class="text-sm uppercase tracking-wider text-[var(--color-text-primary)]"
                                    >
                                        <th class="px-4 py-3 text-left w-[20%]">
                                            Group Code
                                        </th>
                                        <th class="px-4 py-3 text-left w-[20%]">
                                            Packing
                                        </th>
                                        <th
                                            class="px-4 py-3 text-center w-[15%]"
                                        >
                                            Price
                                        </th>
                                        <th
                                            class="px-4 py-3 text-center w-[15%]"
                                        >
                                            Quantity
                                        </th>
                                        <th
                                            class="px-4 py-3 text-center w-[15%]"
                                        >
                                            Status
                                        </th>
                                        <th
                                            v-if="canUpdate('0104-ITMPCK')"
                                            class="px-4 py-3 text-center w-[20%]"
                                        >
                                            Action
                                        </th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                        <div class="relative">
                            <div
                                class="overflow-y-auto max-h-[220px] scrollbar-thin scrollbar-stable [scrollbar-gutter:stable] pl-2 pb-2"
                            >
                                <table
                                    class="w-full text-[var(--color-text-primary)] text-sm"
                                >
                                    <tbody class="rounded-xl">
                                        <tr
                                            v-for="(row, index) in rows"
                                            :key="index"
                                            :class="[
                                                'transition-all duration-300 ease-in-out rounded-md',
                                                editingIndex === index
                                                    ? 'bg-[var(--color-primary)]/20 border border-[var(--color-border)]'
                                                    : '',
                                                isMatchingRow(row, index)
                                                    ? '!border-red-500 !bg-red-500/10'
                                                    : '',
                                                index === rows.length - 1 &&
                                                index !== 0
                                                    ? 'animate-fade-in'
                                                    : '',
                                            ]"
                                        >
                                            <!-- Group Code -->
                                            <td class="px-4 py-3 w-[20%]">
                                                <div class="relative">
                                                    <input
                                                        v-if="
                                                            editingIndex ===
                                                            index
                                                        "
                                                        v-model="row.groupcode"
                                                        type="text"
                                                        @click="
                                                            openSelection(index)
                                                        "
                                                        readonly
                                                        class="cursor-pointer"
                                                        :class="[
                                                            row.groupcode
                                                                ? 'border-[var(--color-border)]'
                                                                : '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10',
                                                            isDuplicateRow(
                                                                index
                                                            )
                                                                ? '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10'
                                                                : '',
                                                        ]"
                                                        placeholder="Click To Select"
                                                    />
                                                    <span v-else>{{
                                                        row.groupcode
                                                    }}</span>

                                                    <!-- Clear button -->
                                                    <button
                                                        type="button"
                                                        v-if="
                                                            editingIndex ===
                                                                index &&
                                                            row.groupcode &&
                                                            !row.saved
                                                        "
                                                        @click.stop="
                                                            clearGroupcode(
                                                                index
                                                            )
                                                        "
                                                        class="absolute top-1/2 right-2 transform -translate-y-1/2 text-[var(--color-text-primary)]"
                                                        title="Clear"
                                                    >
                                                        <svg-icon
                                                            type="mdi"
                                                            :path="mdiClose"
                                                            class="w-4 h-4 hover:text-red-500"
                                                        />
                                                    </button>
                                                </div>
                                            </td>
                                            <!-- Packing -->
                                            <td class="px-4 py-3 w-[20%]">
                                                <div class="relative">
                                                    <input
                                                        v-if="
                                                            editingIndex ===
                                                            index
                                                        "
                                                        v-model="row.packing"
                                                        type="text"
                                                        @click="
                                                            openSelectionPacking(
                                                                index
                                                            )
                                                        "
                                                        readonly
                                                        class="cursor-pointer"
                                                        :class="[
                                                            row.packing
                                                                ? 'border-[var(--color-border)]'
                                                                : '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10',
                                                            isDuplicateRow(
                                                                index
                                                            )
                                                                ? '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10'
                                                                : '',
                                                        ]"
                                                        placeholder="Click To Select"
                                                    />
                                                    <span v-else>{{
                                                        row.packing
                                                    }}</span>

                                                    <!-- Clear button -->
                                                    <button
                                                        type="button"
                                                        v-if="
                                                            editingIndex ===
                                                                index &&
                                                            row.packing &&
                                                            !row.saved
                                                        "
                                                        @click.stop="
                                                            clearPacking(index)
                                                        "
                                                        class="absolute top-1/2 right-2 transform -translate-y-1/2 text-[var(--color-text-primary)]"
                                                        title="Clear"
                                                    >
                                                        <svg-icon
                                                            type="mdi"
                                                            :path="mdiClose"
                                                            class="w-4 h-4 hover:text-red-500"
                                                        />
                                                    </button>
                                                </div>
                                            </td>

                                            <!-- Price -->
                                            <td
                                                class="px-4 py-3 text-right w-[15%]"
                                            >
                                                <input
                                                    v-if="
                                                        editingIndex === index
                                                    "
                                                    v-model="row.price"
                                                    type="number"
                                                    inputmode="decimal"
                                                    min="0"
                                                    step="any"
                                                    :class="[
                                                        row.price
                                                            ? 'border-[var(--color-border)]'
                                                            : '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10',
                                                    ]"
                                                    placeholder="0.00"
                                                />
                                                <span v-else>{{
                                                    row.price
                                                }}</span>
                                            </td>

                                            <!-- Quantity -->
                                            <td
                                                class="px-4 py-3 text-right w-[15%]"
                                            >
                                                <input
                                                    v-if="
                                                        editingIndex === index
                                                    "
                                                    v-model="row.quantity"
                                                    type="number"
                                                    inputmode="numeric"
                                                    min="0"
                                                    step="1"
                                                    :class="[
                                                        row.quantity
                                                            ? 'border-[var(--color-border)]'
                                                            : '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10',
                                                    ]"
                                                    placeholder="0"
                                                />
                                                <span v-else>{{
                                                    row.quantity
                                                }}</span>
                                            </td>

                                            <!-- Status -->
                                            <td
                                                class="px-4 py-3 text-center w-[15%]"
                                            >
                                                <div class="relative">
                                                    <input
                                                        v-if="
                                                            editingIndex ===
                                                            index
                                                        "
                                                        v-model="row.status"
                                                        type="text"
                                                        @click="
                                                            openSelectionStatus(
                                                                index
                                                            )
                                                        "
                                                        readonly
                                                        class="cursor-pointer"
                                                        :class="[
                                                            row.status
                                                                ? 'border-[var(--color-border)]'
                                                                : '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10',
                                                        ]"
                                                        placeholder="Click To Select"
                                                    />
                                                    <span v-else>{{
                                                        row.status
                                                    }}</span>

                                                    <!-- Clear button -->
                                                    <button
                                                        type="button"
                                                        v-if="
                                                            editingIndex ===
                                                                index &&
                                                            row.status &&
                                                            !row.saved
                                                        "
                                                        @click.stop="
                                                            clearStatus(index)
                                                        "
                                                        class="absolute top-1/2 right-2 transform -translate-y-1/2 text-[var(--color-text-primary)]"
                                                        title="Clear"
                                                    >
                                                        <svg-icon
                                                            type="mdi"
                                                            :path="mdiClose"
                                                            class="w-4 h-4 hover:text-red-500"
                                                        />
                                                    </button>
                                                </div>
                                            </td>

                                            <!-- Actions -->
                                            <td
                                                v-if="canUpdate('0104-ITMPCK')"
                                                class="px-4 py-3 flex justify-center items-center gap-2 w-full"
                                            >
                                                <!-- Save -->
                                                <button
                                                    v-if="
                                                        editingIndex === index
                                                    "
                                                    @click.prevent="
                                                        saveRow(index)
                                                    "
                                                    title="Save"
                                                    class="bg-blue-600 hover:bg-blue-700 cursor-pointer text-white p-1.5 rounded-full transition group"
                                                >
                                                    <div
                                                        class="flex justify-center items-center gap-2"
                                                    >
                                                        <span
                                                            class="transition-transform duration-300 group-hover:rotate-360"
                                                        >
                                                            <svg-icon
                                                                type="mdi"
                                                                :path="mdiCheck"
                                                                class="w-4 h-4"
                                                            />
                                                        </span>
                                                    </div>
                                                </button>

                                                <!-- Edit -->
                                                <button
                                                    v-else
                                                    @click.prevent="
                                                        editRow(index)
                                                    "
                                                    title="Edit"
                                                    class="bg-green-600 hover:bg-green-700 cursor-pointer text-white p-1.5 rounded-full transition group"
                                                >
                                                    <div
                                                        class="flex justify-center items-center gap-2"
                                                    >
                                                        <span
                                                            class="transition-transform duration-300 group-hover:rotate-360"
                                                        >
                                                            <svg-icon
                                                                type="mdi"
                                                                :path="
                                                                    mdiPencil
                                                                "
                                                                class="w-4 h-4"
                                                            />
                                                        </span>
                                                    </div>
                                                </button>

                                                <!-- Delete -->
                                                <button
                                                    v-if="
                                                        editingIndex !==
                                                            index &&
                                                        index !==
                                                            rows.length - 1
                                                    "
                                                    @click.prevent="
                                                        deleteRow(index)
                                                    "
                                                    title="Delete"
                                                    class="bg-red-600 hover:bg-red-700 cursor-pointer text-white p-1.5 rounded-full transition group"
                                                >
                                                    <div
                                                        class="flex justify-center items-center gap-2"
                                                    >
                                                        <span
                                                            class="transition-transform duration-300 group-hover:rotate-180"
                                                        >
                                                            <svg-icon
                                                                type="mdi"
                                                                :path="mdiClose"
                                                                class="w-4 h-4"
                                                            />
                                                        </span>
                                                    </div>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-white/10">
                        <div class="flex justify-end gap-3">
                            <button
                                type="button"
                                @click="closeModal"
                                class="closeButton group"
                            >
                                <div
                                    class="flex justify-center items-center gap-2"
                                >
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
                                v-if="canUpdate('0104-ITMPCK')"
                                type="submit"
                                class="submitButton group"
                                :disabled="
                                    form.processing ||
                                    editingIndex !== rows.length - 1 ||
                                    !hasAtLeastOneRow
                                "
                            >
                                <div
                                    class="flex justify-center items-center gap-2"
                                >
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
    </div>
</template>

<script setup>
import { ref, watch, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import { route } from "../../../../vendor/tightenco/ziggy/src/js";
import axios from "axios";
import ToastAlertWarning from "../../Pages/Components/ToastAlertWarning.vue";
import {
    mdiCheck,
    mdiClose,
    mdiNavigationVariantOutline,
    mdiPencil,
} from "@mdi/js";
import SelectionTable from "../../Pages/Components/SelectionTable.vue";
import usePermissions from "../../Pages/Composables/usePermissions";

const props = defineProps({
    show: Boolean,
    itemID: Number,
});

const { canUpdate } = usePermissions();

//groupcode
const priceGroupResults = ref([]);
const loading = ref(false);
const buts = ref([]);
const activeDropdownIndex = ref(null); // Track which dropdown is active

//packing
const showDropdownPacking = ref(false);
const packingOptions = ref([]);
const loadingPacking = ref(false);
const butsPacking = ref([]);
const activeDropdownIndexPacking = ref(null);

//status
const showDropdownStatus = ref(false);
const statusOptions = ref([]);
const loadingStatus = ref(false);
const butsStatus = ref([]);
const activeDropdownIndexStatus = ref(null);

//selectiontable
const showSelectionTable = ref(false);
const showSelectionTablePacking = ref(false);
const showSelectionTableStatus = ref(false);
const selectedRowIndex = ref(null);

const emit = defineEmits(["close", "closeSuccess"]);
const closeModal = () => emit("close");

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

const rows = ref([
    {
        groupcode: "",
        packing: "",
        price: "",
        quantity: "",
        status: "",
        saved: false, // Track whether the row has been saved
    },
]);
const editingIndex = ref(rows.value.length - 1); // default to last row
buts.value = rows.value.map(() => false); // initialize buts
butsPacking.value = rows.value.map(() => false); // initialize buts
butsStatus.value = rows.value.map(() => false); // initialize buts

const form = useForm({
    item_id: null, // set this dynamically
    packings: [],
});

//PRICE GROUP
const fetchPriceGroup = async () => {
    try {
        const response = await axios.get(route('fetchPriceGroup'));

        if (response.data &&
            Array.isArray(response.data.price_group) &&
            response.data.price_group.length > 0
        ) {
            console.log(response.data);

            priceGroupResults.value = response.data.price_group.map(
                (group) => group.prc_group_code
            );
        } else {
            priceGroupResults.value = [];
            console.warn("No price groups available.");
        }
    } catch (error) {
        console.error("Error fetching price group data:", error);
        priceGroupResults.value = [];
    }
};

const onSelectGroupcode = (selected) => {
    if (selectedRowIndex.value !== null) {
        rows.value[selectedRowIndex.value].groupcode = selected.value;
        buts.value[selectedRowIndex.value] = true;
        showSelectionTable.value = false;
        selectedRowIndex.value = null;
    }
};

const clearGroupcode = (index) => {
    rows.value[index].groupcode = ""; // Clear the groupcode value
    buts.value[index] = false;
};

const openSelection = async (index) => {
    selectedRowIndex.value = index;

    if (!rows.value[index].groupcode?.trim()) {
        await fetchPriceGroup();
    }

    showSelectionTable.value = true;
};

//PACKING
const fetchPacking = async () => {
    try {
        const response = await axios.get(route("getPackingTypes"));

        packingOptions.value = response.data;
    } catch (error) {
        console.error("Error fetching packing data:", error);
        packingOptions.value = [];
    }
};

const onSelectPacking = (selected) => {
    if (selectedRowIndex.value !== null) {
        rows.value[selectedRowIndex.value].packing = selected.value;
        butsPacking.value[selectedRowIndex.value] = true;
        showSelectionTablePacking.value = false;
        selectedRowIndex.value = null;
    }
};

const clearPacking = (index) => {
    rows.value[index].packing = ""; // Clear the groupcode value
    butsPacking.value[index] = false;
};

const openSelectionPacking = async (index) => {
    selectedRowIndex.value = index;

    if (!rows.value[index].packing?.trim()) {
        await fetchPacking();
    }

    showSelectionTablePacking.value = true;
};

//STATUS
const fetchStatus = async () => {
    statusOptions.value = ["Available", "Block"];
};

const onSelectStatus = (selected) => {
    if (selectedRowIndex.value !== null) {
        rows.value[selectedRowIndex.value].status = selected.value;
        butsStatus.value[selectedRowIndex.value] = true;
        showSelectionTableStatus.value = false;
        selectedRowIndex.value = null;
    }
};

const clearStatus = (index) => {
    rows.value[index].status = ""; // Clear the groupcode value
    butsStatus.value[index] = false;
};

const openSelectionStatus = async (index) => {
    selectedRowIndex.value = index;

    if (!rows.value[index].status?.trim()) {
        await fetchStatus();
    }

    showSelectionTableStatus.value = true;
};

//ROW SAVE EDIT DELETE
const editRow = (index) => {
    editingIndex.value = index;
};

const saveRow = (index) => {
    const currentRow = rows.value[index];

    // Validation: if any field is empty, don't proceed
    if (
        currentRow.groupcode.trim() === "" ||
        currentRow.packing.trim() === "" ||
        currentRow.price === "" ||
        currentRow.quantity === "" ||
        currentRow.status === ""
    ) {
        showWarningToast("Please fill in all fields before saving");
        return;
    }

    // After saving, mark the row as saved
    currentRow.saved = true;

    // Exit editing mode after validation
    editingIndex.value = rows.value.length - 1;

    // If it's the last row, push a new empty row
    if (index === rows.value.length - 1) {
        rows.value.push({
            groupcode: "",
            packing: "",
            price: "",
            quantity: "",
            status: "",
            saved: false,
            _new: true,
        });
        editingIndex.value = rows.value.length - 1;

        setTimeout(() => {
            delete rows.value[rows.value.length - 1]._new;
        }, 400);
    }
};

const deleteRow = (index) => {
    if (index !== rows.value.length - 1) {
        rows.value.splice(index, 1);
    }
    editingIndex.value = rows.value.length - 1;
};

const hasAtLeastOneRow = computed(() => {
    if (hadInitialData.value) {
        return true;
    }
    return rows.value.some(
        (row) =>
            row.groupcode.trim() !== "" &&
            row.packing.trim() !== "" &&
            row.price &&
            row.quantity
    );
});

//SUBMIT
const submit = () => {
    if (rows.value[rows.value.length - 1].groupcode) {
        return showWarningToast(
            "Please save the current packing or clear it before submitting"
        );
    }

    form.item_id = props.itemID;
    form.packings = rows.value.filter(
        (row) =>
            row.groupcode.trim() !== "" &&
            row.packing.trim() !== "" &&
            row.price &&
            row.quantity
    );
    form.post(route("item-packings.store"), {
        onSuccess: () => {
            form.reset(); // clear on success
            emit("closeSuccess");
        },
        onError: (errors) => {
            const errorMessage = errors.packings || "";
            showWarningToast(errorMessage);
            console.log(errors); // helpful for debugging
        },
    });
};

const hadInitialData = ref(false);

const isLoading = ref(false);
watch(
    () => props.show,
    async (newVal) => {
        if (newVal && props.itemID) {
            isLoading.value = true;
            try {
                const response = await axios.get(
                    route("showItemPackings", props.itemID)
                );
                rows.value = response.data.map((row) => ({
                    ...row,
                    _new: true,
                }));

                hadInitialData.value = response.data.length > 0;

                // Always ensure there's an empty row at the end
                if (canUpdate("0104-ITMPCK")) {
                    rows.value.push({
                        groupcode: "",
                        packing: "",
                        price: "",
                        quantity: "",
                        status: "",
                        _new: true,
                    });
                    editingIndex.value = rows.value.length - 1; // optionally begin editing the first
                } else {
                    editingIndex.value = -1;
                }
            } catch (error) {
                console.error("Failed to fetch packings", error);
            } finally {
                isLoading.value = false;
            }
        }
    },
    { immediate: true }
);

const isDuplicateRow = (currentIndex) => {
    const current = rows.value[currentIndex];

    if (!current) return false;

    return rows.value.some((row, i) => {
        if (!row || i === currentIndex) return false;
        return (
            row.groupcode === current.groupcode &&
            row.packing === current.packing &&
            current.groupcode &&
            current.packing
        );
    });
};

const isMatchingRow = (row, index) => {
    if (!row.groupcode || !row.packing) return false;

    return rows.value.some((otherRow, i) => {
        return (
            i !== index &&
            otherRow.groupcode === row.groupcode &&
            otherRow.packing === row.packing
        );
    });
};
</script>

<style scoped>
@keyframes fadeIn {
    0% {
        opacity: 0;
        transform: translateY(10px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeIn 0.4s ease-out;
}
</style>
