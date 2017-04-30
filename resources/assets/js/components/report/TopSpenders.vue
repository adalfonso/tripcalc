<template>
    <div class="dialogue popup report">
        <h4 class="centered form-header">Top Spenders Report</h4>
        <hr>
        <img src="/img/icon/closePopup.png" class="closePopup" @click="close">

        <div v-for="spend in spenders">
			<p style="margin-bottom: 0">
				{{ spend.name }} - ${{ spend.currency }}
			</p>
			<div class="progressbar" :style="{ width: spend.sum / max * 100 + '%' }">
				<div v-if="spend.sum / max * 100 > 7.5">
					{{ spend.percent }}%
				</div>
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
            spenders: [],
            max: 0
        };
    },

	created() {
		axios.get(`/trips/${ this.trip_id }/report/topSpenders`)
        .then(response => {
           this.spenders = response.data.spend;
           this.max = response.data.max;
        });
	},

    computed: {
    },

    methods: {
        close() {
            this.$emit('close');
        }
    }
}
</script>
