<template>
    <div>
        <div v-for="item in localFeed" class="activity-item clearfix">
    		<post :data="item" :id="id" :type="'profile'" :is-owner="isOwner"></post>
    	</div>
    </div>
</template>

<script>

export default {

props: {
    feed: { default: {} },
    id: { required: true },
    isOwner : { default: 0 }
},

data() {
    return {
        scrollTimeout : null,

        localFeed: {}
    };
},

created() {
    window.addEventListener('scroll', this.scroll);

    this.localFeed = this.feed;
},

methods: {
    scroll() {
        clearInterval(this.scrollTimeout);

        this.scrollTimeout = setTimeout(() => {
            this.checkPagePosition();
        }, 400);
    },

    checkPagePosition() {
        let element = document.querySelector('body');
        let scrollAmount = element.scrollTop;
        let maximumScroll = element.scrollHeight - element.clientHeight;

        if (scrollAmount / maximumScroll >= .95) {
            this.growActivityFeed();
        }
    },

    growActivityFeed() {
        axios.post(`/profile/${this.id}/posts/fetch`, {
            oldestDate: this.localFeed[this.localFeed.length - 1].created_at.date

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
