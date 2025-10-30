<template>
    <div v-if="show" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center px-4">
        <ToastAlertWarning :show="showToast" :message="toastMessage" />
        <transition @before-enter="beforeEnter" @enter="enter" @after-enter="afterEnter" @before-leave="beforeLeave"
            @leave="leave">
            <div v-if="isExpanded" ref="formElement"
                class="bg-[var(--color-bg-secondary)] text-[var(--color-text-primary)] w-full max-w-6xl h-[85vh] rounded-2xl border border-[var(--color-border)] shadow-2xl overflow-hidden flex">
                <!-- Sidebar - User List -->
                <div class="w-80 border-r border-[var(--color-border)] flex flex-col bg-[var(--color-bg-secondary)]">
                    <!-- Header -->
                    <div class="p-4 border-b border-[var(--color-border)]">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-2">
                                <div class="p-2 rounded-full bg-[var(--color-bg-avatar)]">
                                    <svg-icon type="mdi" :path="mdiMessageOutline" class="w-5 h-5 text-white" />
                                </div>
                                <h3 class="text-lg font-bold text-[var(--color-text-primary)] bg-clip-text">
                                    Messages
                                </h3>
                            </div>
                            <button @click="closeModal()"
                                class="p-2 rounded-full hover:bg-[var(--color-border)] transition-colors">
                                <XMarkIcon class="w-5 h-5" />
                            </button>
                        </div>

                        <!-- Search -->
                        <div class="relative">
                            <input v-model="searchQuery" type="text" placeholder="Search..." ref="searchInput"
                                class="w-full rounded-md px-4 py-2 text-[var(--color-text-primary)] border border-[var(--color-border)]"
                                :class="{
                                    '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10':
                                        filteredUsers.length === 0,
                                }" />
                            <button v-if="searchQuery" @click="clearSearch"
                                class="absolute top-1/2 right-2 transform -translate-y-1/2 text-[var(--color-text-primary)] hover:text-red-500">
                                <svg-icon type="mdi" :path="mdiClose" class="w-5 h-5 hover:text-red-500" />
                            </button>
                            <div v-else
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-[var(--color-text-secondary)]">
                                <svg-icon type="mdi" :path="mdiMagnify" size="20" />
                            </div>
                        </div>
                    </div>

                    <!-- Users List -->
                    <div
                        class="flex-1 overflow-y-auto scrollbar-thin scrollbar-thumb-[var(--color-scrollbar-track)] scrollbar-track-[var(--color-scrollbar-track)] scrollbar-stable [scrollbar-gutter:stable] scrollbar-thumb-rounded-full">
                        <div v-if="usersLoading" class="flex justify-center items-center py-8">
                            <svg width="30" height="30" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                                fill="var(--color-icon)">
                                <rect class="spinner_jCIR" x="1" y="6" width="2.8" height="12" />
                                <rect class="spinner_jCIR spinner_upm8" x="5.8" y="6" width="2.8" height="12" />
                                <rect class="spinner_jCIR spinner_2eL5" x="10.6" y="6" width="2.8" height="12" />
                                <rect class="spinner_jCIR spinner_Rp9l" x="15.4" y="6" width="2.8" height="12" />
                                <rect class="spinner_jCIR spinner_dy3W" x="20.2" y="6" width="2.8" height="12" />
                            </svg>
                        </div>
                        <div v-else class="p-2 space-y-1">
                            <div v-for="user in filteredUsers" :key="user.id" @click="selectUser(user)"
                                class="flex items-center p-3 rounded-lg cursor-pointer transition-all hover:bg-[var(--color-primary)]/20 group"
                                :class="{
                                    'bg-[var(--color-primary)]/20 border border-[var(--color-border)]':
                                        selectedUser?.id === user.id,
                                    'hover:scale-[1.02]':
                                        selectedUser?.id !== user.id,
                                }">
                                <!-- Avatar -->
                                <div class="relative flex-shrink-0">
                                    <div
                                        class="flex justify-between items-center w-10 h-10 rounded-full overflow-hidden bg-[var(--color-bg-avatar)] border-2 border-[var(--color-border)]">
                                        <img v-if="showImage" :src="profilePhotoUrl(user.name)" alt="User"
                                            class="rounded-full w-10 h-10 object-contain" @error="showImage = false" />
                                        <div v-else
                                            class="w-10 h-10 flex items-center justify-center text-white text-sm font-semibold rounded-full">
                                            {{
                                                user.name
                                                    .charAt(0)
                                                    .toUpperCase()
                                            }}
                                        </div>
                                    </div>
                                    <div v-if="user.is_online"
                                        class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-500 border-2 border-[var(--color-bg-secondary)] rounded-full">
                                    </div>
                                    <div v-else
                                        class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-gray-500 border-2 border-[var(--color-bg-secondary)] rounded-full">
                                    </div>
                                </div>

                                <!-- User Info -->
                                <div class="ml-3 flex-1 min-w-0">
                                    <p class="font-medium truncate transition-colors">
                                        {{ user.name }}
                                    </p>
                                    <p class="text-xs text-[var(--color-text-secondary)] truncate">
                                        {{ user.role || "User" }}
                                    </p>
                                </div>

                                <!-- Unread Count -->
                                <div v-if="user.unread_count > 0"
                                    class="bg-[var(--color-bg-avatar)] text-white text-xs rounded-full px-2 py-1 min-w-[25px] text-center font-medium">
                                    {{
                                        user.unread_count > 99
                                            ? "99+"
                                            : user.unread_count
                                    }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Chat Area -->
                <div class="flex-1 flex flex-col">
                    <!-- Chat Header -->
                    <div v-if="selectedUser"
                        class="p-4 border-b border-[var(--color-border)] bg-[var(--color-bg-secondary)]">
                        <div class="flex items-center space-x-3">
                            <div class="relative flex-shrink-0">
                                <div
                                    class="flex justify-between items-center w-10 h-10 rounded-full overflow-hidden bg-[var(--color-bg-avatar)] border-2 border-[var(--color-primary)]">
                                    <img v-if="showImage" :src="profilePhotoUrl(selectedUser.name)
                                        " alt="User" class="rounded-full w-10 h-10 object-contain"
                                        @error="showImage = false" />
                                    <div v-else
                                        class="w-10 h-10 flex items-center justify-center text-white text-sm font-semibold rounded-full">
                                        {{
                                            selectedUser.name
                                                .charAt(0)
                                                .toUpperCase()
                                        }}
                                    </div>
                                </div>
                                <div v-if="selectedUser.is_online"
                                    class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-500 border-2 border-[var(--color-bg-secondary)] rounded-full">
                                </div>
                                <div v-else
                                    class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-gray-500 border-2 border-[var(--color-bg-secondary)] rounded-full">
                                </div>
                            </div>
                            <div>
                                <h4 class="font-semibold">
                                    {{ selectedUser.name }}
                                </h4>
                                <p class="text-xs text-[var(--color-text-secondary)]">
                                    {{
                                        selectedUser.is_online
                                            ? "Online"
                                            : "Last seen " +
                                            formatLastSeen(
                                                selectedUser.last_seen
                                            )
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- No User Selected -->
                    <div v-else class="flex-1 flex items-center justify-center bg-[var(--color-bg-primary)]">
                        <div class="text-center">
                            <div
                                class="w-20 h-20 mx-auto mb-4 rounded-full bg-[var(--color-bg-avatar)] flex items-center justify-center">
                                <svg-icon type="mdi" :path="mdiMessageOutline" class="w-10 h-10 text-white" />
                            </div>
                            <h3 class="text-lg font-medium text-[var(--color-text-primary)] mb-2">
                                Start a conversation
                            </h3>
                            <p class="text-[var(--color-text-secondary)]">
                                Select a user from the list to start messaging
                            </p>
                        </div>
                    </div>

                    <!-- Messages Area -->
                    <div v-if="selectedUser" class="flex-1 flex flex-col">
                        <!-- Messages List -->
                        <div ref="messagesContainer"
                            class="flex-1 overflow-y-auto p-4 space-y-4 bg-[var(--color-bg-primary)] scrollbar-thin scrollbar-thumb-[var(--color-scrollbar-track)] scrollbar-track-[var(--color-scrollbar-track)] scrollbar-stable [scrollbar-gutter:stable] scrollbar-thumb-rounded-full transition-all duration-150 ease-out"
                            :style="{ maxHeight: messagesMaxHeight }">
                            <div v-if="messagesLoading" class="flex justify-center items-center py-8 w-full h-full">
                                <svg width="30" height="30" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                                    fill="var(--color-icon)">
                                    <rect class="spinner_jCIR" x="1" y="6" width="2.8" height="12" />
                                    <rect class="spinner_jCIR spinner_upm8" x="5.8" y="6" width="2.8" height="12" />
                                    <rect class="spinner_jCIR spinner_2eL5" x="10.6" y="6" width="2.8" height="12" />
                                    <rect class="spinner_jCIR spinner_Rp9l" x="15.4" y="6" width="2.8" height="12" />
                                    <rect class="spinner_jCIR spinner_dy3W" x="20.2" y="6" width="2.8" height="12" />
                                </svg>
                            </div>
                            <div v-else-if="messages.length === 0"
                                class="flex-1 flex items-center justify-center bg-[var(--color-bg-primary)] w-full h-full">
                                <div class="text-center">
                                    <div
                                        class="w-20 h-20 mx-auto mb-4 rounded-full bg-[var(--color-bg-avatar)] flex items-center justify-center">
                                        <svg-icon type="mdi" :path="mdiHandHeart" class="w-10 h-10 text-white" />
                                    </div>
                                    <h3 class="text-lg font-medium text-[var(--color-text-primary)] mb-2">
                                        No messages yet. Start the conversation!
                                    </h3>
                                    <p class="text-[var(--color-text-secondary)]">
                                        Every connection begins with a simple
                                        hello
                                    </p>
                                </div>
                            </div>
                            <div v-else>
                                <div v-for="message in messages" :key="message.id" class="flex" :class="{
                                    'justify-end':
                                        message.sender_id ===
                                        currentUser.id,
                                }">
                                    <div class="max-w-[70%] group flex flex-col" :class="message.sender_id === currentUser.id
                                        ? 'items-end ml-auto'
                                        : 'items-start mr-auto'
                                        ">
                                        <!-- Message Bubble -->
                                        <div class="inline-block px-4 py-2 rounded-2xl shadow-sm" :class="{
                                            'bg-[var(--color-bg-avatar)] text-white':
                                                message.sender_id ===
                                                currentUser.id,
                                            'bg-[var(--color-bg-secondary)] border border-[var(--color-border)]':
                                                message.sender_id !==
                                                currentUser.id,
                                        }">
                                            <p class="text-sm">
                                                {{ message.content }}
                                            </p>
                                        </div>

                                        <!-- Timestamp -->
                                        <div class="flex text-xs text-[var(--color-text-secondary)] mt-1 transition-opacity"
                                            :class="{
                                                'justify-end gap-1 items-center':
                                                    message.sender_id ===
                                                    currentUser.id,
                                                'opacity-100':
                                                    shouldShowTimestamp(
                                                        message
                                                    ),
                                                'opacity-0 group-hover:opacity-100':
                                                    !shouldShowTimestamp(
                                                        message
                                                    ),
                                            }">
                                            <span>
                                                {{
                                                    message.sender_id ===
                                                        currentUser.id &&
                                                        message.id !==
                                                        lastSeenMessage?.id &&
                                                        !message.read_at
                                                        ? "Sent"
                                                        : message.sender_id ===
                                                            currentUser.id &&
                                                            message.id ===
                                                            lastSeenMessage?.id
                                                            ? "Seen"
                                                            : "Received"
                                                }}
                                                {{
                                                    formatMessageTime(
                                                        message.created_at
                                                    )
                                                }}
                                            </span>

                                            <!-- Seen avatar -->
                                            <transition name="slide-down">
                                                <div v-if="
                                                    message.id ===
                                                    lastSeenMessage?.id
                                                "
                                                    class="flex justify-center items-center w-4 h-4 rounded-full overflow-hidden bg-[var(--color-bg-avatar)] border-2 border-[var(--color-border)]">
                                                    <img v-if="showImage" :src="profilePhotoUrl(
                                                        selectedUser.name
                                                    )
                                                        " alt="User" class="rounded-full w-4 h-4 object-contain"
                                                        @error="
                                                            showImage = false
                                                            " />
                                                    <div v-else
                                                        class="w-4 h-4 flex items-center justify-center text-white text-sm font-semibold rounded-full">
                                                        {{
                                                            selectedUser.name
                                                                .charAt(0)
                                                                .toUpperCase()
                                                        }}
                                                    </div>
                                                </div>
                                            </transition>
                                        </div>
                                    </div>
                                </div>

                                <!-- Typing Indicator -->
                                <div v-if="isTyping && userTyping" class="flex">
                                    <div class="max-w-[70%] mr-auto">
                                        <div
                                            class="px-4 py-2 rounded-2xl bg-[var(--color-bg-secondary)] border border-[var(--color-border)]">
                                            <div class="flex items-center space-x-1">
                                                <div class="flex space-x-1">
                                                    <div
                                                        class="w-2 h-2 bg-[var(--color-border)] rounded-full animate-bounce">
                                                    </div>
                                                    <div class="w-2 h-2 bg-[var(--color-border)] rounded-full animate-bounce"
                                                        style="
                                                            animation-delay: 0.1s;
                                                        "></div>
                                                    <div class="w-2 h-2 bg-[var(--color-border)] rounded-full animate-bounce"
                                                        style="
                                                            animation-delay: 0.2s;
                                                        "></div>
                                                </div>
                                                <span class="text-xs text-[var(--color-text-secondary)] ml-2">{{
                                                    userTyping.name }} is
                                                    typing...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Message Input -->
                        <div class="p-4 border-t border-[var(--color-border)] bg-[var(--color-bg-secondary)]">
                            <form @submit.prevent="sendMessage" class="flex items-end space-x-3">
                                <div class="flex-1">
                                    <div class="relative">
                                        <div class="relative">
                                            <textarea ref="messageInput" v-model="newMessage"
                                                @keydown.enter.exact.prevent="
                                                    sendMessage
                                                " @keydown.shift.enter="
                                                    newMessage += '\n'
                                                    " @input="handleInput" placeholder="Type a message..." rows="1"
                                                class="w-full px-4 py-3 rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] resize-none focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all overflow-hidden"
                                                style="
                                                    min-height: 44px;
                                                    max-height: 120px;
                                                    line-height: 1.25;
                                                    field-sizing: content;
                                                    overflow-wrap: break-word;
                                                    word-wrap: break-word;
                                                    word-break: break-word;
                                                    white-space: pre-wrap;
                                                "></textarea>
                                        </div>
                                        <!-- <div
                                            class="absolute right-3 bottom-3 text-xs text-[var(--color-text-secondary)]"
                                        >
                                            {{ newMessage.length }}/1000
                                        </div> -->
                                    </div>
                                </div>
                                <button type="submit" :disabled="!newMessage.trim() ||
                                    sending ||
                                    messagesLoading
                                    "
                                    class="p-3 rounded-full bg-[var(--color-bg-avatar)] text-white disabled:opacity-50 disabled:cursor-not-allowed transition-all transform hover:scale-105 disabled:hover:scale-100">
                                    <PaperAirplaneIcon v-if="!sending" class="w-5 h-5 transform" />
                                    <div v-else
                                        class="animate-spin w-5 h-5 border-2 border-white border-t-transparent rounded-full">
                                    </div>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick, onUnmounted } from "vue";
