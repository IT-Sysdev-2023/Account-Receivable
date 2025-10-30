<template>
    <div
        v-if="show"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center px-4"
    >
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
                message="DO YOU WANT TO PRINT THE DOCUMENT?"
                @close="handleConfirm"
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
            <PdfPreviewModal
                v-if="showPdfModal"
                :show="showPdfModal"
                :apiEndpoint="apiRoute"
                :formData="pdfFormData"
                @closeSuccess="pdfPrintSuccess"
                @close="pdfPrintSuccess"
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
            <CustomerListModal
                v-if="showCustomerModal"
                :show="showCustomerModal"
                @close="showCustomerModal = false"
                @submit="handleSelectedCustomer"
            />
        </Transition>

        <ToastAlertWarning :show="showToast" :message="toastMessage" />

        <transition
            @before-enter="beforeEnter"
            @enter="enter"
            @after-enter="afterEnter"
            @before-leave="beforeLeave"
            @leave="leave"
        >
            <form
                v-if="isExpanded"
                ref="formElement"
                @submit.prevent="submit"
                class="bg-[var(--color-bg-secondary)] text-[var(--color-text-primary)] w-full max-w-7xl rounded-2xl shadow-lg shadow-[#131313a2] border border-[var(--color-border)] overflow-hidden"
            >
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
                <div v-else class="px-8 py-6">
                    <!-- Header -->
                    <div class="px-8 pb-4">
                        <h2 class="text-2xl font-bold text-center">
                            POST DATED CHECK & DATED CHECK CLEARING
                        </h2>
                        <div
                            class="mt-2 h-0.5 bg-gradient-to-r from-transparent via-[var(--color-border)] to-transparent"
                        ></div>
                    </div>

                    <div class="flex flex-col md:flex-col gap-4 px-4">
                        <div
                            class="w-full grid sm:grid-cols-1 md:grid-cols-3 gap-4"
                        >
                            <TextInput
                                label="Clearing Number"
                                v-model="form.clearing_no"
                                type="text"
                                readonly
                                :message="form.errors.clearing_no"
                            />
                            <TextInput
                                label="Transaction Date"
                                v-model="form.transaction_date"
                                type="date"
                                readonly
                                :message="form.errors.transaction_date"
                            />
                            <div class="mb-2">
                                <label
                                    class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2"
                                    >Clearing Date</label
                                >
                                <DatePicker
                                    v-model="form.clearing_date"
                                    placeholder="Select Date"
                                    format="MM/DD/YYYY"
                                    :message="form.errors.clearing_date"
                                />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label
                                    class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2"
                                    >Check Type</label
                                >
                                <div class="w-full flex gap-4">
                                    <!-- PDC Option -->
                                    <label
                                        class="w-full inline-flex items-center cursor-pointer group"
                                    >
                                        <input
                                            type="radio"
                                            v-model="form.check_type"
                                            value="Post Dated Check"
                                            class="hidden peer"
                                        />
                                        <div
                                            class="w-full relative flex items-center justify-center p-2"
                                        >
                                            <!-- Hover circle -->
                                            <div
                                                class="absolute -inset-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-[var(--color-icon)]"
                                                :class="{
                                                    'opacity-100':
                                                        form.check_type ===
                                                        'Post Dated Check',
                                                }"
                                            ></div>
                                            <!-- Radio button -->
                                            <div
                                                class="relative w-5 h-5 mr-2 rounded-full border-2 border-[var(--color-bg-avatar)] transition-colors z-10 group-hover:border-[var(--color-bg-secondary)]"
                                                :class="{
                                                    'border-[var(--color-bg-secondary)]':
                                                        form.check_type ===
                                                        'Post Dated Check',
                                                }"
                                            >
                                                <div
                                                    class="absolute inset-0 m-auto w-2.5 h-2.5 rounded-full bg-[var(--color-bg-secondary)] transition-opacity"
                                                    :class="{
                                                        'opacity-100':
                                                            form.check_type ===
                                                            'Post Dated Check',
                                                        'opacity-0':
                                                            form.check_type !==
                                                            'Post Dated Check',
                                                    }"
                                                ></div>
                                            </div>
                                            <span
                                                class="text-sm font-semibold text-[var(--color-text-primary)] z-10 group-hover:text-white transition-colors"
                                                :class="{
                                                    'text-white':
                                                        form.check_type ===
                                                        'Post Dated Check',
                                                }"
                                                >Post Dated Check</span
                                            >
                                        </div>
                                    </label>

                                    <!-- By Customer Option -->
                                    <label
                                        class="w-full inline-flex items-center cursor-pointer group"
                                    >
                                        <input
                                            type="radio"
                                            v-model="form.check_type"
                                            value="Dated Check"
                                            class="hidden peer"
                                        />
                                        <div
                                            class="w-full relative flex items-center justify-center p-2"
                                        >
                                            <!-- Hover circle -->
                                            <div
                                                class="absolute -inset-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-[var(--color-icon)]"
                                                :class="{
                                                    'opacity-100':
                                                        form.check_type ===
                                                        'Dated Check',
                                                }"
                                            ></div>
                                            <!-- Radio button -->
                                            <div
                                                class="relative w-5 h-5 mr-2 rounded-full border-2 border-[var(--color-bg-avatar)] transition-colors z-10 group-hover:border-[var(--color-bg-secondary)]"
                                                :class="{
                                                    'border-[var(--color-bg-secondary)]':
                                                        form.check_type ===
                                                        'Dated Check',
                                                }"
                                            >
                                                <div
                                                    class="absolute inset-0 m-auto w-2.5 h-2.5 rounded-full bg-[var(--color-bg-secondary)] transition-opacity"
                                                    :class="{
                                                        'opacity-100':
                                                            form.check_type ===
                                                            'Dated Check',
                                                        'opacity-0':
                                                            form.check_type !==
                                                            'Dated Check',
                                                    }"
                                                ></div>
                                            </div>
                                            <span
                                                class="text-sm font-semibold text-[var(--color-text-primary)] z-10 group-hover:text-white transition-colors"
                                                :class="{
                                                    'text-white':
                                                        form.check_type ===
                                                        'Dated Check',
                                                }"
                                                >Dated Check</span
                                            >
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <TextInput
                                label="Customer Code"
                                type="text"
                                v-model="form.customer_code"
                                @click="onCustomerClick()"
                                :message="form.errors.customer_code"
                                :readonly="!form.clearing_date"
                                :default-placeholder="'Click to Select'"
                                :modified-placeholder="'Select Clearing Date First'"
                                selectable="yes"
                            />
                            <TextInput
                                label="Customer Name"
                                type="text"
                                v-model="form.customer_name"
                                readonly
                                :message="form.errors.customer_name"
                            />
                        </div>
                        <div
                            class="w-full rounded-xl overflow-hidden border border-[var(--color-border)] backdrop-blur-sm pl-2"
                        >
                            <div class="sticky top-0 z-10 pr-2">
                                <table
                                    class="w-full text-[var(--color-text-primary)]"
                                >
                                    <thead
                                        class="border-b border-[var(--color-border)]/50 text-sm"
                                    >
                                        <tr>
                                            <th
                                                class="px-3 py-2 text-left w-[12%]"
                                            >
                                                PAYMENT NO
                                            </th>
                                            <th
                                                class="px-3 py-2 text-left w-[12%]"
                                            >
                                                CHECK NO
                                            </th>
                                            <th
                                                class="px-3 py-2 text-left w-[12%]"
                                            >
                                                DOCUMENT NO
                                            </th>
                                            <th
                                                class="px-3 py-2 text-left w-[12%]"
                                            >
                                                DUE DATE
                                            </th>
                                            <th
                                                class="px-3 py-2 text-center w-[12%]"
                                            >
                                                AMOUNT
                                            </th>
                                            <th
                                                class="px-3 py-2 text-center w-[15%]"
                                            >
                                                STATUS
                                            </th>
                                            <th
                                                class="px-3 py-2 text-center w-[25%]"
                                            >
                                                REMARKS
                                            </th>
                                        </tr>
                                    </thead>
                                </table>
                                <div class="relative overflow-hidden">
                                    <div
                                        class="max-h-72 overflow-y-auto relative scrollbar-thin scrollbar-thumb-[var(--color-scrollbar-track)] scrollbar-track-[var(--color-primary)]/20 scrollbar-stable [scrollbar-gutter:stable] scrollbar-thumb-rounded-full"
                                    >
                                        <table
                                            class="w-full text-[var(--color-text-primary)] text-sm"
                                        >
                                            <tbody v-if="isLoading">
                                                <tr>
                                                    <td
                                                        colspan="7"
                                                        class="text-center py-8"
                                                    >
                                                        <div
                                                            class="flex justify-center items-center"
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
                                                    </td>
                                                </tr>
                                            </tbody>

                                            <!-- Data Rows -->
                                            <tbody
                                                v-else
                                                class="divide-y divide-[var(--color-border)]/50 rounded-xl"
                                            >
                                                <tr
                                                    v-for="(
                                                        payment, index
                                                    ) in paymentDetails"
                                                    :key="index"
                                                    class="rounded-xl hover:bg-[var(--color-primary)]/20 transition-colors duration-150 group"
                                                >
                                                    <td
                                                        class="px-3 py-1 font-medium w-[12%]"
                                                    >
                                                        {{ payment.payment_no }}
                                                    </td>
                                                    <td
                                                        class="px-3 py-1 font-medium w-[12%]"
                                                    >
                                                        {{
                                                            payment.check_no ||
                                                            "N/A"
                                                        }}
                                                    </td>
                                                    <td
                                                        class="px-3 py-1 font-medium w-[12%]"
                                                    >
                                                        {{
                                                            payment.document_no
                                                        }}
                                                    </td>
                                                    <td
                                                        class="px-3 py-1 font-medium w-[12%]"
                                                    >
                                                        <span
                                                            v-if="
                                                                isDueToday(
                                                                    payment.due_date
                                                                )
                                                            "
                                                            class="inline-flex items-center"
                                                        >
                                                            <span
                                                                class="relative flex h-2.5 w-2.5 mr-2"
                                                            >
                                                                <span
                                                                    class="bg-yellow-400 animate-ping absolute inline-flex h-full w-full rounded-full"
                                                                ></span>
                                                                <span
                                                                    class="relative bg-yellow-500 inline-flex rounded-full h-2.5 w-2.5"
                                                                ></span>
                                                            </span>
                                                            <span
                                                                class="capitalize text-yellow-500"
                                                            >
                                                                {{
                                                                    formatDate(
                                                                        payment.due_date
                                                                    )
                                                                }}
                                                            </span>
                                                        </span>
                                                        <span
                                                            v-else-if="
                                                                isPastDue(
                                                                    payment.due_date
                                                                )
                                                            "
                                                            class="inline-flex items-center"
                                                        >
                                                            <span
                                                                class="relative flex h-2.5 w-2.5 mr-2"
                                                            >
                                                                <span
                                                                    class="bg-red-400 animate-ping absolute inline-flex h-full w-full rounded-full"
                                                                ></span>
                                                                <span
                                                                    class="relative bg-red-500 inline-flex rounded-full h-2.5 w-2.5"
                                                                ></span>
                                                            </span>
                                                            <span
                                                                class="capitalize text-red-500"
                                                            >
                                                                {{
                                                                    formatDate(
                                                                        payment.due_date
                                                                    )
                                                                }}
                                                            </span>
                                                        </span>
                                                        <span
                                                            v-else
                                                            class="inline-flex items-center"
                                                        >
                                                            <span
                                                                class="relative flex h-2.5 w-2.5 mr-2"
                                                            >
                                                                <span
                                                                    class="bg-green-400 animate-ping absolute inline-flex h-full w-full rounded-full"
                                                                ></span>
                                                                <span
                                                                    class="relative bg-green-500 inline-flex rounded-full h-2.5 w-2.5"
                                                                ></span>
                                                            </span>
                                                            <span
                                                                class="capitalize text-green-500"
                                                            >
                                                                {{
                                                                    formatDate(
                                                                        payment.due_date
                                                                    )
                                                                }}
                                                            </span>
                                                        </span>
                                                    </td>
                                                    <td
                                                        class="px-3 py-1 text-right font-medium w-[12%]"
                                                    >
                                                        {{
                                                            formatCurrency(
                                                                payment.amount_paid
                                                            )
                                                        }}
                                                    </td>
                                                    <td
                                                        class="px-3 py-1 text-center font-medium w-[15%]"
                                                    >
                                                        <DropdownInput
                                                            v-model="
                                                                payment.status
                                                            "
                                                            :options="[
                                                                'Floating',
                                                                'Cleared',
                                                                'Cancelled',
                                                            ]"
                                                            placeholder="Click to Select"
                                                        />
                                                        <!-- <select
                                                            v-model="
                                                                payment.status
                                                            "
                                                            class="block w-full rounded-lg border border-[rgb(40,70,50)] bg-[rgb(25,55,40)] px-3 py-1.5 text-sm text-white shadow-inner shadow-black/20 focus:outline-none focus:ring-2 focus:ring-green-500/70 focus:border-green-500 transition-all duration-200 hover:border-green-500/50 cursor-pointer"
                                                        >
                                                            <option
                                                                value="Floating"
                                                            >
                                                                Floating
                                                            </option>
                                                            <option
                                                                value="Cleared"
                                                            >
                                                                Cleared
                                                            </option>
                                                            <option
                                                                value="Cancelled"
                                                            >
                                                                Cancelled
                                                            </option>
                                                        </select> -->
                                                    </td>
                                                    <td
                                                        class="px-3 py-1 text-center font-medium w-[25%]"
                                                    >
                                                        <input
                                                            v-model="
                                                                payment.remarks
                                                            "
                                                            type="text"
                                                            placeholder="Add remarks..."
                                                        />
                                                    </td>
                                                </tr>
                                                <template
                                                    v-if="
                                                        paymentDetails.length <
                                                            3 &&
                                                        paymentDetails.length !==
                                                            0
                                                    "
                                                >
                                                    <tr
                                                        v-for="n in 3 -
                                                        paymentDetails.length"
                                                        :key="'empty-' + n"
                                                        class="h-[48px]"
                                                    >
                                                        <td colspan="7">
                                                            &nbsp;
                                                        </td>
                                                    </tr>
                                                </template>
                                                <tr
                                                    v-if="
                                                        paymentDetails.length ===
                                                            0 && !isLoading
                                                    "
                                                >
                                                    <td
                                                        v-if="
                                                            form.clearing_date &&
                                                            form.customer_code
                                                        "
                                                        colspan="7"
                                                        class="px-5 py-6 text-center"
                                                    >
                                                        <div
                                                            class="flex flex-col items-center justify-center text-[var(--color-text-primary)]"
                                                        >
                                                            <svg
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                class="h-10 w-10 mb-2"
                                                                fill="none"
                                                                viewBox="0 0 24 24"
                                                                stroke="currentColor"
                                                            >
                                                                <path
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round"
                                                                    stroke-width="1.5"
                                                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                                                />
                                                            </svg>
                                                            <p
                                                                class="font-medium"
                                                            >
                                                                No data found on
                                                                this Selected
                                                                Customer and
                                                                Clearing Date.
                                                            </p>
                                                        </div>
                                                    </td>
                                                    <td
                                                        v-else
                                                        colspan="7"
                                                        class="px-5 py-6 text-center"
                                                    >
                                                        <div
                                                            class="flex flex-col items-center justify-center text-[var(--color-text-primary)]"
                                                        >
                                                            <svg
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                class="h-10 w-10 mb-2"
                                                                fill="none"
                                                                viewBox="0 0 24 24"
                                                                stroke="currentColor"
                                                            >
                                                                <path
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round"
                                                                    stroke-width="1.5"
                                                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                                                />
                                                            </svg>
                                                            <p
                                                                class="font-medium"
                                                            >
                                                                No data found.
                                                                Please select
                                                                customer.
                                                            </p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="paymentDetails.length !== 0"
                            class="w-full flex justify-start gap-4"
                        >
                            <span class="inline-flex items-center">
                                <span class="relative flex h-2.5 w-2.5 mr-2">
                                    <span
                                        class="bg-green-400/80 animate-ping absolute inline-flex h-full w-full rounded-full"
                                    ></span>
                                    <span
                                        class="relative bg-green-500 inline-flex rounded-full h-2.5 w-2.5"
                                    ></span>
                                </span>
                                <span class="text-xs font-light">
                                    Due Date Upcoming
                                </span>
                            </span>
                            <span class="inline-flex items-center">
                                <span class="relative flex h-2.5 w-2.5 mr-2">
                                    <span
                                        class="bg-yellow-400/80 animate-ping absolute inline-flex h-full w-full rounded-full"
                                    ></span>
                                    <span
                                        class="relative bg-yellow-500 inline-flex rounded-full h-2.5 w-2.5"
                                    ></span>
                                </span>
                                <span class="text-xs font-light">
                                    Due Date Today
                                </span>
                            </span>
                            <span class="inline-flex items-center">
                                <span class="relative flex h-2.5 w-2.5 mr-2">
                                    <span
                                        class="bg-red-400/80 animate-ping absolute inline-flex h-full w-full rounded-full"
                                    ></span>
                                    <span
                                        class="relative bg-red-500 inline-flex rounded-full h-2.5 w-2.5"
                                    ></span>
                                </span>
                                <span class="text-xs font-light">
                                    Due Date Overdue
                                </span>
                            </span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div
                        class="flex justify-end gap-2 pt-2 border-t border-[var(--color-border)] mt-4"
                    >
                        <button
                            type="button"
                            @click="closeModal"
                            class="closeButton group"
                        >
                            <div class="flex justify-center items-center gap-2">
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
                            class="submitButton group"
                            :disabled="form.processing"
                        >
                            <div class="flex justify-center items-center gap-2">
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
                                    >Submitting...</span
                                >
                                <span v-else>Submit</span>
                            </div>
                        </button>
                    </div>
                </div>
            </form>
        </transition>
    </div>
