<template>
    <div class="dialogue popup report">
        <h4 class="centered form-header">Progress Report</h4>

        <hr>

        <img src="/img/icon/closePopup.png" class="closePopup"
            @click="close">

        <div class="header clearfix">
            <p>Gets Paid</p>
            <p>Pays</p>
        </div>

        <div class="graph">
            <table>
                <tr class="axis">
                    <td :style="{ width: getsPaid }">
                        <div>${{ min() }}</div>
                        <div v-if="min() > max()" class="center">$0</div>
                    </td>
                    <td :style="{ width: paysWidth }" style="text-align: right">
                        <div v-if="max() >= min()" class="center">$0</div>
                        <div class="pays">${{ max() }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <spender-progress-bar v-for="spender in getsPaid()"
                            :spender="spender" :range="min()" class="tile left">
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
		axios.get(`/trips/${ this.trip_id }/report`)
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

        min() {
            let min = Math.abs(
                this.spenders.reduce(function(carry, spender) {
                    return spender.total < carry ? spender.total : carry;
                }, 0)
            );

            let remainder = min % 50;

            return 50 - remainder + min;
        },

        max() {
            let max = Math.abs(
                this.spenders.reduce(function(carry, spender){
                    return spender.total > carry ? spender.total : carry;
                }, 0)
            );

            let remainder = max % 50;

            return 50 - remainder + max;
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

        close() {
            this.$emit('close');
        }
    }
}
</script>
