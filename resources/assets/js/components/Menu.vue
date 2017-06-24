<template>
    <div>
        <div>&#9662;</div>
        <ul>
            <li v-for="item in items">{{ item.display }}</li>
        </ul>
    </div>
</template>

<script>
export default {

    props: {
        items: { required: true }
    },

    data() {
        return {};
    },

    created() {

    },

    computed: {

    },

    methods: {

    }
}
</script>
