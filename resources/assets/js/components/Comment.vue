<template>
    <div class="comment">
        <div class="header clearfix">
            <p class="float-left">
                <a :href="'/profile/' +  data.user.username" class="smoke">
                    {{ data.user.first_name }}
                    {{ data.user.last_name }}
                </a>

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

        <div class="body">
            <p class="date">{{ data.dateForHumans }}</p>
            <p v-show="!editing">{{ data.content }}</p>
        </div>

        <form class="edit-form" @submit.prevent="update">
            <p class="ui-error" v-if="form.errors.has('content')"
                v-text="form.errors.get('content')"></p>
            <textarea
                v-if="editing"
                v-model="form.content"
                autofocus
                maxlength="255" class="plain placeholder-dark"
                placeholder="Enter a comment..." >
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

                <div v-if="deleting">
                    <div class="btn" @click="cancel">
                        Cancel
                    </div>
                    <div class="btn" @click="deleteComment">
                        Are you sure?
                    </div>
                </div>
            </div>
        </form>

    </div>
</template>

<script>
    import Form from '../lib/Form.js';

    export default {
        props: ['data'],

        data() {
            return {
                editing: false,
                deleting: false,
                form: new Form({ content: this.data.content })
            }
        },

        computed: {
            editable() {
                return this.data.created_by === userId;
            },

            deleteOnly() {
                return this.data.post.created_by === userId
            }
        },

        methods: {
            edit() {
                this.editing = true;
            },

            cancel() {
                this.deleting = false;
                this.editing = false;
                this.form.content = this.data.content;
            },

            update() {
                this.form.patch('/comment/' + this.data.id)
                    .then(response => {
                        this.editing = false;
                        this.data.content = this.form.content;
                        this.form.errors.clear();
                    })
                    .catch();
            },

            deleteComment() {
                axios.delete('/comment/' + this.data.id)
                    .then(response => {
                        this.$emit('delete');
                    })
                    .catch();
            }
        }
    }
</script>