import axios from "axios";
import { usePage } from "@inertiajs/vue3";
import Echo from "laravel-echo";
import Pusher from "pusher-js";
import {
    mdiMessage,
    mdiMessageOutline,
    mdiMagnify,
    mdiClose,
    mdiHandHeart,
} from "@mdi/js";
import {
    XMarkIcon,
    MagnifyingGlassIcon,
    PaperAirplaneIcon,
} from "@heroicons/vue/24/outline";
import { route } from "../../../vendor/tightenco/ziggy/src/js";
import ToastAlertWarning from "../Pages/Components/ToastAlertWarning.vue";

const props = defineProps({
    show: Boolean,
    users: Array,
});

const emit = defineEmits(["close"]);

// Get current user from Inertia
const page = usePage();
const currentUser = computed(() => page.props.auth.user);

// State
const showToast = ref(false);
const toastMessage = ref("");
const users = ref([]);
const selectedUser = ref(null);
const messages = ref([]);
const newMessage = ref("");
const searchQuery = ref("");
const usersLoading = ref(false);
const messagesLoading = ref(false);
const sending = ref(false);
const messagesContainer = ref(null);
const messageInput = ref(null);
const inputHeight = ref(44);
const isTyping = ref(false);
const userTyping = ref(null);
const searchInput = ref(null);
let typingTimer = null;

