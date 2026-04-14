<template>
    <div class="order-card shadow-sm" :class="cardBorderClass">
        <!-- Compact Header -->
        <div class="card-header-compact">
            <div class="flex items-center justify-between gap-1 mb-1">
                <span class="order-id">#{{ order.order_serial_no }}</span>
                <span v-if="order.order_type" class="text-[10px] font-bold text-gray-600 bg-gray-100 px-1.5 py-0.5 rounded flex items-center gap-1" :title="orderTypeLabel">
                    <i :class="orderTypeIcon"></i> {{ orderTypeLabel }}
                </span>
                <div class="flex items-center gap-1.5">
                    <span :class="statusBadgeClass">{{ statusLabel }}</span>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5 overflow-hidden">
                    <img :src="order.user.image || '/images/default/profile.png'" class="w-4 h-4 rounded-full" v-if="order.user"/>
                    <span class="text-[11px] font-semibold text-gray-700 truncate max-w-[100px]">{{ order.user ? order.user.name : 'Guest' }}</span>
                </div>
                <div class="flex items-center gap-1 text-[10px] whitespace-nowrap" :class="timeColorClass">
                    <i class="fa-regular fa-clock"></i>
                    <span>{{ timeElapsed }}</span>
                </div>
            </div>
        </div>

        <!-- Condensed Items -->
        <div class="card-body-compact">
            <div v-if="order.order_items && order.order_items.length > 0">
                <div v-for="(item, i) in order.order_items" :key="i" class="flex flex-col py-1 border-b border-gray-100 last:border-0 leading-tight">
                    <span class="text-[12px] text-gray-800 font-medium flex items-start">
                        <span class="text-primary font-bold mr-1">{{ item.quantity }}x</span>
                        <span class="flex-1">{{ item.order_item ? item.order_item.name : 'Unknown' }}</span>
                    </span>
                    <div class="pl-4 mt-0.5 text-[10px] text-gray-500">
                        <div v-if="item.item_variations && parseVariation(item.item_variations)" class="mb-0.5">
                            <span class="font-semibold text-gray-600">Options:</span> {{ parseVariation(item.item_variations) }}
                        </div>
                        <div v-if="item.item_extras && parseExtras(item.item_extras)" class="mb-0.5">
                            <span class="font-semibold text-gray-600">Extras:</span> {{ parseExtras(item.item_extras) }}
                        </div>
                        <div v-if="item.instruction" class="text-amber-600 font-medium italic mt-0.5 bg-amber-50 px-1 py-0.5 rounded">
                            "{{ item.instruction }}"
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="text-[10px] text-gray-400 italic text-center py-1">No items found</div>
            
            <div v-if="order.instruction" class="mt-2 text-[11px] bg-red-50 text-red-700 p-1.5 rounded border border-red-100">
                <span class="font-bold">Note:</span> {{ order.instruction }}
            </div>
        </div>

        <!-- Minimal Actions -->
        <div class="card-actions-compact">
            <button v-if="column === 'pending' || column === 'processing'"
                @click="$emit('move-forward')"
                class="compact-btn btn-forward" :title="column === 'pending' ? 'Accept' : 'Ready'">
                <i class="fa-solid" :class="column === 'pending' ? 'fa-play' : 'fa-check'"></i>
                <span>{{ column === 'pending' ? 'Accept' : 'Ready' }}</span>
            </button>
            
            <div class="flex gap-1" :class="{'w-full justify-between mt-1': column === 'processing'}">
                <button v-if="column === 'processing' || column === 'completed'"
                    @click="$emit('move-back')"
                    class="compact-btn btn-back" title="Move Back">
                    <i class="fa-solid fa-arrow-rotate-left"></i>
                </button>
                <button v-if="column !== 'completed'"
                    @click="$emit('cancel')"
                    class="compact-btn btn-cancel" title="Cancel">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "OrderKanbanCard",
    props: {
        order: { type: Object, required: true },
        column: { type: String, required: true },
    },
    emits: ["move-forward", "move-back", "cancel"],
    data() {
        return {
            now: new Date(),
            timer: null
        }
    },
    computed: {
        statusLabel() {
            const map = { 1: "New", 4: "Accepted", 7: "Cooking", 10: "Ready", 13: "Done", 16: "X", 19: "X" };
            return map[this.order.status] || "?";
        },
        statusBadgeClass() {
            const base = "px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-tighter ";
            const map = {
                1:  base + "bg-amber-100 text-amber-700",
                4:  base + "bg-blue-100 text-blue-700",
                7:  base + "bg-indigo-100 text-indigo-700",
                10: base + "bg-teal-100 text-teal-700",
                13: base + "bg-green-100 text-green-700",
            };
            return map[this.order.status] || base + "bg-gray-100 text-gray-500";
        },
        cardBorderClass() {
             const map = {
                1:  "border-l-4 border-amber-400",
                4:  "border-l-4 border-blue-400",
                7:  "border-l-4 border-indigo-500",
                10: "border-l-4 border-teal-500",
            };
            return map[this.order.status] || "border-l-4 border-gray-200";
        },
        orderTypeIcon() {
            const map = { 1: "fa-solid fa-globe", 2: "fa-solid fa-desktop", 3: "fa-solid fa-utensils" };
            return map[this.order.order_type] || "fa-solid fa-circle-question";
        },
        orderTypeLabel() {
            const map = { 1: "Online", 2: "POS", 3: "Table" };
            return map[this.order.order_type] || "Unknown";
        },
        timeElapsed() {
            if (!this.order.order_datetime) return "0m";
            const diff = Math.floor((this.now - new Date(this.order.order_datetime)) / 60000);
            if (diff < 0) return "just now";
            if (diff < 60) return `${diff}m`;
            return `${Math.floor(diff / 60)}h ${diff % 60}m`;
        },
        timeColorClass() {
            if (!this.order.order_datetime) return "text-gray-400";
            const diff = Math.floor((this.now - new Date(this.order.order_datetime)) / 60000);
            if (diff > 30) return "text-red-500 font-bold";
            if (diff > 15) return "text-amber-600 font-semibold";
            return "text-gray-500";
        }
    },
    methods: {
        parseVariation(data) {
            if (!data) return '';
            try {
                let parsed = typeof data === 'string' ? JSON.parse(data) : data;
                if (!parsed || Object.keys(parsed).length === 0) return '';
                if (parsed.names) {
                    return Object.entries(parsed.names).map(([k, v]) => `${k}: ${v}`).join(', ');
                }
                if (Array.isArray(parsed)) return parsed.map(v => (v.variation_name ? v.variation_name + ': ' : '') + (v.name || v)).join(', ');
                if (typeof parsed === 'object') return Object.values(parsed).map(v => (v.variation_name ? v.variation_name + ': ' : '') + (v.name || v)).filter(Boolean).join(', ');
                return String(parsed);
            } catch (e) { return String(data); }
        },
        parseExtras(data) {
            if (!data) return '';
            try {
                let parsed = typeof data === 'string' ? JSON.parse(data) : data;
                if (!parsed || Object.keys(parsed).length === 0) return '';
                if (Array.isArray(parsed)) return parsed.map(e => e.name || e).join(', ');
                if (typeof parsed === 'object') return Object.values(parsed).map(e => e.name || e).filter(Boolean).join(', ');
                return String(parsed);
            } catch (e) { return String(data); }
        }
    },
    mounted() {
        this.timer = setInterval(() => {
            this.now = new Date();
        }, 10000);
    },
    beforeUnmount() {
        clearInterval(this.timer);
    }
};
</script>

