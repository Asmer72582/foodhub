import axios from "axios";
import appService from "../../services/appService";

export const dashboard = {
    namespaced: true,
    state: {
        totalSales: [],
        totalTips: [],
        totalOrders: [],
        totalCustomers: [],
        totalMenuItems: [],
        orderStatistics: [],
        orderSummary: [],
        salesSummary: [],
        customerStates: [],
        featuredItems: [],
        mostPopularItems: [],
        topCustomers: [],
        employeeOrders: [],
        paymentBreakdownData: null,
        orderBreakdown: null,
        paymentMethodBreakdown: null
    },

    getters: {
        totalSales: function (state) {
            return state.totalSales;
        },
        totalTips: function (state) {
            return state.totalTips;
        },
        totalOrders: function (state) {
            return state.totalOrders;
        },
        totalCustomers: function (state) {
            return state.totalCustomers;
        },
        totalMenuItems: function (state) {
            return state.totalMenuItems;
        },
        orderStatistics: function (state) {
            return state.orderStatistics;
        },
        orderSummary: function (state) {
            return state.orderSummary;
        },
        salesSummary: function (state) {
            return state.salesSummary;
        },
        customerStates: function (state) {
            return state.customerStates;
        },
        featuredItems: function (state) {
            return state.featuredItems;
        },
        mostPopularItems: function (state) {
            return state.mostPopularItems;
        },
        topCustomers: function (state) {
            return state.topCustomers;
        },
        employeeOrders: function (state) {
            return state.employeeOrders;
        },
        getPaymentBreakdown: state => state.paymentBreakdownData,
        getOrderBreakdown: state => state.orderBreakdown,
        getPaymentMethodBreakdown: state => state.paymentMethodBreakdown
    },

    actions: {
        totalSales: function (context) {
            return new Promise((resolve, reject) => {
                axios
                    .get("admin/dashboard/total-sales")
                    .then((res) => {
                        context.commit("totalSales", res.data.data);
                        resolve(res);
                    })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
        totalTips: function (context) {
            return new Promise((resolve, reject) => {
                axios
                    .get("admin/dashboard/total-tips")
                    .then((res) => {
                        context.commit("totalTips", res.data.data);
                        resolve(res);
                    })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
        totalOrders: function (context) {
            return new Promise((resolve, reject) => {
                axios
                    .get("admin/dashboard/total-orders")
                    .then((res) => {
                        context.commit("totalOrders", res.data.data);
                        resolve(res);
                    })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
        totalCustomers: function (context) {
            return new Promise((resolve, reject) => {
                axios
                    .get("admin/dashboard/total-customers")
                    .then((res) => {
                        context.commit("totalCustomers", res.data.data);
                        resolve(res);
                    })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
        totalMenuItems: function (context) {
            return new Promise((resolve, reject) => {
                axios
                    .get("admin/dashboard/total-menu-items")
                    .then((res) => {
                        context.commit("totalMenuItems", res.data.data);
                        resolve(res);
                    })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
        orderStatistics: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = "admin/dashboard/order-statistics";
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }
                axios.get(url).then((res) => {
                    context.commit("orderStatistics", res.data.data);
                    resolve(res);
                })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
        orderSummary: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = "admin/dashboard/order-summary";
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }
                axios.get(url).then((res) => {
                    context.commit("orderSummary", res.data.data);
                    resolve(res);
                })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
        salesSummary: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = "admin/dashboard/sales-summary";
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }
                axios.get(url).then((res) => {
                    context.commit("salesSummary", res.data.data);
                    resolve(res);
                })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
        customerStates: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = "admin/dashboard/customer-states";
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }
                axios.get(url).then((res) => {
                    context.commit("customerStates", res.data.data);
                    resolve(res);
                })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
        featuredItems: function (context) {
            return new Promise((resolve, reject) => {
                axios
                    .get("admin/dashboard/featured-items")
                    .then((res) => {
                        context.commit("featuredItems", res.data.data);
                        resolve(res);
                    })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
        mostPopularItems: function (context) {
            return new Promise((resolve, reject) => {
                axios
                    .get("admin/dashboard/popular-items")
                    .then((res) => {
                        context.commit("mostPopularItems", res.data.data);
                        resolve(res);
                    })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
        topCustomers: function (context) {
            return new Promise((resolve, reject) => {
                axios
                    .get("admin/dashboard/top-customers")
                    .then((res) => {
                        context.commit("topCustomers", res.data.data);
                        resolve(res);
                    })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
        employeeOrders: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = "admin/dashboard/employee-orders";
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }
                axios.get(url).then((res) => {
                    context.commit("employeeOrders", res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        async paymentBreakdown({ commit }, params) {
            try {
                let url = "admin/dashboard/payment-breakdown";
                if (params) {
                    url = url + appService.requestHandler(params);
                }
                const response = await axios.get(url);
                commit('SET_PAYMENT_BREAKDOWN', response.data.data);
                return response;
            } catch (error) {
                throw error;
            }
        },
        orderBreakdown({ commit }, params) {
            return new Promise((resolve, reject) => {
                axios.get('/admin/dashboard/order-breakdown', { params })
                    .then((res) => {
                        if (res.data.data) {
                            commit('SET_ORDER_BREAKDOWN', res.data.data);
                            resolve(res);
                        } else {
                            reject(new Error('No data received'));
                        }
                    })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
        async paymentMethodBreakdown({ commit }, params) {
            try {
                let url = "admin/dashboard/payment-method-breakdown";
                if (params) {
                    url = url + appService.requestHandler(params);
                }
                const response = await axios.get(url);
                commit("SET_PAYMENT_METHOD_BREAKDOWN", response.data);
                return response.data;
            } catch (error) {
                throw error;
            }
        }
    },

    mutations: {
        totalSales: function (state, payload) {
            state.totalSales = payload;
        },
        totalTips: function (state, payload) {
            state.totalTips = payload;
        },
        totalOrders: function (state, payload) {
            state.totalOrders = payload;
        },
        totalCustomers: function (state, payload) {
            state.totalCustomers = payload;
        },
        totalMenuItems: function (state, payload) {
            state.totalMenuItems = payload;
        },
        orderStatistics: function (state, payload) {
            state.orderStatistics = payload;
        },
        orderSummary: function (state, payload) {
            state.orderSummary = payload;
        },
        salesSummary: function (state, payload) {
            state.salesSummary = payload;
        },
        customerStates: function (state, payload) {
            state.customerStates = payload;
        },
        featuredItems: function (state, payload) {
            state.featuredItems = payload;
        },
        mostPopularItems: function (state, payload) {
            state.mostPopularItems = payload;
        },
        topCustomers: function (state, payload) {
            state.topCustomers = payload;
        },
        employeeOrders: function (state, payload) {
            state.employeeOrders = payload;
        },
        SET_PAYMENT_BREAKDOWN(state, data) {
            state.paymentBreakdownData = data;
        },
        SET_ORDER_BREAKDOWN(state, data) {
            state.orderBreakdown = data;
        },
        SET_PAYMENT_METHOD_BREAKDOWN(state, data) {
            state.paymentMethodBreakdown = data;
        }
    },
};
