<template>
    <div
        v-if="show"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center px-4"
    >
        <ToastAlertWarning :show="showToast" :message="toastMessage" />
        <transition
            @before-enter="beforeEnter"
            @enter="enter"
            @after-enter="afterEnter"
            @before-leave="beforeLeave"
            @leave="leave"
        >
            <div
                v-if="isExpanded"
                ref="formElement"
                class="p-4 bg-[var(--color-bg-secondary)] text-[var(--color-text-primary)] w-full max-w-md rounded-2xl border border-[var(--color-border)] shadow-xl"
            >
                <!-- Header -->
                <div
                    class="flex justify-between items-center mb-4 pb-3 border-b border-[var(--color-border)]"
                >
                    <div class="flex items-center space-x-2">
                        <div class="p-2 rounded-full bg-[var(--color-primary)]">
                            <svg-icon
                                type="mdi"
                                :path="mdiBell"
                                class="w-5 h-5 text-white"
                            />
                        </div>
                        <h3 class="text-lg font-bold">Notifications</h3>
                        <span
                            v-if="unreadCount > 0"
                            class="px-2 py-1 text-xs font-medium rounded-full bg-[var(--color-primary)] text-white"
                        >
                            {{ unreadCount }} new
                        </span>
                    </div>

                    <button
                        @click="closeModal()"
                        class="p-1 rounded-full transition-colors"
                    >
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>

                <!-- Notification List -->
                <div
                    class="max-h-[60vh] overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-[var(--color-scrollbar-track)] scrollbar-track-[var(--color-scrollbar-track)] scrollbar-stable [scrollbar-gutter:stable] scrollbar-thumb-rounded-full"
                >
                    <div
                        v-if="modalLoading"
                        class="flex justify-center items-center py-20"
                    >
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
                    <div v-else>
                        <div v-if="notifications.length > 0" class="space-y-3">
                            <div
                                v-for="notification in notifications"
                                :key="notification.id"
                                class="p-3 rounded-lg transition-all"
                                :class="{
                                    'bg-[var(--color-primary)]/10 border border-[var(--color-border)]':
                                        !notification.read,
                                    'hover:bg-[var(--color-primary)]/20':
                                        notification.read,
                                }"
                            >
                                <div class="flex items-start gap-3">
                                    <div
                                        v-if="!notification.read"
                                        class="mt-1.5 w-2 h-2 rounded-full bg-[var(--color-primary)] flex-shrink-0"
                                    ></div>
                                    <div class="flex-1">
                                        <p class="font-medium text-sm">
                                            {{ notification.message }}
                                        </p>
                                        <div
                                            class="flex justify-between items-center mt-1.5"
                                        >
                                            <span
                                                class="text-xs text-[var(--color-text-secondary)]"
                                            >
                                                {{
                                                    formatDate(
                                                        notification.notified_at
                                                    )
                                                }}
                                            </span>
                                            <button
                                                v-if="!notification.read"
                                                @click.stop="
                                                    markAsRead(notification.id)
                                                "
                                                class="text-xs text-[var(--color-text-primary)]/50 hover:underline"
                                            >
                                                Mark as read
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            v-else
                            class="flex flex-col items-center justify-center py-8 text-center"
                        >
                            <svg-icon
                                type="mdi"
                                :path="mdiBellOutline"
                                class="w-12 h-12 mb-3 text-[var(--color-text-secondary)]/50"
                            />
                            <p class="text-[var(--color-text-secondary)]">
                                No new notifications
                            </p>
                            <p
                                class="text-sm text-[var(--color-text-secondary)]/70 mt-1"
                            >
                                You're all caught up!
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div
                    v-if="notifications.length > 0"
                    class="flex justify-between items-center pt-3 mt-3 border-t border-[var(--color-border)]"
                >
                    <button
                        @click="markAllAsRead()"
                        class="text-sm text-[var(--color-text-primary)] hover:underline"
                    >
                        Mark all as read
                    </button>
                    <button
                        @click="clearAll()"
                        class="text-sm text-[var(--color-text-primary)] hover:text-[var(--color-text-secondary)]"
                    >
                        Clear all
                    </button>
                </div>
            </div>
        </transition>
        <button
            v-if="canInsert('0103-CHKR')"
            @click="showCreateForm = true"
            class="fixed bottom-6 right-6 p-3 bg-[var(--color-primary)] text-white rounded-full shadow-lg hover:bg-[var(--color-primary)]/90 transition-colors"
        >
            <svg-icon type="mdi" :path="mdiPlus" class="w-6 h-6" />
        </button>
        <div
            v-if="showCreateForm"
            class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center px-4"
        >
            <div
                class="p-6 bg-[var(--color-bg-secondary)] rounded-2xl border border-[var(--color-border)] w-full max-w-md"
            >
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">Create Notification</h3>
                    <button @click="closeCreateNotif()">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <form @submit.prevent="createNotification">
                    <div class="space-y-4">
                        <div>
                            <TextInput
                                label="Message"
                                type="textarea"
                                v-model="newNotification.message"
                                validation="no"
                            />
                        </div>
                        <div>
                            <div class="flex flex-col gap-2">
                                <label
                                    class="block text-sm font-medium text-[var(--color-text-secondary)]"
                                    >Notify To</label
                                >
                                <div
                                    class="w-full grid grid-cols-2 grid-rows-auto gap-4"
                                >
                                    <!-- Admin -->
                                    <label
                                        class="relative inline-flex items-center cursor-pointer"
                                    >
                                        <input
                                            type="checkbox"
                                            v-model="newNotification.role.admin"
                                            class="peer appearance-none w-5 h-5 border-2 border-[var(--color-border)] rounded-md checked:bg-[var(--color-bg-avatar)] checked:border-transparent focus:outline-none transition-colors duration-200"
                                        />
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            class="absolute p-0.5 top-0 left-0 w-5 h-5 hidden peer-checked:block pointer-events-none"
                                            fill="white"
                                        >
                                            <path
                                                d="M9,20.42L2.79,14.21L5.62,11.38L9,14.77L18.88,4.88L21.71,7.71L9,20.42Z"
                                            />
                                        </svg>
                                        <span class="ml-2 text-sm font-medium"
                                            >Admin</span
                                        >
                                    </label>

                                    <!-- Concession -->
                                    <label
                                        class="relative inline-flex items-center cursor-pointer"
                                    >
                                        <input
                                            type="checkbox"
                                            v-model="
                                                newNotification.role.invoicing
                                            "
                                            class="peer appearance-none w-5 h-5 border-2 border-[var(--color-border)] rounded-md checked:bg-[var(--color-bg-avatar)] checked:border-transparent focus:outline-none transition-colors duration-200"
                                        />
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            class="absolute p-0.5 top-0 left-0 w-5 h-5 hidden peer-checked:block pointer-events-none"
                                            fill="white"
                                        >
                                            <path
                                                d="M9,20.42L2.79,14.21L5.62,11.38L9,14.77L18.88,4.88L21.71,7.71L9,20.42Z"
                                            />
                                        </svg>
                                        <span class="ml-2 text-sm font-medium"
                                            >Invoicing</span
                                        >
                                    </label>

                                    <!-- External -->
                                    <label
                                        class="relative inline-flex items-center cursor-pointer"
                                    >
                                        <input
                                            type="checkbox"
                                            v-model="
                                                newNotification.role.accounting
                                            "
                                            class="peer appearance-none w-5 h-5 border-2 border-[var(--color-border)] rounded-md checked:bg-[var(--color-bg-avatar)] checked:border-transparent focus:outline-none transition-colors duration-200"
                                        />
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            class="absolute p-0.5 top-0 left-0 w-5 h-5 hidden peer-checked:block pointer-events-none"
                                            fill="white"
                                        >
                                            <path
                                                d="M9,20.42L2.79,14.21L5.62,11.38L9,14.77L18.88,4.88L21.71,7.71L9,20.42Z"
                                            />
                                        </svg>
                                        <span class="ml-2 text-sm font-medium"
                                            >Accounting</span
                                        >
                                    </label>

                                    <!-- Internal -->
                                    <label
                                        class="relative inline-flex items-center cursor-pointer"
                                    >
                                        <input
                                            type="checkbox"
                                            v-model="
                                                newNotification.role.bookkeeper
                                            "
                                            class="peer appearance-none w-5 h-5 border-2 border-[var(--color-border)] rounded-md checked:bg-[var(--color-bg-avatar)] checked:border-transparent focus:outline-none transition-colors duration-200"
                                        />
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            class="absolute p-0.5 top-0 left-0 w-5 h-5 hidden peer-checked:block pointer-events-none"
                                            fill="white"
                                        >
                                            <path
                                                d="M9,20.42L2.79,14.21L5.62,11.38L9,14.77L18.88,4.88L21.71,7.71L9,20.42Z"
                                            />
                                        </svg>
                                        <span class="ml-2 text-sm font-medium"
                                            >Bookkeeper</span
                                        >
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2"
                                >Schedule Time</label
                            >
                            <DateTimePicker
                                v-model="newNotification.notified_at"
                                placeholder="Select Date"
                                format="MM/DD/YYYY hh:mm tt"
                                validation="no"
                            />
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <!-- <button
                            type="button"
                            @click="closeCreateNotif()"
                            class="closeButton"
                        >
                            Cancel
                        </button> -->
                        <button type="submit" class="submitButton">
                            Create
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from "vue";
import axios from "axios";
import { mdiBell, mdiBellOutline, mdiPlus } from "@mdi/js";
import { XMarkIcon } from "@heroicons/vue/24/outline";
import { route } from "../../../vendor/tightenco/ziggy/src/js";
import TextInput from "../Pages/Components/TextInput.vue";
import DropdownInput from "../Pages/Components/DropdownInput.vue";
import DateTimePicker from "../Pages/Components/DateTimePicker.vue";
import ToastAlertWarning from "../Pages/Components/ToastAlertWarning.vue";
import usePermissions from "../Pages/Composables/usePermissions";

