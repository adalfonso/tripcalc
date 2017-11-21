<template>
	<div>
		<div class="type">
			<div class="speech-bubble"></div>
		</div>

		<div class="info">
			<div class="post">
				<div class="clearfix">
					<p class="float-left">
						<strong>{{ data.poster }}</strong>
					</p>

					<div class="float-right action-bar">
		                <img class="btn-edit" v-if="editable"
		                    src="/img/icon/edit.png" @click="edit">

		                <div class="btn-edit close"
		                    v-else-if="deleteOnly"
		                    @click="deleting = true">
		                </div>
		            </div>
				</div>

				<div class="date">{{ data.dateForHumans }}</div>

				<p v-show="!editing">{{ data.content }}</p>
			</div>

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
	                <button class="btn" v-if="editing">
	                    Update
	                </button>

	                <div class="btn"
	                    v-if="editing && !deleting"
	                    @click="deleting = true">
	                    Delete
	                </div>

                    <div class="btn"
						v-if="deleting || editing"
						@click="cancel">
                        Cancel
                    </div>

                    <div class="btn"
						v-if="deleting"
						@click="deletePost">
                        Are you sure?
                    </div>
	            </div>
			</form>

			<comment :data="comment"
				v-for="(comment, index) in comments.use()"
				v-show="index === 0 || more"
				@delete="removeComment(index)">
			</comment>

			<p class="more fake-link midnight"
				v-if="showMore"
				@click="more = true">
				{{ comments.count() - 1 }} more replies
			</p>

			<form @submit.prevent="comment" class="nested">
				<p class="ui-error" v-if="commentForm.errors.has('content')"
					v-text="commentForm.errors.get('content')"></p>
				<textarea maxlength="255"
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

export default {

props: {
	data: { default: {} },
	type: { required: true }
},

data() {
    return {
		editing: false,
		deleting: false,
		more: false,
		comments: new Collect(this.data.comments),
        editForm: new Form({ content: this.data.content }),
		commentForm: new Form({ content: '' })
    };
},

computed: {
	showMore() {
		return this.comments.count() > 1 && !this.more;
	},

	editable() {
		return this.data.created_by === userId;
	},

	deleteOnly() {
		return this.data.postable_type === 'App\\User'
			&& this.data.postable_id === userId
	}
},

methods: {
	edit() {
		this.editing = true;
	},

	cancel() {
		this.deleting = false;
		this.editing = false;
		this.editForm.content = this.data.content;
	},

    update() {
		let id = this.data.postable_id;
		this.editForm.patch(`/${this.type}/${id}/post/${this.data.id}`)
        .then(data => {
			this.editing = false;
			this.deletable = false;
			this.data.content = this.editForm.content;
			this.editForm.errors.clear();
		})
        .catch(errors => {});
    },

	deletePost() {
		let id = this.data.postable_id;
        this.editForm.delete(`/${this.type}/${id}/post/${this.data.id}`)
        .then(data => {
			return this.type === 'profile'
				? window.location = '/user/' + id
				: window.location = '/' + this.type + '/' + id;
		})
        .catch(errors => {});
    },

	comment($event) {
		let id = this.data.postable_id;
		if (event.shiftKey) {
            return;
        }

		this.commentForm.post(`/${this.type}/${id}/post/${this.data.id}/comment`)
        .then(data => {
			this.commentForm.content = '';
			this.commentForm.errors.clear();
			this.comments = new Collect(data);
		})
        .catch(errors => {});
	},

	removeComment(index) {
		this.comments.forget(index);

		let temp = this.comments;
		this.comments = new Collect([]);

		setTimeout(() => {
			this.comments = temp;
		}, 1);

		if (temp.count() <= 1) {
			this.more = false;
		}
	}
}

}
</script>
