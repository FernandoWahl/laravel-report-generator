<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<style>
		    .center {
		        text-align: center;
		    }
			table .left {
				text-align: left;
			}
			table .right {
				text-align: right;
			}
			table .bold {
				font-weight: 600;
			}
			table .italic {
				font-style: italic;
			}
			table .oblique {
				font-style: oblique;
			}
			.bg-black {
				background-color: #000;
			}
			.f-white {
				color: #fff;
			}
			@foreach ($styles as $style)
			{{ $style['selector'] }} {
				{{ $style['style'] }}
			}
			@endforeach
		</style>
	</head>
	<body>
		<?php
		$ctr = 1;
		$no = 1;
		$currentGroupByData = [];
		$isOnSameGroup = true;
		$grandTotalSkip = 1;

		if ($showTotalColumns != []) {
			foreach ($columns as $colName => $colData) {
				if (!array_key_exists($colName, $showTotalColumns)) {
					$grandTotalSkip++;
				} else {
					break;
				}
			}
		}
		?>
    	<table>
    		<tr>
    			<td colspan="{{ count($columns) + 1 }}" class="center"><h1>{{ $headers['title'] }}</h1></td>
    			@if ($showMeta)
					@foreach($headers['meta'] as $name => $value)
						<tr>
							<td><b>{{ $name }}</b></td>
							<td colspan="{{ count($columns) }}">{{ ucwords($value) }}</td>
						</tr>
					@endforeach
				@endif
    		</tr>
    	</table>
    	<table>
    		@if ($showHeader)
    		<thead>
	    		<tr>
    				@if ($showNumColumn)
    	    			<th class="left">No</th>
    				@endif
	    			@foreach ($columns as $colName => $colData)
	    				@if (array_key_exists($colName, $editColumns))
	    					<th class="{{ isset($editColumns[$colName]['class']) ? $editColumns[$colName]['class'] : 'left' }}">{{ $colName }}</th>
	    				@else
		    				<th class="left">{{ $colName }}</th>
	    				@endif
	    			@endforeach
	    		</tr>
    		</thead>
    		@endif
    		<?php
    		$chunkRecordCount = ($limit == null || $limit > 50000) ? 50000 : $limit + 1;
    		$__env = isset($__env) ? $__env : null;
			$query->chunk($chunkRecordCount, function($results) use(&$ctr, &$no, &$currentGroupByData, &$isOnSameGroup, $grandTotalSkip, $headers, $columns, $limit, $editColumns, $showTotalColumns, $groupByArr, $applyFlush, $showNumColumn, $__env) {
			?>
    		@foreach($results as $result)
				<?php
					if ($limit != null && $ctr == $limit + 1) return false;
					if ($groupByArr != []) {
						$isOnSameGroup = true;
						foreach ($groupByArr as $groupBy) {
							if (is_object($columns[$groupBy]) && $columns[$groupBy] instanceof Closure) {
		    					$thisGroupByData[$groupBy] = $columns[$groupBy]($result);
		    				} else {
		    					$thisGroupByData[$groupBy] = $result->{$columns[$groupBy]};
		    				}

		    				if (isset($currentGroupByData[$groupBy])) {
		    					if ($thisGroupByData[$groupBy] != $currentGroupByData[$groupBy]) {
		    						$isOnSameGroup = false;
		    					}
		    				}

		    				$currentGroupByData[$groupBy] = $thisGroupByData[$groupBy];
		    			}

		    			if ($isOnSameGroup === false) {
    						echo '<tr class="f-white">
    							<td class="bg-black" colspan="' . $grandTotalSkip . '"><b>Total geral</b></td>';
								$dataFound = false;
    							foreach ($columns as $colName => $colData) {
    								if (array_key_exists($colName, $showTotalColumns)) {
										$class = 'right';
										if (isset($editColumns[$colName]['class'])) {
											$class = $editColumns[$colName]['class'];
										}
    									echo '<td class="'.$class.' bg-black"><b>' . $showTotalColumns[$colName] . '</b></td>';
    									$dataFound = true;
    								} else {
    									if ($dataFound) {
	    									echo '<td class="bg-black"></td>';
	    								}
    								}
    							}
    						echo '</tr>';//<tr style="height: 10px;"><td colspan="99">&nbsp;</td></tr>';

							// Reset No, Reset Total geral
    						$no = 1;
    						$isOnSameGroup = true;
    					}
	    			}
				?>
	    		<tr align="center">
    				@if ($showNumColumn)
    	    			<td class="left">{{ $no }}</td>
    				@endif
	    			@foreach ($columns as $colName => $colData)
	    				<?php
		    				$class = 'left';
		    				// Check Edit Column to manipulate class & Data
		    				if (is_object($colData) && $colData instanceof Closure) {
		    					$generatedColData = $colData($result);
		    				} else {
		    					$generatedColData = $result->{$colData};
		    				}
		    				$displayedColValue = $generatedColData;
		    				if (array_key_exists($colName, $editColumns)) {
		    					if (isset($editColumns[$colName]['class'])) {
		    						$class = $editColumns[$colName]['class'];
		    					}

		    					if (isset($editColumns[$colName]['displayAs'])) {
		    						$displayAs = $editColumns[$colName]['displayAs'];
			    					if (is_object($displayAs) && $displayAs instanceof Closure) {
			    						$displayedColValue = $displayAs($result);
			    					} elseif (!(is_object($displayAs) && $displayAs instanceof Closure)) {
			    						$displayedColValue = $displayAs;
			    					}
			    				}
		    				}
	    				?>
	    				<td class="{{ $class }}">{{ $displayedColValue }}</td>
	    			@endforeach
	    		</tr>
    			<?php $ctr++; $no++; ?>
    		@endforeach
            <?php
            if ($applyFlush) flush();
            });
            ?>
			@if ($showTotalColumns != [] && $ctr > 1)
				<tr class="f-white">
					<td colspan="{{ $grandTotalSkip }}" class="bg-black"><b>Total geral</b></td> {{-- For Number --}}
					<?php $dataFound = false; ?>
					@foreach ($columns as $colName => $colData)
						@if (array_key_exists($colName, $showTotalColumns))
							<?php $dataFound = true;
								$class = 'right';
								if (isset($editColumns[$colName]['class'])) {
									$class = $editColumns[$colName]['class'];
								}
								?>
								<td class="{{ $class }} bg-black"><b>{{ $showTotalColumns[$colName] }}</b></td>
						@else
							@if ($dataFound)
								<td class="bg-black"></td>
							@endif
						@endif
					@endforeach
				</tr>
			@endif
    	</table>
	</body>
</html>
