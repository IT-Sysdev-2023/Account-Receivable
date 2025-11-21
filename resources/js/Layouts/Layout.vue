<template>
    <Transition name="fade" appear>
        <div class="min-h-screen bg-[var(--color-bg-primary)] text-[var(--color-text-primary)]">
            <ToastAlert :show="showToast" :message="toastMessage" />
            <ToastAlertWarning :show="showWToast" :message="toastWMessage" />
            <Notifications v-if="showNotifications" :show="showNotifications" @close="showNotifications = false" />
            <Messages v-if="showMessages" :show="showMessages" :users="users" @close="showMessages = false" />
            <div id="app" class="flex h-screen" v-cloak>
                <!-- Sidebar -->
                <aside
                    class="bg-[var(--color-bg-secondary)] border-r border-[var(--color-border)] backdrop-blur-sm flex flex-col z-30 relative shadow-[6px_0_12px_-2px_rgba(0,0,0,0.3)] transition-all duration-300 ease-in-out h-full"
                    :class="{
                        'w-60': !sidebarCollapsed,
                        'w-20': sidebarCollapsed,
                    }">
                    <!-- Header and Logo -->
                    <div class="h-[67px] p-2 backdrop-blur-sm flex items-center justify-between pl-4" :class="{
                        'pr-4': sidebarCollapsed,
                        'pr-6': !sidebarCollapsed,
                    }">
                        <img :src="'/storage/images/mflogo.png'" alt="Logo"
                            class="w-12 h-12 object-contain transition-all duration-300" />
                        <div class="text-xl font-extrabold whitespace-nowrap overflow-hidden transition-all duration-300 text-[var(--color-text-primary)]"
                            :class="{
                                'w-0 h-0 opacity-0': sidebarCollapsed,
                                'w-auto h-auto opacity-100 ml-2':
                                    !sidebarCollapsed,
                            }">
                            Marcela Farms
                        </div>
                    </div>

                    <!-- Scrollable Menu Section -->
                    <div class="flex-1 pl-4 scrollbar-thin scrollbar-thumb-[var(--color-primary)]/50 scrollbar-track-[var(--color-scrollbar-track)] scrollbar-stable [scrollbar-gutter:stable] scrollbar-thumb-rounded-full scrollbar-track-rounded-full"
                        :class="{
                            'overflow-y-auto overflow-x-hidden pr-2':
                                !sidebarCollapsed,
                            'pr-4': sidebarCollapsed,
                        }">
                        <hr class="border-[var(--color-border)] mb-2" />

                        <!-- User Info -->
                        <ul>
                            <li v-for="(menuUser, indexUser) in user_menus" :key="indexUser"
                                @mouseenter="handleMouseEnter(indexUser)" @mouseleave="handleMouseLeave(indexUser)"
                                class="relative">
                                <button
                                    class="text-left rounded space-x-2 hover:bg-[var(--color-primary)] transition-all ease-linear duration-200 cursor-pointer w-full flex items-center justify-between p-1 py-2"
                                    :class="{
                                        'bg-[var(--color-primary)]':
                                            activeUserMenu === indexUser,
                                    }" @click="toggleUserSubMenu(indexUser)">
                                    <div
                                        class="flex-shrink-0 flex justify-between items-center w-10 h-10 rounded-full overflow-hidden bg-[var(--color-bg-avatar)] border-2 border-[var(--color-primary)]">
                                        <img v-if="showImage" :src="profilePhotoUrl" alt="User"
                                            class="rounded-full w-10 h-10 object-cover" @error="showImage = false" />
                                        <div v-else
                                            class="w-10 h-10 flex items-center justify-center text-white text-sm font-semibold rounded-full">
                                            {{ userInitials }}
                                        </div>
                                    </div>

                                    <div class="font-semibold text-left truncate text-[var(--color-text-primary)]"
                                        :class="{
                                            'w-0 opacity-0': sidebarCollapsed,
                                            'w-auto opacity-100':
                                                !sidebarCollapsed,
                                        }">
                                        {{ firstName }}
                                    </div>

                                    <span :class="{
                                        'transition-transform duration-200': true,
                                        'rotate-180':
                                            activeUserMenu === indexUser,
                                        'rotate-0':
                                            activeUserMenu !== indexUser,
                                        'w-0 opacity-0': sidebarCollapsed,
                                        'w-auto opacity-100':
                                            !sidebarCollapsed,
                                    }">
                                        <ChevronDownIcon class="size-4 text-[var(--color-text-primary)]" />
                                    </span>
                                </button>

                                <!-- User Sub Menu -->
                                <transition enter-active-class="transition duration-300 ease-out"
                                    enter-from-class="opacity-0 transform -translate-y-2"
                                    enter-to-class="opacity-100 transform translate-y-0"
                                    leave-active-class="transition duration-200 ease-in"
                                    leave-from-class="opacity-100 transform translate-y-0"
                                    leave-to-class="opacity-0 transform -translate-y-2">
                                    <ul v-if="activeUserMenu === indexUser"
                                        class="bg-[var(--color-bg-secondary)] rounded-lg p-2 text-sm space-y-1" :class="{
                                            'absolute left-full top-0 ml-5 w-48 rounded-lg bg-[var(--color-bg-secondary)] border border-[var(--color-border)] overflow-hidden':
                                                sidebarCollapsed,
                                            'text-sm p-2 mt-1 relative':
                                                !sidebarCollapsed,
                                        }">
                                        <li v-for="(
