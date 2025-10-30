<template>
    <div>
        <Head :title="` | ${$page.component}`" />
        <div class="flex justify-between pb-3 pt-1">
            <button
                @click="openModal()"
                :disabled="!canInsert('0103-CHKR')"
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
            <div class="w-1/3">
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

        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <CheckerModal
                v-if="showModal"
                :show="showModal"
                @close="closeModal"
                @closeSuccess="closeSuccessModal"
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
            <EditCheckerModal
                v-if="showEditModal"
                :show="showEditModal"
                :checker="selectedChecker"
                @close="closeEditModal"
                @closeSuccess="closeEditSuccessModal"
            />
        </Transition>
        <ToastAlert :show="showToast" :message="toastMessage" />
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
                message="Are you sure about deleting this checker?"
                @close="handleConfirm"
            />
        </Transition>

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
                            CODE
                        </th>
                        <th
                            class="px-3 py-2 w-[35%] text-left font-semibold tracking-wider"
                        >
                            FIRST NAME
                        </th>
                        <th
                            class="px-3 py-2 w-[35%] text-left font-semibold tracking-wider"
                        >
                            LAST NAME
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

                <tbody v-else>
                    <tr
                        v-for="checker in checkers.data"
                        :key="checker.id"
                        class="hover:bg-[var(--color-primary)]/20 transition-colors duration-150 group h-10"
                    >
                        <td class="px-3 py-2 font-medium">
                            {{ checker.id }}
                        </td>
                        <td class="px-3 py-2 font-medium">
                            <div class="flex justify-start items-center gap-2">
                                <div class="flex justify-start">
                                    <img
                                        class="w-10 h-10 rounded-full border-2 border-[var(--color-border)] object-cover"
                                        alt="Photo"
                                        :src="
                                            checker.photo
                                                ? checker.photo
                                                : '/storage/images/default_profile_pic.png'
                                        "
                                    />
                                </div>
                                {{ checker.first_name }}
                            </div>
                        </td>
                        <td class="px-3 py-2 font-medium">
                            {{ checker.last_name }}
                        </td>
                        <td class="px-3 py-2 text-center">
                            <div class="flex justify-center gap-2">
                                <button
                                    :disabled="!canUpdate('0103-CHKR')"
                                    @click="openEditModal(checker)"
                                    class="p-1.5 cursor-pointer rounded-lg transition-all duration-200 bg-[var(--color-primary)]/30 hover:bg-[var(--color-primary)]/50 hover:shadow-lg group-hover:opacity-100"
                                >
                                    <svg-icon
                                        type="mdi"
                                        :path="mdiPencil"
                                        class="w-4 h-4 text-[var(--color-primary)]"
                                    />
                                </button>
                                <button
                                    :disabled="!canDelete('0103-CHKR')"
                                    @click="deleteChecker(checker)"
                                    class="p-1.5 cursor-pointer rounded-lg transition-all duration-200 bg-red-500/30 hover:bg-red-500/50 hover:shadow-lg group-hover:opacity-100"
                                >
                                    <svg
                                        class="w-4 h-4 text-red-600"
                                        fill="currentColor"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <!-- Empty State -->
                    <tr v-if="!isLoading && checkers.data.length === 0">
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
            <div v-if="isLoading || checkers.data.length === 0" />
            <div v-else>
                <PaginationLinks :paginator="checkers" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { nextTick, onMounted, onUnmounted, ref, watch } from "vue";
import PaginationLinks from "./Components/PaginationLinks.vue";
import { router } from "@inertiajs/vue3";
import { debounce } from "lodash";
import CheckerModal from "../Modals/MasterfileModals/CheckerModal.vue";
import EditCheckerModal from "../Modals/MasterfileModals/EditCheckerModal.vue";
import ToastAlert from "./Components/ToastAlert.vue";
import ConfirmationDialog from "./Components/ConfirmationDialog.vue";
import { mdiCached, mdiClose, mdiMagnify, mdiPencil, mdiPlus } from "@mdi/js";
import { route } from "../../../vendor/tightenco/ziggy/src/js";
import usePermissions from "./Composables/usePermissions";

const props = defineProps({
    checkers: Object,
    searchTerm: String,
    can: Object,
    broadcastChannel: String,
});

const showModal = ref(false);
const showEditModal = ref(false);
const selectedChecker = ref(null);
const showToast = ref(false);
const toastMessage = ref("");
const showDialog = ref(false);
const pendingDeleteID = ref(null);

const { canInsert } = usePermissions();
const { canUpdate } = usePermissions();
const { canDelete } = usePermissions();

const search = ref(props.searchTerm);

const showSuccessToast = (message) => {
    toastMessage.value = message;
    showToast.value = true;

    setTimeout(() => {
        showToast.value = false;
    }, 3000); // Show toast for 3 seconds
};

const openModal = () => {
    showModal.value = true;
};

const openEditModal = (checker) => {
    selectedChecker.value = checker;
    showEditModal.value = true;
};

const closeModal = () => {
    showModal.value = false; // Close the modal
};
const closeSuccessModal = () => {
    showModal.value = false; // Close the modal
    showSuccessToast("Checker has Been Addedd Successfully");
};

const closeEditModal = () => {
    showEditModal.value = false; // Close the modal
};
const closeEditSuccessModal = () => {
    showEditModal.value = false; // Close the modal
    showSuccessToast("Checker has Been Updated Successfully");
};

const deleteChecker = async (checkerID) => {
    pendingDeleteID.value = checkerID;
    showDialog.value = true;
};
const handleConfirm = async (confirmed) => {
    showDialog.value = false;
    if (confirmed && pendingDeleteID.value) {
        try {
            await router.delete(route("deleteChecker", pendingDeleteID.value), {
                onSuccess: () => {
                    showSuccessToast("Checker has been deleted successfully");
                },
                onError: (errors) => {
                    console.error("Failed to delete Checker:", errors);
                },
            });
        } catch (error) {
            console.error("Unexpected error deleting Checker:", error);
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
    router.get(
        route("checker"),
        { search: q },
        {
            preserveState: true,
            replace: true,
            onFinish: () => {
                isLoading.value = false;
            },
        }
    );
}, 1000);
watch(search, (q) => {
    isLoading.value = true;
    performSearch(q);
});

onMounted(() => {
    try {
        window.Echo.channel(props.broadcastChannel)
            .listen(".checker.created", () => {
                if (!showModal.value && !showEditModal.value) {
                    router.reload({
                        preserveState: true,
                        only: ["checkers"],
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