<style scoped>
.order-card {
    background: #ffffff;
    border-radius: 0.5rem;
    border: 1px solid #eef2f6;
    margin-bottom: 0.5rem;
    transition: all 0.2s ease;
    overflow: hidden;
}
.order-card:hover {
    transform: scale(1.01);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.card-header-compact {
    padding: 0.625rem 0.75rem;
    background: #fafafb;
    border-bottom: 1px solid #f1f5f9;
}
.order-id {
    font-size: 0.75rem;
    font-weight: 800;
    color: #0f172a;
}
.card-body-compact {
    padding: 0.625rem 0.75rem;
    min-height: 40px;
}
.card-actions-compact {
    padding: 0.5rem 0.75rem;
    background: #fbfbfc;
    border-top: 1px dashed #e2e8f0;
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.compact-btn {
    border: none;
    outline: none;
    cursor: pointer;
    border-radius: 0.375rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
    padding: 0.375rem 0.5rem;
    font-size: 0.7rem;
    font-weight: 700;
    transition: opacity 0.2s;
}
.compact-btn:hover { opacity: 0.8; }
.btn-forward {
    background: #6366f1;
    color: white;
    flex-grow: 1;
}
.btn-back {
    background: #f1f5f9;
    color: #64748b;
}
.btn-cancel {
    background: #fee2e2;
    color: #ef4444;
}
</style>

