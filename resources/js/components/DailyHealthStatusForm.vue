<template>
    <div>
        <page-title title="填写"/>
        <div style="margin: 8px 8px 8px 8px" v-loading="isLoading">
            <slide-fade-transition>
                <div v-if="todayReported" style="color: gray; text-align: center; font-size: 20px; padding-top: 80px;">
                    暂无需上报信息
                </div>
                <el-form v-else-if="hasHealthCard" size="small" label-position="top"
                         v-on:submit.native.prevent="submitDailyHealthStatusForm">
                    <el-form-item label="日期" required>
                        <el-input :value="serverDate" readonly/>
                    </el-form-item>

                    <el-form-item label="姓名" required>
                        <el-input :value="name" readonly/>
                    </el-form-item>

                    <el-form-item label="自身健康状况" required>
                        <el-radio-group v-model="form.self_status">
                            <el-radio :label="0">正常</el-radio>
                            <el-radio :label="1">异常</el-radio>
                        </el-radio-group>
                    </el-form-item>

                    <el-form-item v-if="form.self_status === 1" label="自身异常症状" required>
                        <el-input v-model="form.self_status_details" type="textarea" maxlength="255" show-word-limit/>
                    </el-form-item>

                    <el-form-item label="家庭成员健康状况" required>
                        <el-radio-group v-model="form.family_status">
                            <el-radio :label="0">正常</el-radio>
                            <el-radio :label="1">异常</el-radio>
                        </el-radio-group>
                    </el-form-item>

                    <el-form-item v-if="form.family_status === 1" label="家庭成员异常症状" required>
                        <el-input v-model="form.family_status_details" type="textarea" maxlength="255" show-word-limit/>
                    </el-form-item>

                    <div style="color: red;">资料提交后无法修改，请谨慎填写！</div>
                    <br>

                    <alert type="error" title="错误" :messages="errors"/>

                    <el-form-item>
                        <el-button style="width: 100%;" native-type="submit" type="primary" :disabled="isLoading">提交
                        </el-button>
                    </el-form-item>
                </el-form>
                <el-form v-else size="small" label-position="top" v-on:submit.native.prevent="submitHealthCardForm">
                    <el-form-item label="姓名" required>
                        <el-input :value="name" readonly/>
                    </el-form-item>

                    <el-form-item label="联系电话" required>
                        <el-input v-model="healthCardForm.phone" type="tel"/>
                    </el-form-item>

                    <el-form-item label="住址" required>
                        <el-input v-model="healthCardForm.address" type="textarea" maxlength="512" show-word-limit/>
                    </el-form-item>

                    <el-form-item label="假期是否曾前往疫情防控重点地区" required>
                        <el-radio-group v-model="healthCardForm.stayed_in_key_places">
                            <el-radio :label="1">是</el-radio>
                            <el-radio :label="0">否</el-radio>
                        </el-radio-group>
                    </el-form-item>

                    <template v-if="healthCardForm.stayed_in_key_places === 1">
                        <el-form-item label="前往时间" required>
                            <el-date-picker
                                v-model="healthCardForm.in_key_places_from"
                                type="date"
                                placeholder="选择日期"
                                value-format="yyyy-MM-dd"
                            clearable>
                            </el-date-picker>
                        </el-form-item>

                        <el-form-item label="离开时间">
                            <el-date-picker
                                v-model="healthCardForm.in_key_places_to"
                                type="date"
                                placeholder="选择日期"
                                value-format="yyyy-MM-dd"
                                clearable>
                            </el-date-picker>
                        </el-form-item>

                        <el-form-item label="返莞时间">
                            <el-date-picker
                                v-model="healthCardForm.back_to_dongguan_at"
                                type="date"
                                placeholder="选择日期"
                                value-format="yyyy-MM-dd"
                                clearable>
                            </el-date-picker>
                        </el-form-item>
                    </template>

                    <el-form-item label="是否接触过疫情防控重点地区高危人员" required>
                        <el-radio-group v-model="healthCardForm.touched_high_risk_people">
                            <el-radio :label="1">是</el-radio>
                            <el-radio :label="0">否</el-radio>
                        </el-radio-group>
                    </el-form-item>

                    <el-form-item v-if="healthCardForm.touched_high_risk_people === 1" label="接触时间" required>
                        <el-date-picker
                            v-model="healthCardForm.touched_high_risk_people_at"
                            type="date"
                            placeholder="选择日期"
                            value-format="yyyy-MM-dd"
                            clearable>
                        </el-date-picker>
                    </el-form-item>

                    <div style="color: red;">资料提交后无法修改，请谨慎填写！</div>
                    <br>

                    <alert type="error" title="错误" :messages="errors"/>

                    <el-form-item>
                        <el-button native-type="submit" type="primary" style="width: 100%;" :disabled="isLoading">提交</el-button>
                    </el-form-item>
                </el-form>
            </slide-fade-transition>
        </div>
    </div>
</template>

<script>
    import PageTitle from "./PageTitle";
    import SlideFadeTransition from "./SlideFadeTransition";
    import Alert from "./Alert";

    export default {
        name: "DailyHealthStatusForm",
        components: {Alert, SlideFadeTransition, PageTitle},
        data: function () {
            return {
                isLoading: false,
                errors: [],
                hasHealthCard: true,
                todayReported: false,
                name: "",
                form: {},
                healthCardForm: {},
            };
        },
        created: function () {
            this.isLoading = true;
            axios.get("/status").then(this.$apiResponseHandler((data) => {
                this.name = data.name;
                this.hasHealthCard = data.hasHealthCard;
                this.todayReported = data.todayReported;
            })).catch((error) => {
                this.$axiosErrorHandler(error, this, null);
            }).then(() => {
                this.isLoading = false;
            })
        },
        methods: {
            submitDailyHealthStatusForm: function () {
                if (confirm("确定填写无误并提交？")) {
                    this.isLoading = true;
                    this.errors = [];
                    axios.post("/healthStatus/daily", this.form).then(this.$apiResponseHandler((data) => {
                        this.errors = [];
                        this.todayReported = true;
                        this.$successMessage("提交成功");
                    })).catch(this.$axiosErrorHandler).then(() => {
                        this.isLoading = false;
                    })
                }
            },
            submitHealthCardForm: function () {
                if (confirm("确定填写无误并提交？")) {
                    this.isLoading = true;
                    this.errors = [];
                    axios.post("/healthCard", this.healthCardForm).then(this.$apiResponseHandler((data) => {
                        this.hasHealthCard = true;
                        this.errors = [];
                        this.$successMessage("提交成功，请填写健康状况信息");
                    })).catch(this.$axiosErrorHandler).then(() => {
                        this.isLoading = false;
                    })
                }
            },
        },
        computed: {
            serverDate: function () {
                return window.serverDate;
            }
        },
    }
</script>

<style scoped>

</style>
