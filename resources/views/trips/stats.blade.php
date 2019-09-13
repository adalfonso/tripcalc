<div id="stats" class="left-col section clearfix">

	@if ($trip->active)
		<div class="flex">
			<div class="btn btn-tiny invite-to-trip" v-cloak
				@click="showInviteFriendsForm">
				+ Invite Friends to Trip
			</div>
		</div>
	@endif


	@if(!$trip->description && !$trip->budget && $sum === 0)
		<p>No stats to show yet.</p>
	@endif

	{{-- Description --}}
	@if ($trip->description)
		<h5><strong>Description:</strong></h5>
		<p>{{ $trip->description }}</p>
		<hr>
	@endif

	{{-- Budget --}}
	@if ($trip->budget)
		<h5><strong>Budget:</strong></h5>
		<p>
			@if ($sum > 0)
				${{ number_format($sum, 2) }} /
			@endif

			${{ $trip->budget }} -

			@if ($sum > $trip->budget)
				<strong>over budget</strong>
			@else
				{{ round(($sum / $trip->budget) * 100) }}% met
			@endif
		</p>
		<hr>
	@endif

	{{-- Reports --}}
	@if ($sum > 0)
		<h5><strong>Reports:</strong></h5>

		{{-- Bottom Line Report --}}
		<report-bottom-line v-if="report.visible && report.type === 'bottomLine'"
			:trip_id="{{$trip->id}}" @hide="hideAll">
		</report-bottom-line>

		{{-- Distribution Report --}}
		<report-distribution v-if="report.visible && report.type === 'distribution'"
			:trip_id="{{$trip->id}}" @hide="hideAll">
		</report-distribution>

		{{-- Top Spenders Report --}}
		<report-top-spenders v-if="report.visible && report.type === 'top-spenders'"
			:trip_id="{{$trip->id}}" @hide="hideAll">
		</report-top-spenders>

		{{-- Detailed Report --}}
		<report-detailed v-if="report.visible && report.type === 'detailed'"
			:trip_id="{{$trip->id}}" @hide="hideAll">
		</report-detailed>

		{{-- Closeout Report --}}
		<report-closeout v-if="report.visible && report.type === 'closeout'"
			:trip_id="{{$trip->id}}" @hide="hideAll">
		</report-closeout>

		@if ($trip->allUsers->count() > 1)
			<p class="item" @click="showReport('bottomLine')">Bottom Line</p>
			<p class="item" @click="showReport('distribution')">Distribution</p>
			<p class="item" @click="showReport('top-spenders')">Top Spenders</p>
		@endif

		<p class="item" @click="showReport('detailed')">Detailed</p>
		<p class="item">
			<a class="light" href="/trip/{{ $trip->id }}/report/extended">
				Detailed (Extended)
			</a>
		</p>

		@if ($trip->allUsers->count() > 1)
			<p class="item" @click="showReport('closeout')">Closeout</p>
		@endif

		<hr>
	@endif
</div>
