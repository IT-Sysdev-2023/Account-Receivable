<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-sm bg-black/60"
    >
        <ToastAlertWarning :show="showToast" :message="toastMessage" />

        <!-- Modal Container -->
        <div
            class="w-full max-w-5xl overflow-hidden rounded-2xl bg-[var(--color-bg-secondary)] border border-[var(--color-border)]"
        >
            <!-- Content -->
            <div class="p-6">
                <!-- Form & Table -->
                <form @submit.prevent="submit">
                    <!-- Header -->
                    <div class="mb-6 text-center">
                        <h2
                            class="text-2xl font-bold text-[var(--color-text-primary)] tracking-wide"
                        >
                            MANAGE USER ROLES
                        </h2>
                        <div
                            class="mt-2 h-0.5 bg-gradient-to-r from-transparent via-[var(--color-border)] to-transparent"
                        ></div>
                    </div>

                    <div
                        v-if="modalLoading"
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

                    <div
                        v-else
                        class="rounded-xl overflow-hidden border border-[var(--color-border)] backdrop-blur-sm"
                    >
                        <div class="sticky top-0 z-10">
                            <table
                                class="w-full text-[var(--color-text-primary)] text-sm"
                            >
                                <thead>
                                    <tr
                                        class="text-sm uppercase tracking-wider text-[var(--color-text-primary)] pl-2"
                                    >
                                        <th class="px-5 py-2 text-left w-[15%]">
                                            Role ID
                                        </th>
                                        <th class="px-5 py-2 text-left w-[25%]">
                                            Description
                                        </th>
                                        <th
                                            class="px-5 py-2 text-center w-[10%]"
                                        >
                                            VIEW
                                        </th>
                                        <th
                                            class="px-5 py-2 text-center w-[10%]"
                                        >
                                            INSERT
                                        </th>
                                        <th
                                            class="px-5 py-2 text-center w-[10%]"
                                        >
                                            update
                                        </th>
                                        <th
                                            class="px-5 py-2 text-center w-[10%]"
                                        >
                                            delete
                                        </th>
                                        <th
                                            class="px-5 py-2 text-center w-[10%]"
                                        >
                                            print
                                        </th>
                                        <th
                                            class="px-5 py-2 text-center w-[10%]"
                                        >
                                            reprint
                                        </th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                        <div class="relative overflow-hidden">
                            <div
                                class="overflow-y-auto max-h-[410px] scrollbar-thin scrollbar-stable [scrollbar-gutter:stable]"
                            >
                                <table
                                    class="w-full text-[var(--color-text-primary)] text-sm"
                                >
                                    <tbody
                                        class="divide-y divide-[var(--color-border)]/50 rounded-xl"
                                    >
                                        <tr
                                            v-for="(role, index) in roles"
                                            :key="index"
                                            class="rounded-xl hover:bg-[var(--color-primary)]/20 transition-colors duration-150 group"
                                        >
                                            <td class="px-5 py-2 w-[15%]">
                                                {{ role.id }}
                                            </td>
                                            <td class="px-5 py-2 w-[25%]">
                                                {{ role.description }}
                                            </td>
                                            <td
                                                v-for="action in actionKeys"
                                                :key="action"
                                                class="text-center px-5 py-2 w-10%"
                                            >
                                                <label
                                                    class="relative inline-block w-5 h-5"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        v-model="
                                                            form.roles[role.id][
                                                                action
                                                            ]
                                                        "
                                                        :disabled="
                                                            !isActionEnabled(
                                                                role.id,
                                                                action
                                                            )
                                                        "
                                                        class="peer appearance-none w-5 h-5 cursor-pointer border-2 border-[var(--color-border)] rounded-sm bg-transparent checked:bg-[var(--color-primary)] checked:!border-[var(--color-primary)] focus:outline-none transition-colors duration-200 disabled:opacity-0 disabled:cursor-not-allowed"
                                                    />
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        viewBox="0 0 24 24"
                                                        class="absolute p-0.5 top-0 left-0 w-5 h-5 text-white hidden peer-checked:block pointer-events-none"
                                                        fill="currentColor"
                                                    >
                                                        <path
                                                            d="M9,20.42L2.79,14.21L5.62,11.38L9,14.77L18.88,4.88L21.71,7.71L9,20.42Z"
                                                        />
                                                    </svg>
                                                </label>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="pt-4 border-t border-white/10 flex justify-between gap-2"
                    >
                        <!-- Select All Button -->
                        <button
                            type="button"
                            @click="toggleSelectAll"
                            class="px-4 py-2 text-[var(--color-text-primary)] rounded-md font-medium transition relative overflow-hidden cursor-pointer"
                        >
                            Select All
                        </button>

                        <!-- Action Buttons -->
                        <div class="flex gap-3">
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
                                type="submit"
                                :disabled="form.processing"
                                class="submitButton group"
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
                                        >Saving...</span
                                    >
                                    <span v-else>Save Changes</span>
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
import { ref, reactive } from "vue";
import { useForm } from "@inertiajs/vue3";
import ToastAlertWarning from "../../Pages/Components/ToastAlertWarning.vue";
import { mdiClose, mdiNavigationVariantOutline } from "@mdi/js";