// Echo instance for real-time messaging
let echo = null;
let privateChannel = null;

const showImage = ref(true);
const profilePhotoUrl = (name) => {
    return route("userPhoto", name);
};

const messagesMaxHeight = computed(() => {
    // Base calculation: 85vh minus header, input area, and padding
    // Adjust the subtraction based on actual input height
    const baseSubtraction = 150; // Original fixed value
    const inputHeightDifference = inputHeight.value - 44; // Growth from base height
    const totalSubtraction = baseSubtraction + inputHeightDifference;
    return `calc(85vh - ${totalSubtraction}px)`;
});

// Computed
const filteredUsers = computed(() => {
    if (!searchQuery.value) return users.value;
    return users.value.filter((user) =>
        user.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});
const clearSearch = () => {
    searchQuery.value = ""; // Clear search input

    nextTick(() => {
        searchInput.value?.focus();
    });
};

// Initialize Laravel Echo
const initializeEcho = () => {
    if (window.Echo) {
        echo = window.Echo;
    } else {
        echo = new Echo({
            broadcaster: "reverb",
            key: import.meta.env.VITE_REVERB_APP_KEY,
            wsHost: import.meta.env.VITE_REVERB_HOST,
            wsPort: import.meta.env.VITE_REVERB_PORT || 8081, // Must match REVERB_PORT in .env
            forceTLS: false,
            enabledTransports: ["ws", "wss"],
        });
    }

    // Create the private channel for the current user
    const userChannel = echo.private(`user.${currentUser.value.id}`);

    // Listen for new messages
    userChannel.listen(".MessageSent", (e) => {
        // Always add the message if it involves the current user
        const isRelevantMessage =
            e.message.sender_id === currentUser.value.id ||
            e.message.receiver_id === currentUser.value.id;

        if (!isRelevantMessage) return;

        // If we're currently chatting with the other user, add to current conversation
        const otherUserId =
            e.message.sender_id === currentUser.value.id
                ? e.message.receiver_id
                : e.message.sender_id;

        if (selectedUser.value && selectedUser.value.id === otherUserId) {
            // Check if message already exists to prevent duplicates
            const messageExists = messages.value.some(
                (msg) => msg.id === e.message.id
            );
            if (!messageExists) {
                messages.value.push(e.message);
                nextTick(() => {
                    scrollToBottom();
                    // Mark as read if we received the message (not sent it)
                    if (e.message.sender_id !== currentUser.value.id) {
                        markMessagesAsRead();
                    }
                });
            }
        }

        // Update unread count if message is from someone else and we're not chatting with them
        if (e.message.sender_id !== currentUser.value.id) {
            const userIndex = users.value.findIndex(
                (u) => u.id === e.message.sender_id
            );
            if (
                userIndex !== -1 &&
                selectedUser.value?.id === e.message.sender_id
            ) {
                // users.value[userIndex].unread_count =
                //     (users.value[userIndex].unread_count || 0) + 1;
                selectedUser.value.unread_count = 0;
            }
        }
    });

    // Listen for message read receipts
    userChannel.listen(".MessageRead", (e) => {
        // Update read status for messages in current conversation
        if (
            selectedUser.value &&
            e.conversation_user_id === selectedUser.value.id
        ) {
            messages.value.forEach((message) => {
                if (
                    message.sender_id === currentUser.value.id &&
                    message.receiver_id === selectedUser.value.id
                ) {
                    message.read_at = e.read_at;
                }
            });
        }
    });

    // Listen for typing indicators via whisper
    userChannel.listenForWhisper("typing", (e) => {
        if (selectedUser.value && e.user.id === selectedUser.value.id) {
            if (e.typing) {
                userTyping.value = e.user;
                isTyping.value = true;
                nextTick(() => scrollToBottom());

                clearTimeout(typingTimer);
                typingTimer = setTimeout(() => {
                    isTyping.value = false;
                    userTyping.value = null;
                }, 3000);
            } else {
                isTyping.value = false;
                userTyping.value = null;
                clearTimeout(typingTimer);
            }
        }
    });
};

// Fetch users
const fetchUsers = async () => {
    usersLoading.value = true;
    try {
        const { data } = await axios.get(route("messages.users"));
        users.value = data;
    } catch (error) {
        console.error("Error fetching users:", error);
        showWarningToast("Failed to load users");
    } finally {
        usersLoading.value = false;
    }
};

// Select user and load messages
const selectUser = async (user) => {
    selectedUser.value = user;
    user.unread_count = 0; // Reset unread count

    if (messageInput.value) {
        messageInput.value.style.height = "44px";
        messageInput.value.style.overflowY = "hidden";
        inputHeight.value = 44;
    }
    newMessage.value = "";

    await fetchMessages();
    await markMessagesAsRead();
    // scrollToBottom();
};

// Fetch messages between current user and selected user
const fetchMessages = async () => {
    if (!selectedUser.value) return;

    messagesLoading.value = true;
    try {
        const { data } = await axios.get(
            route("messages.conversation", selectedUser.value.id)
        );
        messages.value = data;
        nextTick(() => scrollToBottom());
    } catch (error) {
        console.error("Error fetching messages:", error);
        showWarningToast("Failed to load messages");
    } finally {
        messagesLoading.value = false;
    }
};

// Send message
const sendMessage = async () => {
    if (!newMessage.value.trim() || !selectedUser.value || sending.value)
        return;

    sending.value = true;
    const messageContent = newMessage.value.trim();
    newMessage.value = "";

    if (messageInput.value) {
        messageInput.value.style.height = "44px";
        messageInput.value.style.overflowY = "hidden";
        inputHeight.value = 44;
    }

    try {
        const { data } = await axios.post(route("messages.send"), {
            receiver_id: selectedUser.value.id,
            content: messageContent,
        });

        // Add the message to current conversation immediately
        if (data.message) {
            const messageExists = messages.value.some(
                (msg) => msg.id === data.message.id
            );
            if (!messageExists) {
                messages.value.push(data.message);
                nextTick(() => scrollToBottom());
            }
        }
    } catch (error) {
        console.error("Error sending message:", error);
        showWarningToast("Failed to send message");
        newMessage.value = messageContent; // Restore message
        nextTick(() => handleInput());
    } finally {
        sending.value = false;
    }
};

// Mark messages as read
const markMessagesAsRead = async () => {
    if (!selectedUser.value) return;

    try {
        await axios.post(route("messages.markAsRead", selectedUser.value.id));
    } catch (error) {
        console.error("Error marking messages as read:", error);
    }
};

// Scroll to bottom of messages
const scrollToBottom = () => {
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop =
            messagesContainer.value.scrollHeight;
    }
};
watch(messages, async () => {
    await nextTick(); // wait until DOM updates
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop =
            messagesContainer.value.scrollHeight;
    }
});

