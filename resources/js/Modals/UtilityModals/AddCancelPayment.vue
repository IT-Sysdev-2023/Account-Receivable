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
                            CANCEL PAYMENT
                        </h2>
                        <div
                            class="mt-2 h-0.5 bg-gradient-to-r from-transparent via-[var(--color-border)] to-transparent"
                        ></div>
                    </div>

                    <div class="w-1/2 flex flex-col gap-2 mb-4 px-4">
                        <label class="block text-md font-bold"
                            >CANCELLATION MODE</label
                        >
                        <div class="flex gap-4">
                            <label
                                class="w-full inline-flex items-center cursor-pointer group"
                            >
                                <input
                                    type="radio"
                                    v-model="cancellation_type"
                                    value="Document No"
                                    class="hidden peer"
                                />
                                <div
                                    class="w-full relative flex items-center justify-start p-2"
                                >
                                    <div
                                        class="absolute -inset-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-[var(--color-border)]/30"
                                        :class="{
                                            'opacity-100':
                                                cancellation_type ===
                                                'Document No',
                                        }"
                                    ></div>
                                    <div
                                        class="relative w-5 h-5 mr-2 rounded-full border-2 border-[var(--color-border)] transition-colors z-10"
                                        :class="{
                                            'border-[var(--color-border)]':
                                                cancellation_type ===
                                                'Document No',
                                        }"
                                    >
                                        <div
                                            class="absolute inset-0 m-auto w-2.5 h-2.5 rounded-full bg-[var(--color-border)] transition-opacity"
                                            :class="{
                                                'opacity-100':
                                                    cancellation_type ===
                                                    'Document No',
                                                'opacity-0':
                                                    cancellation_type !==
                                                    'Document No',
                                            }"
                                        ></div>
                                    </div>
                                    <span class="text-sm z-10"
                                        >By Document No</span
                                    >
                                </div>
                            </label>

                            <label
                                class="w-full inline-flex items-center cursor-pointer group"
                            >
                                <input
                                    type="radio"
                                    v-model="cancellation_type"
                                    value="Payment No"
                                    class="hidden peer"
                                />
                                <div
                                    class="w-full relative flex items-center justify-start p-2"
                                >
                                    <div
                                        class="absolute -inset-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-[var(--color-border)]/30"
                                        :class="{
                                            'opacity-100':
                                                cancellation_type ===
                                                'Payment No',
                                        }"
                                    ></div>
                                    <div
                                        class="relative w-5 h-5 mr-2 rounded-full border-2 border-[var(--color-border)] transition-colors z-10"
                                        :class="{
                                            'border-[var(--color-border)]':
                                                cancellation_type ===
                                                'Payment No',
                                        }"
                                    >
                                        <div
                                            class="absolute inset-0 m-auto w-2.5 h-2.5 rounded-full bg-[var(--color-border)] transition-opacity"
                                            :class="{
                                                'opacity-100':
                                                    cancellation_type ===
                                                    'Payment No',
                                                'opacity-0':
                                                    cancellation_type !==
                                                    'Payment No',
                                            }"
                                        ></div>
                                    </div>
                                    <span class="text-sm z-10"
                                        >By Payment No</span
                                    >
                                </div>
                            </label>
                        </div>
                    </div>

                    <div
                        v-if="cancellation_type === 'Document No'"
                        class="flex flex-col md:flex-col gap-4 px-4"
                    >
                        <div
                            class="w-full grid sm:grid-cols-1 md:grid-cols-2 gap-4"
                        >
                            <TextInput
                                label="Document No"
                                type="text"
                                v-model="form.document_no"
                                :message="form.errors.document_no"
                                :default-placeholder="'Enter Document No'"
                            />
                            <DropdownInput
                                label="Type"
                                v-model="form.type"
                                :options="[
                                    'Sales Invoice',
                                    'Charge Invoice',
                                    'Payment',
                                    'BG',
                                ]"
                                :message="form.errors.type"
                                :disabled="!form.document_no"
                                placeholder="Click to Select"
                                disabledPlaceholder="Enter Document No First"
                            />
                        </div>
                        <div
                            class="w-full grid sm:grid-cols-1 md:grid-cols-3 gap-4"
                        >
                            <TextInput
                                label="Customer Code"
                                type="text"
                                v-model="form.customer_code"
                                @click="onCustomerClick()"
                                :readonly="!form.type"
                                :message="form.errors.customer_code"
                                :default-placeholder="'Click to Select'"
                                :modified-placeholder="'Select Type First'"
                                selectable="yes"
                            />
                            <TextInput
                                label="Customer Name"
                                type="text"
                                v-model="form.customer_name"
                                readonly
                                :message="form.errors.customer_name"
                                class="col-span-2"
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
                                                class="px-3 py-2 text-left w-[20%]"
                                            >
                                                PAYMENT NO
                                            </th>
                                            <th
                                                class="px-3 py-2 text-left w-[20%]"
                                            >
                                                RECEIPT DATE
                                            </th>
                                            <th
                                                class="px-3 py-2 text-left w-[20%]"
                                            >
                                                PAYMENT TYPE
                                            </th>
                                            <th
                                                class="px-3 py-2 text-center w-[20%]"
                                            >
                                                AMOUNT
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
                                                        class="px-3 py-2 font-medium w-[20%]"
                                                    >
                                                        {{ payment.payment_no }}
                                                    </td>
                                                    <td
                                                        class="px-3 py-2 font-medium w-[20%]"
                                                    >
                                                        {{
                                                            formatDate(
                                                                payment.payment_receipt_date
                                                            )
                                                        }}
                                                    </td>
                                                    <td
                                                        class="px-3 py-2 font-medium w-[20%]"
                                                    >
                                                        {{
                                                            payment.payment_type
                                                        }}
                                                    </td>
                                                    <td
                                                        class="px-3 py-2 text-right font-medium w-[20%]"
                                                    >
                                                        {{
                                                            formatCurrency(
                                                                payment.amount_paid
                                                            )
                                                        }}
                                                    </td>
                                                </tr>
                                                <tr
                                                    v-if="
                                                        paymentDetails.length ===
                                                            0 && !isLoading
                                                    "
                                                >
                                                    <td
                                                        v-if="
                                                            form.type &&
                                                            form.customer_code
                                                        "
                                                        colspan="5"
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
                                                                this selected
                                                                Document No,
                                                                Type and
                                                                Customer.
                                                            </p>
                                                        </div>
                                                    </td>
                                                    <td
                                                        v-else
                                                        colspan="5"
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
                    </div>

                    <div v-else class="flex flex-col md:flex-col gap-4 px-4">
                        <div
                            class="w-full grid sm:grid-cols-1 md:grid-cols-2 gap-4"
                        >
                            <TextInput
                                label="Payment No"
                                type="text"
                                v-model="form.payment_no"
                                :message="form.errors.payment_no"
                                :default-placeholder="'Enter Document No'"
                            />
                            <TextInput
                                label="Type"
                                type="text"
                                v-model="form.type"
                                :message="form.errors.type"
                                readonly
                            />
                            <!-- <DropdownInput
                                label="Type"
                                v-model="form.type"
                                :options="[
                                    'Sales Invoice',
                                    'Charge Invoice',
                                    'Payment',
                                    'BG',
                                ]"
                                :message="form.errors.type"
                                :disabled="!form.document_no"
                                placeholder="Click to Select"
                                disabledPlaceholder="Enter Document No First"
                            /> -->
                        </div>
                        <div
                            class="w-full grid sm:grid-cols-1 md:grid-cols-3 gap-4"
                        >
                            <TextInput
                                label="Customer Code"
                                type="text"
                                v-model="form.customer_code"
                                readonly
                                :message="form.errors.customer_code"
                            />
                            <TextInput
                                label="Customer Name"
                                type="text"
                                v-model="form.customer_name"
                                readonly
                                :message="form.errors.customer_name"
                                class="col-span-2"
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
                                                class="px-3 py-2 text-left w-[20%]"
                                            >
                                                DOCUMENT NO
                                            </th>
                                            <th
                                                class="px-3 py-2 text-left w-[20%]"
                                            >
                                                RECEIPT DATE
                                            </th>
                                            <th
                                                class="px-3 py-2 text-left w-[20%]"
                                            >
                                                PAYMENT TYPE
                                            </th>
                                            <th
                                                class="px-3 py-2 text-center w-[20%]"
                                            >
                                                AMOUNT
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
                                                        class="px-3 py-2 font-medium w-[20%]"
                                                    >
                                                        {{
                                                            payment.document_no
                                                        }}
                                                    </td>
                                                    <td
                                                        class="px-3 py-2 font-medium w-[20%]"
                                                    >
                                                        {{
                                                            formatDate(
                                                                payment.payment_receipt_date
                                                            )
                                                        }}
                                                    </td>
                                                    <td
                                                        class="px-3 py-2 font-medium w-[20%]"
                                                    >
                                                        {{
                                                            payment.payment_type
                                                        }}
                                                    </td>
                                                    <td
                                                        class="px-3 py-2 text-right font-medium w-[20%]"
                                                    >
                                                        {{
                                                            formatCurrency(
                                                                payment.amount_paid
                                                            )
                                                        }}
                                                    </td>
                                                </tr>
                                                <tr
                                                    v-if="
                                                        paymentDetails.length ===
                                                            0 && !isLoading
                                                    "
                                                >
                                                    <td
                                                        v-if="
                                                            form.type &&
                                                            form.customer_code
                                                        "
                                                        colspan="5"
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
                                                                this selected
                                                                Document No,
                                                                Type and
                                                                Customer.
                                                            </p>
                                                        </div>
                                                    </td>
                                                    <td
                                                        v-else
                                                        colspan="5"
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
                            :disabled="
                                form.processing || paymentDetails.length === 0
                            "
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
                                    >Cancelling Payment...</span
                                >
                                <span v-else>Cancel Payment</span>
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
import TextInput from "../../Pages/Components/TextInput.vue";
import ToastAlertWarning from "../../Pages/Components/ToastAlertWarning.vue";
import CustomerListModal from "../TransactionModals/CustomerListModal.vue";
import DropdownInput from "../../Pages/Components/DropdownInput.vue";
import { mdiClose, mdiNavigationVariantOutline } from "@mdi/js";