const props = defineProps({
    userId: Number,
    permissions: Object,
});

const modalLoading = ref(false);

const roles = ref([
    {
        id: "0101-CUST",
        description: "Customer Masterfile",
        enabledActions: ["can_view", "can_update"],
    },
    {
        id: "0102-USER",
        description: "User Masterfile",
        enabledActions: ["can_view", "can_insert", "can_update", "can_delete"],
    },
    {
        id: "0103-CHKR",
        description: "Checker Masterfile",
        enabledActions: ["can_view", "can_insert", "can_update", "can_delete"],
    },
    {
        id: "0104-ITEM",
        description: "Item Masterfile",
        enabledActions: ["can_view", "can_insert", "can_update", "can_delete"],
    },
    {
        id: "0104-ITMPCK",
        description: "Item Pricing & Packing Masterfile",
        enabledActions: ["can_view", "can_update"],
    },
    {
        id: "0105-ADJRS",
        description: "Adjustment Reason Setup Masterfile",
        enabledActions: ["can_view", "can_insert", "can_update", "can_delete"],
    },

    {
        id: "0106-CAB",
        description: "Cash in Bank Masterfile",
        enabledActions: ["can_view", "can_insert", "can_update", "can_delete"],
    },
    {
        id: "0107-CIT",
        description: "Charge Invoice Type Masterfile",
        enabledActions: ["can_view", "can_insert", "can_update", "can_delete"],
    },
    {
        id: "0108-PCKT",
        description: "Packing Type Masterfile",
        enabledActions: ["can_view", "can_insert", "can_update", "can_delete"],
    },
    {
        id: "0109-SAMNT",
        description: "Shortage Amount Masterfile",
        enabledActions: ["can_view", "can_insert", "can_update", "can_delete"],
    },
    {
        id: "0201-CIT",
        description: "Charge Invoice Transaction",
        enabledActions: ["can_view", "can_insert", "can_print", "can_reprint"],
    },
    {
        id: "0202-ADT",
        description: "Adjustment Transaction",
        enabledActions: ["can_view", "can_insert", "can_print", "can_reprint"],
    },
    {
        id: "0203-PAYT",
        description: "Payment Transaction",
        enabledActions: ["can_view", "can_insert", "can_print", "can_reprint"],
    },
    {
        id: "0204-BGBLT",
        description: "AR Beg Bal Transaction",
        enabledActions: ["can_view", "can_insert"],
    },
    {
        id: "0301-GNRPRT",
        description: "Generate Report",
        enabledActions: ["can_view"],
    },
    {
        id: "0302-CUSLED",
        description: "Customer Ledger",
        enabledActions: ["can_view"],
    },
    {
        id: "0401-CHKCLR",
        description: "Check Clearing",
        enabledActions: ["can_view", "can_insert", "can_print", "can_reprint"],
    },
    {
        id: "0402-WHTCLR",
        description: "WHT Clearing",
        enabledActions: ["can_view", "can_insert", "can_print", "can_reprint"],
    },
    {
        id: "0403-CNCLPY",
        description: "Cancel Payment",
        enabledActions: ["can_view", "can_insert"],
    },
    {
        id: "0404-EXPRTGL",
        description: "Export To GL",
        enabledActions: ["can_view", "can_update"],
    },
    {
        id: "NOTIFICATIONS",
        description: "Notifications Create",
        enabledActions: ["can_insert"],
    },
    {
        id: "MANAGERKEY",
        description: "Managers Key Access",
        enabledActions: ["can_insert"],
    },
]);

const actionKeys = [
    "can_view",
    "can_insert",
    "can_update",
    "can_delete",
    "can_print",
    "can_reprint",
];

const isActionEnabled = (roleId, action) => {
    const role = roles.value.find((r) => r.id === roleId);
    return role?.enabledActions?.includes(action) || false;
};
// Set up default permissions
const permissions = reactive({});
roles.value.forEach((role) => {
    modalLoading.value = true;

    permissions[role.id] = {};
    actionKeys.forEach((action) => {
        const value = props.permissions?.[role.id]?.[action];
        permissions[role.id][action] = value === 1;
    });
    modalLoading.value = false;
});

const form = useForm({ roles: reactive(permissions) });

const emit = defineEmits(["close", "closeSuccess"]);
const closeModal = () => {
    emit("close");
};

// Select All toggle logic
const toggleSelectAll = () => {
    const allChecked = roles.value.every((role) =>
        actionKeys.every(
            (action) =>
                !isActionEnabled(role.id, action) || form.roles[role.id][action]
        )
    );

    roles.value.forEach((role) => {
        actionKeys.forEach((action) => {
            if (isActionEnabled(role.id, action)) {
                form.roles[role.id][action] = !allChecked;
            }
        });
    });
};

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
    form.post(route("assign.role.permissions", { user: props.userId }), {
        preserveScroll: true,
        onSuccess: () => {
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
</script>
