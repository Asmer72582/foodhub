import ChefComponent from "../../components/admin/chefs/ChefComponent";
import ChefListComponent from "../../components/admin/chefs/ChefListComponent";
import ChefKanbanComponent from "../../components/admin/chefs/ChefKanbanComponent";

export default [
    {
        path: "/admin/chefs",
        component: ChefComponent,
        name: "admin.chefs",
        redirect: { name: "admin.chefs.list" },
        meta: {
            isFrontend: false,
            auth: true,
            breadcrumb: "chefs",
        },
        children: [
            {
                path: "",
                component: ChefListComponent,
                name: "admin.chefs.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "chefs",
                    breadcrumb: "",
                },
            },
            {
                path: "kanban",
                component: ChefKanbanComponent,
                name: "admin.chefs.kanban",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "chef-kanban",
                    breadcrumb: "kanban",
                },
            },
        ],
    },
];
