<template>
    <div>
        <div v-for="item in localFeed" class="activity-item clearfix">
    		<post :data="item" :type="'profile'"></post>
    	</div>
        <loading v-if="busy"></loading>
    </div>
</template>

<script>

import ScrollTracker from '../lib/ScrollTracker.js';

export default {

props: {
    feed: { default: {} },
    id: { required: true }
},

data() {
    return {
        localFeed: this.feed,
        tracker: null,
        busy: false
    };
},

mounted() {
    this.tracker = new ScrollTracker(document.querySelector('#app'), this.more);
},

methods: {
    more() {
        this.busy = true;
        axios.post(`/profile/${this.id}/posts/fetch`, {
            oldestDate: this.localFeed[this.localFeed.length - 1].created_at

        }).then(response => {
            this.busy = false;
            if (response.data.length === 0) {
                return;
            }

            this.localFeed = this.localFeed.concat(response.data);
        })
        .catch(error => {
            this.busy = false;
        });
    }
}

}
</script>