// Format message time
const formatMessageTime = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;

    if (diff < 60000) return "Just now";
    if (diff < 3600000) return `${Math.floor(diff / 60000)}m ago`;
    if (diff < 86400000)
        return date.toLocaleTimeString("en-US", {
            hour: "2-digit",
            minute: "2-digit",
        });
    return date.toLocaleDateString("en-US", { month: "short", day: "numeric" });
};

// Format last seen
const formatLastSeen = (dateString) => {
    if (!dateString) return "recently";

    const date = new Date(dateString);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000); // difference in seconds

    if (diff < 0) return "recently";

    if (diff < 60) {
        const s = diff;
        return `${s} second${s === 1 ? "" : "s"} ago`;
    }

    if (diff < 3600) {
        const m = Math.floor(diff / 60);
        return `${m} minute${m === 1 ? "" : "s"} ago`;
    }

    if (diff < 86400) {
        const h = Math.floor(diff / 3600);
        return `${h} hour${h === 1 ? "" : "s"} ago`;
    }

    const d = Math.floor(diff / 86400);
    return `${d} day${d === 1 ? "" : "s"} ago`;
};

// Handle typing indicator
let typingTimeout = null;
const handleTyping = () => {
    if (!selectedUser.value) return;

    // Send typing event
    echo.private(`user.${selectedUser.value.id}`).whisper("typing", {
        user: currentUser.value,
        typing: true,
    });

    // Clear previous timeout
    clearTimeout(typingTimeout);

    // Stop typing after 1 second of no input
    typingTimeout = setTimeout(() => {
        echo.private(`user.${selectedUser.value.id}`).whisper("typing", {
            user: currentUser.value,
            typing: false,
        });
    }, 1000);
};
// Show warning toast
//WARNING TOAST
let toastTimeout = null;
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

