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
                    <router-link v-if="index === 0" to="#" @click.prevent="allCategory"
                        class="w-28 flex flex-col items-center text-center gap-4 py-4 px-3 rounded-lg border-b-2 border-transparent transition hover:bg-[#FFEDF4] hover:border-primary bg-white">
                        <img class="h-7 drop-shadow-category" :src="category.thumb" alt="category">
                        <h3 class="text-xs leading-[16px] font-medium font-rubik">{{ category.name }}</h3>
                    </router-link>
                    <router-link v-else to="#" @click.prevent="setCategory(category.id)"
                        class="w-28 flex flex-col items-center text-center gap-4 py-4 px-3 rounded-lg border-b-2 border-transparent transition hover:bg-[#FFEDF4] hover:border-primary bg-white">
                        <img class="h-7 drop-shadow-category" :src="category.thumb" alt="category">
                        <h3 class="text-xs leading-[16px] font-medium font-rubik">{{ category.name }}</h3>
                    </router-link>
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

                    <!-- No Results Message - Only show when searching and no customer is selected -->
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
                    v-model="checkoutProps.form.token" :placeholder="$t('label.token_no')" />
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
                            <span v-for="(variation, variationName) in cart.item_variations.names">
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
                                    <span v-for="extra in cart.item_extras.names">
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
            <div class="flex h-[38px]" v-if="carts.length > 0">
                <div class="db-field-down-arrow">
                    <select v-model="discountType"
                        class="w-[120px] h-full text-sm font-rubik rounded-tl rounded-bl appearance-none border pl-3 text-heading border-[#EFF0F6]">
                        <option :value="discountTypeEnum.PERCENTAGE">{{ $t("label.percentage") }}</option>
                        <option :value="discountTypeEnum.FIXED">{{ $t("label.fixed") }}</option>
                    </select>
                </div>
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

            <div class="flex gap-4 mt-2 mb-2" v-if="checkoutProps.form.pos_payment_method == posPaymentMethodEnum.SPLIT && carts.length > 0">
                <input type="number" step="0.01" placeholder="Cash Amount" v-model="checkoutProps.form.pos_split_cash" class="w-full h-[38px] text-sm font-rubik px-3 rounded-lg border border-[#EFF0F6] text-heading">
                <input type="number" step="0.01" placeholder="Online Amount" v-model="checkoutProps.form.pos_split_online" class="w-full h-[38px] text-sm font-rubik px-3 rounded-lg border border-[#EFF0F6] text-heading">
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
                        Total
                    </span>
                    <span class="text-sm font-medium font-rubik capitalize leading-6 text-[#2E2F38]">
                        {{
                            currencyFormat(parseFloat(subtotal - posDiscount),
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
                <button @click.prevent="orderSubmit"
                    class="capitalize text-sm font-medium leading-6 font-rubik w-full text-center rounded-3xl py-2 text-white bg-[#1AB759]">
                    {{ $t('button.order') }}

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
import orderStatusEnum from "../../../enums/modules/orderStatusEnum";
import orderMethodEnum from "../../../enums/modules/orderMethodEnum";
import axios from 'axios';

export default {
    name: "PosComponent",
    components: {
        ReceiptComponent,
        LoadingComponent,
        ItemComponent,
        Swiper,
        SwiperSlide,
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            order: {},
            discount: null,
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
                    status: orderStatusEnum.PROCESSING,
                    tip_amount: 0,
                    tip_employee_id: null
                }
            },
            employees: [],
            showTipSection: false,
            props: {
                search: {
                    paginate: 0,
                    order_column: "sort",
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
            orderStatusEnum: orderStatusEnum,
            discountTypeEnum: discountTypeEnum,
            discountType: discountTypeEnum.PERCENTAGE,
            discountErrorMessage: "",
            splitErrorMessage: "",
            posPaymentMethodEnum: posPaymentMethodEnum,
            posPaymentMethodEnumArray: {
                1: this.$t("label.cash"),
                2: this.$t("label.upi")
            },
            posPaymentMethodObject: [
                {
                    name: this.$t("label.cash"),
                    value: 1
                },
                {
                    name: this.$t("label.upi"),
                    value: 2
                }
            ],
            orderTypeOptions: [
                { id: orderTypeEnum.DINING_TABLE, name: this.$t("label.dine_in") },
                { id: orderTypeEnum.TAKEAWAY, name: this.$t("label.takeaway") },
                { id: orderTypeEnum.DELIVERY, name: this.$t("label.delivery") }
            ],
            orderMethodEnum: orderMethodEnum,
            orderMethodOptions: [
                { id: orderMethodEnum.DINE_IN, name: 'Dine In' },
                { id: orderMethodEnum.TAKEAWAY, name: 'Takeaway' },
                { id: orderMethodEnum.DELIVERY, name: 'Delivery' }
            ],
            deliveryOption: 'existing',
            showNewCustomerModal: false,
            selectedCustomerDetails: null,
            newCustomer: {
                name: '',
                phone: '',
                email: '',
                address: ''
            },
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
            phoneSearch: '',
            phoneSearchResults: [],
            phoneSearchTimeout: null,
            isSearching: false,
            countryCode: '91', // Default country code
            allCustomers: [], // Store all customers for local filtering
            deliveryAddress: '',
            walkingCustomerId: null, // Store walking customer ID
            isCreatingNewUser: false,
            randomNames: [
                'Customer', 'Guest', 'Visitor', 'Patron', 'Client', 'Shopper',
                'Buyer', 'Consumer', 'User', 'Member'
            ],
            customerForm: {
                form: {
                    name: "",
                    email: "",
                    phone: "",
                    password: "",
                    password_confirmation: "",
                    status: statusEnum.ACTIVE,
                    country_code: "",
                    address: "",
                }
            }
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
        getSelectedCustomer() {
            if (!this.checkoutProps.form.customer_id || !this.customers) {
                return null;
            }
            return this.customers.find(c => c && c.id === this.checkoutProps.form.customer_id) || null;
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
        }
    },
    mounted() {
        try {
            this.loading.isActive = true;

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

            // Load customers and set walking customer as default
            this.$store.dispatch('user/lists', {
                order_column: 'id',
                order_type: 'asc',
                status: statusEnum.ACTIVE,
                role_id: roleEnum.CUSTOMER
            }).then((res) => {
                if (res.data && res.data.data && res.data.data.length > 0) {
                    // Find walking customer from the list
                    const walkingCustomer = res.data.data.find(customer =>
                        customer.name.toLowerCase().includes('walking') ||
                        customer.name.toLowerCase().includes('walk in')
                    );
                    if (walkingCustomer) {
                        this.walkingCustomerId = walkingCustomer.id;
                        // Set walking customer as default
                        this.checkoutProps.form.customer_id = walkingCustomer.id;
                        this.handleCustomerSelect(walkingCustomer);
                    }
                    // Only set first customer if walking customer not found
                    else if (res.data.data.length > 0) {
                        this.checkoutProps.form.customer_id = res.data.data[0].id;
                        this.handleCustomerSelect(res.data.data[0]);
                    }
                }
            }).catch((err) => {
                this.loading.isActive = false;
            });

            // Load categories
            this.$store.dispatch('itemCategory/lists', this.categoryProps).then((res) => {
                this.categories = [
                    {
                        id: 0,
                        name: this.$t('label.all_category'),
                        thumb: '/images/default/all-category.png'
                    },
                    ...res.data.data
                ];
            });

            // Load default branch
            this.$store.dispatch('defaultAccess/show').then((res) => {
                this.checkoutProps.form.branch_id = res.data.data.branch_id;
            });

            // Load company details
            this.$store.dispatch("company/lists").then((res) => {
                this.company.name = res.data.data.company_name;
                this.company.email = res.data.data.company_email;
                this.company.phone = res.data.data.company_phone;
                this.company.address = res.data.data.company_address;

                // Get default country code for new customers
                this.$store.dispatch('countryCode/show', res.data.data.company_country_code).then(res => {
                    this.newCustomerForm.country_code = res.data.data.calling_code;
                }).catch((err) => {
                    this.loading.isActive = false;
                });
            }).catch((err) => {
                this.loading.isActive = false;
            });

            // Set default order method
            this.checkoutProps.form.order_method = orderMethodEnum.DINE_IN;

            // Load items
            this.itemCategories();
            this.itemList();

            // If delivery method is already selected, load customers
            if (this.checkoutProps.form.order_method === this.orderMethodEnum.DELIVERY) {
                this.loadAllCustomers();
            }

        } catch (err) {
            this.loading.isActive = false;
        }
    },
    methods: {
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
        setCategory: function (id) {
            this.props.search.item_category_id = id;
            this.itemList();
        },
        cartQuantityUp: function (id, e) {
            if (e.target.value > 0) {
                this.$store.dispatch('posCart/quantity', { id: id, status: e.target.value }).then().catch();
            }
        },
        cartQuantityIncrement: function (id) {
            this.$store.dispatch('posCart/quantity', { id: id, status: "increment" }).then().catch();
        },
        cartQuantityDecrement: function (id) {
            this.$store.dispatch('posCart/quantity', { id: id, status: "decrement" }).then().catch();
        },
        deleteCartItem: function (id) {
            this.$store.dispatch('posCart/deleteCartItem', { id: id, status: "decrement" }).then().catch();
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
        resetCart: function () {
            this.$store.dispatch('posCart/resetCart').then(res => {
                this.checkoutProps.form.token = "";
                this.checkoutProps.form.subtotal = null;
                this.checkoutProps.form.total = 0;
                this.checkoutProps.form.total_amount_price = 0;
                this.checkoutProps.form.discount = 0;
                this.checkoutProps.form.delivery_charge = 0;
                this.checkoutProps.form.delivery_time = null;
                this.checkoutProps.form.order_method = this.orderMethodEnum.DINE_IN;
                this.checkoutProps.form.is_advance_order = isAdvanceOrderEnum.NO;
                this.checkoutProps.form.source = sourceEnum.POS;
                this.checkoutProps.form.address_id = null;
                this.checkoutProps.form.tip_amount = 0;
                this.showTipSection = false;
                this.checkoutProps.form.coupon_id = null;
                this.checkoutProps.form.items = [];
                this.checkoutProps.form.pos_payment_method = this.posPaymentMethodEnum.CASH;
                this.checkoutProps.form.pos_payment_note = null;
                this.checkoutProps.form.pos_split_cash = null;
                this.checkoutProps.form.pos_split_online = null;
                this.discount = null;
                this.discountType = discountTypeEnum.PERCENTAGE;
                this.splitErrorMessage = "";
                this.checkoutProps.form.tip_amount = 0;
                this.checkoutProps.form.tip_employee_id = null;

                if (!this.isAdmin && this.authInfo) {
                    this.checkoutProps.form.tip_employee_id = this.authInfo.id;
                }

                // Set customer to walking customer if available
                if (this.walkingCustomerId) {
                    this.checkoutProps.form.customer_id = this.walkingCustomerId;
                    const walkingCustomer = this.customers.find(c => c.id === this.walkingCustomerId);
                    if (walkingCustomer) {
                        this.handleCustomerSelect(walkingCustomer);
                    }
                }
            }).catch();
        },
        orderSubmit: function () {
            this.loading.isActive = true;

            // Calculate totals using original prices
            const itemTotal = this.carts.reduce((sum, item) => {
                const convertPrice = parseFloat(item.convert_price) || 0;
                const itemTotal = (convertPrice +
                    (parseFloat(item.item_variation_total) || 0) +
                    (parseFloat(item.item_extra_total) || 0)) * item.quantity;
                return sum + itemTotal;
            }, 0);

            let foodTotal = itemTotal - this.checkoutProps.form.discount;
            let tipAmt = parseFloat(this.checkoutProps.form.tip_amount) || 0;
            let computedTotal = foodTotal + tipAmt;

            if (this.checkoutProps.form.pos_payment_method == this.posPaymentMethodEnum.SPLIT) {
                let cashAmt = parseFloat(this.checkoutProps.form.pos_split_cash) || 0;
                let onlineAmt = parseFloat(this.checkoutProps.form.pos_split_online) || 0;
                let splitTotal = cashAmt + onlineAmt;
                if (Math.abs(splitTotal - computedTotal) > 0.05) {
                    this.splitErrorMessage = "Split amounts (including tip) must exactly equal the total order amount.";
                    this.loading.isActive = false;
                    return;
                } else {
                    this.splitErrorMessage = "";
                }
            }

            this.checkoutProps.form.subtotal = itemTotal;
            this.checkoutProps.form.total = parseFloat(foodTotal).toFixed(this.setting.site_digit_after_decimal_point);
            this.checkoutProps.form.total_amount_price = parseFloat(foodTotal).toFixed(this.setting.site_digit_after_decimal_point);

            this.checkoutProps.form.items = [];

            // Populate items array with cart items
            _.forEach(this.carts, (item) => {
                let item_variations = [];
                if (Object.keys(item.item_variations.variations).length > 0) {
                    _.forEach(item.item_variations.variations, (value, index) => {
                        item_variations.push({
                            "id": value,
                            "item_id": item.item_id,
                            "item_attribute_id": index,
                        });
                    });
                }

                if (Object.keys(item.item_variations.names).length > 0) {
                    let i = 0;
                    _.forEach(item.item_variations.names, (value, index) => {
                        item_variations[i].variation_name = index;
                        item_variations[i].name = value;
                        i++;
                    });
                }

                let item_extras = [];
                if (item.item_extras.extras.length) {
                    _.forEach(item.item_extras.extras, (value) => {
                        item_extras.push({
                            id: value,
                            item_id: item.item_id,
                        });
                    });
                }

                if (item.item_extras.names.length) {
                    let i = 0;
                    _.forEach(item.item_extras.names, (value) => {
                        item_extras[i].name = value;
                        i++;
                    });
                }

                const itemPrice = parseFloat(item.convert_price) || 0;
                const totalPrice = (itemPrice +
                    (parseFloat(item.item_variation_total) || 0) +
                    (parseFloat(item.item_extra_total) || 0)) * item.quantity;

                this.checkoutProps.form.items.push({
                    item_id: item.item_id,
                    item_name: item.name,
                    item_price: itemPrice,
                    branch_id: this.checkoutProps.form.branch_id,
                    instruction: item.instruction,
                    quantity: item.quantity,
                    discount: item.discount,
                    total_price: totalPrice,
                    item_variation_total: item.item_variation_total,
                    item_extra_total: item.item_extra_total,
                    item_variations: item_variations,
                    item_extras: item_extras
                });
            });

            // Convert items array to JSON string
            this.checkoutProps.form.items = JSON.stringify(this.checkoutProps.form.items);

            // Set payment note based on selected payment method
            this.checkoutProps.form.pos_payment_note = this.checkoutProps.form.pos_payment_method === this.posPaymentMethodEnum.CASH ?
                this.$t("label.cash") : this.$t("label.upi");

            // Set default status to PROCESSING (7)
            this.checkoutProps.form.status = this.orderStatusEnum.PROCESSING;

            // Include customer details for delivery orders
            if (this.checkoutProps.form.order_method === this.orderMethodEnum.DELIVERY) {
                const selectedCustomer = this.getSelectedCustomer;
                if (selectedCustomer) {
                    // Structure customer data according to the API response format
                    this.checkoutProps.form.user = {
                        id: selectedCustomer.id,
                        name: selectedCustomer.name,
                        first_name: selectedCustomer.name.split(' ')[0],
                        last_name: selectedCustomer.name.split(' ').slice(1).join(' ') || '',
                        email: selectedCustomer.email || '',
                    };

                    // Set the delivery address and phone directly in the order data
                    this.checkoutProps.form.delivery_address = this.deliveryAddress;
                    this.checkoutProps.form.delivery_phone = this.phoneSearch ? `${this.countryCode}${this.phoneSearch}` : '';

                    // Update customer's address if it's a new address
                    if (this.deliveryAddress && (!selectedCustomer.address || selectedCustomer.address !== this.deliveryAddress)) {
                        this.$store.dispatch('customer/save', {
                            form: {
                                id: selectedCustomer.id,
                                address: this.deliveryAddress
                            }
                        }).catch(error => {
                            console.error('Error updating customer address:', error);
                        });
                    }
                }
            }

            this.$store.dispatch("defaultAccess/show").then((res) => {
                this.checkoutProps.form.branch_id = res.data.data.branch_id;

                // Add delivery address and phone to form data
                if (this.checkoutProps.form.order_method === this.orderMethodEnum.DELIVERY) {
                    console.log('🚚 Delivery Order Detected');
                    console.log('📝 Current Form Data:', {
                        deliveryAddress: this.deliveryAddress,
                        phoneSearch: this.phoneSearch,
                        countryCode: this.countryCode
                    });

                    this.checkoutProps.form.delivery_address = this.deliveryAddress;
                    this.checkoutProps.form.delivery_phone = this.phoneSearch ? `${this.countryCode}${this.phoneSearch}` : '';

                    console.log('📞 Set Delivery Details:', {
                        delivery_address: this.checkoutProps.form.delivery_address,
                        delivery_phone: this.checkoutProps.form.delivery_phone
                    });

                    // Update user information in database if customer exists
                    if (this.checkoutProps.form.customer_id) {
                        console.log('🔄 Updating customer information:', {
                            customer_id: this.checkoutProps.form.customer_id,
                            phone: this.phoneSearch ? `${this.countryCode}${this.phoneSearch}` : '',
                            address: this.deliveryAddress
                        });

                        // First get the customer details
                        this.$store.dispatch("customer/show", this.checkoutProps.form.customer_id)
                            .then(response => {
                                const customer = response.data.data;

                                // Prepare customer data for update
                                const customerToUpdate = {
                                    id: customer.id,
                                    name: customer.name,
                                    email: customer.email,
                                    phone: this.phoneSearch ? `${this.phoneSearch}` : customer.phone,
                                    status: customer.status,
                                    country_code: this.countryCode,
                                    address: this.deliveryAddress || customer.address
                                };

                                // Use the existing edit method
                                this.$store.dispatch("customer/edit", customerToUpdate.id)
                                    .then(() => {
                                        // Update the customer using the store
                                        return this.$store.dispatch("customer/save", {
                                            form: customerToUpdate
                                        });
                                    })
                                    .then(response => {
                                        console.log('✅ Customer updated successfully:', response.data);
                                    })
                                    .catch(error => {
                                        console.error('❌ Error updating customer:', error.response?.data || error);
                                    });
                            })
                            .catch(error => {
                                console.error('❌ Error fetching customer:', error.response?.data || error);
                            });
                    }
                } else {
                    console.log('🏪 Non-Delivery Order - Skipping Customer Update');
                }

                this.$store.dispatch('posOrder/save', this.checkoutProps.form).then(orderResponse => {
                    if (orderResponse.data && orderResponse.data.data && orderResponse.data.data.id) {
                        // Fetch the complete order details to get items
                        this.$store.dispatch('posOrder/show', orderResponse.data.data.id).then(res => {
                            this.order = res.data.data;

                            // Set order method and delivery details
                            if (this.checkoutProps.form.order_method === this.orderMethodEnum.DELIVERY) {
                                this.order.order_method = this.orderMethodEnum.DELIVERY;
                                this.order.delivery_address = this.deliveryAddress;
                                this.order.delivery_phone = this.phoneSearch ? `${this.countryCode}${this.phoneSearch}` : '';

                                // Set user details if available
                                const selectedCustomer = this.getSelectedCustomer;
                                if (selectedCustomer) {
                                    this.order.user = {
                                        name: selectedCustomer.name,
                                        phone: selectedCustomer.phone,
                                        email: selectedCustomer.email
                                    };
                                }
                            }

                            // Reset the form and cart after successful save
                            this.resetForm();
                            this.loading.isActive = false;

                            // Show receipt modal
                            this.$nextTick(() => {
                                this.showReceipt = true;
                                appService.modalShow('#receiptModal');
                            });
                        }).catch(err => {
                            this.loading.isActive = false;
                            console.error('Error fetching order details:', err);
                        });
                    }
                }).catch(err => {
                    this.loading.isActive = false;
                    if (err.response?.data?.errors) {
                        _.forEach(err.response.data.errors, (error) => {
                            alertService.error(error[0]);
                        });
                    } else {
                        alertService.error(err.response?.data?.message || this.$t('message.save_failed'));
                    }
                });
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response?.data?.message || this.$t('message.save_failed'));
            });
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
        editOrder() {
            this.$router.push({
                name: 'admin.pos.edit',
                query: { order_id: this.$route.params.id }
            });
        },
        addToCart: function () {
            this.itemArrays = [
                {
                    name: this.temp.name,
                    image: this.temp.image,
                    item_id: this.temp.item_id,
                    quantity: this.temp.quantity,
                    discount: this.temp.discount,
                    currency_price: this.temp.currency_price,
                    convert_price: this.temp.convert_price,
                    item_variations: this.temp.item_variations,
                    item_extras: this.temp.item_extras,
                    item_variation_total: this.temp.item_variation_total,
                    item_extra_total: this.temp.item_extra_total,
                    instruction: this.temp.instruction
                }
            ];

            if (this.addons !== "undefined" && Object.keys(this.addons).length !== 0) {
                _.forEach(this.addons, (addon) => {
                    this.itemArrays.push({
                        name: addon.name,
                        image: addon.image,
                        item_id: addon.item_id,
                        quantity: addon.quantity,
                        discount: addon.discount,
                        currency_price: addon.currency_price,
                        convert_price: addon.convert_price,
                        item_variations: addon.item_variations,
                        item_extras: addon.item_extras,
                        item_variation_total: addon.item_variation_total,
                        item_extra_total: addon.item_extra_total,
                        instruction: addon.instruction
                    });
                });
            }

            // Update Vuex store
            this.$store.dispatch('posCart/lists', this.itemArrays).then(() => {
                // Update localStorage with original prices
                try {
                    const storedData = localStorage.getItem('vuex');
                    if (storedData) {
                        const vuexData = JSON.parse(storedData);
                        if (vuexData.posCart && vuexData.posCart.lists) {
                            vuexData.posCart.lists = vuexData.posCart.lists.map(item => {
                                const convertPrice = parseFloat(item.convert_price) || 0;
                                const itemTotal = (convertPrice +
                                    (parseFloat(item.item_variation_total) || 0) +
                                    (parseFloat(item.item_extra_total) || 0)) * item.quantity;

                                return {
                                    ...item,
                                    total: itemTotal
                                };
                            });

                            // Calculate subtotal using original prices
                            vuexData.posCart.subtotal = vuexData.posCart.lists.reduce((sum, item) => {
                                const convertPrice = parseFloat(item.convert_price) || 0;
                                const itemTotal = (convertPrice +
                                    (parseFloat(item.item_variation_total) || 0) +
                                    (parseFloat(item.item_extra_total) || 0)) * item.quantity;
                                return sum + itemTotal;
                            }, 0);

                            localStorage.setItem('vuex', JSON.stringify(vuexData));
                        }
                    }
                } catch (error) {
                    console.error('Error updating posCart in localStorage:', error);
                }

                // Reset temp data
                this.temp = {
                    name: "",
                    image: "",
                    item_id: "",
                    quantity: 1,
                    discount: 0,
                    currency_price: "",
                    convert_price: "",
                    item_variations: {
                        variations: {},
                        names: {}
                    },
                    item_extras: {
                        extras: [],
                        names: []
                    },
                    item_variation_total: 0,
                    item_extra_total: 0,
                    instruction: ""
                };
                this.addons = {};
                appService.hideModal('#itemModal');
            }).catch();
        },
        handleOrderMethodChange: async function (method) {
            console.log('Order Method Changed:', method);
            if (method === this.orderMethodEnum.DELIVERY) {
                // Get default country code
                try {
                    const companyResponse = await this.$store.dispatch('company/lists');
                    if (companyResponse.data && companyResponse.data.data) {
                        const countryCode = companyResponse.data.data.company_country_code;
                        if (countryCode) {
                            const countryResponse = await this.$store.dispatch('countryCode/show', countryCode);
                            if (countryResponse.data && countryResponse.data.data) {
                                this.countryCode = countryResponse.data.data.calling_code;
                            }
                        }
                    }
                } catch (error) {
                    console.error('Error fetching country code:', error);
                }
            }
        },
        handleCustomerSelect(selectedCustomer) {
            try {
                // Reset customer ID first to avoid stale data
                this.checkoutProps.form.customer_id = null;

                // Only proceed if we have a valid customer object
                if (selectedCustomer && typeof selectedCustomer === 'object' && selectedCustomer.id) {
                    this.checkoutProps.form.customer_id = selectedCustomer.id;

                    // If this is a delivery order, update delivery details
                    if (this.checkoutProps.form.order_method === this.orderMethodEnum.DELIVERY) {
                        // Set the delivery address
                        this.deliveryAddress = selectedCustomer.address || '';
                    }
                } else {
                    // Reset delivery-related data if no customer selected
                    this.selectedCustomerDetails = null;
                    this.deliveryAddress = '';
                }
            } catch (error) {
                console.error('Error in handleCustomerSelect:', error);
                this.checkoutProps.form.customer_id = null;
                this.selectedCustomerDetails = null;
                this.deliveryAddress = '';
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

                // Format the customer data to match API expectations
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

                // Check if response and response.data exist
                if (response && response.data) {
                    // Store the new customer ID
                    const newCustomerId = response.data.id;

                    // Reload the customer list
                    await this.$store.dispatch('user/lists', {
                        order_column: 'id',
                        order_type: 'asc',
                        status: statusEnum.ACTIVE,
                        role_id: roleEnum.CUSTOMER
                    });

                    // Only set the customer ID if we got it successfully
                    if (newCustomerId) {
                        this.checkoutProps.form.customer_id = newCustomerId;
                        // Update selected customer details if needed
                        this.handleCustomerSelect(response.data);
                    }

                    this.showNewCustomerModal = false;

                    // Show success message with customer name
                    const successMsg = this.$t('message.customer_created_success', {
                        name: this.newCustomerForm.name
                    });
                    this.$toast.success(successMsg, {
                        timeout: 3000,
                        position: 'top-right',
                        closeButton: true
                    });

                    // Reset form
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

                // Handle different types of errors
                if (error.response && error.response.data && error.response.data.errors) {
                    this.newCustomerErrors = error.response.data.errors;
                    this.$toast.error(this.$t('message.validation_error'), {
                        timeout: 3000,
                        position: 'top-right',
                        closeButton: true
                    });
                } else if (error.response && error.response.data && error.response.data.message) {
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
        phoneNumber(e) {
            return appService.phoneNumber(e);
        },
        async loadAllCustomers() {
            try {
                this.isLoadingCustomers = true;
                const response = await this.$store.dispatch('user/lists', {
                    status: statusEnum.ACTIVE,
                    role_id: roleEnum.CUSTOMER,
                    paginate: 0 // Get all results
                });

                if (response.data && response.data.data) {
                    this.allCustomers = response.data.data;
                }
            } catch (error) {
                console.error('Error loading customers:', error);
                this.allCustomers = [];
            } finally {
                this.isLoadingCustomers = false;
            }
        },
        generateRandomName() {
            const randomName = this.randomNames[Math.floor(Math.random() * this.randomNames.length)];
            const randomNumber = Math.floor(Math.random() * 10000);
            return `${randomName}${randomNumber}`;
        },
        generateRandomEmail(name) {
            const randomDomains = ['example.com', 'temp.com', 'dummy.com', 'test.com'];
            const domain = randomDomains[Math.floor(Math.random() * randomDomains.length)];
            return `${name.toLowerCase().replace(/\s+/g, '.')}@${domain}`;
        },
        generateRandomPassword() {
            const length = 12;
            const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
            let password = "";
            for (let i = 0; i < length; i++) {
                password += charset.charAt(Math.floor(Math.random() * charset.length));
            }
            return password;
        },
        async createNewUserFromPhone() {
            if (!this.phoneSearch) return;

            this.isCreatingNewUser = true;
            const randomName = this.generateRandomName();
            const randomPassword = this.generateRandomPassword();

            try {
                // Prepare form data using the same structure as CustomerCreateComponent
                this.customerForm.form = {
                    name: randomName,
                    email: this.generateRandomEmail(randomName),
                    phone: this.phoneSearch,
                    password: randomPassword,
                    password_confirmation: randomPassword,
                    status: statusEnum.ACTIVE,
                    country_code: this.countryCode,
                    address: this.deliveryAddress || '',
                };

                // Use the same store action as CustomerCreateComponent
                const response = await this.$store.dispatch('customer/save', this.customerForm);

                if (response.data && response.data.data) {
                    // Update the customer list
                    const userResponse = await this.$store.dispatch('user/lists', {
                        order_column: 'id',
                        order_type: 'asc',
                        status: statusEnum.ACTIVE,
                        role_id: roleEnum.CUSTOMER
                    });

                    if (userResponse.data && userResponse.data.data) {
                        this.allCustomers = userResponse.data.data;
                    }

                    // Select the newly created customer
                    this.checkoutProps.form.customer_id = response.data.data.id;
                    this.handleCustomerSelect(response.data.data);

                    // Show success message
                    alertService.success(this.$t('message.save_success'));
                }
            } catch (error) {
                console.error('Error creating new user:', error);
                if (error.response?.data?.errors) {
                    // Handle validation errors
                    const errorMessages = Object.values(error.response.data.errors)
                        .flat()
                        .join(', ');
                    alertService.error(errorMessages);
                } else {
                    alertService.error(error.response?.data?.message || this.$t('message.save_failed'));
                }
            } finally {
                this.isCreatingNewUser = false;
            }
        },
        searchCustomerByPhone() {
            if (this.phoneSearchTimeout) {
                clearTimeout(this.phoneSearchTimeout);
            }

            this.isSearching = true;
            this.phoneSearchTimeout = setTimeout(async () => {
                if (!this.phoneSearch) {
                    this.phoneSearchResults = [];
                    this.isSearching = false;
                    return;
                }

                try {
                    // Get all customers and filter locally
                    const response = await this.$store.dispatch('user/lists', {
                        status: statusEnum.ACTIVE,
                        role_id: roleEnum.CUSTOMER,
                        paginate: 0 // Get all results without pagination
                    });

                    if (response.data && response.data.data) {
                        // Store all customers for future filtering
                        this.allCustomers = response.data.data;

                        // Filter customers whose phone numbers include the search term
                        this.phoneSearchResults = response.data.data.filter(customer => {
                            const customerPhone = customer.phone || '';
                            return customerPhone.toLowerCase().includes(this.phoneSearch.toLowerCase());
                        });

                        // Sort results to prioritize exact matches and matches at the start
                        this.phoneSearchResults.sort((a, b) => {
                            const aPhone = a.phone || '';
                            const bPhone = b.phone || '';

                            // Exact matches first
                            if (aPhone === this.phoneSearch) return -1;
                            if (bPhone === this.phoneSearch) return 1;

                            // Then matches at start
                            if (aPhone.startsWith(this.phoneSearch) && !bPhone.startsWith(this.phoneSearch)) return -1;
                            if (!aPhone.startsWith(this.phoneSearch) && bPhone.startsWith(this.phoneSearch)) return 1;

                            return 0;
                        });

                        // Limit to first 10 results for better performance
                        this.phoneSearchResults = this.phoneSearchResults.slice(0, 10);

                        // If no matching customers found and phone number is valid length, create new user
                        if (this.phoneSearchResults.length === 0 && this.phoneSearch.length >= 10) {
                            await this.createNewUserFromPhone();
                        }
                    }
                } catch (error) {
                    console.error('Error searching customers:', error);
                    this.phoneSearchResults = [];
                } finally {
                    this.isSearching = false;
                }
            }, 300);
        },
        selectCustomerFromSearch(customer) {
            this.checkoutProps.form.customer_id = customer.id;
            this.handleCustomerSelect(customer);
            this.phoneSearchResults = []; // Clear search results
            this.phoneSearch = customer.phone; // Update phone search input
            this.deliveryAddress = customer.address || ''; // Set delivery address
        },
        clearSelectedCustomer() {
            this.checkoutProps.form.customer_id = null;
            this.selectedCustomerDetails = null;
            this.phoneSearch = '';
            this.phoneSearchResults = [];
            this.deliveryAddress = '';
        },
        resetForm() {
            // Reset checkout form
            if (this.walkingCustomerId) {
                this.checkoutProps.form.customer_id = this.walkingCustomerId;
            }
            this.checkoutProps.form.items = [];
            this.checkoutProps.form.note = '';
            this.checkoutProps.form.total = 0;
            this.checkoutProps.form.sub_total = 0;
            this.checkoutProps.form.discount = 0;
            this.checkoutProps.form.tax = 0;
            this.checkoutProps.form.delivery_charge = 0;
            this.checkoutProps.form.payment_method = null;
            this.checkoutProps.form.payment_status = null;
            this.checkoutProps.form.order_status = null;
            this.checkoutProps.form.delivery_time = null;
            this.checkoutProps.form.delivery_date = null;
            this.checkoutProps.form.delivery_address = '';
            this.checkoutProps.form.delivery_phone = '';

            // Reset order method to dine in
            this.checkoutProps.form.order_method = this.orderMethodEnum.DINE_IN;

            // Reset phone search and delivery address
            this.phoneSearch = '';
            this.deliveryAddress = '';
            this.phoneSearchResults = [];
            this.selectedCustomer = null;

            // Reset cart
            this.carts = [];
            this.$store.dispatch('posCart/resetCart').catch(console.error);

            // Reset all modals
            this.showCheckoutModal = false;
            this.showPaymentModal = false;
            this.showNewCustomerModal = false;
            this.showDeliveryModal = false;
            this.showOrderStatusModal = false;
            this.showOrderTimeModal = false;
            this.showOrderDateModal = false;
            this.showOrderNoteModal = false;
            this.showOrderDiscountModal = false;
            this.showOrderTaxModal = false;
            this.showOrderDeliveryChargeModal = false;
            this.showOrderPaymentMethodModal = false;
            this.showOrderPaymentStatusModal = false;
        },
    }
}
</script>

<style scoped>
.db-btn-outline {
    @apply px-4 py-2 text-sm font-medium font-rubik rounded-lg border transition-colors duration-200;
}
.db-btn-outline.danger {
    @apply border-[#FB4E4E] text-[#FB4E4E] hover:bg-[#FB4E4E] hover:text-white;
}
.db-btn-outline.success {
    @apply border-[#1AB759] text-[#1AB759] hover:bg-[#1AB759] hover:text-white;
}
.db-field-control {
    @apply w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors duration-200;
}
.db-field-control::placeholder {
    @apply text-gray-400;
}
.modern-select :deep(.vs__dropdown-toggle) {
    padding: 0;
    border: none;
    border-radius: 8px;
    background-color: rgb(249 250 251);
    min-height: 40px;
}

.modern-select :deep(.vs__selected-options) {
    padding: 2px 8px;
}

.modern-select :deep(.vs__selected) {
    margin: 0;
    padding: 0;
}

.modern-select :deep(.vs__search) {
    margin: 0;
    padding: 4px;
    font-size: 14px;
}

.modern-select :deep(.vs__actions) {
    padding-right: 8px;
}

.modern-select :deep(.vs__dropdown-menu) {
    border: none;
    border-radius: 8px;
    margin-top: 4px;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
}

.modern-select :deep(.vs__dropdown-option) {
    padding: 8px 12px;
    font-size: 14px;
}

.modern-select :deep(.vs__dropdown-option--highlight) {
    background: rgb(249 250 251);
    color: var(--primary-color);
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.phone-search-results {
    scrollbar-width: thin;
    scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
}

.phone-search-results::-webkit-scrollbar {
    width: 6px;
}

.phone-search-results::-webkit-scrollbar-track {
    background: transparent;
}

.phone-search-results::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 3px;
}
</style>
