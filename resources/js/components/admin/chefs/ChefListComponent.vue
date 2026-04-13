<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t("menu.chefs") }}</h3>
                <div class="db-card-filter">
                    <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                    <ChefCreateComponent :props="props" v-if="permissionChecker('chefs_create')" />
                </div>
            </div>

            <div class="db-table-responsive">
                <table class="db-table stripe" id="chef_print">
                    <thead class="db-table-head">
                        <tr class="db-table-head-tr">
                            <th class="db-table-head-th">{{ $t("label.name") }}</th>
                            <th class="db-table-head-th">{{ $t("label.email") }}</th>
                            <th class="db-table-head-th">{{ $t("label.phone") }}</th>
                            <th class="db-table-head-th">{{ $t("label.status") }}</th>
                            <th class="db-table-head-th hidden-print"
                                v-if="permissionChecker('chefs_edit') || permissionChecker('chefs_delete')">
                                {{ $t("label.action") }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="db-table-body" v-if="chefs.length > 0">
                        <tr class="db-table-body-tr" v-for="chef in chefs" :key="chef.id">
                            <td class="db-table-body-td">
                                <div class="flex items-center gap-2">
                                    <img :src="chef.image" class="w-8 h-8 rounded-full object-cover" alt="avatar">
                                    <span>{{ textShortener(chef.name, 20) }}</span>
                                </div>
                            </td>
                            <td class="db-table-body-td">{{ chef.email }}</td>
                            <td class="db-table-body-td">{{ chef.country_code + '' + chef.phone }}</td>
                            <td class="db-table-body-td">
                                <span :class="statusClass(chef.status)">
                                    {{ enums.statusEnumArray[chef.status] }}
                                </span>
                            </td>
                            <td class="db-table-body-td hidden-print"
                                v-if="permissionChecker('chefs_edit') || permissionChecker('chefs_delete')">
                                <div class="flex justify-start items-center gap-1.5">
                                    <SmIconSidebarModalEditComponent @click="edit(chef)" v-if="permissionChecker('chefs_edit')" />
                                    <SmIconDeleteComponent @click="destroy(chef.id)" v-if="permissionChecker('chefs_delete')" />
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    <tbody class="db-table-body" v-else>
                        <tr class="db-table-body-tr">
                            <td class="db-table-body-td text-center" colspan="5">{{ $t("message.no_chef_found") }}</td>
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
import ChefCreateComponent from "./ChefCreateComponent";
import alertService from "../../../services/alertService";
import PaginationTextComponent from "../components/pagination/PaginationTextComponent";
import PaginationBox from "../components/pagination/PaginationBox";
import PaginationSMBox from "../components/pagination/PaginationSMBox";
import appService from "../../../services/appService";
import statusEnum from "../../../enums/modules/statusEnum";
import TableLimitComponent from "../components/TableLimitComponent";
import SmIconSidebarModalEditComponent from "../components/buttons/SmIconSidebarModalEditComponent";
import SmIconDeleteComponent from "../components/buttons/SmIconDeleteComponent";

export default {
    name: "ChefListComponent",
    components: {
        TableLimitComponent, PaginationSMBox, PaginationBox, PaginationTextComponent,
        ChefCreateComponent, LoadingComponent, SmIconDeleteComponent, SmIconSidebarModalEditComponent,
    },
    data() {
        return {
            loading: { isActive: false },
            enums: {
                statusEnum: statusEnum,
                statusEnumArray: {
                    [statusEnum.ACTIVE]: this.$t("label.active"),
                    [statusEnum.INACTIVE]: this.$t("label.inactive"),
                },
            },
            props: {
                form: {
                    name: "", email: "", phone: "", password: "", password_confirmation: "",
                    country_code: "", branch_id: null, role_id: 5, status: statusEnum.ACTIVE,
                },
                search: {
                    paginate: 1, page: 1, per_page: 10,
                    order_column: "id", order_type: "desc",
                },
            },
            country_code: "",
        };
    },
    computed: {
        chefs() { return this.$store.getters["chef/lists"]; },
        pagination() { return this.$store.getters["chef/pagination"]; },
        paginationPage() { return this.$store.getters["chef/page"]; },
    },
    mounted() {
        this.list();
    },
    methods: {
        permissionChecker(e) { return appService.permissionChecker(e); },
        statusClass(status) { return appService.statusClass(status); },
        textShortener(text, number = 30) { return appService.textShortener(text, number); },
        list(page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch("chef/lists", this.props.search).then(() => {
                this.loading.isActive = false;
            }).catch(() => { this.loading.isActive = false; });
        },
        edit(chef) {
            appService.sideDrawerShow();
            this.$store.dispatch("chef/edit", chef.id);
            this.props.form = {
                name: chef.name, email: chef.email, phone: chef.phone,
                password: "", password_confirmation: "",
                branch_id: chef.branch_id, role_id: 5,
                status: chef.status, country_code: chef.country_code || this.country_code,
            };
        },
        destroy(id) {
            appService.destroyConfirmation().then(() => {
                try {
                    this.loading.isActive = true;
                    this.$store.dispatch("chef/destroy", { id, search: this.props.search }).then(() => {
                        this.loading.isActive = false;
                        alertService.successFlip(null, this.$t("menu.chefs"));
                    }).catch((err) => {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    });
                } catch (err) {
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                }
            }).catch(() => { this.loading.isActive = false; });
        },
    },
};
</script>

<style scoped>
@media print {
    .hidden-print { display: none !important; }
}
</style>
