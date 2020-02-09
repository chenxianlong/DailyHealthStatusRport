<template>
    <div class="router-view">
        <slide-fade-transition>
            <router-view v-bind="$attrs"/>
        </slide-fade-transition>
    </div>
</template>

<script>
    import SlideFadeTransition from "./SlideFadeTransition";
    export default {
        name: "transition-router-view",
        components: {SlideFadeTransition},
        inheritAttrs: false,
    }
</script>

<style scoped>
    .slide-fade-enter-active {
        transition: all .15s ease;
    }

    .slide-fade-leave-active {
        transition: all .15s cubic-bezier(1.0, 0.5, 0.8, 1.0);
    }

    .slide-fade-enter, .slide-fade-leave-to {
        transform: translateX(10px);
        opacity: 0;
    }
</style>
