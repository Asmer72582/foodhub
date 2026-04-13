<template>
    <LoadingComponent :props="loading" />

    <!-- Add password modal -->
    <div v-if="showPasswordModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <h3 class="text-lg font-semibold mb-4">Enter Password</h3>
            <input
                type="password"
                v-model="enteredPassword"
                class="w-full px-3 py-2 border rounded mb-4"
                placeholder="Enter password"
                @keyup.enter="validatePassword"
            >
            <div class="flex justify-end gap-2">
                <button
                    @click="cancelPasswordModal"
                    class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded"
                >
                    {{ $t('button.cancel') }}
                </button>
                <button
                    @click="validatePassword"
                    class="px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark"
                >
                    Confirm
                </button>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="db-card p-4">
            <div class="flex flex-wrap gap-y-5 items-end justify-between">
                <div>
                    <div class="flex flex-wrap items-start gap-y-2 gap-x-6 mb-5">
                        <p class="text-2xl font-medium">
                            {{ $t('label.order_id') }}:
                            <span class="text-heading">
                                #{{ order.order_serial_no }}
                            </span>
                        </p>
                        <div class="flex items-center gap-2 mt-1.5">
                            <span
                                :class="'text-xs capitalize h-5 leading-5 px-2 rounded-3xl text-[#FB4E4E] bg-[#FFDADA]' + statusClass(order.payment_status)">
                                {{ enums.paymentStatusEnumArray[order.payment_status] }}
                            </span>
                            <span :class="'text-xs capitalize px-2 rounded-3xl ' + orderStatusClass(order.status)">
                                {{ enums.orderStatusEnumArray[order.status] }}
                            </span>
                        </div>
                    </div>
                    <ul class="flex flex-col gap-2">
                        <li class="flex items-center gap-2">
                            <i class="lab lab-calendar-line lab-font-size-16"></i>
                            <span class="text-xs">{{ order.order_datetime }}</span>
                        </li>
                        <li class="text-xs">
                            {{ $t('label.payment_type') }}:

                            <span class="text-heading">
                                {{ enums.posPaymentMethodEnumArray[order.pos_payment_method] }}

                                 <span v-if="order.pos_payment_method !== enums.posPaymentMethodEnum.CASH && order.pos_payment_note"> ({{ order.pos_payment_note }})</span>
                            </span>


                        </li>
                        <li class="text-xs">
                            {{ $t('label.order_type') }}:
                            <span class="text-heading">
                                {{ this.$t("label.pos") }}
                            </span>
                        </li>
                        <li class="text-xs">
                            Order Method:
                            <span class="text-heading">
                                {{ getOrderMethodName(order.order_method) }}
                            </span>
                        </li>
                        <li class="text-xs">{{
                            $t('label.delivery_time')
                        }}:
                            <span class="text-heading">
                                {{ order.delivery_date }}
                            </span>
                        </li>
                        <li class="text-xs" v-if="order.token">{{
                            $t('label.token_no')
                        }}:
                            <span class="text-heading">
                                #{{ order.token }}
                            </span>
                        </li>
                        <li class="text-xs" v-if="order.tip_amount > 0">
                            {{ $t('label.tip') }}:
                            <span class="text-heading">
                                {{ order.tip_currency_amount }} (Attributed to: {{ order.tip_employee_name }})
                            </span>
                        </li>
                    </ul>
                </div>

                <div class="flex flex-wrap gap-3">
                    <div class="relative">
                        <select v-model="payment_status" @change="changePaymentStatus($event)"
                            class="text-sm capitalize appearance-none pl-4 pr-10 h-[38px] rounded border border-primary bg-white text-primary">
                            <option v-for="paymentStatus in enums.paymentStatusObject" :value="paymentStatus.value">{{
                                paymentStatus.name
                            }}</option>
                        </select>
                        <i class="lab lab-arrow-down-2 lab-font-size-16 absolute top-1/2 right-3.5 -translate-y-1/2 text-primary"></i>
                    </div>

                    <div class="relative">
                        <select v-model="pos_payment_method" @change="changePaymentMethod($event)"
                            class="text-sm capitalize appearance-none pl-4 pr-10 h-[38px] rounded border border-primary bg-white text-primary">
                            <option v-for="method in enums.posPaymentMethodObject" :key="method.value" :value="method.value">
                                {{ method.name }}
                            </option>
                        </select>
                        <i class="lab lab-arrow-down-2 lab-font-size-16 absolute top-1/2 right-3.5 -translate-y-1/2 text-primary"></i>
                    </div>

                    <div class="relative">
                        <select v-model="order_status" @change.prevent="orderStatus($event)"
                            class="text-sm capitalize appearance-none pl-4 pr-10 h-[38px] rounded border border-primary bg-white text-primary">
                            <option v-for="orderStatus in enums.orderStatusObject" :value="orderStatus.value">
                                {{ orderStatus.name }}
                            </option>
                        </select>
                        <i class="lab lab-arrow-down-2 lab-font-size-16 absolute top-1/2 right-3.5 -translate-y-1/2 text-primary"></i>
                    </div>

                    <div class="relative">
                        <select v-model="order_method" @change="changeOrderMethod($event)"
                            class="text-sm capitalize appearance-none pl-4 pr-10 h-[38px] rounded border border-primary bg-white text-primary">
                            <option v-for="method in enums.orderMethodObject" :key="method.value" :value="method.value">
                                {{ method.name }}
                            </option>
                        </select>
                        <i class="lab lab-arrow-down-2 lab-font-size-16 absolute top-1/2 right-3.5 -translate-y-1/2 text-primary"></i>
                    </div>

                    <button @click="editOrder" type="button"
                        class="flex items-center justify-center gap-2 px-4 h-[38px] rounded shadow-db-card bg-[#008BBA]">
                        <i class="lab lab-edit-line lab-font-size-16 text-white"></i>
                        <span class="text-sm capitalize text-white">{{ $t('button.edit') }}</span>
                    </button>

                    <button type="button" v-print="printObj"
                        class="flex items-center justify-center gap-2 px-4 h-[38px] rounded shadow-db-card bg-primary">
                        <i class="lab lab-printer-line lab-font-size-16 text-white"></i>
                        <span class="text-sm capitalize text-white">{{ $t('button.print_invoice') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 sm:col-6">
        <div class="db-card">
            <div class="db-card-header">
                <h3 class="db-card-title">{{ $t('label.order_details') }}</h3>
            </div>
            <div class="db-card-body">
                <div class="pl-3">
                    <div class="mb-3 pb-3 border-b last:mb-0 last:pb-0 last:border-b-0 border-gray-2"
                        v-if="orderItems.length > 0" v-for="item in orderItems" :key="item">
                        <div class="flex items-center gap-3 relative">
                            <h3
                                class="absolute top-5 -left-3 text-sm w-[26px] h-[26px] leading-[26px] text-center rounded-full text-white bg-heading">
                                {{ item.quantity }}</h3>
                            <img class="w-16 h-16 rounded-lg flex-shrink-0" :src="item.item_image" alt="thumbnail">
                            <div class="w-full">
                                <a href="#" class="text-sm font-medium capitalize transition text-heading hover:underline">
                                    {{ item.item_name }}
                                </a>
                                <p v-if="typeof item.item_variations === 'string'" class="capitalize text-xs mb-1.5">
                                    <span v-for="(variation, index) in JSON.parse(item.item_variations)">
                                        {{ variation.variation_name }}: {{ variation.name }}<span
                                            v-if="index + 1 < JSON.parse(item.item_variations).length">,&nbsp;</span>
                                    </span>
                                </p>
                                <h3 class="text-xs font-semibold">{{ item.total_currency_price }}</h3>
                            </div>
                        </div>
                        <ul v-if="item.item_extras.length > 0 || item.instruction !== ''"
                            class="flex flex-col gap-1.5 mt-2">
                            <li class="flex gap-1" v-if="item.item_extras.length > 0">
                                <h3 class="capitalize text-xs w-fit whitespace-nowrap">{{ $t('label.extras') }}:</h3>
                                <p class="text-xs">
                                    <span v-for="(extra, index) in item.item_extras">
                                        {{ extra.name }}<span v-if="index + 1 < item.item_extras.length">,&nbsp;</span>
                                    </span>
                                </p>
                            </li>
                            <li class="flex gap-1" v-if="item.instruction !== ''">
                                <h3 class="capitalize text-xs w-fit whitespace-nowrap">{{
                                    $t('label.instruction')
                                }}:</h3>
                                <p class="text-xs">{{ item.instruction }}</p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 sm:col-6">
        <div class="row">
            <div class="col-12">
                <div class="db-card p-1">
                    <ul class="flex flex-col gap-2 p-3 border-b border-dashed border-[#EFF0F6]">
                        <li class="flex items-center justify-between text-heading">
                            <span class="text-sm leading-6 capitalize">{{ $t('label.subtotal') }}</span>
                            <span class="text-sm leading-6 capitalize">{{ order.subtotal_currency_price }}</span>
                        </li>
                        <li class="flex items-center justify-between text-heading">
                            <span class="text-sm leading-6 capitalize">{{ $t('label.discount') }}</span>
                            <span class="text-sm leading-6 capitalize">{{ order.discount_currency_price }}</span>
                        </li>
                        <li class="flex items-center justify-between text-heading" v-if="order.tip_amount > 0">
                            <span class="text-sm leading-6 capitalize">{{ $t('label.tip') }} ({{ order.tip_employee_name }})</span>
                            <span class="text-sm leading-6 capitalize">{{ order.tip_currency_amount }}</span>
                        </li>
                    </ul>
                    <div class="flex items-center justify-between p-3">
                        <h4 class="text-sm leading-6 font-bold capitalize">{{ $t('label.total') }}</h4>
                        <h5 class="text-sm leading-6 font-bold capitalize">
                            {{ order.total_currency_price }}
                        </h5>
                    </div>
                </div>
            </div>
            <div class="col-12" v-if="order.order_method === enums.orderMethodEnum.DELIVERY">
                <div class="db-card">
                    <div class="db-card-header">
                        <h3 class="db-card-title">{{ $t('label.delivery_information') }}</h3>
                    </div>
                                        <div class="db-card-body">
                        <!-- Delivery Information Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4">
                            <!-- Customer Details -->
                            <div class="bg-gradient-to-br from-primary/5 to-primary/10 rounded-xl p-5">
                                <div class="flex items-center gap-3 mb-3">
                                    <img class="w-14 h-14 rounded-xl shadow-sm object-cover border-2 border-white"
                                         :src="orderUser.image"
                                         alt="Customer">
                                    <div>
                                        <h4 class="text-lg font-medium text-gray-800">
                                            {{ textShortener(orderUser.name, 20) }}
                                        </h4>
                                        <p class="text-sm text-gray-500">
                                            <i class="lab lab-call-calling-linear mr-1"></i>
                                            <span dir="ltr">{{ orderUser.country_code + '' + orderUser.phone }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Delivery Address -->
                            <div class="bg-gradient-to-br from-primary/5 to-primary/10 rounded-xl p-5">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 flex items-center justify-center rounded-lg bg-primary text-white">
                                            <i class="lab lab-location lab-font-size-18"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <h5 class="text-sm font-medium text-gray-500 mb-1">Delivery Address</h5>
                                        <p class="text-base text-gray-800 leading-relaxed">
                                            {{ order.delivery_address || orderUser.address || 'No address provided' }}
                                        </p>
                                        <div v-if="order.address_type"
                                             class="inline-flex items-center mt-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary">
                                            {{ order.address_type }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <PosOrderReceiptComponent :order="order" />
</template>
<script>
import LoadingComponent from "../components/LoadingComponent";
import alertService from "../../../services/alertService";
import PaginationTextComponent from "../components/pagination/PaginationTextComponent";
import PaginationBox from "../components/pagination/PaginationBox";
import PaginationSMBox from "../components/pagination/PaginationSMBox";
import appService from "../../../services/appService";
import orderStatusEnum from "../../../enums/modules/orderStatusEnum";
import TableLimitComponent from "../components/TableLimitComponent";
import paymentStatusEnum from "../../../enums/modules/paymentStatusEnum";
import print from "vue3-print-nb";
import PosOrderReceiptComponent from "./PosOrderReceiptComponent";
import posPaymentMethodEnum from "../../../enums/modules/posPaymentMethodEnum";
import orderMethodEnum from "../../../enums/modules/orderMethodEnum";
import axios from "axios";

export default {
    name: "PosOrderShowComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        LoadingComponent,
        PosOrderReceiptComponent
    },
    directives: {
        print
    },
    data() {
        return {
            loading: {
                isActive: false
            },
            printLoading: true,
            printObj: {
                id: "print",
                popTitle: this.$t("menu.order_receipt"),
            },
            enums: {
                orderStatusEnum: orderStatusEnum,
                paymentStatusEnum: paymentStatusEnum,
                posPaymentMethodEnum: {
                    CASH: 1,
                    UPI: 2
                },
                orderMethodEnum: {
                    DINE_IN: 1,
                    TAKEAWAY: 2,
                    DELIVERY: 3
                },
                orderMethodObject: [
                    {
                        name: 'Dine In',
                        value: 1
                    },
                    {
                        name: 'Takeaway',
                        value: 2
                    },
                    {
                        name: 'Delivery',
                        value: 3
                    }
                ],
                orderStatusEnumArray: {
                    [orderStatusEnum.PROCESSING]: this.$t("label.processing"),
                    [orderStatusEnum.DELIVERED]: this.$t("label.completed")
                },
                paymentStatusEnumArray: {
                    [paymentStatusEnum.PAID]: this.$t("label.paid"),
                    [paymentStatusEnum.UNPAID]: this.$t("label.unpaid")
                },
                posPaymentMethodEnumArray: {
                    1: this.$t("label.cash"),
                    2: "UPI"
                },
                paymentStatusObject: [
                    {
                        name: this.$t("label.paid"),
                        value: paymentStatusEnum.PAID
                    },
                    {
                        name: this.$t("label.unpaid"),
                        value: paymentStatusEnum.UNPAID,
                    }
                ],
                orderStatusObject: [
                    {
                        name: this.$t("label.processing"),
                        value: orderStatusEnum.PROCESSING,
                    },
                    {
                        name: this.$t("label.completed"),
                        value: orderStatusEnum.DELIVERED,
                    }
                ],
                posPaymentMethodObject: [
                    {
                        name: this.$t("label.cash"),
                        value: 1
                    },
                    {
                        name: this.$t("label.upi"),
                        value: 2
                    }
                ]
            },
            payment_status: null,
            order_status: null,
            pos_payment_method: null,
            pos_payment_note: null,
            posPaymentMethodEnum: {
                CASH: 1,
                ONLINE: 2
            },
            order_method: null,
            orderMethodOptions: [
                { value: 1, name: 'Dine In' },
                { value: 2, name: 'Takeaway' },
                { value: 3, name: 'Delivery' }
            ],
            showPasswordModal: false,
            enteredPassword: '',
            correctPassword: '2045',
        }
    },
    mounted() {
        this.loading.isActive = true;
        this.$store.dispatch('posOrder/show', this.$route.params.id).then(res => {
            this.payment_status = res.data.data.payment_status;
            this.order_status = res.data.data.status;
            this.pos_payment_method = res.data.data.pos_payment_method;
            this.pos_payment_note = res.data.data.pos_payment_note;
            this.order_method = res.data.data.order_method;
            this.loading.isActive = false;
            this.updateLocalStorage();
        }).catch((error) => {
            this.loading.isActive = false;
        });
    },
    computed: {
        order: function () {
            return this.$store.getters['posOrder/show'];
        },
        orderItems: function () {
            return this.$store.getters['posOrder/orderItems'];
        },
        orderUser: function () {
            return this.$store.getters['posOrder/orderUser'];
        }
    },
    methods: {
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        orderStatusClass: function (status) {
            return appService.orderStatusClass(status);
        },
        textShortener: function (text, number = 30) {
            return appService.textShortener(text, number);
        },
        orderStatus: function (e) {
            try {
                this.loading.isActive = true;
                this.$store.dispatch("posOrder/changeStatus", {
                    id: this.$route.params.id,
                    status: e.target.value,
                }).then((res) => {
                    this.loading.isActive = false;
                    alertService.successFlip(
                        1,
                        this.$t("label.status")
                    );
                }).catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            }
        },
        changePaymentStatus: function (e) {
            try {
                this.loading.isActive = true;
                this.$store.dispatch("posOrder/changePaymentStatus", {
                    id: this.$route.params.id,
                    payment_status: e.target.value,
                }).then((res) => {
                    this.loading.isActive = false;
                    alertService.successFlip(
                        1,
                        this.$t("label.payment_status")
                    );
                }).catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            }
        },
        changePaymentMethod: function (e) {
            try {
                const newPaymentMethod = parseInt(e.target.value);
                this.loading.isActive = true;

                // Set payment note based on payment method
                const paymentNote = newPaymentMethod === this.enums.posPaymentMethodEnum.CASH ?
                    this.$t("label.cash") : "UPI";

                this.$store.dispatch("posOrder/changePaymentMethod", {
                    id: this.$route.params.id,
                    pos_payment_method: newPaymentMethod,
                    pos_payment_note: paymentNote
                }).then((res) => {
                    // Update local state with the response data
                    this.pos_payment_method = res.data.data.pos_payment_method;
                    this.pos_payment_note = res.data.data.pos_payment_note;

                    this.loading.isActive = false;
                    alertService.successFlip(
                        1,
                        this.$t("label.payment_method")
                    );
                }).catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            }
        },
        editOrder() {
            this.showPasswordModal = true;
        },
        cancelPasswordModal() {
            this.showPasswordModal = false;
            this.enteredPassword = '';
        },
        validatePassword() {
            if (this.enteredPassword === this.correctPassword) {
                this.showPasswordModal = false;
                this.enteredPassword = '';
                this.$router.push({
                    name: 'admin.pos.edit',
                    query: { order_id: this.$route.params.id }
                });
            } else {
                alertService.error('Invalid password');
                this.enteredPassword = '';
            }
        },
        getOrderMethodName: function(method) {
            switch(method) {
                case this.enums.orderMethodEnum.DINE_IN:
                    return 'Dine In';
                case this.enums.orderMethodEnum.TAKEAWAY:
                    return 'Takeaway';
                case this.enums.orderMethodEnum.DELIVERY:
                    return 'Delivery';
                default:
                    return '';
            }
        },
        changeOrderMethod(e) {
            try {
                const newOrderMethod = parseInt(e.target.value);
                this.loading.isActive = true;

                this.$store.dispatch("posOrder/changeOrderMethod", {
                    id: this.order.id,
                    order_method: newOrderMethod
                }).then((res) => {
                    // Update local state
                    this.order_method = res.data.data.order_method;

                    // Update store state if needed
                    if (this.$store.state.posOrder && this.$store.state.posOrder.order) {
                        this.$store.commit('posOrder/SET_ORDER', res.data.data);
                    }

                    this.loading.isActive = false;
                    alertService.successFlip(1, 'Order Method Updated');
                }).catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err.message);
            }
        },
        updateLocalStorage() {
            // Implement the logic to update localStorage
        },
    },
}
</script>