const props = defineProps({
    show: Boolean,
});

const form = useForm({
    payment_no: null,
    document_no: null,
    type: null,
    customer_code: null,
    customer_name: null,
    payment_details: [],
});

const cancellation_type = ref("Document No");

const showCustomerModal = ref(false);

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

    showCustomerModal.value = false;
};
///////////////// FETCH PAYMENT DETAILS ///////////////////////////////////////////////////////////////////////////////////////////////////////////
const paymentDetails = ref([]);

const fetchPaymentDetails = async (customerCode) => {
    isLoading.value = true;
    try {
        let apiUrl = "";
        let params = {};

        if (cancellation_type.value === "Document No") {
            apiUrl = route("payment_list");
            params = {
                customer_code: customerCode,
                document_no: form.document_no,
                type: form.type,
            };
        } else if (cancellation_type.value === "Payment No") {
            apiUrl = route("documentspaid_list");
            params = { payment_no: form.payment_no };
        }

        if (!apiUrl) {
            showWarningToast("Invalid cancellation type");
        }

        const response = await axios.get(apiUrl, { params });

        if (cancellation_type.value === "Payment No") {
            if (response.data.payment_info) {
                form.type = response.data.payment_info.type || "";
                form.customer_code =
                    response.data.payment_info.customer_code || "";
                form.customer_name = response.data.payment_info.name || "";
            }
            paymentDetails.value = Array.isArray(response.data.payment_details)
                ? response.data.payment_details.map((payment) => ({
                      id: payment.id,
                      payment_no: payment.payment_no,
                      payment_receipt_date: payment.payment_receipt_date,
                      payment_date: payment.payment_date,
                      document_no: payment.document_no,
                      document_date: payment.document_date,
                      payment_type: payment.payment_type,
                      type: payment.type,
                      advpy_amount_paid: payment.advpy_amount_paid,
                      amount_paid: payment.amount_paid,
                      status: payment.status,
                      remarks: payment.remarks || "",
                  }))
                : [];
        } else {
            paymentDetails.value = Array.isArray(response.data)
                ? response.data.map((payment) => ({
                      id: payment.id,
                      payment_no: payment.payment_no,
                      payment_receipt_date: payment.payment_receipt_date,
                      payment_date: payment.payment_date,
                      document_no: payment.document_no,
                      document_date: payment.document_date,
                      payment_type: payment.payment_type,
                      type: payment.type,
                      advpy_amount_paid: payment.advpy_amount_paid,
                      amount_paid: payment.amount_paid,
                      status: payment.status,
                      remarks: payment.remarks || "",
                  }))
                : [];
        }
    } catch (error) {
        console.error("Error fetching payment details:", error);
        paymentDetails.value = [];
        showWarningToast("Failed to fetch payment details");
    } finally {
        isLoading.value = false;
    }
};

