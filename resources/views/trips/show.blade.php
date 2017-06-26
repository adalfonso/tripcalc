@extends('layout')

@section('nav-right')
	<context-menu :items="menuItems"
		@advanced="showAdvancedSettings"
		@invite="showInviteFriendsForm"
		@virtual="showVirtualUsersForm">
	</context-menu>
@stop

@section('nav-settings')
	<a @click="showTripForm" class="link-enhanced clearfix">
		<img src="/img/icon/gear-64x64.png">
		<p class="fake-link">Settings</p>
	</a>
@stop

@section('content')
<div id="trip">
	<trip-form v-if="tripForm.visible" :trip_id="{{$trip->id}}"
		@hide="hideAll">
    </trip-form>

	<advanced-trip-settings v-if="advancedSettings.visible" :trip_id="{{$trip->id}}"
		@hide="hideAll">
    </advanced-trip-settings>

    <invite-friend v-if="inviteFriend.visible" :trip_id="{{$trip->id}}"
    	@hide="hideAll">
    </invite-friend>

	<virtual-user-manager v-if="virtualUsers.visible" :trip_id="{{$trip->id}}"
		@hide="hideAll">
	</virtual-user-manager>

	<div class="trip-header clearfix">
		<h3 id="name">{{ $trip->name }}</h3>

		@if ($trip->start_date != "00/00/0000")
			<h6 id="dateRange">
				<strong>{{ $trip->dateRange }}</strong>
			</h6>
		@endif
	</div>

	@include('trips.stats')

	@include('trips.activities')
</div>
@endsection

@section('vue')
	<script>

		new Vue({
		    el: '#app',

		    data: {

				advancedSettings: { visible: false },

				inviteFriend: { visible: false },

				menuItems: [
					{ display: 'Invite Friends', emit: 'invite' },
				@if ($trip->virtual_users)
					{ display: 'Manage Virtual Users', emit: 'virtual' },
				@endif
					{ display: 'Advanced', emit: 'advanced' }
				],

				report: {
					visible: false,
					type: null
				},

				tripForm: { visible: false },

				virtualUsers: { visible: false }
		    },

			created() {
				bus.$on('closeModals', this.hideAll);
			},

		    methods: {

				hideAll() {
					this.advancedSettings.visible = false;
					this.inviteFriend.visible = false;
					this.report.visible = false;
					this.tripForm.visible = false;
					this.virtualUsers.visible = false;
				},

				createPost() {
					bus.$emit('submit');
				},

				showAdvancedSettings() {
					bus.$emit('closeModals');
					this.advancedSettings.visible = true;
				},

				showTripForm() {
					bus.$emit('closeModals');
					this.tripForm.visible = true;
				},

				showInviteFriendsForm() {
					bus.$emit('closeNav');
					bus.$emit('closeModals');
					this.inviteFriend.visible = true;
				},

				showTransactionForm() {
			    	bus.$emit('showTransactionForm');
			    },

				showVirtualUsersForm() {
					bus.$emit('closeModals');
					this.virtualUsers.visible = true;
				},

				showReport(type) {
					if (!type) {
						return false;
					}

					this.report.visible = true;
					this.report.type= type;
				}
		    }
		});
	</script>
@overwrite
