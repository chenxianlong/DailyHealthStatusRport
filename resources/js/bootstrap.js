window._ = require('lodash');

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found');
}

window.laravelRoute = function (name, params) {
    return window.route(name, params, false).toString();
};

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// window.Pusher = require('pusher-js');

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     forceTLS: true
// });

const expandIterable = function (iterables2Expand) {
    let list = [];
    if (typeof iterables2Expand === "object" || Array.isArray(iterables2Expand)) {
        for (let i in iterables2Expand) {
            let iterable2Expand = iterables2Expand[i];
            let expandResult = expandIterable(iterable2Expand);
            list = list.concat(expandResult);
        }
    } else {
        list.push(iterables2Expand)
    }
    return list;
};

import Vue from "vue";

import Vuex from 'vuex';
Vue.use(Vuex);
import VueRouter from 'vue-router';
Vue.use(VueRouter);

import ElementUI from 'element-ui';
import 'element-ui/lib/theme-chalk/index.css';
Vue.use(ElementUI);

Vue.mixin({
    methods: {
        ziggyRoute: route,
        laravelRoute: laravelRoute,
        $apiResponseHandler: function (onSuccess, app) {
            return (response) => {
                if (response.data.result) {
                    onSuccess(response.data.data);
                } else {
                    this.$responseErrorHandler(response, app);
                }
            };
        },
        $successMessage: function (message) {
            this.$message({
                showClose: true,
                message: message,
                type: "success",
            });
        },
        $errorMessage: function (message) {
            console.log(message);
            this.$message({
                showClose: true,
                message: message,
                type: "error",
            });
        },
        $getErrorSetter: function (app = undefined, varName = undefined) {
            if (typeof app === "undefined") {
                app = this;
            }
            if (typeof varName === "undefined") {
                varName = "errors";
            }
            if (typeof app === "string") {
                varName = app;
                app = this;
            }

            let setErrors;
            if (app instanceof Vue && typeof varName === "string" && app.hasOwnProperty(varName)) {
                setErrors = (errors) => {
                    app[varName] = errors;
                };
            } else if (typeof app === "function") {
                setErrors = app;
            } else {
                setErrors = (errors) => {
                    errors = expandIterable(errors);
                    for (let i in errors) {
                        this.$errorMessage(errors[i]);
                    }
                };
            }
            return setErrors;
        },
        $axiosErrorHandler: function (error, app, varName) {
            console.log(error);
            let setErrors = this.$getErrorSetter(app, varName);
            if (error.response) {
                this.$responseErrorHandler(error.response, setErrors);
            } else {
                setErrors(["请求出错：" + error.toString()]);
            }
        },
        $responseErrorHandler: function (response, app, varName) {
            let setErrors = this.$getErrorSetter(app, varName);
            if (response.data.hasOwnProperty("errors")) {
                setErrors(response.data.errors);
            } else if (response.data.hasOwnProperty("message")) {
                setErrors([response.data.message]);
            } else {
                setErrors(["无法解析服务器响应"]);
            }
        },
    },
});

function twoDigits(d) {
    if(0 <= d && d < 10) return "0" + d.toString();
    if(-10 < d && d < 0) return "-0" + (-1*d).toString();
    return d.toString();
}

window.twoDigits = twoDigits;

Date.prototype.toMysqlFormat = function() {
    return this.getFullYear() + "-" + twoDigits(1 + this.getMonth()) + "-" + twoDigits(this.getDate()) + " " + twoDigits(this.getHours()) + ":" + twoDigits(this.getMinutes()) + ":" + twoDigits(this.getSeconds());
};

export default Vue;
