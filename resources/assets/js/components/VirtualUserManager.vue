<template>
<div class="popup-wrap" @click.self="hide">
<div class="popup">

    <alert v-if="alert" :type="'decide'" @hide="alert = false"
        @yes="merge" @no="$emit('hide')"
        :message="'Would you like to merge this user\'s transacations into your account?\n\n(This will also delete the virtual user)'">
    </alert>

    <conflict-handler v-if="conflict.handle" :data="conflict.data"
        @hide="conflict.handle = false" @resolve="resolveMerge">
    </conflict-handler>

    <div class="popup-close" @click="hide">&times;</div>

    <h4 class="centered">Virtual Users</h4>

    <hr>

    <table class="table-full font-dark">

        <!-- Username Row -->
        <template v-for="user in users">
            <tr v-if="edit.id === user.id && edit.errors.has('name')">
                <td colspan="3">
                    <p class="ui-error centered" v-text="edit.errors.get('name')"></p>
                </td>
            </tr>
            <tr>
                <td>
                    <div v-if="edit.id === user.id">
                        <input class="plain thin marginless" type="text"
                            v-model="edit.name">
                    </div>
                    <div v-else>{{ user.name }}</div>
                </td>
                <td class="align-right" style="width:90px">
                    <div class="btn btn-extra-thin" @click="startEditing(user)"
                        v-html="edit.id === user.id ? 'Update' : 'Edit' ">
                    </div>
                </td>
                <td class="align-right" style="width:90px">
                    <div class="btn btn-extra-thin" v-if="user.deleteable"
                        @click="removeUser(user)">
                        Delete
                    </div>
                    <div class="btn btn-extra-thin" v-else
                        @click="promptMerge(user)">
                        Merge
                    </div>
                </td>
            </tr>
        </template>

        <tr v-if="users.length > 0">
            <td colspan="3"><hr></td>
        </tr>

        <tr>
            <td colspan="3" class="centered">
                <div @click="addable = !addable" class="btn-count">
                    [<span v-html=" addable? '&minus;' : '&plus;'"></span>]
                </div>
            </td>
        </tr>
    </table>
    <table class="table-full font-dark" v-if="addable">
        <tr>
            <td colspan="3">
                <p class="ui-error centered" v-if="newUser.errors.has('name')"
                    v-text="newUser.errors.get('name')"></p>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <input class="plain thin marginless" type="text"
                    v-model="newUser.name" maxlength="63">
            </td>
            <td class="align-right">
                <div class="btn btn-extra-thin" @click="addNewUser">
                    Add New
                </div>
            </td>
        </tr>
    </table>
</div>
</div>
</template>

<script>

import Form from '../lib/Form.js';

export default {
    props: {
        trip_id: { required: true }
    },

    data() {
        return {
            addable: false,
            alert: false,
            newUser: new Form({ name: ''}),
            users: [],
            edit: new Form({name: '', id: null}),
            conflict: {
                handle: false,
                data: {}
            },
            mergeAttempt: {
                user: null,
                rules: null
            }
        };
    },

    created() {
        axios.get(`/trip/${this.trip_id}/virtualUsers`)
        .then(response => {
            this.users = response.data;
        });
    },

    methods: {
        hide() {
            this.$emit('hide');
        },

        addNewUser() {
            this.newUser.post(`/trip/${this.trip_id}/virtualUsers`)
            .then(data => {
                this.newUser.name = '';
                this.users = data;
            })
            .catch(error => {});
        },

        removeUser(user) {
            axios.delete(`/trip/${this.trip_id}/virtualUser/${user.id}`)
            .then(response => {
                this.users = response.data;
            });
        },

        promptMerge(user, rules = null) {
            this.mergeAttempt = {
                user: user,
                rules: rules
            };

            this.alert = true;
        },

        resolveMerge(data) {
            this.conflict.handle = false;
            this.mergeAttempt.rules = data;
            this.merge();
        },

        merge() {
            let user = this.mergeAttempt.user;
            let rules = this.mergeAttempt.rules;

            this.alert = false;

            axios.post(`/trip/${this.trip_id}/virtualUser/${user.id}/merge`, {
                rules: rules
            })
            .then(response => {
                this.hide();
            }).catch(error => {
                if (error.response.status !== 422) {
                    return;
                }

                this.conflict.data = error.response.data;
                this.conflict.handle = true;
            });
        },

        updateUser(user) {
            if (user.name === this.edit.name) {
                return this.stopEditing();
            }

            this.edit.patch(`/trip/${this.trip_id}/virtualUser/${user.id}`)
            .then(response => {
                this.users = response;
                this.stopEditing();
            })
            .catch(error => {});
        },

        startEditing(user) {
            if (this.edit.id === user.id) {
                return this.updateUser(user);
            }

            this.edit.errors.clear();
            this.edit.name = user.name;
            this.edit.id = user.id;
        },

        stopEditing() {
            this.edit.name = '';
            this.edit.id = null;
        }
    }
}
</script>