// Close modal
const closeModal = () => {
    selectedUser.value = "";
    emit("close");
};

// Lifecycle
onMounted(() => {
    users.value = props.users;
    // fetchUsers();
    initializeEcho();
});

onUnmounted(() => { });

// Watch for modal visibility
watch(
    () => props.show,
    (newVal) => {
        // if (newVal) {
        //     fetchUsers();
        // } else {
        selectedUser.value = null;
        messages.value = [];
        // }
    }
);

const handleInput = () => {
    // Auto-expand textarea
    if (messageInput.value) {
        messageInput.value.style.height = "auto";

        const minHeight = 44;
        const maxHeight = 120;
        const scrollHeight = messageInput.value.scrollHeight;

        const newHeight = Math.min(
            Math.max(scrollHeight, minHeight),
            maxHeight
        );
        messageInput.value.style.height = newHeight + "px";

        inputHeight.value = newHeight;

        if (scrollHeight > maxHeight) {
            messageInput.value.style.overflowY = "auto";
        } else {
            messageInput.value.style.overflowY = "hidden";
        }
    }

    handleTyping(); // Call existing typing function
};

const shouldShowTimestamp = (message) => {
    const lastSent = getLastSentMessage.value;
    const lastReceived = getLastReceivedMessage.value;
    const lastSeen = lastSeenMessage.value;

    return (
        message.id === lastSent?.id ||
        message.id === lastReceived?.id ||
        message.id === lastSeen?.id
    );
};