subUser, subUserIndex
                                            ) in menuUser.subUserMenus" :key="subUserIndex">
                                            <Link :href="subUser.link"
                                                class="w-full text-left cursor-pointer block py-2 px-4 text-[var(--color-text-primary)] rounded hover:bg-[var(--color-primary)] relative overflow-hidden"
                                                :class="{
                                                    'bg-[var(--color-primary)] rounded':
                                                        activeUserSubmenu ===
                                                        subUser.link,
                                                }" :method="subUser.name === 'Logout'
                                                    ? 'post'
                                                    : ''
                                                    " :as="subUser.name === 'Logout'
                                                        ? 'button'
                                                        : 'a'
                                                        " @click="
                                                            handleLinkClick(
                                                                subUser,
                                                                indexUser,
                                                                subUser.link,
                                                                'Profile',
                                                                subUser.name
                                                            )
                                                            ">
                                            <span>{{ subUser.name }}</span>
                                            </Link>
                                        </li>
                                    </ul>
                                </transition>
                            </li>
                        </ul>

                        <hr class="border-[var(--color-border)] my-2" />

                        <div class="my-0.5 text-[var(--color-text-secondary)] font-semibold text-sm">
                            <span>MENU</span>
                        </div>

                        <!-- Navigation Menu -->
                        <nav class="flex-1">
                            <ul class="space-y-2">
                                <li>
                                    <Link :href="route('dashboard')"
                                        class="text-left rounded p-2 flex items-center space-x-2 transition-all ease-linear duration-200 cursor-pointer relative overflow-hidden text-[var(--color-text-primary)] hover:text-[var(--color-text-primary)] hover:bg-[var(--color-primary)] group"
                                        :class="{
                                            'bg-[var(--color-primary)]':
                                                activeMenu === 'dashboard' ||
                                                (realActiveMenu === null &&
                                                    activeSubmenu === ''),
                                        }" @click="
                                            setActive('dashboard', 'Dashboard')
                                            ">
                                    <RectangleGroupIcon
                                        class="icon flex-shrink-0 text-[var(--color-icon)] transition-colors duration-200 group-hover:text-[var(--color-icon-hovered)]"
                                        :class="{
                                            'text-[var(--color-icon-hovered)]':
                                                activeMenu ===
                                                'dashboard' ||
                                                (realActiveMenu === null &&
                                                    activeSubmenu === ''),
                                        }" />
                                    <span :class="{
                                        hidden: sidebarCollapsed,
                                    }">Dashboard</span>
                                    </Link>
                                </li>

                                <li v-for="(menu, index) in filteredMenus" :key="index"
                                    @mouseenter="handleMouseEnterMenu(index)" @mouseleave="handleMouseLeaveMenu(index)"
                                    class="relative">
                                    <button
                                        class="text-left rounded p-2 space-x-2 transition-all ease-linear duration-200 cursor-pointer w-full flex items-center justify-between relative overflow-hidden text-[var(--color-text-primary)] hover:bg-[var(--color-primary)] group"
                                        :class="{
                                            'bg-[var(--color-primary)]':
                                                activeMenu === index ||
                                                isSubmenuActive(index),
                                        }" @click="toggleSubMenu(index)">
                                        <div class="flex items-center">
                                            <component :is="iconComponents[menu.icon]" v-if="menu.icon"
                                                class="icon flex-shrink-0 text-[var(--color-icon)] transition-colors duration-200 group-hover:text-[var(--color-icon-hovered)]"
                                                :class="{
                                                    'text-[var(--color-icon-hovered)]':
                                                        activeMenu === index ||
                                                        isSubmenuActive(index),
                                                }" />
                                            <span :class="{
                                                hidden: sidebarCollapsed,
                                            }">{{ menu.name }}</span>
                                        </div>
                                        <span :class="{
                                            'transition-transform duration-200': true,
                                            'rotate-180':
                                                activeMenu === index,
                                            'rotate-0':
                                                activeMenu !== index,
                                            hidden: sidebarCollapsed,
                                        }">
                                            <ChevronDownIcon class="size-4" />
                                        </span>
                                    </button>

                                    <transition enter-active-class="transition duration-300 ease-out"
                                        enter-from-class="opacity-0 transform -translate-y-2"
                                        enter-to-class="opacity-100 transform translate-y-0"
                                        leave-active-class="transition duration-200 ease-in"
                                        leave-from-class="opacity-100 transform translate-y-0"
                                        leave-to-class="opacity-0 transform -translate-y-2">
                                        <ul v-if="activeMenu === index"
                                            class="bg-[var(--color-bg-secondary)] rounded-lg p-2 text-sm space-y-1"
                                            :class="{
                                                'absolute left-full top-0 ml-5 w-48 rounded-lg bg-[var(--color-bg-secondary)] border border-[var(--color-border)] backdrop-blur-sm overflow-hidden':
                                                    sidebarCollapsed,
                                                'text-sm p-2 mt-1 relative':
                                                    !sidebarCollapsed,
                                            }">
                                            <li>
                                                <div :class="{
                                                    hidden: !sidebarCollapsed,
                                                }">
                                                    <span
                                                        class="font-bold uppercase text-[var(--color-text-secondary)]">{{
                                                        menu.name }}</span>
                                                </div>
                                            </li>
                                            <li v-for="(
