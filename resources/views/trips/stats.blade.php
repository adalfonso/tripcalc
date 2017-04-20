<div id="stats" class="left-col">

	@php
		$sum = $transactions->sum('amount');
		$sectionExists = false;
	@endphp

	@if ($trip->description)
		<h5><strong>Description:</strong></h5>
		<p>{{ $trip->description }}</p>

		@php $sectionExists = true; @endphp
	@endif

	@if ($trip->budget)

		@if ($sectionExists)
			<hr>
		@endif

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

		@php $sectionExists = true; @endphp
	@endif

	@if ($sum > 0)
		@php
			$transactionsByUser = $transactions
				->groupBy(function($item){
					return $item->creator->first_name
				   . ' ' . $item->creator->last_name;
				})
				->map(function($item) use ($sum){
					return (object) [
						'sum' => number_format($item->sum('amount'), 2),
						'percent' => round($item->sum('amount') / $sum * 100)
					];
				})->sortByDesc('sum');
		@endphp

		@if ($sectionExists)
			<hr>
		@endif

		<h5><strong>Top Spenders:</strong></h5>

		@foreach($transactionsByUser as $user => $spend)

			<p style="margin-bottom: 0">
				{{ $user }} - ${{ $spend->sum }}
			</p>
			<div class="progressbar" style="width:{{ $spend->percent }}%">
				@if($spend->percent > 7)
					{{ $spend->percent }}%
				@endif
			</div>
		@endforeach

		@php $sectionExists = true; @endphp
	@endif

	{{-- @if ($transactions)
		<p class="fake-link">Draw Expense Graph</p>
	@endif --}}
</div>