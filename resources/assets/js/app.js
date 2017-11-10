//require('./bootstrap');

import Vue from 'vue';
import axios from 'axios';
import Form from './lib/Form';
import Collect from './lib/Collection.js';

window.axios = axios;
window.Collect = Collect;
window.Form = Form;
window.Vue = Vue;
window.userId = document.querySelector('[name="user-id"]').content;

window.bus = new Vue();

Vue.component('alert', require('./components/Alert.vue'));
Vue.component('carousel', require('./components/Carousel.vue'));
Vue.component('date-picker', require('./components/DatePicker.vue'));
Vue.component('hamburger', require('./components/Hamburger.vue'));
Vue.component('loading', require('./components/Loading.vue'));
Vue.component('logout', require('./components/Logout.vue'));
Vue.component('context-menu', require('./components/ContextMenu.vue'));
Vue.component('post', require('./components/Post.vue'));

Vue.component('announcer', require('./components/Announcer.vue'));
Vue.component('requests', require('./components/Requests.vue'));
Vue.component('notifications', require('./components/Notifications.vue'));

Vue.component('search-people', require('./components/SearchPeople.vue'));

// Profile
Vue.component('friend-preferences', require('./components/FriendPreferences.vue'));
Vue.component('friend-manager', require('./components/FriendManager.vue'));
Vue.component('photo-upload-form', require('./components/PhotoUploadForm.vue'));
Vue.component('profile-feed', require('./components/ProfileFeed.vue'));
Vue.component('profile-info-form', require('./components/ProfileInfoForm.vue'));
Vue.component('profile-photo', require('./components/ProfilePhoto.vue'));

// Activity Feed
Vue.component('activity-feed', require('./components/ActivityFeed.vue'));
Vue.component('comment', require('./components/Comment.vue'));
Vue.component('post-form', require('./components/PostForm.vue'));
Vue.component('transaction', require('./components/Transaction.vue'));
Vue.component('transaction-form', require('./components/TransactionForm.vue'));

// Reports
Vue.component('spender-progress-bar', require('./components/report/SpenderProgressBar.vue'));
Vue.component('report-bottom-line', require('./components/report/BottomLine.vue'));
Vue.component('report-closeout', require('./components/report/Closeout.vue'));
Vue.component('report-detailed', require('./components/report/Detailed.vue'));
Vue.component('report-distribution', require('./components/report/Distribution.vue'));
Vue.component('report-top-spenders', require('./components/report/TopSpenders.vue'));

// Trip
Vue.component('advanced-trip-settings', require('./components/AdvancedTripSettings.vue'));
Vue.component('invite-friend', require('./components/InviteFriend.vue'));
Vue.component('closeout-helper', require('./components/CloseoutHelper.vue'));
Vue.component('trip-form', require('./components/TripForm.vue'));
Vue.component('virtual-user-manager', require('./components/VirtualUserManager.vue'));
Vue.component('conflict-handler', require('./components/ConflictHandler.vue'));
