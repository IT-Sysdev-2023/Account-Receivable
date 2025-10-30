<template>
    <div
        class="fixed inset-0 bg-black/60 backdrop-blur-sm flex justify-center items-center z-50"
    >
        <div
            class="bg-[rgb(15,42,29)] p-4 text-white w-full max-w-6xl max-h-[90vh] rounded-2xl shadow-lg overflow-hidden flex flex-col"
        >
            <!-- Header -->
            <div class="px-2  pt-2">
                <h2 class="text-2xl font-extrabold text-center tracking-wide">
                    ITEM WHOLESALE
                </h2>
            </div>
            <ToastAlertWarning :show="showToast" :message="toastMessage" />
            <!-- Form & Table -->
            <form @submit.prevent="submit">
                <div class="bg-[rgb(15,42,29)] p-4 rounded-md">
                    <div
                        class="max-h-[410px] overflow-y-scroll scrollbar-thin scrollbar-thumb-gray-500 scrollbar-track-[rgb(15,42,29)] scrollbar-thumb-rounded-full scrollbar-track-rounded-full"
                    >
                        <!-- Show spinner while loading -->
                        <div
                            v-if="isLoading"
                            class="flex justify-center items-center py-20"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 200 200"
                                width="80"
                                height="80"
                            >
                                <circle
                                    fill="#00A63E"
                                    stroke="#00A63E"
                                    stroke-width="8"
                                    r="15"
                                    cx="40"
                                    cy="65"
                                >
                                    <animate
                                        attributeName="cy"
                                        calcMode="spline"
                                        dur="0.5"
                                        values="65;135;65;"
                                        keySplines=".5 0 .5 1;.5 0 .5 1"
                                        repeatCount="indefinite"
                                        begin="-.4"
                                    ></animate>
                                </circle>
                                <circle
                                    fill="#00A63E"
                                    stroke="#00A63E"
                                    stroke-width="8"
                                    r="15"
                                    cx="100"
                                    cy="65"
                                >
                                    <animate
                                        attributeName="cy"
                                        calcMode="spline"
                                        dur="0.5"
                                        values="65;135;65;"
                                        keySplines=".5 0 .5 1;.5 0 .5 1"
                                        repeatCount="indefinite"
                                        begin="-.2"
                                    ></animate>
                                </circle>
                                <circle
                                    fill="#00A63E"
                                    stroke="#00A63E"
                                    stroke-width="8"
                                    r="15"
                                    cx="160"
                                    cy="65"
                                >
                                    <animate
                                        attributeName="cy"
                                        calcMode="spline"
                                        dur="0.5"
                                        values="65;135;65;"
                                        keySplines=".5 0 .5 1;.5 0 .5 1"
                                        repeatCount="indefinite"
                                        begin="0"
                                    ></animate>
                                </circle>
                            </svg>
                        </div>

                        <!-- Show table after data is loaded -->
                        <table
                            v-else
                            class="w-full table-fixed text-sm text-white border-separate border-spacing-y-2 p-2"
                        >
                            <thead
                                class="sticky top-0 z-10 backdrop-blur-sm bg-[rgba(15,42,29,0.85)] text-slate-300 text-lg"
                            >
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left w-[100px] border-b border-slate-400"
                                    >
                                        Group Code
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left w-[150px] border-b border-slate-400"
                                    >
                                        Packing
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left w-[100px] border-b border-slate-400"
                                    >
                                        Price
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left w-[100px] border-b border-slate-400"
                                    >
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(row, index) in rows"
                                    :key="index"
                                    :class="[
                                        'transition-all duration-300 ease-in-out border border-slate-700 rounded-md overflow-hidden',
                                        editingIndex === index
                                            ? 'bg-green-900/30 ring-2 ring-green-600/40'
                                            : '',
                                        index === rows.length - 1 && index !== 0
                                            ? 'animate-fade-in'
                                            : '',
                                    ]"
                                >
                                    <!-- Group Code -->
                                    <td class="px-4 py-3">
                                        <input
                                            v-if="editingIndex === index"
                                            v-model="row.groupcode"
                                            class="hide-arrows bg-white/10 backdrop-blur-sm text-white px-2 py-1 rounded-md focus:outline-none focus:ring-2 focus:ring-green-400 w-full min-w-[80px]"
                                        />
                                        <span v-else>{{ row.groupcode }}</span>
                                    </td>

                                    <!-- Packing -->
                                    <td class="px-4 py-3">
                                        <input
                                            v-if="editingIndex === index"
                                            v-model="row.packing"
                                            class="bg-white/10 backdrop-blur-sm text-white px-2 py-1 rounded-md focus:outline-none focus:ring-2 focus:ring-green-400 w-full min-w-[80px]"
                                        />
                                        <span v-else>{{ row.packing }}</span>
                                    </td>

                                    <!-- Price -->
                                    <td class="px-4 py-3">
                                        <input
                                            v-if="editingIndex === index"
                                            v-model="row.price"
                                            type="number"
                                            inputmode="decimal"
                                            min="0"
                                            step="any"
                                            class="hide-arrows bg-white/10 backdrop-blur-sm text-white px-2 py-1 rounded-md focus:outline-none focus:ring-2 focus:ring-green-400 w-full min-w-[80px]"
                                        />
                                        <span v-else>{{ row.price }}</span>
                                    </td>

                                    <!-- Actions -->
                                    <td
                                        class="px-4 py-3 flex items-center gap-2"
                                    >
                                        <!-- Save -->
                                        <button
                                            v-if="editingIndex === index"
                                            @click.prevent="saveRow(index)"
                                            title="Save"
                                            class="bg-blue-600 hover:bg-blue-700 cursor-pointer text-white p-1.5 rounded-full transition"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                viewBox="0 0 24 24"
                                            >
                                                <path d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>

                                        <!-- Edit -->
                                        <button
                                            v-else
                                            @click.prevent="editRow(index)"
                                            title="Edit"
                                            class="bg-green-600 hover:bg-green-700 cursor-pointer text-white p-1.5 rounded-full transition"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    d="M15.2 6.2l2.6 2.6L9 17.6H6.4V15z"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                />
                                            </svg>
                                        </button>

                                        <!-- Delete -->
                                        <button
                                            v-if="
                                                editingIndex !== index &&
                                                index !== rows.length - 1
                                            "
                                            @click.prevent="deleteRow(index)"
                                            title="Delete"
                                            class="bg-red-600 hover:bg-red-700 cursor-pointer text-white p-1.5 rounded-full transition"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    d="M6 18L18 6M6 6l12 12"
                                                />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="pt-4 flex justify-end">
                    <div class="flex gap-3">
                        <button
                            type="submit"
                            class="px-4 py-2 bg-green-700 hover:bg-green-800 text-white rounded-md font-semibold cursor-pointer transition disabled:opacity-50 relative overflow-hidden shadow-lg shadow-[#131313a2]"
                            :disabled="form.processing || editingIndex !== rows.length - 1"
                        >
                            <span v-if="form.processing">Submitting...</span>
                            <span v-else>Submit</span>
                        </button>
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md font-semibold cursor-pointer transition relative overflow-hidden shadow-lg shadow-[#131313a2]"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import { route } from "../../../../vendor/tightenco/ziggy/src/js";
import axios from "axios";
import ToastAlertWarning from "../../Pages/Components/ToastAlertWarning.vue";

const props = defineProps({
    show: Boolean,
    itemID: Number,
});

const showToast = ref(false);
const toastMessage = ref("");

const emit = defineEmits(["close", "closeSuccess"]);
const closeModal = () => emit("close");

const showWarningToast = (message) => {
    toastMessage.value = message;
    showToast.value = true;

    setTimeout(() => {
        showToast.value = false;
    }, 3000); // Show toast for 3 seconds
};

const rows = ref([
    { groupcode: "", packing: "", price: "" },
]);
const editingIndex = ref(rows.value.length - 1); // default to last row

const editRow = (index) => {
    editingIndex.value = index;
};

const saveRow = (index) => {
    const currentRow = rows.value[index];

    // Validation: if any field is empty, don't proceed
    if (
        currentRow.groupcode === "" ||
        currentRow.packing.trim() === "" ||
        currentRow.price === "" 
    ) {
        showWarningToast("Please fill in all fields before saving");
        return;
    }

    // Exit editing mode after validation
    editingIndex.value = rows.value.length - 1;

    // If it's the last row, push a new empty row
    if (index === rows.value.length - 1) {
        rows.value.push({
            groupcode: "",
            packing: "",
            price: "",
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

const form = useForm({
    item_id: null, // set this dynamically
    wholesales: [],
});

const submit = () => {
    form.item_id = props.itemID;
    form.wholesales = rows.value.filter(
        (row) => row.packing.trim() !== "" && row.price && row.groupcode.trim() !== ""
    );
    form.post(route("item-wholesales.store"), {
        onSuccess: () => {
            form.reset(); // clear on success
            emit("closeSuccess");
        },
        onError: (errors) => {
            console.log(errors); // helpful for debugging
        },
    });
};

const isLoading = ref(false);
watch(
    () => props.show,
    async (newVal) => {
        if (newVal && props.itemID) {
            isLoading.value = true;
            try {
                const response = await axios.get(
                    `/item-wholesales/${props.itemID}`
                );
                rows.value = response.data.map((row) => ({
                    ...row,
                    _new: false,
                }));

                // Always ensure there's an empty row at the end
                rows.value.push({
                    groupcode: "",
                    packing: "",
                    price: "",
                    _new: true,
                });

                editingIndex.value = rows.value.length - 1; // optionally begin editing the first
            } catch (error) {
                console.error("Failed to fetch wholesales", error);
            } finally {
                isLoading.value = false;
            }
        }
    },
    { immediate: true }
);
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