const props = defineProps({
    show: Boolean,
});

const emit = defineEmits(["close"]);

const { canInsert } = usePermissions();

const showToast = ref(false);
const toastMessage = ref("");
let toastTimeout = null;

const notifications = ref([]);
const modalLoading = ref(false);
const showCreateForm = ref(false);
const newNotification = ref({
    message: "",
    role: {
        admin: false,
        invoicing: false,
        accounting: false,
        bookkeeper: false,
    },
    notified_at: "",
});

const unreadCount = computed(() => {
    return notifications.value.filter((n) => !n.read).length;
});

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString("en-US", {
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}

async function fetchNotifications() {
    modalLoading.value = true;
    try {
        const { data } = await axios.get(route("getNotifications"));
        notifications.value = data;
    } catch (error) {
        console.log("Error : ", error);
        modalLoading.value = false;
    } finally {
        modalLoading.value = false;
    }
}

async function markAsRead(id) {
    await axios.post(route("markAsRead", id));
    const notification = notifications.value.find((n) => n.id === id);
    if (notification) notification.read = true;
}

async function markAllAsRead() {
    await axios.post(route("markAllAsRead"));
    notifications.value.forEach((n) => (n.read = true));
}

async function clearAll() {
    await axios.delete(route("clearAll"));
    notifications.value = [];
}

async function createNotification() {
    if (!newNotification.value.message) {
        showWarningToast("Please enter a message");
        return;
    }

    // Check if at least one role is selected
    const roles = newNotification.value.role;
    const isRoleSelected = Object.values(roles).some((role) => role === true);

    if (!isRoleSelected) {
        showWarningToast("Please select at least one role");
        return;
    }

    if (!newNotification.value.notified_at) {
        showWarningToast("Please select date and time");
        return;
    }

    try {
        const { data } = await axios.post(route("createNotifications"), {
            message: newNotification.value.message,
            roles: newNotification.value.role,
            notified_at: newNotification.value.notified_at || undefined,
        });

        // data.notifications.forEach((notification) => {
        //     notifications.value.unshift(notification);
        // });

        // showCreateForm.value = false;
        // newNotification.value = {
        //     message: "",
        //     role: {
        //         admin: false,
        //         invoicing: false,
        //         accounting: false,
        //         bookkeeper: false,
        //     },
        //     notified_at: "",
        // };

        closeCreateNotif();
        fetchNotifications();
    } catch (error) {
        console.error("Error creating notification:", error);
    }
}

function closeCreateNotif() {
    showCreateForm.value = false;
    newNotification.value.notified_at = "";
    newNotification.value.message = "";
    newNotification.value.role = "";
}

const showWarningToast = (message) => {
    toastMessage.value = message;
    showToast.value = false;
    if (toastTimeout) clearTimeout(toastTimeout);

    setTimeout(() => {
        showToast.value = true;
    }, 0);

    toastTimeout = setTimeout(() => {
        showToast.value = false;
        toastTimeout = null;
    }, 3000);
};

function closeModal() {
    emit("close");
}

onMounted(fetchNotifications);

//#region ///////////////////////////////////ANIMATION////////////////////////////////////////
///////////////////////////////////////////////////FORM ANIMATION////////////////////////////
const formElement = ref(null);
const isExpanded = ref(true); // Control this with your v-if condition

// Handle dynamic content changes
watch(
    () => notifications.value.length, // Watch whatever causes your form to expand
    async () => {
        if (!formElement.value || !isExpanded.value) return;

        // Start transition
        formElement.value.style.transition = "height 300ms ease-in-out";
        formElement.value.style.overflow = "hidden";

        // Set current height
        const startHeight = formElement.value.scrollHeight;
        formElement.value.style.height = `${startHeight}px`;

        await nextTick();

        // Get new height after content change
        const endHeight = formElement.value.scrollHeight;

        // Only animate if height actually changed
        if (startHeight !== endHeight) {
            formElement.value.style.height = `${endHeight}px`;

            // Clean up after animation completes
            const onTransitionEnd = () => {
                formElement.value.style.height = "";
                formElement.value.style.overflow = "";
                formElement.value.style.transition = "";
                formElement.value.removeEventListener(
                    "transitionend",
                    onTransitionEnd
                );
            };

            formElement.value.addEventListener(
                "transitionend",
                onTransitionEnd
            );
        } else {
            // No height change needed
            formElement.value.style.height = "";
            formElement.value.style.overflow = "";
            formElement.value.style.transition = "";
        }
    },
    { deep: true }
);

// Initial expand animation
const beforeEnter = (el) => {
    el.style.height = "0";
    el.style.overflow = "hidden";
};

const enter = (el) => {
    el.style.height = `${el.scrollHeight}px`;
};

const afterEnter = (el) => {
    el.style.height = "";
    el.style.overflow = "";
};

// Collapse animation
const beforeLeave = (el) => {
    el.style.height = `${el.scrollHeight}px`;
    el.style.overflow = "hidden";
};

const leave = (el) => {
    requestAnimationFrame(() => {
        el.style.height = "0";
    });
};
/////////////////////////TABLE ANIMATION/////////////////////////////////////
//#endregion
</script>
<style scoped>
/* Fallback for height transitions */
.v-enter-active,
.v-leave-active {
    transition: height 300ms ease-in-out;
}
</style>
