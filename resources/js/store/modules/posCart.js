import _ from "lodash";
import activityEnum from "../../enums/modules/activityEnum";
import orderTypeEnum from "../../enums/modules/orderTypeEnum";
import axios from "axios";


export const posCart = {
    namespaced: true,
    state: {
        lists: [],
        subtotal: 0,
        discount: 0,
        orderType: null,
        orderMethod: null,
        total: 0,
        token: null
    },
    getters: {
        lists: function (state) {
            return state.lists;
        },
        subtotal: function (state) {
            return state.subtotal;
        },
        discount: function (state) {
            return state.discount;
        },
        orderType: function (state) {
            return state.orderType;
        },
        orderMethod: function (state) {
            return state.orderMethod;
        },
        total: function (state) {
            return state.total;
        },
        token: function (state) {
            return state.token;
        }
    },
    actions: {
        lists: function (context, payload) {
            context.commit("lists", payload);
            context.commit("subtotal");
        },
        quantity: function (context, payload) {
            context.commit("quantity", payload);
            context.commit("subtotal");
            context.commit("discount", 0);
        },
        deleteCartItem: function (context, payload) {
            context.commit("deleteCartItem", payload);
            context.commit("subtotal");
            context.commit("discount", 0);
        },
        discount: function (context, payload) {
            context.commit("discount", payload);
        },
        destroyDiscount: function (context) {
            context.commit('discount', 0);
        },
        resetCart: function (context) {
            context.commit('resetCart');
        },
        updateToken: function (context, token) {
            context.commit('updateToken', token);
        },
    },
    mutations: {
        lists: function (state, payload) {
            if (!Array.isArray(payload)) {
                return;
            }

            payload.forEach(pay => {
                // Check if item already exists with same variations, extras, and instruction
                let existingItemIndex = state.lists.findIndex(item => {
                    if (item.item_id !== pay.item_id) {
                        return false;
                    }

                    // Check variations match
                    const variationsMatch = JSON.stringify(item.item_variations) === JSON.stringify(pay.item_variations);
                    
                    // Check extras match
                    const extrasMatch = JSON.stringify(item.item_extras) === JSON.stringify(pay.item_extras);

                    // Check instruction match
                    const instructionMatch = item.instruction === (pay.instruction || "");

                    return variationsMatch && extrasMatch && instructionMatch;
                });

                if (existingItemIndex !== -1) {
                    // Update existing item quantity
                    state.lists[existingItemIndex].quantity += parseInt(pay.quantity) || 1;
                } else {
                    // Add new item
                    state.lists.push({
                        discount: parseFloat(pay.discount) || 0,
                        image: pay.image,
                        instruction: pay.instruction || "",
                        item_extra_total: parseFloat(pay.item_extra_total) || 0,
                        item_extras: pay.item_extras || { extras: [], names: [] },
                        item_id: pay.item_id,
                        item_variation_total: parseFloat(pay.item_variation_total) || 0,
                        item_variations: pay.item_variations || { variations: {}, names: {} },
                        name: pay.name,
                        currency_price: parseFloat(pay.currency_price) || 0,
                        convert_price: parseFloat(pay.convert_price) || 0,
                        quantity: parseInt(pay.quantity) || 1,
                        total: parseFloat(pay.total) || 0
                    });
                }
            });
        },
        subtotal: function (state) {
            if (state.lists.length > 0) {
                let subtotal = 0;
                state.lists.forEach((list) => {
                    const convertPrice = parseFloat(list.convert_price) || 0;
                    const itemTotal = (convertPrice +
                        (parseFloat(list.item_variation_total) || 0) +
                        (parseFloat(list.item_extra_total) || 0)) * list.quantity;
                    list.total = itemTotal;
                    subtotal += itemTotal;
                });
                state.subtotal = subtotal;
            } else {
                state.subtotal = 0;
            }
        },
        quantity: function (state, payload) {
            if (payload.status === "increment") {
                state.lists[payload.id].quantity++;
            } else if (payload.status === "decrement") {
                if (state.lists[payload.id].quantity === 1) {
                    state.lists.splice(payload.id, 1);
                    state.discount = 0;
                } else {
                    state.lists[payload.id].quantity--;
                }
            } else {
                state.lists[payload.id].quantity = parseInt(payload.status) || 1;
            }
        },
        deleteCartItem: function (state, payload) {
            state.lists.splice(payload.id, 1);
            if (state.lists.length === 0) {
                state.discount = 0;
            }
        },
        discount: function (state, payload) {
            state.discount = parseFloat(payload) || 0;
        },
        resetCart: function (state) {
            state.lists = [];
            state.subtotal = 0;
            state.discount = 0;
            state.token = '';
        },
        updateToken: function (state, token) {
            state.token = token;
        },
    }
};