sub, subIndex
                                                ) in menu.subMenus" :key="subIndex">
                                                <Link :href="sub.link"
                                                    class="text-left py-2 px-4 rounded w-full flex relative overflow-hidden text-[var(--color-text-primary)] hover:bg-[var(--color-primary)]"
                                                    :class="{
                                                        'bg-[var(--color-primary)]':
                                                            activeSubmenu ===
                                                            sub.link,
                                                    }" @click="
                                                        setActiveSubmenu(
                                                            index,
                                                            sub.link,
                                                            menu.name,
                                                            sub.name
                                                        )
                                                        " v-if="canView(sub?.roleId)">
                                                <ArrowTurnDownRightIcon class="submenuicon flex-shrink-0" />
                                                <span>{{ sub.name }}</span>
                                                </Link>
                                            </li>
                                        </ul>
                                    </transition>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    <div class="p-4 shrink-0 space-y-2">
                        <button type="button" @click="showMessageModal()"
                            class="text-left rounded p-2 flex items-center space-x-2 transition-all ease-linear duration-200 cursor-pointer overflow-hidden relative text-[var(--color-text-primary)] hover:bg-[var(--color-primary)] group w-full">
                            <svg-icon type="mdi" :path="mdiMessage"
                                class="icon flex-shrink-0 text-[var(--color-icon)] transition-colors duration-200 group-hover:text-[var(--color-icon-hovered)]" />
                            <span :class="{ hidden: sidebarCollapsed }">Messages</span>

                            <span v-if="totalUnreadCount > 0"
                                class="absolute bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs transition-all"
                                :class="[
                                    sidebarCollapsed
                                        ? 'top-1 right-1'
                                        : 'top-3 right-3',
                                ]">
                                {{ totalUnreadCount }}
                            </span>
                        </button>
                        <button type="button" @click="showNotificationModal()"
                            class="text-left rounded p-2 flex items-center space-x-2 transition-all ease-linear duration-200 cursor-pointer overflow-hidden relative text-[var(--color-text-primary)] hover:bg-[var(--color-primary)] group w-full">
                            <svg-icon type="mdi" :path="mdiBell"
                                class="icon flex-shrink-0 text-[var(--color-icon)] transition-colors duration-200 group-hover:text-[var(--color-icon-hovered)]" />
                            <span :class="{ hidden: sidebarCollapsed }">Notifications</span>

                            <span v-if="unreadCount > 0"
                                class="absolute bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs transition-all"
                                :class="[
                                    sidebarCollapsed
                                        ? 'top-1 right-1'
                                        : 'top-3 right-3',
                                ]">
                                {{ unreadCount }}
                            </span>
                        </button>
                    </div>
                </aside>

                <!-- Main content -->
                <main class="flex-1 flex flex-col min-h-0 transition-all duration-300 ease-in-out">
                    <!-- Sticky Header -->
                    <header
                        class="sticky top-0 z-10 bg-[var(--color-bg-secondary)] backdrop-blur-sm p-2 border-b border-[var(--color-border)] shadow-[0_6px_12px_-2px_rgba(0,0,0,0.3)]">
                        <div class="flex justify-between items-center max-w-[1800px] mx-auto">
                            <div
                                class="relative flex justify-between w-full items-center px-1 pb-1 rounded-lg overflow-hidden">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-md flex-shrink-0 transition-all ease-linear duration-200 cursor-pointer hover:bg-[var(--color-primary)] group"
                                        @click="
                                            sidebarCollapsed = !sidebarCollapsed
                                            ">
                                        <Bars3Icon
                                            class="size-6 text-[var(--color-icon)] transition-colors duration-200 group-hover:text-[var(--color-icon-hovered)]" />
                                    </div>
                                    <h2 class="text-lg font-semibold text-[var(--color-text-primary)] truncate">
                                        {{ currentPageTitle }}
                                    </h2>
                                </div>

                                <!-- Switch database button  -->
                                <div class="relative flex items-center gap-10">
                                    <button @click="switchDatabaseModal = true"
                                        v-if="props.auth?.user?.role === 'Admin'"
                                        class="flex items-center gap-2 px-4 py-2 rounded-lg bg-[var(--color-bg-primary)] text-[var(--color-text-secondary)] dark:text-[var(--color-text-primary)] hover:bg-[var(--color-primary)]"
                                        title="Switch Business Unit">
                                        <span class="font-semibold">Switch BU</span>

                                        <!-- Icon -->
                                        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                            fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M12 7.205c4.418 0 8-1.165 8-2.602C20 3.165 16.418 2 12 2S4 3.165 4 4.603c0 1.437 3.582 2.602 8 2.602ZM12 22c4.963 0 8-1.686 8-2.603v-4.404c-.052.032-.112.06-.165.09a7.75 7.75 0 0 1-.745.387c-.193.088-.394.173-.6.253-.063.024-.124.05-.189.073a18.934 18.934 0 0 1-6.3.998c-2.135.027-4.26-.31-6.3-.998-.065-.024-.126-.05-.189-.073a10.143 10.143 0 0 1-.852-.373 7.75 7.75 0 0 1-.493-.267c-.053-.03-.113-.058-.165-.09v4.404C4 20.315 7.037 22 12 22Zm7.09-13.928a9.91 9.91 0 0 1-.6.253c-.063.025-.124.05-.189.074a18.935 18.935 0 0 1-6.3.998c-2.135.027-4.26-.31-6.3-.998-.065-.024-.126-.05-.189-.074a10.163 10.163 0 0 1-.852-.372 7.816 7.816 0 0 1-.493-.268c-.055-.03-.115-.058-.167-.09V12c0 .917 3.037 2.603 8 2.603s8-1.686 8-2.603V7.596c-.052.031-.112.059-.165.09a7.816 7.816 0 0 1-.745.386Z" />
                                        </svg>
                                    </button>


                                    <span v-if="selectedBu" class="font-semibold">{{ selectedBu }}</span>
                                    <button v-else class="flex gap-2 font-semibold items-center justify-center"
                                        @click="refreshPage">
                                        <svg class="w-6 h-6 text-[var(--color-text-primary)]" aria-hidden="true"
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                            viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M17.651 7.65a7.131 7.131 0 0 0-12.68 3.15M18.001 4v4h-4m-7.652 8.35a7.13 7.13 0 0 0 12.68-3.15M6 20v-4h4" />
                                        </svg>
                                        Click to refresh
                                    </button>

                                    <!-- Left Column: Day Strip + Date Info -->
                                    <!-- <div
                                        class="flex flex-col items-center gap-2"
                                    > -->
                                    <!-- Day Strip with Progress Indicator -->
                                    <!-- <div
                                            class="relative w-full flex justify-between px-1 gap-2"
                                        >
                                            <div
                                                v-for="(day, index) in dayNames"
                                                :key="day"
                                                :ref="
                                                    (el) =>
                                                        (dayRefs[index] = el)
                                                "
                                                class="text-xs text-center font-medium flex-1 transition-all duration-300"
                                                :class="{
                                                    'text-[var(--color-text-primary)] font-semibold scale-110':
                                                        index ===
                                                        currentDayIndex,
                                                    'text-[var(--color-text-secondary)]':
                                                        index !==
                                                        currentDayIndex,
                                                }"
                                            >
                                                {{ day }}
                                            </div> -->

                                    <!-- Highlight Bar -->
                                    <!-- <div
                                                class="absolute bottom-0 h-0.5 bg-[var(--color-primary)] transition-all duration-500 ease-out"
                                                :style="{
                                                    width: `${indicatorWidth}px`,
                                                    left: `${indicatorLeft}px`,
                                                }"
                                            ></div>
                                        </div> -->

                                    <!-- Date and Week Number -->
                                    <!-- <div
                                            class="flex items-center gap-2 text-xs font-medium text-[var(--color-text-primary)]"
                                        >
                                            <span>{{ formattedDate }}</span>
                                            <span
                                                class="h-1 w-1 rounded-full bg-[var(--color-text-primary)]"
                                            ></span>
                                            <span>Week {{ weekNumber }}</span>
                                        </div>
                                    </div> -->

                                    <!-- Right Column: Clock + Location -->
                                    <div class="flex flex-col items-center px-1">
                                        <!-- Live Clock -->
                                        <div class="relative flex items-center">
                                            <!-- Clock Digits -->
                                            <div
                                                class="text-2xl font-mono font-bold text-[var(--color-text-primary)] tracking-tighter">
                                                <span class="inline-block min-w-[1.5rem] text-center">{{ hours
                                                    }}</span>
                                                <span class="text-[var(--color-primary)] mx-1">:</span>
                                                <span class="inline-block min-w-[1.5rem] text-center">{{ minutes
                                                    }}</span>
                                                <span class="text-[var(--color-primary)] mx-1">:</span>
                                                <span class="inline-block min-w-[1.5rem] text-center">{{ seconds
                                                    }}</span>
                                            </div>

                                            <!-- AM/PM beside the clock -->
                                            <div class="ml-2 text-sm font-medium text-[var(--color-primary)]/90">
                                                {{ ampm }}
                                            </div>
                                        </div>

                                        <!-- Location Tags -->
                                        <div class="flex gap-2">
                                            <!-- Bohol -->
                                            <div
                                                class="text-[10px] font-medium text-[var(--color-text-primary)] flex items-center gap-1">
                                                <ClockIcon class="h-3 w-3 text-[var(--color-icon)]" />
                                                Bohol
                                            </div>

                                            <!-- Tagbilaran -->
                                            <div
                                                class="text-[10px] font-medium text-[var(--color-text-primary)] flex items-center gap-1">
                                                <ClockIcon class="h-3 w-3 text-[var(--color-icon)]" />
                                                Tagbilaran
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </header>

                    <!-- Content Area -->
                    <div
                        class="flex-1 overflow-y-auto pl-5 pr-2 pt-2 pb-6 scrollbar-thin rounded-xl scrollbar-thumb-[var(--color-scrollbar-track)] scrollbar-track-[var(--color-scrollbar-track)] scrollbar-stable [scrollbar-gutter:stable] scrollbar-thumb-rounded-full scrollbar-track-rounded-full">
                        <div class="max-w-[1800px] mx-auto">
                            <slot />
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </Transition>
    <SwitchDatabase :show="switchDatabaseModal" @close="switchDatabaseModal = false" @success="success"
        @error="errorMessage" />
