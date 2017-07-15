<template>
    <div v-if="count" @click.prevent>
        <div class="notification-count" @click="showRequests"
            @mouseover="removeTopOffset">
            {{ count }}
        </div>

        <requests :data="requests" @refresh="refresh"></requests>
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
            this.count = response.data.count;
            this.requests = response.data.requests;
        });
    },

    methods: {
        refresh(response) {
            this.count = response.count;
            this.requests = response.requests;
        },

        showRequests() {
            if (!this.isMobile()) {
                return;
            }

            let elem = document.getElementById('request-menu');
            let link = elem.parentElement.parentElement.parentElement;
            let top = link.clientHeight + link.offsetTop;

            elem.classList.toggle('displayInNav');
            elem.style.top = top + 'px';
        },

        isMobile() {
            return window.getComputedStyle(
               document.querySelector('.hamburger')
            ).display === 'block';
        }
    }
}
</script>
