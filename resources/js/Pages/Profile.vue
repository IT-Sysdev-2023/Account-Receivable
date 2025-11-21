<template>
    <div class="rounded-md mt-3">
        <ToastAlert :show="showToast" :message="toastMessage" />
        <ToastAlertWarning :show="showWToast" :message="toastWMessage" />
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4">
            <!-- Profile Information Card -->
            <div class="bg-[var(--color-bg-secondary)] rounded-2xl shadow-lg overflow-hidden lg:col-span-2">
                <!-- Facebook-style Cover Photo -->
                <div class="relative h-40 sm:h-48 bg-[var(--color-primary-hover)]">
                    <!-- Cover Photo -->
                    <img v-if="cover_photo" :src="cover_photo" class="w-full h-full object-cover" alt="Cover photo" />
                    <div v-else class="w-full h-full bg-[var(--color-primary-hover)]"></div>

                    <!-- Profile Picture -->
                    <div class="absolute -bottom-12 sm:-bottom-16 left-4 sm:left-6">
                        <div
                            class="h-24 w-24 sm:h-32 sm:w-32 md:h-40 md:w-40 rounded-full border-4 border-[var(--color-primary)] bg-[var(--color-bg-avatar)] overflow-hidden shadow-lg">
                            <img v-if="
                                showImage && user.name !== 'Administrator'
                            " :src="profilePhotoUrl" class="w-full h-full object-cover" alt="Profile photo"
                                @error="showImage = false" />
                            <div v-else
                                class="w-full h-full flex items-center justify-center text-2xl sm:text-3xl md:text-4xl font-bold text-white">
                                {{ userInitials }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Info Section -->
                <div class="pt-16 sm:pt-20 px-4 sm:px-6 pb-4 sm:pb-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-[var(--color-text-primary)]">
                                {{ user.name }}
                            </h1>
                            <p class="text-sm sm:text-base text-[var(--color-text-secondary)] mt-1">
                                {{
                                    user.name === "Administrator"
                                        ? "Administrator"
                                        : hrmsData?.data?.employee[0]
                                            .employee_position
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Personal Information Section -->
                <div class="p-4 sm:p-6 border-t border-[var(--color-border)] flex flex-col gap-3">
                    <h2 class="text-lg sm:text-xl font-semibold text-[var(--color-text-primary)] flex items-center">
                        <svg-icon type="mdi" :path="mdiAccountCircle"
                            class="h-6 w-6 sm:h-8 sm:w-8 mr-2 text-[var(--color-primary)]" />
                        Personal Information
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div class="space-y-1" v-for="(field, index) in employeeFields" :key="index">
                            <label class="block text-xs sm:text-sm font-medium text-[var(--color-text-secondary)] mb-1">
                                {{ field.label }}
                            </label>
                            <div
                                class="text-sm sm:text-base text-[var(--color-text-primary)] font-medium bg-[var(--color-bg-secondary)]/80 p-2 sm:p-3 rounded-lg border border-[var(--color-border)]">
                                {{
                                    user.name === "Administrator"
                                        ? "Administrator"
                                        : hrmsData?.data?.employee[0][field.key]
                                }}
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs sm:text-sm font-medium text-[var(--color-text-secondary)] mb-1">
                                Role
                            </label>
                            <div
                                class="text-sm sm:text-base text-[var(--color-text-primary)] font-medium bg-[var(--color-bg-secondary)]/80 p-2 sm:p-3 rounded-lg border border-[var(--color-border)]">
                                {{
                                    user.name === "Administrator"
                                        ? "Administrator"
                                        : props.user.role
                                }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Settings Card -->
            <div class="bg-[var(--color-bg-secondary)] rounded-2xl shadow-lg overflow-hidden p-4 sm:p-6 md:p-8">
                <h2
                    class="text-lg sm:text-xl font-semibold text-[var(--color-text-primary)] flex items-center mb-4 sm:mb-6">
                    <svg-icon type="mdi" :path="mdiCog"
                        class="h-6 w-6 sm:h-8 sm:w-8 mr-2 text-[var(--color-primary)]" />
                    Account Settings
                </h2>

                <!-- Theme Selector Dropdown -->
                <div class="mb-6 sm:mb-8">
                    <DropdownInput label="Theme" v-model="selectedTheme" :options="[
                        'Light',
                        // 'Light Blue',
                        // 'Light Gray',
                        'Dark',
                        // 'Dark Blue',
                        // 'Dark Green',
                        // 'Zen Mist',
                        // 'Eucalyptus',
                        // 'Lavender Haze',
                        // 'Seafoam',
                        // 'Parchment',
                        // 'Beige',
                        // 'Midnight Mist',
                        // 'Deep Forest',
                        // 'Twilight Lavender',
                        // 'Abyssal Teal',
                        // 'Charcoal Parchment',
                        // 'Light Coffee',
                        // 'Dark Coffee',
                    ]" placeholder="Select Type" />
                </div>

                <!-- Managers Key Code Generator -->
                <form v-if="canInsert('MANAGERKEY')" @submit.prevent="generateManagersKeyCode"
                    class="space-y-3 sm:space-y-4 mb-6 sm:mb-8">
                    <div>
                        <TextInput label="Managers Key Code" type="text" v-model="managersKeyCode.generatedCode"
                            readonly :message="managersKeyCode.errors.generatedCode" validation="no" />
                    </div>
                    <button type="submit"
                        class="w-full px-3 py-2 sm:px-4 sm:py-3 rounded-md font-medium transition-all duration-300 flex items-center justify-center gap-2 relative overflow-hidden bg-[var(--color-primary)] text-white hover:bg-transparent hover:text-[var(--color-primary)] hover:ring-1 hover:ring-[var(--color-primary)] disabled:opacity-70 disabled:cursor-not-allowed text-sm sm:text-base"
                        :disabled="managersKeyCode.processing">
                        <div class="relative flex items-center justify-center gap-1">
                            <span class="inline-block group-hover:-translate-x-2 transition-transform duration-300">
                                {{
                                    managersKeyCode.processing
                                        ? "Generating Code..."
                                        : "Generate Code"
                                }}
                            </span>
                        </div>
                    </button>
                </form>

                <!-- Change Username Form -->
                <form @submit.prevent="updateUsername" class="space-y-3 sm:space-y-4 mb-6 sm:mb-8">
                    <div>
                        <TextInput label="Username" type="text" v-model="usernameForm.username"
                            :message="usernameForm.errors.username" />
                    </div>
                    <button type="submit"
                        class="w-full px-3 py-2 sm:px-4 sm:py-3 rounded-md font-medium transition-all duration-300 flex items-center justify-center gap-2 relative overflow-hidden bg-[var(--color-primary)] text-white hover:bg-transparent hover:text-[var(--color-primary)] hover:ring-1 hover:ring-[var(--color-primary)] disabled:opacity-70 disabled:cursor-not-allowed text-sm sm:text-base"
                        :disabled="usernameForm.processing">
                        <div class="relative flex items-center justify-center gap-1">
                            <span class="inline-block group-hover:-translate-x-2 transition-transform duration-300">
                                {{
                                    usernameForm.processing
                                        ? "Updating Username..."
                                        : "Update Username"
                                }}
                            </span>
                        </div>
                    </button>
                </form>

                <!-- Change Password Form -->
                <form @submit.prevent="updatePassword" class="space-y-3 sm:space-y-4">
                    <div>
                        <TextInput label="Current Password" type="password" validation="no"
                            v-model="passwordForm.current_password" :message="passwordForm.errors.current_password" />
                    </div>
                    <div>
                        <TextInput label="New Password" type="password" validation="no" v-model="passwordForm.password"
                            :message="passwordForm.errors.password" />
                    </div>
                    <div>
                        <TextInput label="Confirm Password" type="password" validation="no"
                            v-model="passwordForm.password_confirmation" :message="passwordForm.errors.password" />
                    </div>
                    <button type="submit"
                        class="w-full px-3 py-2 sm:px-4 sm:py-3 rounded-md font-medium transition-all duration-300 flex items-center justify-center gap-2 relative overflow-hidden bg-[var(--color-primary)] text-white hover:bg-transparent hover:text-[var(--color-primary)] hover:ring-1 hover:ring-[var(--color-primary)] disabled:opacity-70 disabled:cursor-not-allowed text-sm sm:text-base"
                        :disabled="usernameForm.processing">
                        <div class="relative flex items-center justify-center gap-1">
                            <span class="inline-block group-hover:-translate-x-2 transition-transform duration-300">
                                {{
                                    usernameForm.processing
                                        ? "Updating Password..."
                                        : "Update Password"
                                }}
                            </span>
                        </div>
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import SvgIcon from "@jamescoyle/vue-icon";
import {
    mdiAccountCircle,
    mdiArrowRight,
    mdiCog,
    mdiPencilCircle,
} from "@mdi/js";
import TextInput from "./Components/TextInput.vue";
import ToastAlert from "./Components/ToastAlert.vue";
import ToastAlertWarning from "./Components/ToastAlertWarning.vue";
import useTheme from "./Composables/useTheme";
import DropdownInput from "./Components/DropdownInput.vue";
import usePermissions from "./Composables/usePermissions";

const { canInsert } = usePermissions();

const props = defineProps({
    user: Object,
    hrmsData: {
        type: Object,
        default: () => ({
            data: {
                employee: [
                    {
                        employee_position: "System Programmer I",
                        employee_id: "1234567890",
                        employee_company: "Alturas",
                        employee_bunit: "HO",
                        employee_dept: "IT",
                        employee_section: "System Development",
                        employee_type: "Contractual",
                    },
                ],
            },
        }),
    },
});

// Forms
const managersKeyCode = useForm({
    generatedCode: "",
    ungeneratedCode: "",
});
const usernameForm = useForm({
    username: props.user.username,
});
const passwordForm = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});

const coverPhotoForm = useForm({
    cover_photo: null,
});

const cover_photo = ref("/storage/images/profilecoverphoto.png");
const showToast = ref(false);
const toastMessage = ref("");
const showWToast = ref(false);
const toastWMessage = ref("");
const showImage = ref(true);
const profilePhotoUrl = computed(() =>
    showImage.value ? `${route("profilePhoto")}?t=${Date.now()}` : ""
);

let toastTimeout = null; // to keep track of the timeout
let toastWTimeout = null; // to keep track of the timeout

const employeeFields = [
    { label: "Employee ID", key: "employee_id" },
    { label: "Company", key: "employee_company" },
    { label: "Business Unit", key: "employee_bunit" },
    { label: "Department", key: "employee_dept" },
    { label: "Section", key: "employee_section" },
];

const { theme, setTheme, initTheme } = useTheme(onMounted, onBeforeUnmount);
const selectedTheme = ref(theme.value);

const generateRandomCode = () => {
    const chars =
        "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    let result = "";
    for (let i = 0; i < 8; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
};

watch(selectedTheme, (newVal) => {
    setTheme(newVal);
});

const changeTheme = () => {
    setTheme(selectedTheme.value);
};

//SUCCESSFULL TOAST
const showSuccessToast = (message) => {
    toastMessage.value = message;
    showToast.value = true;

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

//WARNING TOAST
const showWarningToast = (message) => {
    toastWMessage.value = message;
    showWToast.value = false; // Hide first to trigger reactivity if the same toast shows again
    if (toastWTimeout) clearTimeout(toastWTimeout); // Clear any previous timeout

    // Trigger reactivity again on next tick
    setTimeout(() => {
        showWToast.value = true;
    }, 0);

    toastWTimeout = setTimeout(() => {
        showWToast.value = false;
        toastWTimeout = null;
    }, 3000);
};

// Get user initials for avatar
const userInitials = computed(() => {
    if (props.user?.name === "Administrator") {
        return "A";
    } else {
        return (
            props.user?.name
                ?.split(" ")
                .map((name) => name[0])
                .join("")
                .slice(1)
                .toUpperCase() || ""
        );
    }
});

const generateManagersKeyCode = () => {
    showToast.value = false;

    const randomCode = generateRandomCode();
    managersKeyCode.ungeneratedCode = randomCode;

    managersKeyCode.put(route("generateManagersKeyCode", props.user.id), {
        preserveScroll: true,
        onSuccess: () => {
            showToast.value = false;
            managersKeyCode.generatedCode = managersKeyCode.ungeneratedCode;
            managersKeyCode.ungeneratedCode = "";
            showSuccessToast("Generated Code Successfully");
        },
        onError: (error) => {
            managersKeyCode.generatedCode = "";
            managersKeyCode.ungeneratedCode = "";
            showWToast.value = false;
            const firstError = Object.values(error)[0];
            showWarningToast(firstError);
        },
    });
};

const updateUsername = () => {
    usernameForm.put(route("updateUsername", props.user.id), {
        preserveScroll: true,
        onSuccess: () => {
            showToast.value = false;
            showSuccessToast("Username Update Successful");
        },
        onError: (error) => {
            showWToast.value = false;
            const firstError = Object.values(error)[0];
            showWarningToast(firstError);
        },
    });
};

const updatePassword = () => {
    passwordForm.put(route("updatePassword", props.user.id), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
            showToast.value = false;
            showSuccessToast("Password Update Successful");
        },
        onError: (error) => {
            showWToast.value = false;
            if (Object.keys(error).length === 1) {
                const firstError = Object.values(error)[0];
                showWarningToast(firstError);
            } else if (Object.keys(error).length !== 1) {
                showWarningToast("Please Fill Up Necessary Fields");
            }
        },
    });
};

// // Handle cover photo change
// const handleCoverPhotoChange = (event) => {
//     coverPhotoForm.cover_photo = event.target.files[0];
//     coverPhotoForm.post(route("profile.update-cover-photo"), {
//         preserveScroll: true,
//         onSuccess: () => {
//             // Handle success
//         },
//     });
// };
</script>
