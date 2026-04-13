import axios from "axios";
import appService from "../../services/appService";

export const chef = {
    namespaced: true,
    state: {
        lists: [],
        page: {},
        pagination: [],
        show: {},
        temp: {
            temp_id: null,
            isEditing: false,
        },
        kanban: {
            pending: [],
            processing: [],
            completed: [],
        },
    },
    getters: {
        lists: (state) => state.lists,
        pagination: (state) => state.pagination,
        page: (state) => state.page,
        show: (state) => state.show,
        temp: (state) => state.temp,
        kanban: (state) => state.kanban,
    },
    actions: {
        lists(context, payload) {
            return new Promise((resolve, reject) => {
                let url = "admin/chef";
                if (payload) url = url + appService.requestHandler(payload);
                axios.get(url).then((res) => {
                    if (typeof payload.vuex === "undefined" || payload.vuex === true) {
                        context.commit("lists", res.data.data);
                        context.commit("page", res.data.meta);
                        context.commit("pagination", res.data);
                    }
                    resolve(res);
                }).catch(reject);
            });
        },
        save(context, payload) {
            return new Promise((resolve, reject) => {
                let method = axios.post;
                let url = "/admin/chef";
                if (context.state.temp.isEditing) {
                    method = axios.put;
                    url = `/admin/chef/${context.state.temp.temp_id}`;
                }
                method(url, payload.form).then((res) => {
                    context.dispatch("lists", payload.search).then().catch();
                    context.commit("reset");
                    resolve(res);
                }).catch(reject);
            });
        },
        edit(context, payload) {
            context.commit("temp", payload);
        },
        destroy(context, payload) {
            return new Promise((resolve, reject) => {
                axios.delete(`admin/chef/${payload.id}`).then((res) => {
                    context.dispatch("lists", payload.search).then().catch();
                    resolve(res);
                }).catch(reject);
            });
        },
        show(context, payload) {
            return new Promise((resolve, reject) => {
                axios.get(`admin/chef/show/${payload}`).then((res) => {
                    context.commit("show", res.data.data);
                    resolve(res);
                }).catch(reject);
            });
        },
        reset(context) {
            context.commit("reset");
        },
        kanban(context) {
            return new Promise((resolve, reject) => {
                axios.get("admin/chef/kanban").then((res) => {
                    context.commit("kanban", res.data.data);
                    resolve(res);
                }).catch(reject);
            });
        },
        changeOrderStatus(context, payload) {
            return new Promise((resolve, reject) => {
                axios.post(`admin/chef/order-status/${payload.orderId}`, { status: payload.status })
                    .then((res) => {
                        // Refresh kanban after update
                        context.dispatch("kanban");
                        resolve(res);
                    }).catch(reject);
            });
        },
        changePassword(context, payload) {
            return new Promise((resolve, reject) => {
                axios.post(`/admin/chef/change-password/${payload.id}`, payload.form).then(resolve).catch(reject);
            });
        },
        changeImage(context, payload) {
            return new Promise((resolve, reject) => {
                axios.post(`/admin/chef/change-image/${payload.id}`, payload.form, {
                    headers: { "Content-Type": "multipart/form-data" },
                }).then((res) => {
                    context.commit("show", res.data.data);
                    resolve(res);
                }).catch(reject);
            });
        },
    },
    mutations: {
        lists(state, payload) { state.lists = payload; },
        pagination(state, payload) { state.pagination = payload; },
        page(state, payload) {
            if (typeof payload !== "undefined" && payload !== null) {
                state.page = { from: payload.from, to: payload.to, total: payload.total };
            }
        },
        show(state, payload) { state.show = payload; },
        temp(state, payload) { state.temp.temp_id = payload; state.temp.isEditing = true; },
        reset(state) { state.temp.temp_id = null; state.temp.isEditing = false; },
        kanban(state, payload) { state.kanban = payload; },
    },
};
