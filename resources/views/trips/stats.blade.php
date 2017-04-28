<div id="stats" class="left-col clearfix">

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
			:trip_id="{{$trip->id}}" @close="closeReport">
		</report-bottom-line>

		@if ($trip->users->count() > 1)
			<p class="item" @click="showReport('bottomLine')">
				Bottom Line
			</p>
		@endif

		{{-- Distribution Report --}}
		<report-distribution v-if="report.visible && report.type === 'distribution'"
			:trip_id="{{$trip->id}}" @close="closeReport">
		</report-distribution>

		@if ($trip->users->count() > 1)
			<p class="item" @click="showReport('distribution')">
				Distribution
			</p>
		@endif

		{{-- Top Spenders Report --}}
		<report-top-spenders v-if="report.visible && report.type === 'top-spenders'"
			:trip_id="{{$trip->id}}" @close="closeReport">
		</report-top-spenders>

		@if ($trip->users->count() > 1)
			<p class="item" @click="showReport('top-spenders')">
				Top Spenders
			</p>
		@endif

		{{-- Detailed Report --}}
		<report-detailed v-if="report.visible && report.type === 'detailed'"
			:trip_id="{{$trip->id}}" @close="closeReport">
		</report-detailed>

		<p class="item" @click="showReport('detailed')">
			Detailed
		</p>

		<hr>
	@endif
</div>
