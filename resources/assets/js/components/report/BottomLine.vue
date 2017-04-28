<template>
    <div class="dialogue popup report">
        <h4 class="centered form-header">Bottom Line Report</h4>

        <hr>

        <img src="/img/icon/closePopup.png" class="closePopup"
            @click="close">

        <div class="centered">
            <h4>{{ status }}</h4>
            <h1 style="font-size: 3.6rem">${{ absoluteTotal }}</h1>
        </div>

    </div>
</template>

<script>

export default {

    props: {
        trip_id: { default: null }
    },

    data() {
        return { total: 0 };
    },

	created() {
		axios.get(`/trips/${ this.trip_id }/report/bottomLine`)
        .then(response => {
           this.total = response.data;
        });
	},

    computed: {

        absoluteTotal() {
            return Number(
                Math.abs(this.total)
            ).toFixed(2);
        },

        status() {
            return this.total > 0 ? 'You are owed': 'You owe';
        }
    },

    methods: {
        close() {
            this.$emit('close');
        }
    }
}
</script>
