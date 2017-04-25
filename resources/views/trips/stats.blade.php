<div id="stats" class="left-col">

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
				${{ $sum }}	/
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

		{{-- Progress Report --}}
		<report-progress v-if="report.visible && report.type === 'progress'"
			:trip_id="{{$trip->id}}" @close="closeReport">
		</report-progress>

		@if ($trip->users->count() > 1)
			<p class="fake-link" @click="showReport('progress')">
				Progress Report
			</p>
		@endif

		{{-- Top Spenders Report --}}
		<report-top-spenders v-if="report.visible && report.type === 'top-spenders'"
			:trip_id="{{$trip->id}}" @close="closeReport">
		</report-top-spenders>

		<p class="fake-link" @click="showReport('top-spenders')">
			Top Spenders Report
		</p>
		<hr>
	@endif
</div>
