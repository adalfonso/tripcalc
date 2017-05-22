<template>
    <div class="popup-wrap" @click.self="hide">
        <div class="popup report distributionReport">
            <div class="popup-close" @click="hide">&times;</div>
            <h4 class="centered">Distribution Report</h4>
            <hr>
            <div class="header clearfix">
                <p>Gets Paid</p>
                <p>Pays</p>
            </div>

            <table>
                <tr class="axis">
                    <td :style="{ width: getsPaid }">
                        <div>${{ min() }}</div>
                        <div v-if="min() > max()" class="center">$0</div>
                    </td>
                    <td :style="{ width: paysWidth }" style="text-align: right">
                        <div v-if="max() >= min()" class="center">$0</div>
                        <div>${{ max() }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <spender-progress-bar v-for="spender in getsPaid()"
                            :spender="spender" :range="min()" class="left">
                        </spender-progress-bar>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <spender-progress-bar v-for="spender in pays()"
                            :spender="spender" :range="max()">
                        </spender-progress-bar>
                    </td>
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
            spenders: []
        };
    },

	created() {
		axios.get(`/trips/${ this.trip_id }/report/distribution`)
        .then(response => {
           this.spenders = response.data;
        });
	},

    computed: {

        paysWidth() {
            return this.max() / this.total() * 100 + '%';
        },

        getsPaidWidth() {
            return this.min() / this.total() * 100 + '%';
        }
    },

    methods: {

        graphScale(amount) {
            let breakpoints = [
                100, 250, 500, 1000, 2500, 5000, 10000, 25000, 50000, 100000, 1000000
            ];

            for (let i = 0; i < breakpoints.length; i++) {
                if (amount <= breakpoints[i]) {
                    return breakpoints[i] / 10;
                }
            };

            return 1000000;
        },

        min() {
            let min = Math.abs(
                this.spenders.reduce(function(carry, spender) {
                    return Number(spender.total) < carry ? Number(spender.total) : carry;
                }, 0)
            );

            let remainder = min % this.graphScale(min);

            return this.graphScale(min) - remainder + min;
        },

        max() {
            let max = Math.abs(
                this.spenders.reduce(function(carry, spender) {
                    return Number(spender.total) > carry ? Number(spender.total) : carry;
                }, 0)
            );

            let remainder = max % this.graphScale(max);;

            return this.graphScale(max) - remainder + max;
        },

        total() {
            return this.min() + this.max();
        },

        pays() {
            return this.spenders.filter(function(spender) {
                return spender.total > 0;
            });
        },

        getsPaid() {
            return this.spenders.filter(function(spender) {
                return spender.total <= 0;
            });
        },

        hide() {
            this.$emit('hide');
        }
    }
}
</script>
