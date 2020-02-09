<template>
    <div>
        <div style="font-size: 18px; text-align: center; color: #323233; padding-top: 15px;">{{ title }}</div>
        <el-divider/>
    </div>
</template>

<script>
    export default {
        name: "PageTitle",
        props: ["title"],
    }
</script>

<style scoped>

</style>
