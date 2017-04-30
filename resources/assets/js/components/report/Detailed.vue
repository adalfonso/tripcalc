<template>
    <div class="dialogue popup report detailedReport">
        <h4 class="centered form-header">Detailed Report</h4>

        <hr>

        <img src="/img/icon/closePopup.png" class="closePopup"
            @click="close">

        <div class="graph">
            <table>
                <tr>
                    <th>Date</th>
                    <th v-if="multiUser">Paid By</th>
                    <th class="align-right">Amount</th>
                    <th v-if="multiUser">Net</th>
                </tr>
                <tr v-for="transaction in transactions">
                    <td>{{ transaction.date }}</td>
                    <td v-if="multiUser">{{ transaction.creator }}</td>
                    <td class="align-right">${{ transaction.amount }}</td>
                    <td v-if="multiUser">{{ currency(transaction.net) }}</td>
                </tr>
                <tr class="total" v-if="multiUser">
                    <td><strong>{{ balanceVerbiage }}</strong></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>${{ fixedLength(Math.abs(balance)) }}</td>
                </tr>
                <tr class="total">
                    <td><strong>Personal total</strong></td>
                    <td v-if="multiUser">&nbsp;</td>
                    <td v-if="multiUser">&nbsp;</td>
                    <td class="align-right">${{ total }}</td>
                </tr>
            </table>
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
        total() {
            let total = this.transactions.filter(function(transaction) {
                return transaction.creator === 'You';

            }).reduce(function(carry, transaction) {
                return carry += Number(transaction.amount);
            }, 0);

            return Number(total - this.balance).toFixed(2);
        },

        balance() {
            let total = this.transactions.reduce(function(carry, transaction){
                return carry + Number(transaction.net);
            }, 0);

            return this.fixedLength(total);
        },

        balanceVerbiage() {
            return this.owedTotal > 0 ? 'You owe' : 'You are owed';
        }
    },

    methods: {
        currency(amount) {
            if (parseInt(amount) === 0) {
                return '';

            } else if (amount < 0 ) {
                return '-$' +  this.fixedLength(Math.abs(amount));
            }

            return '$' + this.fixedLength(amount);
        },

        close() {
            this.$emit('close');
        },

        fixedLength(amount) {
            return Number(amount).toFixed(2)
        }
    }
}
</script>
