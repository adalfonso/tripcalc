<template>
    <div class="popup-wrap" @click.self="hide">
        <div class="popup">
            <div class="popup-close" @click="hide">&times;</div>

            <h4 class="centered">Virtual Users</h4>

            <hr>

            <table class="table-full font-dark">
                <tr v-for="user in users">
                    <td>{{ user.name }}</td>
                    <td class="align-right">
                        <div class="btn btn-extra-thin">
                            Edit
                        </div>
                    </td>
                    <td class="align-right">
                        <div class="btn btn-extra-thin" v-if="user.deleteable"
                            @click="removeUser(user)">
                            Delete
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="3"><hr></td>
                </tr>
                <tr>
                    <td colspan="3" class="centered">
                        <div @click="addable = !addable">
                            [<span v-html=" addable? '&minus;' : '&plus;'"></span>]
                        </div>
                    </td>
                </tr>
                <tr v-if="addable">
                    <td colspan="2">
                        <p class="ui-error" v-if="newUser.errors.has('name')"
                            v-text="newUser.errors.get('name')"></p>
                        <input class="plain thin marginless" type="text" v-model="newUser.name"
                            maxlength="63">
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
            newUser: new Form({ name: ''}),
            users: []
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
            .catch(error => {

            });
        },

        removeUser(user) {
            axios.delete(`/trip/${this.trip_id}/virtualUser/${user.id}`)
            .then(response => {
                this.users = response.data;
            });
        }
    }
}
</script>