/////// WATCH //////////////////////////////////////////////////////////////////////////////////////////////////////////////
watch(
    () => cancellation_type.value,
    async (newVal, oldVal) => {
        if (newVal !== oldVal) {
            form.payment_no = "";
            form.document_no = "";
            form.type = "";
            form.customer_code = "";
            form.customer_name = "";
            paymentDetails.value = [];
        }
    }
);

watch(
    () => form.customer_code,
    async (newVal, oldVal) => {
        if (newVal !== oldVal && newVal !== "") {
            paymentDetails.value = [];
            await fetchPaymentDetails(form.customer_code);
        }
    }
);
watch(
    () => form.payment_no,
    async (newVal, oldVal) => {
        if (newVal !== oldVal) {
            form.type = "";
            form.customer_code = "";
            form.customer_name = "";
            paymentDetails.value = [];
            await fetchPaymentDetails(form.customer_code);
        }
    }
);

watch(
    () => form.document_no,
    async (newVal, oldVal) => {
        if (newVal !== oldVal) {
            form.type = "";
            form.customer_code = "";
            form.customer_name = "";
            paymentDetails.value = [];
        }
    }
);

watch(
    () => form.type,
    async (newVal, oldVal) => {
        if (newVal !== oldVal && cancellation_type.value === "Document No") {
            form.customer_code = "";
            form.customer_name = "";
        }
    }
);

