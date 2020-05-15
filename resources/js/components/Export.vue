<template>
    <div>
        <el-form style="margin: 8px 8px 8px 8px;" v-if="authenticated" label-width="80px" size="small" v-on:submit.native.prevent="() => {}" v-loading="isLoading">
            <template v-if="allowExportTeachers === 1 || allowExportTeachers === 2 || allowExportStudents === 1 || allowExportStudents === 2">
                <el-divider>导出已填人员健康报告</el-divider>
                <el-form-item label="人员类型" required>
                    <el-radio-group v-model="exportReportedType">
                        <el-radio v-if="allowExportTeachers === 1 || allowExportTeachers === 2" :label="1">教职工</el-radio>
                        <el-radio v-if="allowExportStudents === 1 || allowExportStudents === 2" :label="0">学生</el-radio>
                    </el-radio-group>
                </el-form-item>

                <el-form-item v-if="false && exportReportedType === 0" label="班级">
                    <el-select v-model="selectedExportClasses" filterable multiple clearable size="small" placeholder="留空导出全部可访问班级">
                        <el-option v-for="availableClass in availableClasses" :key="availableClass" :label="availableClass" :value="availableClass"/>
                    </el-select>
                </el-form-item>

                <el-form-item label="从">
                    <el-date-picker
                        v-model="startAt"
                        type="date"
                        placeholder="选择日期"
                        value-format="yyyy-MM-dd"
                        :editable="false"
                        :picker-options="{
                        disabledDate: disabledDate,
                    }">
                    </el-date-picker>
                </el-form-item>

                <el-form-item label="到">
                    <el-date-picker
                        v-model="endAt"
                        type="date"
                        placeholder="选择日期"
                        value-format="yyyy-MM-dd"
                        :editable="false"
                        :picker-options="{
                        disabledDate: disabledDate,
                    }">
                    </el-date-picker>
                </el-form-item>

                <el-form-item>
                    <el-button type="primary" v-on:click="exportAll">导出报告</el-button>
                </el-form-item>

                <el-divider>导出未填人员名单</el-divider>
            </template>

            <el-form-item label="人员类型" required>
                <el-radio-group v-model="exportNotReportedType">
                    <el-radio v-if="allowExportTeachers !== 0" :label="1">教职工</el-radio>
                    <el-radio v-if="allowExportStudents !== 0" :label="0">学生</el-radio>
                </el-radio-group>
            </el-form-item>

            <el-form-item v-if="false && exportNotReportedType === 0" label="班级">
                <el-select v-model="selectedExportNotReportedClasses" filterable multiple clearable size="small" placeholder="留空导出全部可访问班级">
                    <el-option v-for="availableClass in availableClasses" :key="availableClass" :label="availableClass" :value="availableClass"/>
                </el-select>
            </el-form-item>

            <el-form-item label="日期" required>
                <el-date-picker
                    v-model="date"
                    type="date"
                    placeholder="选择日期"
                    value-format="yyyy-MM-dd"
                    :editable="false"
                    :picker-options="{
                        disabledDate: disabledDate,
                    }">
                </el-date-picker>
            </el-form-item>

            <el-form-item>
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
                startAt: null,
                endAt: null,
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
                if (this.allowExportTeachers === 1 || this.allowExportTeachers === 2) {
                    this.exportReportedType = 1;
                    this.exportNotReportedType = 1;
                } else {
                    this.exportReportedType = 0;
                    this.exportNotReportedType = 0;
                }
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
                axios.post("/export/all", {type: this.exportReportedType, selectedClasses: this.selectedExportClasses, startAt: this.startAt, endAt: this.endAt}).then(this.$apiResponseHandler((data) => {
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
