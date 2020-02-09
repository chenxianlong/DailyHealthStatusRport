<template>
    <div>
        <page-title title="填写"/>

        <div v-if="notRequired2Fill">
            <div style="color: gray; text-align: center; font-size: 20px; padding-top: 80px;">
                暂无需上报信息
            </div>
        </div>
        <el-form v-else style="margin: 8px 8px 8px 8px;" ref="form" :model="form" size="small" label-position="top" v-on:submit.native.prevent="onSubmit" v-loading="isLoading">
            <el-form-item label="姓名" required>
                <el-input :value="name" readonly/>
            </el-form-item>

            <el-form-item label="籍贯（境内：省份+地市；境外：香港/澳门/台湾/国家名）" required>
                <el-input v-model="form.native_place" placeholder="" maxlength="32"/>
            </el-form-item>

            <el-form-item label="户籍地址（境内：省份+地市；境外：香港/澳门/台湾/国家名）" required>
                <el-input type="textarea" v-model="form.permanent_place" placeholder="" maxlength="512"/>
            </el-form-item>

            <el-form-item label="家庭住址（居住地在莞：东莞+镇街；居住地不在莞：省份+地市，或香港/澳门/台湾/国家名）" required>
                <el-input type="textarea" v-model="form.address" placeholder=""  maxlength="512"/>
            </el-form-item>

            <el-form-item label="目前所在区域（目前在莞：东莞+镇街；目前不在莞：省份+地市，或香港/澳门/台湾/国家名）" required>
                <el-input type="text" v-model="form.current_place" placeholder="" maxlength="32"/>
            </el-form-item>

            <el-form-item label="最近14天是否曾到过湖北" required>
                <el-radio-group v-model="form.from_hb_in_14">
                    <el-radio label="1">是</el-radio>
                    <el-radio label="0">否</el-radio>
                </el-radio-group>
            </el-form-item>

            <el-form-item label="手机号码" required>
                <el-input type="tel" v-model="form.phone"/>
            </el-form-item>

            <el-form-item label="紧急联系人" required>
                <el-input v-model="form.emergency_contact"/>
            </el-form-item>

            <el-form-item label="紧急联系人电话" required>
                <el-input type="tel" v-model="form.emergency_contact_phone"/>
            </el-form-item>

            <el-form-item label="目前身体健康状况" required>
                <el-input v-model="form.current_health_status" placeholder="良好"/>
            </el-form-item>

            <el-form-item label="最近14天是否接触过“近14日出入过湖北地区的人员”" required>
                <el-radio-group v-model="form.touched_from_hb_in14">
                    <el-radio label="1">是</el-radio>
                    <el-radio label="0">否</el-radio>
                </el-radio-group>
            </el-form-item>

            <el-form-item label="最近14天是否接触过疑似病例/确诊病例”" required>
                <el-radio-group v-model="form.touched_suspected">
                    <el-radio label="1">是</el-radio>
                    <el-radio label="0">否</el-radio>
                </el-radio-group>
            </el-form-item>

            <el-form-item label="最近14天内离莞出行情况(没有离莞，填“留莞”，离莞请填“起止日期和驻留地点)" required>
                <el-input v-model="form.recently_leave_dg"/>
            </el-form-item>

            <el-form-item label="有无乘坐长途公共交通工具" required>
                <el-radio-group v-model="form.by_long_distance_transport">
                    <el-radio label="1">是</el-radio>
                    <el-radio label="0">否</el-radio>
                </el-radio-group>
            </el-form-item>

            <el-form-item label="是否为重点人群" required>
                <el-radio-group v-model="form.is_key_people">
                    <el-radio label="1">是</el-radio>
                    <el-radio label="0">否</el-radio>
                </el-radio-group>
            </el-form-item>

            <el-form-item label="同住家庭成员有无重点人群" required>
                <el-radio-group v-model="form.is_live_key_people">
                    <el-radio label="1">是</el-radio>
                    <el-radio label="0">否</el-radio>
                </el-radio-group>
            </el-form-item>

            <el-form-item label="备注">
                <el-input type="textarea" v-model="form.remark" maxlength="512" show-word-limit/>
            </el-form-item>

            <div style="color: red;">资料提交后无法修改，请谨慎填写！</div>

            <br>

            <alert type="error" title="错误" :messages="errors"/>

            <el-form-item>
                <el-button native-type="submit" type="primary" style="width: 100%" :disabled="isLoading">提交</el-button>
            </el-form-item>
        </el-form>
    </div>
</template>

<script>
    import PageTitle from "./PageTitle";
    import Alert from "./Alert";
    export default {
        name: "HealthReportForm",
        components: {Alert, PageTitle},
        data: function () {
            return {
                isLoading: false,
                notRequired2Fill: false,
                errors: [],
                name: "",
                form: {
                }
            }
        },
        created: function () {
            this.isLoading = false;
            axios.get("/status").then((response) => {
                this.notRequired2Fill = response.data.data.reported;
                this.name = response.data.data.name;
                if (response.data.data.latestReport) {
                    delete response.data.data.latestReport.from_hb_in_14;
                    delete response.data.data.latestReport.touched_from_hb_in14;
                    delete response.data.data.latestReport.touched_suspected;
                    delete response.data.data.latestReport.by_long_distance_transport;
                    delete response.data.data.latestReport.is_key_people;
                    delete response.data.data.latestReport.is_live_key_people;
                    delete response.data.data.latestReport.recently_leave_dg;
                    delete response.data.data.latestReport.current_place;
                    delete response.data.data.latestReport.current_health_status;
                    this.form = response.data.data.latestReport;
                }
            }).catch((error) => {
                this.$message.error(error.toString());
            }).then(() => {
                this.isLoading = false;
            })
        },
        methods: {
            onSubmit() {
                this.$confirm("确定填写无误并提交？").then(() => {
                    this.isLoading = true;
                    this.errors = [];
                    axios.post("/healthReport", this.form).then(this.$apiResponseHandler((data) => {
                        this.notRequired2Fill = true;
                        this.$successMessage("上报成功");
                    })).catch(this.$axiosErrorHandler).then(() => {
                        this.isLoading = false;
                    })
                }).catch(() => {});
            }
        },
    }
</script>

<style scoped>

</style>