</template>

<script setup>
import { computed, nextTick, ref, watch, onMounted, onUnmounted } from "vue";
import { useForm } from "@inertiajs/vue3";
import ConfirmationDialog from "../../Pages/Components/ConfirmationDialog.vue";
import TextInput from "../../Pages/Components/TextInput.vue";
import ToastAlertWarning from "../../Pages/Components/ToastAlertWarning.vue";
import CustomerListModal from "../TransactionModals/CustomerListModal.vue";
import DropdownInput from "../../Pages/Components/DropdownInput.vue";
import { mdiClose, mdiNavigationVariantOutline } from "@mdi/js";
import PdfPreviewModal from "../PdfPreviewModal.vue";
import { route } from "../../../../vendor/tightenco/ziggy/src/js";
import DatePicker from "../../Pages/Components/DatePicker.vue";
import usePermissions from "../../Pages/Composables/usePermissions";

const props = defineProps({
    show: Boolean,
});

const form = useForm({
    clearing_no: null,
    transaction_date: null,
    clearing_date: null,
    customer_code: null,
    customer_name: null,
    check_type: "Post Dated Check",
    payment_details: [],
});

const { canPrint } = usePermissions();

const showCustomerModal = ref(false);

form.transaction_date = new Date().toISOString().split("T")[0];

