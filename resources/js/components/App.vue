<template>
    <div id="app">
        <transition-router-view/>
    </div>
</template>

<script>
    import TransitionRouterView from "./TransitionRouterView";
    export default {
        name: "App",
        components: {TransitionRouterView}
    }
</script>

<style scoped>
</style>
