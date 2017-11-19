<template>
	<div>
		<div class="type">
			<div class="speech-bubble"></div>
		</div>

		<div class="info">
			<div class="clearfix">
				<p class="float-left">
					<strong>{{ data.poster }}</strong>
				</p>

				<div class="float-right action-bar">
					<img class="btn-edit" v-if="data.editable || isOwner"
						src="/img/icon/edit.png" @click="editPost()">
				</div>
			</div>

			<div class="date">{{ data.dateForHumans }}</div>

			<p v-show="!editing">{{ data.content }}</p>

			<form class="edit-form" @submit.prevent="update">
				<p class="ui-error" v-if="editForm.errors.has('content')"
					v-text="editForm.errors.get('content')"></p>
				<textarea
					v-if="editing"
					v-model="editForm.content"
					autofocus
					maxlength="255" class="plain placeholder-dark"
					placeholder="Enter a message..." >
				</textarea>

				<div class="button-bar clearfix">
					<button class="btn" v-if="editing && data.editable">Update</button>
					<div class="btn" v-if="editing" @click="deletable = true">Delete</div>
					<div class="btn" v-if="deletable" @click="deletePost">Are you sure?</div>
				</div>
			</form>

			<template v-if="more">
				<comment :data="comment"
					v-for="comment in comments">
				</comment>
			</template>

			<template v-else-if="comments.length">
				<comment :data="firstComment"></comment>
			</template>

			<p class="more fake-link midnight"
				v-if="showMore"
				@click="more = true">
				{{ comments.length - 1 }} more replies
			</p>

			<form @submit.prevent="comment" class="nested">
				<p class="ui-error" v-if="commentForm.errors.has('content')"
					v-text="commentForm.errors.get('content')"></p>
				<textarea maxlength="255"
					style="height:2rem;"
					class="plain placeholder-dark last enterComment"
					placeholder="Enter a comment..."
					v-model="commentForm.content"
					@keyup.enter="comment"
					@keyup="commentForm.errors.clear()">
				</textarea>
			</form>
		</div>
	</div>
</template>

<script>
import Form from '../lib/Form.js';
import DatePicker from '../lib/DatePicker.js';

export default {

props: {
	data: { default: {} },
	id: { required: true },
	type: { required: true },
	isOwner: { default: 0 }
},

data() {
    return {
		editing: false,
		deletable: false,
		more: false,
		comments: this.data.comments,
        editForm: new Form({ content: '' }),
		commentForm: new Form({ content: '' })
    };
},

computed: {
	firstComment() {
		return this.comments[0];
	},

	showMore() {
		return this.comments.length > 1 && !this.more;
	}
},

methods: {
	editPost() {
		this.editing = !this.editing;

		this.editing
			? this.editForm.content = this.data.content
			: this.deletable = false;
	},

    update() {
		this.editForm.patch(`/${ this.type }/${ this.id }/post/${ this.data.id }`)
        .then(data => {
			this.editing = false;
			this.deletable = false;
			this.data.content = this.editForm.content;
			this.editForm.errors.clear();
		})
        .catch(errors => {});
    },

	deletePost() {
        this.editForm.delete(`/${ this.type }/${ this.id }/post/${ this.data.id }`)
        .then(data => {
			return this.type === 'profile'
				? window.location = '/user/' + this.id
				: window.location = '/' + this.type + '/' + this.id;
		})
        .catch(errors => {});
    },

	comment($event) {
		if (event.shiftKey) {
            return;
        }

		this.commentForm.post(`/${ this.type }/${ this.id }/post/${ this.data.id }/comment`)
        .then(data => {
			this.commentForm.content = '';
			this.commentForm.errors.clear();
			this.comments = data;
		})
        .catch(errors => {});
	}
}

}
</script>
