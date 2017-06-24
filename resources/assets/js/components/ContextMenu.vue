<template>
    <div class="menu clearfix float-left">
        <div class="trigger">
            <div class="caret">&#9660;</div>
        </div>
        <ul class="body">
            <div class="arw-up"></div>
            <div class="invis"></div>
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
