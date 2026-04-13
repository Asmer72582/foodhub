<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t('menu.pos_orders') }}</h3>
                <div class="db-card-filter">
                    <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                    <FilterComponent />
                    <div class="dropdown-group">
                        <ExportComponent />
                        <div class="dropdown-list db-card-filter-dropdown-list">
                            <PrintComponent :props="printObj" />
                            <ExcelComponent :method="xls" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Method Tabs -->
            <div class="px-5 pt-4 pb-2">
                <div class="flex items-center gap-3">
                    <button 
                        v-for="tab in orderMethodTabs" 
                        :key="tab.value"
                        @click="filterByOrderMethod(tab.value)"
                        class="group relative flex-1"
                    >
                        <div :class="[
                            'flex items-center justify-center gap-2 px-4 py-3 rounded-xl transition-all duration-200',
                            props.search.order_method === tab.value || (tab.value === null && !props.search.order_method)
                                ? 'bg-primary shadow-lg shadow-primary/20' 
                                : 'bg-gray-100 hover:bg-gray-200'
                        ]">
                            <!-- Icon based on tab type -->
                            <i :class="[
                                'lab text-lg',
                                getTabIcon(tab.value),
                                props.search.order_method === tab.value || (tab.value === null && !props.search.order_method)
                                    ? 'text-white'
                                    : 'text-gray-600'
                            ]"></i>
                            
                            <div class="flex flex-col items-start">
                                <span :class="[
                                    'font-medium',
                                    props.search.order_method === tab.value || (tab.value === null && !props.search.order_method)
                                        ? 'text-white'
                                        : 'text-gray-700'
                                ]">{{ tab.label }}</span>
                                <span :class="[
                                    'text-xs',
                                    props.search.order_method === tab.value || (tab.value === null && !props.search.order_method)
                                        ? 'text-white/90'
                                        : 'text-gray-500'
                                ]">{{ getOrderCountByMethod(tab.value) }} Orders</span>
                            </div>
                        </div>
                    </button>
                </div>
            </div>

            <div class="table-filter-div">
                <form class="p-4 sm:p-5 mb-5" @submit.prevent="search">
                    <div class="row">
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="order_id" class="db-field-title after:hidden">{{ $t('label.order_id') }}</label>
                            <input id="order_id" v-model="props.search.order_serial_no" type="text"
                                class="db-field-control">
                        </div>

                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchStatus" class="db-field-title after:hidden">
                                {{ $t('label.status') }}
                            </label>
                            <vue-select class="db-field-control f-b-custom-select" id="searchStatus"
                                v-model="props.search.status" :options="[
                                    { id: enums.orderStatusEnum.PROCESSING, name: $t('label.processing') }
                                ]" label-by="name" value-by="id" :closeOnSelect="true" :searchable="true"
                                :clearOnClose="false" placeholder="--" search-placeholder="--" />
                        </div>

                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="user_id" class="db-field-title">
                                {{ $t("label.customer") }}
                            </label>
                            <vue-select class="db-field-control f-b-custom-select" id="user_id"
                                v-model="props.search.user_id" :options="customers" label-by="name" value-by="id"
                                :closeOnSelect="true" :searchable="true" :clearOnClose="true" placeholder="--"
                                search-placeholder="--" />
                        </div>

                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchStartDate" class="db-field-title after:hidden">
                                {{ $t('label.date') }}
                            </label>
                            <Datepicker hideInputIcon autoApply :enableTimePicker="false" utc="false"
                                @update:modelValue="handleDate" v-model="props.form.date" range
                                :preset-ranges="presetRanges">
                                <template #yearly="{ label, range, presetDateRange }">
                                    <span @click="presetDateRange(range)">{{ label }}</span>
                                </template>
                            </Datepicker>
                        </div>

                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="order_method" class="db-field-title after:hidden">
                                Order Method
                            </label>
                            <vue-select class="db-field-control f-b-custom-select" id="order_method"
                                v-model="props.search.order_method"
                                :options="[
                                    { id: enums.orderMethodEnum.DINE_IN, name: 'Dine In' },
                                    { id: enums.orderMethodEnum.TAKEAWAY, name: 'Takeaway' },
                                    { id: enums.orderMethodEnum.DELIVERY, name: 'Delivery' }
                                ]"
                                label-by="name" value-by="id" :closeOnSelect="true" :searchable="true"
                                :clearOnClose="true" placeholder="--" search-placeholder="--" />
                        </div>

                        <div class="col-12">
                            <div class="flex flex-wrap gap-3 mt-4">
                                <button class="db-btn py-2 text-white bg-primary">
                                    <i class="lab lab-search-line lab-font-size-16"></i>
                                    <span>{{ $t('button.search') }}</span>
                                </button>
                                <button class="db-btn py-2 text-white bg-gray-600" @click="clear">
                                    <i class="lab lab-cross-line-2 lab-font-size-22"></i>
                                    <span>{{ $t('button.clear') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="db-table-responsive">
                <table class="db-table stripe" id="print" :dir="direction">
                    <thead class="db-table-head">
                        <tr class="db-table-head-tr">
                            <th class="db-table-head-th">{{ $t('label.order_id') }}</th>
                            <th class="db-table-head-th">{{ $t('label.customer') }}</th>
                            <th class="db-table-head-th">Employee</th>
                            <th class="db-table-head-th">{{ $t('label.amount') }}</th>
                            <th class="db-table-head-th">{{ $t('label.date') }}</th>
                            <th class="db-table-head-th">{{ $t('label.status') }}</th>
                            <th class="db-table-head-th">Order Method</th>
                            <th class="db-table-head-th hidden-print" v-if="permissionChecker('pos-orders')">{{
                                $t('label.action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="db-table-body" v-if="orders.length > 0">
                        <tr class="db-table-body-tr" v-for="order in orders" :key="order">
                            <td class="db-table-body-td">
                                {{ order.order_serial_no }}
                            </td>
                            <td class="db-table-body-td">
                                {{ order.customer_name }}
                            </td>
                            <td class="db-table-body-td">
                                {{ order.employee_name || '--' }}
                            </td>
                            <td class="db-table-body-td">{{ order.total_amount_price }}</td>
                            <td class="db-table-body-td">{{ order.order_datetime }}</td>
                            <td class="db-table-body-td">
                                <span :class="orderStatusClass(order.status)">
                                    {{ enums.orderStatusEnumArray[order.status] }}
                                </span>
                            </td>
                            <td class="db-table-body-td">
                                {{ getOrderMethodName(order.order_method) }}
                            </td>
                            <td class="db-table-body-td hidden-print" v-if="permissionChecker('pos-orders')">
                                <div class="flex justify-start items-center sm:items-start sm:justify-start gap-1.5">
                                    <SmIconViewComponent :link="'admin.pos.orders.show'" :id="order.id"
                                        v-if="permissionChecker('pos-orders')" />

                                    <button
                                        @click="editOrder(order.id)"
                                        class="db-btn-outline sm success modal-btn m-0.5"
                                        v-if="permissionChecker('pos-orders')"
                                        :title="$t('button.edit')">
                                        <i class="lab lab-edit-line"></i>

                                    </button>


                                    <SmIconDeleteComponent @click="destroy(order.id)"
                                        v-if="permissionChecker('pos-orders')" />
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-6">
                <PaginationSMBox :pagination="pagination" :method="list" />
                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                    <PaginationTextComponent :props="{ page: paginationPage }" />
                    <PaginationBox :pagination="pagination" :method="list" />
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import LoadingComponent from "../components/LoadingComponent";
import alertService from "../../../services/alertService";
import PaginationTextComponent from "../components/pagination/PaginationTextComponent";
import PaginationBox from "../components/pagination/PaginationBox";
import PaginationSMBox from "../components/pagination/PaginationSMBox";
import appService from "../../../services/appService";
import orderStatusEnum from "../../../enums/modules/orderStatusEnum";
import orderTypeEnum from "../../../enums/modules/orderTypeEnum";
import TableLimitComponent from "../components/TableLimitComponent";
import SmIconDeleteComponent from "../components/buttons/SmIconDeleteComponent";
import SmIconViewComponent from "../components/buttons/SmIconViewComponent";
import FilterComponent from "../components/buttons/collapse/FilterComponent";
import ExportComponent from "../components/buttons/export/ExportComponent";
import PrintComponent from "../components/buttons/export/PrintComponent";
import ExcelComponent from "../components/buttons/export/ExcelComponent";
// import SmIconEditComponent from "../components/buttons/SmIconEditComponent";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import { ref } from 'vue';
import { endOfMonth, endOfYear, startOfMonth, startOfYear, subMonths } from 'date-fns';
import statusEnum from "../../../enums/modules/statusEnum";
import displayModeEnum from "../../../enums/modules/displayModeEnum";


export default {
    name: "PosOrderListComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        LoadingComponent,
        SmIconDeleteComponent,
        SmIconViewComponent,
        FilterComponent,
        ExportComponent,
        PrintComponent,
        ExcelComponent,
        Datepicker
    },
    setup() {
        const date = ref();

        const presetRanges = ref([
            { label: 'Today', range: [new Date(), new Date()] },
            { label: 'This month', range: [startOfMonth(new Date()), endOfMonth(new Date())] },
            {
                label: 'Last month',
                range: [startOfMonth(subMonths(new Date(), 1)), endOfMonth(subMonths(new Date(), 1))],
            },
            { label: 'This year', range: [startOfYear(new Date()), endOfYear(new Date())] },
            {
                label: 'This year (slot)',
                range: [startOfYear(new Date()), endOfYear(new Date())],
                slot: 'yearly',
            },
        ]);

        return {
            date,
            presetRanges,
        }
    },
    data() {
        return {
            loading: {
                isActive: false
            },
            enums: {
                orderStatusEnum: orderStatusEnum,
                orderTypeEnum: orderTypeEnum,
                orderStatusEnumArray: {
                    [orderStatusEnum.PROCESSING]: this.$t("label.processing")
                },
                orderTypeEnumArray: {
                    [orderTypeEnum.POS]: this.$t("label.pos"),
                },
                orderMethodEnum: {
                    DINE_IN: 1,
                    TAKEAWAY: 2,
                    DELIVERY: 3
                },
            },
            printLoading: true,
            printObj: {
                id: "print",
                popTitle: this.$t("menu.online_orders"),
            },
            props: {
                form: {
                    date: null,
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 10,
                    order_column: 'id',
                    order_by: "desc",
                    order_serial_no: "",
                    order_type: orderTypeEnum.POS,
                    user_id: null,
                    status: orderStatusEnum.PROCESSING,
                    from_date: "",
                    to_date: "",
                    order_method: null,
                }
            },
            orderMethodTabs: [
                { label: 'All Orders', value: null },
                { label: 'Dine In', value: 1 },
                { label: 'Takeaway', value: 2 },
                { label: 'Delivery', value: 3 }
            ],
        }
    },
    mounted() {
        this.list();
        this.$store.dispatch('user/lists', {
            order_column: 'id',
            order_type: 'asc',
            status: statusEnum.ACTIVE
        });
    },
    computed: {
        orders: function () {
            return this.$store.getters['posOrder/lists'];
        },
        customers: function () {
            return this.$store.getters['user/lists'];
        },
        pagination: function () {
            return this.$store.getters['posOrder/pagination'];
        },
        paginationPage: function () {
            return this.$store.getters['posOrder/page'];
        },
        direction: function () {
            return this.$store.getters['frontendLanguage/show'].display_mode === displayModeEnum.RTL ? 'rtl' : 'ltr';
        },
    },
    methods: {
        editOrder: async function(id) {
            try {
                this.loading.isActive = true;

                // First, reset the cart
                await this.$store.dispatch('posCart/resetCart');

                // Then fetch the order details
                const response = await this.$store.dispatch('posOrder/show', id);

                if (!response.data || !response.data.data) {
                    throw new Error('Invalid order data received');
                }

                // Navigate to POS edit page with order ID
                this.$router.push({
                    name: 'admin.pos.edit',
                    query: {
                        order_id: id
                    }
                });

            } catch (error) {
                console.error('Error in editOrder:', error);
                alertService.error(error.response?.data?.message || 'Failed to load order details');
            } finally {
                this.loading.isActive = false;
            }
        },
        permissionChecker(e) {
            return appService.permissionChecker(e);
        },
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        orderStatusClass: function (status) {
            return appService.orderStatusClass(status);
        },
        textShortener: function (text, number = 30) {
            return appService.textShortener(text, number);
        },
        search: function () {
            this.list();
        },
        handleDate: function (e) {
            if (e) {
                this.props.search.from_date = e[0];
                this.props.search.to_date = e[1];
            } else {
                this.props.form.date = null;
                this.props.search.from_date = null;
                this.props.search.to_date = null;
            }
        },
        clear: function () {
            this.props.search.paginate = 1;
            this.props.search.page = 1;
            this.props.search.order_by = "desc";
            this.props.search.order_serial_no = "";
            this.props.search.status = orderStatusEnum.PROCESSING;
            this.props.search.excepts = orderTypeEnum.DELIVERY + '|' + orderTypeEnum.TAKEAWAY;
            this.props.search.from_date = "";
            this.props.search.to_date = "";
            this.props.search.user_id = null;
            this.props.search.order_method = null;
            this.props.form.date = null;
            this.list();
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch('posOrder/lists', this.props.search).then(res => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        destroy: function (id) {
            appService.destroyConfirmation().then((res) => {
                try {
                    this.loading.isActive = true;
                    this.$store.dispatch('posOrder/destroy', { id: id, search: this.props.search }).then((res) => {
                        this.loading.isActive = false;
                        alertService.successFlip(null, this.$t('menu.pos_orders'));
                    }).catch((err) => {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    })
                } catch (err) {
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                }
            }).catch((err) => {
                this.loading.isActive = false;
            })
        },
        xls: function () {
            this.loading.isActive = true;
            this.$store.dispatch("posOrder/export", this.props.search).then((res) => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], {
                    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                });
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.pos_orders");
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        },
        getOrderMethodName(method) {
            switch(method) {
                case this.enums.orderMethodEnum.DINE_IN:
                    return 'Dine In';
                case this.enums.orderMethodEnum.TAKEAWAY:
                    return 'Takeaway';
                case this.enums.orderMethodEnum.DELIVERY:
                    return 'Delivery';
                default:
                    return '-';
            }
        },
        filterByOrderMethod(method) {
            this.props.search.order_method = method;
            this.list();
        },

        getOrderCountByMethod(method) {
            if (!this.orders) return 0;
            
            if (method === null) {
                return this.orders.length;
            }
            
            return this.orders.filter(order => order.order_method === method).length;
        },

        getTabIcon(value) {
            switch(value) {
                case null:
                    return 'lab-category-2-line';
                case this.enums.orderMethodEnum.DINE_IN:
                    return 'lab-restaurant-2-line';
                case this.enums.orderMethodEnum.TAKEAWAY:
                    return 'lab-shopping-bag-3-line';
                case this.enums.orderMethodEnum.DELIVERY:
                    return 'lab-truck-line';
                default:
                    return 'lab-category-2-line';
            }
        },
    }
}
</script>

<style scoped>
@media print {
    .hidden-print {
        display: none !important;
    }
}
</style>
