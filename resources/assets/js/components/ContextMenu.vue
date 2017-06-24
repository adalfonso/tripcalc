<template>
    <div class="menu clearfix float-left">
        <div class="trigger">
            <div class="caret">&#9660;</div>
        </div>
        <ul class="body">
            <div class="arw-up"></div>
            <div class="invis"></div>
            <li v-for="item in items" @click="handle(item)">
                {{ item.display }}
            </li>
        </ul>
    </div>
</template>

<script>
export default {

    props: {
        items: { required: true }
    },

    methods: {
        handle(item) {
            if (item.hasOwnProperty('emit')) {
                return this.$emit(item.emit);
            }

            if (item.hasOwnProperty('href')) {
                return window.location = item.href;
            }
        }
    }
}
</script>