const modalLoading = ref(false);
const isLoading = ref(false);

const emit = defineEmits(["close", "closeSuccess"]);

const closeModal = () => {
    emit("close");
};

const formatDate = (dateString) => {
    const options = { year: "numeric", month: "short", day: "numeric" };
    return new Date(dateString).toLocaleDateString(undefined, options);
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(amount);
};

// Helper methods for date checking
const isDueToday = (dateString) => {
    if (!dateString) return false;
    const dueDate = new Date(dateString);
    const today = new Date();
    return dueDate.toDateString() === today.toDateString();
};

const isPastDue = (dateString) => {
    if (!dateString) return false;
    const dueDate = new Date(dateString);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return dueDate < today;
};

////////////////TOAST///////////////////////////////////////////////////////////////////////////////////////////////////////
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

//////// CUSTOMER CODE DROPDOWN ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function onCustomerClick() {
    showCustomerModal.value = true;
}
const handleSelectedCustomer = (selectedData) => {
    form.customer_code = selectedData.cus_code;
    form.customer_name = selectedData.cus_name;
    form.price_group = selectedData.price_group;

    showCustomerModal.value = false;
};
///////////////// FETCH PAYMENT DETAILS ///////////////////////////////////////////////////////////////////////////////////////////////////////////
const paymentDetails = ref([]);

