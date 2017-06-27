<template>
    <div class="menu clearfix float-left">
        <div class="trigger">
            <div class="caret">&#9660;</div>
        </div>
        <ul class="body">
            <div class="arw-up"></div>
            <div class="invis"></div>
            <li v-for="item in localItems.where('active', true).data"
            @click="handle(item)">
                {{ item.display }}
            </li>
        </ul>
    </div>
</template>

<script>

import Collection from '../lib/Collection.js';

export default {

    props: {
        items: { required: true }
    },

    data() {
        return {
            localItems: new Collection(this.items)
        };
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
