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
		@hide="hideAll" :active="tripIsActive">
    </trip-form>

	<advanced-trip-settings v-if="advancedSettings.visible" :trip_id="{{$trip->id}}"
		@hide="hideAll" @change-virtual-user-state="changeVirtualUserState"
		@change-trip-state="changeTripState">
    </advanced-trip-settings>

    <invite-friend v-if="inviteFriend.visible" :trip_id="{{$trip->id}}"
    	@hide="hideAll">
    </invite-friend>

	<virtual-user-manager v-if="virtualUsers.visible" :trip_id="{{$trip->id}}"
		@hide="hideAll">
	</virtual-user-manager>

	<div class="trip-header clearfix">
		<div class="clearfix">
			<h3 id="name">
				{{ $trip->name }}


				<div class="badge-plain" v-if="closeoutState === 'closed'" v-cloak>
					Closed Out
				</div>
			</h3>

			@if ($trip->start_date != "00/00/0000")
				<h6 id="dateRange">
					<strong>{{ $trip->dateRange }}</strong>
				</h6>
			@endif
		</div>

		<h6 class="marginless" v-if="closeoutState === 'closing'" v-cloak>
			<em>Closeout is underway</em>
			<closeout-helper
				:trip_id="{{$trip->id}}"
				@change-trip-state="changeTripState">
			</closeout-helper>
		</h6>

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

				closeoutState: '{{ $trip->state }}',

				inviteFriend: { visible: false },

				menuItems: [
					{
						display: '+ Invite Friends to Trip',
						active: {{ $trip->active }},
						emit: 'invite'
					},
					{
						display: 'Manage Virtual Users',
						active: {{ json_encode($trip->virtual_users) }},
						emit: 'virtual'
					},
					{
						display: 'Advanced',
						active: true,
						emit: 'advanced'
					}
				],

				report: {
					visible: false,
					type: null
				},

				tripIsActive: {{ $trip->active }},

				tripForm: { visible: false },

				virtualUsers: { visible: false }
		    },

			created() {
				bus.$on('closeModals', this.hideAll);
			},

		    methods: {

				changeVirtualUserState(state) {
					new Collect(this.menuItems)
						.where('display', 'Manage Virtual Users')
						.first()
						.active = state;
				},

				changeTripState(state = active) {
					this.closeoutState = state;

					if (state === 'closed') {
						this.tripIsActive = false;
					}
				},

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
					bus.$emit('closeNav');
					bus.$emit('closeModals');
					this.advancedSettings.visible = true;
				},

				showTripForm() {
					bus.$emit('closeNav');
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
					bus.$emit('closeNav');
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