const fetchPaymentDetails = async (customerCode) => {
    isLoading.value = true;
    try {
        const response = await axios.get(route("getFloatingChecks"), {
            params: {
                customer_code: customerCode,
                checktype: form.check_type,
                clearingdate: form.clearing_date,
            },
        });

        // Map the response data to our table structure
        paymentDetails.value = response.data.map((payment) => ({
            payment_no: payment.payment_no,
            check_no: payment.check_no,
            document_no: payment.document_no,
            due_date: payment.due_date,
            type: payment.type,
            amount_paid: payment.amount_paid,
            status: payment.status,
            remarks: payment.remarks || "",
        }));
    } catch (error) {
        console.error("Error fetching payment details:", error);
        paymentDetails.value = [];
        showWarningToast("Failed to fetch payment details");
    } finally {
        isLoading.value = false;
    }
};

//////////// PRINT PDF //////////////////////////////////////////////////////////////////////////////////////////////////////////
const showPdfModal = ref(false);
const pdfFormData = ref(null);
const apiRoute = ref(null);
const previewInvoice = async () => {
    try {
        apiRoute.value = "previewCheckCleared";
        pdfFormData.value = form;
        showPdfModal.value = true;
    } catch (error) {
        console.error("Error previewing invoice:", error);
    }
};

