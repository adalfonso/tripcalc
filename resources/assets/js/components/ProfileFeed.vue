<template>
    <div>
        <div v-for="item in localFeed" class="activity-item clearfix">
    		<post :data="item" :type="'profile'"></post>
    	</div>
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
        tracker: ''
    };
},

mounted() {
    this.tracker = new ScrollTracker(window, this.more);
},

methods: {
    more() {
        axios.post(`/profile/${this.id}/posts/fetch`, {
            oldestDate: this.localFeed[this.localFeed.length - 1].created_at

        }).then(response => {
            if (response.data.length === 0) {
                return;
            }

            this.localFeed = this.localFeed.concat(response.data);
        });
    }
}

}
</script>