</template>

<script setup>
import {
    computed,
    ref,
    onMounted,
    onBeforeUnmount,
    watch,
    nextTick,
    onUnmounted,
} from "vue";
import { usePage, Link } from "@inertiajs/vue3";
import axios from "axios";
import { route } from "../../../vendor/tightenco/ziggy/src/js";
import {
    ArrowTurnDownRightIcon,
    RectangleGroupIcon,
    Square3Stack3DIcon,
    DocumentCurrencyDollarIcon,
    ClipboardDocumentListIcon,
    Cog6ToothIcon,
    ChevronDownIcon,
    Bars3Icon,
    ClockIcon,
    InformationCircleIcon,
} from "@heroicons/vue/24/solid";
import useTheme from "../Pages/Composables/useTheme";
import usePermissions from "../Pages/Composables/usePermissions";
import { mdiBell } from "@mdi/js";
import Notifications from "../Modals/Notifications.vue";
import { mdiMessage } from "@mdi/js";
import Messages from "../Modals/Messages.vue";
import SwitchDatabase from "../Modals/SwitchDatabase.vue";
import ToastAlert from "../Pages/Components/ToastAlert.vue";
import ToastAlertWarning from "../Pages/Components/ToastAlertWarning.vue";



const selectedBu = ref([]);
const switchDatabaseModal = ref(false);
const showToast = ref(false);
const toastMessage = ref("");
const showWToast = ref(false);
const toastWMessage = ref("");
let toastTimeout = null;
let toastWTimeout = null;

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

