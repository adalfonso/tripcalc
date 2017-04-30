<template>
    <div class="dialogue popup report">
        <h4 class="centered form-header">Bottom Line Report</h4>

        <hr>

        <img src="/img/icon/closePopup.png" class="closePopup"
            @click="close">

        <div class="centered" v-if="response">
            <h4 v-if="total !== 0">{{ status }}</h4>
            <h1 style="font-size: 3rem">{{ absoluteTotal }}</h1>
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
            return '$' + Number(
                Math.abs(this.total)
            ).toFixed(2);
        },

        status() {
            return this.total < 0 ? 'You are owed': 'You owe';
        }
    },

    methods: {
        close() {
            this.$emit('close');
        }
    }
}
</script>
