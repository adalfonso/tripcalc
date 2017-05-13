<template>
    <div>
        <transaction-form v-if="transactionForm.visible" :trip_id="trip_id"
            :transaction_id="transactionForm.id" @hide="hideTransactionForm">
        </transaction-form>

        <div v-for="item in feed" class="activity-item clearfix">

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
        }
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
            this.emitScroll();
        }, 400);
    },

    emitScroll() {
        let element = document.querySelector('body');

        let scrollAmount = element.scrollTop;
        let maximumScroll = element.scrollHeight - element.clientHeight;

        if (scrollAmount / maximumScroll === 1) {
            this.$emit('scrollBottom');
        }
    },

    onSubmit() {

    }
}

}
</script>
