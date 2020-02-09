<template>
    <el-alert v-if="Object.keys(messages).length > 0" :type="type" :title="title" :closable="false">
        <ul>
            <li v-for="message in expandedMessages">{{ message }}</li>
        </ul>
    </el-alert>
</template>

<script>
    export default {
        name: "Alert",
        props: ["type", "title", "messages"],
        methods: {
            expand: function (iterables2Expand) {
                let list = [];
                if (typeof iterables2Expand === "object" || Array.isArray(iterables2Expand)) {
                    for (let i in iterables2Expand) {
                        let iterable2Expand = iterables2Expand[i];
                        let expandResult = this.expand(iterable2Expand);
                        list = list.concat(expandResult);
                    }
                } else {
                    list.push(iterables2Expand)
                }
                return list;
            },
        },
        computed: {
            expandedMessages: function () {
                return this.expand(this.messages);
            }
        },
    }
</script>

<style scoped>
    .el-alert {
        margin: 15px 0 15px 0;
        line-height: initial;
    }

    ul {
        padding-left: 15px;
        margin: 0 0 0 0;
    }
</style>
