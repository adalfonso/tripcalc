<template>
    <div class="popup-wrap" @click.self="hide">
        <div class="popup report">
            <div class="popup-close" @click="hide">&times;</div>
            <h4 class="centered">Top Spenders Report</h4>
            <hr>

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
    </div>
</template>

<script>

export default {

    props: {
        trip_id: { required: true }
    },

    data() {
        return {
            spenders: [],
            max: 0
        };
    },

	created() {
		axios.get(`/trip/${ this.trip_id }/report/topSpenders`)
        .then(response => {
           this.spenders = response.data.spend;
           this.max = response.data.max;
        });
	},

    methods: {
        hide() {
            this.$emit('hide');
        }
    }
}
</script>
