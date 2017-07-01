<template>
    <div class="tile">
        <p v-if="spender.type === 'virtual'">{{ spender.name}} </p>
        <p v-else>{{ spender.first_name }}</p>
        <div class="progressbar" :style="{ width: width }">
        </div>
    </div>
</template>

<script>

export default {

    props: {
        spender: { required: true },
        range: { default: 0 }
    },

    computed: {
        width() {
            return Math.abs(this.spender.total / this.range) * 100 + '%';
        }
    }
}
</script>
