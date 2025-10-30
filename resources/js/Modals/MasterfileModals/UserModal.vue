<template>
    <div v-if="show" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <ToastAlertWarning :show="showToast" :message="toastMessage" />

        <form @submit.prevent="submit" class="w-full max-w-4xl flex flex-col">
            <div
                class="bg-[var(--color-bg-secondary)] text-[var(--color-text-primary)] rounded-2xl border border-[var(--color-border)] flex flex-col h-full">
                <!-- Header -->
                <div class="px-4 sm:px-8 py-4 sm:py-6 flex-shrink-0">
                    <h2 class="text-xl sm:text-2xl font-bold text-center">
                        ADD NEW USER
                    </h2>
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

                <!-- Content -->
                <div v-else class="flex flex-col lg:flex-row">
                    <!-- Left Column: Employee Info -->
                    <div
                        class="w-full lg:w-[40%] p-4 sm:p-6 border-b lg:border-b-0 lg:border-r border-[var(--color-border)]">
                        <div class="flex justify-center -mt-4 sm:-mt-6 mb-4">
                            <img v-if="showImage" :src="profilePhotoUrl" alt="Employee Photo"
                                class="w-32 h-32 sm:w-40 sm:h-40 rounded-full border-4 border-[var(--color-border)] object-cover"
                                @error="showImage = false" />
                            <div v-else
                                class="w-32 h-32 sm:w-40 sm:h-40 rounded-full border-4 border-[var(--color-border)] flex items-center justify-center text-4xl font-bold text-white">
                                {{ userInitials }}
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div
                                class="bg-[var(--color-bg-secondary)] p-3 flex flex-col items-center rounded-lg border border-[var(--color-border)]">
                                <p class="text-sm font-bold text-[var(--color-text-primary)]">
                                    {{ employeeData.employee_name }}
                                </p>
                                <p class="text-xs text-[var(--color-text-secondary)] font-medium">
                                    ({{ employeeData.employee_id }})
                                </p>
                            </div>

                            <div
                                class="bg-[var(--color-bg-secondary)] text-sm p-3 rounded-lg border border-[var(--color-border)] space-y-2">
                                <p class="text-[var(--color-text-primary)]">
                                    <span class="font-medium text-[var(--color-text-secondary)]">Position:</span>
                                    {{ employeeData.employee_position }}
                                </p>

                                <p class="text-[var(--color-text-primary)]">
                                    <span class="font-medium text-[var(--color-text-secondary)]">Company:</span>
                                    {{ employeeData.employee_company }}
                                </p>
                                <p class="text-[var(--color-text-primary)]">
                                    <span class="font-medium text-[var(--color-text-secondary)]">BU:</span>
                                    {{ formattedBunit }}
                                </p>
                                <p class="text-[var(--color-text-primary)]">
                                    <span class="font-medium text-[var(--color-text-secondary)]">Dept:</span>
                                    {{ employeeData.employee_dept }}
                                </p>
                                <p class="text-[var(--color-text-primary)]">
                                    <span class="font-medium text-[var(--color-text-secondary)]">Section:</span>
                                    {{ employeeData.employee_section }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Form Inputs -->
                    <div class="w-full lg:w-[60%] p-4 sm:pl-6 sm:pr-6 sm:pb-6 sm:pt-2">
                        <div class="grid grid-cols-1 gap-4">
                            <TextInput label="Username" v-model="form.username" type="text"
                                :message="form.errors.username" readonly />
                            <TextInput label="Password" v-model="form.password" type="password"
                                :message="form.errors.password" readonly />
                            <TextInput label="Confirm Password" v-model="form.password_confirmation" type="password"
                                readonly />
                            <DropdownInput label="Role" v-model="form.role" :options="[
                                'Admin',
                                'Invoicing',
                                'Accounting',
                                'Bookkeeper',
                                'IAD',
                            ]" placeholder="Click to Select" :message="form.errors.role" />
                            <DropdownInput label="Status" v-model="form.status" :options="['Active', 'Not Active']"
                                placeholder="Click to Select" :message="form.errors.status" />
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-4 sm:px-8 py-4 flex justify-end gap-2 flex-shrink-0">
                    <button type="button" @click="closeModal" class="closeButton group">
                        <div class="flex justify-center items-center gap-2">
                            <span class="transition-transform duration-300 group-hover:rotate-180">
                                <svg-icon type="mdi" :path="mdiClose" class="w-5 h-5" />
                            </span>
                            Close
                        </div>
                    </button>
                    <button type="submit" :disabled="form.processing" class="submitButton group">
                        <div class="flex justify-center items-center gap-2">
                            <span class="transition-transform duration-300 group-hover:rotate-405">
                                <svg-icon type="mdi" :path="mdiNavigationVariantOutline" class="w-5 h-5" />
                            </span>
                            <span v-if="form.processing">Submitting...</span>
                            <span v-else>Submit</span>
                        </div>
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import { route } from "../../../../vendor/tightenco/ziggy/src/js";
import TextInput from "../../Pages/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import ToastAlertWarning from "../../Pages/Components/ToastAlertWarning.vue";
import SvgIcon from "@jamescoyle/vue-icon";
import { mdiClose, mdiNavigationVariantOutline } from "@mdi/js";
import DropdownInput from "../../Pages/Components/DropdownInput.vue";

