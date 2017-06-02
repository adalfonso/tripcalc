<template>
<div class="popup-wrap" @click.self="hide">
<form id="inviteFriendForm" class="popup" @submit.prevent>
    <div class="popup-close" @click="hide">&times;</div>
    <loading v-if="loading.visible"></loading>

    <alert v-if="alert.visible" :message="alert.message"
        @hide="hideAlert">
    </alert>
    <h4 class="centered">Invite Friends</h4>
    <hr>

    <p class="ui-error" v-if="errors.has('friend')" v-text="errors.get('friend')"></p>
    <div class="ui-input-btn" @click="addToQueue">
        <span class="font-large">+</span>
    </div>

    <input class="hasBtn" type="text" placeholder="Enter friend's name or email"
        maxlength="50" v-model="input" @keyup="searchFriend"
        @keyup.enter="addToQueue" autofocus>

    <div class="inviteFriendResults" v-if="results.length > 0">
        <h5 v-for="friend in results" @click="addToQueue($event, friend)" class="hovertext">
            <strong>+</strong> {{ friend.first_name }} {{ friend.last_name }}
        </h5>
    </div>

    <div class="clearfix" v-if="queue.items.length > 0">
        <h6 class="item" v-for="friend in queue.items" @click="queue.remove(friend)">
            {{ friend.display }}
        </h6>
    </div>

    <div class=" btn btn-full" @click="onSubmit">Send Invitations</div>
</form>
</div>
</template>

<script>
import FriendQueue from '../lib/FriendQueue.js';
import Errors from '../lib/Errors.js';

export default {

    props: {
        trip_id: { required: true }
    },

    data() {
        return {
            alert: { visible: false, message: ''},
            errors: new Errors(),
            input: '',
            loading: { visible: false },
            queue: new FriendQueue(),
            results: [],
            timeout: setTimeout(0)
        };
    },

    methods: {

        addToQueue(event, friend = null) {
            // If there are results, set target to first one
            if (!this.resultsAreEmpty() && event.type === 'keyup') {
                friend = this.results[0];
            }

            // If input and results are empty, form can be submitted
            if (this.datapointsAreEmpty() && event.type === 'keyup') {
                return this.onSubmit();
            }

            // Attempt to add the friend
            let add = this.queue.add({
                input: this.input,
                item: friend
            });

            if (add === true) {
                this.clearResults();
                this.input = '';

            } else if (typeof add === 'object' && add.hasOwnProperty('response')) {
                this.errors.record({
                    friend: [add.response]
                });

                this.input = '';
            }
        },

        alertTimeout(timeout) {
            return setTimeout(() => {
                this.hide();
            }, timeout);
        },

        clearResults() { this.results = []; },

        hide() {
            this.$emit('hide');
        },

        setAlert(message, timeout = null) {
            this.alert.message = message;
            this.alert.visible = true;

            if (timeout !== null) {
                this.alertTimeout(timeout);
            }
        },

        hideAlert() {
            this.alert.visible = false;
        },

        resultsAreEmpty(){
            return this.results.length === 0;
        },

        inputIsEmpty() {
            return this.input === null
                || this.input.replace(/\s/g, '') === '';
        },

        datapointsAreEmpty() {
            return this.resultsAreEmpty() && this.inputIsEmpty();
        },

        startLoading() { this.loading.visible = true; },

        endLoading() { this.loading.visible = false; },

        searchFriend() {
            this.errors.clear();

            clearTimeout(this.timeout);

            this.timeout = setTimeout(function() {
                if (this.input === '') {
                    this.clearResults();
                    return;
                }

                axios.post(`/trip/${this.trip_id}/eligibleFriends`, {
                    input: this.input,
                    trip_id : this.trip_id,
                    excluded: this.queue.userIds()
                })
                .then(response => {
                    this.results = response.data;
                });
            }.bind(this), 200);
        },

        onSubmit(event) {
            if (this.queue.items.length === 0) {
                if (this.input != '') {
                    this.addToQueue();
                } else {
                    return;
                }
            }

            if (this.queue.items.length === 0) { return; }

            this.startLoading();

            axios.post(
                `/trip/${this.trip_id}/inviteFriends`, {
                    friends: this.queue.items,
                    trip_id: this.trip_id
                }
            )
            .then(response => {
                this.setAlert('Successfully invited friends.', 2000);
                this.endLoading();
            });
        }
    }
}
</script>