const getLastSentMessage = computed(() => {
    return messages.value
        .filter((msg) => msg.sender_id === currentUser.value.id)
        .slice(-1)[0];
});

const getLastReceivedMessage = computed(() => {
    return messages.value
        .filter((msg) => msg.sender_id !== currentUser.value.id)
        .slice(-1)[0];
});

const lastSeenMessage = computed(() => {
    return messages.value
        .filter((msg) => msg.sender_id === currentUser.value.id && msg.read_at)
        .slice(-1)[0]; // get the last one
});

// Animation handlers
const formElement = ref(null);
const isExpanded = ref(true);

const beforeEnter = (el) => {
    el.style.transform = "scale(0.9)";
    el.style.opacity = "0";
};

const enter = (el) => {
    el.style.transition = "transform 300ms ease-out, opacity 300ms ease-out";
    el.style.transform = "scale(1)";
    el.style.opacity = "1";
};

const afterEnter = (el) => {
    el.style.transition = "";
};

const beforeLeave = (el) => {
    el.style.transition = "transform 300ms ease-in, opacity 300ms ease-in";
};

const leave = (el) => {
    el.style.transform = "scale(0.9)";
    el.style.opacity = "0";
};
</script>

<style scoped>
/* Auto-resize textarea */
textarea {
    resize: none;
    transition: height 0.15s ease-out;
    box-sizing: border-box;
}

/* .transition-all {
    transition-property: all;
} */

/* .duration-150 {
    transition-duration: 150ms;
}

.ease-out {
    transition-timing-function: cubic-bezier(0, 0, 0.2, 1);
} */

textarea::-webkit-scrollbar {
    width: 4px;
}

textarea::-webkit-scrollbar-track {
    background: transparent;
}

textarea::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 2px;
}

/* Animation transitions */
.v-enter-active,
.v-leave-active {
    transition: transform 300ms ease-in-out, opacity 300ms ease-in-out;
}

.v-enter-from {
    transform: scale(0.9);
    opacity: 0;
}

.v-leave-to {
    transform: scale(0.9);
    opacity: 0;
}

/* Typing indicator animation */
@keyframes bounce {

    0%,
    60%,
    100% {
        transform: translateY(0);
    }

    30% {
        transform: translateY(-10px);
    }
}

.animate-bounce {
    animation: bounce 1.4s infinite ease-in-out;
}

.slide-down-enter-active,
.slide-down-leave-active {
    transition: all 0.3s ease;
}

.slide-down-enter-from,
.slide-down-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