const props = defineProps({
    show: Boolean,
    employeeData: Object,
});

const form = useForm({
    employee_id: null,
    name: null,
    username: null,
    password: null,
    password_confirmation: null,
    role: null,
    status: null,
});

const showImage = ref(true);
const profilePhotoUrl = route("userPhoto", props.employeeData.employee_name);

const modalLoading = ref(false);

const emit = defineEmits(["close", "closeSuccess"]);

const closeModal = () => {
    emit("close");
};

const userInitials = computed(() => {
    return (
        props.employeeData.employee_name
            ?.split(" ")
            .map((name) => name[0])
            .join("")
            .slice(1)
            .toUpperCase() || ""
    );
});

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

const submit = () => {
    form.employee_id = props.employeeData.employee_id;
    form.post(route("addUser"), {
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
        },
    });
};

watch(
    () => props.employeeData,
    (newVal) => {
        modalLoading.value = true;
        if (newVal) {
            form.name = newVal.employee_name;
            form.username = newVal.employee_id;
            form.password = newVal.employee_id;
            form.password_confirmation = newVal.employee_id;
            modalLoading.value = false;
        }
    },
    { immediate: true }
);

const formattedBunit = computed(() => {
    return props.employeeData.employee_bunit === "HO"
        ? "Head Office"
        : props.employeeData.employee_bunit === "ICM"
            ? "Island City Mall"
            : props.employeeData.employee_bunit === "Acctng / Franchise"
                ? "Accounting/Franchise"
                : props.employeeData.employee_bunit === "CK - Admin"
                    ? "Admin-Chowking"
                    : props.employeeData.employee_bunit === "Aggregates & Cons"
                        ? "Aggregates and Construction Materials"
                        : props.employeeData.employee_bunit === "AMall"
                            ? "ASC Main"
                            : props.employeeData.employee_bunit === "ASC Tal"
                                ? "Alturas Talibon"
                                : props.employeeData.employee_bunit === "DSG"
                                    ? "Distribution Sales Group"
                                    : props.employeeData.employee_bunit === "ASC Tubigon"
                                        ? "Alturas Tubigon"
                                        : props.employeeData.employee_bunit === "ASC Tech - Tagb"
                                            ? "ASC Tech - Tagbilaran"
                                            : props.employeeData.employee_bunit === "SlaughterhouseII"
                                                ? "MFI Slaughterhouse & Meat Cutting Plant II"
                                                : props.employeeData.employee_bunit === "BS Commi"
                                                    ? "Bakeshop Commissary"
                                                    : props.employeeData.employee_bunit === "ORPB"
                                                        ? "Oceanica Resort Panglao Bohol"
                                                        : props.employeeData.employee_bunit === "Cold Storage"
                                                            ? "Cold Storage Commissary"
                                                            : props.employeeData.employee_bunit === "BMC"
                                                                ? "Bohol Milkfish Corporation"
                                                                : props.employeeData.employee_bunit === "Marcela Farms Lab"
                                                                    ? "Marcela Farms Laboratory"
                                                                    : props.employeeData.employee_bunit === "Catagbacan"
                                                                        ? "Catagbacan Farms"
                                                                        : props.employeeData.employee_bunit === "COL-C"
                                                                            ? "Colonnade - Colon"
                                                                            : props.employeeData.employee_bunit === "COL-M"
                                                                                ? "Colonnade - Mandaue"
                                                                                : props.employeeData.employee_bunit === "Commi Compound"
                                                                                    ? "Commissary Compound"
                                                                                    : props.employeeData.employee_bunit === "TPF"
                                                                                        ? "The Prawn Farm"
                                                                                        : props.employeeData.employee_bunit === "FS Commi"
                                                                                            ? "Food Service Commissary"
                                                                                            : props.employeeData.employee_bunit === "Glass Tagb"
                                                                                                ? "Glass Service Tagbilaran"
                                                                                                : props.employeeData.employee_bunit === "Glass Tal"
                                                                                                    ? "Glass Service Talibon"
                                                                                                    : props.employeeData.employee_bunit === "Rizal Breeder"
                                                                                                        ? "MFI Poultry Broiler - Rizal Breeder"
                                                                                                        : props.employeeData.employee_bunit === "GW - AMall"
                                                                                                            ? "Greenwhich Alturas Mall"
                                                                                                            : props.employeeData.employee_bunit === "GW - ICM"
                                                                                                                ? "Greenwhich ICM"
                                                                                                                : props.employeeData.employee_bunit === "Group 1"
                                                                                                                    ? "Group 1 - Grocery Group Management"
                                                                                                                    : props.employeeData.employee_bunit === "Group 2"
                                                                                                                        ? "Group 2 - Home and Fashion, Fixrite"
                                                                                                                        : props.employeeData.employee_bunit === "Group 3"
                                                                                                                            ? "Group 3 - Food Group Management"
                                                                                                                            : props.employeeData.employee_bunit === "Group 4"
                                                                                                                                ? "Group 4 - Farms"
                                                                                                                                : props.employeeData.employee_bunit === "Ortigas"
                                                                                                                                    ? "Ortigas Farms"
                                                                                                                                    : props.employeeData.employee_bunit === "PK"
                                                                                                                                        ? "Peanut Kisses"
                                                                                                                                        : props.employeeData.employee_bunit === "WDG"
                                                                                                                                            ? "Wholesale Distribution Group"
                                                                                                                                            : props.employeeData.employee_bunit === "JB - AMall"
                                                                                                                                                ? "Jollibee Alturas Mall"
                                                                                                                                                : props.employeeData.employee_bunit === "JB - PM"
                                                                                                                                                    ? "Jollibee Plaza Marcela"
                                                                                                                                                    : props.employeeData.employee_bunit === "JB - Alta Citta"
                                                                                                                                                        ? "Jollibee Alta Citta"
                                                                                                                                                        : props.employeeData.employee_bunit === "JB - ICM"
                                                                                                                                                            ? "Jollibee ICM"
                                                                                                                                                            : props.employeeData.employee_bunit === "RR - Tubigon"
                                                                                                                                                                ? "Red Ribbon Tubigon"
                                                                                                                                                                : props.employeeData.employee_bunit === "Maribojoc"
                                                                                                                                                                    ? "Maribojoc Farms"
                                                                                                                                                                    : props.employeeData.employee_bunit === "Dressing Plant"
                                                                                                                                                                        ? "MFI Dressing Plant"
                                                                                                                                                                        : props.employeeData.employee_bunit === "Meat Processing"
                                                                                                                                                                            ? "MFI Meat Processing"
                                                                                                                                                                            : props.employeeData.employee_bunit === "Piggery (Cortes)"
                                                                                                                                                                                ? "MFI Piggery (Cortes)"
                                                                                                                                                                                : props.employeeData.employee_bunit === "Canhayupon"
                                                                                                                                                                                    ? "MFI Poultry Broiler - Canhayupon Breeder"
                                                                                                                                                                                    : props.employeeData.employee_bunit === "Lapsaon"
                                                                                                                                                                                        ? "MFI Poultry Broiler - Lapsaon Breeder"
                                                                                                                                                                                        : props.employeeData.employee_bunit === "Growout"
                                                                                                                                                                                            ? "MFI Poultry Broiler - Growout"
                                                                                                                                                                                            : props.employeeData.employee_bunit === "Hatchery"
                                                                                                                                                                                                ? "MFI Poultry Broiler - Hatchery"
                                                                                                                                                                                                : props.employeeData.employee_bunit === "Bilar Breeder"
                                                                                                                                                                                                    ? "MFI Poultry Broiler - Bilar Breeder"
                                                                                                                                                                                                    : props.employeeData.employee_bunit === "Poultry Layer"
                                                                                                                                                                                                        ? "MFI Poultry Layer"
                                                                                                                                                                                                        : props.employeeData.employee_bunit === "Repacking Srvcs"
                                                                                                                                                                                                            ? "MFI Repacking Services"
                                                                                                                                                                                                            : props.employeeData.employee_bunit === "Tilapia Breeder"
                                                                                                                                                                                                                ? "MFI Tilapia Breeder"
                                                                                                                                                                                                                : props.employeeData.employee_bunit === "Piggery (Alicia)"
                                                                                                                                                                                                                    ? "MFI Piggery (Untaga, Alicia)"
                                                                                                                                                                                                                    : props.employeeData.employee_bunit === "Noodles"
                                                                                                                                                                                                                        ? "Noodles Factory"
                                                                                                                                                                                                                        : props.employeeData.employee_bunit === "Tipcan"
                                                                                                                                                                                                                            ? "Tipcan Farms"
                                                                                                                                                                                                                            : props.employeeData.employee_bunit === "PM"
                                                                                                                                                                                                                                ? "Plaza Marcela"
                                                                                                                                                                                                                                : props.employeeData.employee_bunit === "RR - ICM"
                                                                                                                                                                                                                                    ? "Red Ribbon - ICM"
                                                                                                                                                                                                                                    : props.employeeData.employee_bunit;
});
</script>