const errorMessage = (message) => {
    showWarningToast(message);
}

const success = (message) => {
    showSuccessToast(message);
    setTimeout(() => {
        window.location.reload()
    }, 3000);
};

const refreshPage = () => {
    window.location.reload()
};

const currentActiveBu = async () => {
    try {
        const response = await axios.get(route('currentDatabase'));
        if (response.data.success) {
            selectedBu.value = response.data.business_unit;
        }
    } catch (err) {
        showWarningToast(err.response?.data?.error);
    }
};

onMounted(() => {
    currentActiveBu();
});


const { canView } = usePermissions();

const { props } = usePage();

const firstName = computed(() => {
    const nameParts = props.auth.user.name.split(" ");
    return nameParts.slice(1, 3).join(" ");
});

const dayNames = ["SUN", "MON", "TUE", "WED", "THU", "FRI", "SAT"];
const currentDayIndex = ref(0);
const hours = ref("");
const minutes = ref("");
const seconds = ref("");
const ampm = ref("");
const formattedDate = ref("");
const weekNumber = ref(0);

const dayRefs = ref([]);
const indicatorLeft = ref(0);
const indicatorWidth = ref(0);

// --- Update Time and Highlight ---
const updateDateTime = () => {
    const now = new Date();

    // Time
    const rawHours = now.getHours();
    hours.value = String(rawHours % 12 || 12).padStart(2, "0");
    minutes.value = String(now.getMinutes()).padStart(2, "0");
    seconds.value = String(now.getSeconds()).padStart(2, "0");
    ampm.value = rawHours >= 12 ? "PM" : "AM";

    // Date
    formattedDate.value = now
        .toLocaleDateString("en-US", {
            month: "short",
            day: "numeric",
            year: "numeric",
        })
        .toUpperCase();

    // Week
    const firstDayOfYear = new Date(now.getFullYear(), 0, 1);
    const pastDaysOfYear = (now - firstDayOfYear) / 86400000;
    weekNumber.value = Math.ceil(
        (pastDaysOfYear + firstDayOfYear.getDay() + 1) / 7
    );

    // Day highlight
    currentDayIndex.value = now.getDay();
};

// --- Update highlight position ---
const updateIndicator = () => {
    nextTick(() => {
        const el = dayRefs.value[currentDayIndex.value];
        if (el) {
            indicatorLeft.value = el.offsetLeft;
            indicatorWidth.value = el.offsetWidth + 1;
        }
    });
};

// --- Mount + Interval ---
let timeInterval;
onMounted(() => {
    updateDateTime();
    timeInterval = setInterval(updateDateTime, 1000);
});
onBeforeUnmount(() => {
    clearInterval(timeInterval);
});

// --- Reactively update indicator
watch(currentDayIndex, updateIndicator);
onMounted(updateIndicator);

//////////////////////////////////SCRIPT
// Component registration
const iconComponents = {
    Square3Stack3DIcon,
    DocumentCurrencyDollarIcon,
    ClipboardDocumentListIcon,
    Cog6ToothIcon,
    InformationCircleIcon,
};

//notif
const showNotifications = ref(false);
const notifications = ref([]);
const showMessages = ref(false);


const loading = ref(false);
const error = ref(null);
const channel = ref(null);
const page = usePage();
let channelInstance = null;

const showNotificationModal = () => {
    showNotifications.value = true;
};

const unreadCount = ref(0);

async function fetchNotifications() {
    const { data } = await axios.get(route("getNotificationsCount"));
    unreadCount.value = data.unread_count;
}

const setupWebSocketListener = () => {
    channelInstance = window.Echo.private(
        `notification-update.${props.auth.user.id}`
    )
        .listen("NotificationEvent", (data) => {
            fetchNotifications();
            if (channelInstance) {
                window.Echo.leave(channel.value);
                channelInstance = null;
            }
        })
        .error((err) => {
            console.error("WebSocket error:", err);
            error.value = "Connection lost. Please retry.";
            loading.value = false;
        });
};
onMounted(() => {
    fetchNotifications();
    setupWebSocketListener();
});
onUnmounted(() => {
    window.Echo.leaveChannel("notifs");
});

//message
const users = ref([]);
const echo = window.Echo;
const currentUser = page.props.auth.user;