//////SUBMIT ///////////////////////////////////////////////////////////////////////////////////////////
const submit = () => {
    // Prepare the data to submit
    const clearedPayments = paymentDetails.value;

    if (cancellation_type.value === "Document No") {
        form.payment_details = clearedPayments.map((payment) => ({
            id: payment.id,
            payment_no: payment.payment_no,
            receipt_date: payment.payment_receipt_date,
            payment_type: payment.payment_type,
            advpy_amount_paid: payment.advpy_amount_paid,
            amount: payment.amount_paid,
            remarks: payment.remarks,
        }));
        form.post(route("cancel-payment"), {
            onSuccess: () => {
                form.reset();
                paymentDetails.value = [];
                emit("closeSuccess");
            },
            onError: (errors) => {
                console.log(errors);
                showWarningToast(errors.message || "Failed to cancel Payment");
            },
        });
    } else if (cancellation_type.value === "Payment No") {
        form.payment_details = clearedPayments.map((payment) => ({
            id: payment.id,
            document_no: payment.document_no,
            receipt_date: payment.payment_receipt_date,
            payment_type: payment.payment_type,
            type: payment.type,
            advpy_amount_paid: payment.advpy_amount_paid,
            amount: payment.amount_paid,
            remarks: payment.remarks,
        }));

        form.post(route("cancel-payment-two"), {
            onSuccess: () => {
                form.reset();
                paymentDetails.value = [];
                emit("closeSuccess");
            },
            onError: (errors) => {
                console.log(errors);
                showWarningToast(errors.message || "Failed to cancel Payment");
            },
        });
    }
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
