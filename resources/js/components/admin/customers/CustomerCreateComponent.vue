<template>
    <LoadingComponent :props="loading" />
    <SmSidebarModalCreateComponent :props="addButton" />

    <div id="sidebar" class="drawer">
        <div class="drawer-header border-b border-gray-200">
            <h3 class="drawer-title text-lg font-semibold text-gray-800">{{ $t("menu.customers") }}</h3>
            <button class="fa-solid fa-xmark close-btn hover:text-red-500 transition-colors" @click="reset"></button>
        </div>
        <div class="drawer-body p-6">
            <form @submit.prevent="save" class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name Field -->
                    <div class="space-y-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ $t("label.name") }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="props.form.name"
                            type="text"
                            id="name"
                            placeholder="Enter customer name"
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors duration-200"
                            :class="{'border-red-500 focus:ring-red-200': errors.name}"
                        />
                        <small class="text-red-500 text-xs block mt-1" v-if="errors.name">{{ errors.name[0] }}</small>
                    </div>

                    <!-- Email Field -->
                    <div class="space-y-2">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ $t("label.email") }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="props.form.email"
                            type="email"
                            id="email"
                            placeholder="Enter email address"
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors duration-200"
                            :class="{'border-red-500 focus:ring-red-200': errors.email}"
                        />
                        <small class="text-red-500 text-xs block mt-1" v-if="errors.email">{{ errors.email[0] }}</small>
                    </div>

                    <!-- Phone Field -->
                    <div class="space-y-2">
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ $t("label.phone") }} <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-3">
                            <div class="w-24">
                                <input
                                    type="text"
                                    v-model="props.form.country_code"
                                    class="w-full px-3 py-3 rounded-lg border border-gray-300 bg-gray-50 text-gray-500 font-medium"
                                    readonly
                                />
                            </div>
                            <div class="flex-1">
                                <input
                                    v-model="props.form.phone"
                                    @keypress="phoneNumber($event)"
                                    type="text"
                                    id="phone"
                                    placeholder="Enter phone number"
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors duration-200"
                                    :class="{'border-red-500 focus:ring-red-200': errors.phone}"
                                />
                            </div>
                        </div>
                        <small class="text-red-500 text-xs block mt-1" v-if="errors.phone">{{ errors.phone[0] }}</small>
                    </div>

                    <!-- Address Field -->
                    <div class="space-y-2">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ $t("label.address") }}
                        </label>
                        <textarea
                            v-model="props.form.address"
                            id="address"
                            rows="3"
                            placeholder="Enter delivery address"
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors duration-200 resize-none"
                            :class="{'border-red-500 focus:ring-red-200': errors.address}"
                        ></textarea>
                        <small class="text-red-500 text-xs block mt-1" v-if="errors.address">{{ errors.address[0] }}</small>
                    </div>

                    <!-- Password Fields -->
                    <div class="space-y-2">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ $t("label.password") }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="props.form.password"
                            type="password"
                            id="password"
                            placeholder="Enter password"
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors duration-200"
                            :class="{'border-red-500 focus:ring-red-200': errors.password}"
                            autocomplete="new-password"
                        />
                        <small class="text-red-500 text-xs block mt-1" v-if="errors.password">{{ errors.password[0] }}</small>
                    </div>

                    <div class="space-y-2">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ $t("label.confirm_password") }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="props.form.password_confirmation"
                            type="password"
                            id="password_confirmation"
                            placeholder="Confirm password"
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors duration-200"
                            :class="{'border-red-500 focus:ring-red-200': errors.password_confirmation}"
                            autocomplete="new-password"
                        />
                        <small class="text-red-500 text-xs block mt-1" v-if="errors.password_confirmation">{{ errors.password_confirmation[0] }}</small>
                    </div>

                    <!-- Status Field -->
                    <div class="col-span-full space-y-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ $t("label.status") }} <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-6 p-4 bg-gray-50 rounded-lg">
                            <label class="inline-flex items-center cursor-pointer">
                                <input
                                    :value="enums.statusEnum.ACTIVE"
                                    v-model="props.form.status"
                                    type="radio"
                                    class="w-4 h-4 text-primary border-gray-300 focus:ring-primary"
                                />
                                <span class="ml-2.5 text-sm text-gray-700">Active</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input
                                    :value="enums.statusEnum.INACTIVE"
                                    v-model="props.form.status"
                                    type="radio"
                                    class="w-4 h-4 text-primary border-gray-300 focus:ring-primary"
                                />
                                <span class="ml-2.5 text-sm text-gray-700">Inactive</span>
                            </label>
                        </div>
                        <small class="text-red-500 text-xs block mt-1" v-if="errors.status">{{ errors.status[0] }}</small>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-4 pt-6 mt-6 border-t border-gray-200">
                    <button type="button" @click="reset"
                        class="px-6 py-2.5 text-sm font-medium border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200 flex items-center">
                        <i class="lab lab-close mr-2"></i>
                        {{ $t("button.close") }}
                    </button>
                    <button type="submit"
                        class="px-6 py-2.5 text-sm font-medium text-white bg-primary rounded-lg hover:bg-primary-dark transition-colors duration-200 flex items-center">
                        <i class="lab lab-save mr-2"></i>
                        {{ $t("label.save") }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
<script>
import SmSidebarModalCreateComponent from "../components/buttons/SmSidebarModalCreateComponent";
import LoadingComponent from "../components/LoadingComponent";
import statusEnum from "../../../enums/modules/statusEnum";
import alertService from "../../../services/alertService";
import appService from "../../../services/appService";

export default {
    name: "CustomerCreateComponent",
    components: { SmSidebarModalCreateComponent, LoadingComponent },
    props: ["props"],
    data() {
        return {
            loading: {
                isActive: false,
            },
            enums: {
                statusEnum: statusEnum,
                statusEnumArray: {
                    [statusEnum.ACTIVE]: this.$t("label.active"),
                    [statusEnum.INACTIVE]: this.$t("label.inactive"),
                },
            },
            errors: {},
            flag: "",
            country_code: "",
        };
    },
    computed: {
        addButton: function () {
            return { title: this.$t('button.add_customer') };
        }
    },
    mounted() {
        this.loading.isActive = true;
        this.$store.dispatch('company/lists').then(companyRes => {
            this.$store.dispatch('countryCode/show', companyRes.data.data.company_country_code).then(res => {

                if (this.props.form.country_code === "") {
                    this.props.form.country_code = res.data.data.calling_code;
                    this.country_code = res.data.data.calling_code;
                }
                this.flag = res.data.data.flag_emoji;
                this.loading.isActive = false;

            }).catch((err) => {
                this.loading.isActive = false;

            });
        }).catch((err) => {
            this.loading.isActive = false;
        });
    },
    methods: {
        phoneNumber(e) {
            return appService.phoneNumber(e);
        },
        reset: function () {
            appService.sideDrawerHide();
            this.$store.dispatch("customer/reset").then().catch();
            this.errors = {};
            this.$props.props.form = {
                name: "",
                email: "",
                phone: "",
                password: "",
                password_confirmation: "",
                status: statusEnum.ACTIVE,
                country_code: this.country_code,
            };
        },

        save: function () {
            try {
                if (this.checkoutProps.form.order_method === this.orderMethodEnum.DELIVERY) {
                    if (!this.deliveryAddress) {
                        alertService.error(this.$t('message.delivery_address_required'));
                        this.loading.isActive = false;
                        return;
                    }
                    if (!this.phoneSearch) {
                        alertService.error(this.$t('message.phone_required'));
                        this.loading.isActive = false;
                        return;
                    }
                }

                const tempId = this.$store.getters["customer/temp"].temp_id;
                this.loading.isActive = true;
                this.$store
                    .dispatch("customer/save", this.props)
                    .then((res) => {
                        appService.sideDrawerHide();
                        this.loading.isActive = false;
                        alertService.successFlip(
                            tempId === null ? 0 : 1,
                            this.$t("menu.customers")
                        );
                        this.props.form = {
                            name: "",
                            email: "",
                            phone: "",
                            password: "",
                            password_confirmation: "",
                            status: statusEnum.ACTIVE,
                            country_code: this.country_code,
                        };
                        this.errors = {};
                    })
                    .catch((err) => {
                        this.loading.isActive = false;
                        this.errors = err.response.data.errors;
                    });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err);
            }
        },
    },
};
</script>
