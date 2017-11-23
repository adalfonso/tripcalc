<template>
    <div>
        <transaction-form v-if="transactionForm.visible" :trip_id="trip_id"
            :transaction_id="transactionForm.id" :active="active"
            @hide="hideTransactionForm">
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
        		<post :data="item" :type="'trip'"></post>
            </div>

    	</div>
    </div>
</template>

<script>

import ScrollTracker from '../lib/ScrollTracker.js';

export default {

props: {
    feed: { default: {} },
    trip_id: { required: true },
    active: { required: true }
},

data() {
    return {
        transactionForm: {
            id      : null,
            visible : false
        },

        localFeed: this.feed,
        tracker: ''
    };
},

created() {
	bus.$on('showTransactionForm', (id = null) => {
		this.showTransactionForm(id);
	});

	bus.$on('closeModals', () => {
		this.hideTransactionForm();
	});
},

mounted() {
    this.tracker = new ScrollTracker(window, this.more.bind(this));
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

    more() {
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
