<template>
    <div>
        <page-title title="导出"/>
        <el-form style="margin: 8px 8px 8px 8px;" v-if="authenticated" label-width="80px" size="small" v-on:submit.native.prevent="() => {}" v-loading="isLoading">
            <template v-if="allowExportTeachers === 2 && allowExportStudents === 2">
                <el-form-item label="人员类型" required>
                    <el-radio-group v-model="exportReportedType">
                        <el-radio v-if="allowExportTeachers === 2" :label="1">教职工</el-radio>
                        <el-radio :label="0">学生</el-radio>
                    </el-radio-group>
                </el-form-item>

                <el-form-item v-if="exportReportedType === 0" label="班级">
                    <el-select v-model="selectedExportClasses" filterable multiple clearable size="small" placeholder="全部">
                        <el-option v-for="availableClass in availableClasses" :key="availableClass" :label="availableClass" :value="availableClass"/>
                    </el-select>
                </el-form-item>

                <el-form-item>
                    <el-button type="primary" v-on:click="exportAll">导出报告</el-button>
                </el-form-item>

                <el-divider>OR</el-divider>
            </template>

            <el-form-item label="人员类型" required>
                <el-radio-group v-model="exportNotReportedType">
                    <el-radio v-if="allowExportTeachers !== 0" :label="1">教职工</el-radio>
                    <el-radio v-if="allowExportStudents !== 0" :label="0">学生</el-radio>
                </el-radio-group>
            </el-form-item>

            <el-form-item v-if="exportNotReportedType === 0" label="班级">
                <el-select v-model="selectedExportNotReportedClasses" filterable multiple clearable size="small" placeholder="全部">
                    <el-option v-for="availableClass in availableClasses" :key="availableClass" :label="availableClass" :value="availableClass"/>
                </el-select>
            </el-form-item>

            <el-form-item label="日期" required>
                <el-date-picker
                    v-model="date"
                    type="date"
                    placeholder="选择日期"
                    value-format="yyyy-MM-dd"
                    :picker-options="{
                        disabledDate: disabledDate,
                    }">
                </el-date-picker>
                <el-button type="primary" v-on:click="exportNotReported">导出未填人员</el-button>
            </el-form-item>
        </el-form>
        <el-form style="margin: 8px 8px 8px 8px;" v-else label-width="80px" size="small" v-loading="isLoading" v-on:submit.native.prevent="authenticate">
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
                availableClasses: [],
                allowExportTeachers: false,
                allowExportStudents: false,
                password: "",
                exportReportedType: null,
                exportNotReportedType: null,
                selectedExportClasses: [],
                selectedExportNotReportedClasses: [],
                date: "",
            };
        },
        created: function () {
            this.isLoading = true;
            axios.get(laravelRoute("export.status")).then(this.$apiResponseHandler((data) => {
                this.authenticated = data.authenticated;
                this.availableDates = data.availableDates;
                this.availableClasses = data.availableClasses;
                this.allowExportTeachers = data.allowExportTeachers;
                this.allowExportStudents = data.allowExportStudents;
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
                this.isLoading = true;
                axios.post("/export/all", {type: this.exportReportedType, selectedClasses: this.selectedExportClasses}).then(this.$apiResponseHandler((data) => {
                    window.location.href = laravelRoute("export.download", {filename: data.filename, expireAt: data.expireAt, userId: data.userId, salt: data.salt, signature: data.signature});
                })).catch(this.$axiosErrorHandler).then(() => {
                    this.isLoading = false;
                })
            },
            exportNotReported: function () {
                if (this.date.length === 0) {
                    this.$errorMessage("请选择日期");
                    return;
                }
                this.isLoading = true;
                axios.post("/export/notReported", {date: this.date, type: this.exportNotReportedType, selectedClasses: this.selectedExportNotReportedClasses}).then(this.$apiResponseHandler((data) => {
                    window.location.href = laravelRoute("export.download", {filename: data.filename, expireAt: data.expireAt, userId: data.userId, salt: data.salt, signature: data.signature});
                })).catch(this.$axiosErrorHandler).then(() => {
                    this.isLoading = false;
                });
            },
        },
    }
</script>

<style scoped>

</style>
