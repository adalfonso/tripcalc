<template>
	<div>
		<div class="type"><p>$</p></div>
		<div class="info">
			<p>
				<strong>{{ data.dateForHumans }}</strong>
				| {{ data.creator }}

				<img style="float: right;" class="editButton"
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

			<ul class="hashtags-plaintext clearfix">
				<li v-for="hashtag in data.hashtags">#{{ hashtag }}</li>
			</ul>
		</div>
	</div>
</template>

<script>
export default {

props: {
	data: {},
	trip_id: null
},

methods: {
	edit(id) {
		bus.$emit('showTransactionForm', id);
	}
}

}
</script>
