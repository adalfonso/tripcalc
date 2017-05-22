<template>
	<div>
		<div class="type">
			<div class="speech-bubble"></div>
		</div>

		<div class="info">
			<p>
				<strong>{{ data.dateForHumans }}</strong>
				| {{ data.poster }}
				<img style="float: right;" class="btn-edit" v-if="data.editable"
					src="/img/icon/edit.png" @click="editPost()">
			</p>

			<p v-show="!edit">{{ data.content }}</p>

			<form @submit.prevent="update">

				<textarea v-if="edit" name="description" type="text" maxlength="255"
					class="plain placeholder-dark" placeholder="Enter a message..." v-model="form.content">
				</textarea>

				<div class="floatable-left clearfix">
					<button class="btn" v-if="edit">Update</button>
					<div class="btn" v-if="edit" @click="deletable = true">Delete</div>
					<div class="btn" v-if="deletable" @click="deletePost">Are you sure?</div>
				</div>
			</form>
		</div>
	</div>
</template>

<style>
	.floatable-left > * {
		margin: 0 .5rem .5rem 0;
		padding: .25rem .6rem;
	}
</style>

<script>
import Form from '../lib/Form.js';
import DatePicker from '../lib/DatePicker.js';

export default {

props: {
	data: {},
	trip_id: null
},

data() {
    return {
		edit: false,
		deletable: false,
        form: new Form({ content: '' })
    };
},

methods: {
	editPost() {
		this.edit = !this.edit;

		this.edit
			? this.form.content = this.data.content
			: this.deletable = false;
	},

    update() {
		this.form.patch(`/trips/${ this.trip_id }/posts/${ this.data.id }`)
        .then(data => {
			this.edit = false;
			this.data.content = this.form.content;
		})
        .catch(errors => {});
    },

	deletePost() {
        this.form.delete(`/trips/${ this.trip_id }/posts/${ this.data.id }`)
        .then(data => { window.location = '/trips/' + this.trip_id })
        .catch(errors => {});
    }
}

}
</script>