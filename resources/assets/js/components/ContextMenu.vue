<template>
    <div class="menu-wrap advanced-settings">

        <div class="trigger">
            <div class="caret">&#9660;</div>
        </div>

        <div class="menu">

            <div class="arw-up-right">
                <div></div>
            </div>

            <div class="invis"></div>

            <div class="body">
                <ul>
                    <li v-for="item in localItems.where('active', true).data"
                    @click="handle(item)">
                        {{ item.display }}
                    </li>
                </ul>
            </div>

        </div>
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