const pdfPrintSuccess = () => {
    showPdfModal.value = false;
    form.reset();
    paymentDetails.value = [];
    emit("closeSuccess");
};

const showDialog = ref(false);
const handleConfirm = async (confirmed) => {
    showDialog.value = false;
    if (confirmed) {
        previewInvoice();
    } else {
        emit("closeSuccess");
    }
};

/////// WATCH //////////////////////////////////////////////////////////////////////////////////////////////////////////////
watch(
    () => props.show,
    async (visible, oldVisible) => {
        if (visible && !oldVisible) {
            modalLoading.value = true;
            form.clearing_no = "********";
            modalLoading.value = false;
        }
    },
    { immediate: true }
);

watch(
    () => form.clearing_date,
    async (newVal, oldVal) => {
        if (newVal !== oldVal) {
            if (form.customer_code) {
                fetchPaymentDetails(form.customer_code);
            }
        }
    }
);

watch(
    () => form.check_type,
    async (newVal, oldVal) => {
        if (newVal !== oldVal) {
            if (form.customer_code) {
                fetchPaymentDetails(form.customer_code);
            }
        }
    }
);

watch(
    () => form.customer_code,
    async (newVal, oldVal) => {
        if (newVal !== oldVal) {
            paymentDetails.value = [];
            await fetchPaymentDetails(form.customer_code);
        }
    }
);

