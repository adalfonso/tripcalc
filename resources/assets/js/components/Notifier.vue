<template>
    <div v-if="count">
        <div class="notification-count">
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
