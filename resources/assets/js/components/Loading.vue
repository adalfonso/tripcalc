<template>
    <div :class="className"></div>
</template>

<script>
    export default {
        props: {
            stationary: { default: false }
        },

        computed: {
            className() {
                return this.stationary ? 'loading stationary' : 'loading';
            }
        }
    }
</script>
