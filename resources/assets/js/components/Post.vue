<template>
	<div>
		<div class="type">
			<div class="speech-bubble"></div>
		</div>

		<div class="info">
			<div class="clearfix">
				<p class="float-left">
					<strong>{{ data.dateForHumans }}</strong>
					| {{ data.poster }}
				</p>

				<div class="float-right action-bar">
					<img class="btn-edit" v-if="data.editable || isOwner"
						src="/img/icon/edit.png" @click="editPost()">
				</div>
			</div>

			<p v-show="!editing">{{ data.content }}</p>

			<form @submit.prevent="update">
				<p class="ui-error" v-if="editForm.errors.has('content')"
					v-text="editForm.errors.get('content')"></p>
				<textarea v-if="editing" maxlength="255" class="plain placeholder-dark"
					placeholder="Enter a message..." v-model="editForm.content">
				</textarea>

				<div class="button-bar clearfix">
					<button class="btn" v-if="editing && data.editable">Update</button>
					<div class="btn" v-if="editing" @click="deletable = true">Delete</div>
					<div class="btn" v-if="deletable" @click="deletePost">Are you sure?</div>
				</div>
			</form>

			<template v-if="more">
				<div class="comment clearfix"
					v-for="comment in comments">
					<p>
						<a :href="'/profile/' +  comment.user.id" class="midnight">
						{{ comment.user.first_name }}
						{{ comment.user.last_name }}
					</a>{{ comment.content }}, {{ comment.dateForHumans }}</p>
				</div>
			</template>

			<div class="comment clearfix"
				v-else-if="comments.length">
				<p>
					<a :href="'/profile/' +  firstComment.user.username" class="midnight">
					{{ firstComment.user.first_name }}
					{{ firstComment.user.last_name }}
				</a>{{ firstComment.content }}, {{ firstComment.dateForHumans }}</p>
			</div>

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
					class="plain placeholder-dark last"
					placeholder="Enter a comment..."
					v-model="commentForm.content"
					@keydown.enter="comment">
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
			this.data.content = this.editForm.content;
			this.editForm.errors.clear();
		})
        .catch(errors => {});
    },

	deletePost() {
        this.editForm.delete(`/${ this.type }/${ this.id }/post/${ this.data.id }`)
        .then(data => { window.location = window.location.href })
        .catch(errors => {});
    },

	comment() {
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
