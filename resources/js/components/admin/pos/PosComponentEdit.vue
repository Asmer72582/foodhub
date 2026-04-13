<template>
    <LoadingComponent :props="loading" />

    <div class="md:w-[calc(100%-340px)] lg:w-[calc(100%-320px)] xl:w-[calc(100%-377px)]">
        <form @submit.prevent="search" class="flex items-center w-full h-[38px] leading-[38px] mb-4 rounded-lg bg-white">
            <input type="text" v-model="props.search.name" :placeholder="$t('label.search_by_menu_item')"
                class="w-full px-5 rounded-tl-lg rounded-bl-lg border placeholder:text-xs placeholder:font-rubik placeholder:text-[#A0A3BD] border-[#EFF0F6]">
            <button type="submit"
                class="flex-shrink-0 w-[38px] h-full text-center ltr:rounded-tr-lg ltr:rounded-br-lg rtl:rounded-tl-lg rtl:rounded-bl-lg bg-primary">
                <i class="lab lab-search-normal text-white"></i>
            </button>
        </form>

        <div class="swiper pos-menu-swiper mb-6" v-if="categories.length > 1">
            <Swiper dir="ltr" :speed="1000" slidesPerView="auto" :spaceBetween="16" class="menu-slides">
                <SwiperSlide class="!w-fit" v-for="(category, index) in categories" :key="category"
                    :class="category.id === props.search.item_category_id || (category.id === 0 && props.search.item_category_id === '') ? 'pos-group' : ''">
                    <button v-if="index === 0" @click="allCategory"
                        class="w-28 flex flex-col items-center text-center gap-4 py-4 px-3 rounded-lg border-b-2 border-transparent transition hover:bg-[#FFEDF4] hover:border-primary bg-white">
                        <img class="h-7 drop-shadow-category" :src="category.thumb" alt="category">
                        <h3 class="text-xs leading-[16px] font-medium font-rubik">{{ category.name }}</h3>
                    </button>
                    <button v-else @click="setCategory(category.id)"
                        class="w-28 flex flex-col items-center text-center gap-4 py-4 px-3 rounded-lg border-b-2 border-transparent transition hover:bg-[#FFEDF4] hover:border-primary bg-white">
                        <img class="h-7 drop-shadow-category" :src="category.thumb" alt="category">
                        <h3 class="text-xs leading-[16px] font-medium font-rubik">{{ category.name }}</h3>
                    </button>
                </SwiperSlide>
            </Swiper>
        </div>
        <ItemComponent :items="items" />
    </div>

    <div
        class="db-pos-cartDiv fixed top-0 ltr:right-0 rtl:left-0 w-full h-screen rounded-none z-50 md:z-10 md:top-[85px] ltr:md:right-5 rtl:md:left-5 md:w-[322px] lg:w-[305px] xl:w-[360px] md:h-[calc(100vh-85px)] md:rounded-lg overflow-y-auto thin-scrolling bg-white">
        <div class="p-4">
            <div class="md:hidden text-right mb-3">
                <button class="db-pos-cartCls">
                    <i class="lab-close-circle-line font-fill-danger lab-font-size-24"></i>
                </button>
            </div>
            <!-- Main Customer Selection -->
            <div class="db-field mb-3">
                <vue-select class="db-field-control text-sm rounded-lg appearance-none text-heading border-[#D9DBE9]"
                    id="customer"
                    v-model="checkoutProps.form.customer_id"
                    :options="customers"
                    label-by="name"
                    value-by="id"
                    :searchable="true"
                    :close-on-select="true"
                    :reduce="customer => customer.id"
                    @input="handleCustomerSelect"
                    :get-option-label="option => option ? (option.name || '') : ''"
                    :placeholder="$t('label.select_customer')"
                />
            </div>

            <div class="db-field mb-3">
                <vue-select class="db-field-control text-sm rounded-lg appearance-none text-heading border-[#D9DBE9]"
                    id="orderMethod" v-model="checkoutProps.form.order_method" :options="orderMethodOptions" label-by="name"
                    value-by="id" :closeOnSelect="true" :searchable="false" :clearOnClose="false"
                    :placeholder="$t('label.select_order_method')"
                    @input="handleOrderMethodChange"
                />
            </div>

            <!-- Phone Search Input -->
            <div v-if="checkoutProps.form.order_method === orderMethodEnum.DELIVERY" class="mb-3">
                <div class="relative">
                    <div class="flex gap-2">
                        <div class="flex-shrink-0">
                            <input type="text" v-model="countryCode"
                                class="w-16 px-2 py-2.5 rounded-lg border border-gray-300 bg-gray-50 text-gray-500 text-center"
                                readonly>
                    </div>
                        <input type="text" v-model="phoneSearch"
                            @input="searchCustomerByPhone"
                            @keypress="phoneNumber($event)"
                            placeholder="Search by phone number"
                            class="flex-1 px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors duration-200">
                </div>

                    <!-- Loading Indicator -->
                    <div v-if="isSearching" class="absolute right-14 top-3">
                        <i class="fa-solid fa-spinner fa-spin text-gray-400"></i>
                        </div>

                    <!-- Phone Search Results -->
                    <div v-if="phoneSearchResults.length > 0"
                        class="absolute z-50 left-0 right-0 mt-1 bg-white border rounded-lg shadow-lg max-h-60 overflow-y-auto">
                        <div v-for="customer in phoneSearchResults"
                            :key="customer.id"
                            @click="selectCustomerFromSearch(customer)"
                            class="p-3 hover:bg-gray-50 cursor-pointer border-b last:border-b-0">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm font-medium text-gray-900">{{ customer.name }}</span>
                                <span class="text-sm text-gray-600">{{ customer.phone }}</span>
                        </div>
                            <div class="text-xs text-gray-500 truncate">
                                {{ customer.address || 'No address available' }}
                        </div>
                    </div>
                    </div>

                    <!-- No Results Message -->
                    <div v-else-if="phoneSearch && phoneSearch.length >= 1 && !isSearching && !checkoutProps.form.customer_id"
                        class="absolute z-50 left-0 right-0 mt-1 p-3 bg-white border rounded-lg shadow-lg text-center">
                        <span class="text-sm text-gray-500">No customers found</span>
                    </div>
                </div>
            </div>

            <!-- Delivery Address -->
            <div v-if="checkoutProps.form.order_method === orderMethodEnum.DELIVERY" class="mb-4">
                <label class="block">
                    <textarea
                        v-model="deliveryAddress"
                        rows="2"
                        :disabled="!checkoutProps.form.customer_id"
                        :placeholder="checkoutProps.form.customer_id ? 'Enter delivery address' : 'Select a customer first'"
                        class="mt-1 block w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary resize-none disabled:bg-gray-50 disabled:text-gray-500">
                    </textarea>
                </label>
            </div>

            <div class="db-field">
                <input class="db-field-control text-sm rounded-lg appearance-none text-heading border-[#D9DBE9]" id="token"
                    v-model="checkoutProps.form.token" placeholder="Token No" />
            </div>
        </div>
        <table class="w-full">
            <thead class="bg-[#FFEDF4]">
                <tr class="h-9">
                    <th class="capitalize text-xs font-normal font-rubik text-left pl-3 text-heading"></th>
                    <th class="capitalize text-xs font-normal font-rubik text-left px-3 text-heading">
                        {{ $t('label.item') }}
                    </th>
                    <th class="capitalize text-xs font-normal font-rubik text-left px-3 text-heading">
                        {{ $t('label.qty') }}
                    </th>
                    <th class="capitalize text-xs font-normal font-rubik text-left px-3 text-heading">
                        {{ $t('label.price') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(cart, index) in carts">
                    <td class="pl-3 py-3 last:pr-3 align-top border-b border-[#EFF0F6]">
                        <button @click.prevent="deleteCartItem(index)">
                            <i class="lab lab-trash-line-2 font-fill-danger"></i>
                        </button>
                    </td>
                    <td class="pl-3 py-3 last:pr-3 align-top border-b border-[#EFF0F6]">
                        <h3 class="capitalize text-xs font-rubik text-[#2E2F38]">{{ cart.name }}</h3>
                        <p v-if="Object.keys(cart.item_variations.variations).length !== 0">
                            <span v-for="(variation, variationName, idx) in cart.item_variations.names" :key="variationName + '_' + idx">
                                <span class="capitalize text-[10px] leading-4 font-rubik text-heading">{{
                                    variationName
                                }}:
                                    &nbsp;</span>
                                <span class="capitalize text-[10px] leading-4 font-rubik">{{ variation }}, &nbsp;</span>
                            </span>
                        </p>
                        <ul v-if="cart.item_extras.extras.length > 0 || cart.instruction !== ''">
                            <li v-if="cart.item_extras.extras.length > 0" class="leading-4">
                                <span class="capitalize text-[10px] leading-4 font-rubik text-heading">
                                    {{ $t('label.extras') }}:
                                </span>
                                <p class="capitalize text-[10px] leading-4 font-rubik">
                                    <span v-for="(extra, idx) in cart.item_extras.names" :key="'extra_' + idx">
                                        {{ extra }}, &nbsp;
                                    </span>
                                </p>
                            </li>
                            <li v-if="cart.instruction !== ''" class="leading-4">
                                <span class="capitalize text-[10px] leading-4 font-rubik text-heading">
                                    {{ $t('label.instruction') }}:
                                </span>
                                <span class="capitalize text-[10px] leading-4 font-rubik">
                                    {{ cart.instruction }}
                                </span>
                            </li>
                        </ul>
                    </td>
                    <td class="pl-3 py-3 last:pr-3 align-top border-b border-[#EFF0F6]">
                        <div class="flex items-center indec-group">
                            <button @click.prevent="cartQuantityDecrement(index)"
                                :class="cart.quantity === 1 ? 'fa-trash-can' : 'fa-minus'"
                                class="fa-solid text-[10px] w-[18px] h-[18px] leading-4 text-center rounded-full border transition text-primary border-primary hover:bg-primary hover:text-white indec-minus"></button>
                            <input v-on:keypress="onlyNumber($event)" v-on:keyup="cartQuantityUp(index, $event)"
                                type="number" :value="cart.quantity"
                                class="text-center w-7 text-xs font-semibold text-heading indec-value">
                            <button @click.prevent="cartQuantityIncrement(index)"
                                class="fa-solid fa-plus text-[10px] w-[18px] h-[18px] leading4 text-center rounded-full border transition text-primary border-primary hover:bg-primary hover:text-white indec-plus"></button>
                        </div>
                    </td>
                    <td class="pl-3 py-3 last:pr-3 align-top border-b border-[#EFF0F6] text-xs font-rubik text-heading">
                        {{
                            currencyFormat(cart.total, setting.site_digit_after_decimal_point,
                                setting.site_default_currency_symbol, setting.site_currency_position)
                        }}
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="p-4">
            <div class="flex h-[38px] mt-2" v-if="carts.length > 0">
                <input v-on:keypress="floatNumber($event)" v-model="discount" type="text"
                    :placeholder="$t('label.add_discount')" class="w-full h-full border-t border-b px-3 border-[#EFF0F6]">
                <button @click.prevent="applyDiscount" type="submit"
                    class="flex-shrink-0 w-16 h-full text-sm font-medium font-rubik capitalize ltr:rounded-tr-lg ltr:rounded-br-lg rtl:rounded-tl-lg rtl:rounded-bl-lg text-white bg-[#008BBA]">
                    {{ $t('button.apply') }}
                </button>
            </div>
            <small class="db-field-alert" v-if="discountErrorMessage">{{ discountErrorMessage }}</small>

            <div class="flex h-[38px] mt-2 mb-2" v-if="carts.length > 0">
                <div class="w-full db-field-down-arrow">
                    <select v-model="checkoutProps.form.pos_payment_method"
                        class="w-full h-full text-sm font-rubik rounded-tl rounded-bl appearance-none border pl-3 text-heading border-[#EFF0F6]">
                        <option :value="posPaymentMethodEnum.CASH">{{ $t("label.cash") }}</option>
                        <option :value="posPaymentMethodEnum.UPI">{{ $t("label.upi") }}</option>
                        <option :value="posPaymentMethodEnum.SPLIT">Split</option>
                    </select>
                </div>
            </div>

            <small class="db-field-alert" v-if="checkoutProps.form.pos_payment_method == posPaymentMethodEnum.SPLIT && splitErrorMessage">{{ splitErrorMessage }}</small>

            <!-- Tipping Section -->
            <div class="mt-4 mb-4" v-if="carts.length > 0">
                <button @click.prevent="showTipSection = !showTipSection" 
                    class="flex items-center justify-between w-full p-3 bg-gray-50 rounded-lg border border-gray-200 transition hover:bg-gray-100">
                    <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider flex items-center gap-2">
                        <i :class="showTipSection ? 'fa-solid fa-chevron-down' : 'fa-solid fa-chevron-right'"></i>
                        Add Tip
                    </span>
                    <span v-if="checkoutProps.form.tip_amount > 0" class="text-xs font-bold text-primary">
                        {{ currencyFormat(checkoutProps.form.tip_amount, setting.site_digit_after_decimal_point, setting.site_default_currency_symbol, setting.site_currency_position) }}
                    </span>
                </button>
                
                <div v-show="showTipSection" class="mt-2 p-3 bg-white rounded-lg border border-gray-100 shadow-sm transition-all duration-300">
                    <div class="flex gap-2 items-center">
                        <div class="flex-1 min-w-0" v-if="isAdmin">
                            <vue-select class="tip-dropdown text-xs rounded-lg appearance-none text-heading border-[#D9DBE9] w-full"
                                id="tipEmployee"
                                v-model="checkoutProps.form.tip_employee_id"
                                :options="employees"
                                label-by="name"
                                value-by="id"
                                :searchable="true"
                                :close-on-select="true"
                                :reduce="emp => emp.id"
                                placeholder="Select Employee"
                            />
                        </div>
                        <div class="flex-1" v-else>
                            <span class="text-xs text-gray-500 italic block mb-1">Tip for: {{ authInfo.name }}</span>
                        </div>
                        <div :class="isAdmin ? 'w-24' : 'w-32'">
                            <input type="number" step="0.01" min="0" placeholder="0.00"
                                v-model="checkoutProps.form.tip_amount"
                                class="w-full h-[32px] text-xs font-rubik px-2 rounded border border-[#EFF0F6] text-heading focus:border-primary">
                        </div>
                    </div>
                </div>
            </div>

            <ul class="flex flex-col gap-1.5 mb-4 mt-4">
                <li class="flex items-center justify-between">
                    <span class="text-sm font-rubik capitalize leading-6 text-[#2E2F38]">
                        {{ $t("label.sub_total") }}
                    </span>
                    <span class="text-sm font-rubik capitalize leading-6 text-[#2E2F38]">
                        {{
                            currencyFormat(subtotal, setting.site_digit_after_decimal_point,
                                setting.site_default_currency_symbol, setting.site_currency_position)
                        }}
                    </span>
                </li>
                <li class="flex items-center justify-between">
                    <span class="text-sm font-rubik capitalize leading-6">{{ $t("label.discount") }}</span>
                    <span class="text-sm font-rubik capitalize leading-6">{{
                        currencyFormat(posDiscount,
                            setting.site_digit_after_decimal_point, setting.site_default_currency_symbol,
                            setting.site_currency_position)
                    }}</span>
                </li>
                <li class="flex items-center justify-between" v-if="checkoutProps.form.tip_amount > 0">
                    <span class="text-sm font-rubik capitalize leading-6 text-primary">
                        Tip Income
                    </span>
                    <span class="text-sm font-rubik capitalize leading-6 text-primary">
                        {{
                            currencyFormat(checkoutProps.form.tip_amount, setting.site_digit_after_decimal_point,
                                setting.site_default_currency_symbol, setting.site_currency_position)
                        }}
                    </span>
                </li>
                <li class="flex items-center justify-between">
                    <span class="text-sm font-medium font-rubik capitalize leading-6 text-[#2E2F38]">
                        Total Sale
                    </span>
                    <span class="text-sm font-medium font-rubik capitalize leading-6 text-[#2E2F38]">
                        {{
                            currencyFormat(subtotal - posDiscount,
                                setting.site_digit_after_decimal_point, setting.site_default_currency_symbol,
                                setting.site_currency_position)
                        }}
                    </span>
                </li>
            </ul>
            <div class="flex items-center justify-center gap-6" v-if="carts.length > 0">
                <button @click.prevent="resetCart"
                    class="capitalize text-sm font-medium leading-6 font-rubik w-full text-center rounded-3xl py-2 text-white bg-[#FB4E4E]">
                    {{ $t('button.cancel') }}
                </button>
                <button @click.prevent="orderUpdate"
                    class="capitalize text-sm font-medium leading-6 font-rubik w-full text-center rounded-3xl py-2 text-white bg-[#1AB759]">
                    {{ $t('Update Order') }}
                </button>
            </div>
        </div>
    </div>

    <button
        class="db-pos-cartBtn fixed md:hidden bottom-0 z-10 left-0 w-full h-14 py-4 text-center flex items-center justify-center shadow-xl-top gap-3 bg-primary">
        <i class="lab lab-bag-2 lab-font-size-13 text-white"></i>
        <span class="text-base font-medium font-rubik text-white">
            {{ totalItems() }} {{ $t('label.items') }} - {{
                currencyFormat(subtotal - posDiscount,
                    setting.site_digit_after_decimal_point, setting.site_default_currency_symbol,
                    setting.site_currency_position)
            }}
        </span>
    </button>

    <ReceiptComponent :order="order" />

    <!-- New Customer Modal -->
    <div v-if="showNewCustomerModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Add New Customer</h3>
                <button @click="closeNewCustomerModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form @submit.prevent="saveNewCustomer" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <input v-model="newCustomerForm.name" type="text"
                            placeholder="Name"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors duration-200"
                            :class="{'border-red-500 focus:ring-red-200': newCustomerErrors.name}" />
                        <small class="text-red-500 text-xs mt-1" v-if="newCustomerErrors.name">
                            {{ newCustomerErrors.name[0] }}
                        </small>
                    </div>

                    <div>
                        <input v-model="newCustomerForm.email" type="email"
                            placeholder="Email"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors duration-200"
                            :class="{'border-red-500 focus:ring-red-200': newCustomerErrors.email}" />
                        <small class="text-red-500 text-xs mt-1" v-if="newCustomerErrors.email">
                            {{ newCustomerErrors.email[0] }}
                        </small>
                    </div>

                    <div>
                        <div class="flex gap-2">
                            <input v-model="newCustomerForm.country_code" type="text"
                                class="w-20 px-4 py-2.5 rounded-lg border border-gray-300 bg-gray-50 text-gray-500"
                                placeholder="+91" readonly />
                            <input v-model="newCustomerForm.phone"
                                @keypress="phoneNumber($event)"
                                type="text"
                                placeholder="Phone Number"
                                class="flex-1 px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors duration-200"
                                :class="{'border-red-500 focus:ring-red-200': newCustomerErrors.phone}" />
                        </div>
                        <small class="text-red-500 text-xs mt-1" v-if="newCustomerErrors.phone">
                            {{ newCustomerErrors.phone[0] }}
                        </small>
                    </div>

                    <div>
                        <input v-model="newCustomerForm.address" type="text"
                            placeholder="Delivery Address"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors duration-200"
                            :class="{'border-red-500 focus:ring-red-200': newCustomerErrors.address}" />
                        <small class="text-red-500 text-xs mt-1" v-if="newCustomerErrors.address">
                            {{ newCustomerErrors.address[0] }}
                        </small>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="closeNewCustomerModal"
                        class="px-6 py-2.5 text-sm font-medium border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-6 py-2.5 text-sm font-medium text-white bg-primary rounded-lg hover:bg-primary-dark transition-colors duration-200">
                        Save Customer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="receiptModalLabel">{{ $t('label.order_receipt') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="print">
                    <ReceiptComponent :order="order" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $t('button.close') }}</button>
                    <button type="button" v-print="printObj" class="btn btn-primary">
                        <i class="lab lab-printer-line lab-font-size-16"></i>
                        {{ $t('button.print') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import LoadingComponent from "../components/LoadingComponent";
import 'vue3-carousel/dist/carousel.css';
import ItemComponent from "./ItemComponent";
import sourceEnum from "../../../enums/modules/sourceEnum";
import orderTypeEnum from "../../../enums/modules/orderTypeEnum";
import isAdvanceOrderEnum from "../../../enums/modules/isAdvanceOrderEnum";
import statusEnum from "../../../enums/modules/statusEnum";
import roleEnum from "../../../enums/modules/roleEnum";
import appService from "../../../services/appService";
import discountTypeEnum from "../../../enums/modules/discountTypeEnum";
import displayModeEnum from "../../../enums/modules/displayModeEnum";
import alertService from "../../../services/alertService";
import ReceiptComponent from "./ReceiptComponent";
import posPaymentMethodEnum from "../../../enums/modules/posPaymentMethodEnum";
import { Swiper, SwiperSlide } from 'swiper/vue';
import 'swiper/css';
import axios from 'axios';
import print from 'vue3-print-nb';
import orderMethodEnum from "../../../enums/modules/orderMethodEnum";
import { Navigation, Pagination, A11y } from 'swiper/modules';

export default {
    name: "PosComponentEdit",
    components: {
        ReceiptComponent,
        LoadingComponent,
        ItemComponent,
        Swiper,
        SwiperSlide,
    },
    directives: {
        print
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            order: {},
            editingOrderId: null,
            discount: null,
            showNewCustomerModal: false,
            selectedCustomerDetails: null,
            newCustomerForm: {
                name: '',
                email: '',
                phone: '',
                country_code: '91',
                password: '123456',
                password_confirmation: '123456',
                address: '',
                status: statusEnum.ACTIVE,
                role_id: roleEnum.CUSTOMER
            },
            newCustomerErrors: {},
            company: {
                name: '',
                email: '',
                phone: '',
                address: ''
            },
            checkoutProps: {
                form: {
                    branch_id: null,
                    subtotal: 0,
                    token: "",
                    customer_id: null,
                    discount: 0,
                    delivery_charge: 0,
                    delivery_time: null,
                    total: 0,
                    order_type: orderTypeEnum.POS,
                    order_method: orderMethodEnum.DINE_IN,
                    is_advance_order: isAdvanceOrderEnum.NO,
                    pos_payment_method: posPaymentMethodEnum.CASH,
                    pos_payment_note: '',
                    pos_split_cash: null,
                    pos_split_online: null,
                    source: sourceEnum.POS,
                    address_id: null,
                    coupon_id: null,
                    items: [],
                    tip_amount: 0,
                    tip_employee_id: null
                }
            },
            employees: [],
            showTipSection: false,
            props: {
                search: {
                    paginate: 0,
                    order_column: "id",
                    order_type: "asc",
                    name: "",
                    item_category_id: "",
                    status: statusEnum.ACTIVE
                },
            },
            categoryProps: {
                paginate: 0,
                order_column: 'sort',
                order_type: 'asc',
                status: statusEnum.ACTIVE
            },

            statusEnum: statusEnum,
            discountTypeEnum: discountTypeEnum,
            discountType: discountTypeEnum.PERCENTAGE,
            discountErrorMessage: "",
            splitErrorMessage: "",
            posPaymentMethodEnum: posPaymentMethodEnum,
            printObj: {
                id: "print",
                popTitle: this.$t("menu.order_receipt"),
            },
            orderMethodEnum: orderMethodEnum,
            orderMethodOptions: [
                { id: orderMethodEnum.DINE_IN, name: 'Dine In' },
                { id: orderMethodEnum.TAKEAWAY, name: 'Takeaway' },
                { id: orderMethodEnum.DELIVERY, name: 'Delivery' }
            ],
            phoneSearch: '',
            phoneSearchResults: [],
            isSearching: false,
            countryCode: '91',
            deliveryAddress: '',
            swiperModules: [Navigation, Pagination, A11y],
            swiper: null,
        }
    },
    computed: {
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
        categories: function () {
            return this.$store.getters["posCategory/lists"];
        },
        items: function () {
            return this.$store.getters["item/lists"];
        },
        customers: function () {
            return this.$store.getters['user/lists'];
        },
        carts: function () {
            return this.$store.getters['posCart/lists'];
        },
        subtotal: function () {
            return this.$store.getters['posCart/subtotal'];
        },
        posDiscount: function () {
            return this.$store.getters['posCart/discount'];
        },
        direction: function () {
            return this.$store.getters['frontendLanguage/show'].display_mode === displayModeEnum.RTL ? 'rtl' : 'ltr';
        },
        authInfo: function () {
            return this.$store.getters.authInfo;
        },
        isAdmin: function () {
            return this.authInfo && this.authInfo.role_id === roleEnum.ADMIN;
        },
        getSelectedCustomer() {
            if (!this.checkoutProps.form.customer_id || !this.customers) {
                return null;
            }
            return this.customers.find(c => c && c.id === this.checkoutProps.form.customer_id) || null;
        },
    },
    mounted() {
        try {
            this.loading.isActive = true;

            // Load categories first
            this.$store.dispatch('itemCategory/lists', this.categoryProps)
                .then((res) => {
                    // Add "All Category" as the first item
                    const allCategories = [
                        {
                            id: 0,
                            name: this.$t('ALL'),
                            thumb: '/images/default/all-category.png'
                        },
                        ...(res.data.data || [])
                    ];
                    
                    // Update the Vuex store with the combined categories
                    this.$store.commit('posCategory/lists', allCategories);
                })
                .catch(error => {
                    console.error('Error loading categories:', error);
                    alertService.error(this.$t('message.categories_load_failed'));
                });

            // Fetch employees for tips
            axios.get('/api/admin/employee/pos-list').then(res => {
                this.employees = res.data.data;
                // If not admin, automatically set the logged in user as the tip recipient
                if (!this.isAdmin && this.authInfo) {
                    this.checkoutProps.form.tip_employee_id = this.authInfo.id;
                }
            }).catch(err => {
                console.log('Error fetching employees:', err);
            });

            // Note: Order data and cart items are loaded by initializeFromRoute() method
            // which is triggered by the route watcher with immediate: true
        } catch (err) {
            console.error('Error in mounted:', err);
            this.loading.isActive = false;
            alertService.error(this.$t('message.load_failed'));
        }
    },
    watch: {
        // Add watcher for categories
        categories: {
            immediate: true,
            deep: true,
            handler(newCategories) {
                console.log('Categories changed:', newCategories);
                if (!newCategories || newCategories.length <= 1) {
                    // If no categories or just one category, try to reload them
                    this.$store.dispatch('itemCategory/lists', this.categoryProps)
                        .then(() => {
                            console.log('Categories reloaded successfully');
                            if (this.swiper) {
                                this.$nextTick(() => {
                                    this.swiper.update();
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error reloading categories:', error);
                        });
                } else {
                    if (this.swiper) {
                        this.$nextTick(() => {
                            this.swiper.update();
                        });
                    }
                }
            }
        },
        '$route.query.order_id': {
            handler(orderId) {
                if (orderId && (!this.editingOrderId || orderId !== this.editingOrderId)) {
                    this.initializeFromRoute();
                }
            },
            immediate: true
        },
        'checkoutProps.form.order_method': {
            async handler(newMethod) {
                if (this.$route.query.order_id && newMethod) {
                    try {
                        const orderId = this.$route.query.order_id;
                        await this.$store.dispatch('posOrder/changeOrderMethod', {
                            id: orderId,
                            order_method: newMethod
                        });

                        this.$store.commit('posCart/UPDATE_ORDER_METHOD', newMethod);
                        this.updateLocalStorage();
                        alertService.successFlip(1, this.$t('message.order_method_updated'));
                    } catch (error) {
                        console.error('Error updating order method:', error);
                        alertService.error(error.response?.data?.message || this.$t('message.order_method_update_failed'));
                        this.checkoutProps.form.order_method = this.order.order_method;
                    }
                } else {
                    this.$store.commit('posCart/UPDATE_ORDER_METHOD', newMethod);
                    this.updateLocalStorage();
                }
            }
        },
        carts: {
            handler(newCarts) {
                if (newCarts && newCarts.length > 0) {
                    this.updateLocalStorage();
                } else {
                    this.$router.push({ name: 'admin.pos-order.index' });
                }
            },
            deep: true
        },
        subtotal: {
            handler(newSubtotal) {
                if (this.carts && this.carts.length > 0) {
                    this.updateLocalStorage();
                }
            }
        },
        customers: {
            handler(newCustomers) {
                // If we have a customer_id but no selected customer, try to select it from the new customer list
                if (this.checkoutProps.form.customer_id && !this.getSelectedCustomer && newCustomers) {
                    const customer = newCustomers.find(c => c.id === this.checkoutProps.form.customer_id);
                    if (customer) {
                        console.log('Re-selecting customer from new customer list:', customer);
                        this.handleCustomerSelect(customer);
                    }
                }
            },
            deep: true
        }
    },
    methods: {
        initializeFromRoute() {
            const orderId = this.$route.query.order_id;
            if (!orderId) {
                alertService.error('No order ID provided');
                return;
            }

            // Reset the cart first
            this.$store.dispatch('posCart/resetCart');

            this.editingOrderId = orderId;
            this.loading.isActive = true;

            // First load the order data to get the linked user
            this.$store.dispatch('posOrder/show', orderId)
                    .then((res) => {
                        if (!res.data || !res.data.data) {
                            throw new Error('Invalid order data received');
                        }

                        const orderData = res.data.data;

                    // Get customers list after getting order data
                    return this.$store.dispatch('user/lists', {
                        order_column: 'id',
                        order_type: 'asc',
                        status: statusEnum.ACTIVE,
                        role_id: roleEnum.CUSTOMER
                    }).then((customerRes) => {
                        // Set form data with validation
                        this.checkoutProps.form.token = orderData.token || '';

                        // Set customer_id from order's user
                        if (orderData.user && orderData.user.id) {
                            this.checkoutProps.form.customer_id = orderData.user.id;

                            // If it's a delivery order, set the customer details and delivery fields
                            if (orderData.order_method === orderMethodEnum.DELIVERY) {
                                this.selectedCustomerDetails = {
                                    name: orderData.user.name || '',
                                    phone: orderData.user.phone || '',
                                    email: orderData.user.email || '',
                                    address: orderData.user.address || orderData.order_address || ''
                                };
                                
                                // Set delivery address field
                                this.deliveryAddress = orderData.order_address || orderData.user.address || '';
                                
                                // Set phone search and country code for delivery
                                if (orderData.user.phone) {
                                    this.phoneSearch = orderData.user.phone;
                                    this.countryCode = orderData.user.country_code || '91';
                                }
                            }
                        }

                        this.checkoutProps.form.pos_payment_method = orderData.pos_payment_method || posPaymentMethodEnum.CASH;
                        this.checkoutProps.form.pos_payment_note = orderData.pos_payment_note || '';
                        this.checkoutProps.form.pos_split_cash = orderData.pos_split_cash || null;
                        this.checkoutProps.form.pos_split_online = orderData.pos_split_online || null;
                        this.checkoutProps.form.order_method = orderData.order_method || orderMethodEnum.DINE_IN;
                        this.checkoutProps.form.tip_amount = orderData.tip_amount || 0;
                        this.checkoutProps.form.tip_employee_id = orderData.tip_employee_id || null;

                        // If not admin and no tip employee set, set to current user
                        if (!this.isAdmin && !this.checkoutProps.form.tip_employee_id && this.authInfo) {
                            this.checkoutProps.form.tip_employee_id = this.authInfo.id;
                        }

                        // Handle discount
                        if (orderData.discount && parseFloat(orderData.discount) > 0) {
                            this.discount = orderData.discount;
                            this.discountType = discountTypeEnum.FIXED;
                        }

                        // Validate order items exist
                        if (!orderData.order_items || !Array.isArray(orderData.order_items)) {
                            throw new Error('No order items found');
                        }

                        // Format cart items from order data with validation
                        const cartItems = orderData.order_items.map(item => {
                            try {
                                // Ensure required fields exist and are valid
                                if (!item.item_id || !item.item_name) {
                                    throw new Error(`Invalid item data: ${JSON.stringify(item)}`);
                                }

                                const quantity = parseInt(item.quantity) || 1;
                                const total_convert_price = parseFloat(item.total_convert_price) || 0;
                                const unitPrice = quantity > 0 ? total_convert_price / quantity : 0;
                                const variationTotal = parseFloat(item.item_variation_total) || 0;
                                const extraTotal = parseFloat(item.item_extra_total) || 0;

                                // Process variations
                                let variations = {};
                                let variationNames = {};

                                if (Array.isArray(item.item_variations)) {
                                    item.item_variations.forEach(variation => {
                                        if (variation.item_attribute_id && variation.id) {
                                            variations[variation.item_attribute_id] = variation.id;
                                            if (variation.variation_name && variation.name) {
                                                variationNames[variation.variation_name] = variation.name;
                                            }
                                        }
                                    });
                                } else if (typeof item.item_variations === 'string' && item.item_variations) {
                                    try {
                                        const parsedVariations = JSON.parse(item.item_variations);
                                        if (Array.isArray(parsedVariations)) {
                                            parsedVariations.forEach(variation => {
                                                if (variation.item_attribute_id && variation.id) {
                                                    variations[variation.item_attribute_id] = variation.id;
                                                    if (variation.variation_name && variation.name) {
                                                        variationNames[variation.variation_name] = variation.name;
                                                    }
                                                }
                                            });
                                        }
                                    } catch (e) {
                                        console.error('Error parsing variations:', e);
                                    }
                                }

                                // Process extras
                                let extras = [];
                                let extraNames = [];

                                if (Array.isArray(item.item_extras)) {
                                    item.item_extras.forEach(extra => {
                                        if (extra.id) {
                                            extras.push(extra.id);
                                            if (extra.name) {
                                                extraNames.push(extra.name);
                                            }
                                        }
                                    });
                                } else if (typeof item.item_extras === 'string' && item.item_extras) {
                                    try {
                                        const parsedExtras = JSON.parse(item.item_extras);
                                        if (Array.isArray(parsedExtras)) {
                                            parsedExtras.forEach(extra => {
                                                if (extra.id) {
                                                    extras.push(extra.id);
                                                    if (extra.name) {
                                                        extraNames.push(extra.name);
                                                    }
                                                }
                                            });
                                        }
                                    } catch (e) {
                                        console.error('Error parsing extras:', e);
                                    }
                                }

                                return {
                                    name: item.item_name,
                                    image: item.item_image || '',
                                    item_id: item.item_id,
                                    quantity: quantity,
                                    discount: parseFloat(item.discount) || 0,
                                    currency_price: parseFloat(item.price) || 0,
                                    convert_price: unitPrice,
                                    item_variations: {
                                        variations: variations,
                                        names: variationNames
                                    },
                                    item_extras: {
                                        extras: extras,
                                        names: extraNames
                                    },
                                    item_variation_total: variationTotal,
                                    item_extra_total: extraTotal,
                                    instruction: item.instruction || "",
                                    total: (unitPrice + variationTotal + extraTotal) * quantity
                                };
                            } catch (itemError) {
                                console.error('Error processing item:', itemError);
                                return null;
                            }
                        }).filter(item => item !== null); // Remove any invalid items

                        if (cartItems.length === 0) {
                            throw new Error('No valid items could be processed');
                        }

                        // Load cart items and apply discount
                        return this.$store.dispatch('posCart/lists', cartItems)
                            .then(() => {
                                if (orderData.discount > 0) {
                                    return this.$store.dispatch('posCart/discount', orderData.discount);
                                }
                            })
                            .then(() => {
                                // Update token in Vuex store
                                this.$store.dispatch('posCart/updateToken', orderData.token || '');
                                // Update localStorage with cart data and token
                                this.updateLocalStorage();
                                this.loading.isActive = false;
                            });
                    });
            })
            .catch((err) => {
                this.loading.isActive = false;
                console.error('Error in initializeFromRoute:', err);
                alertService.error(err.message || err.response?.data?.message || 'Error loading order details');
                // Redirect back to list on error
                this.$router.push({ name: 'admin.pos.orders.list' });
            });
        },
        updateLocalStorage() {
            try {
                let vuexData = {};
                const storedData = localStorage.getItem('vuex');
                if (storedData) {
                    vuexData = JSON.parse(storedData);
                }

                // Calculate the total using original prices
                const subtotal = this.carts.reduce((sum, item) => {
                    const convertPrice = parseFloat(item.convert_price) || 0;
                    const variationTotal = parseFloat(item.item_variation_total) || 0;
                    const extraTotal = parseFloat(item.item_extra_total) || 0;
                    const quantity = parseInt(item.quantity) || 1;
                    const itemTotal = (convertPrice + variationTotal + extraTotal) * quantity;
                    return sum + itemTotal;
                }, 0);

                // Create cart object with the required structure
                const cartObject = {
                    lists: this.carts.map(item => ({
                        discount: parseFloat(item.discount) || 0,
                        image: item.image,
                        instruction: item.instruction || "",
                        item_extra_total: parseFloat(item.item_extra_total) || 0,
                        item_extras: item.item_extras || { extras: [], names: [] },
                        item_id: item.item_id,
                        item_variation_total: parseFloat(item.item_variation_total) || 0,
                        item_variations: item.item_variations || { variations: {}, names: {} },
                        name: item.name,
                        currency_price: item.currency_price,
                        convert_price: parseFloat(item.convert_price) || 0,
                        quantity: parseInt(item.quantity) || 1,
                        total: (parseFloat(item.convert_price) +
                               parseFloat(item.item_variation_total) +
                               parseFloat(item.item_extra_total)) * item.quantity
                    })),
                    subtotal: subtotal,
                    discount: parseFloat(this.posDiscount) || 0,
                    orderType: null,
                    total: 0,
                    token: this.checkoutProps.form.token || ""
                };

                // Update the posCart in vuex data
                if (!vuexData.posCart) {
                    vuexData.posCart = {};
                }
                vuexData.posCart = cartObject;

                // Save back to localStorage
                localStorage.setItem('vuex', JSON.stringify(vuexData));
            } catch (error) {
                console.error('Error updating localStorage:', error);
            }
        },

        onlyNumber: function (e) {
            return appService.onlyNumber(e);
        },
        floatNumber: function (e) {
            return appService.floatNumber(e);
        },
        currencyFormat: function (amount, decimal, currency, position) {
            return appService.currencyFormat(amount, decimal, currency, position);
        },
        search: function () {
            this.itemList();
        },
        allCategory: function () {
            this.props.search.name = "";
            this.props.search.item_category_id = "";
            this.itemList();
        },
        setCategory: function (id) {
            this.props.search.item_category_id = id;
            this.itemList();
        },
        itemCategories: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch("posCategory/lists", this.categoryProps).then((res) => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        itemList: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch("item/lists", this.props.search).then((res) => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        cartQuantityUp: function (id, e) {
            if (e.target.value > 0) {
                this.$store.dispatch('posCart/quantity', { id: id, status: e.target.value }).then(() => {
                    this.updateLocalStorage();
                }).catch();
            }
        },
        cartQuantityIncrement: function (id) {
            this.$store.dispatch('posCart/quantity', { id: id, status: "increment" }).then(() => {
                this.updateLocalStorage();
            }).catch();
        },
        cartQuantityDecrement: function (id) {
            this.$store.dispatch('posCart/quantity', { id: id, status: "decrement" }).then(() => {
                this.updateLocalStorage();
            }).catch();
        },
        deleteCartItem: function (id) {
            this.$store.dispatch('posCart/deleteCartItem', { id: id }).then(() => {
                this.updateLocalStorage();
            }).catch();
        },
        applyDiscount: function () {
            this.discountErrorMessage = "";
            if (this.discountType == discountTypeEnum.FIXED) {
                if (this.subtotal < this.discount) {
                    this.discountErrorMessage = this.$t('message.discount_fixed_error_message');
                } else {
                    this.checkoutProps.form.discount = parseFloat(+this.discount).toFixed(this.setting.site_digit_after_decimal_point);
                    this.$store.dispatch('posCart/discount', this.checkoutProps.form.discount).then().catch();
                }

            } else {
                if (this.discount > 100) {
                    this.discountErrorMessage = this.$t('message.discount_error_message');
                } else {

                    this.checkoutProps.form.discount = parseFloat((this.subtotal * this.discount) / 100).toFixed(this.setting.site_digit_after_decimal_point);
                    this.$store.dispatch('posCart/discount', this.checkoutProps.form.discount).then().catch();

                }
            }
        },
        orderUpdate() {
            if (!this.checkoutProps.form.customer_id) {
                alertService.error(this.$t('message.please_select_customer'));
                return;
            }

            if (this.checkoutProps.form.order_method === this.orderMethodEnum.DELIVERY && !this.deliveryAddress) {
                alertService.error(this.$t('message.please_enter_delivery_address'));
                return;
            }

            // Prevent multiple submissions
            if (this.loading.isActive) {
                console.log('Update already in progress');
                return;
            }

            try {
                this.loading.isActive = true;
                console.log('Starting order update...');

                // Calculate totals first
                let subtotal = 0;
                const itemsData = this.carts.map(item => {
                    const convertPrice = parseFloat(item.convert_price) || 0;
                    const itemTotal = (convertPrice +
                        (parseFloat(item.item_variation_total) || 0) +
                        (parseFloat(item.item_extra_total) || 0)) * item.quantity;
                    subtotal += itemTotal;

                    // Prepare item data
                    let item_variations = [];
                    if (item.item_variations && item.item_variations.variations && Object.keys(item.item_variations.variations).length > 0) {
                        _.forEach(item.item_variations.variations, (value, index) => {
                            item_variations.push({
                                id: value,
                                item_id: item.item_id,
                                item_attribute_id: index,
                            });
                        });

                        if (item.item_variations.names && Object.keys(item.item_variations.names).length > 0) {
                            let i = 0;
                            _.forEach(item.item_variations.names, (value, index) => {
                                if (item_variations[i]) {
                                    item_variations[i].variation_name = index;
                                    item_variations[i].name = value;
                                    i++;
                                }
                            });
                        }
                    }

                    let item_extras = [];
                    if (item.item_extras && item.item_extras.extras && item.item_extras.extras.length) {
                        _.forEach(item.item_extras.extras, (value) => {
                            item_extras.push({
                                id: value,
                                item_id: item.item_id,
                            });
                        });

                        if (item.item_extras.names && item.item_extras.names.length) {
                            let i = 0;
                            _.forEach(item.item_extras.names, (value) => {
                                if (item_extras[i]) {
                                    item_extras[i].name = value;
                                    i++;
                                }
                            });
                        }
                    }

                    return {
                        item_id: item.item_id,
                        item_name: item.name,
                        item_price: convertPrice,
                        instruction: item.instruction || '',
                        quantity: parseInt(item.quantity) || 1,
                        discount: parseFloat(item.discount) || 0,
                        total_price: itemTotal,
                        item_variation_total: parseFloat(item.item_variation_total) || 0,
                        item_extra_total: parseFloat(item.item_extra_total) || 0,
                        item_variations: item_variations,
                        item_extras: item_extras,
                        price: convertPrice
                    };
                });

                // Get branch ID
                this.$store.dispatch("defaultAccess/show").then((res) => {
                    const branchId = res.data.data.branch_id;

                    // Set totals
                    const discount = parseFloat(this.checkoutProps.form.discount) || 0;
                    const total = subtotal - discount;
                    const tipAmt = parseFloat(this.checkoutProps.form.tip_amount) || 0;
                    const computedTotal = total + tipAmt;

                    if (this.checkoutProps.form.pos_payment_method == this.posPaymentMethodEnum.SPLIT) {
                        let cashAmt = parseFloat(this.checkoutProps.form.pos_split_cash) || 0;
                        let onlineAmt = parseFloat(this.checkoutProps.form.pos_split_online) || 0;
                        let splitTotal = cashAmt + onlineAmt;
                        if (Math.abs(splitTotal - computedTotal) > 0.05) {
                            this.splitErrorMessage = "Split amounts (including tip) must exactly equal the total order amount.";
                            this.loading.isActive = false;
                            return Promise.reject(new Error("Split amounts must exactly equal the total order amount."));
                        } else {
                            this.splitErrorMessage = "";
                        }
                    }

                    // Prepare update data
                    const updateData = {
                        id: this.$route.query.order_id,
                        customer_id: this.checkoutProps.form.customer_id,
                        branch_id: branchId,
                        order_method: this.checkoutProps.form.order_method,
                        pos_payment_method: this.checkoutProps.form.pos_payment_method || this.posPaymentMethodEnum.CASH,
                        pos_split_cash: this.checkoutProps.form.pos_split_cash,
                        pos_split_online: this.checkoutProps.form.pos_split_online,
                        pos_payment_note: this.checkoutProps.form.pos_payment_note || '',
                        token: this.checkoutProps.form.token || '',
                        subtotal: parseFloat(subtotal).toFixed(2),
                        discount: parseFloat(discount).toFixed(2),
                        total: parseFloat(total).toFixed(2),
                        total_amount_price: parseFloat(total).toFixed(2),
                        source: sourceEnum.POS,
                        order_type: orderTypeEnum.POS,
                        is_advance_order: isAdvanceOrderEnum.NO,
                        status: this.statusEnum.COMPLETED,
                        tip_amount: tipAmt,
                        tip_employee_id: this.checkoutProps.form.tip_employee_id
                    };

                    // Add delivery-specific data if needed
                    if (this.checkoutProps.form.order_method === this.orderMethodEnum.DELIVERY) {
                        updateData.delivery_address = this.deliveryAddress;
                        if (this.phoneSearch) {
                            updateData.delivery_phone = `${this.countryCode}${this.phoneSearch}`;
                        }
                        // Add user details for delivery
                        const selectedCustomer = this.getSelectedCustomer;
                        if (selectedCustomer) {
                            updateData.user = {
                                id: selectedCustomer.id,
                                name: selectedCustomer.name,
                                phone: selectedCustomer.phone,
                                address: selectedCustomer.address
                            };
                        }
                    }

                    // Set branch_id for each item
                    itemsData.forEach(item => {
                        item.branch_id = branchId;
                    });

                    // Convert items to JSON string
                    updateData.items = JSON.stringify(itemsData);

                    console.log('Sending update request with data:', updateData);

                    // Update the order
                    return this.$store.dispatch('posOrder/update', updateData);
                }).then(response => {
                    console.log('Order updated successfully:', response);
                    if (response.data && response.data.data) {
                        // Create receipt items from current cart with consolidation
                        const itemMap = new Map();
                        
                        // First pass: group identical items
                        this.carts.forEach(item => {
                            const key = JSON.stringify({
                                item_id: item.item_id,
                                variations: item.item_variations,
                                extras: item.item_extras,
                                instruction: item.instruction || ''
                            });
                            
                            if (itemMap.has(key)) {
                                const existingItem = itemMap.get(key);
                                existingItem.quantity += parseInt(item.quantity);
                                existingItem.total_price += parseFloat(item.total_price);
                                existingItem.total_without_tax_currency_price = existingItem.total_price.toFixed(2);
                            } else {
                                itemMap.set(key, {
                                    id: item.item_id,
                                    item_name: item.name,
                                    quantity: parseInt(item.quantity),
                                    price: parseFloat(item.convert_price),
                                    item_variations: item.item_variations,
                                    item_extras: item.item_extras,
                                    instruction: item.instruction || '',
                                    item_variation_total: parseFloat(item.item_variation_total) || 0,
                                    item_extra_total: parseFloat(item.item_extra_total) || 0,
                                    total_price: parseFloat(item.total_price),
                                    total_without_tax_currency_price: parseFloat(item.total_price).toFixed(2)
                                });
                            }
                        });

                        // Convert map values to array
                        const receiptItems = Array.from(itemMap.values());

                        // Create the updated order object
                        const updatedOrder = {
                            ...response.data.data,
                            order_items: receiptItems,
                            subtotal: parseFloat(subtotal).toFixed(2),
                            discount: parseFloat(this.checkoutProps.form.discount || 0).toFixed(2),
                            total: parseFloat(subtotal - (this.checkoutProps.form.discount || 0)).toFixed(2),
                            delivery_address: this.deliveryAddress || '',
                            delivery_phone: this.phoneSearch ? `${this.countryCode}${this.phoneSearch}` : '',
                            user: this.getSelectedCustomer || null
                        };

                        // Update both the local order and Vuex store
                        this.order = updatedOrder;
                        this.$store.commit('posOrder/orderItems', receiptItems);
                        this.$store.dispatch('posOrder/show', this.$route.query.order_id)
                            .then(() => {
                                // Show receipt modal
                                this.showReceipt = true;

                                this.$nextTick(() => {
                                    appService.modalShow('#receiptModal');
                                });

                            // Reset the form and cart after successful save
                            this.resetForm();
                            this.loading.isActive = false;

                                // Add event listener for modal close
                                const receiptModal = document.getElementById('receiptModal');
                                if (receiptModal) {
                                    const handleModalClose = () => {
                                        // Reset form and redirect only after modal is closed
                                        this.resetForm();
                                        this.$router.push({ name: 'admin.pos.orders.list' });
                                        // Remove the event listener
                                        receiptModal.removeEventListener('hidden.bs.modal', handleModalClose);
                                    };
                                    receiptModal.addEventListener('hidden.bs.modal', handleModalClose);
                                }

                                // Show success message
                                alertService.success(this.$t('message.update_success'));
                            })
                            .catch(error => {
                                console.error('Error fetching updated order:', error);
                            });
                    }
                }).catch(error => {
                    console.error('Error updating order:', error);
                    console.error('Error response:', error.response);
                    console.error('Error response data:', error.response?.data);
                    console.error('Error status:', error.response?.status);
                    console.error('Error headers:', error.response?.headers);

                    if (error.response?.data?.errors) {
                        Object.values(error.response.data.errors).forEach(error => {
                            alertService.error(error[0]);
                        });
                    } else if (error.response?.data?.message) {
                        alertService.error(error.response.data.message);
                    } else {
                        alertService.error(this.$t('message.update_failed'));
                    }
                }).finally(() => {
                    this.loading.isActive = false;
                });
            } catch (err) {
                console.error('Error in orderUpdate:', err);
                console.error('Error stack:', err.stack);
                this.loading.isActive = false;
                alertService.error(this.$t('message.update_failed'));
            }
        },
        resetForm() {
            this.checkoutProps.form.token = "";
            this.checkoutProps.form.subtotal = null;
            this.checkoutProps.form.total = 0;
            this.checkoutProps.form.total_amount_price = 0;
            this.checkoutProps.form.discount = 0;
            this.checkoutProps.form.delivery_time = null;
            this.checkoutProps.form.order_type = orderTypeEnum.POS;
            this.checkoutProps.form.is_advance_order = isAdvanceOrderEnum.NO;
            this.checkoutProps.form.source = sourceEnum.POS;
            this.checkoutProps.form.address_id = null;
            this.checkoutProps.form.coupon_id = null;
            this.checkoutProps.form.items = [];
            this.checkoutProps.form.pos_payment_method = this.posPaymentMethodEnum.CASH;
            this.checkoutProps.form.pos_payment_note = null;
            this.checkoutProps.form.pos_split_cash = null;
            this.checkoutProps.form.pos_split_online = null;
            this.discount = null;
            this.discountType = discountTypeEnum.PERCENTAGE;
            this.splitErrorMessage = "";

            this.$store.dispatch('posCart/resetCart');
        },
        totalItems: function () {
            if (this.carts.length > 0) {
                let totalItem = 0;
                this.carts.forEach(cart => {
                    totalItem += cart.quantity;
                });
                return totalItem;
            }
        },
        resetCart: function () {
            this.loading.isActive = true;
            this.$store.dispatch('posCart/resetCart').then(() => {
                this.checkoutProps.form.tip_amount = 0;
                this.showTipSection = false;
                this.loading.isActive = false;
                this.itemList();
            }).catch((err) => {
                // Reset the form data
                this.checkoutProps.form.token = "";
                this.checkoutProps.form.subtotal = null;
                this.checkoutProps.form.discount = 0;
                this.checkoutProps.form.delivery_time = null;
                this.checkoutProps.form.total = 0;
                this.checkoutProps.form.order_type = orderTypeEnum.POS;
                this.checkoutProps.form.is_advance_order = isAdvanceOrderEnum.NO;
                this.checkoutProps.form.source = sourceEnum.POS;
                this.checkoutProps.form.address_id = null;
                this.checkoutProps.form.coupon_id = null;
                this.checkoutProps.form.items = [];
                this.checkoutProps.form.pos_payment_method = this.posPaymentMethodEnum.CASH;
                this.checkoutProps.form.pos_payment_note = null;
                this.checkoutProps.form.pos_split_cash = null;
                this.checkoutProps.form.pos_split_online = null;
                this.discount = null;
                this.discountType = discountTypeEnum.PERCENTAGE;
                this.splitErrorMessage = "";

                // Clear localStorage
                try {
                    const storedData = localStorage.getItem('vuex');
                    if (storedData) {
                        const vuexData = JSON.parse(storedData);
                        if (vuexData.posCart) {
                            vuexData.posCart = {
                                lists: [],
                                subtotal: 0,
                                discount: 0,
                                orderType: null
                            };
                            localStorage.setItem('vuex', JSON.stringify(vuexData));
                        }
                    }
                } catch (error) {
                    console.error('Error resetting posCart in localStorage:', error);
                }

                // Redirect back to orders list
                this.$router.push({ name: 'admin.pos.orders.list' });
            }).catch((error) => {
                console.error('Error resetting cart:', error);
            });
        },
        addToCart: function () {
            try {
                // Create a new array to hold all items
                let newCartItems = [...this.carts];
                
                // Helper function to check if two items are identical (including variations and extras)
                const areItemsIdentical = (item1, item2) => {
                    // Check basic properties
                    if (item1.item_id !== item2.item_id) return false;
                    if (item1.instruction !== item2.instruction) return false;

                    // Check variations
                    const variations1 = item1.item_variations?.variations || {};
                    const variations2 = item2.item_variations?.variations || {};
                    if (JSON.stringify(variations1) !== JSON.stringify(variations2)) return false;

                    // Check extras
                    const extras1 = item1.item_extras?.extras || [];
                    const extras2 = item2.item_extras?.extras || [];
                    if (JSON.stringify(extras1) !== JSON.stringify(extras2)) return false;

                    return true;
                };

                // Helper function to calculate item total
                const calculateItemTotal = (item) => {
                    const basePrice = parseFloat(item.convert_price) || 0;
                    const variationTotal = parseFloat(item.item_variation_total) || 0;
                    const extraTotal = parseFloat(item.item_extra_total) || 0;
                    const quantity = parseInt(item.quantity) || 1;
                    return (basePrice + variationTotal + extraTotal) * quantity;
                };

                // Process main item
                const mainItem = {
                    name: this.temp.name,
                    image: this.temp.image,
                    item_id: this.temp.item_id,
                    quantity: parseInt(this.temp.quantity) || 1,
                    discount: this.temp.discount,
                    currency_price: this.temp.currency_price,
                    convert_price: parseFloat(this.temp.convert_price) || 0,
                    item_variations: this.temp.item_variations || { variations: {}, names: {} },
                    item_extras: this.temp.item_extras || { extras: [], names: [] },
                    item_variation_total: parseFloat(this.temp.item_variation_total) || 0,
                    item_extra_total: parseFloat(this.temp.item_extra_total) || 0,
                    instruction: this.temp.instruction || ''
                };

                // Find existing identical item
                const existingItemIndex = newCartItems.findIndex(item => areItemsIdentical(item, mainItem));

                if (existingItemIndex !== -1) {
                    // Update existing item quantity
                    newCartItems[existingItemIndex].quantity += mainItem.quantity;
                    // Recalculate total
                    newCartItems[existingItemIndex].total = calculateItemTotal(newCartItems[existingItemIndex]);
                } else {
                    // Add as new item
                    mainItem.total = calculateItemTotal(mainItem);
                    newCartItems.push(mainItem);
                }

                // Process addons if they exist
                if (this.addons && typeof this.addons === 'object' && Object.keys(this.addons).length > 0) {
                    Object.values(this.addons).forEach(addon => {
                        const addonItem = {
                            name: addon.name,
                            image: addon.image,
                            item_id: addon.item_id,
                            quantity: parseInt(addon.quantity) || 1,
                            discount: addon.discount,
                            currency_price: addon.currency_price,
                            convert_price: parseFloat(addon.convert_price) || 0,
                            item_variations: addon.item_variations || { variations: {}, names: {} },
                            item_extras: addon.item_extras || { extras: [], names: [] },
                            item_variation_total: parseFloat(addon.item_variation_total) || 0,
                            item_extra_total: parseFloat(addon.item_extra_total) || 0,
                            instruction: addon.instruction || ''
                        };

                        // Find existing identical addon
                        const existingAddonIndex = newCartItems.findIndex(item => areItemsIdentical(item, addonItem));

                        if (existingAddonIndex !== -1) {
                            // Update existing addon quantity
                            newCartItems[existingAddonIndex].quantity += addonItem.quantity;
                            // Recalculate total
                            newCartItems[existingAddonIndex].total = calculateItemTotal(newCartItems[existingAddonIndex]);
                        } else {
                            // Add as new addon
                            addonItem.total = calculateItemTotal(addonItem);
                            newCartItems.push(addonItem);
                        }
                    });
                }

                // Update Vuex store with consolidated items
                this.$store.dispatch('posCart/lists', newCartItems)
                    .then(() => {
                        this.updateLocalStorage();
                        alertService.success(this.$t('message.add_to_cart'));
                        appService.modalHide('#item-variation-modal');
                    })
                    .catch(error => {
                        console.error('Error updating cart:', error);
                        alertService.error(this.$t('message.add_to_cart_failed'));
                    });

            } catch (error) {
                console.error('Error in addToCart:', error);
                alertService.error(this.$t('message.add_to_cart_failed'));
            }
        },
        refreshComponent() {
            // Store current route and query parameters
            const currentRoute = this.$route.fullPath;

            // Force a refresh by navigating away and back
            this.$router.replace('/temp-route').then(() => {
                this.$router.replace(currentRoute);
            });
        },
        handleCustomerSelect(selectedCustomer) {
            if (selectedCustomer && typeof selectedCustomer === 'object') {
                    this.checkoutProps.form.customer_id = selectedCustomer.id;

                    if (this.checkoutProps.form.order_method === this.orderMethodEnum.DELIVERY) {
                    this.deliveryAddress = selectedCustomer.address || '';
                    this.phoneSearch = selectedCustomer.phone || '';
                    }
                } else {
                this.checkoutProps.form.customer_id = null;
                this.deliveryAddress = '';
                this.phoneSearch = '';
            }
        },

        handleOrderMethodChange(method) {
            console.log('Order Method Changed:', method);
            if (method === this.orderMethodEnum.DELIVERY) {
                // Get selected customer details if available
                const selectedCustomer = this.getSelectedCustomer;
                if (selectedCustomer) {
                    this.deliveryAddress = selectedCustomer.address || '';
                    this.phoneSearch = selectedCustomer.phone || '';
                    this.countryCode = selectedCustomer.country_code || '91';
                }
            } else {
                // Reset delivery-related fields when switching to non-delivery method
                this.deliveryAddress = '';
                this.phoneSearch = '';
            }
        },

        closeNewCustomerModal() {
            this.showNewCustomerModal = false;
            this.newCustomerForm = {
                name: '',
                email: '',
                phone: '',
                country_code: '91',
                password: '123456',
                password_confirmation: '123456',
                address: '',
                status: statusEnum.ACTIVE,
                role_id: roleEnum.CUSTOMER
            };
            this.newCustomerErrors = {};
        },

        async saveNewCustomer() {
            try {
                this.loading.isActive = true;
                this.newCustomerErrors = {};

                const customerData = {
                    form: {
                        name: this.newCustomerForm.name,
                        email: this.newCustomerForm.email,
                        phone: this.newCustomerForm.phone,
                        country_code: this.newCustomerForm.country_code,
                        password: '123456',
                        password_confirmation: '123456',
                        address: this.newCustomerForm.address,
                        status: statusEnum.ACTIVE,
                        role_id: roleEnum.CUSTOMER
                    }
                };

                const response = await this.$store.dispatch('customer/save', customerData);

                if (response && response.data) {
                    const newCustomerId = response.data.id;

                    await this.$store.dispatch('user/lists', {
                        order_column: 'id',
                        order_type: 'asc',
                        status: statusEnum.ACTIVE,
                        role_id: roleEnum.CUSTOMER
                    });

                    if (newCustomerId) {
                        this.checkoutProps.form.customer_id = newCustomerId;
                        this.handleCustomerSelect(response.data);
                    }

                    this.showNewCustomerModal = false;

                    const successMsg = this.$t('message.customer_created_success', {
                        name: this.newCustomerForm.name
                    });
                    this.$toast.success(successMsg, {
                        timeout: 3000,
                        position: 'top-right',
                        closeButton: true
                    });

                    this.newCustomerForm = {
                        name: '',
                        email: '',
                        phone: '',
                        country_code: '91',
                        password: '123456',
                        password_confirmation: '123456',
                        address: '',
                        status: statusEnum.ACTIVE,
                        role_id: roleEnum.CUSTOMER
                    };
                } else {
                    throw new Error(this.$t('message.invalid_response'));
                }
            } catch (error) {
                console.error('Error saving customer:', error);

                if (error.response?.data?.errors) {
                    this.newCustomerErrors = error.response.data.errors;
                    this.$toast.error(this.$t('message.validation_error'), {
                        timeout: 3000,
                        position: 'top-right',
                        closeButton: true
                    });
                } else if (error.response?.data?.message) {
                    this.$toast.error(error.response.data.message, {
                        timeout: 3000,
                        position: 'top-right',
                        closeButton: true
                    });
                } else {
                    this.$toast.error(error.message || this.$t('message.something_wrong'), {
                        timeout: 3000,
                        position: 'top-right',
                        closeButton: true
                    });
                }
            } finally {
                this.loading.isActive = false;
            }
        },

        async fetchCustomers() {
            try {
                console.log('Fetching Customer List');
                const response = await this.$store.dispatch('user/lists', {
                    status: statusEnum.ACTIVE,
                    role_id: roleEnum.CUSTOMER
                });
                console.log('Customer List Fetched:', this.customers);
            } catch (error) {
                console.error('Error Fetching Customers:', error);
                alertService.error(error.response?.data?.message || this.$t('message.failed_to_fetch_customers'));
            }
        },

        phoneNumber(e) {
            return appService.phoneNumber(e);
        },

        searchCustomerByPhone() {
            if (this.phoneSearch.length >= 1) {
                this.isSearching = true;
                this.phoneSearchResults = [];
                this.$store.dispatch('user/searchByPhone', {
                    phone: this.phoneSearch,
                    country_code: this.countryCode
                }).then(response => {
                    this.phoneSearchResults = response.data.data;
                }).catch(error => {
                    console.error('Error searching customers by phone:', error);
                    this.phoneSearchResults = [];
                }).finally(() => {
                    this.isSearching = false;
                });
            } else {
                this.phoneSearchResults = [];
            }
        },

        selectCustomerFromSearch(customer) {
            this.checkoutProps.form.customer_id = customer.id;
            this.selectedCustomerDetails = {
                name: customer.name,
                phone: customer.phone,
                email: customer.email,
                address: customer.address || ''
            };
            this.phoneSearch = '';
            this.phoneSearchResults = [];
        },
        onSwiper(swiper) {
            this.swiper = swiper;
            console.log('Swiper instance:', swiper);
        }
    }
}
</script>
