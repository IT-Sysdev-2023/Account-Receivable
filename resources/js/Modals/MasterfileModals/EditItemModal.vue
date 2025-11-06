<template>
    <div v-if="show" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center">
        <ToastAlertWarning :show="showWToast" :message="toastWMessage" />

        <!-- Toast + Packing Modal -->
        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <ItemPackingModal v-if="showItemPackingModal" :show="showItemPackingModal" :itemID="item_id"
                @close="closeItemPackingModal" @closeSuccess="closeItemPackingSuccessModal" />
        </Transition>

        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <AccountCodeList v-if="showAccCodeModal" :show="showAccCodeModal" @close="showAccCodeModal = false"
                @submit="handleSelectedAccCode" />
        </Transition>

        <ToastAlert :show="showToast" :message="toastMessage" />
        <div
            class="bg-[var(--color-bg-secondary)] text-[var(--color-text-primary)] w-full max-w-5xl rounded-2xl border border-[var(--color-border)]">
            <form @submit.prevent="submit">
                <!-- Header -->
                <div class="px-8 pt-6 pb-4">
                    <h2 class="text-2xl font-bold text-center">EDIT ITEM</h2>
                    <div class="mt-2 h-0.5 bg-gradient-to-r from-transparent via-[var(--color-border)] to-transparent">
                    </div>
                </div>
                <div v-if="modalLoading" class="flex justify-center items-center py-20">
                    <svg width="40" height="40" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                        fill="var(--color-icon)">
                        <rect class="spinner_jCIR" x="1" y="6" width="2.8" height="12" />
                        <rect class="spinner_jCIR spinner_upm8" x="5.8" y="6" width="2.8" height="12" />
                        <rect class="spinner_jCIR spinner_2eL5" x="10.6" y="6" width="2.8" height="12" />
                        <rect class="spinner_jCIR spinner_Rp9l" x="15.4" y="6" width="2.8" height="12" />
                        <rect class="spinner_jCIR spinner_dy3W" x="20.2" y="6" width="2.8" height="12" />
                    </svg>
                </div>
                <div v-else class="px-8 space-y-6">
                    <div class="flex flex-col md:flex-row gap-6">
                        <!-- Left: Image and Icons -->
                        <div class="w-full md:w-1/3 flex flex-col items-center gap-4">
                            <!-- Image Upload -->
                            <label
                                class="group relative w-full aspect-square rounded-xl p-2 border-2 border-dashed border-[var(--color-border)] flex items-center justify-center overflow-hidden cursor-pointer shadow-lg shadow-[#131313a2]">
                                <img :src="imageUrl" alt="Item Photo"
                                    class="object-cover w-full h-full transition group-hover:opacity-80 rounded-xl" />
                                <input type="file" accept="image/*" @change="onImageChange" class="hidden" />
                                <div
                                    class="absolute inset-0 bg-[var(--color-primary)]/80 flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition">
                                    <span class="text-sm font-medium">Change Photo</span>
                                </div>
                            </label>
                            <p class="text-center font-medium">Item Photo</p>

                            <!-- Action Icons -->
                            <button :disabled="!canView('0104-ITMPCK')" type="button" @click="openItemModal()"
                                title="Edit Packing"
                                class="w-full px-4 py-2 rounded-md font-medium transition-all duration-300 flex items-center justify-center gap-2 relative overflow-hidden bg-[var(--color-primary)] text-white hover:bg-transparent hover:text-[var(--color-primary)] hover:ring-1 hover:ring-[var(--color-primary)] disabled:opacity-70 disabled:cursor-not-allowed group">
                                <div class="relative flex items-center justify-center gap-2">
                                    <span class="transition-transform duration-300 group-hover:rotate-360">
                                        <svg-icon type="mdi" :path="mdiPackageVariantPlus" class="w-7 h-7" />
                                    </span>
                                    Item Pricing and Packing
                                </div>
                            </button>
                        </div>

                        <!-- Right: Inputs -->
                        <div class="w-full md:w-2/3 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <TextInput label="Code" v-model="form.code" type="text" :message="form.errors.code" />
                            <TextInput label="Setup Date" v-model="form.setup_date" type="date" :readonly="true"
                                :message="form.errors.setup_date" />
                            <TextInput label="Name" v-model="form.name" type="text" :message="form.errors.name"
                                class="md:col-span-2" />
                            <TextInput label="Description" v-model="form.description" type="textarea"
                                :message="form.errors.description" class="md:col-span-2" />

                            <div ref="dropdownRef" class="relative">
                                <label class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2">Set
                                    Packing For This Item</label>
                                <!-- Select-like box -->
                                <div @click="isDropdownOpen = !isDropdownOpen" :class="[selectedPackingItems.length === 0 ? '!form-input rounded-md px-3 py-2 cursor-pointer select-none flex justify-between items-center border ring-2 ring-red-300 border-red-400 hover:border-red-400 focus-within:ring-2 focus-within:ring-red-500/50'
                                    : 'form-input border rounded-md px-3 py-2 cursor-pointer select-none flex justify-between items-center border-[var(--color-border)] hover:border-green-400 focus-within:ring-2 focus-within:ring-green-500/50'
                                ]">
                                    <span v-if="selectedPackingItems.length === 0"
                                        class="text-green-700 font-medium text-center">
                                        Select Packing
                                    </span>

                                    <span v-else class="truncate text-green-700 font-medium">
                                        {{selectedPackingItems.map(i => i.packing_type).join(', ')}}
                                    </span>

                                    <svg class="w-4 h-4 ml-2 text-gray-500 transition-transform duration-150"
                                        :class="{ 'rotate-180': isDropdownOpen }" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>

                                <!-- Dropdown list -->
                                <div v-if="isDropdownOpen"
                                    class="absolute z-50 mt-1 w-full bg-[var(--color-bg-secondary)] border border-[var(--color-border)] rounded-md shadow-lg max-h-48 overflow-y-auto scrollbar-thin scrollbar-thumb-rounded-full scrollbar-track-rounded-full">
                                    <div v-for="item in packingTypeList" :key="item.id"
                                        @click.stop="toggleSelection(item.id)"
                                        class="px-3 py-2 cursor-pointer select-none hover:bg-green-100 transition-all duration-150 flex justify-between items-center"
                                        :class="{
                                            'bg-green-900/10 border-l-4 border-green-500 font-medium text-green-700':
                                                selectedPackingIds.includes(item.id),
                                        }">
                                        <span>{{ item.packing_type }}</span>
                                        <span v-if="selectedPackingIds.includes(item.id)">✅</span>
                                    </div>
                                </div>
                            </div>
                            <DropdownInput label="Type" v-model="form.type" placeholder="Click to Select"
                                :options="typeOptions" :message="form.errors.type" />
                            <TextInput label="Account Code" v-model="form.acc_code" @click="onAccCodeClick()"
                                type="text" :message="form.errors.acc_code" :default-placeholder="'Click to Select'"
                                selectable="yes" />
                        </div>
                    </div>
                </div>
                <!-- Footer -->
                <div class="mt-4 mx-8 pb-6 pt-2 border-t gap-2 border-[var(--color-border)] flex justify-between">
                    <div class="flex items-center">
                        <p v-if="props.item.created_by" class="text-xs">
                            Updated By : {{ props.item.created_by }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" @click="closeModal" class="closeButton group">
                            <div class="flex justify-center items-center gap-2">
                                <span class="transition-transform duration-300 group-hover:rotate-180">
                                    <svg-icon type="mdi" :path="mdiClose" class="w-5 h-5" />
                                </span>
                                Close
                            </div>
                        </button>
                        <button type="submit" class="submitButton group" :disabled="form.processing">
                            <div class="flex justify-center items-center gap-2">
                                <span class="transition-transform duration-300 group-hover:rotate-405">
                                    <svg-icon type="mdi" :path="mdiNavigationVariantOutline" class="w-5 h-5" />
                                </span>
                                <span v-if="form.processing">Saving...</span>
                                <span v-else>Save Changes</span>
                            </div>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { route } from "../../../../vendor/tightenco/ziggy/src/js";
import TextInput from "../../Pages/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import ItemPackingModal from "./ItemPackingModal.vue";
import ToastAlert from "../../Pages/Components/ToastAlert.vue";
import axios from "axios";
import ToastAlertWarning from "../../Pages/Components/ToastAlertWarning.vue";
import {
    mdiClose,
    mdiNavigationVariantOutline,
    mdiPackageVariantPlus,
} from "@mdi/js";
import DropdownInput from "../../Pages/Components/DropdownInput.vue";
import usePermissions from "../../Pages/Composables/usePermissions";
import AccountCodeList from "../TransactionModals/AccountCodeList.vue";

const props = defineProps({
    show: Boolean,
    item: Object,
});

const form = useForm({
    code: null,
    setup_date: null,
    name: null,
    description: null,
    type: null,
    acc_code: null,
    item_photo: null,
});

form.setup_date = props.item.setup_date;
const DEFAULT_PHOTO = "/storage/images/addItem.png";
const imageUrl = ref(DEFAULT_PHOTO);
const imageFile = ref(null);
const showItemPackingModal = ref(false);
const showItemWholeSaleModal = ref(false);
const item_id = ref(null);
const showToast = ref(false);
const toastMessage = ref("");
const typeOptions = ref([]);
const modalLoading = ref(false);
const showAccCodeModal = ref(false);
const packingTypeList = ref([]);

const selectedPackingIds = ref([]);
const isDropdownOpen = ref(false);
const dropdownRef = ref(null)


const { canView } = usePermissions();

const showSuccessToast = (message) => {
    toastMessage.value = message;
    showToast.value = true;

    setTimeout(() => {
        showToast.value = false;
    }, 3000); // Show toast for 3 seconds
};

function onImageChange(event) {
    const file = event.target.files[0];
    if (file) {
        form.item_photo = imageFile.value = file;
        imageUrl.value = URL.createObjectURL(file);
    }
}

const openItemModal = () => {
    item_id.value = props.item.id;
    showItemPackingModal.value = true;
};
const closeItemPackingModal = () => {
    showItemPackingModal.value = false;
};
const closeItemPackingSuccessModal = () => {
    showItemPackingModal.value = false;
    showSuccessToast("Item Packing Has Been Updated Successfully");
    emit("closeSuccess");
};

const openItemWholeSaleModal = () => {
    item_id.value = props.item.id;
    showItemWholeSaleModal.value = true;
};
const closeItemWholeSaleModal = () => {
    showItemWholeSaleModal.value = false;
};
const closeItemWholeSaleSuccessModal = () => {
    showItemWholeSaleModal.value = false;
    showSuccessToast("Item Wholesale Has Been Updated Successfully");
};

function onAccCodeClick() {
    showAccCodeModal.value = true;
}

const handleSelectedAccCode = (selectedData) => {
    form.acc_code = selectedData.listaccCode;
    showAccCodeModal.value = false;
};

const emit = defineEmits(["close", "closeSuccess"]);

const closeModal = () => {
    emit("close");
};

//////////////////////////////////////// SHOW TOAST /////////////////////////////////////////////////////////////////////////////////////////
const showWToast = ref(false);
const toastWMessage = ref("");
let toastTimeout = null; // to keep track of the timeout

const showWarningToast = (message) => {
    toastWMessage.value = message;
    showWToast.value = false;
    if (toastTimeout) clearTimeout(toastTimeout);

    setTimeout(() => {
        showWToast.value = true;
    }, 0);

    toastTimeout = setTimeout(() => {
        showWToast.value = false;
        toastTimeout = null;
    }, 3000);
};

////////////  //////////////////////////////////////////////////////////////////////////////////////////////////////////

const submit = () => {
    form.post(route("updateItem", {
        id: props.item.id,
        packing: selectedPackingIds.value
    }), {
        onSuccess: () => {
            form.reset(); // clear on success
            emit("closeSuccess");
        },
        onError: (error) => {
            showToast.value = false;
            if (Object.keys(error).length === 1) {
                const firstError = Object.values(error)[0];
                showWarningToast(firstError);
            } else if (Object.keys(error).length !== 1) {
                showWarningToast("Please Fill Up Necessary Fields");
            }
        }
    });
};


watch(
    () => props.show,
    async (newVal) => {
        modalLoading.value = true;
        if (newVal && props.item) {
            form.code = props.item.code;
            form.name = props.item.name;
            form.description = props.item.description;
            form.type = props.item.type;
            form.acc_code = props.item.acc_code;
            if (props.item.item_photo) {
                form.item_photo = null;
                imageUrl.value = props.item.item_photo;
            } else {
                form.item_photo = null;
                imageUrl.value = DEFAULT_PHOTO;
            }
            // convert JSON string to array from the props.item.packing 
            try {
                selectedPackingIds.value = Array.isArray(props.item.packing)
                    ? props.item.packing.map(id => Number(id))
                    : JSON.parse(props.item.packing || '[]').map(id => Number(id));
            } catch (error) {
                console.error('Failed to parse packing JSON:', error);
                selectedPackingIds.value = [];
            }

            try {
                const response = await axios.get(route("ci-types"));
                typeOptions.value = response.data;
            } catch (error) {
                console.error("Failed to fetch ci_types:", error);
            }
            modalLoading.value = false;
        }
    },
    { immediate: true }
);

// This is for setting item packing 
const fetchPackingTypeList = async () => {
    try {
        const response = await axios.get(route('packingTypeList'))
        packingTypeList.value = response.data
    } catch (error) {
        console.error('Error fetching packing types:', error)
    }
};

const toggleSelection = (id) => {
    if (selectedPackingIds.value.includes(id)) {
        selectedPackingIds.value = selectedPackingIds.value.filter(item => item !== id)
    } else {
        selectedPackingIds.value.push(id)
    }
};

const selectedPackingItems = computed(() => {
    if (!selectedPackingIds.value.length) return []
    return packingTypeList.value.filter(item => selectedPackingIds.value.includes(item.id))
});

const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isDropdownOpen.value = false
    }
};

onMounted(() => {
    fetchPackingTypeList()
    document.addEventListener('click', handleClickOutside)
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside)
});


</script>
