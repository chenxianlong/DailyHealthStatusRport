<template>
    <div>
        <page-title title="返校健康卡填报"/>
        <div style="margin: 8px 8px 8px 8px" v-loading="isLoading">
            <slide-fade-transition>
                <div v-if="todayReported" style="color: gray; text-align: center; font-size: 20px; padding-top: 80px;">
                    暂无需上报信息
                </div>
                <el-form v-else size="small" label-position="top"
                         v-on:submit.native.prevent="submitDailyHealthStatusForm">
                    <el-form-item label="日期" required>
                        <el-input :value="serverDate" readonly/>
                    </el-form-item>

                    <el-form-item label="姓名" required>
                        <el-input :value="name" readonly/>
                    </el-form-item>

                    <el-form-item :label="type === 0 ? '班级' : '部门'" required>
                        <el-input :value="department" readonly/>
                    </el-form-item>

                    <el-form-item label="联系电话" required>
                        <el-input v-model="healthCardForm.phone" type="tel"/>
                    </el-form-item>

                    <el-form-item label="现居住地址（填报当日居住地址，具体到门牌号）" required>
                        <el-input v-model="healthCardForm.address" type="textarea" maxlength="512" show-word-limit/>
                    </el-form-item>

                    <template v-if="type === 0">
                        <el-form-item label="学校的宿舍床位号（请具体到床位号，格式1A101-A，12-101-A）" required>
                            <el-input v-model="healthCardForm.dorm_room" type="text" maxlength="32"/>
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
                    </template>


                    <el-form-item label="目前，本人身体状况" required>
                        <el-radio-group v-model="form.self_status">
                            <el-radio class="radio" :label="0">健康，无症状</el-radio>
                            <el-radio class="radio" :label="1">发热（37.3度以上）</el-radio>
                            <el-radio class="radio" :label="2">咳嗽</el-radio>
                            <el-radio class="radio" :label="3">气促</el-radio>
                            <el-radio class="radio" :label="4">乏力</el-radio>
                            <el-radio class="radio" :label="5">其他症状</el-radio>
                        </el-radio-group>
                    </el-form-item>

                    <el-form-item v-if="form.self_status === 5" label="请描述本人症状" required>
                        <el-input v-model="form.self_status_details" type="textarea" maxlength="255" show-word-limit/>
                    </el-form-item>

                    <el-form-item label="同住家庭成员身体情况" required>
                        <el-radio-group v-model="form.family_status">
                            <el-radio class="radio" :label="0">健康，无症状</el-radio>
                            <el-radio class="radio" :label="1">发热（37.3度以上）</el-radio>
                            <el-radio class="radio" :label="2">咳嗽</el-radio>
                            <el-radio class="radio" :label="3">气促</el-radio>
                            <el-radio class="radio" :label="4">乏力</el-radio>
                            <el-radio class="radio" :label="5">其他症状</el-radio>
                        </el-radio-group>
                    </el-form-item>

                    <el-form-item v-if="form.family_status === 5" label="请描述同住家庭成员症状" required>
                        <el-input v-model="form.family_status_details" type="textarea" maxlength="255" show-word-limit/>
                    </el-form-item>

                    <template v-if="type === 1">
                        <el-form-item label="本人或同住家庭成员当日是否接触过确诊病例、疑是病例或无症状感染者" required>
                            <el-radio-group v-model="form.extra.today_touce_risk_people">
                                <el-radio :label="0">否</el-radio>
                                <el-radio :label="1">是</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item label="当日是否在校上班" required>
                            <el-radio-group v-model="form.extra.today_work_in_school">
                                <el-radio :label="0">否</el-radio>
                                <el-radio :label="1">是</el-radio>
                            </el-radio-group>
                        </el-form-item>
                    </template>

                    <el-form-item required>
                        <span style="color: red;">本人郑重承诺：以上情况属实，不存在任何隐瞒的情况。如有不实，本人愿意承担相关责任。</span>
                        <el-checkbox v-model="confirm2"><span style="color: red;">同意</span></el-checkbox>
                    </el-form-item>

                    <alert type="error" title="错误" :messages="errors"/>

                    <el-form-item>
                        <el-button style="width: 100%;" native-type="submit" type="primary"
                                   :disabled="isLoading || confirm2 === false">
                            <template v-if="confirm2">提交</template>
                            <template v-else>请同意“本人郑重承诺”</template>
                        </el-button>
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
                hasHealthCard: false,
                todayReported: false,
                name: "",
                department: "",
                type: null,
                form: {
                    extra: {},
                },
                healthCardForm: {},
                confirm1: false,
                confirm2: false,
                statusCodeMap: {
                    1: "发热（37.3度以上）",
                    2: "咳嗽",
                    3: "气促",
                    4: "乏力",
                },
            };
        },
        created: function () {
            this.isLoading = true;
            axios.get("/status").then(this.$apiResponseHandler((data) => {
                this.name = data.name;
                this.department = data.department;
                this.type = data.type;
                this.todayReported = data.todayReported;
                if (data.hasHealthCard) {
                    this.$set(this.healthCardForm, "phone", data.hasHealthCard.phone);
                    this.$set(this.healthCardForm, "address", data.hasHealthCard.address);
                    this.$set(this.healthCardForm, "dorm_room", data.hasHealthCard.dorm_room);
                    if (data.hasHealthCard.in_key_places_from) {
                        this.$set(this.healthCardForm, "stayed_in_key_places", 1);
                        this.$set(this.healthCardForm, "in_key_places_from", data.hasHealthCard.in_key_places_from.substr(0, 10));
                    } else {
                        this.$set(this.healthCardForm, "stayed_in_key_places", 0);
                    }
                    if (data.hasHealthCard.in_key_places_to) {
                        this.$set(this.healthCardForm, "in_key_places_to", data.hasHealthCard.in_key_places_to.substr(0, 10));
                    }
                    if (data.hasHealthCard.back_to_dongguan_at) {
                        this.$set(this.healthCardForm, "back_to_dongguan_at", data.hasHealthCard.back_to_dongguan_at.substr(0, 10));
                    }
                    if (data.hasHealthCard.touched_high_risk_people_at) {
                        this.$set(this.healthCardForm, "touched_high_risk_people", 1);
                        this.$set(this.healthCardForm, "touched_high_risk_people_at", data.hasHealthCard.touched_high_risk_people_at.substr(0, 10));
                    } else {
                        this.$set(this.healthCardForm, "touched_high_risk_people", 0);
                    }
                }
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
                    let status = _.cloneDeep(this.form);
                    if (status.self_status > 0 && status.self_status < 5) {
                        status.self_status_details = this.statusCodeMap[status.self_status];
                        status.self_status = 1;
                    }
                    if (status.family_status > 0 && status.family_status < 5) {
                        status.family_status_details = this.statusCodeMap[status.family_status];
                        status.family_status = 1;
                    }
                    axios.post("/healthStatus/daily", {
                        card: this.healthCardForm,
                        status: status,
                    }).then(this.$apiResponseHandler((data) => {
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
    .radio {
        margin-bottom: 10px;
        display: block;
    }
</style>
