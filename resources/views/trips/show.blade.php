@extends('layout')

@section('content')
<div id="trip">
	<trip-form v-if="tripForm.visible" :trip_id="{{$trip->id}}"
		@hide="hideAll">
    </trip-form>

    <invite-friend v-if="inviteFriend.visible" :trip_id="{{$trip->id}}"
    	@hide="hideAll">
    </invite-friend>

    <transaction-form v-if="transactionForm.visible" :trip_id="{{$trip->id}}"
    	:transaction_id="transactionForm.id" @hide="hideAll">
    </transaction-form>

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
				},

		        transactionForm: {
		        	visible: false,
		        	id: null
		        }
		    },

		    methods: {

				hideAll() {
					this.tripForm.visible = false;
					this.inviteFriend.visible = false;
					this.report.visible = false;
					this.transactionForm.visible = false;
				},

				popupVisible() {
					for (prop in this._data) {
						if (this._data[prop].visible) {
							return true;
						}
					}

					return false;
				},

				createPost() {
					bus.$emit('submit');
				},

		    	showTransactionForm(id = null) {
		    		if (this.popupVisible()) {
		    			return new Promise((resolve) => {
		    				this.hideAll();
		    				resolve();

		    			}).then(() => {
		    				this.showTransactionForm(id);
		    			});
		    		}

		    		this.transactionForm.visible = true;
		    		this.transactionForm.id = id;
		    	},

				showTripForm() {
					this.hideAll();
					this.tripForm.visible = true;
				},

				showInviteFriendsForm() {
					this.hideAll();
					bus.$emit('closeNav');
					this.inviteFriend.visible = true;
				},

				showReport(type) {
		    		if (this.popupVisible()) {
		    			return new Promise((resolve) => {
		    				this.hideAll();
		    				resolve();

		    			}).then(() => {
		    				this.showReport(type);
		    			});
		    		}

					this.report.visible = true;
					if (type) {
						this.report.type= type;
					}
				}
		    }
		});
	</script>
@overwrite
