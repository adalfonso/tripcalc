@extends('layout')

@section('content')
<div id="trip">
	<trip-form v-if="tripForm.visible" :trip_id="{{$trip->id}}"
		@hide="hideAll">
    </trip-form>

    <invite-friend v-if="inviteFriend.visible" :trip_id="{{$trip->id}}"
    	@hide="hideAll">
    </invite-friend>

	<div class="trip-header clearfix">
		<h3 id="name">
		{{ $trip->name }}
			<img trip class="editButton" src="/img/icon/edit.png"
				@click="showTripForm">
		</h3>

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

		        tripForm: { visible: false },

		        inviteFriend: { visible: false },

				report: {
					visible: false,
					type: null
				}
		    },

			created() {
				bus.$on('closeModals', this.hideAll);
			},

		    methods: {

				hideAll() {
					this.tripForm.visible = false;
					this.inviteFriend.visible = false;
					this.report.visible = false;
				},

				createPost() {
					bus.$emit('submit');
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