const totalUnreadCount = computed(() => {
    return users.value.reduce((total, user) => {
        return total + (user.unread_count || 0);
    }, 0);
});

const fetchUsers = async () => {
    try {
        const { data } = await axios.get(route("messages.users"));
        users.value = data;
    } catch (error) {
        console.error("Error fetching users:", error);
        showWarningToast("Failed to load users");
    }
};

if (window.echoSubscribed === undefined) {
    window.echoSubscribed = false;
}

const showMessageModal = () => {
    showMessages.value = true;
};

onMounted(async () => {
    await fetchUsers();
    if (window.echoSubscribed) {
        return;
    }

    window.echoSubscribed = true;

    echo.join("users")
        .here((onlineUsers) => {
            onlineUsers.forEach((onlineUser) => {
                const userIndex = users.value.findIndex(
                    (u) => u.id === onlineUser.id
                );
                if (userIndex !== -1) {
                    users.value[userIndex].is_online = true;
                }
            });
        })
        .joining((user) => {
            const userIndex = users.value.findIndex((u) => u.id === user.id);
            if (userIndex !== -1) {
                users.value[userIndex].is_online = true;
            }
        })
        .leaving((user) => {
            const userIndex = users.value.findIndex((u) => u.id === user.id);
            if (userIndex !== -1) {
                users.value[userIndex].is_online = false;
                users.value[userIndex].last_seen = new Date().toISOString();
            }
        });

    // Private channel for messages
    echo.private(`user.${currentUser.id}`).listen(".MessageSent", (e) => {
        if (
            e.message.sender_id !== currentUser.id &&
            e.message.receiver_id === currentUser.id
        ) {
            const senderIndex = users.value.findIndex(
                (u) => u.id === e.message.sender_id
            );

            if (senderIndex !== -1) {
                // Initialize unread_count if it doesn't exist
                if (typeof users.value[senderIndex].unread_count !== "number") {
                    users.value[senderIndex].unread_count = 0;
                }

                // Increment unread count
                users.value[senderIndex].unread_count += 1;
            }
        }
    });
});

const markUserOffline = () => {
    const url = route("user.markOffline");
    const data = new FormData();
    data.append(
        "_token",
        document.querySelector('meta[name="csrf-token"]').content
    );

    navigator.sendBeacon(url, data);
};

// Runs when Vue layout unmounts (SPA navigation away)
onUnmounted(() => {
    if (!window.echoSubscribed) return;

    try {
        echo.leave(`user.${currentUser.id}`);
        echo.leave("users");
        window.echoSubscribed = false;

        markUserOffline();
    } catch (error) {
        console.error("Error cleaning up Echo in layout:", error);
    }
});

// Runs when tab is closed or browser is refreshed
window.addEventListener("beforeunload", markUserOffline);


// Reactive state
const sidebarCollapsed = ref(false);
const leaveTimeout = ref(null);
const leaveTimeoutMenu = ref(null);
const activeMenu = ref(null);
const activeSubmenu = ref("");
const activeUserMenu = ref(null);
const activeUserSubmenu = ref("");
// const pageTitle = ref("Dashboard");
const realActiveMenu = ref(null);

const menus = ref([
    {
        name: "Masterfile",
        icon: "Square3Stack3DIcon",
        subMenus: [
            { name: "Customers", link: route("customer"), roleId: "0101-CUST" },
            { name: "Users", link: route("user"), roleId: "0102-USER" },
            // { name: "Checkers", link: route("checker"), roleId: "0103-CHKR" },
            { name: "Item", link: route("item"), roleId: "0104-ITEM" },
            {
                name: "Adjustment Reason Setup",
                link: route("adjustmentreasonsetup"),
                roleId: "0105-ADJRS",
            },
            {
                name: "Cash in Bank",
                link: route("cashinbank"),
                roleId: "0106-CAB",
            },
            {
                name: "Charge Invoice Type",
                link: route("chargeinvoicetype"),
                roleId: "0107-CIT",
            },
            {
                name: "Packing Type",
                link: route("packingtype"),
                roleId: "0108-PCKT",
            },
            {
                name: "Shortage Amount",
                link: route("shortageamount"),
                roleId: "0109-SAMNT",
            },
        ],
    },
    {
        name: "Transactions",
        icon: "DocumentCurrencyDollarIcon",
        subMenus: [
            { name: "Invoice", link: route("invoice"), roleId: "0201-CIT" },
            {
                name: "Adjustment",
                link: route("adjustment"),
                roleId: "0202-ADT",
            },
            { name: "Payment", link: route("payment"), roleId: "0203-PAYT" },
            {
                name: "AR Beginning Balance",
                link: route("beginningbalance"),
                roleId: "0204-BGBLT",
            },
        ],
    },
    {
        name: "Reports",
        icon: "ClipboardDocumentListIcon",
        subMenus: [
            {
                name: "Generate Report",
                link: route("generatereport"),
                roleId: "0301-GNRPRT",
            },
            {
                name: "Customer Ledger",
                link: route("customerledger"),
                roleId: "0302-CUSLED",
            },
        ],
    },
    {
        name: "Utility",
        icon: "Cog6ToothIcon",
        subMenus: [
            {
                name: "Check Clearing",
                link: route("clearing"),
                roleId: "0401-CHKCLR",
            },
            {
                name: "WHT Clearing",
                link: route("withholdingtaxclearing"),
                roleId: "0402-WHTCLR",
            },
            {
                name: "Cancel Payment",
                link: route("cancelpayment"),
                roleId: "0403-CNCLPY",
            },
            {
                name: "Export to GL",
                link: route("exporttogl"),
                roleId: "0404-EXPRTGL",
            },
        ],
    },
    {
        name: "System Info",
        icon: "InformationCircleIcon",
        subMenus: [
            {
                name: "About Us",
                link: route("aboutus"),
                roleId: "AboutUs",
            },
            {
                name: "User Guide",
                link: route("userguide"),
                roleId: "AboutUs",
            },
        ],
    },
]);

