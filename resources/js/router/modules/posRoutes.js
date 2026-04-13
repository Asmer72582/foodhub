import OnlineOrderComponent from "../../components/admin/onlineOrders/OnlineOrderComponent.vue";
import PosComponent from "../../components/admin/pos/PosComponent";
import PosComponentEdit from "../../components/admin/pos/PosComponentEdit.vue";
import PosOrderCompletedComponent from "../../components/admin/posOrders/PosOrderCompletedComponent.vue";

export default [
    {
        path: "/admin/pos",
        component: PosComponent,
        name: "admin.pos",
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "pos",
        },
    },
    {
        path: "/admin/pos/edit",
        component: PosComponentEdit,
        name: "admin.pos.edit",
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "pos",
        },
    },
    {
        path: "/admin/pos/orders/completed",
        component: PosOrderCompletedComponent,
        name: "admin.pos.orders.completed",
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "pos-orders",
        },
    },
];
