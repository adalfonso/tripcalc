<template>
    <div>
        <transaction-form v-if="transactionForm.visible" :trip_id="trip_id"
            :transaction_id="transactionForm.id" @hide="hideTransactionForm">
        </transaction-form>

        <div v-for="item in localFeed" class="activity-item clearfix">

            <!-- Transaction -->
            <div v-if="item.type == 'transaction'">
                <transaction :data="item" :trip_id="trip_id"
                    @showTransactionForm="showTransactionForm(id)"
                ></transaction>
            </div>

            <!-- Post -->
            <div v-else>
        		<post :data="item" :trip_id="trip_id"></post>
            </div>

    	</div>
    </div>
</template>

<script>

export default {

props: {
    feed: {},
    trip_id: null
},

data() {
    return {
        scrollTimeout : null,

        transactionForm: {
            id      : null,
            visible : false
        },

        localFeed: {}
    };
},

created() {
	bus.$on('showTransactionForm', (id = null) => {
		this.showTransactionForm(id);
	});

	bus.$on('closeModals', () => {
		this.hideTransactionForm();
	});

    window.addEventListener('scroll', this.scroll);

    this.localFeed = this.feed;
},

methods: {

    showTransactionForm(id = null) {
        this.transactionForm.visible = true;
        this.transactionForm.id = id;
    },

    hideTransactionForm() {
        this.transactionForm.id = null;
        this.transactionForm.visible = false;
    },

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
        axios.post(`/trip/${this.trip_id}/activities`, {
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
