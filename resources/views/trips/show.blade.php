@extends('layout')

@section('content')
<div id="trip">

	<trip-form v-if="tripForm.visible" :trip_id="{{$trip->id}}"
		@close="tripForm.visible = false">
    </trip-form>

    <invite-friend v-if="inviteFriend.visible" :trip_id="{{$trip->id}}"
    	@close="inviteFriend.visible = false">
    </invite-friend>

    <transaction-form v-if="transactionForm.visible" :trip_id="{{$trip->id}}"
    	:transaction_id="transactionForm.id" @close="closeTransactionForm">
    </transaction-form>

	<div class="trip-header clearfix">
		<h3 id="name">
			{{ $trip->name }}
			<img trip class="editButton" src="/img/icon/edit.png" @click="tripForm.visible = true">
		</h3>

		@if ($trip->start_date != "00/00/0000")
			<h6 id="dateRange">
				<strong>{{ $trip->dateRange }}</strong>
			</h6>
		@endif

	</div>

	@include('trips.stats')

	<div id="activity" transaction class="clearfix right-col">
		<button class="btn-full" @click="transactionForm.visible = true">+ New Transaction</button>
		<h4 class="margin-top">Recent Activity</h4>
		@if ($transactions)
			@foreach($transactions as $transaction)
				<div class="transaction">
					<p>
						<strong>{{ \Carbon\Carbon::parse($transaction->date)->format('M d, Y') }}</strong>
						 - ${{ $transaction->amount }}
						<img class="editButton" src="/img/icon/edit.png"
							@click="openTransactionForm({{ $transaction->id }})">
					</p>
					<p>{{ $transaction->description }}</p>
				</div>
			@endforeach
		@else
			<p>No transactions yet. Better get spending!</p>
		@endif
	</div>

</div>
@endsection

@section('vue')
	<script>
		new Vue({
		    el: '#app',

		    data: {
		        tripForm: { visible: false },
		        inviteFriend: { visible: false },
		        transactionForm: {
		        	visible: false,
		        	id: null
		        }
		    },

		    methods: {
		    	closeTransactionForm() {
		    		this.transactionForm.visible = false;
		    		this.transactionForm.id = null;
		    	},

		    	openTransactionForm(id = null) {
		    		this.transactionForm.visible = true;
		    		this.transactionForm.id = id;
		    	}
		    }
		});
	</script>
@overwrite
