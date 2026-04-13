<template>
    <div id="receiptModal" class="modal">
        <div class="modal-dialog max-w-[340px] rounded-none" id="print" :dir="direction">
            <div class="modal-header hidden-print">
                <button type="button" @click="reset"
                    class="modal-close flex items-center justify-center gap-1.5 py-2 px-4 rounded bg-[#FB4E4E]">
                    <i class="lab lab-back-bold lab-font-size-16 text-white"></i>
                    <span class="text-xs leading-5 capitalize text-white">{{ $t('button.close') }}</span>
                </button>
                <button type="button" v-print="printObj"
                    class="flex items-center justify-center gap-1.5 py-2 px-4 rounded bg-[#1AB759]">
                    <i class="lab lab-print-bold lab-font-size-16 text-white"></i>
                    <span class="text-xs leading-5 capitalize text-white">{{ $t('button.print_invoice') }}</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center pb-3.5 border-b border-dashed border-gray-400">
                    <h3 class="text-2xl font-bold mb-1">{{ company.company_name }}</h3>
                    <h4 class="text-sm font-normal">{{ branch.address }}</h4>
                    <h5 class="text-sm font-normal">Tel: {{ branch.phone }}</h5>
                    <h5 v-if="branch.email" class="text-sm font-normal">Email: {{ branch.email }}</h5>
                    <h5 v-if="company.company_website" class="text-sm font-normal">{{ company.company_website }}</h5>
                </div>

                <table class="w-full my-1.5">
                    <tbody>
                        <tr>
                            <td class="text-xs text-left py-0.5 text-heading">{{ $t('button.order') }}
                                #{{ order.order_serial_no }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-xs text-left py-0.5 text-heading">{{ order.order_date }}</td>
                            <td class="text-xs text-right py-0.5 text-heading">{{ order.order_time }}</td>
                        </tr>
                    </tbody>
                </table>

                <table class="w-full">
                    <thead class="border-t border-b border-dashed border-gray-400">
                        <tr>
                            <th scope="col" class="py-1 font-normal text-xs capitalize text-left text-heading w-8">
                                {{ $t('label.qty') }}
                            </th>
                            <th scope="col"
                                class="py-1 font-normal text-xs capitalize flex items-center justify-between text-heading">
                                <span>{{ $t('label.item_description') }}</span>
                                <span>{{ $t('label.price') }}</span>
                            </th>
                        </tr>
                    </thead>

                    <tbody class="border-b border-dashed border-gray-400">
                        <tr v-if="orderItems.length > 0" v-for="item in orderItems" :key="item.id">
                            <td class="text-left font-normal align-top py-1">
                                <p class="text-xs leading-5 text-heading">{{ item.quantity }}</p>
                            </td>
                            <td class="text-left font-normal align-top py-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-normal capitalize">{{ item.item_name }}</h4>
                                    <p class="text-xs leading-5 text-heading">{{ item.total_without_tax_currency_price }}
                                    </p>
                                </div>
                                <p v-if="Object.keys(item.item_variations).length !== 0"
                                    class="text-xs leading-5 font-normal text-heading max-w-[200px]">
                                    <span v-for="(variation, index) in item.item_variations" :key="'var_' + index">
                                        {{ variation.variation_name }}: {{ variation.name }}
                                        <span v-if="index + 1 < Object.keys(item.item_variations).length">, </span>
                                    </span>
                                </p>
                                <p v-if="item.item_extras.length > 0"
                                    class="text-xs leading-5 font-normal text-heading max-w-[200px]">
                                    {{ $t('label.extras') }}:
                                    <span v-for="(extra, index) in item.item_extras" :key="'extra_' + index">
                                        {{ extra.name }}
                                        <span v-if="index + 1 < item.item_extras.length">, </span>
                                    </span>
                                </p>
                                <p v-if="item.instruction" class="text-xs leading-5 font-normal text-heading max-w-[200px]">
                                    {{ $t('label.instruction') }}: {{ item.instruction }}
                                </p>

                                <div class="flex items-center justify-between" v-if="item.tax_rate > 0">
                                    <p class="text-xs leading-5 font-normal text-heading">
                                        {{ item.tax_name }} ({{ item.tax_currency_rate }} {{ item.tax_type }})</p>
                                    <p class="text-xs leading-5 font-normal text-heading">
                                        {{ item.tax_currency_amount }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="py-2 pl-7">
                    <table class="w-full">
                        <tbody>
                            <tr>
                                <td class="text-xs text-left py-0.5 uppercase text-heading">{{ $t('label.subtotal') }}:</td>
                                <td class="text-xs text-right py-0.5 text-heading">{{
                                    order.subtotal_without_tax_currency_price
                                }}</td>
                            </tr>
                            <tr>
                                <td class="text-xs text-left py-0.5 uppercase text-heading">
                                    {{ $t('label.total_tax') }}:
                                </td>
                                <td class="text-xs text-right py-0.5 text-heading">
                                    {{ order.total_tax_currency_price }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-xs text-left py-0.5 uppercase text-heading">{{ $t('label.discount') }}:</td>
                                <td class="text-xs text-right py-0.5 text-heading">{{ order.discount_currency_price }}</td>
                            </tr>

                            <tr>
                                <td class="text-xs text-left py-0.5 font-bold uppercase text-heading">
                                    {{ $t('label.total') }}:
                                </td>
                                <td class="text-xs text-right py-0.5 font-bold text-heading">
                                    {{ order.total_currency_price }}
                                </td>
                            </tr>
                            <tr v-if="order.tip_amount > 0">
                                <td class="text-xs text-left py-0.5 uppercase text-heading">{{ $t('label.tip') }}:</td>
                                <td class="text-xs text-right py-0.5 text-heading">{{ order.tip_currency_amount }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="text-xs py-2 border-t border-dashed border-gray-400 text-heading" :class="{'border-b': order.pos_payment_method != 6}">
                    {{ $t('label.payment_type') }}: {{ posPaymentMethodEnumArray[order.pos_payment_method] }}
                </p>
                <div v-if="order.pos_payment_method == 6" class="text-xs pb-2 border-b border-dashed border-gray-400 text-heading">
                    <p class="flex justify-between pt-1"><span>Cash:</span> <span>{{ order.pos_split_cash }}</span></p>
                    <p class="flex justify-between pt-1"><span>Online (UPI):</span> <span>{{ order.pos_split_online }}</span></p>
                </div>
                <p class="text-xs py-2 border-b border-dashed border-gray-400 text-heading">
                    Order Method: {{ getOrderMethodName(order.order_method) }}
                </p>

                <!-- Delivery Customer Details -->
                <div v-if="order.order_method === orderMethodEnum.DELIVERY"
                    class="py-2 border-b border-dashed border-gray-400">
                    <h4 class="text-xs font-medium mb-2 text-heading">{{ $t('Delivery Details') }}:</h4>
                    <div class="pl-2 space-y-1">
                       
                        <!-- Phone -->
                        <p v-if="order.delivery_phone" class="text-xs leading-5 text-heading flex items-start">

                            <span>Phone Number: {{ order.delivery_phone }}</span>
                        </p>
                        <!-- Address -->
                        <p v-if="order.delivery_address" class="text-xs leading-5 text-heading flex items-start">

                            <span class="flex-1">Address: {{ order.delivery_address }}</span>
                        </p>
                        <!-- Note -->
                        <p v-if="order.delivery_note" class="text-xs leading-5 text-heading flex items-start">
                            <i class="lab lab-note-bold lab-font-size-16 mr-1"></i>
                            <span class="flex-1">{{ order.delivery_note }}</span>
                        </p>
                    </div>
                </div>

                <h4 v-if="order.token"
                    class="py-2 capitalize text-xl font-bold text-center border-b border-dashed border-gray-400">
                    {{ $t('label.token') }} #{{ order.token }}
                </h4>
                <div class="text-center pt-2 pb-4">
                    <p class="text-[11px] leading-[14px] capitalize text-heading">
                        {{ $t('message.thank_you') }}
                    </p>
                    <p class="text-[11px] leading-[14px] capitalize text-heading">
                        {{ $t('message.please_come_again') }}
                    </p>
                </div>
                <div class="flex flex-col items-end">
                    <h5 class="text-[8px] font-normal text-left w-[46px] leading-[10px]">
                        {{ $t('label.powered_by') }}
                    </h5>
                    <h6 class="text-xs font-normal leading-4">{{ company.company_name }}</h6>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import print from "vue3-print-nb";
import appService from "../../../services/appService";
import displayModeEnum from "../../../enums/modules/displayModeEnum";
import posPaymentMethodEnum from "../../../enums/modules/posPaymentMethodEnum";
import orderMethodEnum from "../../../enums/modules/orderMethodEnum";

export default {
    name: "ReceiptComponent",
    props: {
        order: Object
    },
    data() {
        return {
            printObj: {
                id: "print",
                popTitle: this.$t("menu.order_receipt"),
            },
            posPaymentMethodEnumArray: {
                [posPaymentMethodEnum.CASH]: this.$t("label.cash"),
                [posPaymentMethodEnum.CARD]: this.$t("label.card"),
                [posPaymentMethodEnum.BANK_TRANSFER]: "Bank Transfer",
                [posPaymentMethodEnum.UPI]: this.$t("label.upi"),
                [posPaymentMethodEnum.MOBILE_PAYMENT]: "Mobile Payment",
                [posPaymentMethodEnum.SPLIT]: "Split Payment",
            },
            orderMethodEnum: {
                DINE_IN: 1,
                TAKEAWAY: 2,
                DELIVERY: 3
            }
        }
    },
    computed: {
        company: function () {
            return this.$store.getters['company/lists'];
        },
        branch: function () {
            return this.$store.getters['backendGlobalState/branchShow'];
        },
        orderItems: function () {
            return this.$store.getters['posOrder/orderItems'];
        },
        direction: function () {
            return this.$store.getters['frontendLanguage/show'].display_mode === displayModeEnum.RTL ? 'rtl' : 'ltr';
        },
    },
    mounted() {
        this.$store.dispatch("company/lists").then().catch();
    },
    watch: {
        order: {
            immediate: true,
            handler(newOrder) {
                if (newOrder && newOrder.order_items) {
                    // Format the order items before setting them in the store
                    const formattedItems = newOrder.order_items.map(item => ({
                        id: item.id,
                        item_name: item.item_name,
                        quantity: parseInt(item.quantity) || 1,
                        price: parseFloat(item.price) || 0,
                        total_without_tax_currency_price: item.total_without_tax_currency_price,
                        tax_name: item.tax_name,
                        tax_rate: parseFloat(item.tax_rate) || 0,
                        tax_type: item.tax_type,
                        tax_currency_rate: item.tax_currency_rate,
                        tax_currency_amount: item.tax_currency_amount,
                        item_variations: item.item_variations || {},
                        item_extras: item.item_extras || [],
                        instruction: item.instruction || ''
                    }));
                    this.$store.commit('posOrder/setOrderItems', formattedItems);
                }
            }
        }
    },
    methods: {
        reset: function () {
            appService.modalHide();
        },
        getOrderMethodName(method) {
            switch(method) {
                case this.orderMethodEnum.DINE_IN:
                    return 'Dine In';
                case this.orderMethodEnum.TAKEAWAY:
                    return 'Takeaway';
                case this.orderMethodEnum.DELIVERY:
                    return 'Delivery';
                default:
                    return '';
            }
        }
    },
    directives: {
        print
    },
}
</script>
<style scoped>
@media print {
    .hidden-print {
        display: none !important;
    }
}
</style>
