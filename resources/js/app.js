import Vue from "./bootstrap"

const routes = [
    {
        name: "welcome",
        path: "/",
        component: require("./components/Welcome").default,
        meta: {
            title: "首页",
        },
    },
    {
        path: "/healthStatus/daily",
        component: require("./components/DailyHealthStatusForm").default,
        meta: {
            title: "填写 - 健康上报",
        },
    },
    {
        path: "/bind",
        component: require("./components/BindUser").default,
        meta: {
            title: "绑定身份证",
        },
    },
    {
        path: "/export",
        component: require("./components/Export").default,
        meta: {
            title: "导出",
        },
    },
];

import vueRouterCreator from "./vue-router-creator";
const router = vueRouterCreator({
    mode: 'history',
    routes: routes,
});

import Vuex from 'vuex'
const store = new Vuex.Store({});

const app = new Vue({
    render: h => h(require("./components/App").default),
    router,
    store,
    el: '#app',
});