const submit = () => {
    // Filter only rows with status changed to "Cleared"
    const clearedPayments = paymentDetails.value.filter(
        (payment) => payment.status !== "Floating"
    );

    if (clearedPayments.length === 0) {
        showWarningToast("No checks selected for clearing");
        return;
    }

    // Prepare the data to submit
    form.payment_details = clearedPayments.map((payment) => ({
        payment_no: payment.payment_no,
        check_no: payment.check_no,
        document_no: payment.document_no,
        type: payment.type,
        due_date: payment.due_date,
        amount: payment.amount_paid,
        status: payment.status,
        remarks: payment.remarks,
    }));

    Object.keys(form.errors).forEach((key) => {
        form.errors[key] = "";
    });
    form.post(route("checkclearing"), {
        onSuccess: () => {
            axios.get(route("clearing.latest.clearingNumber")).then((res) => {
                form.clearing_no = res.data.clearing_number;
                if (canPrint("0401-CHKCLR")) {
                    showDialog.value = true;
                } else {
                    emit("closeSuccess");
                }
            });
        },
        onError: (errors) => {
            console.log(errors);
            showWarningToast(errors.message || "Failed to clear checks");
        },
    });
};

//#region ///////////////////////////////////ANIMATION////////////////////////////////////////
///////////////////////////////////////////////////FORM ANIMATION////////////////////////////
const formElement = ref(null);
const isExpanded = ref(true); // Control this with your v-if condition

// Handle dynamic content changes
watch(
    () => paymentDetails.value.length, // Watch whatever causes your form to expand
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

form {
    transition: box-shadow 300ms ease, border-radius 300ms ease;
}

/* Fallback for height transitions */
.v-enter-active,
.v-leave-active {
    transition: height 300ms ease-in-out;
}
</style>