const user_menus = ref([
    {
        subUserMenus: [
            { name: "My Profile", link: route("profile") },
            { name: "Logout", link: route("logout") },
        ],
    },
]);

// const canView = (roleId) => {
//     const perms = page.props.auth?.permissions || {};
//     return !!perms[roleId]?.can_view;
// };

const filteredMenus = computed(() =>
    menus.value.filter((m) => m?.subMenus?.some((sub) => canView(sub.roleId)))
);

// Methods
const toggleSubMenu = (index) => {
    activeMenu.value = activeMenu.value === index ? null : index;
    realActiveMenu.value = activeMenu.value === index ? index : null;
    localStorage.setItem("activeMenu", activeMenu.value);
};

const setActive = (menu, title) => {
    resetUserProfileMenu();
    activeMenu.value = menu;
    activeSubmenu.value = "";
    realActiveMenu.value = menu;
    // pageTitle.value = title;
    localStorage.setItem("activeMenu", activeMenu.value);
    localStorage.setItem("pageTitle", "Dashboard");
    localStorage.removeItem("activeSubmenu");
};

const setActiveSubmenu = (menuIndex, submenu, parentTitle, submenuTitle) => {
    resetUserProfileMenu();
    activeMenu.value = menuIndex;
    realActiveMenu.value = menuIndex;
    activeSubmenu.value = submenu;
    // pageTitle.value = `${parentTitle} > ${submenuTitle}`;
    localStorage.setItem("activeMenu", activeMenu.value);
    localStorage.setItem("activeSubmenu", activeSubmenu.value);
    localStorage.setItem("pageTitle", `${parentTitle} > ${submenuTitle}`);
};

const toggleUserSubMenu = (indexUser) => {
    activeUserMenu.value =
        activeUserMenu.value === indexUser ? null : indexUser;
    localStorage.setItem("activeUserMenu", activeUserMenu.value);
};

const setUserActive = (menuUser, title) => {
    resetMenu();
    activeUserMenu.value = menuUser;
    activeUserSubmenu.value = "";
    // pageTitle.value = title;
    localStorage.setItem("activeUserMenu", activeUserMenu.value);
    localStorage.setItem("pageTitle", "Profile");
    localStorage.removeItem("activeUserSubmenu");
};

const setUserActiveSubmenu = (
    menuUserIndex,
    submenuUser,
    parentTitle,
    submenuUserTitle
) => {
    resetMenu();
    activeUserMenu.value = menuUserIndex;
    activeUserSubmenu.value = submenuUser;
    // pageTitle.value = `${parentTitle} > ${submenuUserTitle}`;
    localStorage.setItem("activeUserMenu", activeUserMenu.value);
    localStorage.setItem("activeUserSubmenu", activeUserSubmenu.value);
    localStorage.setItem("pageTitle", `${parentTitle} > ${submenuUserTitle}`);
};

const handleLinkClick = (
    subUser,
    indexUser,
    submenuLink,
    menuUserName,
    subUserName
) => {
    if (subUserName === "Logout") {
        handleLogout(subUser);
    } else {
        setUserActiveSubmenu(indexUser, submenuLink, menuUserName, subUserName);
    }
};

const handleLogout = (subUser) => {
    // pageTitle.value = "";
    localStorage.removeItem("activeUserMenu");
    localStorage.removeItem("activeUserSubmenu");
    localStorage.removeItem("pageTitle");
    localStorage.removeItem("activeMenu");
    localStorage.removeItem("activeSubmenu");
    setActive("dashboard", "Dashboard");
    activeUserMenu.value = null;
};

const resetUserProfileMenu = () => {
    activeUserMenu.value = null;
    activeUserSubmenu.value = "";
    localStorage.removeItem("activeUserMenu");
    localStorage.removeItem("activeUserSubmenu");
};

const resetMenu = () => {
    activeMenu.value = null;
    activeSubmenu.value = "";
    localStorage.removeItem("activeMenu");
    localStorage.removeItem("activeSubmenu");
};

const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
};

const handleMouseEnter = (index) => {
    if (sidebarCollapsed.value) {
        if (leaveTimeout.value) {
            clearTimeout(leaveTimeout.value);
        }
        activeUserMenu.value = index;
    }
};

const handleMouseLeave = (index) => {
    if (sidebarCollapsed.value) {
        if (leaveTimeout.value) {
            clearTimeout(leaveTimeout.value);
        }
        leaveTimeout.value = setTimeout(() => {
            if (activeUserMenu.value === index) {
                activeUserMenu.value = null;
            }
        }, 300);
    }
};

const handleMouseEnterMenu = (index) => {
    if (sidebarCollapsed.value) {
        if (leaveTimeoutMenu.value) {
            clearTimeout(leaveTimeoutMenu.value);
        }
        activeMenu.value = index;
    }
};

