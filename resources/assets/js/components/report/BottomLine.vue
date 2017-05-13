<template>
    <div class="popup-wrap" @click.self="hide">
        <div class="dialogue popup report">
            <div class="popup-close" @click="hide">&times;</div>
            <h4 class="centered form-header">Bottom Line Report</h4>
            <hr>

            <div class="centered" v-if="response">
                <h4 v-if="total !== 0">{{ status }}</h4>
                <h1 style="font-size: 3rem">{{ absoluteTotal }}</h1>
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
            total: 0,
            response: false
        };
    },

	created() {
		axios.get(`/trips/${ this.trip_id }/report/bottomLine`)
        .then(response => {
           this.total = response.data;
           this.response = true;
        });
	},

    computed: {

        absoluteTotal() {
            if (this.total === 0) {
                return 'You are square.'
            }

            let usd = new Intl.NumberFormat('us-US', {
                minimumFractionDigits: 2
            });

            return '$' + usd.format(Math.abs(this.total));
        },

        status() {
            return this.total > 0 ? 'You are owed': 'You owe';
        }
    },

    methods: {
        hide() {
            this.$emit('hide');
        }
    }
}
</script>
