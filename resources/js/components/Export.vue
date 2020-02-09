<template>
    <div>
        <page-title title="导出"/>
        <el-form v-if="authenticated" label-width="80px" size="small" v-loading="isLoading">
            <el-form-item label="日期">
                <el-date-picker
                    v-model="date"
                    type="date"
                    placeholder="选择日期"
                    value-format="yyyy-MM-dd"
                    :picker-options="{
                        disabledDate: disabledDate,
                    }">
                </el-date-picker>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" v-on:click="exportAll">导出所有报告</el-button>
                <el-button type="primary" v-on:click="exportNotReported">导出未填人员</el-button>
            </el-form-item>
        </el-form>
        <el-form v-else label-width="80px" size="small" v-loading="isLoading" v-on:submit.native.prevent="authenticate">
            <el-form-item label="访问密码">
                <el-input type="password" v-model="password"/>
            </el-form-item>
            <el-form-item>
                <el-button native-type="submit" type="primary">验证</el-button>
            </el-form-item>
        </el-form>
    </div>
</template>

<script>
    import PageTitle from "./PageTitle";
    export default {
        name: "Export",
        components: {PageTitle},
        data: function () {
            return {
                isLoading: false,
                authenticated: true,
                availableDates: [],
                password: "",
                date: "",
            };
        },
        created: function () {
            this.isLoading = true;
            axios.get(laravelRoute("export.status")).then(this.$apiResponseHandler((data) => {
                this.authenticated = data.authenticated;
                this.availableDates = data.availableDates;
            })).catch(this.$axiosErrorHandler).then(() => {
                this.isLoading = false;
            })
        },
        methods: {
            disabledDate: function (time) {
                return !this.availableDates.hasOwnProperty(time.toMysqlFormat());
            },
            authenticate: function () {
                this.isLoading = true;
                axios.post(laravelRoute("export.authenticate"), {password: this.password}).then(this.$apiResponseHandler((data) => {
                    this.authenticated = true;
                    this.availableDates = data.availableDates;
                    this.$successMessage("身份验证通过");
                })).catch(this.$axiosErrorHandler).then(() => {
                    this.isLoading = false;
                })
            },
            exportAll: function () {
                if (this.date.length === 0) {
                    this.$errorMessage("请选择日期");
                    return;
                }
                window.open(laravelRoute("export.all", {date: this.date}));
            },
            exportNotReported: function () {
                if (this.date.length === 0) {
                    this.$errorMessage("请选择日期");
                    return;
                }
                window.open(laravelRoute("export.notReported", {date: this.date}));
            },
        },
    }
</script>

<style scoped>

</style>
