<template>
	<div>
		<div class="type"><p>$</p></div>
		<div class="info">
			<p>
				<strong>{{ data.dateForHumans }}</strong>
				| {{ data.creator }}

				<img style="float: right;" class="btn-edit"
				   src="/img/icon/edit.png" @click="edit(data.id)">
			</p>

			<p v-if="data.creator !== data.updater">
				<em>
					Updated {{ data.updatedDateForHumans}}
					by {{ data.updater }}.
				</em>
			</p>

			<p>
				<strong>{{ data.date }}</strong>
				- ${{ data.amount }}
			</p>

			<p v-if="data.description !== null">{{ data.description }}</p>

			<ul class="hashtags clearfix">
				<li v-for="hashtag in data.hashtags">#{{ hashtag }}</li>
			</ul>
		</div>
	</div>
</template>

<script>
export default {

props: {
	data: { required: true },
	trip_id: { required : true }
},

methods: {
	edit(id) {
		bus.$emit('showTransactionForm', id);
	}
}

}
</script>
