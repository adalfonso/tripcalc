<template>
    <div class="popup-wrap" @click.self="hide">
        <div class="dialogue popup report detailedReport">
            <div class="popup-close" @click="hide">&times;</div>
            <h4 class="centered form-header">Detailed Report</h4>
            <hr>

            <div class="graph">
                <table>
                    <tr>
                        <th>Date</th>
                        <th v-if="multiUser">Paid By</th>
                        <th class="align-right">Amount</th>
                        <th v-if="multiUser" class="align-right">Net</th>
                    </tr>
                    <tr v-for="transaction in transactions">
                        <td>{{ transaction.date }}</td>
                        <td v-if="multiUser">{{ transaction.creator }}</td>
                        <td class="align-right">{{ transaction.amount }}</td>
                        <td v-if="multiUser" class="align-right">{{ currency(transaction.net) }}</td>
                    </tr>
                    <tr class="total" v-if="multiUser">
                        <td><strong>{{ balanceVerbiage }}</strong></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td class="align-right">{{ fixedLength(Math.abs(balance)) }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</template>

<script>

export default {

    props: {
        trip_id: { default: null }
    },

    data() {
        return {
            transactions: [],
            multiUser: false
        };
    },

	created() {
		axios.get(`/trips/${ this.trip_id }/report/detailed`)
        .then(response => {
           this.transactions = response.data.transactions;
           this.multiUser = response.data.multiUser;
        });
	},

    computed: {

        balance() {
            let total = this.transactions.reduce(function(carry, transaction){
                return carry + Number(transaction.net);
            }, 0);

            return this.fixedLength(total);
        },

        balanceVerbiage() {
            return this.balance > 0 ? 'You are owed' : 'You owe';
        }
    },

    methods: {
        currency(amount) {
            if (parseInt(amount) === 0) {
                return '';

            } else if (amount < 0 ) {
                return this.fixedLength(amount);
            }

            return this.fixedLength(amount);
        },

        hide() {
            this.$emit('hide');
        },

        fixedLength(amount) {
            return Number(amount).toFixed(2)
        }
    }
}
</script>
