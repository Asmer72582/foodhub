
import PosComponentEdit from "../../components/admin/pos/PosComponentEdit.vue";
import PosOrderComponent from "../../components/admin/posOrders/PosOrderComponent";
import PosOrderListComponent from "../../components/admin/posOrders/PosOrderListComponent";
import PosOrderShowComponent from "../../components/admin/posOrders/PosOrderShowComponent";

export default [
    {
        path: "/admin/pos-orders",
        component: PosOrderComponent,
        name: "admin.pos.orders",
        redirect: { name: "admin.pos.orders.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: 'pos',
            breadcrumb: 'pos_orders'
        },
        children: [
            {
                path: "",
                component: PosOrderListComponent,
                name: "admin.pos.orders.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "pos",
                    breadcrumb: "",
                },
            },
            {
                path: "show/:id",
                component: PosOrderShowComponent,
                name: "admin.pos.orders.show",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "pos",
                    breadcrumb: "view",
                },
            },
            {
                path: "edit/:id",
                component: PosComponentEdit,
                name: "admin.pos.orders.edit",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "pos",
                    breadcrumb: "view",
                },
            }
        ],
    },
];
