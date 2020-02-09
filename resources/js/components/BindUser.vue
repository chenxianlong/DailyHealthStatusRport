<template>
    <div>
        <page-title title="绑定身份证"/>

        <el-form style="margin: 8px 8px 8px 8px;" size="small" label-position="top" v-on:submit.native.prevent="onSubmit" v-loading="isLoading">
            <el-form-item label="身份证号码">
                <el-input v-model="id_card_no"/>
            </el-form-item>

            <div style="color: red;">身份证绑定后不可更改，请认真填写</div>

            <br>

            <el-form-item>
                <el-button style="width: 100%;" native-type="submit" type="primary" :disabled="isLoading">绑定</el-button>
            </el-form-item>
        </el-form>
    </div>
</template>

<script>
    import PageTitle from "./PageTitle";
    export default {
        name: "BindUser",
        components: {PageTitle},
        data: function () {
            return {
                isLoading: false,
                id_card_no: "",
            };
        },
        methods: {
            onSubmit: function () {
                this.isLoading = true;
                axios.post(laravelRoute("users.bind"), {idCardNo: this.id_card_no}).then((response) => {
                    let data = response.data;
                    if (data.result) {
                        if (confirm("姓名：" + data.data.name)) {
                            axios.post(laravelRoute("users.bind"), {idCardNo: this.id_card_no, name: data.data.name}).then((response) => {
                                let data = response.data;
                                if (data.result) {
                                    this.$message({
                                        message: '身份证绑定成功',
                                        type: 'success'
                                    });
                                    this.$router.push("/healthStatus/daily");
                                } else {
                                    this.$message.error(data.message);
                                }
                            }).catch((error) => {
                                this.$message.error(error.toString());
                            }).then(() => {
                                this.isLoading = false;
                            });
                        } else {
                            this.isLoading = false;
                        }
                    } else {
                        this.$message.error(data.message);
                        this.isLoading = false;
                    }
                }).catch((error) => {
                    this.$message.error(error.toString());
                    this.isLoading = false;
                }).then(() => {
                });
            }
        },
    }
</script>

<style scoped>

</style>
