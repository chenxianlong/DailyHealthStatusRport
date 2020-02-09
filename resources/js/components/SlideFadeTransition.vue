<template>
    <transition name="slide-fade" mode="out-in" :appear="true">
        <slot/>
    </transition>
</template>

<script>
    export default {
        name: "SlideFadeTransition"
    }
</script>

<style>
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
