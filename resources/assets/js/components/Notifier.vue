<template>
    <div>
        <div class="notification-count" v-if="count"
            @click.prevent="visible = !visible">
            {{ count }}

            <requests></requests>
        </div>



    </div>
</template>

<script>

import Collection from '../lib/Collection.js';

export default {

    data() {
        return {
            count: 0,
            requests: {},
            visible: false
        };
    },

    created() {
        axios.get('/user/requests')
        .then(response => {
            this.requests = response.data.requests;
            this.count = response.data.count;
        });
    }
}
</script>
