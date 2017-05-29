//require('./bootstrap');

import Vue from 'vue';
import axios from 'axios';
import Form from './lib/Form';

window.axios = axios;
window.Form = Form;
window.Vue = Vue;

window.bus = new Vue();

Vue.component('alert', require('./components/Alert.vue'));
Vue.component('carousel', require('./components/Carousel.vue'));
Vue.component('date-picker', require('./components/DatePicker.vue'));
Vue.component('hamburger', require('./components/Hamburger.vue'));
Vue.component('invite-friend', require('./components/InviteFriend.vue'));
Vue.component('loading', require('./components/Loading.vue'));
Vue.component('logout', require('./components/Logout.vue'));
Vue.component('post', require('./components/Post.vue'));
Vue.component('request-popup', require('./components/RequestPopup.vue'));
Vue.component('search-people', require('./components/SearchPeople.vue'));
Vue.component('trip-form', require('./components/TripForm.vue'));

// Profile
Vue.component('photo-upload-form', require('./components/PhotoUploadForm.vue'));
Vue.component('profile-feed', require('./components/ProfileFeed.vue'));
Vue.component('profile-info-form', require('./components/ProfileInfoForm.vue'));
Vue.component('profile-photo', require('./components/ProfilePhoto.vue'));

// Activity Feed
Vue.component('activity-feed', require('./components/ActivityFeed.vue'));
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
