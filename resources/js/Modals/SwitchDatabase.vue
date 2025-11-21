<template>
    <div v-if="show" class="fixed inset-0 flex items-center justify-center bg-black/40 backdrop-blur-sm z-50">
        <div
            class="bg-[var(--color-bg-secondary)] text-[var(--color-text-secondary)] rounded-2xl shadow-2xl w-full max-w-3xl p-6 border border-[var(--color-border)]">
            <!-- Close Button -->
            <button @click="close"
                class="absolute top-4 right-4 text-[var(--color-icon)] hover:text-[var(--color-icon-hover)]">
                ✕
            </button>

            <!-- Title -->
            <h2 class="text-2xl font-bold mb-5 text-[var(--color-text-primary)]">
                {{ title }}
            </h2>

            <!-- search  -->
            <div class="flex justify-end mb-5">
                <input v-model="searchQuery" type="text" placeholder="Search business unit..."
                    class="w-full rounded-md px-4 py-2 text-[var(--color-text-primary)] border border-[var(--color-border)]">
            </div>

            <!-- loading  -->
            <div v-if="loading" class="flex justify-center items-center py-8">
                <svg width="30" height="30" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                    fill="var(--color-icon)">
                    <rect class="spinner_jCIR" x="1" y="6" width="2.8" height="12" />
                    <rect class="spinner_jCIR spinner_upm8" x="5.8" y="6" width="2.8" height="12" />
                    <rect class="spinner_jCIR spinner_2eL5" x="10.6" y="6" width="2.8" height="12" />
                    <rect class="spinner_jCIR spinner_Rp9l" x="15.4" y="6" width="2.8" height="12" />
                    <rect class="spinner_jCIR spinner_dy3W" x="20.2" y="6" width="2.8" height="12" />
                </svg>
            </div>

            <!-- Table Container -->
            <div v-else class="max-h-96 overflow-y-auto custom-scroll rounded-xl border border-[var(--color-border)]">
                <table class="w-full text-left">
                    <thead class="bg-[var(--color-bg-primary)] text-[var(--color-text-secondary)]">
                        <tr>
                            <th class="px-4 py-3 font-semibold">List of all Business Units</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="item in filteredBusinessUnits" :key="item.id" @click="rowClicked(item.bu_id)"
                            class="cursor-pointer transition hover:bg-[var(--color-primary-hover)]">
                            <td
                                class="px-4 py-3 border-b font-semibold border-[var(--color-border)] text-[var(--color-text-primary)]">
                                {{ item.business_unit }} - {{ item.business_unit_code }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer Buttons -->
            <div class="mt-6 flex justify-end gap-3">
                <button @click="close"
                    class="px-5 py-2.5 bg-[var(--color-bg-secondary)] text-[var(--color-text-primary)] rounded-lg hover:bg-[var(--color-primary-hover)] transition border border-[var(--color-border)]">
                    Close
                </button>

                <button v-if="confirmText" @click="confirm"
                    class="px-5 py-2.5 bg-[var(--color-primary)] text-white rounded-lg hover:bg-[var(--color-primary-hover)] shadow transition">
                    {{ confirmText }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import axios from 'axios';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    show: Boolean,
    title: { type: String, default: "Switch Database" },
    confirmText: { type: String, default: "" },
})

const emits = defineEmits(["close", "confirm", "success", "error"]);
const close = () => emits("close");
const confirm = () => emits("confirm");

const loading = ref(false);
const searchQuery = ref('');
const businessUnits = ref([]);

// Computed property to filter business units based on search query
const filteredBusinessUnits = computed(() => {
    if (!searchQuery.value) return businessUnits.value;
    return businessUnits.value.filter(bu =>
        bu.business_unit.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        bu.business_unit_code.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

// Fetching list of business unit database with status
const fetchDatabase = async () => {
    loading.value = true;
    try {
        const response = await axios.get(route('businessUnits'));
        if (response.data.success) {
            businessUnits.value = response.data.data;
        }
    } catch (error) {
        console.log(error);
    } finally {
        loading.value = false;
    }
};

// Fetch when modal opens
watch(() => props.show, (newValue) => {
    if (newValue) fetchDatabase();
});

// Clickable rows event
const rowClicked = async (id) => {
    try {
        const response = await axios.post(route('selectedBu', id));
        if (response.data.success) {
            emits("close");
            emits("success", response.data.message);
        }
    } catch (error) {
        const msg = error.response?.data?.message || 'Failed to connect to the selected database';
        emits("close");
        emits("error", msg);
    }
};
</script>


<style>
.custom-scroll::-webkit-scrollbar {
    width: 8px;
}

.custom-scroll::-webkit-scrollbar-track {
    background: var(--color-scrollbar-track);
}

.custom-scroll::-webkit-scrollbar-thumb {
    background: var(--color-primary);
}
</style>