const handleMouseLeaveMenu = (index) => {
    if (sidebarCollapsed.value) {
        if (leaveTimeoutMenu.value) {
            clearTimeout(leaveTimeoutMenu.value);
        }
        leaveTimeoutMenu.value = setTimeout(() => {
            if (activeMenu.value === index) {
                activeMenu.value = null;
            }
        }, 300);
    }
};

const currentPageTitle = computed(() => {
    const currentPath = page.url; // Use Inertia's reactive URL

    // Check dashboard first
    if (currentPath === "/dashboard") {
        return "Dashboard";
    }

    // Check main menus
    for (const menu of filteredMenus.value) {
        for (const sub of menu.subMenus) {
            const cleanedLink = sub.link.replace(
                /^https?:\/\/\d+\.\d+\.\d+\.\d+:\d+/,
                ""
            );
            if (cleanedLink === currentPath) {
                return `${menu.name} > ${sub.name}`;
            }
        }
    }

    // Check user menus
    for (const menuUser of user_menus.value) {
        for (const subUser of menuUser.subUserMenus) {
            if (subUser.link === currentPath) {
                return subUser.name === "Logout"
                    ? "Logout"
                    : `Profile > ${subUser.name}`;
            }
        }
    }

    // Fallback to localStorage title or default
    return localStorage.getItem("pageTitle") || "Dashboard";
});

// Lifecycle hooks
onMounted(() => {
    // SideBar Menu
    const savedMenu = localStorage.getItem("activeMenu");
    const savedSubmenu = localStorage.getItem("activeSubmenu");
    const savedPageTitle = localStorage.getItem("pageTitle");
    const currentPath = window.location.pathname;

    if (currentPath === "/dashboard") {
        setActive("dashboard", "Dashboard");
    } else {
        filteredMenus.value.forEach((menu, menuIndex) => {
            menu.subMenus.forEach((sub) => {
                const cleanedLink = sub.link.replace(
                    /^https?:\/\/\d+\.\d+\.\d+\.\d+:\d+/,
                    ""
                );

                if (cleanedLink === currentPath) {
                    setActiveSubmenu(menuIndex, sub.link, menu.name, sub.name);
                }
            });
        });

        if (!activeSubmenu.value) {
            if (savedMenu !== null && savedMenu !== "null") {
                activeMenu.value = isNaN(savedMenu)
                    ? savedMenu
                    : parseInt(savedMenu);
            }
            if (savedSubmenu) {
                activeSubmenu.value = savedSubmenu;
            }
            // if (savedPageTitle) {
            //     pageTitle.value = savedPageTitle;
            // }
            // pageTitle.value = currentPageTitle;
        }
    }

    // User Profile Menu
    const savedUserMenu = localStorage.getItem("activeUserMenu");
    const savedUserSubmenu = localStorage.getItem("activeUserSubmenu");
    const currentUserPath = window.location.pathname;

    user_menus.value.forEach((menuUser, menuUserIndex) => {
        menuUser.subUserMenus.forEach((subUser) => {
            if (subUser.link === currentUserPath) {
                setUserActiveSubmenu(
                    menuUserIndex,
                    subUser.link,
                    menuUser.name,
                    subUser.name
                );
            }
        });
    });

    if (!activeUserSubmenu.value) {
        if (savedUserMenu !== null && savedUserMenu !== "null") {
            activeUserMenu.value = isNaN(savedUserMenu)
                ? savedUserMenu
                : parseInt(savedUserMenu);
        }
        if (savedUserSubmenu) {
            activeUserSubmenu.value = savedUserSubmenu;
        }
        if (savedPageTitle) {
            // pageTitle.value = savedPageTitle;
        }
    }

    const savedSidebarState = localStorage.getItem("sidebarCollapsed");
    if (savedSidebarState !== null) {
        sidebarCollapsed.value = savedSidebarState === "true";
        activeMenu.value = null;
        activeUserMenu.value = null;
    }
});

// Watcher
watch(sidebarCollapsed, (newVal) => {
    localStorage.setItem("sidebarCollapsed", newVal);
    if (newVal) {
        activeMenu.value = null;
        activeUserMenu.value = null;
    }
});

//computed
const isSubmenuActive = computed(() => (index) => {
    return (
        filteredMenus.value[index].subMenus.some(
            (sub) => sub.link === activeSubmenu.value
        ) ||
        (realActiveMenu.value === index && activeSubmenu.value !== "")
    );
});

const showImage = ref(true);
const profilePhotoUrl = computed(() =>
    showImage.value ? `${route("profilePhoto")}?t=${Date.now()}` : ""
);
// Get user initials for avatar
const userInitials = computed(() => {
    return (
        firstName.value
            ?.split(" ")
            .map((name) => name[0])
            .join("")
            .toUpperCase() || ""
    );
});

const { setTheme, initTheme } = useTheme();

onMounted(() => {
    // Init theme on load
    initTheme();

    // Listen for changes from other tabs
    const handleStorageChange = (e) => {
        if (e.key === "theme" && e.newValue) {
            setTheme(e.newValue);
        }
    };

    window.addEventListener("storage", handleStorageChange);

    onBeforeUnmount(() => {
        window.removeEventListener("storage", handleStorageChange);
    });
});
</script>

<style scoped>
.fade-enter-active {
    transition: opacity 1.6s ease;
}

.fade-enter-from {
    opacity: 0;
}
</style>
