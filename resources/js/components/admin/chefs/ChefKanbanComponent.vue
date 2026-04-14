<template>
    <LoadingComponent :props="loading" />
    <div class="col-12 chef-kanban-wrapper">
        <!-- Header -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-4 py-3 mb-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                        <span class="text-2xl">👨‍🍳</span> Kitchen Dashboard
                    </h1>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[10px] text-gray-400 uppercase font-bold hidden sm:inline">Auto-refresh active</span>
                    <button @click="fetchKanban" class="flex items-center gap-2 px-3 py-1.5 bg-primary text-white rounded-lg text-xs font-bold hover:opacity-90 transition shadow-sm">
                        <i class="fa-solid fa-rotate-right"></i>
                        <span>REFRESH</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Kanban Columns -->
        <div class="kanban-grid">

            <!-- Pending Column -->
            <div class="kanban-column border-t-4 border-amber-400">
                <div class="kanban-col-header bg-amber-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-bullhorn text-amber-500 text-xs"></i>
                            <h3 class="col-title uppercase tracking-tighter">New Orders</h3>
                        </div>
                        <span class="col-badge bg-amber-200 text-amber-800">{{ kanban.pending ? kanban.pending.length : 0 }}</span>
                    </div>
                </div>
                <div class="kanban-cards-container">
                    <div v-if="kanban.pending && kanban.pending.length > 0"
                         v-for="order in kanban.pending" :key="order.id">
                        <OrderKanbanCard :order="order" column="pending"
                            @move-forward="moveForward(order)"
                            @cancel="cancelOrder(order)" />
                    </div>
                    <div v-else class="kanban-empty">
                        <p class="text-xs text-gray-400">No new orders</p>
                    </div>
                </div>
            </div>

            <!-- Processing/Cooking Column -->
            <div class="kanban-column border-t-4 border-indigo-500">
                <div class="kanban-col-header bg-indigo-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-fire-burner text-indigo-500 text-xs"></i>
                            <h3 class="col-title uppercase tracking-tighter">In Cooking</h3>
                        </div>
                        <span class="col-badge bg-indigo-200 text-indigo-800">{{ kanban.processing ? kanban.processing.length : 0 }}</span>
                    </div>
                </div>
                <div class="kanban-cards-container">
                    <div v-if="kanban.processing && kanban.processing.length > 0"
                         v-for="order in kanban.processing" :key="order.id">
                        <OrderKanbanCard :order="order" column="processing"
                            @move-forward="moveForward(order)"
                            @move-back="moveBack(order)"
                            @cancel="cancelOrder(order)" />
                    </div>
                    <div v-else class="kanban-empty">
                        <p class="text-xs text-gray-400">Kitchen is idle</p>
                    </div>
                </div>
            </div>

            <!-- Completed Column -->
            <div class="kanban-column border-t-4 border-teal-500">
                <div class="kanban-col-header bg-teal-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-bell-concierge text-teal-600 text-xs"></i>
                            <h3 class="col-title uppercase tracking-tighter">Ready / Done</h3>
                        </div>
                        <span class="col-badge bg-teal-200 text-teal-800">{{ kanban.completed ? kanban.completed.length : 0 }}</span>
                    </div>
                </div>
                <div class="kanban-cards-container">
                    <div v-if="kanban.completed && kanban.completed.length > 0"
                         v-for="order in kanban.completed" :key="order.id">
                        <OrderKanbanCard :order="order" column="completed"
                            @move-back="moveBack(order)" />
                    </div>
                    <div v-else class="kanban-empty">
                        <p class="text-xs text-gray-400">Nothing ready yet</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../components/LoadingComponent";
import OrderKanbanCard from "./OrderKanbanCard";

// OrderStatus enums matching backend
const OrderStatus = {
    PENDING: 1,
    ACCEPT: 4,
    PROCESSING: 7,
    OUT_FOR_DELIVERY: 10,
    DELIVERED: 13,
    CANCELED: 16,
    REJECTED: 19,
};

export default {
    name: "ChefKanbanComponent",
    components: { LoadingComponent, OrderKanbanCard },
    data() {
        return {
            loading: { isActive: false },
            refreshInterval: null,
        };
    },
    computed: {
        kanban() { return this.$store.getters["chef/kanban"]; },
    },
    mounted() {
        this.fetchKanban();
        // Auto-refresh every 30s
        this.refreshInterval = setInterval(() => { this.fetchKanban(); }, 30000);
    },
    beforeUnmount() {
        clearInterval(this.refreshInterval);
    },
    methods: {
        fetchKanban() {
            this.loading.isActive = true;
            this.$store.dispatch("chef/kanban").then(() => {
                this.loading.isActive = false;
            }).catch(() => { this.loading.isActive = false; });
        },
        moveForward(order) {
            const nextStatus = this.getNextStatus(order.status);
            if (nextStatus) {
                this.changeStatus(order, nextStatus);
            }
        },
        moveBack(order) {
            const prevStatus = this.getPrevStatus(order.status);
            if (prevStatus) {
                this.changeStatus(order, prevStatus);
            }
        },
        cancelOrder(order) {
            if (confirm("Are you sure you want to cancel this order?")) {
                this.changeStatus(order, OrderStatus.CANCELED);
            }
        },
        changeStatus(order, status) {
            this.loading.isActive = true;
            this.$store.dispatch("chef/changeOrderStatus", { orderId: order.id, status }).then(() => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
                alert(err.response?.data?.message || "Failed to update order status.");
            });
        },
        getNextStatus(currentStatus) {
            const map = {
                [OrderStatus.PENDING]: OrderStatus.ACCEPT,
                [OrderStatus.ACCEPT]: OrderStatus.PROCESSING,
                [OrderStatus.PROCESSING]: OrderStatus.OUT_FOR_DELIVERY,
                [OrderStatus.OUT_FOR_DELIVERY]: OrderStatus.DELIVERED,
            };
            return map[currentStatus] ?? null;
        },
        getPrevStatus(currentStatus) {
            const map = {
                [OrderStatus.ACCEPT]: OrderStatus.PENDING,
                [OrderStatus.PROCESSING]: OrderStatus.ACCEPT,
                [OrderStatus.OUT_FOR_DELIVERY]: OrderStatus.PROCESSING,
                [OrderStatus.DELIVERED]: OrderStatus.OUT_FOR_DELIVERY,
            };
            return map[currentStatus] ?? null;
        },
    },
};
</script>

<style scoped>
.chef-kanban-wrapper {
    padding: 0;
    width: 100%;
}
.kanban-grid {
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    gap: 1rem;
    width: 100%;
}
@media (min-width: 1024px) {
    .kanban-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}
.kanban-column {
    background: #fdfdfe;
    border-radius: 0.75rem;
    overflow: hidden;
    border: 1px solid #eef2f6;
    display: flex;
    flex-direction: column;
    min-height: calc(100vh - 180px);
}
.kanban-col-header {
    padding: 0.625rem 0.875rem;
    border-bottom: 1px solid #eef2f6;
}
.col-title { font-weight: 800; font-size: 0.8rem; color: #334155; }
.col-badge {
    font-size: 0.7rem; font-weight: 800;
    padding: 1px 6px; border-radius: 6px;
}
.kanban-cards-container {
    padding: 0.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    max-height: calc(100vh - 160px);
}
.kanban-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100px;
    opacity: 0.5;
}
</style>
