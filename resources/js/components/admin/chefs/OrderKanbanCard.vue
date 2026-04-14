<template>
    <div class="order-card shadow-md transition-all duration-300" :class="[cardBorderClass, {'scale-[1.02] shadow-lg': column === 'pending'}]">
        <!-- Compact Header -->
        <div class="card-header-compact" :class="headerBgClass">
            <div class="flex items-center justify-between gap-1 mb-1.5">
                <span class="order-id text-lg font-black tracking-tight">#{{ order.order_serial_no }}</span>
                <span v-if="order.order_type" class="text-[11px] font-extrabold text-white bg-slate-800 px-2 py-1 rounded-md flex items-center gap-1.5 shadow-sm" :title="orderTypeLabel">
                    <i :class="orderTypeIcon"></i> {{ orderTypeLabel.toUpperCase() }}
                </span>
                <div class="flex items-center gap-1.5">
                    <span :class="statusBadgeClass">{{ statusLabel }}</span>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 overflow-hidden bg-white/50 px-2 py-1 rounded-full border border-white/20">
                    <img :src="order.user.image" class="w-5 h-5 rounded-full ring-1 ring-white" v-if="order && order.user && order.user.image"/>
                    <img src="/images/default/profile.png" class="w-5 h-5 rounded-full ring-1 ring-white" v-else-if="order && order.user"/>
                    <span class="text-[12px] font-bold text-gray-800 truncate max-w-[120px]">{{ order.user ? order.user.name : 'Guest' }}</span>
                </div>
                <div class="flex items-center gap-1.5 text-[11px] font-black px-2 py-1 rounded-md" :class="timeColorClass">
                    <i class="fa-regular fa-clock"></i>
                    <span>{{ timeElapsed }}</span>
                </div>
            </div>
        </div>

        <!-- Condensed Items -->
        <div class="card-body-compact">
            <div v-if="order.order_items && order.order_items.length > 0">
                <div v-for="(item, i) in order.order_items" :key="i" class="flex flex-col py-2 border-b border-gray-100 last:border-0 leading-tight">
                    <span class="text-[14px] text-gray-900 font-extrabold flex items-start">
                        <span class="bg-primary text-white text-[12px] px-1.5 rounded mr-2 min-w-[24px] text-center">{{ item.quantity }}x</span>
                        <span class="flex-1 uppercase tracking-tight">{{ item.item_name || (item.order_item ? item.order_item.name : 'Unknown') }}</span>
                    </span>
                    <div class="pl-8 mt-1 space-y-1">
                        <div v-if="item.item_variations && parseVariation(item.item_variations)" class="text-[11px] bg-slate-50 text-slate-700 p-1 rounded border border-slate-100">
                            <span class="font-black text-[9px] uppercase text-slate-400 block mb-0.5">Variations:</span>
                            {{ parseVariation(item.item_variations) }}
                        </div>
                        <div v-if="item.item_extras && parseExtras(item.item_extras)" class="text-[11px] bg-indigo-50 text-indigo-700 p-1 rounded border border-indigo-100">
                            <span class="font-black text-[9px] uppercase text-indigo-400 block mb-0.5">Extras/Add-ons:</span>
                            {{ parseExtras(item.item_extras) }}
                        </div>
                        <div v-if="item.instruction" class="text-[11px] bg-amber-50 text-amber-700 p-1.5 rounded border border-amber-200 font-bold italic">
                             <i class="fa-solid fa-comment-dots mr-1"></i> "{{ item.instruction }}"
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="text-xs text-gray-400 italic text-center py-4 bg-gray-50 rounded-lg border-2 border-dashed border-gray-100">No items found</div>
            
            <div v-if="order.instruction" class="mt-3 text-[12px] bg-rose-50 text-rose-700 p-2.5 rounded-lg border-2 border-rose-100 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-1 bg-rose-200/30 rounded-bl-lg">
                    <i class="fa-solid fa-triangle-exclamation text-[10px]"></i>
                </div>
                <span class="font-black uppercase text-[9px] block mb-1">Kitchen Instruction:</span>
                <span class="font-bold font-serif">{{ order.instruction }}</span>
            </div>
        </div>

        <!-- Minimal Actions -->
        <div class="card-actions-compact">
            <button v-if="column === 'pending' || column === 'processing'"
                @click="$emit('move-forward')"
                class="compact-btn btn-forward h-10 text-[13px]" :title="column === 'pending' ? 'Accept Order' : 'Mark as Ready'">
                <i class="fa-solid" :class="column === 'pending' ? 'fa-play' : 'fa-check-double'"></i>
                <span>{{ column === 'pending' ? 'ACCEPT' : 'READY TO SERVE' }}</span>
            </button>
            
            <div class="flex gap-2" :class="{'w-full justify-between mt-1': column === 'processing'}">
                <button v-if="column === 'processing' || column === 'completed'"
                    @click="$emit('move-back')"
                    class="compact-btn btn-back w-10 h-10" title="Move Back">
                    <i class="fa-solid fa-arrow-rotate-left"></i>
                </button>
                <button v-if="column !== 'completed'"
                    @click="$emit('cancel')"
                    class="compact-btn btn-cancel w-10 h-10" title="Cancel Order">
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
        headerBgClass() {
            if (this.column === 'pending') return 'bg-amber-50/80';
            if (this.column === 'processing') return 'bg-indigo-50/80';
            return 'bg-teal-50/80';
        },
        statusLabel() {
            const map = { 1: "NEW", 4: "ACCEPTED", 7: "COOKING", 10: "READY", 13: "DONE", 16: "CANCELLED", 19: "REJECTED" };
            return map[this.order.status] || "ST: " + this.order.status;
        },
        statusBadgeClass() {
            const base = "px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-tight shadow-sm ";
            const map = {
                1:  base + "bg-amber-500 text-white animate-pulse",
                4:  base + "bg-blue-600 text-white",
                7:  base + "bg-indigo-600 text-white",
                10: base + "bg-teal-600 text-white",
                13: base + "bg-green-600 text-white",
            };
            return map[this.order.status] || base + "bg-gray-500 text-white";
        },
        cardBorderClass() {
             const map = {
                1:  "border-l-[6px] border-amber-500",
                4:  "border-l-[6px] border-blue-600",
                7:  "border-l-[6px] border-indigo-600",
                10: "border-l-[6px] border-teal-600",
            };
            return map[this.order.status] || "border-l-[6px] border-gray-400";
        },
        orderTypeIcon() {
            const map = { 1: "fa-solid fa-globe", 2: "fa-solid fa-desktop", 3: "fa-solid fa-utensils" };
            return map[this.order.order_type] || "fa-solid fa-circle-question";
        },
        orderTypeLabel() {
            const map = { 1: "Online", 2: "POS", 3: "Table" };
            return map[this.order.order_type] || "Other";
        },
        timeElapsed() {
            if (!this.order.order_datetime) return "0M";
            const diff = Math.floor((this.now - new Date(this.order.order_datetime)) / 60000);
            if (diff < 0) return "NOW";
            if (diff < 60) return `${diff}M`;
            return `${Math.floor(diff / 60)}H ${diff % 60}M`;
        },
        timeColorClass() {
            if (!this.order.order_datetime) return "text-gray-400 bg-gray-100";
            const diff = Math.floor((this.now - new Date(this.order.order_datetime)) / 60000);
            if (diff > 30) return "text-red-700 bg-red-100 ring-1 ring-red-200";
            if (diff > 15) return "text-amber-700 bg-amber-100 ring-1 ring-amber-200";
            return "text-emerald-700 bg-emerald-50 ring-1 ring-emerald-100";
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
    border-radius: 0.75rem;
    border: 1px solid #eef2f6;
    margin-bottom: 0.875rem;
    overflow: hidden;
}
.card-header-compact {
    padding: 0.875rem 1rem;
    border-bottom: 1px solid #e2e8f0;
}
.card-body-compact {
    padding: 1rem;
    min-height: 40px;
}
.card-actions-compact {
    padding: 0.75rem 1rem;
    background: #f8fafc;
    border-top: 1px dashed #cbd5e1;
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.compact-btn {
    border: none;
    outline: none;
    cursor: pointer;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 800;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.compact-btn:active { transform: scale(0.95); }
.btn-forward {
    background: #6366f1;
    color: white;
    flex-grow: 1;
    box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.3);
}
.btn-forward:hover { background: #4f46e5; }
.btn-back {
    background: #f1f5f9;
    color: #475569;
}
.btn-back:hover { background: #e2e8f0; }
.btn-cancel {
    background: #fff1f2;
    color: #e11d48;
}
.btn-cancel:hover { background: #ffe4e6; }
</style>

